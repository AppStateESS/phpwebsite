<?php
/**
 * This is the PHPWSBB_Forum class.  It handles saving, updating, and organization
 * of topics.  It also contains public functions that allow this forum to be edited
 * and saved.
 *
 * @version $Id: Forum.php,v 1.2 2008/09/12 07:12:03 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module phpwsBB
 * @var int id : Database id of this forum
 * @var int key_id : Key Class id of this forum
 * @var string title : Title of this forum 
 * @var string description : Summary description of this forum
 * @var int active : Whether or not this forum is active
 * @var int topics : Number of topics to this forum
 * @var int sortorder : Sort order of this forum
 * @var int lastpost : unix timestamp of last post
 * @var int posts : # of posts in this forum
 * @var array moderators : IDs & usernames of this forum's moderators
 * @var int allow_anon : Whether or not phpwsbb-created topics in this forum should allow anonymous posting
 * @var int locked : Locked Flag.  No more posts are allowed.
 */

class PHPWSBB_Forum
{
    public $id              = 0;
    public $key_id          = 0;
    public $title           = '';
    public $description     = '';
    public $active          = true;
    public $topics          = 0;
    public $sortorder       = 0;
    public $posts           = 0;
    public $allow_anon      = 0;
    public $default_approval = 0;
    public $locked          = 0;

    /**
     * Constructor for the PHPWSBB_Forum object.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param int id : Database id of the article to be instantiated (sp? heh).
     * @return none
     */
    public function __construct ($id = NULL)
    {
        if(empty($id)) {
            $this->allow_anon = PHPWS_Settings::get('phpwsbb', 'allow_anon_posts');
            $this->default_approval = PHPWS_Settings::get('comments', 'default_approval');
            return;
        }
        if(!is_array($id)) {
            $db = & new PHPWS_DB('phpwsbb_forums');
            $this->addColumns($db);
            $db->addWhere('id', (int) $id);
            if(!Current_User::allow('phpwsbb', 'manage_forums')) 
                Key::restrictView($db, 'phpwsbb', false);
            $result = $db->loadObject($this);
            if (PEAR::isError($result)) {
                $this->id = 0;
                return PHPWS_Error::get('Forum not loaded', 'phpwsbb', 'PHPWSBB_Forum::new');
            }
        }
        /* otherwise, $id is an array of object data */
        else {
            PHPWS_Core::plugObject($this, $id);
        }
    }

    /**
     * Adds all column names necessary for loading a forum.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param object : $db The database object to add the columns to 
     * @return array
     * @access public
     */
    public function addColumns (& $db)
    {
        if(Current_User::allow('phpwsbb', 'manage_forums')) {
            $db->addColumn('phpwsbb_forums.*');
            $db->addColumn('phpws_key.active', null, 'active');
            $db->addWhere('phpws_key.id', 'phpwsbb_forums.key_id');
        }
        else
            Key::restrictView($db, 'phpwsbb', false);
    }
	
