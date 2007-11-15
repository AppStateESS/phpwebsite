<?php

define ('DBPAGER_DEFAULT_LIMIT', 10);
define ('DBPAGER_PAGE_LIMIT', 12);
define ('DBPAGER_DEFAULT_EMPTY_MESSAGE', _('No rows found.'));

/**
 * DB Pager differs from other paging methods in that it applies
 * limits and store the object results. Other pagers require you
 * to pull all the data at once. 
 * This pager pulls only the data it needs for display.
 *
 * @version $Id$
 * @author  Matt McNaney <matt@NOSPAM.tux.appstate.edu>
 * @package Core
 */

class DBPager {
    /**
     * Name of the class used
     */
    var $class = NULL;

    /**
     * Object rows pulled from DB
     */
    var $display_rows = NULL;

    /**
     * Name of the module using list
     * Needed for template purposes
     */ 
    var $module = NULL;

    var $toggles = NULL;

    /**
     * Methods the developer wants to run prior to
     * using the object
     */
    var $run_methods = NULL;

    var $run_function = NULL;

    var $toggle_function = null;
    
    var $toggle_func_number = 0;

    /**
     * List of methods in class
     */  
    var $_methods = NULL;

    var $_class_vars = NULL;

    var $table_columns = null;

    var $page_tags = NULL;

    /**
     * Tags set per row by the object
     */
    var $row_tags = NULL;

    var $page_turner_left = '&lt;';

    var $page_turner_right = '&gt;';

    /**
     * Template file name and directory
     */
    var $template = NULL;

    /**
     * Limit of rows to pull from db
     */
    var $limit = NULL;

    var $limitList = array(5, 10, 25);

    var $searchColumn = NULL;

    /**
     * Which column to order by
     */
    var $orderby = NULL;

    var $orderby_dir = NULL;

    /**
     * If set, then this order will be used if no other
     * orders are selected
     */ 
    var $default_order = NULL;

    var $default_order_dir = 'asc';

    /**
     * DBpager will derive the link from the url
     * If it has problems or you just want to force the link,
     * then you can set the link
     */
    var $link = NULL;

    var $search = NULL;

    /**
     * Total number of rows in database
     */
    var $total_rows = NULL;

    /**
     * Total number of pages needed to display data
     */
    var $total_pages = NULL;

    /**
     * Database object
     */
    var $db = NULL;

    var $current_page = 1;

    /**
     * Message echoed if no rows are found
     */
    var $empty_message = DBPAGER_DEFAULT_EMPTY_MESSAGE;

    /**
     * Template made before processed
     */
    var $final_template = NULL;

    var $error = null;

    var $table = null;

    // Record of the sql query used to pull the rows.
    var $row_query = null;

    var $anchor = null;

    var $sub_result = array();

    var $sub_order = array();

