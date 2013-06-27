<?php
/**
 * Manages administrative functions including the creation and editing
 * of blog entries.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('blog', 'Blog_Form.php');
if (!defined('MAX_BLOG_CACHE_PAGES')) {
    define('MAX_BLOG_CACHE_PAGES', 3);
}

class Blog_Admin {

    public static function main()
    {
        if (!Current_User::authorized('blog')) {
            Current_User::disallow(dgettext('blog', 'User attempted access to Blog administration.'));
            return;
        }

        $title = $content = NULL;
        $message = Blog_Admin::getForward();

        $panel = Blog_Admin::cpanel();
        $panel->enableSecure();
        PHPWS_Core::initModClass('version', 'Version.php');

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['blog_id'])) {
            $blog = new Blog((int)$_REQUEST['blog_id']);
        } else {
            $blog = new Blog;
        }

        switch ($command){
            case 'edit':
                $panel->setCurrentTab('list');
                if ( !Current_User::isUser($blog->author_id) &&
                !Current_User::authorized('blog', 'edit_blog', $_REQUEST['blog_id'], 'entry')) {
                    Current_User::disallow(dgettext('blog', 'User tried to edit a blog.'));
                    return;
                }

                $title = dgettext('blog', 'Update Blog Entry');

                $version = new Version('blog_entries');
                $version->setSource($blog);
                $approval_id = $version->isWaitingApproval();

                if (isset($approval_id)) {
                    $version->setId($approval_id);
                    $version->init();

                    $unapproved_blog = new Blog;
                    $version->loadObject($unapproved_blog);

                    if (Current_User::isRestricted('blog')) {
                        $message = dgettext('blog', 'This version has not been approved.');
                        $content = Blog_Form::edit($unapproved_blog, $version->id);
                    } else {
                        $link = dgettext('blog', 'A version of this entry is awaiting approval.');
                        $linkVar['action']     = 'admin';
                        $linkVar['command']    = 'edit_unapproved';
                        $linkVar['version_id'] = $approval_id;
                        $message = PHPWS_Text::secureLink($link, 'blog', $linkVar);
                        $content = Blog_Form::edit($blog);
                    }

                } else {
                    $content = Blog_Form::edit($blog);
                }

                break;

            case 'approval':
                $title = dgettext('blog', 'Blog Entries Awaiting Approval');
                $approval = new Version_Approval('blog', 'blog_entries', 'blog', 'brief_view');

                $vars['action'] = 'admin';

                $vars['command'] = 'edit_unapproved';
                $approval->setEditUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

                $vars['command'] = 'view_version';
                $approval->setViewUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

                $vars['command'] = 'approve_item';
                $approval->setApproveUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

                $vars['command'] = 'disapprove_item';
                $approval->setDisapproveUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

                $content = $approval->getList();
                break;

            case 'disapprove_item':
                if (!Current_User::isUnrestricted('blog')) {
                    Current_User::disallow('Attempted to disapprove an entry as a restricted user.');
                    return;
                }
                $version = new Version('blog_entries', $_REQUEST['version_id']);
                $result = $version->delete();
                if (PHPWS_Error::isError($result)) {
                    PHPWS_Error::log($result);
                    Blog_Admin::setForward(dgettext('blog', 'A problem occurred when trying to disapprove this entry.'), 'approval');
                } else {
                    Blog_Admin::setForward(dgettext('blog', 'Blog entry disapproved.'), 'approval');
                }
                break;

            case 'approve_item':
                if (!Current_User::isUnrestricted('blog')) {
                    Current_User::disallow('Attempted to approve an entry as a restricted user.');
                    return;
                }

                $version = new Version('blog_entries', $_REQUEST['version_id']);
                $version->loadObject($blog);
                $blog->approved = 1;

                if (!$blog->author_id) {
                    // if author id is zero, then plug in the approver's information
                    $blog->author = Current_User::getDisplayName();
                    $blog->author_id = Current_User::getId();
                }

                $blog->save();
                $version->setSource($blog);
                $version->setApproved(TRUE);
                $result = $version->save();
                //Blog_Admin::resetCache();
                if (PHPWS_Error::isError($result)) {
                    PHPWS_Error::log($result);
                    Blog_Admin::setForward(dgettext('blog', 'An error occurred when saving your version.'), 'approval');
                } else {
                    $key = new Key($version->source_data['key_id']);
                    $version->authorizeCreator($key);
                    Blog_Admin::setForward(dgettext('blog', 'Blog entry approved.'), 'approval');
                }
                break;

            case 'edit_unapproved':
                if (!Current_User::authorized('blog', 'edit_blog')) {
                    Current_User::disallow(dgettext('blog', 'Tried to edit an unapproved item.'));
                    return;
                }

                $version = new Version('blog_entries', $_REQUEST['version_id']);
                $version->loadObject($blog);

                $title = dgettext('blog', 'Update Unapproved Blog Entry');
                $content = Blog_Form::edit($blog, $_REQUEST['version_id']);
                break;

            case 'new':
                $title = dgettext('blog', 'New Blog Entry');
                $content = Blog_Form::edit($blog);
                break;

            case 'delete':
                //Blog_Admin::resetCache();
                $result = $blog->delete();
                Blog_Admin::setForward(dgettext('blog', 'Blog entry deleted.'), 'list');
                break;

            case 'list':
                $title = dgettext('blog', 'Blog Entries');
                $content = Blog_Admin::entry_list();
                break;

            case 'menu_submit_link':
                Menu::pinLink(dgettext('blog', 'Submit entry'), 'index.php?module=blog&action=user&action=submit');
                PHPWS_Core::reroute('index.php?module=blog&action=admin&tab=settings&authkey=' . Current_User::getAuthKey());
                break;

            case 'restore':
                $title = dgettext('blog', 'Blog Restore') . ' : ' . $blog->title;
                $content = Blog_Admin::restoreVersionList($blog);
                break;

            case 'sticky':
                if (!Current_User::isUnrestricted('blog')) {
                    Current_User::disallow();
                }
                Blog_Admin::sticky($blog);
                PHPWS_Core::goBack();
                break;

            case 'unsticky':
                if (!Current_User::isUnrestricted('blog')) {
                    Current_User::disallow();
                }
                Blog_Admin::unsticky($blog);
                PHPWS_Core::goBack();
                break;

            case 'restorePrevBlog':
                if (Current_User::isRestricted('blog') || !Current_User::authorized('blog')) {
                    Current_User::disallow();
                    return;
                }
                //Blog_Admin::resetCache();
                Blog_Admin::restoreBlog($_REQUEST['version_id']);
                Blog_Admin::setForward(dgettext('blog', 'Blog entry restored.'), 'list');
                break;

            case 'removePrevBlog':
                if (!Current_User::isDeity()) {
                    Current_User::disallow();
                    return;
                }

                $blog_id = &$_REQUEST['blog_id'];

                Blog_Admin::removePrevBlog($_REQUEST['version_id']);
                Blog_Admin::setForward(dgettext('blog', 'Blog entry removed.'), 'restore&blog_id=' . $blog_id);
                break;

            case 'post_entry':
                $title = dgettext('blog', 'Blog Archive');
                $panel->setCurrentTab('list');
                $blog->post_entry();

                $link_back = PHPWS_Text::linkAddress('blog', array('action' => 'admin', 'tab'=>'list'), TRUE);

                if ($blog->_error) {
                    if (empty($blog->id)) {
                        $panel->setCurrentTab('new');
                    }
                    $content = Blog_Form::edit($blog);
                } else {
                    if (!isset($_POST['blog_id']) && PHPWS_Core::isPosted()) {
                        Blog_Admin::setForward(dgettext('blog', 'Entry saved successfully.'), 'list');
                    }

                    $result = $blog->save();
                    //Blog_Admin::resetCache();

                    if (PHPWS_Error::isError($result)) {
                        $message = dgettext('blog', 'An error occurred when trying to save your entry. Please check your logs.');
                        PHPWS_Error::log($result);
                        Blog_Admin::setForward($message, 'list');
                    }

                    if (!$blog->approved) {
                        Blog_Admin::setForward(dgettext('blog', 'Your entry is being held for approval.'), 'list');
                    } else {
                        PHPWS_Core::reroute($blog->getViewLink(true));
                    }
                }
                break;

            case 'reset_cache':
                Blog_Admin::resetCache();
                PHPWS_Core::goBack();
                break;

            case 'post_settings':
                if (!Current_User::authorized('blog', 'settings')) {
                    Current_User::disallow();
                    return;
                }

                if (Current_User::isDeity() && isset($_POST['purge_confirm'])) {
                    $title = dgettext('blog', 'Purge Blog Entries');
                    $content = Blog_Admin::confirmPurge($_POST['purge_date']);
                    break;
                }

                Blog_Admin::postSettings();
                $message = dgettext('blog', 'Blog settings saved.');
            case 'settings':
                if (!Current_User::allow('blog', 'settings')) {
                    Current_User::disallow();
                    return;
                }
                $panel->setCurrentTab('settings');
                $title = dgettext('blog', 'Blog Settings');
                $content = Blog_Form::settings();
                break;

            case 'view_version':
                $title = dgettext('blog', 'View version');
                $content = Blog_Admin::viewVersion($_REQUEST['version_id']);
                break;

            case 'purge_entries':
                if (Current_User::authorized('blog') && Current_User::isDeity()) {
                    Blog_Admin::purgeEntries($_GET['pd']);
                    $message = dgettext('blog', 'Blog entries purged.');
                }
                $content = Blog_Form::settings();
        }

        Layout::add(PHPWS_ControlPanel::display($panel->display($content, $title, $message)));
    }


    public static function postSettings()
    {
        if (isset($_POST['show_recent'])) {
            PHPWS_Settings::set('blog', 'show_recent', $_POST['show_recent']);
        }

        isset($_POST['allow_comments']) ?
        PHPWS_Settings::set('blog', 'allow_comments', 1) :
        PHPWS_Settings::set('blog', 'allow_comments', 0);

        isset($_POST['anonymous_comments']) ?
        PHPWS_Settings::set('blog', 'anonymous_comments', 1) :
        PHPWS_Settings::set('blog', 'anonymous_comments', 0);

        /*
         isset($_POST['cache_view']) ?
         PHPWS_Settings::set('blog', 'cache_view', 1) :
         PHPWS_Settings::set('blog', 'cache_view', 0);
         */

        isset($_POST['captcha_submissions']) ?
        PHPWS_Settings::set('blog', 'captcha_submissions', 1) :
        PHPWS_Settings::set('blog', 'captcha_submissions', 0);

        isset($_POST['home_page_display']) ?
        PHPWS_Settings::set('blog', 'home_page_display', 1) :
        PHPWS_Settings::set('blog', 'home_page_display', 0);

        isset($_POST['allow_anonymous_submits']) ?
        PHPWS_Settings::set('blog', 'allow_anonymous_submits', 1) :
        PHPWS_Settings::set('blog', 'allow_anonymous_submits', 0);

        isset($_POST['show_category_icons']) ?
        PHPWS_Settings::set('blog', 'show_category_icons', 1) :
        PHPWS_Settings::set('blog', 'show_category_icons', 0);

        isset($_POST['show_category_links']) ?
        PHPWS_Settings::set('blog', 'show_category_links', 1) :
        PHPWS_Settings::set('blog', 'show_category_links', 0);

        isset($_POST['single_cat_icon']) ?
        PHPWS_Settings::set('blog', 'single_cat_icon', 1) :
        PHPWS_Settings::set('blog', 'single_cat_icon', 0);

        isset($_POST['logged_users_only']) ?
        PHPWS_Settings::set('blog', 'logged_users_only', 1) :
        PHPWS_Settings::set('blog', 'logged_users_only', 0);


        if (isset($_POST['view_only']) && is_array($_POST['view_only'])) {
            $view_only = implode(':', $_POST['view_only']);
        } else {
            $view_only = null;
        }

        PHPWS_Settings::set('blog', 'view_only', $view_only);

        if (isset($_POST['simple_image'])) {
            PHPWS_Settings::set('blog', 'simple_image', 1);
            isset($_POST['mod_folders_only']) ?
            PHPWS_Settings::set('blog', 'mod_folders_only', 1) :
            PHPWS_Settings::set('blog', 'mod_folders_only', 0);

            if ( !empty($_POST['max_width']) ) {
                $max_width = (int)$_POST['max_width'];
                if ($max_width >= 50 && $max_width <= 2048 ) {
                    PHPWS_Settings::set('blog', 'max_width', $max_width);
                }
            }

            if ( !empty($_POST['max_height']) ) {
                $max_height = (int)$_POST['max_height'];
                if ($max_height >= 50 && $max_height <= 2048 ) {
                    PHPWS_Settings::set('blog', 'max_height', $max_height);
                }
            }
        } else {
            PHPWS_Settings::set('blog', 'simple_image', 0);
        }

        $past_limit = (int)$_POST['past_entries'];

        if ((int)$past_limit >= 0) {
            PHPWS_Settings::set('blog', 'past_entries', $past_limit);
        } else {
            PHPWS_Settings::reset('blog', 'past_entries');
        }

        $blog_limit = (int)$_POST['blog_limit'];
        if ((int)$blog_limit > 0) {
            PHPWS_Settings::set('blog', 'blog_limit', $blog_limit);
        } else {
            PHPWS_Settings::reset('blog', 'blog_limit');
        }

        PHPWS_Settings::save('blog');
    }


    public function viewVersion()
    {
        $version = new Version('blog_entries', (int)$_REQUEST['version_id']);
        $blog = new Blog;
        $version->loadObject($blog);

        $vars['action'] = 'admin';
        $vars['version_id'] = $version->id;
        $vars['command'] = 'edit_unapproved';

        $options[] = PHPWS_Text::secureLink(dgettext('blog', 'Edit'), 'blog', $vars);

        if (!$version->vr_approved && Current_User::isUnrestricted('blog')) {
            $vars['command'] = 'approve_item';
            $options[] = PHPWS_Text::secureLink(dgettext('blog', 'Approve'), 'blog', $vars);

            $vars['command'] = 'disapprove_item';
            $options[] = PHPWS_Text::secureLink(dgettext('blog', 'Disapprove'), 'blog', $vars);
        }

        $vars['command'] = 'approval';
        $options[] = PHPWS_Text::secureLink(dgettext('blog', 'Approval list'), 'blog', $vars);

        $template['OPTIONS'] = implode(' | ', $options);
        $template['VIEW'] = $blog->brief_view();
        return PHPWS_Template::process($template, 'blog', 'version_view.tpl');
    }

    public function setForward($message, $command)
    {
        $_SESSION['Blog_Forward'] = $message;
        $link = PHPWS_Text::linkAddress('blog', array('action'=>'admin', 'command' => $command), TRUE);
        PHPWS_Core::reroute($link);
    }

    public static function getForward()
    {
        if (!isset($_SESSION['Blog_Forward'])) {
            return NULL;
        }

        $message = $_SESSION['Blog_Forward'];
        unset($_SESSION['Blog_Forward']);
        return $message;
    }

    public static function cpanel()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $listLink = 'index.php?module=blog&amp;action=admin';
        $listCommand = array ('title'=>dgettext('blog', 'List'), 'link'=> $listLink);

        /*
        if (Current_User::isUnrestricted('blog')) {
            $version = new Version('blog_entries');
            $unapproved = $version->countUnapproved();

            if (PHPWS_Error::isError($unapproved)) {
                PHPWS_Error::log($unapproved);
                $unapproved = '??';
            }
            $approvalLink = 'index.php?module=blog&amp;action=admin';
            $approvalCommand = array ('title'=>sprintf(dgettext('blog', 'Approval (%s)'), $unapproved), 'link'=> $approvalLink);
        }
        */

        if (Current_User::allow('blog', 'edit_blog')) {
            $tabs['list'] = &$listCommand;
            //$tabs['approval'] = &$approvalCommand;
        }

        if (Current_User::allow('blog', 'settings')) {
            $tabs['settings'] = array('title' => dgettext('blog', 'Settings'),
                                      'link' => 'index.php?module=blog&amp;action=admin');
        }

        $panel = new PHPWS_Panel('blog');
        $panel->quickSetTabs($tabs);
        return $panel;
    }


    public static function entry_list()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $db = new PHPWS_DB('blog_stickies');
        $db->addColumn('blog_id');
        $GLOBALS['blog_stickies'] = $db->select('col');

        $pageTags['SUMMARY'] = dgettext('blog', 'Summary');
        $pageTags['ACTION']  = dgettext('blog', 'Action');
        $pageTags['ADD'] = PHPWS_Text::secureLink(t('Create new blog entry'), 'blog', array('action'=>'admin', 'command'=>'new'), null, t('Create new blog entry'), 'btn btn-success pull-right');
        $pageTags['ADD_URI'] = PHPWS_Text::linkAddress('blog', array('action'=>'admin', 'command'=>'new'), true);
        $pageTags['ADD_TEXT'] = t('Create new blog entry');

        $pager = new DBPager('blog_entries', 'Blog');
        $pager->addSortHeader('title', dgettext('blog', 'Title'));
        $pager->addSortHeader('create_date', dgettext('blog', 'Creation'));
        $pager->addSortHeader('publish_date', dgettext('blog', 'Publish'));
        $pager->addSortHeader('expire_date', dgettext('blog', 'Expire'));

        $pager->setModule('blog');
        $pager->setTemplate('list.tpl');
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);
        $pager->setSearch('title');
        $pager->setDefaultOrder('create_date', 'desc');
        $pager->cacheQueries();
        $pager->setReportRow('report_rows');
        $content = $pager->get();
        return $content;
    }

    public function _loadCategory(&$cat_item, Blog $blog, $version=NULL)
    {
        $cat_item->setItemId($blog->id);
        $cat_item->setTitle($blog->getTitle() . ' - ' . $blog->getFormatedDate());
        if (MOD_REWRITE_ENABLED) {
            $link = 'blog/view/' . $blog->id;
        } else {
            $link = 'index.php?module=blog&amp;action=view&amp;id=' . $blog->id;
        }

        $cat_item->setLink($link);

        if (isset($version)) {
            $cat_item->setVersionId($version->id);
        }
    }

    public static function restoreVersionList(&$blog)
    {
        PHPWS_Core::initModClass('version', 'Restore.php');
        $vars['action'] = 'admin';
        $vars['command'] = 'restorePrevBlog';
        $vars['blog_id'] = $blog->id;
        $restore_link = PHPWS_Text::linkAddress('blog', $vars, TRUE);

        $vars['command'] = 'removePrevBlog';
        $remove_link = PHPWS_Text::linkAddress('blog', $vars, TRUE);

        $restore = new Version_Restore('blog', 'blog_entries', $blog->id, 'blog', 'brief_view');
        $restore->setRestoreUrl($restore_link);
        $restore->setRemoveUrl($remove_link);
        $result = $restore->getList();
        return $result;
    }

    public static function restoreBlog($version_id)
    {
        $version = new Version('blog_entries', $version_id);
        $version->restore();
    }

    public static function removePrevBlog($version_id)
    {
        $version = new Version('blog_entries', $version_id);
        $version->delete();
    }

    public static function sticky($blog)
    {
        $db = new PHPWS_DB('blog_entries');
        $db->addWhere('sticky', 0, '>');
        $db->addWhere('id', $blog->id, '!=');
        $db->addOrder('sticky');
        $db->addColumn('id');
        $db->addColumn('sticky');
        $result = $db->select();

        $count = 1;
        if (!empty($result)) {
            foreach ($result as $bg) {
                $db->reset();
                $db->addWhere('id', $bg['id']);
                $db->addValue('sticky', $count);
                $db->update();
                $count++;
            }
        }
        $db->reset();
        $db->addWhere('id', $blog->id);
        $db->addValue('sticky', $count);
        $db->update();
    }

    public static function unsticky($blog)
    {
        $blog->sticky = 0;
        $blog->save();

        $db = new PHPWS_DB('blog_entries');
        $db->addWhere('sticky', 0, '>');
        $db->addWhere('id', $blog->id, '!=');
        $db->addOrder('sticky');
        $db->addColumn('id');
        $db->addColumn('sticky');
        $result = $db->select();
        $count = 1;
        if (!empty($result)) {
            foreach ($result as $bg) {
                $db->reset();
                $db->addWhere('id', $bg['id']);
                $db->addValue('sticky', $count);
                $db->update();
                $count++;
            }
        }
    }

    /**
     * Clears Blog's anonymous cache
     */
    public function resetCache()
    {
        for ($i=1; $i <= MAX_BLOG_CACHE_PAGES; $i++) {
            PHPWS_Cache::remove(BLOG_CACHE_KEY . $i);
        }
    }

    public function confirmPurge($purge_date)
    {
        $unix_purge_date = strtotime($purge_date);
        $purge_date = strftime('%c', $unix_purge_date);
        $tpl['CONFIRM'] = PHPWS_Text::secureLink(sprintf(dgettext('blog', 'I am sure that I want to delete all blog entries prior to %s'),
        $purge_date),
                                                 'blog', array('action'=>'admin', 'command'=>'purge_entries', 'pd'=>$unix_purge_date));
        $tpl['DENY'] = PHPWS_Text::secureLink(dgettext('blog', 'Nevermind, go back to settings'),
                                              'blog', array('action'=>'admin', 'command'=>'settings'));
        $tpl['INSTRUCTIONS'] = dgettext('blog', 'You have chosen to purge old blog entries from your web site. Be aware they will be deleted permanently.');

        return PHPWS_Template::process($tpl, 'blog', 'purge_confirm.tpl');
    }

    public function purgeEntries($date)
    {
        PHPWS_Core::initModClass('blog', 'Blog.php');
        if (empty($date)) {
            return;
        }

        $db = new PHPWS_DB('blog_entries');
        $db->addWhere('create_date', $date, '<');
        $entries = $db->getObjects('Blog');

        if (empty($entries) || PHPWS_Error::logIfError($entries)) {
            return;
        }

        foreach ($entries as $blog) {
            PHPWS_Error::logIfError($blog->delete());
        }

    }
}

?>