    /**
     * Develops all template tags for this forum.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param bool $getlasttopic : If true, will retrieve the last topic's information
     * @return array
     * @access private
     */
    public function _get_tags ($getlasttopic = false)
    {
        if (!empty($GLOBALS['BBForumTags'][$this->id]))
            return $GLOBALS['BBForumTags'][$this->id];

        $tags['FORUM_ID'] = $this->id;
        $tags['FORUM_KEY_ID'] = $this->key_id;

        $tags['HOME_LINK'] = PHPWS_Text::rewriteLink(dgettext('phpwsbb', 'Bulletin Boards'), 'phpwsbb'); 

        $title = $this->get_title();
        $tags['FORUM_TITLE'] = $title;
        $tags['FORUM_TITLE_LINK'] = PHPWS_Text::rewriteLink($title, 'phpwsbb', array('view'=>'forum', 'id'=>$this->id));
        $tags['FORUM_LABEL'] = dgettext('comments', 'Forum');

        if ($this->description)
            $tags['FORUM_DESCRIPTION'] = PHPWS_Text::parseOutput($this->description);

        $tags['FORUM_ACTIVE'] = $this->active;
        $tags['FORUM_TOPICS'] = $this->topics;
        $tags['FORUM_SORTORDER'] = $this->sortorder;
        $tags['FORUM_POSTS'] = $this->posts;

        // Retrieve lastpost/topic information
        if ($getlasttopic) {
            $lastthread = new PHPWSBB_Topic();
            $db = & new PHPWS_DB('phpwsbb_topics');
            PHPWSBB_Topic::addColumns($db);
            $db->addWhere('fid', $this->id);
            Key::restrictView($db, 'phpwsbb');
            $db->addOrder('lastpost_date desc');
            $db->loadObject($lastthread);
            if ($lastthread->id) {
                $tags['FORUM_LASTPOST_TOPIC_LABEL'] = $lastthread->title;
                $tags['FORUM_LASTPOST_TOPIC_LINK'] = PHPWS_Text::rewriteLink($lastthread->title, 'phpwsbb', array('view'=>'topic', 'id'=>$lastthread->id));
                $tags['FORUM_LASTPOST_TOPIC_ID'] = $lastthread->id;
                $tags['FORUM_LASTPOST_POST_ID'] = $lastthread->lastpost_post_id;
                $tags['FORUM_LASTPOST_AUTHOR'] = $lastthread->lastpost_author;
                if (!$lastthread->lastpost_author_id)
                    $tags['FORUM_LASTPOST_AUTHOR'] .=  '('.dgettext('phpwsbb', 'Guest').')';
                $tags['FORUM_LASTPOST_DATE_LONG'] = PHPWSBB_Data::get_long_date($lastthread->lastpost_date);
                $tags['FORUM_LASTPOST_DATE_SHORT'] = PHPWSBB_Data::get_short_date($lastthread->lastpost_date);
                $tags['FORUM_LASTPOST_DATE_REL'] = PHPWS_Time::relativeTime(PHPWS_Time::getUserTime($lastthread->lastpost_date));
                $str = dgettext('phpwsbb', 'View the last post');
                $link = PHPWS_Text::quickLink('<span>'.$str.'</span>', 'phpwsbb', array('view'=>'topic', 'id'=>$lastthread->id, 'pg'=>'last'), null, $str, 'phpwsbb_go_forum_new_message_link');
                $link->rewrite = true;
                $link->setAnchor('cm_'.$lastthread->lastpost_post_id);
                $tags['FORUM_LASTPOST_POST_LINK'] = $link->get();
                $tags['IN'] = dgettext('phpwsbb', 'in');
                $tags['BY'] = dgettext('phpwsbb', 'by');
                // Forum Status Icon
                if ($lastthread->lastpost_date > @$_SESSION['phpwsbb_last_on']) 
                    $tags['FORUM_HAS_NEW'] = sprintf('<div class="%1$s" title="%2$s"><span>%3$s</span></div>'
                                                     , 'phpwsbb_forum_new_messages', dgettext('phpwsbb', 'Forum Contains New Posts'), dgettext('phpwsbb', 'New Posts'));
                else 
                    $tags['FORUM_HAS_NEW'] = sprintf('<div class="%1$s" title="%2$s"><span>%3$s</span></div>'
                                                     , 'phpwsbb_forum_no_new_messages', dgettext('phpwsbb', 'Forum Contains No New Posts'), dgettext('phpwsbb', 'No New Posts'));
            }
            else {
                $tags['FORUM_LASTPOST_POST_LINK'] = dgettext('phpwsbb', 'None');
                $tags['FORUM_HAS_NEW'] = sprintf('<div class="%1$s" title="%2$s"><span>%3$s</span></div>'
                                                 , 'phpwsbb_forum_no_new_messages', dgettext('phpwsbb', 'Forum Contains No New Posts'), dgettext('phpwsbb', 'No New Posts'));
            }
        }

        $tags['FORUM_MODERATORS_LBL'] = dgettext('phpwsbb', 'Moderators assigned to this forum');
        if (empty($GLOBALS['Moderators_byForum'][$this->id]))
            $tags['FORUM_MODERATORS'] = dgettext('phpwsbb', 'None');
        else 
            $tags['FORUM_MODERATORS'] = implode(', ', $GLOBALS['Moderators_byForum'][$this->id]);
        if ($this->locked) 
            $tags['FORUM_LOCKED'] = sprintf('<div class="%1$s" title="%2$s"><span>%2$s</span></div>'
                                            , 'phpwsbb_forum_locked', dgettext('phpwsbb', 'Closed Forum'));
        if ($this->can_post()) { 
            $str = dgettext('comments', 'Submit a new topic');
            $link = 'index.php?module=phpwsbb&amp;op=create_topic&amp;forum=' . $this->id;
            $tags['FORUM_ADD_TOPIC_BTN'] = sprintf('<a href="%1$s" class="%2$s" title="%3$s"><span>%4$s</span></a>'
                                                   , $link, 'phpwsbb_add_topic_link', $str, $str);
        }

        /* Show Category links & icons */
    	$cat_link = $cat_icon = array();
        $db = new PHPWS_DB('categories');
        $db->addWhere('category_items.key_id', $this->key_id);
        $db->addWhere('id', 'category_items.cat_id');
        $cat_result = $db->getObjects('Category');
        if (count($cat_result)) {
            foreach ($cat_result AS $category) {
                if (!is_object($category))
                    break;
                $cat_link[] = $category->getViewLink('phpwsbb');
                if ($category->icon) 
                    $cat_icon[] = $category->getViewLink('phpwsbb', $category->getIcon());
            }
            if (!empty($cat_link)) {
                $tags['CATEGORY_TEXT'] = dgettext('phpwsbb', 'Category');
                $tags['CATEGORY_LINKS'] = implode(', ', $cat_link);
            }
            if (!empty($cat_icon))
                $tags['CATEGORY_ICONS'] = implode(' ', $cat_icon);
        }

        $GLOBALS['BBForumTags'][$this->id] = $tags;
        return $tags;
    }

