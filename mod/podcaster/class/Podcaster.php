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

Core\Core::requireInc('podcaster', 'errordefines.php');
Core\Core::requireConfig('podcaster');

class Podcaster {
    var $forms   = null;
    var $panel   = null;
    var $title   = null;
    var $message = null;
    var $content = null;
    var $channel = null;
    var $episode = null;

    function adminMenu()
    {
        if (!Current_User::allow('podcaster')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;

        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'edit_channel':
                $this->loadForm('edit_channel');
                break;

            case 'post_channel':
                if (!Current_User::authorized('podcaster')) {
                    Current_User::disallow();
                }
                if ($this->postChannel()) {
                    if (Core\Error::logIfError($this->channel->save())) {
                        $this->forwardMessage(dgettext('podcaster', 'Error occurred when saving channel.'));
                        Core\Core::reroute('index.php?module=podcaster&aop=list');
                    } else {
                        $this->forwardMessage(dgettext('podcaster', 'Channel saved successfully.') . ' ' . dgettext('podcaster', 'Add an episode below.'));
                        Core\Core::reroute('index.php?module=podcaster&aop=edit_episode&id=' . $this->channel->id);
                    }
                } else {
                    $this->loadForm('edit');
                }
                break;

            case 'activate_channel':
                if (Current_User::isRestricted('podcaster')) {
                    Current_User::disallow();
                }
                $this->loadChannel();
                $this->channel->active = 1;
                $this->channel->save();
                $this->message = dgettext('podcaster', 'Podcaster channel activated.');
                $this->loadForm('list');
                break;

            case 'deactivate_channel':
                if (Current_User::isRestricted('podcaster')) {
                    Current_User::disallow();
                }
                $this->loadChannel();
                $this->channel->active = 0;
                $this->channel->save();
                $this->message = dgettext('podcaster', 'Podcaster channel deactivated.');
                $this->loadForm('list');
                break;

            case 'delete_channel':
                $this->loadChannel();
                $this->channel->delete();
                $this->message = dgettext('podcaster', 'Podcaster channel deleted.');
                $this->loadForm('list');
                break;

            case 'new_episode':
            case 'edit_episode':
                $this->loadEpisode();
                $this->loadForm('edit_episode');
                break;

            case 'post_episode':
                $javascript = true;
                if (!Current_User::authorized('podcaster')) {
                    Current_User::disallow();
                }

                if ($this->postEpisode()) {
                    if (Core\Error::logIfError($this->episode->save())) {
                        $this->forwardMessage(dgettext('podcaster', 'Error occurred when saving episode.'));
                        Core\Core::reroute('index.php?module=podcaster&aop=list');
                    } else {
                        if (!$this->episode->approved) {
                            $this->forwardMessage(dgettext('podcaster', 'Episode submitted for approval successfully.'));
                        } else {
                            $this->forwardMessage(dgettext('podcaster', 'Episode saved successfully.'));
                        }
//                        Core\Core::reroute('index.php?module=podcaster&aop=edit_episode&id=' . $this->channel->id);
                        Core\Core::reroute('index.php?module=podcaster&uop=view_channel&id=' . $this->channel->id);
                    }
                } else {
                    $this->loadForm('edit_episode');
                }
                break;

            case 'activate_episode':
                if (Current_User::isRestricted('podcaster')) {
                    Current_User::disallow();
                }
                $this->loadEpisode();
                $this->episode->active = 1;
                $this->episode->save();
                $this->message = dgettext('podcaster', 'Podcaster episode activated.');
                $this->content = $this->channel->view();
                break;

            case 'deactivate_episode':
                if (Current_User::isRestricted('podcaster')) {
                    Current_User::disallow();
                }
                $this->loadEpisode();
                $this->episode->active = 0;
                $this->episode->save();
                $this->message = dgettext('podcaster', 'Podcaster episode deactivated.');
                $this->content = $this->channel->view();
                break;

            case 'approve_episode':
                if (Current_User::isRestricted('podcaster')) {
                    Current_User::disallow();
                }
                $this->loadEpisode();
                $this->episode->approved = 1;
                $this->episode->save();
                $this->forwardMessage(dgettext('podcaster', 'Podcaster episode approved.'));
                Core\Core::reroute('index.php?module=podcaster&aop=menu&tab=approvals');
                break;

            case 'unapprove_episode':
                if (Current_User::isRestricted('podcaster')) {
                    Current_User::disallow();
                }
                $this->loadEpisode();
                $this->episode->approved = 0;
                $this->episode->save();
                $this->forwardMessage(dgettext('podcaster', 'Podcaster episode unapproved.'));
                Core\Core::reroute('index.php?module=podcaster&aop=menu&tab=approvals');
                break;

            case 'delete_episode':
                $this->loadEpisode();
                $this->deleteEpisode();
                break;


            case 'post_settings':
                if (!Current_User::authorized('podcaster', 'settings')) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('podcaster', 'Podcaster settings saved.'));
                    Core\Core::reroute('index.php?module=podcaster&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'podcaster', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(Core\Template::process($tpl, 'podcaster', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    function sendMessage()
    {
        Core\Core::reroute('index.php?module=podcaster&amp;uop=message');
    }

    function forwardMessage($message, $title=null)
    {
        $_SESSION['PCR_Message']['message'] = $message;
        if ($title) {
            $_SESSION['PCR_Message']['title'] = $title;
        }
    }


    function loadMessage()
    {
        if (isset($_SESSION['PCR_Message'])) {
            $this->message = $_SESSION['PCR_Message']['message'];
            if (isset($_SESSION['PCR_Message']['title'])) {
                $this->title = $_SESSION['PCR_Message']['title'];
            }
            Core\Core::killSession('PCR_Message');
        }
    }


    function loadForm($type)
    {
        Core\Core::initModClass('podcaster', 'PCR_Forms.php');
        $this->forms = new Podcaster_Forms;
        $this->forms->podcaster = & $this;
        $this->forms->get($type);
    }


    function loadChannel($id=0)
    {
        Core\Core::initModClass('podcaster', 'PCR_Channel.php');

        if ($id) {
            $this->channel = new Podcaster_Channel($id);
        } elseif (isset($_REQUEST['channel_id'])) {
            $this->channel = new Podcaster_Channel($_REQUEST['channel_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->channel = new Podcaster_Channel($_REQUEST['id']);
        } elseif (isset($_REQUEST['channel'])) {
            $this->channel = new Podcaster_Channel($_REQUEST['channel']);
        } else {
            $this->channel = new Podcaster_Channel;
        }
    }


    function loadEpisode($id=0)
    {
        Core\Core::initModClass('podcaster', 'PCR_Episode.php');
        if ($id) {
            $this->episode = new Podcaster_Episode($id);
        } elseif (isset($_REQUEST['episode_id'])) {
            $this->episode = new Podcaster_Episode($_REQUEST['episode_id']);
        } elseif (isset($_REQUEST['episode'])) {
            $this->episode = new Podcaster_Episode($_REQUEST['episode']);
        } else {
            $this->episode = new Podcaster_Episode;
        }

        if (empty($this->channel)) {
            if ($this->episode->channel_id) {
                $this->loadChannel($this->episode->channel_id);
            } else {
                $this->loadChannel();
                $this->episode->channel_id = $this->channel->id;
            }
        }

    }


    function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                Core\Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }

        $this->loadMessage();

        switch ($action) {
            case 'message':
                $this->loadMessage();
                if (empty($this->message)) {
                    Core\Core::home();
                }
                $this->title = dgettext('podcaster', 'Podcaster');
                break;

            case 'list':
                Core\Core::initModClass('podcaster', 'PCR_Forms.php');
                $this->forms = new Podcaster_Forms;
                $this->forms->podcaster = & $this;
                $this->forms->listChannels();
                break;

            case 'view_archives':
                $channel_id = $_REQUEST['id'];
                $approved = 1;
                Core\Core::initModClass('podcaster', 'PCR_Forms.php');
                $this->forms = new Podcaster_Forms;
                $this->forms->podcaster = & $this;
                $this->forms->listEpisodes($approved,$channel_id);
                break;

            case 'view_channel':
                $this->loadChannel();
                $this->title = $this->channel->getChannelMast();
                $this->content = $this->channel->view();
                break;

            case 'view_episode':
                $this->loadEpisode();
                $this->title = $this->episode->getEpisodeMast();
                $this->content = $this->episode->view();
                break;

            case 'view_rss':
                $this->loadChannel();
                $this->rssChannel();
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'podcaster', 'main_user.tpl'));
        } else {
            Layout::add(Core\Template::process($tpl, 'podcaster', 'main_user.tpl'));
        }

    }


