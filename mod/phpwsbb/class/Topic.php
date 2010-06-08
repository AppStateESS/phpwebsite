<?php
/**
 * This is the PHPWSBB_Topic class.
 *
 * It largely serves as a virtual thread placeholder as the main work is done in
 * the Comments module.
 * It also contains public functions that allow this thread to be edited and saved.
 *
 * @version $Id: Topic.php,v 1.2 2008/09/12 07:12:03 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module phpwsBB
 * @var int id : Database id of this thread. Same as related Comments Thread
 * @var int key_id : Key Class id of this thread. Same as related Comments Thread
 * @var int is_phpwsbb : Whether this thread belongs to phpwsbb. If this > 1, this is the id of the thread that this topic was forked from
 * @var int fid : ID of the parent Forum
 * @var int sticky : Sticky Flag
 * @var int locked : Locked Flag.  No more posts are allowed.
 * @var int total_posts : # of posts in this thread
 * @var int views : Keeps track of how many times this thread was viewed
 * @var int lastpost_post_id : ID of the last post
 * @var int lastpost_date : unix timestamp of last post
 * @var int lastpost_author_id : Id# of the last poster (0 if guest)
 * @var string lastpost_author : Display name of the last poster
 * @var object _key : KEY object associated with this topic.
 * @var string _error : array of error messages.
 */

class PHPWSBB_Topic
{
    public $id                  = 0;
    public $key_id              = 0;
    public $is_phpwsbb          = 1;
    public $fid                 = 0;
    public $sticky              = 0;
    public $locked              = 0;
    public $total_posts         = 0;
    public $lastpost_post_id    = 0;
    public $lastpost_date       = 0;
    public $lastpost_author_id  = 0;
    public $lastpost_author     = '';
    public $_key                = null;
    public $_error              = array();

    // These reference elements of the associated Key
    public $module          = NULL;
    public $item_name       = NULL;
    public $item_id         = NULL;
    public $title           = NULL;
    public $summary         = NULL;
    public $url             = '';
    public $active          = 1;
    public $create_date     = 0;
    public $update_date     = 0;
    public $creator         = '';
    public $creator_id      = 0;
    public $updater         = '';
    public $updater_id      = 0;
    public $times_viewed    = 0;

    /**
     * Constructor for the PHPWSBB_Topic object.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param int id : Database id of the article to be instantiated (sp? heh).
     * @return none
     */
    public function __construct ($id = NULL, $key = null)
    {
        if(empty($id))
        $this->loadKey($key);

        elseif(!is_array($id)) {
            $db = new \core\DB('phpwsbb_topics');
            $db->addWhere('id', (int) $id);
            $result = $db->loadObject($this);
            if (core\Error::logIfError($result))
            return $result;
            $this->loadKey($key);
            // Make sure we can view inactive topics
            if (!$this->active) {
                $forum = $this->get_forum();
                if (!$forum->canModerate())
                $this->id = false;
            }
        }
        /* otherwise, $id is an array of object data */
        else {
            \core\Core::plugObject($this, $id);
        }
    }

    /**
     * Sets the topic's KEY information
     *
     * @param object $key : KEY object may be given
     * @return object : The current Key
     */
    public function loadKey($key = null)
    {
        if (!empty($key) && \core\Key::isKey($key))
        $this->_key = $key;
        elseif (!core\Key::isKey($this->_key)) {
            $this->_key = new \core\Key($this->key_id);
            if (core\Error::logIfError($this->_key->_error))
            exit('There has been an error.  Please check your phpWebsite error logs.');
        }

        // Set up reference links for easier coding
        $this->module       =& $this->_key->module;
        $this->item_name    =& $this->_key->item_name;
        $this->item_id      =& $this->_key->item_id;
        $this->title        =& $this->_key->title;
        $this->summary      =& $this->_key->summary;
        $this->url          =& $this->_key->url;
        $this->active       =& $this->_key->active;
        $this->create_date  =& $this->_key->create_date;
        $this->update_date  =& $this->_key->update_date;
        $this->creator      =& $this->_key->creator;
        $this->creator_id   =& $this->_key->creator_id;
        $this->updater      =& $this->_key->updater;
        $this->updater_id   =& $this->_key->updater_id;
        $this->times_viewed =& $this->_key->times_viewed;
    }

