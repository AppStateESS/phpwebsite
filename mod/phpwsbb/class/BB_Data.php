<?php
/**
 * This is the PHPWSBB_Data class.  It contains specialized data access and formatting public functions.
 *
 * @version $Id: BB_Data.php,v 1.1 2008/08/23 04:19:14 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module phpwsBB
 */
class PHPWSBB_Data
{

    /**
     * Formats a date for long format display.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @param int $date : Unix Timestamp.
     * @param string $format : Format to display date in (optional).
     * @return string : Formatted date string.
     */
    public function get_long_date($date, $type=null)
    {
        if (!$type)
        $type = PHPWS_Settings::get('phpwsbb', 'long_date_format');
        if(!is_numeric($date))
        $date = (int) $date;
        return strftime($type, PHPWS_Time::getUserTime($date));
    }

    /**
     * Formats a date for short format display.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @param int $date : Unix Timestamp.
     * @param string $format : Format to display date in (optional).
     * @return string : Formatted date string.
     */
    public function get_short_date($date, $type=null)
    {
        if (!$type)
        $type = PHPWS_Settings::get('phpwsbb', 'short_date_format');
        if(!is_int($date))
        $date = (int) $date;
        return strftime($type, PHPWS_Time::getUserTime($date));
    }

    /**
     * Adds information to the MiniAdmin block.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param none
     * @return none
     */
    public function MiniAdmin()
    {
        $link = array();
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'List Forums'), 'phpwsbb', array());
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'New Posts'), 'phpwsbb', array('op'=>'getnew'));
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Today\'s Posts'), 'phpwsbb', array('op'=>'viewtoday'));
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'This Week\'s Posts'), 'phpwsbb', array('op'=>'viewweek'));
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Locked Topics'), 'phpwsbb', array('op'=>'viewlockedthreads'));
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Empty Topics'), 'phpwsbb', array('op'=>'viewzerothreads'));
        if (Current_User::isLogged())
        $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'My Topics'), 'phpwsbb', array('op'=>'viewuserthreads'));
        if (Current_User::allow('phpwsbb', 'manage_forums')) {
            $link[] = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Add a New Forum'), 'phpwsbb', array('op'=>'create_forum'));
            $link[] = PHPWS_Text::secureLink(dgettext('phpwsbb', 'Admin Settings'), 'phpwsbb', array('op'=>'config'));
        }
        if (!empty($link));
        MiniAdmin::add('phpwsbb', $link);
    }


    /**
     * Parses text for SmartTags and then calls ParseOutput.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param string text : Text to parse.
     * @return string : Parsed text
     */
    public function parseOutput ($text)
    {
        $ignore_list[] = 'phpwsbb';
        //		PHPWS_Text::parseTag($text, null, $ignore_list);
        return PHPWS_Text::parseOutput(PHPWS_Text::parseTag($text, null, $ignore_list));
    }

    /**
     * Generates an indexed list of viewable forums.
     *
     * This list is cached for unregistered users
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @param none
     * @return array
     */
    public static function get_forum_list ($id_only = false)
    {
        $cachekey = 'bb_forumlist';
        if (!Current_User::isLogged()) {
            $s = PHPWS_Cache::get($cachekey);
            if (!empty($s))
            return unserialize($s);
        }
        // Load all forum records
        $db = new PHPWS_DB('phpwsbb_forums');
        $db->addColumn('id');
        $db->addColumn('title');
        $db->addOrder('sortorder asc');
        $db->addOrder('title asc');
        if(!Current_User::allow('phpwsbb', 'manage_forums'))
        Key::restrictView($db, 'phpwsbb', false);
        $result = $db->select();
        if (PHPWS_Error::logIfError($result))
        return null;
        $list = array();
        if (!empty($result))
        foreach ($result AS $row)
        $list[$row['id']] = $row['title'];

        if (!Current_User::isLogged()) {
            $lifetime = 86400; // number of seconds until cache refresh
            // default is set in CACHE_LIFETIME in the
            // config/core/config.php file
            PHPWS_Cache::save($cachekey, $list, $lifetime);
        }
        return $list;
    }

    /**
     * Generates an indexed list of viewable forum ids.
     *
     * This list is cached for unregistered users
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @param none
     * @return array
     */
    public function get_forum_ids ()
    {
        $cachekey = 'bb_forumIds';
        if (!Current_User::isLogged()) {
            $s = PHPWS_Cache::get($cachekey);
            if (!empty($s))
            return unserialize($s);
        }
        // Load all forum records
        $db = new PHPWS_DB('phpwsbb_forums');
        $db->addColumn('id');
        if(!Current_User::allow('phpwsbb', 'manage_forums'))
        Key::restrictView($db, 'phpwsbb', false);
        $result = $db->select('col');
        if (PHPWS_Error::logIfError($result))
        return null;

        // Cache the results for unregistered users
        if (!Current_User::isLogged()) {
            $lifetime = 86400; // number of seconds until cache refresh
            // default is set in CACHE_LIFETIME in the
            // config/core/config.php file
            PHPWS_Cache::save($cachekey, $result, $lifetime);
        }
        return $result;
    }

    /**
     * Adds an "Attatch (or Move) to Forum" link to the MiniAdmin
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param object $object : Item to move.  Can be either a PHPWSBB_Topic or a Key object.
     * @return none
     */
    public function move_item_link (&$object)
    {
        if (!Current_User::allow('phpwsbb', 'move_threads') || !$object_class = get_class($object))
        return;
        // add an "Attatch to Forum" link to the MiniAdmin
        if (javascriptEnabled()) {
            if (strtolower($object_class) == 'key') {
                $js_vars['label'] = dgettext('phpwsbb', 'Assign to a Forum');
                $js_vars['link_title'] = dgettext('phpwsbb', "Add this item's discussion to the phpwsbb Bulletin Board module");
                $vars['key_id'] = $object->id;
            }
            elseif (strtolower($object_class) == 'phpwsbb_topic') {
                $js_vars['label'] = dgettext('phpwsbb', 'Move Topic');
                $js_vars['link_title'] = dgettext('phpwsbb', 'Move this topic to another Forum');
                $vars['topic'] = $object->id;
            }
            else
            return;

            $vars['module'] = 'phpwsbb';
            $vars['op'] = 'move_topic';
            $vars['popup'] = '1';

            $js_vars['width'] = 640;
            $js_vars['height'] = 200;
            $js_vars['address'] = PHPWS_Text::linkAddress('phpwsbb', $vars, TRUE);
            $link = javascript('open_window', $js_vars);
            MiniAdmin::add('phpwsbb', $link);
        } else {
            PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
            $content = PHPWSBB_Forms::assign_forum($object);
            if (!empty($content))
            Layout::add($content, 'phpwsb');
        }
    }

    /**
     * Adds a "Drop from phpwsbb" link to the MiniAdmin
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param int $id : Topic id to drop.
     * @param string $module : Parent module.
     * @param string $item_name : Item type.
     * @return none
     */
    public function drop_item_link ($id, $module, $item_name)
    {
        if (!Current_User::allow('phpwsbb', 'delete_threads'))
        return;
        $js_var['QUESTION'] = sprintf(dgettext('phpwsbb', 'This will erase the topic from the Bulletin Board, but the comments will still be seen when you view the %1$s %2$s!  Are you sure you want to drop this from phpwsBB?'), $module, $item_name);
        $js_var['ADDRESS'] = 'index.php?module=phpwsbb&amp;op=drop_topic&amp;yes=1&amp;topic='.$id.'&amp;authkey='.Current_User::getAuthKey();
        $js_var['LINK']    = dgettext('phpwsbb', 'Drop from phpwsbb');
        if (javascriptEnabled())
        $link[] = Layout::getJavascript('confirm', $js_var);
        else
        $link[] = sprintf('<a href="./%s" title="%s">%s</a>', str_replace('&amp;yes=1','', $js_var['ADDRESS']), $js_var['QUESTION'], $js_var['LINK']);
        MiniAdmin::add('phpwsbb', $link);
    }

    /**
     * Clears a phpwsbb caches
     *
     * @param none
     * @return none
     */
    public function clearCaches()
    {
        PHPWS_Cache::remove('bb_forumlist');
        PHPWS_Cache::remove('bb_forumsblock');
        PHPWS_Cache::remove('bb_latestpostsblock');
        PHPWS_Cache::remove('bb_forum_moderators');

    }

    /**
     * Loads an array containing indexed lists of forum moderators
     *
     * forum_mod_ids : list of moderator user ids by forum
     * forum_mod_names : list of moderator display names by forum
     *
     * user_mod_forum_ids : list of forums that a user is a listed moderator by user
     * user_mod_forum_names : names of forums that a user is a listed moderator by user
     *
     * Each pair of indexes uses the same key value for cross-referencing ability
     *
     * @param $force_reload : Flag to delete cache & reload from database
     * @return array
     */
    public function load_moderators ($force_reload = false)
    {
        if (!$force_reload && isset($GLOBALS['Moderators_byForum']))
        return;

        $cachekey = 'bb_forum_moderators';
        if (!$force_reload) {
            $s = PHPWS_Cache::get($cachekey);
            if (!empty($s)) {
                unserialize($s);
                $GLOBALS['Moderators_byForum'] = $s['byForum'];
                $GLOBALS['Moderators_byUser']  = $s['byUser'];
                return;
            }
        }
        // Load all forum records
        $db = new PHPWS_DB('phpwsbb_moderators');
        $db->addColumn('*');
        $db->addColumn('phpwsbb_forums.title');
        $db->addColumn('users.display_name');
        $db->addWhere('users.id', 'phpwsbb_moderators.user_id');
        $db->addWhere('phpwsbb_forums.id', 'phpwsbb_moderators.forum_id');
        $db->addOrder('phpwsbb_forums.sortorder asc');
        $db->addOrder('phpwsbb_forums.title asc');
        $result = $db->select();
        if (PHPWS_Error::logIfError($result))
        return null;
        $byForum = $byUser = array();
        if (!empty($result))
        foreach ($result AS $row) {
            $byForum[$row['forum_id']][$row['user_id']] = $row['display_name'];
            $byUser[$row['user_id']][$row['forum_id']] = $row['title'];
        }

        if (!Current_User::isLogged()) {
            $lifetime = 86400; // number of seconds until cache refresh
            // default is set in CACHE_LIFETIME in the
            // config/core/config.php file
            PHPWS_Cache::save($cachekey, array('byForum'=>$byForum, 'byUser'=>$byUser), $lifetime);
        }
        $GLOBALS['Moderators_byForum'] = $byForum;
        $GLOBALS['Moderators_byUser']  = $byUser;
    }

    /**
     * Creates a thread comment
     *
     * @param
     * @return array
     */
    public function create_comment ($thread_id, $subject, $entry, $author_id, $anon_name = '', $approved = 1)
    {
        $c_item = new Comment_Item;
        $c_item->thread_id = $thread_id;
        $c_item->setSubject($subject);
        $c_item->setEntry($entry);
        $c_item->stampCreateTime();
        $c_item->author_ip = $_SERVER['REMOTE_ADDR'];
        if ($author_id)
        $c_item->author_id = (int) $author_id;
        elseif (!$c_item->setAnonName($anon_name))
        $c_item->author_id = Current_User::getId();
        $db = new PHPWS_DB('comments_items');
        $result = $db->saveObject($c_item);
        if (PHPWS_Error::logIfError($result) || !$result)
        return false;
        if ($c_item->approved) {
            $result = PHPWS_Error::logIfError($c_item->stampThread());
            if (PHPWS_Error::logIfError($result) || !$result)
            return false;
        }
        return true;
    }


}
?>