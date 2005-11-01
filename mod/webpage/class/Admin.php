<?php

/**
 * Control class for administrative options.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('webpage', 'Volume.php');
PHPWS_Core::initModClass('webpage', 'Forms.php');

class Webpage_Admin {

    /**
     * Main is the first step of administrative functions
     *
     * If the action is based on use input, the action is transferred
     * to adminForms
     */
    function main()
    {
        $title = 'Missing title';
        $content = 'Missing content';
        $message = NULL;

        if (!Current_User::allow('webpage')) {
            Current_User::disallow();
            exit();
        }

        $panel = Webpage_admin::cpanel();

        if (isset($_REQUEST['wp_admin'])) {
            $command = $_REQUEST['wp_admin'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['volume_id'])) {
            $volume = & new Webpage_Volume($_REQUEST['volume_id']);            
        } else {
            $volume = & new Webpage_Volume;
        }

        if (isset($_REQUEST['page_id'])) {
            $page = $volume->getPagebyId($_REQUEST['page_id']);
        } else {
            $page = & new Webpage_Page;
            $page->volume_id = $volume->id;
            $page->_volume = $volume;
        }

        // Determines if page panel needs creating
        // also see panel commands below switch to add content
        switch ($command) {
        case 'new':
        case 'edit_webpage':
        case 'edit_page':
        case 'add_page':
        case 'edit_header':
        case 'post_header':
        case 'post_page':
            $pagePanel = Webpage_Forms::pagePanel($volume);
        }

        switch ($command) {
            // web page admin
        case 'new':
            $pagePanel->setCurrentTab('header');
            $title = _('Create header');
            $content = Webpage_Forms::editHeader($volume);
            break;

        case 'edit_webpage':
            $title = sprintf(_('Administrate page: %s'), $volume->title);
            if ($page->id) {
                $pagePanel->setCurrentTab('page_' . $page->page_number);
                $content = $page->view(TRUE);
            } elseif (stristr($pagePanel->getCurrentTab(), 'page_')) {
                $page = $volume->getPagebyNumber(substr($pagePanel->getCurrentTab(), 5));
                if ($page) {
                    $content = $page->view(TRUE);
                } else {
                    $content = $volume->viewHeader();
                }

            } else {
                $content = $volume->viewHeader();
            }
            break;

        case 'edit_page':
            $pagePanel->setCurrentTab('page_' . $page->page_number);
            $title = sprintf(_('Edit Page %s'),$page->page_number);
            $content = Webpage_Forms::editPage($page);
            break;

        case 'add_page':
            $title = sprintf(_('Add page: %s'), $volume->title);
            $content = Webpage_Forms::editPage($page);
            break;

        case 'edit_header':
            $title = sprintf(_('Edit header: %s'), $volume->title);
            $content = Webpage_Forms::editHeader($volume);
            break;

        case 'post_header':

            if (PHPWS_Core::isPosted()) {
                if ($volume->id) {
                    PHPWS_Core::reroute('index.php?module=webpage&wp_admin=edit_webpage&volume_id=' . $volume->id);
                } else {
                    PHPWS_Core::reroute('index.php?module=webpage&tab=new');
                }
                break;
            }

            $result = $volume->post();
            if (is_array($result)) {
                $title = sprintf(_('Edit header page: %s'), $volume->title);
                $content = Webpage_Forms::editHeader($volume);
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
                    $title = sprintf(_('Administrate page: %s'), $volume->title);
                    $message = _('Header saved successfully!');
                    $pagePanel = Webpage_Forms::pagePanel($volume);
                    $content = $volume->viewHeader();
                }
            }
            break;

        case 'post_page':

            $title = sprintf(_('Administrate page: %s'), $volume->title);
            $result = $page->post();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $message = _('An error occurred while saving your page. Please check the error log.');
                $content = $page->view();
                break;
            } elseif (is_array($result)) {
                $title = sprintf(_('Edit Page %s'),$page->page_number);
                $message = implode('<br />', $result);
                $content = WebpageForms::editPage($page);
            } else {
                $result = $page->save();

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $message = _('An error occurred while saving your page. Please check the error log.');
                    $content = $page->view();
                }

                if (isset($_POST['force_template'])) {
                    $force_result = $volume->forceTemplate($page->template);

                    if (PEAR::isError($force_result)) {
                        PHPWS_Error::log($force_result);
                        $message = _('An error occurred when trying to force the page templates.');
                    }
                }

                $volume->loadPages();
                $pagePanel = Webpage_Forms::pagePanel($volume);
                $pagePanel->setCurrentTab('page_' . $page->page_number);
                $message = _('Page saved successfully.');
                $content = $page->view();
            }
            break;
            // end web page admin cases

        case 'list':
            $title = _('List Web Pages');
            $content = Webpage_Forms::wp_list();
            break;

        case 'delete_webpage':
            if (!Current_User::authorized('webpage', 'delete_page')) {
                Current_User::disallow();
            }
            $result = $volume->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Error');
                $content = _('A problem occurred when trying to delete your webpage.');
            } else {
                PHPWS_Core::goBack();
            }
            break;

        default:
            exit($command);
        }

        // Sticks inside the panel
        switch ($command) {
        case 'new':
        case 'edit_webpage':
        case 'edit_page':
        case 'add_page':
        case 'post_page':
        case 'edit_header':
        case 'post_header':
            $pagePanel->setContent($content);
            $content = $pagePanel->display();
        }


        $template = Webpage_Admin::template($title, $content, $message);

        $final = PHPWS_Template::process($template, 'webpage', 'main.tpl');
        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
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

        $panel = & new PHPWS_Panel('wp_main_panel');
        $panel->quickSetTabs($tabs);

        $panel->setModule('webpage');
        return $panel;
    }



}

?>