<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class RSS_Admin {

    public static function main()
    {
        $tpl['MESSAGE'] = NULL;
        $message = RSS_Admin::getMessage();
        PHPWS_Core::initModClass('rss', 'Feed.php');
        PHPWS_Core::initModClass('rss', 'Channel.php');

        if (!Current_User::allow('rss')) {
            Current_User::disallow();
        }

        $panel = RSS_Admin::adminPanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['channel_id'])) {
            $channel = new RSS_Channel($_REQUEST['channel_id']);
        } else {
            $channel = new RSS_Channel;
        }

        if (isset($_REQUEST['feed_id'])) {
            $feed = new RSS_Feed($_REQUEST['feed_id']);
        } else {
            $feed = new RSS_Feed;
        }

        switch ($command) {
            case 'channels':
                $tpl = RSS_Admin::channels();
                break;

            case 'settings':
                $tpl = RSS_Admin::settings();
                break;

            case 'save_settings':
                $result = RSS_Admin::save_settings();

                if (!$result) {
                    PHPWS_Settings::save('rss');
                    $result = dgettext('rss', 'Settings saved successfully.');
                }
                $tpl = RSS_Admin::settings();
                $tpl['MESSAGE'] = &$result;
                break;

            case 'save_feed':
                $result = $feed->post();
                if (is_array($result)) {
                    $tpl = RSS_Admin::editFeed($feed);
                    $tpl['MESSAGE'] = implode('<br />', $result);
                    Layout::nakedDisplay(PHPWS_Template::process($tpl, 'rss', 'main.tpl'));
                    exit();
                } else {
                    $result = $feed->save();
                    javascript('close_refresh');
                }
                break;

            case 'edit_channel':
                $tpl = RSS_Admin::editChannel($channel);
                break;

            case 'post_channel':
                $result = $channel->post();
                if (is_array($result)) {
                    $message = implode('<br />', $result);
                    $tpl = RSS_Admin::editChannel($channel);
                } else {
                    $result = $channel->save();
                    if (PHPWS_Error::isError($result)) {
                        RSS_Admin::sendMessage(dgettext('rss', 'An error occurred when saving your channel.'), 'channels');
                    } else {
                        RSS_Admin::sendMessage(dgettext('rss', 'Channel saved.'), 'channels');
                    }
                }
                break;

            case 'reset_feed':
                $feed->reset();
            case 'import':
                $tpl = RSS_Admin::import();
                break;

            case 'turn_on_display':
                $feed->display = 1;
                $feed->save();
                $tpl = RSS_Admin::import();
                break;

            case 'turn_off_display':
                $feed->display = 0;
                $feed->save();
                $tpl = RSS_Admin::import();
                break;

            case 'add_feed':
                $tpl = RSS_Admin::editFeed($feed);
                Layout::nakedDisplay(PHPWS_Template::process($tpl, 'rss', 'main.tpl'));
                exit();
                break;

            case 'edit_feed':
                $tpl = RSS_Admin::editFeed($feed);
                Layout::nakedDisplay(PHPWS_Template::process($tpl, 'rss', 'main.tpl'));
                exit();
                break;

            case 'delete_feed':
                $feed->delete();
                $tpl = RSS_Admin::import();
                break;

            default:
                PHPWS_Core::errorPage('404');
                break;
        }

        if (!empty($message)) {
            $tpl['MESSAGE'] = $message;
        }

        $content = PHPWS_Template::process($tpl, 'rss', 'main.tpl');

        $panel->setContent($content);
        $content = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($content));
    }


    public function sendMessage($message, $command)
    {
        $_SESSION['RSS_Message'] = $message;

        PHPWS_Core::reroute(sprintf('index.php?module=rss&command=%s&authkey=%s',
        $command, Current_User::getAuthKey()));

    }

    public static function getMessage()
    {
        if (!isset($_SESSION['RSS_Message'])) {
            return NULL;
        }

        $message = $_SESSION['RSS_Message'];
        unset($_SESSION['RSS_Message']);
        return $message;
    }

    public static function adminPanel()
    {
        $opt['link'] = 'index.php?module=rss';

        $opt['title'] = dgettext('rss', 'Channels');
        $tab['channels'] = $opt;

        $opt['title'] = dgettext('rss', 'Import');
        $tab['import'] = $opt;

        $opt['title'] = dgettext('rss', 'Settings');
        $tab['settings'] = $opt;

        $panel = new PHPWS_Panel('rss_admin');
        $panel->quickSetTabs($tab);
        return $panel;
    }

    public static function editChannel(RSS_Channel $channel)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'rss');
        $form->addHidden('command', 'post_channel');
        $form->addSubmit(dgettext('rss', 'Save Channel'));

        if ($channel->id) {
            $form->addHidden('channel_id', $channel->id);
        }

        $form->addText('title', $channel->title);
        $form->setLabel('title', dgettext('rss', 'Title'));

        $form->addTextArea('description', $channel->description);
        $form->setLabel('description', dgettext('rss', 'Description'));

        $formtpl = $form->getTemplate();

        $tpl['CONTENT'] = PHPWS_Template::processTemplate($formtpl, 'rss', 'channel_form.tpl');

        $tpl['TITLE'] = dgettext('rss', 'Edit channel');

        return $tpl;

    }

    public function settings()
    {
        $form = new PHPWS_Form('rss-settings');
        $form->addHidden('module', 'rss');
        $form->addHidden('command', 'save_settings');

        $files = array(1=>'1', 2=>'2');
        $filenames = array(1=>'RSS 1.0', 2=>'RSS 2.0');
        $form->addRadio('rssfeed', $files);
        $form->setLabel('rssfeed', $filenames);
        $form->setMatch('rssfeed', PHPWS_Settings::get('rss', 'rssfeed'));


        $form->addText('editor', PHPWS_Settings::get('rss', 'editor'));
        $form->setLabel('editor', dgettext('rss', 'Managing editor email address'));
        $form->setSize('editor', 30);

        $form->addText('webmaster', PHPWS_Settings::get('rss', 'webmaster'));
        $form->setLabel('webmaster', dgettext('rss', 'Webmaster email address'));
        $form->setSize('webmaster', 30);

        $form->addText('copyright', PHPWS_Settings::get('rss', 'copyright'));
        $form->setLabel('copyright', dgettext('rss', 'Copyright'));
        $form->setSize('copyright', 40);
        $form->addSubmit(dgettext('rss', 'Save settings'));

        $tpl = $form->getTemplate();

        $fc['TITLE']   = dgettext('rss', 'General Settings');
        $fc['CONTENT'] = PHPWS_Template::process($tpl, 'rss', 'settings.tpl');

        return $fc;
    }

    public function save_settings()
    {
        $message = null;
        PHPWS_Settings::set('rss', 'rssfeed', (int)$_POST['rssfeed']);

        if (!empty($_POST['editor'])) {
            if (PHPWS_Text::isValidInput($_POST['editor'], 'email')) {
                PHPWS_Settings::set('rss', 'editor', $_POST['editor']);
            } else {
                $message = dgettext('rss', 'Please check editor email format.');
            }
        } else {
            PHPWS_Settings::set('rss', 'editor', '');
        }

        if (!empty($_POST['webmaster'])) {
            if (PHPWS_Text::isValidInput($_POST['webmaster'], 'email')) {
                PHPWS_Settings::set('rss', 'webmaster', $_POST['webmaster']);
            } else {
                $message = dgettext('rss', 'Please check webmaster email format.');
            }
        } else {
            PHPWS_Settings::set('rss', 'webmaster', '');
        }

        if (!empty($_POST['copyright'])) {
            PHPWS_Settings::set('rss', 'copyright', strip_tags($_POST['copyright']));
        }

        return $message;
    }

    public static function channels()
    {
        PHPWS_Core::initModClass('rss', 'Channel.php');
        $final_tpl['TITLE'] = dgettext('rss', 'Administrate RSS Feeds');

        $db = new PHPWS_DB('rss_channel');
        $db->addOrder('title');
        $channels = $db->getObjects('RSS_Channel');

        if (empty($channels)) {
            $final_tpl['CONTENT'] = dgettext('rss', 'No channels have been registered.');
            return $final_tpl;
        } elseif (PHPWS_Error::isError($channels)) {
            PHPWS_Error::log($channels);
            $final_tpl['CONTENT'] = dgettext('rss', 'An error occurred when trying to access your RSS channels.');
            return $final_tpl;
        }

        foreach ($channels as $oChannel) {
            $row['TITLE'] = $oChannel->getTitle();
            $row['ACTION'] = implode(' | ', $oChannel->getActionLinks());
            if ($oChannel->active) {
                $row['ACTIVE'] = dgettext('rss', 'Yes');
            } else {
                $row['ACTIVE'] = dgettext('rss', 'No');
            }

            $tpl['channels'][] = $row;
        }

        $tpl['TITLE_LABEL']  = dgettext('rss', 'Title');
        $tpl['ACTIVE_LABEL'] = dgettext('rss', 'Active');
        $tpl['ACTION_LABEL'] = dgettext('rss', 'Action');

        $final_tpl['CONTENT'] = PHPWS_Template::process($tpl, 'rss', 'channel_list.tpl');

        return $final_tpl;
    }

    public static function editFeed(RSS_Feed $feed)
    {
        $form = new PHPWS_Form;
        if ($feed->id) {
            $form->addHidden('feed_id', $feed->id);
        }
        $form->addHidden('module', 'rss');
        $form->addHidden('command', 'save_feed');

        $form->addText('address', $feed->address);
        $form->setLabel('address', dgettext('rss', 'Address'));
        $form->setSize('address', '30');

        $form->addText('title', $feed->title);
        $form->setLabel('title', dgettext('rss', 'Title'));
        $form->setSize('title', '30');

        $form->addSubmit('submit', dgettext('rss', 'Save'));

        $form->addButton('cancel', dgettext('rss', 'Cancel'));
        $form->setExtra('cancel', 'onclick="window.close()"');

        $form->addText('item_limit', $feed->item_limit);
        $form->setSize('item_limit', 2);
        $form->setLabel('item_limit', dgettext('rss', 'Item limit'));

        $form->addText('refresh_time', $feed->refresh_time);
        $form->setSize('refresh_time', 5);
        $form->setLabel('refresh_time', dgettext('rss', 'Refresh time'));

        $template = $form->getTemplate();

        $template['TITLE_WARNING'] = dgettext('rss', 'Feed title will be used if left empty');
        $template['REFRESH_WARNING'] = dgettext('rss', 'In seconds');

        $content = PHPWS_Template::process($template, 'rss', 'add_feed.tpl');

        $tpl['TITLE'] = dgettext('rss', 'Add Feed');
        $tpl['CONTENT'] = $content;
        return $tpl;
    }

    public static function import()
    {
        PHPWS_Core::requireConfig('rss');

        if (!ini_get('allow_url_fopen')) {
            $tpl['TITLE'] = dgettext('rss', 'Sorry');
            $tpl['CONTENT'] = dgettext('rss', 'You must enable allow_url_fopen in your php.ini file.');
            return $tpl;
        }

        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('rss', 'Feed.php');
        $content = NULL;

        $vars['address'] = 'index.php?module=rss&command=add_feed';
        $vars['label'] = dgettext('rss', 'Add feed');
        $vars['width'] = '450';
        $vars['height'] = '350';
        $template['ADD_LINK'] = javascript('open_window', $vars);

        $template['TITLE_LABEL']   = dgettext('rss', 'Title');
        $template['ADDRESS_LABEL'] = dgettext('rss', 'Address');
        $template['DISPLAY_LABEL'] = dgettext('rss', 'Display?');
        $template['ACTION_LABEL']  = dgettext('rss', 'Action');
        $template['REFRESH_TIME_LABEL'] = dgettext('rss', 'Refresh feed');

        $pager = new DBPager('rss_feeds', 'RSS_Feed');
        $pager->setModule('rss');
        $pager->setTemplate('admin_feeds.tpl');
        $pager->addPageTags($template);
        $pager->addRowTags('pagerTags');
        $content = $pager->get();

        $tpl['TITLE'] = dgettext('rss', 'Import RSS Feeds');
        $tpl['CONTENT'] = $content;
        if (!defined('ALLOW_CACHE_LITE') || !ALLOW_CACHE_LITE) {
            $tpl['MESSAGE'] = dgettext('rss', 'Please enable Cache Lite in your config/core/config.php file.');
        }

        return $tpl;
    }
}

?>