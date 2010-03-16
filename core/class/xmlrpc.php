<?php
require_once PHPWS_SOURCE_DIR . '/lib/xml/XML-RPC/IXR_Library.inc.php';

/**
 * This is a XML-RPC Server.
 *
 * Extended by another class to allow XML-RPC functionality.
 * Originally written by Eloi George for the Article Manager.
 * Docs coming. See blog's Blog_XML.php for examples
 *
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @modified Matt McNaney
 * @version $Id$
 * @module core
 */

define('XMLRPC_CANNOT_AUTHENTICATE', 'Unable to authenticate user.');
define('XMLRPC_BAD_RESULT', 'Unexpected results.');

/**
 * Some xmlrpc clients submit images as a octet-stream instead of a proper image type
 */
define('ALLOW_OCTET_STREAM', true);

/**
 * Until I work out all the kinks, I am leaving in my testing
 */

define('LOG_RESULTS', false);
define('LOG_DIR', 'files/xml/');

class MyServer extends IXR_IntrospectionServer {
    public $validuser      = false;

    public function __construct() {
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            record(__FUNCTION__,$GLOBALS['HTTP_RAW_POST_DATA']);
        }

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

    public function removeCategoryItems($key_id)
    {
        $db = new PHPWS_DB('category_items');
        $db->addWhere('key_id', (int)$key_id);
        return $db->delete();
    }

