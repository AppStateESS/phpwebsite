<?php

define ('DBPAGER_DEFAULT_LIMIT', 10);
define ('DBPAGER_PAGE_LIMIT', 12);
define ('DBPAGER_DEFAULT_EMPTY_MESSAGE', _('No rows found.'));

if (!defined('UTF8_MODE')) {
    define ('UTF8_MODE', false);
}

/**
 * DB Pager differs from other paging methods in that it applies
 * limits and store the object results. Other pagers require you
 * to pull all the data at once.
 * This pager pulls only the data it needs for display.
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

class DBPager {
    /**
     * Name of the class used
     */
    public $class = NULL;

    /**
     * Object rows pulled from DB
     */
    public $display_rows = NULL;

    /**
     * Name of the module using list
     * Needed for template purposes
     */
    public $module = NULL;

    public $toggles = NULL;

    /**
     * Methods the developer wants to run prior to
     * using the object
     */
    public $run_methods = NULL;

    public $run_function = NULL;

    public $toggle_function = null;

    public $toggle_func_number = 0;

    /**
     * List of methods in class
     */
    private $_methods = NULL;

    private $_class_vars = NULL;

    public $table_columns = null;

    public $page_tags = NULL;

    /**
     * Tags set per row by the object
     */
    public $row_tags = NULL;

    public $page_turner_left = '&lt;';

    public $page_turner_right = '&gt;';

    /**
     * Template file name and directory
     */
    public $template = NULL;

    /**
     * Limit of rows to pull from db
     */
    public $limit = NULL;

    public $default_limit = 0;

    public $limitList = array(5, 10, 25);

    public $searchColumn = NULL;

    /**
     * Which column to order by
     */
    public $orderby = NULL;

    public $orderby_dir = NULL;

    /**
     * If set, then this order will be used if no other
     * orders are selected
     */
    public $default_order = NULL;

    public $default_order_dir = 'asc';

    /**
     * DBpager will derive the link from the url
     * If it has problems or you just want to force the link,
     * then you can set the link
     */
    public $link = NULL;

    public $search = NULL;

    /**
     * Total number of rows in database
     */
    public $total_rows = NULL;

    /**
     * Total number of pages needed to display data
     */
    public $total_pages = NULL;

    /**
     * Database object
     */
    public $db = NULL;

    public $current_page = 1;

    /**
     * Message echoed if no rows are found
     */
    public $empty_message = DBPAGER_DEFAULT_EMPTY_MESSAGE;

    /**
     * Template made before processed
     */
    public $final_template = NULL;

    public $error = null;

    public $table = null;

    // Record of the sql query used to pull the rows.
    public $row_query = null;

    public $anchor = null;

    public $sub_result = array();

    public $sub_order = array();

    public $sub_search = false;

    public $total_column = null;

    public $clear_button = false;

    public $search_button = true;

    public $search_label = true;

    public $sort_headers = array();

    public $convert_date = array();

    /**
     * If true, DBPager will cache last user request
     */
    public $cache_queries = false;

    /**
     * If set, DBPager will use a custom identifier for this object's
     * cache instance
     */
    public $cache_identifier = null;

    /**
     * If true, automatically create sort icons
     */
    public $auto_sort = true;

    public function __construct($table, $class=NULL)
    {
        if (empty($table)) {
            $this->error = PHPWS_Error::get(DBPAGER_NO_TABLE, 'core', 'DB_Pager::DBPager');
            return;
        }

        if(isset($_SESSION['DBPager_Last_View'][$table])) {
            unset($_SESSION['DBPager_Last_View'][$table]);
        }

        $this->table = &$table;
        $this->db = & new PHPWS_DB($table);
        $this->table_columns = $this->db->getTableColumns();

        if (PEAR::isError($this->db)){
            $this->error = $this->db;
            $this->db = NULL;
        }

        if (class_exists($class)) {
            $this->class = $class;
            $this->_methods = get_class_methods($class);

            // Remove hidden variables from class variable list
            $class_var_list = array_keys(get_class_vars($class));
            foreach ($class_var_list as $key => $varname) {
                if (substr($varname, 0, 1) == '_') {
                    unset($class_var_list[$key]);
                }
            }
            $this->_class_vars = $class_var_list;
        }

        $this->loadLink();

        if (isset($_REQUEST['change_page'])) {
            $this->current_page = (int)$_REQUEST['change_page'];
        } elseif (isset($_REQUEST['pg'])) {
            if ($_REQUEST['pg'] == 'last') {
                $this->current_page = $_REQUEST['pg'];
            } else {
                $this->current_page = (int)$_REQUEST['pg'];
            }
        }

        if (!$this->current_page) {
            $this->current_page = 1;
        }

        if (isset($_REQUEST['limit']) && $_REQUEST['limit'] > 0) {
            $this->limit = (int)$_REQUEST['limit'];
        }

        if (isset($_REQUEST['orderby'])) {
            $this->orderby = preg_replace('/[^\w.]/', '', $_REQUEST['orderby']);
        }

        if (isset($_REQUEST['orderby_dir'])) {
            $this->orderby_dir = preg_replace('/\W/', '', $_REQUEST['orderby_dir']);
        }

        if (isset($_REQUEST['pager_c_search'])) {
            if (!empty($_REQUEST['pager_c_search'])) {
                $this->loadSearch($_REQUEST['pager_c_search']);
                $this->current_page = 1;
            } else {
                $this->search = NULL;
            }
        } elseif (isset($_REQUEST['pager_search'])) {
            $this->loadSearch($_REQUEST['pager_search']);
        }

    }

    public function joinResult($source_column, $join_table, $join_column, $content_column, $new_name=null, $searchable=false)
    {
        static $index = 1;

        if ($searchable) {
            $this->sub_search = true;
        }

        if (empty($new_name)) {
            $new_name = $content_column;
        }

        $this->sub_result['dbp' . $index] = array('sc' => $source_column,
                                                  'jt' => $join_table,
                                                  'jc' => $join_column,
                                                  'cc' => $content_column,
                                                  'nn' => $new_name,
                                                  'srch' => (bool)$searchable);

        $this->sub_order[$new_name] = array('dbp' . $index, $content_column);
        $this->table_columns[$new_name] = $new_name;
        $index++;
    }

    public function loadSearch($search)
    {
        if (UTF8_MODE) {
            $preg = '/[^\w\s\pL]/u';
        } else {
            $preg = '/[^\w\s]/u';
        }
        $search = preg_replace($preg, '', trim($search));
        $search = preg_replace('/\s{2,}/', ' ', $search);
        $this->search = & $search;
    }

    public function loadLink()
    {
        $this->link = PHPWS_Core::getCurrentUrl(TRUE, FALSE);
    }

    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }

    public function getAnchor()
    {
        if (empty($this->anchor)) {
            return null;
        } else {
            return '#' . $this->anchor;
        }
    }

    /**
     * Sets the default order for the pager. If only_if_empty is TRUE
     * then a sort can overwrite the direction.
     */
    public function setOrder($column, $direction='asc', $only_if_empty=false)
    {
        if ($only_if_empty && !empty($this->orderby)) {
            return;
        }
        $this->orderby =  preg_replace('/[^\w\.]/', '', $column);
        if (!preg_match('/asc|desc/i', $direction)) {
            $direction = 'asc';
        }
        $this->orderby_dir = $direction;
    }

    public function setDefaultOrder($default_order, $direction='asc')
    {
        if (preg_match('/\W/', $default_order)) {
            return FALSE;
        }
        $this->default_order = $default_order;
        if ($direction != 'asc' && $direction != 'desc') {
            return FALSE;
        }
        $this->default_order_dir = $direction;
        return TRUE;
    }

    public function setDefaultLimit($limit)
    {
        $this->default_limit = (int)$limit;
    }

    public function setSearch()
    {
        $col_list = func_get_args();

        foreach ($col_list as $column) {
            if (UTF8_MODE) {
                $preg = '/[^\.\w\pL]/u';
            } else {
                $preg = '/[^\.\w]/u';
            }

            if (!preg_match($preg, $column) && $this->db->isTableColumn($column)) {
                $this->searchColumn[] = $column;
            }
        }
    }

    public function setPageTurnerLeft($turner)
    {
        $this->page_turner_left = $turner;
    }

    public function setPageTurnerRight($turner)
    {
        $this->page_turner_right = $turner;
    }

    public function setLimitList($list)
    {
        if (!is_array($list)) {
            return FALSE;
        }

        $this->limitList = $list;
    }

    public function addToggle($toggle)
    {
        $this->toggles[] = $toggle;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getLinkQuery()
    {
        return substr(strstr($this->link, '?'), 1);
    }

    public function getLinkBase()
    {
        return str_replace(strstr($this->link, '?'), '', $this->link);
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Allows the developer to add extra or processes row tags
     *
     */
    public function addRowTags()
    {
        $method = func_get_arg(0);

        if (empty($this->class)) {
            return FALSE;
        }

        if (func_num_args() < 1) {
            return FALSE;
        }

        if (version_compare(phpversion(), '5.0.0',  '<')) {
            $method = strtolower($method);
        }

        if (func_num_args() > 1) {
            $variables = func_get_args();
            //strip the method
            array_shift($variables);
        } else {
            $variables = NULL;
        }

        $this->row_tags = array('method'=>$method, 'variable'=>$variables);
    }

    public function setEmptyMessage($message)
    {
        $this->empty_message = $message;
    }

    public function addToggleFunction($function, $toggle=2)
    {
        if (empty($function) || $toggle < 2) {
            return false;
        }

        $this->toggle_func_number = (int)$toggle;

        if (is_string($function) && function_exists($function)) {
            $this->toggle_function = $function;
            return true;
        } elseif( is_array($function) && class_exists($function[0]) ) {
            if (version_compare(phpversion(), '5.0.0',  '<')) {
                $method = strtolower($function[1]);
            } else {
                $method = & $function[1];
            }

            if ( in_array($method, get_class_methods($function[0])) ) {
                $this->toggle_function = $function;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Adds a function or static method call to pager
     */
    public function addRowFunction($function)
    {
        if (is_string($function) && function_exists($function)) {
            $this->run_function = $function;
            return true;
        } elseif( is_array($function) && class_exists($function[0]) ) {
            if (version_compare(phpversion(), '5.0.0',  '<')) {
                $method = strtolower($function[1]);
            } else {
                $method = & $function[1];
            }

            if ( in_array($method, get_class_methods($function[0])) ) {
                $this->run_function = $function;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function addRunMethod($method)
    {
        if (!in_array(strtolower($method), $this->_methods)) {
            return;
        }

        $this->run_methods[] = $method;
    }

    public function addWhere($column, $value, $operator=NULL, $conj=NULL, $group=NULL)
    {
        return $this->db->addWhere($column, $value, $operator, $conj, $group);
    }

    public function addPageTags($tags)
    {
        $this->page_tags = $tags;
    }

    public function getLimit()
    {
        if (empty($this->limit) || !in_array($this->limit, $this->limitList)) {
            list($this->limit) = each($this->limitList);
        }

        $start = ($this->current_page - 1) * $this->limit;
        return array((int)$this->limit, (int)$start);
    }

    public function setTotalColumn($column)
    {
        if (!strstr($column, '.')) {
            $table = $this->db->getTable();
            if ($table) {
                $this->total_column = $table . '.' . trim($column);
            }
        } else {
            $this->total_column = trim($column);
        }
    }

    public function getTotalRows()
    {
        if (isset($this->error)) {
            return;
        }

        /**
         * if total_column is set, use it to get total rows
         */
        if ($this->total_column) {
            $order    = $this->db->order;
            $columns  = $this->db->columns;
            $group_by = $this->db->group_by;
            $this->db->group_by = $this->db->order = $this->db->columns = null;
            $this->db->addColumn($this->total_column, null, null, true, true);
            $result = $this->db->select('one');
            $this->db->columns  = $columns;
            $this->db->order    = $order;
            $this->db->group_by = $group_by;
            return $result;
        } else {
            /**
             * If total_column is not set check number of tables
             */
            if (count($this->db->tables) > 1) {
                /**
                 * if more than one table, go through each and look for an index.
                 * if an index is found, set it as the total_column and recursively
                 * call this function.
                 */
                foreach ($this->db->tables as $table) {
                    if ($index = $this->db->getIndex($table)) {
                        $this->total_column = $table . '.' . $index;
                        return $this->getTotalRows();
                    }
                }

                /**
                 * An index could not be found, use full count method to return
                 * row count.
                 */
                return $this->fullRowCount();
            } else {
                /**
                 * There is only one table. See if it has an index
                 */
                if ($index = $this->db->getIndex()) {
                    /**
                     * An index was found, set as total_column and recursively
                     * call this function
                     */
                    $table = $this->db->getTable(false);
                    $this->total_column = $table . '.' . $index;
                    return $this->getTotalRows();
                } else {
                    /**
                     * An index could not be found, use full count method to return
                     * row count.
                     */
                    return $this->fullRowCount();
                }
            }
        }

    }

    /**
     * Calls a count on *. Less reliable than counting on one column.
     * A fallback method for getTotalRows
     */
    public function fullRowCount()
    {
        $this->db->setDistinct(TRUE);
        $order    = $this->db->order;
        $columns  = $this->db->columns;
        $group_by = $this->db->group_by;
        $this->db->columns = null;
        $result = $this->db->select('count');
        $this->db->columns = $columns;
        $this->db->order   = $order;
        $this->db->group_by = $group_by;
        return $result;
    }

    public function getRows()
    {
        return $this->display_rows;
    }

    /**
     * Pulls the appropriate rows from the data base.
     *
     * This function pulls the database information then plugs
     * the data it gets into the object.
     * @modified Eloi George
     */
    public function initialize($load_rows=true)
    {
        if (empty($this->cache_identifier))
            $this->cache_identifier = $this->template;
        if (empty($this->limit) && empty($this->orderby) &&
            empty($this->search) && empty($this->orderby) &&
            isset($_SESSION['DB_Cache'][$this->module][$this->cache_identifier])) {
            extract($_SESSION['DB_Cache'][$this->module][$this->cache_identifier]);
            $this->limit        = $limit;
            $this->orderby      = $orderby;
            $this->orderby_dir  = $orderby_dir;
            $this->search       = $search;
            $this->current_page = $current_page;
        }

        if (isset($this->error)) {
            return $this->error;
        }

        if ($this->search) {
            $search = preg_replace('/\s/', '|', $this->search);
        } else {
            $search = null;
        }

        if (!empty($this->sub_result)) {
            foreach ($this->sub_result as $sub_table => $sub) {
                $this->db->addTable($sub['jt'], $sub_table);
                $this->db->addJoin('left', $this->table, $sub_table, $sub['sc'], $sub['jc']);

                if (!empty($search)) {
                    if ($sub['srch']) {
                        $col = $sub_table . '.' . $sub['cc'];
                        $this->db->addWhere($col, $search, 'regexp', 'or', 1);
                    }
                }
            }
        }

        if (!empty($search) && isset($this->searchColumn)) {
            foreach ($this->searchColumn as $column_name) {
                $this->db->addWhere($column_name, $search, 'regexp', 'or', 1);
            }
        }

        $count = $this->getTotalRows();

        $this->db->setDistinct(TRUE);
        if (!empty($this->sub_result)) {
            $this->db->addColumn('*');
            foreach ($this->sub_result as $sub_table => $sub) {
                $this->db->addColumn($sub_table . '.' . $sub['cc'], null, $sub['nn']);
            }
        }

        if (PEAR::isError($count)) {
            return $count;
        }

        if (empty($this->limit)) {
            if ($this->default_limit)
                $this->limit = $this->default_limit;
            else
                $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        if ($this->limit > 0) {
            $this->db->setLimit($this->getLimit());
        }

        $this->total_rows = &$count;
        $this->total_pages = ceil($this->total_rows / $this->limit);

        if ($this->current_page > $this->total_pages || $this->current_page == 'last') {
            $this->current_page = $this->total_pages;
            $this->db->setLimit($this->getLimit());
        }

        if (isset($this->orderby)) {
            $sub_order = @$this->sub_order[$this->orderby];
            if (!empty($sub_order)) {
                $orderby = implode('.', $sub_order);
            } else {
                $orderby = $this->orderby;
            }
            $this->db->addOrder($orderby . ' ' . $this->orderby_dir);
        } elseif (isset($this->default_order)) {
            $this->db->addOrder($this->default_order . ' ' . $this->default_order_dir);
        }

        if (!$load_rows) {
            return true;
        }

        if (empty($this->class)) {
            $result = $this->db->select();
        } else {
            $result = $this->db->getObjects($this->class);
        }

        $this->row_query = $this->db->lastQuery();

        if (PEAR::isError($result)) {
            return $result;
        }

        $this->display_rows = &$result;

        if ($this->cache_queries) {
            $cache['limit']        = $this->limit;
            $cache['orderby']      = $this->orderby;
            $cache['orderby_dir']  = $this->orderby_dir;
            $cache['search']       = $this->search;
            $cache['current_page'] = $this->current_page;

            $_SESSION['DB_Cache'][$this->module][$this->cache_identifier] = $cache;
        } else {
            $this->clearQuery();
        }
        return TRUE;
    }

    public function getPageLinks()
    {
        if ($this->total_pages < 1) {
            $current_page = $total_pages = 1;
        } else {
            $current_page = $this->current_page;
            $total_pages = $this->total_pages;
        }

        if ($total_pages == 1) {
            return '[1]';
        }

        $values = $this->getLinkValues();
        unset($values['pg']);

        $module = $values['module'];
        unset($values['module']);

        $anchor = $this->getAnchor();
        if ($anchor) {
            $values['#'] = $anchor;
        }

        // page one
        if ($current_page != 1) {
            if ($total_pages > 500 && $current_page > 50) {
                $values['pg'] = $current_page - 50;
                $pageList[] = PHPWS_Text::moduleLink('&lt;&lt;&lt;', $module, $values, null, _('Back 50 pages'));
            }

            if ($total_pages > 100 && $current_page > 10) {
                $values['pg'] = $current_page - 10;
                $pageList[] = PHPWS_Text::moduleLink('&lt;&lt;', $module, $values, null, _('Back 10 pages'));
            }
            $values['pg'] = $current_page - 1;
            $pageList[] = PHPWS_Text::moduleLink('&lt;', $module, $values, null, _('Back one page'));
            $values['pg'] = 1;
            $pageList[] = PHPWS_Text::moduleLink('1', $module, $values, null, _('First page'));
        } else {
            $pageList[] = '[1]';
        }


        if ($total_pages > DBPAGER_PAGE_LIMIT) {
            // break up pages
            $divider = floor(DBPAGER_PAGE_LIMIT / 2);
            if ($current_page <= $divider) {
                $divider = DBPAGER_PAGE_LIMIT - 2;
                if ($current_page != 1) {
                    $divider--;
                    for ($i=2; $i < $current_page; $i++) {
                        if ($i != $current_page) {
                            $values['pg'] = 1;
                            $pageList[] = PHPWS_Text::moduleLink($i, $module, $values, null, sprintf(_('Go to page %s'), $i));
                        } else {
                            $pageList[] = "[$current_page]";
                        }
                        $divider--;
                    }
                }
                $remaining_pages = $total_pages - $current_page;
                $skip = floor($remaining_pages / $divider);

                for ($i=0,$j = $current_page + $skip; $i < $divider; $i++,$j += $skip) {
                    $values['pg'] = $j;
                    $pageList[] = PHPWS_Text::moduleLink($j, $module, $values, null, sprintf(_('Go to page %s'), $j));
                }
            } else {
                $beginning_pages = $current_page - 1;
                $remaining_pages = $total_pages - $current_page;

                if ($remaining_pages < $divider) {
                    if (!$remaining_pages) {
                        $divider *= 2;
                        $front_skip = floor($total_pages / (DBPAGER_PAGE_LIMIT - 1));
                        $back_skip = 0;
                    } else {
                        $divider += $remaining_pages;
                        $front_skip = floor($beginning_pages / $divider);
                        $back_skip = 1;
                    }
                } else {
                    $front_skip = round($beginning_pages / $divider);
                    $back_skip = round($remaining_pages / $divider);
                }
                for ($i=0,$j = 1 + $front_skip; $i < $divider - 1 && $j < $current_page; $i++,$j += $front_skip) {
                    $values['pg'] = $j;
                    $pageList[] = PHPWS_Text::moduleLink($j, $module, $values, null, sprintf(_('Go to page %s'), $j));
                }

                $pageList[] = "[$current_page]";

                if ($back_skip) {
                    for ($i=0,$j = $current_page + $back_skip; $i < $divider - 1 && $j < $total_pages; $i++,$j += $back_skip) {
                        $values['pg'] = $j;
                        $pageList[] = PHPWS_Text::moduleLink($j, $module, $values, null, sprintf(_('Go to page %s'), $j));
                    }
                }
            }

        } else {
            for($i=2; $i < $total_pages; $i++) {
                if ($i == $current_page) {
                    $pageList[] = "[$i]";
                } else {
                    $values['pg'] = $i;
                    $pageList[] = PHPWS_Text::moduleLink($i, $module, $values, null, sprintf(_('Go to page %s'), $i));
                }
            }
        }

        if ($total_pages != $current_page) {
            $values['pg'] = $total_pages;
            $pageList[] = PHPWS_Text::moduleLink($total_pages, $module, $values, null, _('Last page'));

            $values['pg'] = $current_page + 1;
            $pageList[] = PHPWS_Text::moduleLink('&gt;', $module, $values, null, _('Forward one page'));

            if ($total_pages > 100 && ($total_pages - 10) >= $current_page) {
                $values['pg'] = $current_page + 10;
                $pageList[] = PHPWS_Text::moduleLink('&gt;&gt;', $module, $values, null, _('Forward 10 pages'));
            }

            if ($total_pages > 500 && ($total_pages - 50) >= $current_page) {
                $values['pg'] = $current_page + 50;
                $pageList[] = PHPWS_Text::moduleLink('&gt;&gt;&gt;', $module, $values, null, _('Forward 50 pages'));
            }
        } else {
            $pageList[] = "[$current_page]";
        }

        return implode(' ', $pageList);
    }

    /**
     * Returns the sorting buttons for table columns
     */
    public function getSortButtons(&$template)
    {
        if (empty($this->table_columns)) {
            return NULL;
        }

        if ($this->auto_sort) {
            $sort_columns = & $this->table_columns;
        } else {
            $sort_columns = array_keys($this->sort_headers);
        }

        foreach ($sort_columns as $varname) {
            $vars = array();
            $values = $this->getLinkValues();

            $module = $values['module'];
            unset($values['module']);

            $anchor = $this->getAnchor();
            if ($anchor) {
                $values['#'] = $anchor;
            }

            $buttonname = str_replace('.', '_', $varname) . '_SORT';

            $values['orderby'] = $varname;

            if ($this->orderby == $varname){
                if ($this->orderby_dir == 'desc'){
                    unset($values['orderby_dir']);
                    $alt = _('Sorted in descending order');
                    $button = sprintf('<img src="images/core/list/down_pointer.png" border="0" alt="%s" title="%s" />', $alt, $alt);
                } elseif ($this->orderby_dir =="asc") {
                    $alt = _('Sorted in ascending order');
                    $values['orderby_dir'] = 'desc';
                    $button = sprintf('<img src="images/core/list/up_pointer.png" border="0" alt="%s" title="%s" />', $alt, $alt);
                } else {
                    $alt = _('Unsorted');
                    $button = sprintf('<img src="images/core/list/sort_none.png" border="0"  alt="%s" title="%s" />', $alt, $alt);
                    $values['orderby_dir'] = 'asc';
                }

            } else {
                $alt = _('Unsorted');
                $button = sprintf('<img src="images/core/list/sort_none.png" border="0"  alt="%s" title="%s" />', $alt, $alt);
                $values['orderby_dir'] = 'asc';
            }

            if (isset($this->sort_headers[$varname])) {
                $button .= '&nbsp;' . $this->sort_headers[$varname];
            }
            $link = PHPWS_Text::moduleLink($button, $module, $values, null, $alt);

            $template[strtoupper($buttonname)] = $link;
        }

        return $template;
    }

    public function addSortHeader($header, $title)
    {
        $this->sort_headers[$header] = $title;
    }

    public function getLinkValues()
    {
        $output = NULL;
        if (isset($GLOBALS['DBPager_Link_Values'])) {
            return $GLOBALS['DBPager_Link_Values'];
        }

        if (empty($this->limit)) {
            $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        $values['pg'] = $this->current_page;
        $values['limit'] = $this->limit;

        if (!empty($this->search)) {
            $values['pager_search'] = $this->search;
        }

        if (isset($this->orderby)) {
            $values['orderby'] = $this->orderby;
            if (isset($this->orderby_dir))
                $values['orderby_dir'] = $this->orderby_dir;
        }

        // pull get values from link setting
        if (!empty($this->link)) {
            $url = parse_url($this->link);
            if (isset($url['query'])) {
                parse_str(str_replace('&amp;', '&', $url['query']), $output);
            }
        }

        // pull any extra values in current url
        $extra = PHPWS_Text::getGetValues();
        $search_val = & $extra['search'];
        if (UTF8_MODE) {
            $preg = '/[^\w\s\pL]/u';
        } else {
            $preg = '/[^\w\s]/u';
        }

        $search_val = preg_replace($preg, '', $search_val);
        $search_val = preg_replace('/\s/', '+', $search_val);

        // if extra values exist, add them to the values array
        // ignore matches in the output and other values
        if (!empty($extra)) {
            if ($output) {
                $diff = array_diff_assoc($extra, $output);
            } else {
                $diff = $extra;
            }

            $diff = array_diff_assoc($diff, $values);

            $values = array_merge($diff, $values);
        }

        if ($output) {
            $values = array_merge($output, $values);
        }

        // prevents a doubling of the value in the page form
        unset($values['change_page']);
        unset($values['pager_c_search']);
        // Don't need the Go button from search to be carried along
        unset($values['go']);

        $GLOBALS['DBPager_Link_Values'] = $values;

        return $values;
    }


    public function getLimitList()
    {
        $values = $this->getLinkValues();
        unset($values['limit']);

        $module = $values['module'];
        unset($values['module']);

        $anchor = $this->getAnchor();
        if ($anchor) {
            $values['#'] = $anchor;
        }

        foreach ($this->limitList as $limit) {
            if ($limit == $this->limit) {
                $links[] = $limit;
            } else {
                $values['limit'] = & $limit;
                $links[] = PHPWS_Text::moduleLink($limit, $module, $values, null, sprintf(_('Limit results to %s rows'), $limit));
            }
        }

        return implode(' ', $links);
    }


    /**
     * Pulls variables from the object results. Calls object's formatting function if
     * specified.
     */
    public function getPageRows()
    {
        $template = null;
        $count = 0;

        if (!isset($this->display_rows)) {
            return NULL;
        }

        foreach ($this->display_rows as $disp_row) {
            if (!empty($this->convert_date)) {
                foreach ($this->convert_date as $key=>$format) {
                    if ($this->class && isset($disp_row->$key)) {
                        $disp_row->$key = strftime($format, $disp_row->$key);
                    } elseif (isset($disp_row[$key])) {
                        $disp_row[$key] = strftime($format, $disp_row[$key]);
                    }
                }
            }
            if (isset($this->class) && isset($this->run_methods)){
                foreach ($this->run_methods as $run_function) {
                    call_user_func(array($disp_row, $run_function));
                }
            }

            if (isset($this->class)) {
                foreach ($this->_class_vars as $varname) {
                    $template[$count][strtoupper($varname)] = $disp_row->$varname;
                }

                if (!empty($this->row_tags)) {
                    if (!in_array($this->row_tags['method'], $this->_methods)) {
                        return PHPWS_Error::get(DBPAGER_NO_METHOD, 'core', 'DBPager::getPageRows', $this->class . ':' . $this->row_tags['method']);
                    }

                    if (empty($this->row_tags['variable'])) {
                        $row_result = call_user_func(array($disp_row, $this->row_tags['method']));
                    } else {
                        $row_result = call_user_func_array(array($disp_row, $this->row_tags['method']), $this->row_tags['variable']);
                    }

                    if (!empty($row_result) && is_array($row_result)) {
                        $template[$count] = array_merge($template[$count], $row_result);
                    }
                }

            } else {
                foreach ($disp_row as $key => $value) {
                    $template[$count][strtoupper($key)] = $value;
                }

                if(isset($this->run_function)) {
                    $row_result = call_user_func($this->run_function, $disp_row);
                    if (!empty($row_result) && is_array($row_result)) {
                        $template[$count] = array_merge($template[$count], $row_result);
                    }
                }
            }

            if (isset($this->toggle_function)) {
                if (!($count % $this->toggle_func_number)) {
                    $row_result = call_user_func($this->toggle_function, $disp_row);
                    if (!empty($row_result)) {
                        $template[$count] = array_merge($template[$count], $row_result);
                    }
                }
            }

            $count++;
        }

        return $template;
    }

    public function getPageDrop()
    {
        if (empty($this->total_pages)) {
            $page_list[1] = 1;
        } else {
            for ($i = 1; $i <= $this->total_pages; $i++) {
                $page_list[$i] = $i;
            }
        }

        $form = & new PHPWS_Form('page_list');
        $form->setMethod('get');
        $values = $this->getLinkValues();
        $form->addHidden($values);
        $form->addSelect('change_page', $page_list);
        $form->setExtra('change_page', 'onchange="this.form.submit()"');
        $form->setMatch('change_page', $this->current_page);

        if (!function_exists('javascriptEnabled') || !javascriptEnabled()) {
            $form->addSubmit('go', _('Go'));
        }

        $template = $form->getTemplate();

        if (PEAR::isError($template)) {
            PHPWS_Error::log($template);
            return NULL;
        }

        return implode("\n", $template);
    }


    public function getSearchBox()
    {
        $form = new PHPWS_Form('search_list');
        $form->setMethod('get');
        $values = $this->getLinkValues();
        unset($values['pager_search']);
        unset($values['go']);
        $form->addHidden($values);
        $form->addText('pager_c_search', $this->search);
        $form->setSize('pager_c_search', 20);
        if ($this->search_label) {
            $form->setLabel('pager_c_search', _('Search'));
        }

        if ($this->clear_button) {
            $form->addButton('clear', _('Clear'));
            $form->setExtra('clear', 'onclick="this.form.search_list_pager_c_search.value=\'\'"');
        }

        if ($this->search_button) {
            $form->addSubmit('go', _('Search'));
        }

        $template = $form->getTemplate();
        if (PEAR::isError($template)) {
            PHPWS_Error::log($template);
            return NULL;
        }

        return implode("\n", $template);
    }

    public function _getNavigation(&$template)
    {
        if ($this->total_rows < 1) {
            $total_row = $start_row = $end_row = 1;
        } else {
            $total_row = $this->total_rows;
            $start_row = ( ($this->current_page - 1) * $this->limit ) + 1;
            $end_row   = $this->current_page * $this->limit;
            if ($end_row > $total_row)
                $end_row = $total_row;
        }

        $pages = $this->getPageLinks();

        if (PEAR::isError($pages)) {
            return $pages;
        }

        $template['PAGES']       = $pages;
        $template['PAGE_LABEL']  = _('Page');
        $template['LIMIT_LABEL'] = _('Limit');
        $template['PAGE_DROP']   = $this->getPageDrop();
        $template['TOTAL_ROWS']  = sprintf(_('%s - %s of %s'), $start_row, $end_row, $total_row);
        $template['LIMITS']      = $this->getLimitList();

        if (isset($this->searchColumn) || $this->sub_search) {
            $template['SEARCH'] = $this->getSearchBox();
        }
    }

    /**
     * Returns the content of the the pager object
     */
    public function get($return_blank_results=TRUE)
    {
        $template = array();

        if (empty($this->display_rows)) {
            $result = $this->initialize();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if (!isset($this->module)) {
            return PHPWS_Error::get(DBPAGER_MODULE_NOT_SET, 'core', 'DBPager::get');
        }

        if (!isset($this->template)) {
            return PHPWS_Error::get(DBPAGER_TEMPLATE_NOT_SET, 'core', 'DBPager::get');
        }

        $rows = $this->getPageRows();

        if (PEAR::isError($rows)) {
            return $rows;
        }

        if (isset($this->toggles)) {
            $max_tog = count($this->toggles);
        }

        $count = 0;
        $this->_getNavigation($template);
        $this->getSortButtons($template);

        if (isset($rows)) {
            foreach ($rows as $rowitem){
                if (isset($max_tog)) {
                    if ($max_tog == 1) {
                        if ($count % 2) {
                            $rowitem['TOGGLE'] = $this->toggles[0];
                        } else {
                            $rowitem['TOGGLE'] = NULL;
                        }
                        $count++;
                    } else {
                        $rowitem['TOGGLE'] = $this->toggles[$count];
                        $count++;

                        if ($count >= $max_tog) {
                            $count = 0;
                        }
                    }
                } else {
                    $rowitem['TOGGLE'] = NULL;
                }

                $template['listrows'][] = $rowitem;
            }


        } elseif(!$return_blank_results) {
            return NULL;
        } else {
            $template['EMPTY_MESSAGE'] = $this->empty_message;
        }

        DBPager::plugPageTags($template);
        $this->final_template = & $template;
        return PHPWS_Template::process($template, $this->module, $this->template);
    }

    public function getFinalTemplate()
    {
        return $this->final_template;
    }

    public function plugPageTags(&$template){
        if (isset($this->page_tags)){
            foreach ($this->page_tags as $key=>$value)
                $template[$key] = $value;
        }
    }

    public function saveLastView()
    {
        $_SESSION['DBPager_Last_View'][$this->table] = PHPWS_Core::getCurrentUrl();
    }

    public function getLastView($table)
    {
        if (isset($_SESSION['DBPager_Last_View'][$table])) {
            return $_SESSION['DBPager_Last_View'][$table];
        } else {
            return null;
        }
    }

    public function convertDate($column_name, $format='%c')
    {
        $this->convert_date[$column_name] = $format;
    }

    public function clearQuery()
    {
        if (isset($_SESSION['DB_Cache'])) {
            unset($_SESSION['DB_Cache'][$this->module][$this->template]);
        }
    }

    public function cacheQueries($cache=true)
    {
        $this->cache_queries = (bool)$cache;
    }

    public function setCacheIdentifier($str)
    {
        $this->cache_identifier = $str;
    }

    public function setAutoSort($auto)
    {
        $this->auto_sort = (bool)$auto;
    }
}

?>