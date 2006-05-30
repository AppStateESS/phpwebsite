<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class RSS_Admin {

    function main()
    {
        $message = RSS_Admin::getMessage();
        PHPWS_Core::initModClass('rss', 'Channel.php');

        if (!Current_User::allow('rss')) {
            Current_User::disallow();
        }

        $panel = & RSS_Admin::adminPanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['channel_id'])) {
            $channel = & new RSS_Channel($_REQUEST['channel_id']);
        } else {
            $channel = & new RSS_Channel;
        }

        switch ($command) {
        case 'channels':
            $tpl = RSS_Admin::channels();
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
                if (PEAR::isError($result)) {
                    RSS_Admin::sendMessage(_('An error occurred when saving your channel.'), 'channels');
                } else {
                    RSS_Admin::sendMessage(_('Channel saved.'), 'channels');
                }
            }
            break;

        case 'import':
            $tpl['TITLE'] = 'Sorry';
            $tpl['CONTENT'] = 'This section has not been written yet.';
            break;

        default:
            PHPWS_Core::errorPage('404');
            break;
        }

        $tpl['MESSAGE'] = $message;

        $content = PHPWS_Template::process($tpl, 'rss', 'main.tpl');

        $panel->setContent($content);
        $content = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($content));
    }


    function sendMessage($message, $command)
    {
        $_SESSION['RSS_Message'] = $message;

        PHPWS_Core::reroute(sprintf('index.php?module=rss&command=%s&authkey=%s',
                                    $command, Current_User::getAuthKey()));

    }

    function getMessage()
    {
        if (!isset($_SESSION['RSS_Message'])) {
            return NULL;
        }

        $message = $_SESSION['RSS_Message'];
        unset($_SESSION['RSS_Message']);
        return $message;
    }

    function &adminPanel()
    {
        $opt['link'] = 'index.php?module=rss';

        $opt['title'] = _('Channels'); 
        $tab['channels'] = $opt;

        $opt['title'] = _('Import');
        $tab['import'] = $opt;

        $panel = & new PHPWS_Panel('rss_admin');
        $panel->quickSetTabs($tab);
        return $panel;
    }

    function editChannel(&$channel)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'rss');
        $form->addHidden('command', 'post_channel');
        $form->addSubmit(_('Save Channel'));

        if ($channel->id) {
            $form->addHidden('channel_id', $channel->id);
        }

        $form->addText('title', $channel->title);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('description', $channel->description);
        $form->setLabel('description', _('Description'));

        $formtpl = $form->getTemplate();
        
        $tpl['CONTENT'] = PHPWS_Template::processTemplate($formtpl, 'rss', 'channel_form.tpl');

        $tpl['TITLE'] = _('Edit channel');

        return $tpl;

    }

    function channels()
    {
        PHPWS_Core::initModClass('rss', 'Channel.php');
        $final_tpl['TITLE'] = _('Administrate RSS Feeds');

        $db = & new PHPWS_DB('rss_channel');
        $db->addOrder('title');
        $channels = $db->getObjects('RSS_Channel');
        
        if (empty($channels)) {
            $final_tpl['CONTENT'] = _('No channels have been registered.');
            return $final_tpl;
        } elseif (PEAR::isError($channels)) {
            PHPWS_Error::log($channels);
            $final_tpl['CONTENT'] = _('An error occurred when trying to access your RSS channels.');
            return $final_tpl;
        }

        foreach ($channels as $oChannel) {
            $row['TITLE'] = $oChannel->title;
            $row['ACTION'] = implode(' | ', $oChannel->getActionLinks());
            if ($oChannel->active) {
                $row['ACTIVE'] = _('Yes');
            } else {
                $row['ACTIVE'] = _('No');
            }

            $tpl['channels'][] = $row;
        }

        $tpl['TITLE_LABEL']  = _('Title');
        $tpl['ACTIVE_LABEL'] = _('Active');
        $tpl['ACTION_LABEL'] = _('Action');


        $final_tpl['CONTENT'] = PHPWS_Template::process($tpl, 'rss', 'channel_list.tpl');

        return $final_tpl;
    }

}

?>