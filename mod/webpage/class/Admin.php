<?php

/**
 * Control class for administrative options.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::requireInc('webpage', 'error_defines.php');
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
            $volume = new Webpage_Volume($_REQUEST['volume_id']);
        } else {
            $volume = new Webpage_Volume;
        }

        // Only makes volume version. Page versions made separately.
        if (!empty($_REQUEST['version_id'])) {
            $version = new Version('webpage_volume', (int)$_REQUEST['version_id']);
            $version->loadObject($volume);
            $volume->loadApprovalPages();
            $version_id = $version->id;
        } else {
            $version_id = 0;
            $version = NULL;
        }

        if (isset($_REQUEST['page_id'])) {
            $page = $volume->getPagebyId($_REQUEST['page_id']);
        } else {
            $page = new Webpage_Page;
            $page->volume_id = $volume->id;
            $page->_volume = & $volume;
        }

        // Determines if page panel needs creating
        // also see panel commands below switch to add content
        switch ($command) {
        case 'edit_webpage':
        case 'edit_page':
        case 'add_page':
            if ($volume->id && Current_User::isRestricted('webpage')) {
                $xversion = new Version('webpage_volume');
                $xversion->setSource($volume);
                $approval_id = $xversion->isWaitingApproval();
                if ($approval_id) {
                     $version_id = & $approval_id;
                     $version = new Version('webpage_volume', $version_id);
                     $version->loadObject($volume);
                     $volume->loadApprovalPages();
                     if (isset($volume->_pages[$_REQUEST['page_id']])) {
                         $page = $volume->_pages[$_REQUEST['page_id']];
                     }
                }

            }
        case 'new':
        case 'delete_page':
        case 'edit_header':
        case 'post_header':
        case 'post_page':
        case 'activate':
        case 'deactivate':
        case 'feature':
        case 'approval_view':
        case 'deactivate_vol':
        case 'activate_vol':
        case 'restore_volume':
        case 'restore_page':
            $pagePanel = Webpage_Forms::pagePanel($volume, $version_id);
            $pagePanel->enableSecure();
        }

        switch ($command) {
            // web page admin
        case 'new':
            $pagePanel->setCurrentTab('header');
            $title = dgettext('webpage', 'Create header');
            $content = Webpage_Forms::editHeader($volume, $version);
            break;

        case 'restore_volume_version':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }
            $version->restore();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Header restored.'),
                                       sprintf('edit_webpage&tab=header&volume_id=%s', $volume->id) );
            break;

        case 'restore_page_version':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $page_version = new Version('webpage_page', (int)$_GET['version_id']);
            /**
             * Have to set the page number.
             * We don't want the saved version to screw up the page order
             **/
            $page_version->source_data['page_number'] = $page->page_number;
            $page_version->restore();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Page restored.'),
                                       sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                               $page->page_number, $volume->id, $page->id));
            break;

        case 'page_up':
            if (!Current_User::allow('webpage', 'edit_page', null, null, true)) {
                Current_User::disallow();
            }
            $page->moveUp();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Page moved.'),
                                        sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                                $page->page_number, $volume->id, $page->id));
            break;

        case 'page_down':
            if (!Current_User::allow('webpage', 'edit_page', null, null, true)) {
                Current_User::disallow();
            }

            $page->moveDown();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Page moved.'),
                                        sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s',
                                                $page->page_number, $volume->id, $page->id));
            break;

        case 'remove_page_restore':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $page_version = new Version('webpage_page', (int)$_GET['version_id']);
            $page_version->delete();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Page restored.'),
                                       sprintf('restore_page&tab=page_%s&volume_id=%s&page_id=%s',
                                               $page->page_number, $volume->id, $page->id));


        case 'remove_volume_restore':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $version->delete();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Header version deleted.'),
                                       sprintf('restore_volume&volume_id=%s', $volume->id) );
            break;

        case 'edit_webpage':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            $title = sprintf(dgettext('webpage', 'Administrate page: %s'), $volume->title);
            if ($page->id) {
                $pagePanel->setCurrentTab('page_' . $page->page_number);
                $content = $page->view(TRUE, $version_id);
            } elseif (stristr($pagePanel->getCurrentTab(), 'page_')) {
                $page = $volume->getPagebyNumber(substr($pagePanel->getCurrentTab(), 5));

                if ($page) {
                    $content = $page->view(TRUE, $version_id);
                } else {
                    $content = $volume->viewHeader($version_id);
                }

            } else {
                $content = $volume->viewHeader($version_id);
            }
            break;

        case 'approve':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $title = dgettext('webpage', 'Web Page Approval');
            $content = Webpage_Forms::approval();
            break;

        case 'join_page':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            if (!isset($_REQUEST['page_id'])) {
                PHPWS_Core::errorPage('404');
            }
            $volume->joinPage((int)$_REQUEST['page_id']);
            Webpage_Admin::sendMessage( dgettext('webpage', 'Page joined.'),
                                       sprintf('edit_webpage&tab=page_%s&volume_id=%s', $page->page_number, $volume->id) );
            break;

        case 'join_all_pages':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            $volume->joinAllPages();
            Webpage_Admin::sendMessage( dgettext('webpage', 'Pages joined.'),
                                        sprintf('edit_webpage&tab=page_1&volume_id=%s', $volume->id) );
            break;

        case 'delete_page':
            if (!Current_User::allow('webpage', 'edit_page', $volume->id, 'volume', true)) {
                Current_User::disallow();
            }

            if (!isset($_REQUEST['page_id'])) {
                PHPWS_Core::errorPage('404');
            }
            $volume->dropPage((int)$_REQUEST['page_id']);
            Webpage_Admin::sendMessage(dgettext('webpage', 'Page removed.'),
                                       'edit_webpage&tab=header&volume_id=' . $volume->id);
            break;


        case 'approval_view':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $title = dgettext('webpage', 'Approval view');
            if (!isset($_REQUEST['version_id'])) {
                PHPWS_Core::errorPage('404');
            }
            $content = Webpage_Admin::approvalView($volume, $version);
            break;

        case 'disapprove_webpage':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow(dgettext('webpage', 'Attempted to disapprove a webpage.'));
                return;
            }
            if (Webpage_Admin::disapproveWebpage()) {
                Webpage_Admin::sendMessage(dgettext('webpage', 'Web page disapproved.'),'approve');
            } else {
                Webpage_Admin::sendMessage(dgettext('webpage', 'A problem occurred when trying to disapprove a web page.'),'approve');
            }
            break;

        case 'approve_webpage':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow(dgettext('webpage', 'Attempted to approve a webpage.'));
                return;
            }
            if (Webpage_Admin::approveWebpage()) {
                Webpage_Admin::sendMessage(dgettext('webpage', 'Web page approved.'),'approve');
            } else {
                Webpage_Admin::sendMessage(dgettext('webpage', 'A problem occurred when trying to approve a web page.'),'approve');
            }
            break;

        case 'edit_page':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            $pagePanel->setCurrentTab('page_' . $page->page_number);
            $title = sprintf(dgettext('webpage', 'Edit Page %s'),$page->page_number);
            $content = Webpage_Forms::editPage($page, $version);
            break;

        case 'add_page':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            $pagePanel->setCurrentTab('add_page');
            $title = sprintf(dgettext('webpage', 'Add page: %s'), $volume->title);
            $content = Webpage_Forms::editPage($page, $version);
            break;

        case 'edit_header':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            $pagePanel->setCurrentTab('header');
            $title = sprintf(dgettext('webpage', 'Edit header: %s'), $volume->title);
            $content = Webpage_Forms::editHeader($volume, $version);
            break;

        case 'post_header':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }

            if (PHPWS_Core::isPosted()) {
                if ($volume->id) {
                    Webpage_Admin::sendMessage(dgettext('webpage', 'Ignoring repeat post.'),
                                               'edit_webpage&tab=header&volume_id=' . $volume->id);
                } else {
                    Webpage_Admin::sendMessage(dgettext('webpage', 'Ignoring repeat post.'), 'new');
                }
                break;
            }

            $result = $volume->post();

            if (is_array($result)) {
                // errors occurred
                $title = sprintf(dgettext('webpage', 'Edit header page: %s'), $volume->title);
                $content = Webpage_Forms::editHeader($volume, $version);
                $message = implode('<br />', $result);
            } else {
                PHPWS_Core::initModClass('webpage', 'Forms.php');
                $new_vol = (bool)$volume->id ? false : true;
                $result = $volume->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    Webpage_Admin::sendMessage(dgettext('webpage', 'An error occurred. Please check your logs.'), 'list');
                } else {
                    if ($new_vol) {
                        Webpage_Admin::sendMessage(dgettext('webpage', 'Header saved successfully.'), 
                                                   sprintf('add_page&volume_id=%s&version_id=%s',
                                                           $volume->id, $version_id)
                                                   );
                    } else {
                        Webpage_Admin::sendMessage(dgettext('webpage', 'Header saved successfully.'), 
                                                   'edit_webpage&tab=header&volume_id=' . $volume->id . '&version_id=' . $version_id);
                    }
                }
            }
            break;

        case 'post_page':
            if (!$volume->canEdit()) {
                Current_User::disallow();
            }
            $title = sprintf(dgettext('webpage', 'Administrate page: %s'), $volume->title);

            $result = $page->post();
            if (isset($_POST['page_version_id'])) {
                $version_id = (int)$_POST['page_version_id'];
            }

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                Webpage_Admin::sendMessage(dgettext('webpage', 'An error occurred while saving your page. Please check the error log.'),
                                           sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s&version_id=%s',
                                                   $page->page_number, $volume->id, $page->id, $version_id));
                
                break;
            } elseif (is_array($result)) {
                $title = sprintf(dgettext('webpage', 'Edit Page %s'),$page->page_number);
                $message = implode('<br />', $result);
                $content = Webpage_Forms::editPage($page, $version);
            } else {
                $result = $page->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    Webpage_Admin::sendMessage(dgettext('webpage', 'An error occurred while saving your page. Please check the error log.'),
                                               sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s&version_id=%s',
                                                       $page->page_number, $volume->id, $page->id, $version_id));
                }

                if ( isset($_POST['force_template']) ) {
                    $force_result = $volume->forceTemplate($page->template);

                    if (PEAR::isError($force_result)) {
                        PHPWS_Error::log($force_result);
                        Webpage_Admin::sendMessage(dgettext('webpage', 'Error: Unable to force template.'),
                                                   sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s&version_id=%s',
                                                           $page->page_number, $volume->id, $page->id, $version_id));
                    }
                }
                if (!$page->approved) {
                    $message = dgettext('webpage', 'Page held for approval.');
                } else {
                    $message = dgettext('webpage', 'Page saved successfully.');
                }
                Webpage_Admin::sendMessage($message,
                                           sprintf('edit_webpage&tab=page_%s&volume_id=%s&page_id=%s&version_id=%s',
                                                   $page->page_number, $volume->id, $page->id, $version_id));
            }
            break;

        case 'list':
            $title = dgettext('webpage', 'List Web Pages');
            $content = Webpage_Forms::wp_list();
            break;

        case 'move_to_frontpage':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            if (isset($_POST['webpage'])) {
                Webpage_Admin::setFrontPage($_POST['webpage'], 1);
            }
            Webpage_Admin::goBack();
            break;

        case 'move_off_frontpage':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            if (isset($_POST['webpage'])) {
                Webpage_Admin::setFrontPage($_POST['webpage'], 0);
            }
            Webpage_Admin::goBack();
            break;
            
            
        case 'delete_wp':
            if (!Current_User::allow('webpage', 'delete_page', $volume->id, 'volume', true)) {
                Current_User::disallow();
            }

            // deletes an entire volume, coming from list page
            if (!Current_User::authorized('webpage', 'delete_page')) {
                Current_User::disallow();
                return;
            }

            $result = Webpage_Admin::deleteWebpages();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = dgettext('webpage', 'Error');
                $content = dgettext('webpage', 'A problem occurred when trying to delete your webpage.');
            } else {
                Webpage_Admin::goBack();
            }

            break;

        case 'activate_vol':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $volume->active = 1;
            $volume->save();
            Webpage_Admin::goBack();
            break;

        case 'deactivate_vol':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $volume->active = 0;
            $volume->save();
            Webpage_Admin::goBack();
            break;

        case 'feature':
            if (!Current_User::allow('webpage', 'featured', null, null, true)) {
                Current_User::disallow();
            }

            if (isset($_POST['webpage'])) {
                Webpage_Admin::setFeatured($_POST['webpage'], 1);
            }
            Webpage_Admin::goBack();
            break;

        case 'activate':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            if (isset($_POST['webpage'])) {
                Webpage_Admin::setActive($_POST['webpage'], 1);
            }
            Webpage_Admin::goBack();
            break;

        case 'deactivate':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            if (isset($_POST['webpage'])) {
                Webpage_Admin::setActive($_POST['webpage'], 0);
            }
            Webpage_Admin::goBack();
            break;

        case 'restore_page':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $title = sprintf(dgettext('webpage', 'Restore Page %s'), $page->page_number);
            $content = Webpage_Admin::restorePage($volume, $page);
            break;

        case 'restore_volume':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $title = dgettext('webpage', 'Restore Web Page Header');
            $content = Webpage_Admin::restoreVolume($volume);
            break;

        case 'post_settings':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            Webpage_Admin::postSettings();
            $message = dgettext('webpage', 'Settings saved.');
        case 'settings':
            if (Current_User::isRestricted('webpage')) {
                Current_User::disallow();
            }

            $title = dgettext('webpage', 'Settings');
            $content = Webpage_Admin::settings();
            break;

        case 'drop_feature':
            if (!Current_User::allow('webpage', 'featured', null, null, true)) {
                Current_User::disallow();
            }

            if (!Current_User::authorized('webpage', 'featured')) {
                Current_User::disallow();
            }
            Webpage_Admin::dropFeature($volume);
            PHPWS_Core::home();
            break;

        case 'up_feature':
            if (!Current_User::allow('webpage', 'featured', null, null, true)) {
                Current_User::disallow();
            }

            if (!Current_User::authorized('webpage', 'featured')) {
                Current_User::disallow();
            }
            Webpage_Admin::moveFeature($volume, 'up');
            PHPWS_Core::home();
            break;

        case 'down_feature':
            if (!Current_User::allow('webpage', 'featured', null, null, true)) {
                Current_User::disallow();
            }

            if (!Current_User::authorized('webpage', 'featured')) {
                Current_User::disallow();
            }
            Webpage_Admin::moveFeature($volume, 'down');
            PHPWS_Core::home();
            break;

        default:
            PHPWS_Core::errorPage('404');
        }   // end web page admin cases

        // Sticks inside the panel
        switch ($command) {
        case 'new':
        case 'edit_webpage':
        case 'edit_page':
        case 'add_page':
        case 'post_page':
        case 'edit_header':
        case 'post_header':
        case 'restore_volume':
        case 'restore_page':
            $pagePanel->setContent($content);
            $content = $pagePanel->display();
        }


        $template = Webpage_Admin::template($title, $content, $message);

        $final = PHPWS_Template::process($template, 'webpage', 'main.tpl');
        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function restorePage(&$volume, &$page)
    {
        PHPWS_Core::initModClass('version', 'Restore.php');
        
        $restore = new Version_Restore('webpage', 'webpage_page', $page->id,
                                       'Webpage_Page', 'viewBasic');

        $vars['volume_id'] = $volume->id;
        $vars['page_id'] = $page->id;
        $vars['wp_admin'] = 'restore_page_version';
        $restore_link = PHPWS_Text::linkAddress('webpage', $vars, true);

        $vars['wp_admin'] = 'remove_page_restore';
        $remove_link = PHPWS_Text::linkAddress('webpage', $vars, true);

        $restore->setRestoreUrl($restore_link);
        $restore->setRemoveUrl($remove_link);
        
        return $restore->getList();
    }

    function restoreVolume(&$volume)
    {
        PHPWS_Core::initModClass('version', 'Restore.php');
        
        $restore = new Version_Restore('webpage', 'webpage_volume', $volume->id,
                                       'Webpage_Volume', 'approval_view');

        $vars['volume_id'] = $volume->id;
        $vars['wp_admin'] = 'restore_volume_version';
        $restore_link = PHPWS_Text::linkAddress('webpage', $vars, true);

        $vars['wp_admin'] = 'remove_volume_restore';
        $remove_link = PHPWS_Text::linkAddress('webpage', $vars, true);

        $restore->setRestoreUrl($restore_link);
        $restore->setRemoveUrl($remove_link);
        
        return $restore->getList();
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
        PHPWS_Core::initModClass('version', 'Version.php');
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link['link'] = 'index.php?module=webpage';

        $link['title'] = dgettext('webpage', 'New');
        $tabs['new'] = $link;

        $link['title'] = dgettext('webpage', 'List');
        $tabs['list'] = $link;

        $link['title'] = dgettext('webpage', 'Settings');
        $tabs['settings'] = $link;

        $version = new Version('webpage_volume');
        $unapproved = $version->countUnapproved();

        if (Current_User::isUnrestricted('webpage')) {
            $link['title'] = sprintf(dgettext('webpage', 'Approval(%s)'), $unapproved);
            $tabs['approve'] = $link;
        }

        $panel = new PHPWS_Panel('wp_main_panel');
        $panel->quickSetTabs($tabs);

        $panel->setModule('webpage');
        return $panel;
    }

    function setFrontPage($pages, $move_val)
    {
        if (!is_array($pages)) {
            return;
        }

        $db = new PHPWS_DB('webpage_volume');
        $db->addWhere('id', $pages);
        $db->addValue('frontpage', (int)$move_val);
        return $db->update();
    }

    function setActive($pages, $active)
    {
        foreach ($pages as $id) {
            $volume = new Webpage_Volume((int)$id);
            $volume->active = (bool)$active;
            $volume->save();
        }
    }

    function setFeatured($pages, $featured=1)
    {
        $db = new PHPWS_DB('webpage_featured');
        $db->addColumn('vol_order');
        $vol_order = $db->select('max');

        $db->reset();
        $db->addColumn('id');
        $all_cols = $db->select('col');

        if (PEAR::isError($all_cols)) {
            PHPWS_Error::log($all_cols);
            return;
        }

        foreach ($pages as $id) {
            if (in_array($id, $all_cols)) {
                continue;
            }
            $vol_order++;
            $db->reset();
            $db->addValue('id', $id);
            $db->addValue('vol_order', $vol_order);
            $result = $db->insert();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return;
            }
        }
    }

    function approvalView(&$volume, &$version)
    {
        $template['PAGE_TITLE'] = $volume->title;
        $template['SUMMARY']    = $volume->getSummary();
        $template['SUMMARY_LABEL'] = dgettext('webpage', 'Summary');

        if (!empty($volume->_pages)) {
            foreach ($volume->_pages as $page) {
                $subtpl = $page->getTplTags(FALSE, FALSE, $version->id);
                $subtpl['PAGE_NUMBER_LABEL'] = dgettext('webpage', 'Page');
                $template['multiple'][] = $subtpl;
            }
        } else {
            $template['PAGE_MESSAGE'] = dgettext('webpage', 'No pages have been created.');
        }

        $vars['wp_admin'] = 'approve';
        $options[] = PHPWS_Text::secureLink(dgettext('webpage', 'Approval list'), 'webpage', $vars);

        $vars['version_id'] = $version->id;
        $vars['volume_id']  = $volume->id;

        $vars['wp_admin']   = 'edit_webpage';
        $options[] = PHPWS_Text::secureLink(dgettext('webpage', 'Edit'), 'webpage', $vars);

        if (!$version->vr_approved && Current_User::isUnrestricted('webpage')) {
            $vars['wp_admin']   = 'approve_webpage';

            $options[] = PHPWS_Text::secureLink(dgettext('webpage', 'Approve'), 'webpage', $vars);

            $vars['wp_admin'] = 'disapprove_webpage';
            $options[] = PHPWS_Text::secureLink(dgettext('webpage', 'Disapprove'), 'webpage', $vars);
        }


        $template['LINKS'] = implode(' | ', $options);
        return PHPWS_Template::process($template, 'webpage', 'approval_view.tpl');
    }

    function deleteWebpages()
    {
        @$webpage = $_REQUEST['webpage'];

        if (empty($webpage) || !is_array($webpage)) {
            if (isset($_GET['volume_id'])) {
                $webpage[] = (int)$_GET['volume_id'];
            } else {
                return;
            }
        }

        foreach ($webpage as $wp) {
            $volume = new Webpage_Volume($wp);
            $result = $volume->delete();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return TRUE;
    }

    function disapproveWebpage()
    {
        $version = new Version('webpage_volume', $_GET['version_id']);
        if (PHPWS_Error::logIfError($version->delete())) {
            return false;
        }

        $db = new PHPWS_DB('webpage_page_version');
        $db->addWhere('volume_id', $version->source_id);
        $db->addWhere('approved', 0);
        return !PHPWS_Error::logIfError($db->delete());
    }

    function approveWebpage()
    {
        $version = new Version('webpage_volume', $_GET['version_id']);
        $volume = new Webpage_Volume;
        $version->loadObject($volume);

        $pages = new Version_Approval('webpage', 'webpage_page');
        $pages->addWhere('volume_id', $volume->id);
        $unapproved_pages = $pages->get(TRUE);

        if (!empty($unapproved_pages)) {
            foreach ($unapproved_pages as $pageVer) {
                $pageObj = new Webpage_Page;
                $pageVer->loadObject($pageObj);
                $pageObj->approved = 1;
                $result = $pageObj->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    return FALSE;
                }
                $pageVer->setSource($pageObj);
                $pageVer->setApproved(TRUE);
                $result = $pageVer->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    return FALSE;
                }
            }
        }

        $volume->approved = 1;

        // If this is a newly approved page, mark it so we can
        // authorize the creator
        $new_volume = $volume->key_id ? false : true;

        $result = $volume->save(true);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        $version->setSource($volume);
        $version->setApproved(TRUE);
        $result = $version->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        if ($new_volume) {
            $version->authorizeCreator($volume->_key);
        }
        return TRUE;
    }

    function postSettings()
    {
        if (isset($_POST['add_images'])) {
            PHPWS_Settings::set('webpage', 'add_images', 1);
        } else {
            PHPWS_Settings::set('webpage', 'add_images', 0);
        }

        PHPWS_Settings::save('webpage');
    }

    function settings()
    {
        $form = new PHPWS_Form('webpage_settings');
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_settings');

        $form->addCheckbox('add_images', 1);
        $form->setMatch('add_images', PHPWS_Settings::get('webpage', 'add_images'));
        $form->setLabel('add_images', dgettext('webpage', 'Simple image forms'));
        $form->addSubmit('save', dgettext('webpage', 'Save settings'));
        
        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'webpage', 'forms/settings.tpl');
    }

    function goBack()
    {
        PHPWS_Core::reroute('index.php?module=webpage&tab=list');
    }

    function dropFeature($volume)
    {
        if (!$volume->id) {
            return;
        }
        $db = new PHPWS_DB('webpage_featured');
        $db->addWhere('id', $volume->id);
        $db->addColumn('vol_order');
        $vol_order = $db->select('one');
        if (PEAR::isError($vol_order)) {
            PHPWS_Error::log($vol_order);
        } elseif (!$vol_order) {
            return;
        }
        $db->delete();
        $db->reset();
        $db->addWhere('vol_order', $vol_order, '>');
        $db->reduceColumn('vol_order');
    }
    
    function moveFeature($volume, $direction)
    {
        if (!$volume->id) {
            return;
        }
        $db = new PHPWS_DB('webpage_featured');
        $db->addWhere('id', $volume->id);
        $db->addColumn('vol_order');
        $vol_order = $db->select('one');
        $db->reset();
        $db->addColumn('vol_order', 'max');
        $max = $db->select('max');
        $db->reset();

        if ($direction == 'up') {
            if ($vol_order == 1) {
                $db->reduceColumn('vol_order');
                $db->addValue('vol_order', $max);
                $db->addWhere('id', $volume->id);
                $db->update();
            } else {
                $db->addWhere('vol_order', $vol_order - 1);
                $db->incrementColumn('vol_order');
                $db->reset();

                $db->addWhere('id', $volume->id);
                $db->reduceColumn('vol_order');
            }
        } else {
            if ($vol_order == $max) {
                $db->incrementColumn('vol_order');
                $db->addValue('vol_order', 1);
                $db->addWhere('id', $volume->id);
                $db->update();
            } else {
                $db->addWhere('vol_order', $vol_order + 1);
                $db->reduceColumn('vol_order');
                $db->reset();

                $db->addWhere('id', $volume->id);
                $db->incrementColumn('vol_order');
            }
        }
    }
}

?>