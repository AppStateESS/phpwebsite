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

class Podcaster_Channel {

    var $id             = 0;
    var $key_id         = 0;
    var $title          = null;
    var $description    = null;
    var $image_id       = 0;
    var $media_type     = 0;
    var $date_created   = 0;
    var $date_updated   = 0;
    var $create_user_id = 0;
    var $created_user   = null;
    var $update_user_id = 0;
    var $updated_user   = null;
    var $active         = 1;
    var $itunes_explicit = 0;
    var $itunes_category = null;
    var $_feeds         = null;
    var $_error         = null;


    function Podcaster_Channel($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    function init()
    {
        $db = new PHPWS_DB('podcaster_channel');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }


    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }


    function setImage_id($image_id)
    {
        $this->image_id = $image_id;
    }


    function setActive($active)
    {
        $this->active = $active;
    }


    function setMedia_type($media_type)
    {
        $this->media_type = $media_type;
    }


    function setItunes_explicit($itunes_explicit)
    {
        $this->itunes_explicit = $itunes_explicit;
    }


    function setItunes_category($itunes_category)
    {
        $this->itunes_category = serialize($itunes_category);
    }


    function getTitle($print=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->title);
        } else {
            return $this->title;
        }
    }


    function getPublisher($print=false)
    {
        if (empty($this->created_user)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->created_user);
        } else {
            return $this->created_user;
        }
    }


    function getChannelMast()
    {
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['PUBLISHER'] = sprintf(dgettext('podcaster', 'Published by: %s'), $this->getPublisher(true));
        return PHPWS_Template::process($tpl, 'podcaster', 'mast_channel.tpl');
    }


    function getDescription($print=false)
    {
        if (empty($this->description)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }


    function getListDescription($length=60){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
    }


    function getFile()
    {
        if (!$this->image_id) {
            return null;
        }
        return Cabinet::getTag($this->image_id);
    }

    function getImage($print=false)
    {
        if (!$this->image_id) {
            return null;
        }

        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $image = new PHPWS_Image($this->image_id);
        if (!$image->id) {
            $image->logErrors();
            return null;
        }

        if ($print) {
            return $image->getTag();
        } else {
            return $image;
        }
    }


    function getDateUpdated($format=null)
    {
        if (empty($format)) {
            $format = PCR_DATE_FORMAT;
        }

        return strftime($format, $this->date_updated);
    }


    function getMedia_type($print=false)
    {
        if (empty($this->media_type)) {
            return null;
        }

        if ($print) {
            if ($this->media_type == '0')
                return dgettext('podcaster', 'Audio/Video');
            if ($this->media_type == '1')
                return dgettext('podcaster', 'Document');
        } else {
            return $this->media_type;
        }
    }


    function getItunes_explicit($print=false)
    {
        if ($print) {
            if ($this->itunes_explicit == '0')
                return dgettext('podcaster', 'No');
            if ($this->itunes_explicit == '1')
                return dgettext('podcaster', 'Yes');
            if ($this->itunes_explicit == '2')
                return dgettext('podcaster', 'Clean');
        } else {
            if (empty($this->itunes_explicit)) {
                return null;
            }
            return $this->itunes_explicit;
        }
    }


    function getItunes_category($print=false)
    {
        if (empty($this->itunes_category)) {
            return null;
        }

        if ($print) {
            PHPWS_Core::initModClass('podcaster', 'PCR_Category.php');
            $cats = NULL;
            foreach ($this->itunes_category as $cat_id) {
                $cat = new Podcaster_Category($cat_id);
                if ($cat->parent_id > 0) {
                    $parent = new Podcaster_Category($cat->parent_id);
                    $cats .= '<itunes:category text="' . htmlentities($parent->getTitle()) . '">' . "\n";
                    $cats .= '<itunes:category text="' . htmlentities($cat->getTitle()) . '" />' . "\n";
                    $cats .= '</itunes:category>' . "\n";
                } else {
                    $cats .= '<itunes:category text="' . htmlentities($cat->getTitle()) . '" />' . "\n";
                }
            }
            return $cats;
        } else {
            return $this->itunes_category;
        }
    }


    function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $key = new Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        Layout::addPageTitle($this->getTitle());
        $template['TITLE'] = $this->getTitle(true);
        $template['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));
//        $template['IMAGE'] = $this->getImage(true);
        $template['IMAGE'] = $this->getFile();

        if (Current_User::allow('podcaster', 'edit_episode')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'new_episode';
            MiniAdmin::add('podcaster', array(PHPWS_Text::secureLink(dgettext('podcaster', 'New episode'), 'podcaster', $vars)));
        }

        if (Current_User::allow('podcaster', 'edit_channel')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_channel';
            MiniAdmin::add('podcaster', array(PHPWS_Text::secureLink(dgettext('podcaster', 'Edit channel'), 'podcaster', $vars)));
        }

        if (Current_User::allow('podcaster', 'edit_episode') || Current_User::allow('podcaster', 'edit_channel')) {
            $vars2['aop']  = 'menu';
            $vars2['tab']  = 'list';
            MiniAdmin::add('podcaster', array(PHPWS_Text::secureLink(dgettext('podcaster', 'List all channels'), 'podcaster', $vars2)));
        }

        $template['CHANNEL_LINKS'] = $this->channelLinks();

        $episodes = $this->getAllEpisodes(true);

        if (PHPWS_Error::logIfError($episodes)) {
            $this->podcaster->content = dgettext('podcaster', 'An error occurred when accessing this channel\'s episodes.');
            return;
        }

        if ($episodes) {
            foreach ($episodes as $episode) {
                $template['current-episodes'][] = $episode->viewTpl();
            }
        } else {
            if (Current_User::allow('podcaster', 'edit_episode'))
                $template['EMPTY'] = dgettext('podcaster', 'Click on "New episode" to start.');
        }

        $key->flag();

        return PHPWS_Template::process($template, 'podcaster', 'view_channel.tpl');

    }


    function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('podcaster_channel');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);

        $db = new PHPWS_DB('podcaster_episode');
        $db->addWhere('channel_id', $this->id);
        PHPWS_Core::initModClass('podcaster', 'PCR_Episode.php');
        $result = $db->getObjects('Podcaster_Episode');
        if ($result) {
            foreach ($result as $episode) {
                $episode->delete();
            }
        }

    }


    function getAllEpisodes($limit=false)
    {
        PHPWS_Core::initModClass('podcaster', 'PCR_Episode.php');
        $db = new PHPWS_DB('podcaster_episode');
        $db->addOrder('date_updated desc');
        $db->addWhere('channel_id', $this->id);
        if (!Current_User::isUnrestricted('podcaster')) {
            $db->addWhere('active', 1);
        }
        if (!Current_User::isUnrestricted('podcaster') || !Current_User::allow('podcaster', 'edit_episode')) {
            $db->addWhere('approved', 1);
        }
        if ($limit) {
            $db->setLimit(PHPWS_Settings::get('podcaster', 'channel_limit'));
        }
        $result = $db->getObjects('Podcaster_Episode');
        return $result;
    }


    function rowTag()
    {
        $vars['id'] = $this->id;
        $vars2['id'] = $this->id;
        $vars2['uop'] = 'view_rss';

        $links[] = '<a href="./index.php?module=podcaster&amp;id=' . $this->id . '&amp;uop=view_rss"><img src="' . PHPWS_SOURCE_HTTP . 'mod/podcaster/img/rss.gif" width="80" height="15" border="0" alt="' . dgettext('podcaster', 'Subscribe RSS') . '" title="' . dgettext('podcaster', 'Subscribe RSS') . '" /></a>';

        if (Current_User::allow('podcaster', 'edit_episode')){
            $vars['aop']  = 'new_episode';
            $label = Icon::show('add', dgettext('rolodex', 'Add Episode'));
            $links[] = PHPWS_Text::secureLink($label, 'podcaster', $vars);
        }

        if (Current_User::allow('podcaster', 'edit_channel')){
            $vars['aop']  = 'edit_channel';
            $label = Icon::show('edit');
            $links[] = PHPWS_Text::secureLink($label, 'podcaster', $vars);
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if ($this->active) {
                $vars['aop'] = 'deactivate_channel';
                $label = Icon::show('active', dgettext('podcaster', 'Deactivate'));
                $active = PHPWS_Text::secureLink($label, 'podcaster', $vars);
            } else {
                $vars['aop'] = 'activate_channel';
                $label = Icon::show('inactive', dgettext('podcaster', 'Activate'));
                $active = PHPWS_Text::secureLink($label, 'podcaster', $vars);
            }
            $links[] = $active;
        } else {
            if (Current_User::allow('podcaster'))
                $links[] = $this->active ? Icon::show('active') : Icon::show('inactive');
        }

        if (Current_User::allow('podcaster', 'delete_channel')){
            $vars['aop'] = 'delete_channel';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('podcaster', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('podcaster', 'Are you sure you want to delete the channel %s?\nAll related episodes and channel information will be permanently removed.'), $this->getTitle());
            $js['LINK'] = Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DATE_UPDATED'] = $this->getDateUpdated();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        if($links)
            $tpl['ACTION'] = implode(' ', $links);
        return $tpl;
    }


    function channelLinks()
    {
        $vars['id'] = $this->id;
        $vars2['id'] = $this->id;

        $vars2['uop'] = 'view_rss';
        $links[] = '<a href="./index.php?module=podcaster&amp;id=' . $this->id . '&amp;uop=view_rss"><img src="' . PHPWS_SOURCE_HTTP . 'mod/podcaster/img/rss.gif" width="80" height="15" border="0" alt="' . dgettext('podcaster', 'Subscribe RSS') . '" title="' . dgettext('podcaster', 'Subscribe RSS') . '" /></a>';

        $vars2['uop'] = 'view_archives';
        $links[] = PHPWS_Text::moduleLink(dgettext('podcaster', 'Archives'), 'podcaster', $vars2);

        if (Current_User::allow('podcaster', 'edit_episode')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'new_episode';
            $links[] = PHPWS_Text::secureLink(dgettext('podcaster', 'New episode'), 'podcaster', $vars);
        }

        if (Current_User::allow('podcaster', 'edit_channel')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_channel';
            $links[] = PHPWS_Text::secureLink(dgettext('podcaster', 'Edit channel'), 'podcaster', $vars);
        }

        if($links)
            return implode(' | ', $links);
    }


    function save()
    {
        $db = new PHPWS_DB('podcaster_channel');

        if (empty($this->id)) {
            $this->date_created = time();
            if (Current_User::isLogged()) {
                $this->create_user_id = Current_User::getId();
                $this->created_user   = Current_User::getDisplayName();
            } elseif (empty($this->created_user)) {
                $this->create_user_id = 0;
                $this->created_user   = dgettext('podcaster', 'Anonymous');
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
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

        if ($this->active) {
            $search = new Search($this->key_id);
            $search->resetKeywords();
            $search->addKeywords($this->title);
            $search->addKeywords($this->description);
            $result = $search->save();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }

    }


    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('podcaster');
        $key->setItemName('channel');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_channel');
        $key->setUrl($this->viewLink(true));
        $key->active = (int)$this->active;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('podcaster_channel');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }


    public function viewLink($bare=false)
    {
        PHPWS_Core::initCoreClass('Link.php');
//        $link = new PHPWS_Link($this->title, 'podcaster', array('id'=>$this->id));
        $link = new PHPWS_Link($this->title, 'podcaster', array('channel'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }

    }


    function loadFeeds()
    {
        PHPWS_Core::initModClass('podcaster', 'PCR_Episode.php');
        $db = new PHPWS_DB('podcaster_episode');
        $db->addWhere('channel_id', $this->id);
        $db->addWhere('active', 1);
        $db->addWhere('approved', 1);
        $db->addOrder('date_created desc');
        $db->setLimit(PHPWS_Settings::get('podcaster', 'channel_limit'));
        $result = $db->getObjects('Podcaster_Episode');

        if (PHPWS_Error::isError($result)) {
            $this->_feeds = NULL;
            $this->_error = $result;
            return $result;
        } else {
            $this->_feeds = $result;
            return TRUE;
        }

    }

    /**
     * Returns a RSS feed. Cached result is returned if exists.
     */
    function viewRSS()
    {

        if (PHPWS_Settings::get('podcaster', 'cache_timeout') > 0) {
            $cache_key = 'pcrchannel_'. $this->id .'_cache_key';
            $content = PHPWS_Cache::get($cache_key, PHPWS_Settings::get('podcaster', 'cache_timeout'));
            if (!empty($content)) {
                return $content;
            }
        }


        $home_http = PHPWS_Core::getHomeHttp();
        $image = $this->getImage();
        $template['CHANNEL_TITLE']       = $this->title;
        $template['CHANNEL_OWNER']       = $this->created_user;
        if ((bool)MOD_REWRITE_ENABLED == FALSE) {
            $template['CHANNEL_ADDRESS']     = $home_http . 'index.php?module=podcaster&amp;id=' . $this->id;
        } else {
            $template['CHANNEL_ADDRESS']     = $home_http . 'podcaster/' . $this->id;
        }
        $template['HOME_ADDRESS']        = $home_http;
        $template['SITE_TITLE']          = Layout::getPageTitle();
        $template['CHANNEL_DESCRIPTION'] = strip_tags($this->description);
        $template['CHANNEL_DESCRIPTION_PREAMBLE'] = substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->description))), 0, 60);
        $template['LANGUAGE']            = substr(ltrim(CURRENT_LANGUAGE), 0, 2); // change later
        $template['SEARCH_LINK']         = sprintf('%sindex.php?module=search&amp;mod_title=podcaster&amp;user=search', $home_http);
        $template['SEARCH_DESCRIPTION']  = sprintf('Search in %s', $this->title);
        $template['SEARCH_NAME']         = 'search';
        $template['THUMB_URL']           = $home_http . $image->file_directory . $image->file_name;
        $template['COPYRIGHT']           = PHPWS_Settings::get('podcaster', 'copyright');
        $template['WEBMASTER']           = PHPWS_Settings::get('podcaster', 'webmaster') . '(' . dgettext('podcaster', 'Webmaster') . ')';
        $template['MANAGING_EDITOR']     = PHPWS_Settings::get('podcaster', 'editor') . '(' . dgettext('podcaster', 'Managing Editor') . ')';
        $template['LAST_BUILD_DATE']     = gmstrftime('%a, %d %b %Y %R GMT', time());
        $template['ITUNES_EXPLICIT']     = $this->getItunes_explicit(true);
        $template['ITUNES_CATEGORY']     = $this->getItunes_category(true);

        if ($this->_feeds) {
            foreach ($this->_feeds as $episode) {

                $item_media = $episode->getMedia();
                $itemTpl = NULL;

                $itemTpl['ITEM_TITLE']        = $episode->title;
                $itemTpl['ITEM_LINK']         = $home_http .   'index.php?module=podcaster&amp;uop=view_episode&amp;episode_id=' . $episode->id;
                $itemTpl['ITEM_GUID']         = $home_http .   'index.php?module=podcaster&amp;uop=view_episode&amp;episode_id=' . $episode->id;
//vv                $itemTpl['ITEM_SOURCE']       = $home_http . 'index.php?module=podcaster&amp;id=' . $this->id;
                $itemTpl['ITEM_DESCRIPTION']  = strip_tags(trim($episode->description));
                $itemTpl['ITEM_DESCRIPTION_PREAMBLE'] = substr(ltrim(strip_tags(str_replace('<br />', ' ', $episode->description))), 0, 60);
                $itemTpl['ITEM_AUTHOR']       = $episode->created_user;
                $itemTpl['ITEM_PUBDATE']      = $episode->getDateCreated('%a, %d %b %Y %T GMT');
                $itemTpl['ITEM_URL']          = $home_http . $item_media->file_directory . $item_media->file_name;
                $itemTpl['ITEM_LENGTH']       = $item_media->size;
                $itemTpl['ITEM_TYPE']         = $item_media->file_type;
//vv                $itemTpl['ITEM_SOURCE_TITLE'] = $this->title;

                $template['item-listing'][] = $itemTpl;
            }
        }

        $content = PHPWS_Template::process($template, 'podcaster', 'view_rss.tpl');
        if (PHPWS_Settings::get('podcaster', 'cache_timeout') > 0) {
            PHPWS_Cache::save($cache_key, $content);
        }
        return $content;
    }


}

?>