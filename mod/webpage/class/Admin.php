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
        $title = NULL;
        $content = NULL;
        $message = Webpage_Admin::getMessage();

        if (!Current_User::allow('webpage')) {
            Current_User::disallow();
            exit();
        }

        $panel = Webpage_admin::cpanel();
        $panel->enableSecure();

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
            $page->_volume = &$volume;
        }

        // Determines if page panel needs creating
        // also see panel commands below switch to add content
        switch ($command) {
        case 'new':
        case 'edit_webpage':
        case 'edit_page':
        case 'add_page':
        case 'drop_page':
        case 'edit_header':
        case 'post_header':
        case 'post_page':
        case 'activate':
        case 'deactivate':
            if ( ( $volume->id 
                   && ( !Current_User::isUser($volume->create_user_id) && !Current_User::authorized('webpage', 'edit_page', $volume->id) ) )
                 || ( !Current_User::authorized('webpage', 'edit_page') ) ) {
                Current_User::disallow();
            }

            $pagePanel = Webpage_Forms::pagePanel($volume);
            $pagePanel->enableSecure();
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

        case 'approve':
            $title = _('Web Page Approval');
            $content = Webpage_Forms::approval();
            break;

        case 'join_page':
            if (!isset($_REQUEST['page_id'])) {
                PHPWS_Core::errorPage('404');
            }
            $volume->joinPage((int)$_REQUEST['page_id']);
            Webpage_Admin::sendMessage( _('Page joined.'),
                                       sprintf('edit_webpage&tab=page_%s&volume_id=%s', $page->page_number, $volume->id) );
            break;

        case 'delete_page':
            if (!isset($_REQUEST['page_id'])) {
                PHPWS_Core::errorPage('404');
            }
            $volume->dropPage((int)$_REQUEST['page_id']);
            Webpage_Admin::sendMessage(_('Page removed.'),
                                       'edit_webpage&tab=header&volume_id=' . $volume->id);
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
                    Webpage_Admin::sendMessage(_('Ignoring repeat post.'),
                                               'edit_webpage&tab=header&volume_id=' . $volume->id);
                } else {
                    Webpage_Admin::sendMessage(_('Ignoring repeat post.'), 'new');
                }
                break;
            }

            $result = $volume->post();
            if (is_array($result)) {
                $title = sprintf(_('Edit header page: %s'), $volume->title);
                $content = Webpage_Forms::editHeader($volume);
                $message = implode('<br />', $result);
            } elseif (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                Webpage_Admin::sendMessage(_('An error occurred. Please check your logs.'), 'list');
            } else {
                PHPWS_Core::initModClass('webpage', 'Forms.php');
                $result = $volume->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    Webpage_Admin::sendMessage(_('An error occurred. Please check your logs.'), 'list');
                } else {
                    Webpage_Admin::sendMessage(_('Header saved successfully.'), 
                                               'edit_webpage&tab=header&volume_id=' . $volume->id);
                }
            }
            break;

        case 'post_page':
            $title = sprintf(_('Administrate page: %s'), $volume->title);

            $result = $page->post();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                Webpage_Admin::sendMessage(_('An error occurred while saving your page. Please check the error log.'),
                                           sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                                   $page->page_number, $volume->id, $page->id));
                
                break;
            } elseif (is_array($result)) {
                $title = sprintf(_('Edit Page %s'),$page->page_number);
                $message = implode('<br />', $result);
                $content = WebpageForms::editPage($page);
            } else {
                $result = $page->save();

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    Webpage_Admin::sendMessage(_('An error occurred while saving your page. Please check the error log.'),
                                               sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                                       $page->page_number, $volume->id, $page->id));
                }

                if ( isset($_POST['force_template']) ) {
                    $force_result = $volume->forceTemplate($page->template);

                    if (PEAR::isError($force_result)) {
                        PHPWS_Error::log($force_result);
                        Webpage_Admin::sendMessage(_('Error: Unable to force template.'), sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                                                                                  $page->page_number, $volume->id, $page->id));
                    }
                }
                    Webpage_Admin::sendMessage(_('Page saved successfully.'), sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                                                                      $page->page_number, $volume->id, $page->id));
            }
            break;
            // end web page admin cases

        case 'list':
            $title = _('List Web Pages');
            $content = Webpage_Forms::wp_list();
            break;

        case 'move_to_frontpage':
            if (isset($_POST['webpage'])) {
                Webpage_Admin::setFrontPage($_POST['webpage'], 1);
            }
            PHPWS_Core::goBack();
            break;

        case 'move_off_frontpage':
            if (isset($_POST['webpage'])) {
                Webpage_Admin::setFrontPage($_POST['webpage'], 0);
            }
            PHPWS_Core::goBack();
            break;
            
            
        case 'delete_wp':
            // deletes an entire volume, coming from list page
            if (!Current_User::authorized('webpage', 'delete_page')) {
                Current_User::disallow();
                return;
            }

            $result = Webpage_Admin::deleteWebpages();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Error');
                $content = _('A problem occurred when trying to delete your webpage.');
            } else {
                PHPWS_Core::goBack();
            }

            break;

        case 'activate':
            if (isset($_POST['webpage'])) {
                Webpage_Admin::setActive($_POST['webpage'], 1);
            }
            PHPWS_Core::goBack();
            break;

        case 'deactivate':
            if (isset($_POST['webpage'])) {
                Webpage_Admin::setActive($_POST['webpage'], 0);
            }
            PHPWS_Core::goBack();
            break;

        default:
            PHPWS_Core::errorPage('404');
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

    function sendMessage($message, $command)
    {
        $_SESSION['Webpage_Message'] = $message;
        $url = sprintf('index.php?module=webpage&wp_admin=%s&authkey=%s',
                       $command, Current_User::getAuthkey());
        PHPWS_Core::reroute($url);
    }

    function getMessage()
    {
        if (!isset($_SESSION['Webpage_Message'])) {
            return NULL;
        }

        $message = $_SESSION['Webpage_Message'];
        unset($_SESSION['Webpage_Message']);
        return $message;
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

    function setFrontPage($pages, $move_val)
    {
        if (!is_array($pages)) {
            return;
        }

        $db = & new PHPWS_DB('webpage_volume');
        $db->addWhere('id', $pages);
        $db->addValue('frontpage', (int)$move_val);
        return $db->update();
    }

    function setActive($pages, $active)
    {
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('module', 'webpage');
        $db->addWhere('item_id', $pages);
        $db->addValue('active', (int)$active);
        $result = $db->update();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }


    function deleteWebpages()
    {
        @$webpage = $_REQUEST['webpage'];

        if (empty($webpage) || !is_array($webpage)) {
            return;
        }

        foreach ($webpage as $wp) {
            $volume = & new Webpage_Volume($wp);
            $result = $volume->delete();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return TRUE;
    }
}

?>