    /**
     * Develops all template tags for this forum.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return array
     * @access private
     */
    public function _get_tags ()
    {
        // Get parent Forum Info
        $forum = $this->get_forum();
        if (!$forum->id)
        return array();
        $tags = $forum->_get_tags();
        /* Develop this thread's tags */
        $tags['THREAD_ID'] = $this->id;
        $tags['THREAD_ACTIVE'] = $this->active;
        $tags['THREAD_STICKY'] = $this->sticky;
        $tags['THREAD_LOCKED'] = dgettext('phpwsbb', 'Locked');
        $tags['THREAD_REPLIES'] = $this->total_posts;
        if (core\Settings::get('phpwsbb', 'use_views'))
        $tags['THREAD_VIEWS'] = $this->times_viewed;
        $tags['THREAD_TITLE_LABEL'] = dgettext('comments', 'In Topic');
        $tags['THREAD_REPLIES_LABEL'] = dgettext('comments', 'Posts');
        $tags['THREAD_VIEWS_LABEL'] = dgettext('comments', 'Views');

        $title = $this->get_title();
        $tags['THREAD_TITLE'] = $title;
        $tags['THREAD_TITLE_LINK'] = $this->get_title_link($title);
        if (!$this->is_phpwsbb)
        $tags['THREAD_SOURCE_LINK'] = '<a href="'.$this->url.'">'.$title.'</a>';

        if (!empty($this->summary))
        $tags['THREAD_SUMMARY'] = \core\Text::parseOutput($this->summary);

        if (!$this->is_phpwsbb) {
            $tags['MODULE'] = $this->module;
            $tags['MODULE_ITEM_NAME'] = ucwords(dgettext($this->module, $this->item_name));
        }

        if ($this->total_posts > COMMENT_DEFAULT_LIMIT) {
            $tags['THREAD_PAGES'] = $this->getPageLinks();
        }

        $tags['THREAD_AUTHOR'] = $this->creator;
        if (!$this->creator_id)
        $tags['THREAD_AUTHOR'] .= ' '.COMMENT_ANONYMOUS_TAG;

        if ($this->lastpost_post_id) {
            $tags['THREAD_LASTPOST_POST_ID'] = $this->lastpost_post_id;
            $tags['THREAD_LASTPOST_AUTHOR'] = $this->lastpost_author;
            if (!$this->lastpost_author_id)
            $tags['THREAD_LASTPOST_AUTHOR'] .=  ' '.COMMENT_ANONYMOUS_TAG;
            $tags['THREAD_LASTPOST_DATE'] = PHPWSBB_Data::get_short_date($this->lastpost_date);

            $str = dgettext('comments', 'View the last post');
            //            $link = sprintf('index.php?module=phpwsbb&amp;view=topic&amp;id=%1$s&amp;pg=last#cm_%2$s', $this->id, $this->lastpost_post_id);
            //			$image_tag = sprintf('<a href="%1$s" class="%2$s" title="%3$s"><span>%4$s</span></a>'
            //                         , $link, 'phpwsbb_go_new_message_link', $str, $str);
            $link = \core\Text::quickLink('<span>'.$str.'</span>', 'phpwsbb', array('view'=>'topic', 'id'=>$this->id, 'pg'=>'last'), null, $str, 'phpwsbb_go_new_message_link');
            $link->rewrite = true;
            $link->setAnchor('cm_'.$this->lastpost_post_id);
            $tags['FORUM_LASTPOST_POST_LINK'] = $link->get();
            //core\Text::rewriteLink('<span>'.$str.'</span>', 'phpwsbb', array('view'=>'topic', 'id'=>$this->id, 'pg'=>'last'), null, $str, 'phpwsbb_go_new_message_link', 'cm_'.$lastthread->lastpost_post_id);
            $tags['THREAD_LASTPOST_INFO'] = sprintf(dgettext('phpwsbb', '%1$s %2$s<br />by %3$s'), $tags['FORUM_LASTPOST_POST_LINK'], $tags['THREAD_LASTPOST_DATE'], $tags['THREAD_LASTPOST_AUTHOR']);
        } else {
            $tags['THREAD_LASTPOST_INFO'] = dgettext('phpwsbb', 'None');
        }
        // Topic Status Icons
        if ($this->lastpost_date >= @$_SESSION['phpwsbb_last_on']) {
            $tags['TOPIC_HAS_NEW'] = sprintf('<div class="%1$s" title="%2$s"><span>%2$s</span></div>'
            , 'phpwsbb_new_messages', dgettext('phpwsbb', 'New Posts'));
        } else {
            $tags['TOPIC_HAS_NEW'] = sprintf('<div class="%1$s" title="%2$s"><span>%2$s</span></div>'
            , 'phpwsbb_no_new_messages', dgettext('phpwsbb', 'No New Posts'));
        }

        if ($this->total_posts > 20 || $this->times_viewed > 100)
        $tags['TOPIC_IS_HOT'] = sprintf('<div class="%1$s" title="%2$s"><span>%2$s</span></div>'
        , 'phpwsbb_hot_topic', dgettext('phpwsbb', 'Popular topic'));

        if ($this->locked)
        $tags['TOPIC_LOCKED'] = sprintf('<div class="%1$s" title="%2$s"><span>%2$s</span></div>'
        , 'phpwsbb_locked', dgettext('comments', 'Closed topic'));
        if ($this->sticky)
        $tags['TOPIC_STICKY'] = sprintf('<div class="%1$s" title="%2$s"><span>%2$s</span></div>'
        , 'phpwsbb_sticky', dgettext('phpwsbb', 'Read This!'));

        // If this isn't a phpwsbb-generated topic...
        if (!$this->is_phpwsbb) {
            // If the parent module doesn't have any veiwing instructions...
            $file = PHPWS_SOURCE_DIR . 'mod/'.$this->module.'/inc/phpwsbb.php';
            if (is_file($file) && $content = (include $file))
            $tags['REFER_DESCRIPTION'] = $content;
            else {
                $tags['REFER_DESCRIPTION'] = sprintf(dgettext('phpwsbb', 'This is the discussion thread for the %1$s "%2$s".'), ucwords($this->module), $this->title);
                $tags['REFER_LINK'] = sprintf(dgettext('phpwsbb', 'To see the full %1$s, <a href="%2$s">click on this link</a>.'), $this->item_name, $this->url);
            }
        }
        return $tags;
    }

