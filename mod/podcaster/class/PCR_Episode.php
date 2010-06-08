<?php
/**
 * podcaster - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

class Podcaster_Episode {

    var $id             = 0;
    var $channel_id     = 0;
    var $key_id         = 0;
    var $title          = null;
    var $description    = null;
    var $media_id       = 0;
    var $date_created   = 0;
    var $date_updated   = 0;
    var $create_user_id = 0;
    var $created_user   = null;
    var $update_user_id = 0;
    var $updated_user   = null;
    var $approved       = 1;
    var $active         = 1;
    var $_error         = null;


    function Podcaster_Episode($id=0)
    {
        if ($id) {
            $this->id = (int)$id;
            $this->init();
        }
    }


    function init()
    {
        $db = new Core\DB('podcaster_episode');
        $result = $db->loadObject($this);
        if (Core\Error::logIfError($result) || !$result) {
            $this->id = 0;
            return false;
        }
        return true;
    }


    function setChannel_id($channel_id)
    {
        if (!is_numeric($channel_id)) {
            return false;
        } else {
            $this->channel_id = (int)$channel_id;
            return true;
        }
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }


    function setDescription($description)
    {
        $this->description = Core\Text::parseInput($description);
    }


    function setMedia_id($media_id)
    {
        $this->media_id = $media_id;
    }


    function setApproved($approved)
    {
        $this->approved = $approved;
    }


    function setActive($active)
    {
        $this->active = $active;
    }


    function getChannel($print=false, $icon=false)
    {
        if (empty($this->channel_id)) {
            return null;
        }

        if ($print) {
            Core\Core::initModClass('podcaster', 'PCR_Channel.php');
            $channel = new Podcaster_Channel($this->channel_id);
            if ($icon) {
                $link = '<a href="./index.php?module=podcaster&amp;id=' . $this->channel_id . '&amp;uop=view_rss"><img src="' . PHPWS_SOURCE_HTTP . 'mod/podcaster/img/rss_sm.png" width="14" height="14" border="0" alt="' . dgettext('podcaster', 'Subscribe RSS') . '" title="' . dgettext('podcaster', 'Subscribe RSS') . '" /></a>';
                return $link . ' ' . $channel->viewLink();
            } else {
                return $channel->viewLink();
            }
        } else {
            return $this->channel_id;
        }
    }


    function getTitle($print=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            return Core\Text::parseOutput($this->title);
        } else {
            return $this->title;
        }
    }


    function getMedia($print=false, $icon=false, $text=null)
    {
/*
        if (!$this->media_id) {
            if ($print) {
                return dgettext('podcaster', 'No Media Attached');
            } else {
                return null;
            }
        }
*/
        if (!$this->media_id) {
            if ($print) {
                return Core\Icon::show('missing', 'No Media Attached');
            } else {
                return null;
            }
        }

        Core\Core::initModClass('filecabinet', 'File_Assoc.php');
        $file = new FC_File_Assoc($this->media_id);
        if (!$file->id) {
            $file->logErrors();
            return null;
        }

        if ($file->file_type == 3) {
            Core\Core::initModClass('filecabinet', 'Multimedia.php');
            $media = new PHPWS_Multimedia($file->file_id);
            if (!$media->id) {
                $media->logErrors();
                return null;
            }
            if ($print) {
                if ($icon) {
                    return $media->getJSView(true);
                } elseif ($text) {
                    return $media->getJSView(false,$text);
                } else {
                    return $media->getJSView();
                }
            } else {
                return $media;
            }
        } elseif ($file->file_type == 2) {
            Core\Core::initModClass('filecabinet', 'Document.php');
            $media = new PHPWS_Document($file->file_id);
            if (!$media->id) {
                $media->logErrors();
                return null;
            }
            if ($print) {
                if ($icon) {
                    return $media->getViewLink(true, 'icon');
                } elseif ($text) {
                    return $media->getViewLink(true, 'title');
                } else {
                    return $media->getTag();
                }
            } else {
                return $media;
            }
        }

    }


    function getPublisher($print=false)
    {
        if (empty($this->created_user)) {
            return null;
        }

        if ($print) {
            return Core\Text::parseOutput($this->created_user);
        } else {
            return $this->created_user;
        }
    }


    function getEpisodeMast()
    {
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['PUBLISHER'] = sprintf(dgettext('podcaster', 'Published by: %s'), $this->getPublisher(true));
        $tpl['CHANNEL'] = sprintf(dgettext('podcaster', 'In channel: %s'), $this->getChannel(true));
        return Core\Template::process($tpl, 'podcaster', 'mast_episode.tpl');
    }


    function getDescription($print=false)
    {
        if (empty($this->description)) {
            return null;
        }

        if ($print) {
            return Core\Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }


    function getListDescription($length=60){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
    }


    function getDateUpdated($format=null)
    {
        if (empty($format)) {
            $format = PCR_DATE_FORMAT;
        }

        return strftime($format, $this->date_updated);
    }


    function getDateCreated($format=null)
    {
        if (empty($format)) {
            $format = PCR_DATE_FORMAT;
        }

        return strftime($format, $this->date_created);
    }


    function save()
    {
        if (!$this->channel_id) {
            return Core\Error::get(PCR_NO_CHANNEL_ID, 'podcaster', 'Podcaster_Episode::save');
        }

        $db = new Core\DB('podcaster_episode');

        if (empty($this->id)) {
            $this->date_created = time();
            if (Current_User::isLogged()) {
                $this->create_user_id = Current_User::getId();
                $this->created_user   = Current_User::getDisplayName();
            } elseif (empty($this->created_user)) {
                $this->create_user_id = 0;
                $this->created_user   = dgettext('podcaster', 'Anonymous');
            }
            if (Core\Settings::get('podcaster', 'req_approval')) {
                if (!Current_User::isUnrestricted('podcaster')) {
                    $this->approved = 0;
                }
            } else {
                $this->approved = 1;
            }

        }

        if (Current_User::isLogged()) {
            $this->update_user_id = Current_User::getId();
            $this->updated_user   = Current_User::getDisplayName();
        } elseif (empty($this->updated_user)) {
            $this->update_user_id = 0;
            $this->updated_user   = dgettext('podcaster', 'Anonymous');
        }

        $this->date_updated = time();

        $result = $db->saveObject($this);
        if (Core\Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

        if ($this->active && $this->approved) {
            $search = new Search($this->key_id);
            $search->resetKeywords();
            $search->addKeywords($this->title);
            $search->addKeywords($this->description);
            $result = $search->save();
            if (Core\Error::isError($result)) {
                return $result;
            }
        }

    }


    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Core\Key;
        } else {
            $key = new Core\Key($this->key_id);
            if (Core\Error::isError($key->_error)) {
                $key = new Core\Key;
            }
        }

        $key->setModule('podcaster');
        $key->setItemName('episode');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_episode');
        $key->setUrl($this->viewLink(true));
/*
        if (MOD_REWRITE_ENABLED) {
            $key->setUrl('podcaster/' . $this->channel_id . '/' . $this->id);
        } else {
            $key->setUrl('index.php?module=podcaster&amp;uop=view_episode&amp;episode_id=' . $this->id);
        }
*/
        if ($this->approved) {
            $key->active = (int)$this->active;
        } else {
            $key->active = 0;
        }
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (Core\Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new Core\DB('podcaster_episode');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            Core\Error::logIfError($db->update());
        }
        return true;
    }


    function episodeLinks()
    {
        $vars['episode_id'] = $this->id;
        $vars2['episode_id'] = $this->id;

        $links[] = $this->getMedia(true,false,Core\Icon::show('play'));

        if (Current_User::allow('podcaster', 'edit_episode')){
            $vars['aop']  = 'edit_episode';
            $label = Core\Icon::show('edit');
            $links[] = Core\Text::secureLink($label, 'podcaster', $vars);
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if ($this->active) {
                $vars['aop'] = 'deactivate_episode';
                $label = Core\Icon::show('active', dgettext('podcaster', 'Deactivate'));
                $active = Core\Text::secureLink($label, 'podcaster', $vars);
            } else {
                $vars['aop'] = 'activate_episode';
                $label = Core\Icon::show('inactive', dgettext('podcaster', 'Activate'));
                $active = Core\Text::secureLink($label, 'podcaster', $vars);
            }
            $links[] = $active;
        } else {
            if (Current_User::allow('podcaster'))
                $links[] = $this->active ? Core\Icon::show('active') : Core\Icon::show('inactive');
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if ($this->approved) {
                $vars['aop'] = 'unapprove_episode';
                $label = Core\Icon::show('approved', dgettext('podcaster', 'Unapprove'));
                $approved = Core\Text::secureLink($label, 'podcaster', $vars);
            } else {
                $vars['aop'] = 'approve_episode';
                $label = Core\Icon::show('unapproved', dgettext('podcaster', 'Approve'));
                $approved = Core\Text::secureLink($label, 'podcaster', $vars);
            }
            $links[] = $approved;
        } else {
            if (Current_User::allow('podcaster'))
                $links[] = $this->approved ? Core\Icon::show('approved') : Core\Icon::show('unapproved');
        }

        if (Current_User::allow('podcaster', 'delete_episode')){
            $vars['aop'] = 'delete_episode';
            $jsconf['QUESTION'] = dgettext('podcaster', 'Are you certain you want to delete this episode?');
            $jsconf['ADDRESS'] = Core\Text::linkAddress('podcaster', $vars, true);
            $jsconf['LINK'] = Core\Icon::show('delete');
            $links[] = javascript('confirm', $jsconf);
        }

        if($links)
            return implode(' ', $links);
    }


    function rowTag()
    {
        $tpl['TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['CHANNEL'] = $this->getChannel(true);
        $tpl['DATE_UPDATED'] = $this->getDateUpdated();
        $tpl['ACTION'] = $this->episodeLinks();
        return $tpl;
    }


    function viewTpl()
    {
        $template['EPISODE_TITLE'] = $this->viewLink();
        $template['DESCRIPTION'] = $this->getDescription(true);
        $template['LINKS'] = $this->episodeLinks();

        return $template;
    }


    public function viewLink($bare=false)
    {
                $link = new Core\Link($this->title, 'podcaster', array('channel'=>$this->channel_id, 'episode'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }

    }


    function view()
    {
        if (!$this->id) {
            Core\Core::errorPage(404);
        }

        $key = new Core\Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        Layout::addPageTitle($this->getTitle());
        $template['TITLE'] = $this->getTitle(true);
        $template['DESCRIPTION'] = Core\Text::parseTag($this->getDescription(true));

        if (Current_User::allow('podcaster', 'edit_episode')) {
            $vars['episode_id'] = $this->id;
            $vars['aop']  = 'edit_episode';
            MiniAdmin::add('podcaster', array(Core\Text::secureLink(dgettext('podcaster', 'Edit episode'), 'podcaster', $vars)));
        }

        if (Current_User::allow('podcaster', 'edit_episode') || Current_User::allow('podcaster', 'edit_channel')) {
            $vars2['aop']  = 'menu';
            $vars2['tab']  = 'list';
            MiniAdmin::add('podcaster', array(Core\Text::secureLink(dgettext('podcaster', 'List all channels'), 'podcaster', $vars2)));
        }

        $template['MEDIA'] = $this->getMedia(true,true);
        $template['EPISODE_LINKS'] = $this->episodeLinks();

        $key->flag();

        return Core\Template::process($template, 'podcaster', 'view_episode.tpl');

    }


    function delete()
    {
        Core\Key::drop($this->key_id);
        $db = new Core\DB('podcaster_episode');
        $db->addWhere('id', $this->id);
        if (Core\Settings::get('podcaster', 'rm_media')) {
            $media = $this->getMedia();
            if ($media) {
                $media->delete();
            }
        }
        if (Core\Error::logIfError($db->delete())) {
            return false;
        }
        return true;
    }

}
?>