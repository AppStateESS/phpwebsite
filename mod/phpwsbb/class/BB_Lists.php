<?php
/**
 * This is the PHPWS_BB_Lists class.
 * It contains  public functions to generate various article listings.
 *
 * @version $Id: BB_Lists.php,v 1.3 2008/10/08 17:11:22 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module Article Manager
 */
class PHPWSBB_Lists
{
    /**
     * Lists all forums
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module Article Manager
     * @param none
     * @return none
     */
    public static function list_forums ()
    {
        Layout::addStyle('phpwsbb');
        if (!Current_User::isLogged()) {
            $cachekey = 'phpwsbbForums';
            $s = PHPWS_Cache::get($cachekey);
        }
        if (!empty($s))
        $cat_arr = unserialize($s);
        else {
            // Load all forum objects into an indexed array
            $db = new PHPWS_DB('phpwsbb_forums');
            $db->addOrder('sortorder asc');
            $db->addOrder('title asc');
            if(!Current_User::allow('phpwsbb', 'manage_forums'))
            Key::restrictView($db, 'phpwsbb', false);
            $result = $db->select('col');
            if (PHPWS_Error::logIfError($result) || empty($result))
            return dgettext('phpwsbb', 'There are no available Forums');
            $cat_arr[0]['forums'] = array();
            foreach ($result AS $value)
            $cat_arr[0]['forums'][$value] = new PHPWSBB_Forum($value);

            // Load all forum ids belonging to categories
            $db = new PHPWS_DB('category_items');
            $db->addColumn('phpwsbb_forums.id');
            $db->addColumn('category_items.cat_id');
            $db->addWhere('category_items.module', 'phpwsbb');
            $db->addWhere('phpwsbb_forums.key_id', 'category_items.key_id');
            $db->addWhere('phpws_key.id', 'phpwsbb_forums.key_id');
            $db->addOrder('phpwsbb_forums.sortorder asc');
            $db->addOrder('phpws_key.title asc');
            $result = $db->select();
            if (PHPWS_Error::logIfError($result))
            return PHPWS_Error::printError($result);
            if (!empty($result))
            // Loop through all records...
            foreach ($result AS $value) {
                // moving the forum object to a category-indexed array
                $cat_arr[$value['cat_id']]['forums'][] = $cat_arr[0]['forums'][$value['id']];
                unset($cat_arr[0]['forums'][$value['id']]);
                if (!isset($cat_arr[$value['cat_id']]['category'])) {
                    $category = new Category($value['cat_id']);
                    $a = array('CATEGORY_NAME' => $category->getTitle(),
                                   'CATEGORY_DESCRIPTION' => $category->getDescription(),
                                   'SECTION_TITLE' => dgettext('phpwsbb', 'Section'));
                    if ($category->icon) {
                        $a['CATEGORY_ICON'] = $category->getIcon();
                    }
                    $cat_arr[$value['cat_id']]['category'] = $a;
                }
            }
            // Cache the results for unregistered users
            if (!Current_User::isLogged()) {
                $lifetime = 86400; // number of seconds until cache refresh
                // default is set in CACHE_LIFETIME in the
                // config/core/config.php file
                PHPWS_Cache::save($cachekey, $cat_arr, $lifetime);
            }
        }

        $tpl = new PHPWS_Template('phpwsbb');
        $tpl->setFile('forum_list.tpl');
        // Loop through the category array for the amount of rows
        foreach ($cat_arr AS $key => $value) {
            // List all forums in this category
            // Single category display
            foreach ($value['forums'] AS $forum) {
                $tpl->setCurrentBlock('cat_forum_list');
                $tpl->setData($forum->_get_tags(true));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('cat_list');
            if (isset($value['category']))
            $tpl->setData($value['category']);
            $tpl->parseCurrentBlock();
        }

        $tags['FORUM_TITLE'] = dgettext('phpwsbb', 'Forum');
        $tags['TOPICS_TITLE'] = dgettext('phpwsbb', 'Topics');
        $tags['POSTS_TITLE'] = dgettext('phpwsbb', 'Posts');
        $tags['VIEWS_TITLE'] = dgettext('phpwsbb', 'Views');
        $tags['LASTPOST_TITLE'] = dgettext('phpwsbb', 'Last Post');
        $tpl->setData($tags);
        return $tpl->get();
    }

    /**
     * Searches the topics stored in the database
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module Article Manager
     * @param none
     * @return none
     */
    public static function search_threads ($type, $var = null)
    {
        /* Create DBPager object */
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('phpwsbb_topics', 'PHPWSBB_Topic');
        $pager->setModule('phpwsbb');
        $pager->setTemplate('search_topics.tpl');
        $pager->setCacheIdentifier('search_'.$type);
        $pager->cacheQueries();
        $pager->setDefaultOrder('lastpost_date', 'desc');
        $pager->setDefaultLimit(30);
        $pager->setLimitList(array(30,60,90));
        $pager->setEmptyMessage(dgettext('phpwsbb', 'No Topics found.'));
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('_get_tags');
        $pager->setSearch('title');
        PHPWSBB_Topic::addColumns($pager->db);

        /* Modify WHERE clause to the desired list type */
        switch ($type) {
            case 'since':
                $pager->db->addWhere('lastpost_date', $var, '>=');
                break;

            case 'zerothreads':
                $pager->db->addWhere('total_posts', '2', '<');
                break;

            case 'userthreads':
                $pager->db->addWhere('phpws_key.creator_id', $var);
                break;

            case 'lockedthreads':
                $pager->db->addWhere('locked', '1');
                break;
        }
        $pager->db->addColumn('phpwsbb_forums.title', null, 'forumname');
        $pager->db->addWhere('phpwsbb_forums.id', 'phpwsbb_topics.fid');
        if(!Current_User::allow('phpwsbb', 'manage_forums'))
        $pager->db->addWhere('phpwsbb_forums.id', PHPWSBB_Data::get_forum_ids());
        $pager->addSortHeader('phpws_key.title', dgettext('phpwsbb', 'Topic'));
        $pager->addSortHeader('phpws_key.creator', dgettext('phpwsbb', 'Topic Starter'));
        $pager->addSortHeader('phpws_key.create_date', dgettext('phpwsbb', 'Start Date'));
        $pager->addSortHeader('lastpost_date', dgettext('phpwsbb', 'Last Post'));
        $pager->addSortHeader('total_posts', dgettext('phpwsbb', 'Posts'));
        $pager->addSortHeader('phpws_key.times_viewed', dgettext('phpwsbb', 'Views'));
        $pager->addSortHeader('phpwsbb_forums.title', dgettext('phpwsbb', 'In Forum'));
        $pager->table_columns[] = 'phpws_key.title';
        $pager->table_columns[] = 'phpws_key.creator';
        $pager->table_columns[] = 'phpws_key.times_viewed';
        $pager->table_columns[] = 'phpwsbb_forums.title';
        // Filter out the unapproved topics -- total_posts>0 OR is_phpwsbb=0
        $pager->db->addWhere('phpwsbb_topics.total_posts', 0, '>', null, 'approved_group');
        $pager->db->addWhere('phpwsbb_topics.is_phpwsbb', 0, '=', 'or', 'approved_group');
        return $pager->get();
    }

}
?>