    /**
     * Displays Topic contents to the user.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return none
     * @access public
     */
    public function view ()
    {
        Layout::addPageTitle(dgettext('phpwsbb', 'Topic').': '.strip_tags($this->title));
        Layout::addStyle('phpwsbb');

        /* Get Thread Info */
        $tags = $this->_get_tags();

        /* Now test to see if thread is viewable */
        if($this->is_phpwsbb && !$this->total_posts > 0)
        $content = dgettext('phpwsbb', 'The thread you requested is awaiting approval by an administrator.');
        elseif(empty($tags))
        $content = dgettext('phpwsbb', 'The thread you requested is not available. Please check the security logs.');
        else {
            /* Raise the Key flag (also updates view count)*/
            $this->_key->flag();
            /* Get Comment Thread */
            \core\Core::initModClass('comments', 'Comments.php');
            $thread = Comments::getThread($this->_key);
            $content = $thread->view();
        }
        return \core\Template::processTemplate($tags, 'phpwsbb', 'topic.tpl') . $content;
    }

    /**
     * Adds all column names necessary for loading a topic.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param object : $db The database object to add the columns to
     * @return array
     * @access public
     */
    public static function addColumns (& $db)
    {
        $db->addColumn('phpwsbb_topics.*');
        $db->addColumn('phpws_key.module',    	null, 'module');
        $db->addColumn('phpws_key.item_name', 	null, 'item_name');
        $db->addColumn('phpws_key.item_id', 	null, 'item_id');
        $db->addColumn('phpws_key.title', 		null, 'title');
        $db->addColumn('phpws_key.summary', 	null, 'summary');
        $db->addColumn('phpws_key.url', 		null, 'url');
        $db->addColumn('phpws_key.active',      null, 'active');
        $db->addColumn('phpws_key.create_date', null, 'create_date');
        $db->addColumn('phpws_key.update_date', null, 'update_date');
        $db->addColumn('phpws_key.creator', 	null, 'creator');
        $db->addColumn('phpws_key.creator_id', 	null, 'creator_id');
        $db->addColumn('phpws_key.updater', 	null, 'updater');
        $db->addColumn('phpws_key.updater_id', 	null, 'updater_id');
        $db->addColumn('phpws_key.times_viewed',null, 'times_viewed');
        $db->addWhere('phpws_key.id', 'phpwsbb_topics.key_id');
    }