    function rssChannel()
    {
        if (empty($this->channel)) {
            $this->loadChannel();
        }
        if ($this->channel->id) {
            $this->channel->loadFeeds();
            header('Content-type: text/xml');
            echo $this->channel->viewRSS();
            exit();
        } else {
            Core\Core::errorPage('404');
        }
    }



    function loadPanel()
    {
        Core\Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('podcaster-panel');
        $link = 'index.php?module=podcaster&aop=menu';
        $db = new Core\DB('podcaster_episode');
        $db->addWhere('approved', 0);
        $unapproved = $db->count();

        if (Current_User::allow('podcaster', 'edit_channel')){
            $tags['new'] = array('title'=>dgettext('podcaster', 'New Channel'),
                                 'link'=>$link);
        }
        $tags['list'] = array('title'=>dgettext('podcaster', 'List Channels'),
                              'link'=>$link);
        if (Current_User::isUnrestricted('podcaster')) {
            $tags['episodes'] = array('title'=>dgettext('podcaster', 'List Episodes'),
                                  'link'=>$link);
            $tags['approvals'] = array('title'=>sprintf(dgettext('podcaster', 'Approval (%s)'), $unapproved),
                                  'link'=>$link);
        }
        if (Current_User::allow('podcaster', 'settings')){
            $tags['settings'] = array('title'=>dgettext('podcaster', 'Settings'),
                                  'link'=>$link);
        }
        if (Current_User::isDeity()) {
            $tags['info'] = array('title'=>dgettext('podcaster', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    function postEpisode()
    {
        $this->loadEpisode();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('podcaster', 'You must give your episode a title.');
        } else {
            $this->episode->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('podcaster', 'You must give your episode a description.');
        } else {
            $this->episode->setDescription($_POST['description']);
        }

        if (isset($_POST['media_id'])) {
            $this->episode->setMedia_id((int)$_POST['media_id']);
        }

        $this->episode->setChannel_id($_POST['channel_id']);

        if (empty($this->episode->channel_id)) {
            $errors[] = dgettext('podcaster', 'Fatal error: Cannot create episode. Missing channel id.');
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if (isset($_POST['active'])) {
                $this->episode->setActive(1);
            } else {
                $this->episode->setActive(0);
            }
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if (isset($_POST['approved'])) {
                $this->episode->setApproved(1);
            } else {
                $this->episode->setApproved(0);
            }
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }

    function postChannel()
    {
        $this->loadChannel();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('podcaster', 'You must give this podcaster channel a title.');
        } else {
            $this->channel->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('podcaster', 'You must give this podcaster channel a description.');
        } else {
            $this->channel->setDescription($_POST['description']);
        }

        if (isset($_POST['image_id'])) {
            $this->channel->setImage_id((int)$_POST['image_id']);
        }

        if (isset($_POST['media_type'])) {
            $this->channel->setMedia_type((int)$_POST['media_type']);
        }

        if (isset($_POST['itunes_explicit'])) {
            $this->channel->setItunes_explicit((int)$_POST['itunes_explicit']);
        }

        if (isset($_POST['itunes_category'])) {
            $this->channel->setItunes_category($_POST['itunes_category']);
        } else {
            $this->channel->setItunes_category(0);
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if (isset($_POST['active'])) {
                $this->channel->setActive(1);
            } else {
                $this->channel->setActive(0);
            }
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }

    }

    function postSettings()
    {

        $channel_limit = (int)$_POST['channel_limit'];
        if ((int)$channel_limit > 0 && (int)$channel_limit <= 50) {
            Core\Settings::set('podcaster', 'channel_limit', $channel_limit);
        } else {
            Core\Settings::reset('podcaster', 'channel_limit');
        }

        $cache_timeout = (int)$_POST['cache_timeout'];
        if ((int)$cache_timeout <= 7200) {
            Core\Settings::set('podcaster', 'cache_timeout', $cache_timeout);
        } else {
            Core\Settings::reset('podcaster', 'cache_timeout');
        }

        isset($_POST['show_block']) ?
            Core\Settings::set('podcaster', 'show_block', 1) :
            Core\Settings::set('podcaster', 'show_block', 0);

        isset($_POST['req_approval']) ?
            Core\Settings::set('podcaster', 'req_approval', 1) :
            Core\Settings::set('podcaster', 'req_approval', 0);

        Core\Settings::set('podcaster', 'block_order_by_rand', $_POST['block_order_by_rand']);

        isset($_POST['block_on_home_only']) ?
            Core\Settings::set('podcaster', 'block_on_home_only', 1) :
            Core\Settings::set('podcaster', 'block_on_home_only', 0);


        if (!empty($_POST['editor'])) {
            if (Core\Text::isValidInput($_POST['editor'], 'email')) {
                Core\Settings::set('podcaster', 'editor', $_POST['editor']);
            } else {
                $errors[] = dgettext('podcaster', 'Please check editor email format.');
            }
        } else {
            Core\Settings::set('podcaster', 'editor', '');
        }

        if (!empty($_POST['webmaster'])) {
            if (Core\Text::isValidInput($_POST['webmaster'], 'email')) {
                Core\Settings::set('podcaster', 'webmaster', $_POST['webmaster']);
            } else {
                $errors[] = dgettext('podcaster', 'Please check webmaster email format.');
            }
        } else {
            Core\Settings::set('podcaster', 'webmaster', '');
        }

        if (!empty($_POST['copyright'])) {
            Core\Settings::set('podcaster', 'copyright', strip_tags($_POST['copyright']));
        }

        isset($_POST['rm_media']) ?
            Core\Settings::set('podcaster', 'rm_media', 1) :
            Core\Settings::set('podcaster', 'rm_media', 0);



        isset($_POST['mod_folders_only']) ?
            Core\Settings::set('podcaster', 'mod_folders_only', 1) :
            Core\Settings::set('podcaster', 'mod_folders_only', 0);

        if ( !empty($_POST['max_width']) ) {
            $max_width = (int)$_POST['max_width'];
            if ($max_width >= 50 && $max_width <= 600 ) {
                Core\Settings::set('podcaster', 'max_width', $max_width);
            }
        }

        if ( !empty($_POST['max_height']) ) {
            $max_height = (int)$_POST['max_height'];
            if ($max_height >= 50 && $max_height <= 600 ) {
                Core\Settings::set('podcaster', 'max_height', $max_height);
            }
        }


        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (Core\Settings::save('podcaster')) {
                return true;
            } else {
                return falsel;
            }
        }

    }

    function deleteEpisode()
    {

        if ($this->episode->delete()) {
            $this->title = dgettext('podcaster', 'Episode deleted successfully.');
        } else {
            $this->title = dgettext('podcaster', 'Episode could not be deleted successfully.');
        }

        $this->content = Core\Text::secureLink(dgettext('podcaster', 'Return to channel page'), 'podcaster',
                                                array('id'=>$this->channel->id, 'uop'=>'view_channel'));

    }
}

?>