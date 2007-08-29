<?php

PHPWS_Core::initCoreClass('xmlrpc.php');
PHPWS_Core::requireConfig('blog');

class Blog_XML extends MyServer {

    function Blog_XML()
    {
        $this->MyServer();
    }

    function allow()
    {
        if (!Current_User::allow('blog', 'edit_blog') || Current_User::isRestricted('blog')) {
            return new IXR_Error(4000, XMLRPC_CANNOT_AUTHENTICATE);
        } else {
            return true;
        }
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

    function post($title, $description, $publish, $read_more, $excerpt)
    {
        // Blog doesn't use excerpt

        if (!Current_User::allow('blog', 'edit_blog') || Current_User::isRestricted('blog')) {
            return new IXR_Error(4000, XMLRPC_CANNOT_AUTHENTICATE);
        }

        $blog = new Blog;
        if (empty($title)) {
            return new IXR_Error(4010, 'Missing title.');
        }

        if (empty($description)) {
            return new IXR_Error(4010, 'Missing summary.');
        }

        $blog->setTitle($title);
        $blog->setSummary($description);

        if (!empty($more_text)) {
            $blog->setEntry($more_text);
        }
        
        $blog->allow_comments = PHPWS_Settings::get('blog', 'allow_comments');
        $blog->approved = 1;
        $result = $blog->save();

        if (PHPWS_Error::logIfError($result)) {
            return new IXR_Error(5010, 'Database Error!  Post not saved.');
        } else {
            return $blog->id;
        }
    }

    function getRPC($blog)
    {
        $d = array();
        $d['userid']      = $blog->author_id;
        $d['dateCreated'] = new IXR_Date($blog->create_date);
        $d['pubDate']     = new IXR_Date($blog->publish_date);
        $d['postid']      = $blog->id;
        $d['description'] = $blog->getEntry(true);

        if (empty($d['description'])) {
            $d['description'] = $blog->getSummary(true);
        } else {
            $d['mt_excerpt'] = $blog->getSummary(true);
        }
        $d['title'] = $blog->title;

        $d['link'] = 'http://' . PHPWS_Core::getHomeHttp() . 'blog/' . $id;
        $d['permalink'] = 'http://' . PHPWS_Core::getHomeHttp() . 'index.php?module=blog&action=view_comments&id=' . $blog->id;

        $d['mt_allow_comments'] = $blog->allow_comments;
        $d['mt_allow_pings'] = 0;
        $d['mt_convert_breaks'] = 0;
        $result = Layout::getMetaPage($this->key_id);

        if (!$result) {
            $db = new PHPWS_DB('layout_config');
            $result = $db->select('row');
        }

        $d['mt_keywords'] = $result['meta_keywords'];
		
        /* Get category list */
        $d['categories'] = Categories::getCategories('list');
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