    /**
     * Displays a screen for entering the first post of a new thread.
     *
     * This is ONLY called when CREATING a thread!
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return none
     * @access private
     */
    public function edit ()
    {
        // Make sure that we can create a topic
        // Get parent Forum Info
        $forum = $this->get_forum();
        if (!$forum->can_post()) {
            $message = dgettext('phpwsbb', 'You are not authorized to create topics here.');
            Security::log($message);
            return $message;
        }

        /* Construct editform tags */
        $form = new \core\Form;
        $form->useBreaker();
        $form->setAction('index.php?module=phpwsbb');
        if ($this->id)
        $form->addHidden('topic', $this->id);
        $form->addHidden('forum', $forum->id);
        $form->addHidden('op', 'save_topic');
        $formtags = $forum->_get_tags();
        $form->mergeTemplate($formtags);
        /* Error Messages */
        if (!empty($GLOBALS['BB_errors']))
        $form->addTplTag('ERROR', implode("<br />\n", $GLOBALS['BB_errors']));
        /* Back Link */
        if (!$this->id)
        $form->addTplTag('BACK_LINK', sprintf(dgettext('phpwsbb', 'Back to Forum "%s"'), $formtags['FORUM_TITLE_LINK']));
        else
        $form->addTplTag('BACK_LINK', sprintf(dgettext('phpwsbb', 'Back to Topic "%s"'), \core\Text::rewriteLink($this->title, 'phpwsbb', array('view'=>'topic', 'id'=>$this->id))));
        /* Anonymous Poster Info */
        if (!Current_User::isLogged() && $forum->allow_anon && \core\Settings::get('comments', 'anonymous_naming')) {
            $form->addText('anon_name', @$_POST['anon_name']);
            $form->setLabel('anon_name', dgettext('comments', 'Name'));
            $form->setSize('anon_name', 20, 20);
        }
        /* Topic Title */
        $form->addText('cm_subject', @$_POST['cm_subject']);
        $form->setMaxSize('cm_subject', '70');
        $form->setWidth('cm_subject', '70%');
        $form->setLabel('cm_subject', dgettext('phpwsbb', 'Title'));
        /* Topic Text */
        $form->addTextArea('cm_entry', @$_POST['cm_entry']);
        $form->useEditor('cm_entry', true, true, 0, 0);
        $form->setWidth('cm_entry', '95%');
        $form->setRows('cm_entry', '10');
        //		$form->useEditor('cm_entry');
        $form->setLabel('cm_entry', dgettext('phpwsbb', 'Comment'));
        /* CAPTCHA */
        \core\Core::initModClass('comments', 'Comments.php');
        if (Comments::useCaptcha()) {
                        $form->setLabel('captcha', dgettext('phpwsbb', 'Please copy the word in the above image.'));
            $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        }

        $form->addSubmit('submit');
        $tags = $form->getTemplate();
        return \core\Template::processTemplate($tags, 'comments', 'edit.tpl').'<br /><br />';
    }

