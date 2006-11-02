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
            $content = $blog->view(TRUE, FALSE);
            break;

        case 'view':
            $content = Blog_User::show();
            Layout::add($content, 'blog', 'view', TRUE);
            return;
            break;

        default:
            PHPWS_Core::errorPage(404);
            break;
        }

        Layout::add($content);
    }

    function getCurrentEntries(&$db, $limit)
    {
        $db->addWhere('approved', 1);
        $db->addWhere('publish_date', mktime(), '<');
        $db->setLimit($limit);
        $db->addOrder('create_date desc');

        Key::restrictView($db, 'blog');

        return $db->getObjects('Blog');
    }

    function show()
    {
        $key = BLOG_CACHE_KEY;

        if (!Current_User::isLogged()    &&
            !Current_User::allow('blog') &&
            $content = PHPWS_Cache::get($key)) {
            return $content;
        }

        $db = new PHPWS_DB('blog_entries');
        $limit = PHPWS_Settings::get('blog', 'blog_limit');
        $result = Blog_User::getCurrentEntries($db, $limit);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            return NULL;
        }

        $past_entries = PHPWS_Settings::get('blog', 'past_entries');
        
        if ($past_entries) {
            $db->setLimit($limit, $past_entries);
            $past = $db->getObjects('Blog');
            if (PEAR::isError($past)) {
                PHPWS_Error::log($past);
            } elseif($past) {
                Blog_User::showPast($past);
            }
        }
    
        foreach ($result as $blog) {
            $view = $blog->view();
            if (!empty($view)) {
                $list[] = $view;
            }
        }

        if (!Current_User::allow('blog')) {
            PHPWS_Cache::save($key, $content);
        } elseif (Current_User::allow('blog', 'edit_blog')) {
            $vars['action'] = 'admin';
            $vars['tab'] = 'list';
            $link[] = PHPWS_Text::secureLink(_('Edit blogs'), 'blog', $vars);
            $vars['tab'] = 'new';
            $link[] = PHPWS_Text::secureLink(_('Add new blog'), 'blog', $vars);
            MiniAdmin::add('blog', $link);
        }

        $tpl['ENTRIES'] = implode('', $list);
        
        return PHPWS_Template::process($tpl, 'blog', 'list_view.tpl');
    }

    /**
     * Works with show function
     * Displays entries outside the page limit
     */
    function showPast($entries)
    {
        if (empty($entries)) {
            return false;
        }
        foreach ($entries as $entry) {
            $tpl['entry'][] = array('TITLE' => sprintf('<a href="%s">%s</a>', $entry->getViewLink(true), $entry->title));
        }

        $tpl['PAST_TITLE'] = _('Previous blog entries');
        $content = PHPWS_Template::process($tpl, 'blog', 'past_view.tpl');
        Layout::add($content, 'blog', 'previous_entries');
    }

    /**
     * Displays current blog entries to side box
     */
    function showSide()
    {
        $db = new PHPWS_DB('blog_entries');
        $limit = PHPWS_Settings::get('blog', 'blog_limit');
        $result = Blog_User::getCurrentEntries($db, $limit);

        if (!$result) {
            return false;
        }

        foreach ($result as $entry) {
            $tpl['entry'][] = array('TITLE' => sprintf('<a href="%s">%s</a>', $entry->getViewLink(true), $entry->title));
        }

        $tpl['RECENT_TITLE'] = sprintf('<a href="index.php?module=blog&amp;action=view">%s</a>', _('Recent blog entries'));
        $content = PHPWS_Template::process($tpl, 'blog', 'recent_view.tpl');
        Layout::add($content, 'blog', 'recent_entries');
    }

}

?>