    /**
     * Displays Forum contents to the user.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none 
     * @return none
     * @access public
     */
    public function view ()
    {
        Layout::addPageTitle(dgettext('phpwsbb', 'Forum').': '.$this->get_title());
        /* Now test to see if forum is viewable */
        if (!$this->active && !$this->canModerate()) 
            return dgettext('phpwsbb', 'The forum you requested is not viewable.');

        /* If it's being viewed by itself... */
        if ($_REQUEST['module']=='phpwsbb') {
            /* Raise the Key flag */
            $key = & new Key($this->key_id);
            $key->flag();
        }

        /* Create DBPager object */
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('phpwsbb_topics', 'PHPWSBB_Topic');
        $pager->setModule('phpwsbb');
        $pager->setTemplate('forum.tpl');
        $pager->setCacheIdentifier('viewforum_'.$this->id);
        $pager->cacheQueries();
        $pager->setDefaultOrder('lastpost_date', 'desc');
        $pager->setDefaultLimit(30); 
        $pager->setLimitList(array(30,60,90));
        $pager->setEmptyMessage(dgettext('phpwsbb', 'No Topics found.'));
        $pager->addToggle(' class="toggle1"');
        $pager->addToggle(' class="toggle2"');
        $pager->addRowTags('_get_tags');
        $pager->setSearch('title');
        PHPWSBB_Topic::addColumns($pager->db);
        $pager->db->addWhere('fid', $this->id);
        if (!$this->canModerate())
            $pager->db->addWhere('phpws_key.active', 1);
        // Filter out the unapproved topics -- total_posts>0 OR is_phpwsbb=0
        $pager->db->addWhere('total_posts', 0, '>', null, 'approved_group');
        $pager->db->addWhere('is_phpwsbb', 0, '=', 'or', 'approved_group');
        $pager->db->addOrder('sticky desc');
        $pager->addSortHeader('phpws_key.title', dgettext('phpwsbb', 'Topic'));
        $pager->addSortHeader('phpws_key.creator', dgettext('phpwsbb', 'Topic Starter'));
        $pager->addSortHeader('phpws_key.create_date', dgettext('phpwsbb', 'Start Date'));
        $pager->addSortHeader('lastpost_date', dgettext('phpwsbb', 'Last Post'));
        $pager->addSortHeader('total_posts', dgettext('phpwsbb', 'Posts'));
        $pager->addSortHeader('phpws_key.times_viewed', dgettext('phpwsbb', 'Views'));
        $pager->table_columns[] = 'phpws_key.title';
        $pager->table_columns[] = 'phpws_key.creator';
        $pager->table_columns[] = 'phpws_key.times_viewed';
        $link = 'index.php?module=phpwsbb&amp;view=forum&amp;id='.$this->id;
        $pager->setLink($link);

        $pager->addPageTags($this->_get_tags() + $this->getStatusTags());
        $content = $pager->get();
        if (PHPWS_Error::logIfError($content)) 
            echo PHPWS_Error::printError($content);
        return $content;
    }

