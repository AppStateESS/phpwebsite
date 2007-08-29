<?php
require_once PHPWS_SOURCE_DIR . '/lib/xml/XML-RPC/IXR_Library.inc.php';
//require_once '../..//lib/xml/XML-RPC/IXR_Library.inc.php';

/**
 * This is a XML-RPC Server.  
 *
 * This script allows offline blog editors to manipulate articles on the website.
 *
 * @version $Id: xmlrpc.php,v 1.7 2007/06/27 03:56:44 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module Article Manager
 */

define('XMLRPC_CANNOT_AUTHENTICATE', 'Unable to authenticate user.');
define('XMLRPC_BAD_RESULT', 'Unexpected results.');

class MyServer extends IXR_IntrospectionServer {
    var $validuser      = false;

    function MyServer() {
        $this->IXR_IntrospectionServer();
        /*  Don't want to support an API that's obsolete 2 times over, don't know if I have to.

      $this->addCallback(
          'blogger.newPost',
          'this:blogger_newPost',
          array('string'),
          'Creates a new post, and optionally publishes it.'
      );
      $this->addCallback(
          'blogger.editPost',
          'this:blogger_editPost',
          array('boolean'),
          'Updates the information about an existing post.'
      );
      $this->addCallback(
          'this:blogger.deletePost',
          'this:blogger_deletePost',
          array('boolean'),
          'Deletes a post.'
      );
      $this->addCallback(
          'blogger.getRecentPosts',
          'this:blogger_getRecentPosts',
          array('array'),
          'Returns a list of the most recent posts in the system.'
      );
      $this->addCallback(
          'blogger.getUserInfo',
          'this:blogger_getUserInfo',
          array('struct'),
          'Returns information about an author in the system'
      );
        */
        $this->addCallback(
                           'blogger.getUsersBlogs',
                           'this:blogger_getUsersBlogs',
                           array('array','string','string','string'),
                           'Returns a list of weblogs to which an author has posting privileges.'
                           );
        $this->addCallback(
                           'metaWeblog.newPost',
                           'this:metaWeblog_newPost',
                           array('string','string','string','string','struct','boolean'),
                           'Creates a new post, and optionally publishes it.'
                           );
        $this->addCallback(
                           'metaWeblog.editPost',
                           'this:metaWeblog_editPost',
                           array('boolean','string','string','string','struct','boolean'),
                           'Updates an existing post.'
                           );
        $this->addCallback(
                           'metaWeblog.getPost',
                           'this:metaWeblog_getPost',
                           array('struct','string','string','string'),
                           'Returns information about a specific post.'
                           );
        $this->addCallback(
                           'metaWeblog.getRecentPosts',
                           'this:metaWeblog_getRecentPosts',
                           array('array','string','string','string','int'),
                           'Returns a list of the most recent posts in the system.'
                           );
        $this->addCallback(
                           'metaWeblog.newMediaObject',
                           'this:metaWeblog_newMediaObject',
                           array('string','string','string','string','struct'),
                           'Uploads a file to your webserver.'
                           );
        $this->addCallback(
                           'metaWeblog.getCategories',
                           'this:metaWeblog_getCategories',
                           array('array','string','string','string'),
                           'Returns a list of all categories defined in the weblog.'
                           );
        $this->addCallback(
                           'mt.getRecentPostTitles',
                           'this:mt_getRecentPostTitles',
                           array('array','string','string','string','int'),
                           'Returns a bandwidth-friendly list of the most recent posts in the system.'
                           );
        $this->addCallback(
                           'mt.getCategoryList',
                           'this:mt_getCategoryList',
                           array('array','string','string','string'),
                           'Returns a list of all categories defined in the weblog.'
                           );
        $this->addCallback(
                           'mt.getPostCategories',
                           'this:mt_getPostCategories',
                           array('array','string','string','string'),
                           'Returns a list of all categories to which the post is assigned.'
                           );
        $this->addCallback(
                           'mt.setPostCategories',
                           'this:mt_setPostCategories',
                           array('boolean','string','string','string','array'),
                           'Sets the categories for a post.'
                           );
        $this->addCallback(
                           'mt.supportedMethods',
                           'this:mt_supportedMethods',
                           array('array'),
                           'Retrieve information about the XML-RPC methods supported by the server.'
                           );
        $this->addCallback(
                           'mt.supportedTextFilters',
                           'this:mt_supportedTextFilters',
                           array('array'),
                           'Retrieve information about the text formatting plugins supported by the server.'
                           );
        $this->addCallback(
                           'mt.getTrackbackPings',
                           'this:mt_getTrackbackPings',
                           array('array','string'),
                           ' Retrieve the list of TrackBack pings posted to a particular entry. This could be used to programmatically retrieve the list of pings for a particular entry, then iterate through each of those pings doing the same, until one has built up a graph of the web of entries referencing one another on a particular topic.'
                           );
        $this->addCallback(
                           'mt.publishPost',
                           'this:mt_publishPost',
                           array('boolean','string','string','string'),
                           'Publish (rebuild) all of the static files related to an entry from your weblog. Equivalent to saving an entry in the system (but without the ping).'
                           );

        $this->addCallback(
                           'test.isLoggedIn',
                           'this:isLoggedIn',
                           array('string','string','string'),
                           'Returns user status'
                           );
        $this->addCallback(
                           'test.getDate',
                           'this:getDate',
                           array('string'),
                           'Returns the current date'
                           );
        $this->addCallback(
                           'test.getTime',
                           'this:getTime',
                           array('string'),
                           'Returns the current time'
                           );
        $this->addCallback(
                           'test.helloWorld',
                           'this:helloWorld',
                           array('string'),
                           'Returns "Hello World"'
                           );
        $this->addCallback(
                           'test.error',
                           'this:ooh',
                           array('struct'),
                           'Triggers an error response'
                           );
        $this->addCallback(
                           'test.multiplied',
                           'this:times10',
                           array('int', 'struct'),
                           'Returns X*10, X*100, X*1000'
                           );
        $this->serve();
    }