    function DBPager($table, $class=NULL)
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
        $this->db->setDistinct(TRUE);
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
            $this->current_page = (int)$_REQUEST['pg'];
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
                $this->search = preg_replace('/\W/', '', $_REQUEST['pager_c_search']);
                $this->current_page = 1;
            } else {
                $this->search = NULL;
            }
        } elseif (isset($_REQUEST['pager_search'])) {
            $this->search = preg_replace('/\W/', '', $_REQUEST['pager_search']);
        }
    }

    function joinResult($source_column, $join_table, $join_column, $content_column, $new_name)
    {
        static $index = 1;

        $this->sub_result['dbp' . $index] = array('sc' => $source_column,
                                                  'jt' => $join_table,
                                                  'jc' => $join_column,
                                                  'cc' => $content_column,
                                                  'nn' => $new_name);
        $this->sub_order[$new_name] = array('dbp' . $index, $content_column);
        $this->table_columns[] = $new_name;
        $index++;
    }

    function loadLink()
    {
        $this->link = PHPWS_Core::getCurrentUrl(TRUE, FALSE);
    }

    function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }

    function getAnchor()
    {
        if (empty($this->anchor)) {
            return null;
        } else {
            return '#' . $this->anchor;
        }
    }

    function setOrder($column, $direction='asc', $only_if_empty=false)
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

    function setDefaultOrder($default_order, $direction='asc')
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

    function setDefaultLimit($limit)
    {
        if (empty($this->limit)) {
            $this->limit = (int)$limit;
        }
    }

    function setSearch()
    {
        $col_list = func_get_args();

        foreach ($col_list as $column) {
            if (!preg_match('/\W/', $column) && $this->db->isTableColumn($column)) {
                $this->searchColumn[] = $column;
            }
        }
    }

    function setPageTurnerLeft($turner)
    {
        $this->page_turner_left = $turner;
    }

    function setPageTurnerRight($turner)
    {
        $this->page_turner_right = $turner;
    }

    function setLimitList($list)
    {
        if (!is_array($list)) {
            return FALSE;
        }

        $this->limitList = $list;
    }

    function addToggle($toggle)
    {
        $this->toggles[] = $toggle;
    }

    function setLink($link)
    {
        $this->link = $link;
    }

    function getLinkQuery()
    {
        return substr(strstr($this->link, '?'), 1);
    }

    function getLinkBase()
    {
        return str_replace(strstr($this->link, '?'), '', $this->link);
    }

    function setModule($module)
    {
        $this->module = $module;
    }

    function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Allows the developer to add extra or processes row tags
     * 
     */
    function addRowTags()
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

    function setEmptyMessage($message)
    {
        $this->empty_message = strip_tags($message);
    }

    function addToggleFunction($function, $toggle=2) 
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
    function addRowFunction($function)
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

    function addRunMethod($method)
    {
        if (!in_array(strtolower($method), $this->_methods)) {
            return;
        }

        $this->run_methods[] = $method;
    }

    function addWhere($column, $value, $operator=NULL, $conj=NULL, $group=NULL)
    {
        return $this->db->addWhere($column, $value, $operator, $conj, $group);
    }

    function addPageTags($tags)
    {
        $this->page_tags = $tags;
    }

    function getLimit()
    {
        if (empty($this->limit) || !in_array($this->limit, $this->limitList)) {
            list($this->limit) = each($this->limitList);
        }

        $start = ($this->current_page - 1) * $this->limit;
        return array((int)$this->limit, (int)$start);
    }

    function getTotalRows()
    {
        if (isset($this->error)) {
            return;
        }

        if (count($this->db->tables) > 1) {
            $this->db->_distinct = FALSE;
            $columns = $this->db->columns;
            $this->db->columns = NULL;
            $result = $this->db->select('count');
            $this->db->columns = $columns;
            $this->db->_distinct = TRUE;
        } else {
            $result = $this->db->select('count');
        }

        return $result;
    }

    function getRows()
    {
        return $this->display_rows;
    }

    /**
     * Pulls the appropriate rows from the data base.
     *
     * This function pulls the database information then plugs
     * the data it gets into the object.
     */
    function initialize($load_rows=true)
    {
        if (isset($this->error)) {
            return $this->error;
        }
        if (empty($this->limit)) {
            $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        if (!empty($this->search) && isset($this->searchColumn)) {
            foreach ($this->searchColumn as $column_name) {
                // change to OR
                $this->addWhere($column_name, '%' . strtolower($this->search) . '%', 'like', 'or', 1);
            }
        }

        $count = $this->getTotalRows();

        if (PEAR::isError($count)) {
            return $count;
        }

        if ($this->limit > 0) {
            $this->db->setLimit($this->getLimit());
        }

        $this->total_rows = &$count;
        $this->total_pages = ceil($this->total_rows / $this->limit);

        if ($this->current_page > $this->total_pages) {
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

        if (!empty($this->sub_result)) {
            $this->db->addColumn('*');
            foreach ($this->sub_result as $sub_table => $sub) {
                $this->db->addTable($sub['jt'], $sub_table);
                $this->db->addWhere($sub['sc'], $sub_table . '.' . $sub['jc']);
                $this->db->addColumn($sub_table . '.' . $sub['cc'], null, $sub['nn']);
            }
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
        return TRUE;
    }

    function getPageLinks()
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

        $url_base = $this->getLinkBase();

        foreach ($values as $key => $value) {
            $link_pairs[] = "$key=$value";
        }

        $url = sprintf('%s?%s', $url_base, implode('&amp;',$link_pairs));

        $anchor = $this->getAnchor();

        // page one
        if ($current_page != 1) {
            if ($total_pages > 500 && $current_page > 50) {
                $pageList[] = sprintf('<a href="%s&amp;pg=%s%s" title="%s">&lt;&lt;&lt;</a>',$url, $current_page - 50, $anchor, _('Back 50 pages'));
            }

            if ($total_pages > 100 && $current_page > 10) {
                $pageList[] = sprintf('<a href="%s&amp;pg=%s%s" title="%s">&lt;&lt;</a>',$url, $current_page - 10, $anchor,  _('Back 10 pages'));
            }
            $pageList[] = sprintf('<a href="%s&amp;pg=%s%s" title="%s">&lt;</a>',$url, $current_page - 1, $anchor, _('Back one page'));
            $pageList[] = sprintf('<a href="%s&amp;pg=1%s">1</a>',$url, $anchor);
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
                        $pageList[] = sprintf('<a href="%s&amp;pg=%s%s">%s</a>',$url, $i, $anchor, $i);
                        $divider--;
                    }

                    $pageList[] = '[' . $current_page . ']';
                }
                $remaining_pages = $total_pages - $current_page;
                $skip = floor($remaining_pages / $divider);

                for ($i=0,$j = $current_page + $skip; $i < $divider; $i++,$j += $skip) {
                    $pageList[] = sprintf('<a href="%s&amp;pg=%s%s">%s</a>',$url, $j, $anchor, $j);
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
                    $pageList[] = sprintf('<a href="%s&amp;pg=%s%s">%s</a>',$url, $j, $anchor, $j);
                }

                $pageList[] = "[$current_page]";

                if ($back_skip) {
                    for ($i=0,$j = $current_page + $back_skip; $i < $divider - 1 && $j < $total_pages; $i++,$j += $back_skip) {
                        $pageList[] = sprintf('<a href="%s&amp;pg=%s%s">%s</a>',$url, $j, $anchor, $j);
                    }
                }
            }

        } else {
            for($i=2; $i < $total_pages; $i++) {
                $pageList[] = sprintf('<a href="%s&amp;pg=%s%s">%s</a>',$url, $i, $anchor, $i);
            }
        }

        if ($total_pages != $current_page) {
            $pageList[] = sprintf('<a href="%s&amp;pg=%s%s">%s</a>',$url, $total_pages, $anchor, $total_pages);
            $pageList[] = sprintf('<a href="%s&amp;pg=%s%s" title="%s">&gt;</a>',$url, $current_page + 1, $anchor, _('Forward one page'));
            if ($total_pages > 100 && ($total_pages - 10) >= $current_page) {
                $pageList[] = sprintf('<a href="%s&amp;pg=%s%s" title="%s">&gt;&gt;</a>',$url, $current_page + 10, $anchor, _('Forward 10 pages'));
            }
            
            if ($total_pages > 500 && ($total_pages - 50) >= $current_page) {
                $pageList[] = sprintf('<a href="%s&amp;pg=%s%s" title="%s">&gt;&gt;&gt;</a>',$url, $current_page + 50, $anchor, _('Forward 50 pages'));
            }
        } else {
            $pageList[] = "[$current_page]";
        }

        return implode(' ', $pageList);
    }

    /**
     * Returns the sorting buttons for table columns
     */
    function getSortButtons(&$template)
    {
        if (empty($this->table_columns)) {
            return NULL;
        }

        foreach ($this->table_columns as $varname) {
            $vars = array();
            $values = $this->getLinkValues();
            $buttonname = str_replace('.', '_', $varname) . '_SORT';

            $values['orderby'] = $varname;

            if ($this->orderby == $varname){
                if ($this->orderby_dir == 'desc'){
                    unset($values['orderby_dir']);
                    $alt = _('Sort ascending');
                    $button = sprintf('<img src="images/core/list/up_pointer.png" border="0" alt="%s" title="%s" />', $alt, $alt);
                } elseif ($this->orderby_dir =="asc") {
                    $alt = _('Sort descending');
                    $values['orderby_dir'] = 'desc';
                    $button = sprintf('<img src="images/core/list/down_pointer.png" border="0" alt="%s" title="%s" />', $alt, $alt);
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

            foreach ($values as $key=>$value) {
                $vars[] = "$key=$value";
            }

            $link = sprintf('<a href="%s?%s%s">%s</a>', $this->getLinkBase(), implode('&amp;', $vars), $this->getAnchor(), $button);

            $template[strtoupper($buttonname)] = $link;
        }
        return $template;
    }

    function getLinkValues()
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
        $extra = preg_replace('/\s/', '+', $extra);

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

        $GLOBALS['DBPager_Link_Values'] = $values;

        return $values;
    }


    function getLimitList()
    {
        $values = $this->getLinkValues();
        unset($values['limit']);
        foreach ($values as $key => $value) {
            $link_pairs[] = "$key=$value";
        }

        foreach ($this->limitList as $limit) {
            if ($limit == $this->limit) {
                $links[] = $limit;
            } else {
                $link_pairs['a'] = "limit=$limit";
                $links[] = sprintf('<a href="%s?%s%s">%s</a>', $this->getLinkBase(), implode('&amp;', $link_pairs), $this->getAnchor(), $limit);
            }
        }

        return implode(' ', $links);
    }


    /**
     * Pulls variables from the object results. Calls object's formatting function if
     * specified.
     */
    function getPageRows()
    {
        $template = null;
        $count = 0;

        if (!isset($this->display_rows)) {
            return NULL;
        }

        foreach ($this->display_rows as $disp_row) {
            if (isset($this->class) && isset($this->run_methods)){
                foreach ($this->run_methods as $run_function) {
                    call_user_func(array(&$disp_row, $run_function));
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
                        $row_result = call_user_func(array(&$disp_row, $this->row_tags['method']));
                    } else {
                        $row_result = call_user_func_array(array(&$disp_row, $this->row_tags['method']), $this->row_tags['variable']);
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
                    if (!empty($row_result)) {
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

    function getPageDrop()
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


    function getSearchBox()
    {
        $form = & new PHPWS_Form('search_list');
        $form->setMethod('get');
        $values = $this->getLinkValues();
        unset($values['pager_search']);
        $form->addHidden($values);
        $form->addText('pager_c_search', $this->search);
        $form->setLabel('pager_c_search', _('Search'));
        $template = $form->getTemplate();
        if (PEAR::isError($template)) {
            PHPWS_Error::log($template);
            return NULL;
        }

        return implode("\n", $template);
    }

    function _getNavigation(&$template)
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
        $template['TOTAL_ROWS']  = $start_row . ' - ' . $end_row . ' ' . _('of') . ' ' . $total_row;
        $template['LIMITS']      = $this->getLimitList();

        if (isset($this->searchColumn)) {
            $template['SEARCH'] = $this->getSearchBox();
        }
    }

    /**
     * Returns the content of the the pager object
     */
    function get($return_blank_results=TRUE)
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
        $this->final_template = &$template;

        return PHPWS_Template::process($template, $this->module, $this->template);
    }

    function getFinalTemplate()
    {
        return $this->final_template;
    }

    function plugPageTags(&$template){
        if (isset($this->page_tags)){
            foreach ($this->page_tags as $key=>$value)
                $template[$key] = $value;
        }
    }

    function saveLastView()
    {
        $_SESSION['DBPager_Last_View'][$this->table] = PHPWS_Core::getCurrentUrl();
    }

    function getLastView($table)
    {
        if (isset($_SESSION['DBPager_Last_View'][$table])) {
            return $_SESSION['DBPager_Last_View'][$table];
        } else {
            return null;
        }
    }
}

?>