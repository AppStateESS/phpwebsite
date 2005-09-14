<?php

/**
 * Control class for administrative options.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('webpage', 'Volume.php');

class Webpage_Admin {

    function main()
    {
        if (!Current_User::allow('webpage')) {
            Current_User::disallow();
            exit();
        }

        $panel = Webpage_admin::cpanel();

        if (isset($_REQUEST['wp_admin'])) {
            $command = $_REQUEST['wp_admin'];
        } else {
            $command = $panel->getCurrentTab();
        }

        switch ($command) {
        case 'new':
        case 'list':
            $template = Webpage_Admin::adminForms($command);
            break;

        case 'post_volume':
            $template = Webpage_Admin::postVolume();
            break;
        }

        if (isset($_REQUEST['volume_id'])) {
            $volume = & new Webpage_Volume($_REQUEST['volume_id']);
        }


        $final = PHPWS_Template::process($template, 'webpage', 'main.tpl');
        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }
        

    function adminForms($command) {
        PHPWS_Core::initModClass('webpage', 'Forms.php');
        $title = $content = $message = NULL;

        switch ($command) {
        case 'new':
            $volume = & new Webpage_Volume;
            $title = _('Create new Web Page');
            $content = Webpage_Forms::edit($volume);
            break;
        case 'edit':
            $volume = & new Webpage_Volume($_REQUEST['volume_id']);
            $title = _('Update Web Page');
            $content = Webpage_Forms::edit($volume);
            break;

        case 'list':
            $title = _('Webpage List');
            $content = Webpage_Forms::wp_list();
            break;
        }

        return Webpage_Admin::template($title, $content, $message);
    }

    function postVolume()
    {
        if (PHPWS_Core::isPosted()) {
            return Webpage_Admin::template(_('Repeat post'),
                                           _('You have previously created or updated a Web Page volume on this page.'));
        }

        if (isset($_POST['volume_id'])) {
            $volume = & new Webpage_Volume($_POST['volume_id']);
        } else {
            $volume = & new Webpage_Volume;
        }

        

        $result = $volume->post();
        if (is_array($result)) {
            if ($volume->id) {
                $title = _('Update Web Page');
            } else {
                $title = _('Create new Web Page');
            }

            $content = Webpage_Forms::edit($volume);
            $message = implode('<br />', $result);
        } elseif (PEAR::isError($result)) {
            $title = _('Sorry');
            $content = _('An error occurred. Please check your logs.');
            PHPWS_Error::log($result);
        } else {
            PHPWS_Core::initModClass('webpage', 'Forms.php');
            $result = $volume->save();
            if (PEAR::isError($result)) {
                $title = _('Sorry');
                $content = _('An error occurred. Please check your logs.');
                PHPWS_Error::log($result);
            } else {
                $title = _('Edit pages');
                $message = _('Volume saved successfully!');
                $content = Webpage_Forms::edit_pages($volume);
            }
        }

        return Webpage_Admin::template($title, $content, $message);
    }

    function template($title, $content, $message=NULL)
    {
        $template['TITLE']   = $title;
        $template['CONTENT'] = $content;
        $template['MESSAGE'] = $message;
        return $template;
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link['link'] = 'index.php?module=webpage';

        $link['title'] = _('New');
        $tabs['new'] = $link;

        $link['title'] = _('List');
        $tabs['list'] = $link;

        $link['title'] = _('Approval');
        $tabs['approve'] = $link;

        $panel = & new PHPWS_Panel('blog');
        $panel->quickSetTabs($tabs);

        $panel->setModule('webpage');
        return $panel;
    }



}

?>