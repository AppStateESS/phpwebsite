<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initCoreClass('xmlrpc.php');

class Blog_XML extends MyServer {
    var $image_directory = 'images/blog/';

    function Blog_XML()
    {
        $this->MyServer();
    }

    function delete($id)
    {
        $blog = new Blog($id);
        if ($blog->delete()) {
            PHPWS_Cache::clearCache();
            return true;
        } else {
            return new IXR_Error(4040, 'Unable to delete entry.');
        }
    }

    function allow($permission)
    {
        if (Current_User::isRestricted('blog')) {
            return new IXR_Error(4010, 'You do not have permission to access Blog.');
        }

        switch ($permission) {
        case 'new':
        case 'list':
        case 'edit':
        case 'category':
        case 'media':
            if (!Current_User::allow('blog', 'edit_blog')) {
                return new IXR_Error(4020, 'You do not have permission to edit entries.');
            }
            break;

        case 'delete':
            if (!Current_User::allow('blog', 'delete_blog')) {
                return new IXR_Error(4030, 'You do not have permission to delete entries.');
            }
            break;

        default:
            return false;
        }

        return true;
    }

    function getRecent($limit)
    {
        $db = new PHPWS_DB('blog_entries');
        $db->setLimit($limit);
        $db->addOrder('publish_date desc');
        Key::restrictEdit($db, 'blog', 'edit_blog');
        $result = $db->getObjects('Blog');

        if (PHPWS_Error::logIfError($result)) {
            return new IXR_Error(4000, XMLRPC_BAD_RESULT);
        }

        if (empty($result)) {
            return new IXR_Error(5010, 'No blog entries found.');
        }

        foreach ($result as $blog) {
            $blogs[] = $this->getRPC($blog);
        }
        return $blogs;
    }

    function getPost($id)
    {
        $blog = new Blog($id);
        if (!$blog->id) {
            return new IXR_Error(22, "Blog not found");
        } else {
            return $this->getRPC($blog);
        }
    }

    function post($id, $details, $publish)
    {
        // Blog doesn't use excerpt
        extract($details);

        if (!Current_User::allow('blog', 'edit_blog') || Current_User::isRestricted('blog')) {
            return new IXR_Error(4000, XMLRPC_CANNOT_AUTHENTICATE);
        }

        if ($id) {
            $blog = new Blog($id);
            if (!$blog->id) {
                return new IXR_Error(5010, 'Database Error!  Post not saved.');
            }
        } else {
            $blog = new Blog;
        }

        if (empty($title)) {
            return new IXR_Error(4010, 'Missing title.');
        }

        if (empty($description)) {
            return new IXR_Error(4010, 'Missing summary.');
        }

        $blog->setTitle($title);
        $blog->setSummary($description);

        if (!empty($mt_text_more)) {
            $blog->setEntry($mt_text_more);
        }

        if (isset($mt_allow_comments)) {
            $blog->allow_comments = (bool)$mt_allow_comments;
        } else {
            $blog->allow_comments = PHPWS_Settings::get('blog', 'allow_comments');
        }

        if (PHPWS_Settings::get('blog', 'obey_publish')) {
            $blog->approved = $publish;
        } else {
            $blog->approved = 1;
        }
        
        $result = $blog->save();

        if (PHPWS_Error::logIfError($result)) {
            return new IXR_Error(5010, 'Database Error!  Post not saved.');
        } else {
            PHPWS_Cache::clearCache();
            return $blog->id;
        }
    }

    function getRPC($blog)
    {
        $d = array();
        $d['userid']       = $blog->author_id;
        $d['dateCreated']  = new IXR_Date($blog->create_date);
        $d['pubDate']      = new IXR_Date($blog->publish_date);
        $d['postid']       = $blog->id;
        $d['description']  = $this->appendImages($blog->getSummary(true));
        $d['mt_text_more'] = $this->appendImages($blog->getEntry());
        $d['title'] = $blog->title;

        if (MOD_REWRITE_ENABLED) {
            $d['link'] = PHPWS_Core::getHomeHttp() . 'blog/' . $blog->id;
        } else {
            $d['link'] = PHPWS_Core::getHomeHttp() . 'index.php?module=blog&action=view_comments&id=' . $blog->id;
        }
        $d['permalink'] = PHPWS_Core::getHomeHttp() . 'index.php?module=blog&action=view_comments&id=' . $blog->id;

        $d['mt_allow_comments'] = $blog->allow_comments;
        $d['mt_allow_pings'] = 0;
        $d['mt_convert_breaks'] = 0;
        $result = Layout::getMetaPage($blog->key_id);

        if ($result) {
            $d['mt_keywords'] = $result['meta_keywords'];
        } else {
            $d['mt_keywords'] = '';
        }
		
        /* Get category list */
        //$d['categories'] = Categories::getCategories('list');
        return $d;
    }

    function getKeyId($id)
    {
        $blog = new Blog($id);
        if (!$blog->id) {
            return null;
        }
        return $blog->key_id;
    }

}

?>