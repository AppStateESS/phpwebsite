<?php
  /**
   * User functionality in Blog
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Blog_User {

    function main()
    {
        if (!isset($_REQUEST['blog_id']) && isset($_REQUEST['id'])) {
            $blog = & new Blog((int)$_REQUEST['id']);
        } elseif (isset($_REQUEST['blog_id'])) {
            $blog = & new Blog((int)$_REQUEST['blog_id']);
        } else {
            $blog = & new Blog();
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

        case 'make_comment':
            $content = Blog_User::makeComment($blog);
            break;

        case 'save_comment':
            $content = Blog_User::postComment($blog);
            break;

        default:
            PHPWS_Core::errorPage(404);
            break;
        }

        Layout::add($content);
    }


    function makeComment(&$blog)
    {
        PHPWS_Core::initModClass('comments', 'Comments.php');

        $thread = $blog->makeThread();
        $thread->form();
    }

    function show(){
        $key = 'front blog page';

        if (!Current_User::isLogged()    &&
            !Current_User::allow('blog') &&
            $content = PHPWS_Cache::get($key)) {
            return $content;
        }

        $limit = 5;

        $db = & new PHPWS_DB('blog_entries');
        $db->setLimit($limit);
        $db->addOrder('date desc');

        Key::restrictView($db, 'blog');

        $result = $db->getObjects('Blog');

        if (empty($result)) {
            return ('No blog entries found.');
        }
    
        foreach ($result as $blog) {
            $view = $blog->view();
            if (!empty($view)) {
                $list[] = $view;
            }
        }

        $content = implode('', $list);
        if (!Current_User::allow('blog')) {
            PHPWS_Cache::save($key, $content);
        }

        return $content;
    }

}

?>