    /**
     * Updates this thread's information
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return none
     */
    public function update_topic ()
    {
        // Update the lastpost information
        $db = new \core\DB('comments_items');
        $db->addColumn('comments_items.id');
        $db->addColumn('comments_items.create_time');
        $db->addColumn('comments_items.author_id');
        $db->addColumn('comments_items.anon_name');
        $db->addColumn('comments_threads.total_comments');
        $db->addWhere('thread_id', $this->id);
        $db->addWhere('comments_threads.id', 'comments_items.thread_id');
        $db->addWhere('comments_items.approved', 1);
        $db->addOrder('create_time desc');
        $row = $db->select('row');
        if (core\Error::logIfError($row))
        return;
        if (empty($row)) { // Topic is either empty or full of unapproved comments
            $this->total_posts = 0;
            return;
        }
        $this->total_posts = $row['total_comments'];
        $this->lastpost_post_id = $row['id'];
        $this->lastpost_date = $row['create_time'];
        if ($row['author_id']) {
            $this->lastpost_author_id = $row['author_id'];
            $user = new PHPWS_User($row['author_id']);
            $this->lastpost_author = $user->getUsername();
        }
        else {
            $this->lastpost_author_id = 0;
            $this->lastpost_author = $row['anon_name'];
        }
    }

    /**
     * Creates a new empty topic & associated Comment thread & Key.
     *
     * @author Eloi George <elo$titlei@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param int $fid : Forum Id
     * @param string $title : Topic title
     * @param string $summary : Topic entry.  Only the first 255 characters will be stored
     * @return Success or Failure.  Reason for failure will be stored in $this->error
     */
    public function create ($fid, $title = '', $summary = '')
    {
        // Get parent Forum Info
        $this->fid = $fid;
        $forum = $this->get_forum();
        // Make sure that we can save this topic
        if (!$forum->can_post()) {
            $this->_error = dgettext('phpwsbb', 'You are not authorized to save this topic.');
            Security::log($this->_error);
            return false;
        }
        if (!empty($title))
        $this->title = $title;
        if (!empty($summary))
        $this->summary = $summary;

        // Determine the id to use
        $db = new \core\DB('comments_threads');
        $this->id = $GLOBALS['core\DB']['connection']->nextId($db->addPrefix('comments_threads'));
        if (!$this->commit(true))
        return false;

        // create associated Comment Thread
        \core\Core::initModClass('comments', 'Comments.php');
        \core\Core::initModClass('comments', 'Comment_Item.php');
        $thread = new Comment_Thread;
        $thread->id = $this->id;
        $thread->key_id = $this->key_id;
        $thread->_key = $this->_key;
        $thread->allowAnonymous($forum->allow_anon);
        $thread->setApproval($forum->default_approval);
        $result = $db->saveObject($thread, false, false);
        if (core\Error::logIfError($result)) {
            $this->_error = dgettext('phpwsbb', 'ERROR: Could not save comment_thread.  Please alert the site administrator.');
            return false;
        }

        // If there's a Comment Post attempt, check it
        if (!empty($_POST['cm_subject']) && !empty($_POST['cm_entry'])) {
            $c_item = new Comment_Item;
            if (!Comments::postComment($thread, $c_item))
            return $c_item->_error;
            $result = $c_item->save();
            if (core\Error::logIfError($result)) {
                $this->_error = dgettext('phpwsbb', 'ERROR: Could not save comment.  Please alert the site administrator.');
                return false;
            }
            $this->title = $c_item->subject;
            $this->summary = $c_item->getEntry(false);
            $this->setKey();
            if ($c_item->approved) {
                // Start subscription
                $user = Comments::getCommentUser(Current_User::getId());
                if ($user->monitordefault)
                Comment_User::subscribe(Current_User::getId(), $thread->id);
            }
            $this->update_topic();
        }

        return true;
    }

