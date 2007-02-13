<?php
  /**
   * User functionality in Blog
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

define('BLOG_CACHE_KEY', 'front_blog_page');

class Blog_User {

    function main()
    {
        if (!isset($_REQUEST['blog_id']) && isset($_REQUEST['id'])) {
            $blog = new Blog((int)$_REQUEST['id']);
        } elseif (isset($_REQUEST['blog_id'])) {
            $blog = new Blog((int)$_REQUEST['blog_id']);
        } else {
            $blog = new Blog();
        }

        if (!isset($_REQUEST['action'])) {
            $action = 'view_comments';
        } else {
            $action = $_REQUEST['action'];
        }

        switch ($action) {
        case 'view_comments':
            Layout::addPageTitle($blog->title);
            $content = $blog->view(TRUE, FALSE);
            break;

        case 'view':
            $content = Blog_User::show();
            Layout::add($content, 'blog', 'view', TRUE);
            return;
            break;
            
        case 'submit':
            if (Current_User::allow('blog', 'edit_blog')) {
                PHPWS_Core::reroute(PHPWS_Text::linkAddress('blog', array('action'=>'admin', 'tab'=>'new'), 1));
            }
            // Must create a new blog. Don't use above shortcut
            $blog = new Blog;
            $content = Blog_User::submitAnonymous($blog);
            break;

        case 'post_suggestion':
            // Must create a new blog. Don't use above shortcut
            $blog = new Blog;
            $content = Blog_User::postSuggestion($blog);
            break;
            
        default:
            PHPWS_Core::errorPage(404);
            break;
        }

        Layout::add($content);
        translate();
    }


    function postSuggestion(&$blog)
    {
        translate('blog');
        if (PHPWS_Core::isPosted()) {
            $tpl['TITLE'] = _('Repeat submission');
            $tpl['CONTENT'] =  _('Your submission is still awaiting approval.');
            return PHPWS_Template::process($tpl, 'blog', 'user_main.tpl');
        }

        if (empty($_POST['title'])) {
            $blog->title = _('No title');
        } else {
            $blog->setTitle($_POST['title']);
        }

        if (!Current_User::isLogged() && !empty($_POST['author'])) {
            $blog->author = strip_tags($_POST['author']);
            $blog->author_id = 0;
        }

        // Do not let anonymous users use html tags
        $blog->setSummary(strip_tags($_POST['summary']));
        $blog->setEntry(strip_tags($_POST['entry']));

        $blog->approved = false;
        $result = $blog->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $tpl['TITLE'] = _('Sorry');
            $tpl['CONTENT'] =  _('A problem occured with your submission. Please try again later.');
        } else {
            $tpl['TITLE'] = _('Thank you');
            $tpl['CONTENT'] =  _('Your entry has been submitted for review.');
        }
        translate();
        return PHPWS_Template::process($tpl, 'blog', 'user_main.tpl');
    }


    function submitAnonymous(&$blog)
    {
        translate('blog');
        PHPWS_Core::initModClass('blog', 'Blog_Form.php');
        $tpl['TITLE'] = _('Submit Entry');
        $tpl['CONTENT'] = Blog_Form::edit($blog, null, true);
        translate();
        return PHPWS_Template::process($tpl, 'blog', 'user_main.tpl');
    }


    function getEntries(&$db, $limit, $offset)
    {
        $db->addWhere('approved', 1);
        $db->addWhere('publish_date', mktime(), '<');

        $db->setLimit($limit, $offset);
        $db->addOrder('create_date desc');
        Key::restrictView($db, 'blog');
        return $db->getObjects('Blog');
    }

    function show()
    {
        translate('blog');
        $key = BLOG_CACHE_KEY;

        if (!Current_User::isLogged()    &&
            !Current_User::allow('blog') &&
            PHPWS_Settings::get('blog', 'cache_view') &&
            $content = PHPWS_Cache::get($key)) {
            return $content;
        }

        $db = new PHPWS_DB('blog_entries');
        $limit = PHPWS_Settings::get('blog', 'blog_limit');
        $page = @$_GET['page'];

        if ($page) {
            $offset = ($page - 1) * $limit;
        } else  {
            $offset = 0;
        }

        if (empty($page) || !is_numeric($page)) {
            $page = 0;
        }

        $result = Blog_User::getEntries($db, $limit, $offset);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            if (Current_User::allow('blog')) {
                MiniAdmin::add('blog', PHPWS_Text::secureLink(_('Create first blog entry!'), 'blog', array('action'=>'admin', 'tab'=>'new')));
            }

            return NULL;
        }

        if ($page < 2) {
            $past_entries = PHPWS_Settings::get('blog', 'past_entries');

            if ($past_entries) {
                $db->setLimit($past_entries, $past_entries);
                $past = $db->getObjects('Blog');

                if (PEAR::isError($past)) {
                    PHPWS_Error::log($past);
                } elseif($past) {
                    Blog_User::showPast($past);
                }
            }
        }
    
        foreach ($result as $blog) {
            $view = $blog->view();
            if (!empty($view)) {
                $list[] = $view;
            }
        }
        $page_vars['action'] = 'view';
        if ($page > 1) {
            $page_vars['page'] = $page - 1;
            $tpl['PREV_PAGE'] = PHPWS_Text::moduleLink(_('Previous page'), 'blog', $page_vars);
            $page_vars['page'] = $page + 1;
            $tpl['NEXT_PAGE'] = PHPWS_Text::moduleLink(_('Next page'), 'blog', $page_vars);
        } else {
            $page_vars['page'] = 2;
            $tpl['NEXT_PAGE'] = PHPWS_Text::moduleLink(_('Next page'), 'blog', $page_vars);
        }

        $tpl['ENTRIES'] = implode('', $list);

        $content = PHPWS_Template::process($tpl, 'blog', 'list_view.tpl');

        if (!Current_User::isLogged() && !Current_User::allow('blog') &&
            PHPWS_Settings::get('blog', 'cache_view')) {
            PHPWS_Cache::save($key, $content);
        } elseif (Current_User::allow('blog', 'edit_blog')) {
            $vars['action'] = 'admin';
            $vars['tab'] = 'list';
            $link[] = PHPWS_Text::secureLink(_('Edit blogs'), 'blog', $vars);
            $vars['tab'] = 'new';
            $link[] = PHPWS_Text::secureLink(_('Add new blog'), 'blog', $vars);
            MiniAdmin::add('blog', $link);
        }
        translate();
        return $content;
    }

    /**
     * Works with show function
     * Displays entries outside the page limit
     */
    function showPast($entries)
    {
        translate('blog');
        if (empty($entries)) {
            return false;
        }
        foreach ($entries as $entry) {
            $tpl['entry'][] = array('TITLE' => sprintf('<a href="%s">%s</a>', $entry->getViewLink(true), $entry->title));
        }

        $tpl['PAST_TITLE'] = _('Previous blog entries');
        $content = PHPWS_Template::process($tpl, 'blog', 'past_view.tpl');
        Layout::add($content, 'blog', 'previous_entries');
        translate();
    }

    /**
     * Displays current blog entries to side box
     */
    function showSide()
    {
        translate('blog');
        $db = new PHPWS_DB('blog_entries');
        $limit = PHPWS_Settings::get('blog', 'blog_limit');
        $result = Blog_User::getEntries($db, $limit);

        if (!$result) {
            return false;
        }

        foreach ($result as $entry) {
            $tpl['entry'][] = array('TITLE' => sprintf('<a href="%s">%s</a>', $entry->getViewLink(true), $entry->title));
        }

        $tpl['RECENT_TITLE'] = sprintf('<a href="index.php?module=blog&amp;action=view">%s</a>', _('Recent blog entries'));
        $content = PHPWS_Template::process($tpl, 'blog', 'recent_view.tpl');
        Layout::add($content, 'blog', 'recent_entries');
        translate();
    }

}

?>