    /**
     * Displays an editing screen. 
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return none
     * @access private
     */
    public function edit ()
    {
        PHPWS_Core::initModClass('help', 'Help.php');
        if(!Current_User::allow('phpwsbb', 'manage_forums')) {
            $message = sprintf(dgettext('phpwsbb', 'You are not authorized to edit forum %s.', $this->title));
            Security::log($message);
            return $message;
        }
        /* Variable to set tab order */
        $tabs = 1;
        /* Create form */
        $form = new PHPWS_Form('bb_edit');
        $form->useBreaker();
        if ($this->id)
            $forum_arg = '&amp;forum='.$this->id;
        $form->setAction('index.php?module=phpwsbb&amp;op=edit_forum'.@$forum_arg);
        /* Error Messages */
        if (!empty($GLOBALS['BB_errors']))
            $form->addTplTag('ERROR', implode("<br />\n", $GLOBALS['BB_errors'])); 
        /* Forum Label */
        $form->addText('Forum_label', $this->title); 
        $form->setMaxSize('Forum_label', '33');
        $form->setWidth('Forum_label', '70%');
        $form->setLabel('Forum_label', dgettext('phpwsbb', 'Title'));
        $form->setTag('Forum_label', 'TITLE');
        /* Forum Description */
        $form->addTextArea('Forum_description', $this->description); 
        $form->setWidth('Forum_description', '70%');
        $form->setRows('Forum_description', '2');
        $form->setLabel('Forum_description', dgettext('phpwsbb', 'Summary'));
        $form->setTag('Forum_description', 'DESCRIPTION');
        /* Forum Sort Order */
        $form->addText('Forum_sortorder', $this->sortorder); 
        $form->setMaxSize('Forum_sortorder', '5');
        $form->setWidth('Forum_sortorder', '20%');
        $form->setLabel('Forum_sortorder', dgettext('phpwsbb', 'Sort Order'));
        $form->setTag('Forum_sortorder', 'SORTORDER');
        $form->addTplTag('SORTORDER_HELP',  PHPWS_Help::show_link('phpwsbb', 'sortorder'));
        /* Anonymous Posting? */
        $form->addCheckbox('Forum_allow_anon');
        $form->setMatch('Forum_allow_anon', $this->allow_anon);
        $form->setLabel('Forum_allow_anon', dgettext('article', 'Allow unregistered users to post messages'));
        $form->setTag('Forum_allow_anon', 'ALLOW_ANON');
        /* default_approval */
        $default_approval[0] = dgettext('comments', 'All comments preapproved');
        $default_approval[1] = dgettext('comments', 'Anonymous comments require approval');
        $default_approval[2] = dgettext('comments', 'All comments require approval');
        $form->addSelect('Forum_default_approval', $default_approval);
        $form->setMatch('Forum_default_approval', $this->default_approval);
        $form->setLabel('Forum_default_approval', dgettext('comments', 'Default approval'));
        $form->setTag('Forum_default_approval', 'DEFAULT_APPROVAL');
        /* Lock Forum */
        $form->addCheckbox('Forum_lock');
        $form->setMatch('Forum_lock', $this->locked);
        $form->setLabel('Forum_lock', dgettext('article', 'Lock this Forum.  Only Moderators can post.'));
        $form->setTag('Forum_lock', 'LOCK');
        /* Save Button */
        $form->addSubmit(dgettext('phpwsbb', 'Save'));
        /* FatCat Category */
        $form->addTplTag('CATEGORY_LABEL', dgettext('phpwsbb', 'Assigned to Category'));
        $result = Categories::getSimpleLinks($this->key_id);
        if (!empty($result)) 
            $form->addTplTag('CATEGORY', implode(', ', $result));
        else
            $form->addTplTag('CATEGORY', dgettext('phpwsbb', 'None'));
        /* Back Link */
        if (!$this->id)
            $form->addTplTag('BACK_LINK', PHPWS_Text::rewriteLink(dgettext('phpwsbb', 'Back to Main Listing'), 'phpwsbb')); 
        else
            $form->addTplTag('BACK_LINK', PHPWS_Text::rewriteLink(dgettext('phpwsbb', 'Back to Forum Listing'), 'phpwsbb', array('view'=>'forum', 'id'=>$this->id))); 
	        
        /* Moderator List */
        // Display list of current moderators
        if (!empty($GLOBALS['Moderators_byForum'][$this->id])) {
            $list = array();
            $vars = array();
            $vars['op'] = 'edit_forum';
            $vars['forum'] = $this->id;
            $count = 0;
            foreach ($GLOBALS['Moderators_byForum'][$this->id] AS $m_id => $m_name) {
                $vars['drop_member'] = $m_id;
                $action = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Drop'), 'phpwsbb', $vars, NULL, dgettext('phpwsbb', 'Drop this moderator from the forum.'));
                if (++$count % 2) 
                    $template['STYLE'] = 'class="bg-light"';
                else 
                    $template['STYLE'] = NULL;
                $template['NAME'] = $m_name;
                $template['ACTION'] = $action;
                $list[] = $template;
            }
            $form->addTplTag('moderator_list', $list);
        }
        $form->addTplTag('CURRENT_MODERATORS_LBL', dgettext('phpwsbb', 'These users are moderators of this forum'));

        // Search for a new moderator's name
        $form->addText('search_member');
        $form->setLabel('search_member', dgettext('phpwsbb', 'Add a Forum Moderator'));
        $form->addSubmit('search', dgettext('phpwsbb', 'Add'));

        // If we did not find a desired user, display a list of all like names
        if (!empty($_SESSION['BBLast_Member_Search'])) {
            $db = new PHPWS_DB('users');
            $db->addColumn('username');
            $db->addColumn('id');
            $db->addWhere('username', '%'.$_SESSION['BBLast_Member_Search'].'%', 'LIKE');
            $db->addOrder('username asc');
            $result = $db->select();
            $list = array();
            $vars = array();
            $vars['op'] = 'edit_forum';
            $vars['forum'] = $this->id;
            $count = 0;
            if (!PHPWS_Error::logIfError($result) && !empty($result)) {
                foreach ($result AS $row) {
                    $vars['add_member'] = $row['id'];
                    $action = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Add'), 'phpwsbb', $vars, NULL, dgettext('phpwsbb', 'Make this user a moderator of this forum.'));
                    if (++$count % 2) 
                        $template['STYLE'] = 'class="bg-light"';
                    else 
                        $template['STYLE'] = NULL;
                    $template['NAME'] = $row['username'];
                    $template['ACTION'] = $action;
                    $list[] = $template;
                }
                $form->addTplTag('suggestion_list', $list);
                $form->addTplTag('SUGGESTION_MESSAGE', sprintf(dgettext('phpwsbb', 'Searching for Member "%s"...'), $_SESSION['BBLast_Member_Search']) . ' ' . dgettext('users', 'Closest matches below.'));
            } 
            else
                $form->addTplTag('SUGGESTION_MESSAGE', sprintf(dgettext('phpwsbb', 'Searching for Member "%s"...'), $_SESSION['BBLast_Member_Search']) . ' ' . dgettext('users', 'No matches found.'));
        }

        $tags = $form->getTemplate();
        return PHPWS_Template::processTemplate($tags, 'phpwsbb', 'edit_forum.tpl');
    }