    function _populate_article(&$article, $args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/populate_article.txt', $show);

    	extract($args);
    	/* Assign directly transferable data to class variables */
    	if(!empty($title))
            $article->title = trim($title);
    	if(!empty($dateCreated)) 
            $article->created_date = $dateCreated->getTimestamp();
    	/* If no summary is specified, the article body goes into the summary */
        if(!empty($mt_excerpt)) {
            $article->summary = trim($mt_excerpt);
            if(!empty($description)) 
                $article->pages[0]['section'][0]['text'] = trim($description);
        }
        else
            $article->summary = $description;
    	if(!empty($mt_allow_comments) 
           && (Current_User::allow('article', 'allow_comments')
               || ($GLOBALS['Article']['val']['users_allow_comments'] 
                   && Current_User::getId())))
            $article->allow_comments = $mt_allow_comments;
    	if(!empty($mt_keywords)) 
            $_POST['meta_keywords'] = $mt_keywords;
    	if(!empty($flNotOnHomePage)) 
            $article->announce = $flNotOnHomePage;
    	if(!empty($pubDate)) 
            $article->publication_date = $pubDate;

        /* Save final article structure into database */
        $result = $article->save();
        $article = null;
        return $result;
    }


    function _get_article($id) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/get_article.txt', $show);
        
        $obj = new Blog($id);
        if(!$obj->id)
            return false;
		
        $article = array();
        $article['userid'] = $obj->author_id;
        $article['dateCreated'] = new IXR_Date($obj->create_date);
        $article['pubDate'] = new IXR_Date($obj->publish_date);
        $article['postid'] = $id;
        //		$section = each($obj->order);
        $article['description'] = $obj->entry;
        if(empty($article['description']))
            $article['description'] = $obj->summary;
        else
            $article['mt_excerpt'] = $obj->summary;
        $article['title'] = $obj->title;
        $article['link'] = 'http://'. PHPWS_Core::getHomeHttp().'blog/' . $id;
        $article['permalink'] = 'http://'. PHPWS_Core::getHomeHttp().'blog/'. $id;
        //      $article['mt_text_more'] = '';
        $article['mt_allow_comments'] = $obj->allow_comments;
        $article['mt_allow_pings'] = 0;
        $article['mt_convert_breaks'] = 1;
        $result = Layout::getMetaPage($this->key_id);
        if (!$result) {
            $db = new PHPWS_DB('layout_config');
            $result = $db->select('row');
        }
        $article['mt_keywords'] = $result['meta_keywords'];
		
        /* Get category list */
        $article['categories'] = Categories::getCategories('list');
		