    public function saveCategories($id, $categories, $api_type)
    {
        PHPWS_Core::initModClass('categories', 'Categories.php');
        PHPWS_Core::initModClass('categories', 'Action.php');

        $key_id = $this->getKeyId($id);

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


    public function blogger_deletePost($args)
    {
        $logged = $this->logUser($args[2], $args[3], 'delete');
        if ($logged !== true) {
            return $logged;
        }

        return $this->delete($args[1]);
    }


    public function blogger_getUsersBlogs($args) {
        return array(array('url'=>PHPWS_Core::getHomeHttp(), 'blogid'=>'1', 'blogName'=>Layout::getPageTitle(true)));
    }


    public function metaWeblog_newPost($args) {
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


    public function metaWeblog_editPost($args) {
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

    public function metaWeblog_getPost($args) {
        $logged = $this->logUser($args[1], $args[2], 'edit');
        if ($logged !== true) {
            return $logged;
        }

        return $this->getPost($args[0]);
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
    public function logUser($username, $password, $subpermission=null)
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

    public function dropUser()
    {
        PHPWS_Core::killSession('User');
    }


    public function metaWeblog_getRecentPosts($args)
    {
        $logged = $this->logUser($args[1], $args[2], 'list');
        if ($logged !== true) {
            return $logged;
        }

        if (is_numeric($args[3])) {
            $limit = (int)$args[3];
        }

        $result = $this->getRecent($limit);

        return $result;
    }

    /**
     * The IXR library required a change to make this work.
     * If you update that file, make sure the base64 decode contains
     * a trim call on the currentTagContents variable.
     */
    public function metaWeblog_newMediaObject($args) {
        PHPWS_Core::requireInc('core', 'file_types.php');
        PHPWS_Core::initCoreClass('File.php');
        $allowed_images = unserialize(ALLOWED_IMAGE_TYPES);

        /* Login the user */
        $logged = $this->logUser($args[1], $args[2], 'media');
        if ($logged !== true) {
            return $logged;
        }

        $filename = PHPWS_File::nameToSafe($args[3]['name']);
        $filetype = $args[3]['type'];
        $ext = PHPWS_File::getFileExtension($filename);

        if ( !(ALLOW_OCTET_STREAM && $filetype == 'application/octet-stream')
        && !in_array($filetype, $allowed_images)) {
            return new IXR_Error(-652, "File type '$filetype' not allowed.");
        }

        if (!isset($allowed_images[$ext])) {
            return new IXR_Error(-653, "File extension '$ext' not allowed.");
        }

        if (isset($this->image_directory)) {
            $img_directory = & $this->image_directory;
        } else {
            $img_directory = 'images/';
        }

        PHPWS_File::appendSlash($img_directory);

        $source_file = $img_directory . $filename;
        @unlink($source_file);
        $handle = @ fopen($source_file, 'wb');
        if (!$handle) {
            return new IXR_Error(-2323, 'Unable to open file.');
        }
        $image = $args[3]['bits'];

        if (!@fwrite($handle, $image)) {
            return new IXR_Error(-651, "Unable to write file - $filename.");
        }
        fclose($handle);
        $url = PHPWS_Core::getHomeHttp() . $img_directory . $filename;
        return $url;
    }

    public function metaWeblog_getCategories($args) {
        return array_values(Categories::getCategories('list'));
    }

    public function mt_getRecentPostTitles($args)
    {
        return new IXR_Error(2, 'mt.getRecentPostTitles not supported yet.');
    }

    public function mt_getCategoryList($args)
    {
        $result = array();

        $list = Categories::getCategories('list');
        if (!empty($list)) {
            foreach($list as $key=>$value) {
                $struct = array();
                $struct['categoryId'] = (string)$key;
                $struct['categoryName'] = $value;
                $result[] = $struct;
            }
        }

        return $result;
    }

    public function mt_getPostCategories($args)
    {
        $logged = $this->logUser($args[1], $args[2], 'category');
        if ($logged !== true) {
            return $logged;
        }

        $id = $args[0];
        $key_id = $this->getKeyId($id);
        if (!$key_id) {
            return false;
        }

        $db = new PHPWS_DB('categories');
        $db->addWhere('category_items.key_id', $key_id);
        $db->addWhere('id', 'category_items.cat_id');
        $result = $db->select();

        if (empty($result)) {
            return false;
        } elseif (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (count($result) > 1) {
            $primary = true;
        } else {
            $primary = false;
        }

        foreach ($result as $cat) {
            $cat_result[] = array('categoryName'=>$cat['title'],
                                  'categoryId' => (string)$cat['id'],
                                  'isPrimary'=>$primary);
        }

        return $cat_result;
    }

    public function mt_setPostCategories($args)
    {
        /* Login the user */
        $logged = $this->logUser($args[1], $args[2], 'category');
        if ($logged !== true) {
            return $logged;
        }

        return $this->saveCategories($args[0], $args[3], 'MT');
    }

    public function mt_supportedMethods($args)
    {
        return $this->listMethods();
    }

    public function mt_supportedTextFilters($args)
    {
        return array();
    }

    public function mt_getTrackbackPings($args)
    {
        return array();
    }

    public function mt_publishPost($args)
    {
        return 1;
    }


    public function isLoggedIn($args)
    {
        if (!$this->logUser($args[0], $args[1])) {
            return 'Not a User! Username:'.trim($args[0]).' Password:'.$args[1];
        }  else {
            return 'Username is "'.Current_User::getUsername().'"';
        }
    }

    /**
     * Adds the full url to relative image links
     */
    public function appendImages($text)
    {
        $url = PHPWS_Core::getHomeHttp();
        return preg_replace('@(src=")\./(images)@', '\\1' . $url . '\\2', $text);
    }


    public function getDate($args) {
        return date('r');
    }

    public function getTime($args) {
        return date('H:i:s');
    }


    public function helloWorld($args) {
        return 'Hello, World!';
    }

    public function ooh($args) {
        return new IXR_Error(4000, 'ha ha ha');
    }


    public function times10($value) {
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

    if (is_array($info) || is_object($info)) {
        ob_start();
        var_dump($info);
        $info = ob_get_contents();
        ob_clean();
    }

    if ($append) {
        file_put_contents(LOG_DIR . $file . '.txt', strftime('%T') . ' ' . $info . "\n", FILE_APPEND);
    } else {
        file_put_contents(LOG_DIR . $file . '.txt', strftime('%T') . ' ' . $info . "\n");
    }
}

?>