    /**
     * Sets the topic's PHPWS_KEY information
     *
     * @param none
     * @return object : The current Key
     */
    public function setKey()
    {
        if (!$this->is_phpwsbb)
        return;
        $this->_key->setModule('phpwsbb');
        $this->_key->setItemName('topic');
        $this->_key->setItemId($this->id);
        $this->_key->setEditPermission('manage_forums');
        $this->_key->setUrl('index.php?module=phpwsbb&amp;view=topic&amp;id='.$this->id);
        $this->_key->setTitle($this->title);
        $this->_key->setSummary(strip_tags($this->summary));
        $result = $this->_key->save();
        if (core\Error::logIfError($result))
        exit('There has been an error.  Please check your phpWebsite error logs.');
        $this->key_id = $this->_key->id;
        return $result;
    }

    /**
     * Commits a topic to the database
     *
     * @param bool $insert : Whether this topic's database record will be INSERTed or UPDATEd
     * @return none
     */
    public function commit($insert = false)
    {
        // Make sure that we can save this topic
        $forum = $this->get_forum();
        if (!$forum->can_post()) {
            $this->_error = dgettext('phpwsbb', 'You are not authorized to save this topic.');
            Security::log($this->_error);
            return false;
        }
        if ($insert && $this->is_phpwsbb)
        $this->setKey();
        $db = new \core\DB('phpwsbb_topics');
        $result = $db->saveObject($this, false, !(bool) $insert);
        if (core\Error::logIfError($result)) {
            $this->_error = dgettext('phpwsbb', 'There was an error when saving this topic to the database!');
            return false;
        }
        // Update Forum post stats
        $forum->update_forum(true);
        // Reset Session Cache
        \core\Cache::remove('bb_latestpostsblock');
        return true;
    }

    /**
     * Returns a formatted title.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return none
     */
    public function get_title ()
    {
        $sticky = $hidden = $topic_type = $split = '';
        if (!$this->is_phpwsbb)
        $topic_type = ucwords(dgettext($this->module, $this->item_name)).': ';
        if ($this->sticky)
        $sticky = '['.dgettext('phpwsbb', 'Sticky').'] ';
        if (!$this->active)
        $hidden = '['.dgettext('phpwsbb', 'Hidden').'] ';
        if ($this->is_phpwsbb > 1)
        $split = '['.core\Text::rewriteLink(dgettext('phpwsbb', 'Fork'), 'phpwsbb', array('view'=>'topic', 'id'=>$this->is_phpwsbb)).'] ';
        return $topic_type . $sticky . $hidden . $split . $this->title;
    }

    /**
     * Returns a title link that shows the topic's summary in the tooltip.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param string $title : [optional] Title text that you want to display.  Default is the bare title.
     * @return none
     */
    public function get_title_link ($title = null)
    {
        if (empty($title))
        $title = $this->title;
        return str_replace('>'.$title, ' title="'.$this->summary.'">'.$title,
        \core\Text::rewriteLink($title, 'phpwsbb', array('view'=>'topic', 'id'=>$this->id)));
    }

