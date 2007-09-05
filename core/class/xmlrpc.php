<?php
require_once PHPWS_SOURCE_DIR . '/lib/xml/XML-RPC/IXR_Library.inc.php';

/**
 * This is a XML-RPC Server.  
 *
 * Extended by another class to allow XML-RPC functionality.
 * Originally written by Eloi George for the Article Manager.
 * Docs coming. See blog's Blog_XML.php for examples
 *
 * @version $Id$
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @modified Matt McNaney
 * @module core
 */

define('XMLRPC_CANNOT_AUTHENTICATE', 'Unable to authenticate user.');
define('XMLRPC_BAD_RESULT', 'Unexpected results.');

// Until I work out all the kinks, I am leaving in my testing 
define('LOG_RESULTS', false);
define('LOG_DIR', 'files/xml/');

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
                           'blogger.deletePost',
                           'this:blogger_deletePost',
                           array('array', 'string', 'string', 'string', 'string', 'boolean'),
                           'Deletes a post.'
                           );
        
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

    function _get_article($id) {
        // returns article
        // see original 
    }


    function removeCategoryItems($key_id)
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

    function saveCategories($id, $categories, $api_type)
    {
        record(__FUNCTION__, 'in function');
        PHPWS_Core::initModClass('categories', 'Categories.php');
        PHPWS_Core::initModClass('categories', 'Action.php');

        record(__FUNCTION__, "id is $id");
        $key_id = $this->getKeyId($id);
        record(__FUNCTION__, "key_id is $key_id");

        if (empty($key_id)) {
            return new IXR_Error(4999, 'Database Error! Data could not be found.');
        }

        if (empty($categories)) {
            $this->removeCategoryItems($key_id);
            return true;
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
        $this->removeCategoryItems($key_id);

        /* Save category ids */
        foreach($cat_list as $cat_id) { 
            Categories_Action::addCategoryItem($cat_id, $key_id);
        }

        return true;
    }


    function blogger_deletePost($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();

        $logged = $this->logUser($args[2], $args[3], 'delete');
        if ($logged !== true) {
            return $logged;
        }

        return $this->delete($args[1]);
    }


    function blogger_getUsersBlogs($args) {
        record(__FUNCTION__, 'just returning address');
        return array(array('url'=>PHPWS_Core::getHomeHttp(), 'blogid'=>'1', 'blogName'=>Layout::getPageTitle(true)));
    }


    function metaWeblog_newPost($args) {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();
        /* Login the user */
        $logged = $this->logUser($args[1], $args[2], 'new');
        if ($logged !== true) {
            return $logged;
        }

        if (empty($args[3])) {
            return new IXR_Error(5010, 'Database Error!  Post not saved.');
        }

        $publish = (bool)$args[4];

        $id = $this->post(0, $args[3], $publish);

        if (!is_numeric($id)) {
            return $id;
        }

        if(!empty($args[3]['categories'])) {
            $this->saveCategories($id, $args[3]['categories'], 'metaWeblog');
        }
    
        return $id;
    }


    function metaWeblog_editPost($args) {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();
        /* Login the user */
        $logged = $this->logUser($args[1], $args[2], 'edit');
        if ($logged !== true) {
            return $logged;
        }

        if (empty($args[3])) {
            return new IXR_Error(5010, 'Missing reference key. Unpublished material cannot be categorized.');
        }

        $publish     = (bool)$args[4];
        $update_id   = (int)$args[0];

        $id = $this->post($update_id, $args[3], $publish);

        if (!is_numeric($id)) {
            return $id;
        }

        if(!empty($args[3]['categories'])) {
            $this->saveCategories($id, $args[3]['categories'], 'metaWeblog');
        }

        return true;

    }

    function metaWeblog_getPost($args) {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        record(__FUCTION__, ob_get_contents());
        ob_clean();

        if(!$result = $this->_get_article($args[0]))
            return new IXR_Error(5020, 'Database Error! Article data could not be found.');
        elseif(count($result->order)>1)
            return new IXR_Error(5030, 'Usage Error! This is a multi-section article.  You\'ll have to edit it online.');
        else
            return $result;
    }

    /**
     * There are five subpermission states. How your module handles them is up
     * to you.
     * 
     * new      - user can create a new entry
     * edit     - user can edit an existing entry
     * list     - user can list entries
     * category - user can post category changes
     * delete   - user can delete entries
     */
    function logUser($username, $password, $subpermission=null)
    {
        $result = Current_User::loginUser($username, $password);
        
        // Bad result or blank result returns an error message
        if (PHPWS_Error::logIfError($result) || !$result) {
            return new IXR_Error(4000, XMLRPC_CANNOT_AUTHENTICATE);
        }

        // No subpermission check passes the user
        if (!$subpermission) {
            return true;
        }

        // No allow function passes the user
        if (!method_exists($this, 'allow')) {
            $this->validUser = true;
            return true;
        }

        // Send the subpermission to the object's allow function
        $result = $this->allow($subpermission);
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


    function metaWeblog_getRecentPosts($args)
    {
        record(__FUNCTION__, 'in function');
        $logged = $this->logUser($args[1], $args[2], 'list');
        if ($logged !== true) {
            return $logged;
        }
        
        if (is_numeric($args[3])) {
            $limit = (int)$args[3];
        }

        $result = $this->getRecent($limit);

        ob_start();
        var_dump($result);
        record(__FUNCTION__, ob_get_contents(),false);
        ob_clean();

        return $result;
    }

    function metaWeblog_newMediaObject($args) {
        return '';
    }

    function metaWeblog_getCategories($args) {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        record(__FUNCTION__, ob_get_contents());
        ob_clean();
        
        return array_values(Categories::getCategories('list'));
    }

    function mt_getRecentPostTitles($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
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

    function mt_getCategoryList($args)
    {
        record(__FUNCTION__, 'in function');
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

    function mt_getPostCategories($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();

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

    function mt_setPostCategories($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        record(__FUNCTION__, ob_get_contents());
        ob_clean();

        /* Login the user */
        $logged = $this->logUser($args[1], $args[2], 'category');
        if ($logged !== true) {
            return $logged;
        }

        return $this->saveCategories($args[0], $args[3], 'MT');
    }

    function mt_supportedMethods($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        ob_clean();
        file_put_contents('files/xml/mt_supportedmethods.txt', $show);

        return $this->listMethods();
    }

    function mt_supportedTextFilters($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();

        return array();
    }

    function mt_getTrackbackPings($args)
    {
        record(__FUNCTION__, 'in function');
        echo 'sd';
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();

        return array();
    }

    function mt_publishPost($args)
    {
        record(__FUNCTION__, 'in function');
        return 1;
    }


    function isLoggedIn($args)
    {
        record(__FUNCTION__, 'in function');
        ob_start();
        var_dump($args);
        $show = ob_get_contents();
        record(__FUNCTION__, $show);
        ob_clean();
        

        if (!$this->logUser($args[0], $args[1])) {
            return 'Not a User! Username:'.trim($args[0]).' Password:'.$args[1];
        }  else {
            return 'Username is "'.Current_User::getUsername().'"';
        }
    }

    
    function getDate($args) {
        record(__FUNCTION__, 'in function');
        return date('r');
    }

    function getTime($args) {
        record(__FUNCTION__, 'in function');
        return date('H:i:s');
    }


    function helloWorld($args) {
        record(__FUNCTION__, 'in function');
        return 'Hello, World!';
    }

    function ooh($args) {
        record(__FUNCTION__, 'in function');
        return new IXR_Error(4000, 'ha ha ha');
    }


    function times10($value) {
        record(__FUNCTION__, 'in function');
        return array(
                     'times10' => (int)$value * 10,
                     'times100' => (int)$value * 10,
                     'times1000' => (int)$value * 10,
                     );
    }

}

/**
 * A debugging tool
 */
function record($file, $info, $append=true) {
    if (!LOG_RESULTS) {
        return;
    }

    if (empty($info)) {
        $info = 'empty';
    }
    if ($append) {
        file_put_contents(LOG_DIR . $file . '.txt', strftime('%T') . ' ' . $info . "\n", FILE_APPEND);
    } else {
        file_put_contents(LOG_DIR . $file . '.txt', strftime('%T') . ' ' . $info . "\n");
    }
}

?>