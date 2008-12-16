<?php
  /**
   * User functionality in Blog
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('BLOG_CACHE_KEY', 'front_blog_page');
if (!defined('MAX_BLOG_CACHE_PAGES')) {
    define('MAX_BLOG_CACHE_PAGES', 3);
 }


class Blog_User {

    public function miniAdminList()
    {
        $vars['action'] = 'admin';
        $vars['tab'] = 'list';
        MiniAdmin::add('blog', PHPWS_Text::secureLink(dgettext('blog', 'Blog list'), 'blog', $vars));
    }

    public function main()
    {
        if (isset($_REQUEST['blog_id'])) {
            $blog = new Blog((int)$_REQUEST['blog_id']);
        } elseif (isset($_REQUEST['id'])) {
            $blog = new Blog((int)$_REQUEST['id']);
        } else {
            $blog = new Blog();
        }

        if (!isset($_REQUEST['action'])) {
            if ($blog->id) {
                $action = 'view_comments';
            } else {
                $action = 'view';
            }
        } else {
            $action = $_REQUEST['action'];
        }

        switch ($action) {
        case 'view_comments':
            Layout::addStyle('blog');
            Layout::addPageTitle($blog->title);
            if (Current_User::allow('blog', 'edit_blog')) {
                Blog_User::miniAdminList();
            }
            if ($blog->publish_date > mktime() && !Current_User::allow('blog')) {
                PHPWS_Core::errorPage('404');
            } else {
                $content = $blog->view(true, false);
            }
            break;

        case 'view':
            if (@$_GET['y']) {
                $day   = 1;
                $month = 1;
                $year  = $_GET['y'];
                if (@$_GET['m']) {
                    $month = $_GET['m'];

                    if (@$_GET['d']) {
                        $day = $_GET['d'];
                        $start_date = mktime(0,0,0, $month, $day, $year);
                        $end_date   = mktime(23,59,59, $month, $day, $year);
                    } else {
                        $start_day = 1;
                        $end_day   = (int)date('t', mktime(0,0,0, $month, 1, $year));
                        $start_date = mktime(0,0,0, $month, 1, $year);
                        $end_date   = mktime(0,0,0, $month, $end_day, $year);
                    }
                } else {
                    $start_date = mktime(0,0,0, 1, 1, $year);
                    $end_date   = mktime(0,0,0, 12, 31, $year);
                }
            } else {
                $start_date = null;
                $end_date   = null;
            }

            $content = Blog_User::show($start_date, $end_date);
            Layout::add($content, 'blog', 'view', true);
            return;
            break;

        case 'submit':
            if (Current_User::allow('blog', 'edit_blog')) {
                PHPWS_Core::reroute(PHPWS_Text::linkAddress('blog', array('action'=>'admin', 'tab'=>'new'), 1));
            } elseif (PHPWS_Settings::get('blog', 'allow_anonymous_submits')) {
                // Must create a new blog. Don't use above shortcut
                $blog = new Blog;
                $content = Blog_User::submitAnonymous($blog);
            } else {
                $content = dgettext('blog', 'Site is not accepting anonymous submissions.');
            }
            break;

        case 'post_suggestion':
            // Must create a new blog. Don't use above shortcut
            $blog = new Blog;
            $content = Blog_User::postSuggestion($blog);
            if (empty($content)) {
                $content = Blog_User::submitAnonymous($blog);
            }
            break;

        default:
            PHPWS_Core::errorPage(404);
            break;
        }

        Layout::add($content);
    }


    public function postSuggestion(Blog $blog)
    {
        if (!PHPWS_Settings::get('blog', 'allow_anonymous_submits')) {
            return dgettext('blog', 'Site is not accepting anonymous submissions.');
        }


        if (empty($_POST['title'])) {
            $blog->title = dgettext('blog', 'No title');
        } else {
            $blog->setTitle($_POST['title']);
        }

        if (!Current_User::isLogged() && !empty($_POST['author'])) {
            $blog->author = strip_tags($_POST['author']);
            $blog->author_id = 0;
        }

        // Do not let anonymous users use html tags
        $summary = strip_tags($_POST['summary']);
        if (empty($summary)) {
            $blog->_error[] = dgettext('blog', 'Your submission must have a summary.');
        } else {
            $blog->setSummary($summary);
        }

        $blog->setEntry(strip_tags($_POST['entry']));

        $blog->approved = false;

        if (PHPWS_Settings::get('blog', 'captcha_submissions')) {
            PHPWS_Core::initCoreClass('Captcha.php');
            if (!Captcha::verify()) {
                $blog->_error[] = dgettext('blog', 'Please enter word in image correctly.');
            }
        }  elseif (PHPWS_Core::isPosted() && empty($blog->_error)) {
            $tpl['TITLE'] = dgettext('blog', 'Repeat submission');
            $tpl['CONTENT'] =  dgettext('blog', 'Your submission is still awaiting approval.');
            return PHPWS_Template::process($tpl, 'blog', 'user_main.tpl');
        }


        if ($blog->_error) {
            return null;
        }
        $result = $blog->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $tpl['TITLE'] = dgettext('blog', 'Sorry');
            $tpl['CONTENT'] =  dgettext('blog', 'A problem occured with your submission. Please try again later.');
        } else {
            $tpl['TITLE'] = dgettext('blog', 'Thank you');
            $tpl['CONTENT'] =  dgettext('blog', 'Your entry has been submitted for review.');
        }
        return PHPWS_Template::process($tpl, 'blog', 'user_main.tpl');
    }


    public function submitAnonymous(Blog $blog)
    {
        PHPWS_Core::initModClass('blog', 'Blog_Form.php');
        $tpl['TITLE'] = dgettext('blog', 'Submit Entry');
        $tpl['CONTENT'] = Blog_Form::edit($blog, null, true);
        return PHPWS_Template::process($tpl, 'blog', 'user_main.tpl');
    }

    public function totalEntries(PHPWS_DB $db)
    {
        $db->addColumn('id',null, null, true);
        return $db->select('one');
    }

    public function getEntries(PHPWS_DB $db, $limit, $offset=0)
    {
        $db->resetColumns();
        $db->setLimit($limit, $offset);
        $db->addOrder('sticky desc');
        $db->addOrder('publish_date desc');
        $db->loadClass('blog', 'Blog.php');
        return $db->getObjects('Blog');
    }

    public function allowView()
    {
        if (Current_User::allow('blog')) {
            return true;
        }

        // Only logged users may view and user is not logged in
        if (PHPWS_Settings::get('blog', 'logged_users_only') &&
            !Current_User::isLogged()) {
            return false;
        }

        $view_groups = PHPWS_Settings::get('blog', 'view_only');
        if (!empty($view_groups)) {
            $allowed_groups = explode(':', $view_groups);
        } else {
            $allowed_groups = null;
        }

        // Allowed groups is set, check the user
        if ($allowed_groups) {
            // User isn't even logged in. Don't show blog
            if (!Current_User::isLogged()) {
                return false;
            }

            // get logged user's groups
            $user_groups = Current_User::getGroups();
            // check intersection
            $intersect = array_intersect($user_groups, $allowed_groups);

            //no intersection found, deny
            if (empty($intersect)) {
                return false;
            }
        }
        return true;
    }

    public function show($start_date=null, $end_date=null)
    {
        if (!Blog_User::allowView()) {
            return null;
        }

        $db = new PHPWS_DB('blog_entries');

        if ($start_date) {
            $db->addWhere('publish_date', $start_date, '>=', 'and', 2);
        }

        if ($end_date) {
            $db->addWhere('publish_date', $end_date, '<=', 'and', 2);
        }

        $db->addWhere('approved', 1);
        $db->addWhere('publish_date', mktime(), '<');
        $db->addWhere('expire_date', mktime(), '>', 'and', 1);
        $db->addWhere('expire_date', 0, '=', 'or', 1);
        $db->setGroupConj(1, 'and');
        Key::restrictView($db, 'blog');

        $total_entries = Blog_User::totalEntries($db);

        $limit = PHPWS_Settings::get('blog', 'blog_limit');
        $page = @$_GET['page'];

        if (!is_numeric($page) || $page < 2) {
            $offset = $page = 0;
        } else {
            $offset = ($page - 1) * $limit;
        }

        if ($page == 0) {
            $cache_key = BLOG_CACHE_KEY . '1';
        } else {
            $cache_key = BLOG_CACHE_KEY . $page;
        }

        // we are only caching the first three pages
        if ($page <= MAX_BLOG_CACHE_PAGES &&
            !Current_User::isLogged() &&
            !Current_User::allow('blog') &&
            PHPWS_Settings::get('blog', 'cache_view') &&
            $content = PHPWS_Cache::get($cache_key)) {
            Layout::getCacheHeaders($cache_key);
            return $content;
        }

        Layout::addStyle('blog');
        $result = Blog_User::getEntries($db, $limit, $offset);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            if (Current_User::allow('blog')) {
                MiniAdmin::add('blog', PHPWS_Text::secureLink(dgettext('blog', 'Create first blog entry!'), 'blog', array('action'=>'admin', 'tab'=>'new')));
            }

            return NULL;
        }

        if ($page < 2) {
            $past_entries = PHPWS_Settings::get('blog', 'past_entries');

            if ($past_entries) {
                $db->setLimit($past_entries, $limit);
                $past = $db->getObjects('Blog');

                if (PEAR::isError($past)) {
                    PHPWS_Error::log($past);
                } elseif($past) {
                    Blog_User::showPast($past);
                }
            }
        }

        $rss = false;
        foreach ($result as $blog) {
            if (!$rss) {
                if (PHPWS_Core::moduleExists('rss')) {
                    PHPWS_Core::initModClass('rss', 'RSS.php');
                    $key = new Key($blog->key_id);
                    RSS::showIcon($key);
                    $rss = true;
                }
            }
            $view = $blog->view();
            if (!empty($view)) {
                $list[] = $view;
            }
        }

        $page_vars['action'] = 'view';
        if ($page > 1) {
            $page_vars['page'] = $page - 1;
            $tpl['PREV_PAGE'] = PHPWS_Text::moduleLink(dgettext('blog', 'Previous page'), 'blog', $page_vars);
            if ($limit + $offset < $total_entries) {
                $page_vars['page'] = $page + 1;
                $tpl['NEXT_PAGE'] = PHPWS_Text::moduleLink(dgettext('blog', 'Next page'), 'blog', $page_vars);
            }
        } elseif ($limit + $offset < $total_entries) {
            $page_vars['page'] = 2;
            $tpl['NEXT_PAGE'] = PHPWS_Text::moduleLink(dgettext('blog', 'Next page'), 'blog', $page_vars);
        }

        $tpl['ENTRIES'] = implode('', $list);

        $content = PHPWS_Template::process($tpl, 'blog', 'list_view.tpl');

        // again only caching first pages
        if ($page <= MAX_BLOG_CACHE_PAGES &&
            !Current_User::isLogged() && !Current_User::allow('blog') &&
            PHPWS_Settings::get('blog', 'cache_view')) {
            PHPWS_Cache::save($cache_key, $content);
            Layout::cacheHeaders($cache_key);
        } elseif (Current_User::allow('blog', 'edit_blog')) {
            Blog_User::miniAdminList();
            $vars['action'] = 'admin';
            $vars['tab'] = 'new';
            $link[] = PHPWS_Text::secureLink(dgettext('blog', 'Add new blog'), 'blog', $vars);
            MiniAdmin::add('blog', $link);
        }

        return $content;
    }

    /**
     * Works with show public function
     * Displays entries outside the page limit
     */
    public function showPast($entries)
    {
        if (empty($entries)) {
            return false;
        }
        foreach ($entries as $entry) {
            $tpl['entry'][] = array('TITLE' => sprintf('<a href="%s">%s</a>', $entry->getViewLink(true), $entry->title));
        }

        $tpl['PAST_TITLE'] = dgettext('blog', 'Previous blog entries');
        $content = PHPWS_Template::process($tpl, 'blog', 'past_view.tpl');
        Layout::add($content, 'blog', 'previous_entries');
    }

    /**
     * Displays current blog entries to side box
     */
    public function showSide()
    {
        switch(PHPWS_Settings::get('blog', 'show_recent')) {
        case 0:
            // don't show
            return;

        case 1:
            // home page only
            if (!PHPWS_Core::atHome()) {
                return;
            }
            break;

        case 2:
            // everywhere
            break;
        }

        $db = new PHPWS_DB('blog_entries');
        $db->addWhere('sticky', 0);
        $limit = PHPWS_Settings::get('blog', 'blog_limit');
        $result = Blog_User::getEntries($db, $limit);

        if (!$result) {
            return false;
        }

        foreach ($result as $entry) {
            $tpl['entry'][] = array('TITLE' => sprintf('<a href="%s">%s</a>', $entry->getViewLink(true), $entry->title));
        }

        $tpl['RECENT_TITLE'] = sprintf('<a href="index.php?module=blog&amp;action=view">%s</a>', dgettext('blog', 'Recent blog entries'));
        $content = PHPWS_Template::process($tpl, 'blog', 'recent_view.tpl');
        Layout::add($content, 'blog', 'recent_entries');
    }

}

?>