    /**
     * Adds BB information to the MiniAdmin block.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param object $object : Current Article
     * @return none
     */
    public function MiniAdmin()
    {
        $link = array();
        $forum = $this->get_forum();
        if ($forum->userCan('phpwsbb', 'sticky_threads'))
        if ($this->sticky)
        $link[] = \core\Text::secureLink(dgettext('phpwsbb', '"Unstick" this Topic'), 'phpwsbb', array('op'=>'unstick_topic','topic'=>$this->id));
        else
        $link[] = \core\Text::secureLink(dgettext('phpwsbb', '"Stick" this Topic'), 'phpwsbb', array('op'=>'stick_topic','topic'=>$this->id));
        if ($forum->userCan('phpwsbb', 'delete_threads')) {
            $js_var['QUESTION'] = dgettext('phpwsbb', 'This will delete the topic and all messages under it!  Are you sure you want to delete this?');
            $js_var['ADDRESS'] = 'index.php?module=phpwsbb&amp;op=delete_topic&amp;yes=1&amp;topic='.$this->id.'&amp;authkey='.Current_User::getAuthKey();
            $js_var['LINK']    = dgettext('phpwsbb', 'Delete this Topic');
            if (javascriptEnabled())
            $link[] = Layout::getJavascript('confirm', $js_var);
            else
            $link[] = sprintf('<a href="./%s" title="%s">%s</a>', str_replace('&amp;yes=1','', $js_var['ADDRESS']), $js_var['QUESTION'], $js_var['LINK']);
        }
        if (Current_User::allow('phpwsbb', 'hide_threads')) {
            if ($this->active)
            $link[] = \core\Text::secureLink(dgettext('phpwsbb', 'Hide this Topic'), 'phpwsbb', array('op'=>'hide_topic','topic'=>$this->id));
            else
            $link[] = \core\Text::secureLink(dgettext('phpwsbb', 'Show this Topic'), 'phpwsbb', array('op'=>'show_topic','topic'=>$this->id));
        }
        if (!empty($link));
        MiniAdmin::add('phpwsbb', $link);
        PHPWSBB_Data::move_item_link($this);
    }

    /**
     * Checks permissions & saves this thread
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param string $authorization : authorization level that user must have to commit these changes
     * @param string $success_message : Message to return on successful save
     * @param bool $set_key : If true, the topic's Key is also updated
     * @return none
     */
    public function set_thread($authorization, $success_message, $set_key=false)
    {
        $forum = $this->get_forum();
        if (!$forum->userCan('phpwsbb', $authorization))
        return dgettext('phpwsbb', "You're not allowed to do this!");
        $result = $this->commit();
        if ($result == true) {
            if ($set_key)
            $this->setKey();
            return $success_message;
        }
        else
        return $result;
    }

    /**
     * Deletes this thread
     *
     * If it's a phpwsbb-owned topic, it deletes everything.
     * Otherwise it just deletes the associated comment thread.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return bool : success or failure
     */
    public function delete()
    {
        if ($this->is_phpwsbb)
        return $this->_key->delete();

        $thread = new Comment_Thread($this->id);
        $thread->delete();
        return $this->drop();
    }

    /**
     * Drops this topic from phpwsBB
     *
     * If it's a phpwsbb-owned topic, it deletes everything.
     * Otherwise it just deletes the associated comment thread.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return bool : success or failure
     */
    public function drop()
    {
        $db = new \core\DB('phpwsbb_topics');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (!$result || \core\Error::logIfError($result))
        return false;
        return true;
    }

    /**
     * Retrieves the parent forum
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return object : pointer to parent PHPWSBB_Forum
     */
    public function &get_forum()
    {
        /* Get parent Forum Info */
        if (!$this->fid)
        exit ('This topic doesn\'t have a forum!!!');
        /* Get Forum object */
        if (!isset($GLOBALS['BBForums'][$this->fid]))
        $GLOBALS['BBForums'][$this->fid] = new PHPWSBB_Forum($this->fid);
        return $GLOBALS['BBForums'][$this->fid];
    }

    public function getPageLinks()
    {
        $total_pages = ceil($this->total_posts / COMMENT_DEFAULT_LIMIT);
        if ($total_pages > 6) {
            $stop = 2;
            $restart = $total_pages - 2;
        }
        else {
            $stop = $total_pages;
            $restart = 0;
        }

        for($i=1; $i <= $stop; $i++)
        $pageList[] = \core\Text::rewriteLink($i , 'phpwsbb', array('view'=>'topic', 'id'=>$this->id, 'pg'=>$i));

        if ($restart) {
            $pageList[] = '...';
            for($i=$restart; $i <= $total_pages; $i++)
            $pageList[] = \core\Text::rewriteLink($i , 'phpwsbb', array('view'=>'topic', 'id'=>$this->id, 'pg'=>$i));
        }

        return 'Pages: ' . implode('<span>, </span>', $pageList);
    }

}
?>