        return $article;
    }


    function _removeCategoryItems($key_id)
    {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/remove_cat_items.txt', $show);

        $db = new PHPWS_DB('category_items');
        $db->addWhere('key_id', (int)$key_id);
        return $db->delete();
    }

    function saveCategories($id, $categories, $api_type) {
        PHPWS_Core::initModClass('categories', 'Categories.php');
        PHPWS_Core::initModClass('categories', 'Action.php');
        $key_id = $this->getKeyId($id);

        if (empty($key_id) || empty($categories)) {
            return new IXR_Error(5020, 'Database Error! Data could not be found.');
        }

        $cat_list = array();
        /* Extract Category ids depending on the format */
        if ($api_type=='MT') {
            /* Movable Type */
            foreach($categories as $cat) {
                $cat_list[] = $cat['categoryId'];
            }
        } elseif ($api_type=='metaWeblog') {
            /* metaWeblog */
            $r_cats = array_flip(Categories::getCategories('list'));
            foreach($categories as $cat) {
                $cat_list[] = $r_cats[$cat];
            }
        }
        /* Erase old category listings */
        $this->_removeCategoryItems($key_id);

        /* Save category ids */
        foreach($cat_list as $cat_id) { 
            Categories_Action::addCategoryItem($cat_id, $key_id);
        }

        return true;
    }


    function blogger_getUsersBlogs($args) {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/blogged_getusersblogs.txt', $show);

        return array(array('url'=>PHPWS_Core::getHomeHttp(), 'blogid'=>'1', 'blogName'=>PHPWS_Core::getHomeHttp()));
    }


    function metaWeblog_newPost($args) {
        ob_start();
        var_dump($args);

        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/metaweblog_newpost.txt', $show);
        
        /* Login the user */
        $logged = $this->logUser($args[1], $args[2]);
        if ($logged !== true) {
            return $logged;
        }

        if (empty($args[3])) {
            return new IXR_Error(5010, 'Database Error!  Post not saved.');
        }

        $title       = @$args[3]['title'];
        $description = @$args[3]['description'];
        $publish     = (bool)$args[4];
        $read_more   = @$args[3]['mt_text_more'];
        $excerpt     = @$args[3]['mt_excerpt'];

        $id = $this->post($title, $description, $publish, $read_more, $excerpt);

        if (!is_numeric($id)) {
            return $id;
        }

        if(!empty($args[3]['categories'])) {
            $this->saveCategories($id, $args[3]['categories'], 'metaWeblog');
        }

        return $id;
    }


    function metaWeblog_editPost($args) {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/metaweblog_editpost.txt', $show);

        /* Login the user */
        $result = Current_User::loginUser($args[1], $args[2]);
        if($result!==true)
            return new IXR_Error(4000, $result);

        /* Retrieve the article */
        $article = new Blog($args[0]);
        if(!$article->id)
            return new IXR_Error(5020, 'Database Error! Article data could not be found.');
        /* Check Authorization to do edit it */
        if (!Current_User::allow('blog', 'edit_blog', $article->id))
            return new IXR_Error(4022, 'Authorization Error! You are not authorixed to edit this article!');
        /* Make sure it's not a multi-section article */

        /* Find & load the highest version */
        $version_list = PHPWS_Article::get_version_ids($args[0]); 
        $version_id = $version_list[0];
        $version = & new Version('blog_entries', $version_id);
        $version->loadObject($article);
        $article->unserialize_sections();
        $article->_version_id = $version->id;
        /* Instant save. No editing lock is needed on existing drafts */
        /* If this is a new draft version */
        if($version->isApproved()) 
            $article->version++; 

        /* Transfer all the given data */
        if($this->_populate_article($article, $args[3])) {
            if(!empty($args[3]['categories']))
                $this->_save_categories($article->title
					, $article->id, $article->key_id, $args[3]['categories']
					, $article->is_viewable(true), 'metaWeblog');
            return true;
        }
        else 
            return new IXR_Error(5010, 'Database Error!  Post not saved.');
    }

    function metaWeblog_getPost($args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/metaweblog_getpost.txt', $show);

        if(!$result = $this->_get_article($args[0]))
            return new IXR_Error(5020, 'Database Error! Article data could not be found.');
        elseif(count($result->order)>1)
            return new IXR_Error(5030, 'Usage Error! This is a multi-section article.  You\'ll have to edit it online.');
        else
            return $result;
    }

    function logUser($username, $password)
    {
        $result = Current_User::loginUser($username, $password);
        
        if (PHPWS_Error::logIfError($result) || !$result) {
            return new IXR_Error(4000, XMLRPC_CANNOT_AUTHENTICATE);
        }

        $result = $this->allow();
        if ($result === true) {
            $this->validUser = true;
            return true;
        } else {
            return $result;
        }
    }

    function dropUser()
    {
        PHPWS_Core::killSession('User');
    }


    function metaWeblog_getRecentPosts($args) {
        $logged = $this->logUser($args[1], $args[2]);
        if ($logged !== true) {
            return $logged;
        }
        
        if (is_numeric($args[3])) {
            $limit = (int)$args[3];
        }

        $result = $this->getRecent($limit);
        $this->dropUser();
        return $result;
    }

    function metaWeblog_newMediaObject($args) {
        return '';
    }

    function metaWeblog_getCategories($args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/metaweblog_getcategories.txt', $show);

        return array_values(Categories::getCategories('list'));
    }

    function mt_getRecentPostTitles($args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/mt_getrecentposttitles.txt', $show);

        $db = & new PHPWS_DB('mod_article');
        $db->addColumn('id');
        $db->addColumn('created_date');
        $db->addColumn('created_username');
        $db->addColumn('title');
    	$db->addWhere('sectioncount', '1');
        Key::restrictView($db, 'article');
        $db->addOrder('updated_date desc');
        $db->setLimit($args[3]);
        $id_array = $db->select('col');
        if (PEAR::isError($id_array)) 
            return new IXR_Error(5010, $result->getMessage());
        if (empty($id_array)) 
            return new IXR_Error(5010, 'No articles were found.');

        $list = array();
        foreach ($id_array AS $row);
        {
            $list[]['postid'] = $row['id'];
            $list[]['userid'] = $row['created_username'];
            $list[]['title'] = $row['title'];
            $list[]['dateCreated'] = new IXR_Date($row['created_date']);
        }
      	return $list;
    }

    function mt_getCategoryList($args) {
        $result = array();

        $list = Categories::getCategories('list');
        if (!empty($list)) {
            foreach($list as $key=>$value) {
                $struct = array();
                $struct['categoryId'] = $key;
                $struct['categoryName'] = $value;
                $result[] = $struct;
            }
        }

        return $result;
    }

    function mt_getPostCategories($args) {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_getpostcategories.txt', $show);

        $db = new PHPWS_DB('categories');
        $db->addColumn('id');
        $db->addColumn('title');
        $db->addWhere('mod_article.id', $args[0]);
        $db->addWhere('category_items.key_id', 'mod_article.key_id');
        $db->addWhere('categories.id', 'category_items.cat_id');
        $result = $db->select();
        $list = array();
        foreach($result as $row) {
            $struct = array();
            $struct['categoryName'] = $row['title'];
            $struct['categoryId'] = $row['id'];
            $struct['isPrimary'] = false;
            $list[] = $struct;
        }
        return $list;
    }

    function mt_setPostCategories($args) {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_setpostcategories.txt', $show);

        /* Login the user */
        $logged = $this->logUser($args[1], $args[2]);
        if ($logged !== true) {
            return $logged;
        }

        return $this->saveCategories($args[0], $args[3], 'MT');
    }

    function mt_supportedMethods($args) {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_supportedmethods.txt', $show);

        return $this->listMethods();
    }

    function mt_supportedTextFilters($args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_supportedtextfilters.txt', $show);

        return array();
    }

    function mt_getTrackbackPings($args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_gettrackbackpings.txt', $show);

        return array();
    }

    function mt_publishPost($args) {
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_publishpost.txt', $show);
        return 5;
    }


    function isLoggedIn($args) {
        ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        
        file_put_contents('files/xml/isloggedin.txt', $show);

        $result = Current_User::loginUser($args[0], $args[1]);
        if($result!==true)
            return new IXR_Error(4000, $result);

        if (!Current_User::getId())
            return 'Not a User! Username:'.trim($args[0]).' Password:'.$args[1];
        else
            return 'Username is "'.Current_User::getUsername().'"';
    }
    function getDate($args) {
       ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/getdate.txt', $show);

        return date('r');
    }
    function getTime($args) {
       ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/gettime.txt', $show);

        return date('H:i:s');
    }
    function helloWorld($args) {
       ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/helloworld.txt', $show);

        return 'Hello, World!';
    }
    function ooh($args) {
       ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/ooh.txt', $show);

        return new IXR_Error(4000, 'ha ha ha');
    }
    function times10($value) {
       ob_start();
        echo 'sd';
var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/times10.txt', $show);

        return array(
                     'times10' => (int)$value * 10,
                     'times100' => (int)$value * 10,
                     'times1000' => (int)$value * 10,
                     );
    }
}

?>