    /**
     * Updates this forum's information in memory
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return mixed success or array of faliure message strings
     */
    public function update ()
    {
        $error = $message = array();
        if (isset($_REQUEST['Forum_label'])) {
            if(!empty($_REQUEST['Forum_label']))
                $this->title = PHPWS_Text::parseInput($_REQUEST['Forum_label']);
            else
                $error['title'] = dgettext('phpwsbb', 'You must have a name for your forum.');
	
            if(!empty($_REQUEST['Forum_description']))
                $this->description = PHPWS_Text::parseInput($_REQUEST['Forum_description']);
            else
                $error['description'] = dgettext('phpwsbb', 'I need a short description of this forum.');
	
            $this->sortorder = (int) $_REQUEST['Forum_sortorder'];
            $this->allow_anon = (int) !empty($_REQUEST['Forum_allow_anon']);
            $this->default_approval = (int) $_POST['Forum_default_approval'];
            $this->locked = (int) !empty($_REQUEST['Forum_lock']);

            if (!empty($error))
                $GLOBALS['BB_errors'] = $error;
        }
		

        // Moderator Name Search
        if (!empty($_POST['search_member'])) {
            $_SESSION['BBLast_Member_Search'] = preg_replace('/[\W]+/', '', $_POST['search_member']);
            $db = new PHPWS_DB('users_groups');
            $db->addColumn('id');
            $db->addWhere('name', $_SESSION['BBLast_Member_Search']);
            $id = $db->select('one');
            // If we found the desired user, flag them to be added
            if (!PHPWS_Error::logIfError($id) && !empty($id)) 
            	$_REQUEST['add_member'] = $id;
        }

        // Delete a moderator
        if (!empty($_REQUEST['drop_member']) && isset($GLOBALS['Moderators_byUser'][(int) $_REQUEST['drop_member']][$this->id])) {
            $db = new PHPWS_DB('phpwsbb_moderators');
            $db->addWhere('forum_id', $this->id);
            $db->addWhere('user_id', (int) $_REQUEST['drop_member']);
            $result = $db->delete();
        }

        // Add a moderator
        if (!empty($_REQUEST['add_member']) && !isset($GLOBALS['Moderators_byUser'][(int) $_REQUEST['add_member']][$this->id])) {
            $id = (int) $_REQUEST['add_member'];
            $user = & new PHPWS_User($id);
            if ($user->allow('comments')) {
                $db = new PHPWS_DB('phpwsbb_moderators');
                $db->addValue('forum_id', $this->id);
                $db->addValue('user_id', $id);
                $result = $db->insert();
                if ($result && !PHPWS_Error::logIfError($result));
                $GLOBALS['BB_message'] = dgettext('users', 'Moderator added.');
            }
            else 
                $GLOBALS['BB_message'] = dgettext('users', 'This user is not allowed to edit messages.  Moderator not added.');
        }
        // Generate a new list of moderator names
        PHPWSBB_Data::load_moderators(true);
        if (!empty($error))
            return $error;
        return true;
    }

