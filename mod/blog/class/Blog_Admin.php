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
            Current_User::disallow(dgettext('blog',
                            'User attempted access to Blog administration.'));
            return;
        }

        $title = $content = NULL;
        $message = Blog_Admin::getForward();

        $panel = Blog_Admin::cpanel();
        $panel->enableSecure();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['blog_id'])) {
            $blog = new Blog((int) $_REQUEST['blog_id']);
        } else {
            $blog = new Blog;
        }

        switch ($command) {
            case 'edit':
                $panel->setCurrentTab('list');
                if (!Current_User::isUser($blog->author_id) &&
                        !Current_User::authorized('blog', 'edit_blog',
                                $_REQUEST['blog_id'], 'entry')) {
                    Current_User::disallow(dgettext('blog',
                                    'User tried to edit a blog.'));
                    return;
                }

                $title = dgettext('blog', 'Update Blog Entry');
                $content = Blog_Form::edit($blog);
                break;

            case 'new':
                $title = dgettext('blog', 'New Blog Entry');
                $content = Blog_Form::edit($blog);
                break;

            case 'delete':
                //Blog_Admin::resetCache();
                $result = $blog->delete();
                Blog_Admin::setForward(dgettext('blog', 'Blog entry deleted.'),
                        'list');
                break;

            case 'list':
                $title = dgettext('blog', 'Blog Entries');
                $content = Blog_Admin::entry_list();
                break;

            case 'menu_submit_link':
                Menu::pinLink(dgettext('blog', 'Submit entry'),
                        'index.php?module=blog&action=user&action=submit');
                PHPWS_Core::reroute('index.php?module=blog&action=admin&tab=settings&authkey=' . Current_User::getAuthKey());
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

            case 'post_entry':
                $title = dgettext('blog', 'Blog Archive');
                $panel->setCurrentTab('list');
                $blog->post_entry();

                $link_back = PHPWS_Text::linkAddress('blog',
                                array('action' => 'admin', 'tab' => 'list'),
                                TRUE);

                if ($blog->_error) {
                    if (empty($blog->id)) {
                        $panel->setCurrentTab('new');
                    }
                    $content = Blog_Form::edit($blog);
                } else {
                    if (!isset($_POST['blog_id']) && PHPWS_Core::isPosted()) {
                        Blog_Admin::setForward(dgettext('blog',
                                        'Entry saved successfully.'), 'list');
                    }

                    $result = $blog->save();
                    //Blog_Admin::resetCache();

                    if (PHPWS_Error::isError($result)) {
                        $message = dgettext('blog',
                                'An error occurred when trying to save your entry. Please check your logs.');
                        PHPWS_Error::log($result);
                        Blog_Admin::setForward($message, 'list');
                    }

                    if (!$blog->approved) {
                        Blog_Admin::setForward(dgettext('blog',
                                        'Your entry is being held for approval.'),
                                'list');
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

            case 'purge_entries':
                if (Current_User::authorized('blog') && Current_User::isDeity()) {
                    Blog_Admin::purgeEntries($_GET['pd']);
                    $message = dgettext('blog', 'Blog entries purged.');
                }
                $content = Blog_Form::settings();
        }

        Layout::add(PHPWS_ControlPanel::display($panel->display($content,
                                $title, $message)));
    }

    public static function postSettings()
    {
        if (isset($_POST['show_recent'])) {
            PHPWS_Settings::set('blog', 'show_recent', $_POST['show_recent']);
        }

        isset($_POST['captcha_submissions']) ?
                        PHPWS_Settings::set('blog', 'captcha_submissions', 1) :
                        PHPWS_Settings::set('blog', 'captcha_submissions', 0);

        isset($_POST['home_page_display']) ?
                        PHPWS_Settings::set('blog', 'home_page_display', 1) :
                        PHPWS_Settings::set('blog', 'home_page_display', 0);

        isset($_POST['allow_anonymous_submits']) ?
                        PHPWS_Settings::set('blog', 'allow_anonymous_submits', 1) :
                        PHPWS_Settings::set('blog', 'allow_anonymous_submits', 0);

        isset($_POST['logged_users_only']) ?
                        PHPWS_Settings::set('blog', 'logged_users_only', 1) :
                        PHPWS_Settings::set('blog', 'logged_users_only', 0);

        isset($_POST['show_posted_date']) ?
                        PHPWS_Settings::set('blog', 'show_posted_date', 1) :
                        PHPWS_Settings::set('blog', 'show_posted_date', 0);

        isset($_POST['show_posted_by']) ?
                        PHPWS_Settings::set('blog', 'show_posted_by', 1) :
                        PHPWS_Settings::set('blog', 'show_posted_by', 0);


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

            if (!empty($_POST['max_width'])) {
                $max_width = (int) $_POST['max_width'];
                if ($max_width >= 50 && $max_width <= 2048) {
                    PHPWS_Settings::set('blog', 'max_width', $max_width);
                }
            }

            if (!empty($_POST['max_height'])) {
                $max_height = (int) $_POST['max_height'];
                if ($max_height >= 50 && $max_height <= 2048) {
                    PHPWS_Settings::set('blog', 'max_height', $max_height);
                }
            }
        } else {
            PHPWS_Settings::set('blog', 'simple_image', 0);
        }

        $past_limit = (int) $_POST['past_entries'];

        if ((int) $past_limit >= 0) {
            PHPWS_Settings::set('blog', 'past_entries', $past_limit);
        } else {
            PHPWS_Settings::reset('blog', 'past_entries');
        }

        $blog_limit = (int) $_POST['blog_limit'];
        if ((int) $blog_limit > 0) {
            PHPWS_Settings::set('blog', 'blog_limit', $blog_limit);
        } else {
            PHPWS_Settings::reset('blog', 'blog_limit');
        }

        PHPWS_Settings::set('blog', 'comment_script', $_POST['comment_script']);

        PHPWS_Settings::save('blog');
    }


    public static function setForward($message, $command)
    {
        $_SESSION['Blog_Forward'] = $message;
        $link = PHPWS_Text::linkAddress('blog',
                        array('action' => 'admin', 'command' => $command), TRUE);
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
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $listLink = 'index.php?module=blog&amp;action=admin';
        $listCommand = array('title' => dgettext('blog', 'List'), 'link' => $listLink);

        if (Current_User::allow('blog', 'edit_blog')) {
            $tabs['list'] = &$listCommand;
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
        $pageTags['ACTION'] = dgettext('blog', 'Action');
        $pageTags['ADD'] = PHPWS_Text::secureLink(t('Create new blog entry'),
                        'blog', array('action' => 'admin', 'command' => 'new'),
                        null, t('Create new blog entry'),
                        'btn btn-success pull-right');
        $pageTags['ADD_URI'] = PHPWS_Text::linkAddress('blog',
                        array('action' => 'admin', 'command' => 'new'), true);
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
        for ($i = 1; $i <= MAX_BLOG_CACHE_PAGES; $i++) {
            PHPWS_Cache::remove(BLOG_CACHE_KEY . $i);
        }
    }

    public function confirmPurge($purge_date)
    {
        $unix_purge_date = strtotime($purge_date);
        $purge_date = strftime('%c', $unix_purge_date);
        $tpl['CONFIRM'] = PHPWS_Text::secureLink(sprintf(dgettext('blog',
                                        'I am sure that I want to delete all blog entries prior to %s'),
                                $purge_date), 'blog',
                        array('action' => 'admin', 'command' => 'purge_entries', 'pd' => $unix_purge_date));
        $tpl['DENY'] = PHPWS_Text::secureLink(dgettext('blog',
                                'Nevermind, go back to settings'), 'blog',
                        array('action' => 'admin', 'command' => 'settings'));
        $tpl['INSTRUCTIONS'] = dgettext('blog',
                'You have chosen to purge old blog entries from your web site. Be aware they will be deleted permanently.');

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