    /**
     * Updates this forum's summary information
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param object $lastthread : Current PHPWSBB_Topic object
     * @return none
     */
    public function update_forum ($commit = false) {
        // Get topic counts
        $db = & new PHPWS_DB('phpwsbb_topics');
        $sql = 'SELECT COUNT(total_posts), SUM(total_posts) FROM phpwsbb_topics WHERE total_posts >0 AND fid = '.$this->id;
        $row = $db->select('row', $sql);
        $this->topics = (int) $row['COUNT(total_posts)'];
        $this->posts = (int) $row['SUM(total_posts)'];
        if ($commit) {
            $sql = 'UPDATE phpwsbb_forums SET topics = '.$this->topics.', posts = '.$this->posts.' WHERE id=' . $this->id;
            PHPWS_Error::logIfError(PHPWS_DB::query($sql));
        }
    }

    /**
     * Saves this object to the database.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return mixed success or faliure message string
     *
     */
    public function save ()
    {
        if(!Current_User::allow('phpwsbb', 'manage_forums')) {
            $message = dgettext('phpwsbb', 'You are not authorized to edit forums.');
            Security::log($message);
            $GLOBALS['BB_message'] = $message;
            return false;
        }

        $db = & new PHPWS_DB('phpwsbb_forums');
        $result = $db->saveObject($this);
        if (PHPWS_Error::logIfError($result)) {
            $GLOBALS['BB_message'] = dgettext('phpwsbb', 'There was an error when saving this forum to the database!');
            return false;
        }

        /* Create/Update this article's Key */
        $update = FALSE;
        if (empty($this->key_id)) 
            {
                $key = & new Key;
                $update = TRUE;
            } 
        else 
            {
                $key = & new Key($this->key_id);
                if (PEAR::isError($key->_error)) {
                    $key = & new Key;
                    $update = TRUE;
                }
            }
        $key->setModule('phpwsbb');
        $key->setItemName('forum');
        $key->setItemId($this->id);
        $key->setEditPermission('manage_forums');
        $key->setUrl('index.php?module=phpwsbb&amp;view=forum&amp;id='.$this->id);
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $key->active = $this->active;
        $result = $key->save();
        $this->key_id = $key->id;
        if ($update) {
            $db1 = & new PHPWS_DB('phpwsbb_forums');
            $db1->addValue('key_id', $this->key_id);
            $db1->addWhere('id', $this->id);
            $db1->update();
        }

    	// Reset all data Caches
        PHPWS_Cache::remove('bb_forumlist');
        PHPWS_Cache::remove('bb_forumIds');
        PHPWS_Cache::remove('bb_forumsblock');
        PHPWS_Cache::remove('bb_latestpostsblock');
        unset($GLOBALS['BBForumTags'][$this->id]);
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
        if (!$this->active)
            return $this->title . ' ['.dgettext('phpwsbb', 'Hidden').']';
        return $this->title;
    }
    
    /**
     * Determines if a user is a listed moderator of a forum
     *
     * @param int $user_id : Id of the user to check
     * @param int $forum_id : Id of the forum to check
     * @return bool : true or false
     */
    public function isModerator ($user_id = null, $forum_id = null)
    {
        if (!$user_id) $user_id = Current_User::getId();
        if (!$forum_id) $forum_id = $this->id;
        if (!isset($GLOBALS['Moderators_byUser'])) {
            PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
            PHPWSBB_Data::load_moderators();
        }
        return isset($GLOBALS['Moderators_byUser'][$user_id][$forum_id]);
    }
	
    /**
     * Determines if a user is a SuperModerator or listed moderator of a forum
     *
     * @param int $user_id : Id of the user to check
     * @param int $forum_id : Id of the forum to check
     * @return bool : true or false
     */
    public function canModerate ($user_id = null, $forum_id = null)
    {
        if ($user_id === 0) // unregistered user
            return false;
        if ($user_id && $user_id != Current_User::getId()) {
            $user = new PHPWS_User($user_id);
            $is_supermod = $user->allow('phpwsbb', 'manage_forums');
            unset($user);
        }
        else {
            $user_id = Current_User::getId();
            $is_supermod = Current_User::allow('phpwsbb', 'manage_forums');
        }
        if (!$forum_id) $forum_id = $this->id;
        return $is_supermod || PHPWSBB_Forum::isModerator($user_id, $forum_id);
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
        if ($this->can_post()) 
            $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Submit a new topic'), 'phpwsbb', array('op'=>'create_topic','forum'=>$this->id));
        if (Current_User::allow('phpwsbb', 'manage_forums')) {
            $link[] = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Edit this Forum'), 'phpwsbb', array('op'=>'edit_forum','forum'=>$this->id));
            if ($this->active)
                $link[] = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Hide this Forum'), 'phpwsbb', array('op'=>'hide_forum','forum'=>$this->id));
            else
                $link[] = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Show this Forum'), 'phpwsbb', array('op'=>'show_forum','forum'=>$this->id));
            $js_var['QUESTION'] = dgettext('phpwsbb', 'This will delete the forum and all topics under it!  Are you sure you want to delete this?');
            $js_var['ADDRESS'] = 'index.php?module=phpwsbb&amp;op=delete_forum&amp;yes=1&amp;forum='.$this->id.'&amp;authkey='.Current_User::getAuthKey();
            $js_var['LINK']    = dgettext('phpwsbb', 'Delete this Forum');
            if (javascriptEnabled()) 
                $link[] = Layout::getJavascript('confirm', $js_var);
            else
                $link[] = sprintf('<a href="./%s" title="%s">%s</a>', str_replace('&amp;yes=1','', $js_var['ADDRESS']), $title, $js_var['LINK']);
        }
        if (!empty($link));
        MiniAdmin::add('phpwsbb', $link);
    }

    /**
     * Determines whether the user can create a topic in this forum
     *
     * @param none
     * @return bool : success or failure
     */
    public function can_post() 
    {
    	$user = Comments::getCommentUser(Current_User::getId());
        return $this->id && !$user->locked && (($this->locked && $this->canModerate())
                                               || (!$this->locked && (Current_User::isLogged() || $this->allow_anon)));
    }

    /**
     * Shows the current user's authorizations
     * 
     * It's pretty much just here for testing purposes.
     * You can get rid of it if you want to.
     * 
     * @param none
     * @return string : success or error object
     */
    public function getStatusTags()
    {
        if ($this->active) {
            $tags['HOME_LINK'] = PHPWS_Text::rewriteLink(dgettext('phpwsbb', 'Bulletin Boards'), 'phpwsbb'); 
            $tags['FORUM_TITLE_LINK'] = PHPWS_Text::rewriteLink($this->title, 'phpwsbb', array('view'=>'forum', 'id'=>$this->id));
            $tags['FORUM_LABEL'] = dgettext('comments', 'Forum');
        }
        if ($this->can_post())
            $list[] = dgettext('comments', 'You <b>can</b> create new topics in this forum');
        else
            $list[] = dgettext('comments', 'You <b>cannot</b> create new topics in this forum');
        if ($this->can_post())
            $list[] = dgettext('comments', 'You <b>can</b> post comments in this forum');
        else
            $list[] = dgettext('comments', 'You <b>cannot</b> post comments in this forum');
        if (Current_User::allow('phpwsbb', 'manage_forums')) 
            $list[] = dgettext('comments', 'You are a <b>Supermoderator</b>');
        if (!empty($GLOBALS['Moderators_byUser'][Current_User::getId()])) {
            $list[] = dgettext('comments', 'You are a listed moderator of the following forums:');
            $list[] = implode(', ', $GLOBALS['Moderators_byUser'][Current_User::getId()]);
        } elseif (Current_User::allow('comments'))
            $list[] = dgettext('comments', 'You <b>are not</b> a listed moderator of any forums');
        if ($this->locked)
            $list[] = dgettext('comments', 'This forum is locked');
        if ($this->allow_anon)
            $list[] = dgettext('comments', 'Unregistered visitors <b>can</b> post in this forum');
        else
            $list[] = dgettext('comments', 'Unregistered visitors <b>cannot</b> post in this forum');
        switch ($this->default_approval) {
        case 1:
            $list[] = dgettext('comments', 'Visitor posts will not be seen until approved');
            break;
        case 2:
            $list[] = dgettext('comments', 'Posts will not be seen until approved');
        }
            
        $tags['FORUM_FLAGS'] = implode("<br />\n", $list);
        return $tags;
    }

    /**
     * Extension of Current_User::allow() that also checks to see if the
     * user is a moderator of the forum 
     *
     * @param string $module : permission module that we're checking for
     * @param string $function : subpermission that we're checking for
     * @return bool : Success or faliure
     */
    public function userCan($module, $function = null)
    {
        return $this->canModerate() && Current_User::allow($module, $function);
    }
    
}  
?>