<?php

define ('DBPAGER_DEFAULT_LIMIT', 10);
define ('DBPAGER_PAGE_LIMIT', 8);
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
    var $runMethods = NULL;

    /**
     * List of methods in class
     */  
    var $_methods = NULL;

    var $_class_vars = NULL;

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

    var $error;

    function DBPager($table, $class=NULL){
        if (empty($table)) {
            $this->error = PHPWS_Error::get(DBPAGER_NO_TABLE, 'core', 'DB_Pager::DBPager');
            return;
        }
        $this->db = & new PHPWS_DB($table);
        $this->db->setDistinct(TRUE);
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

        if (isset($_REQUEST['page']))
            $this->current_page = (int)$_REQUEST['page'];

        if (isset($_REQUEST['limit']) && $_REQUEST['limit'] > 0)
            $this->limit = (int)$_REQUEST['limit'];

        if (isset($_REQUEST['orderby']))
            $this->orderby = preg_replace('/\W/', '', $_REQUEST['orderby']);

        if (isset($_REQUEST['orderby_dir']))
            $this->orderby_dir = preg_replace('/\W/', '', $_REQUEST['orderby_dir']);

        if (isset($_REQUEST['search']) && !empty($_REQUEST['search']))
            $this->search = preg_replace('/\W/', '', $_REQUEST['search']);

    }

    function setOrder($column, $direction)
    {
        $this->orderby =  preg_replace('/\W/', '', $column);
        $this->orderby_dir = preg_replace('/\W/', '', $direction);
    }

    function setDefaultLimit($limit) {
        if (empty($this->limit))
            $this->limit = (int)$limit;
    }

    function setSearch(){
        $col_list = func_get_args();

        foreach ($col_list as $column)
            if (ctype_alnum($column) && $this->db->isTableColumn($column))
                $this->searchColumn[] = $column;
    }

    function setPageTurnerLeft($turner){
        $this->page_turner_left = $turner;
    }

    function setPageTurnerRight($turner){
        $this->page_turner_right = $turner;
    }

    function setLimitList($list)
    {
        if (!is_array($list)) {
            return FALSE;
        }

        $this->limitList = $list;
    }

    function addToggle($toggle){
        $this->toggles[] = $toggle;
    }

    function setLink($link){
        $this->link = $link;
    }

    function setModule($module){
        $this->module = $module;
    }

    function setTemplate($template){
        $this->template = $template;
    }

    /**
     * Allows the developer to add extra or processes row tags
     * 
     */
    function addRowTags()
    {
        if (empty($this->class)) {
            return FALSE;
        }

        if (func_num_args() < 1) {
            return FALSE;
        }

        $method = func_get_arg(0);

        if (func_num_args() > 1) {
            $variables = func_get_args();
            //strip the method
            array_shift($variables);
        } else {
            $variables = NULL;
        }

        $this->row_tags = array('method'=>$method, 'variable'=>$variables);
    }

    function setEmptyMessage($message){
        $this->empty_message = strip_tags($message);
    }

    function addRunMethod($method){
        if (!in_array(strtolower($method), $this->_methods))
            return;

        $this->runMethods[] = $method;
    }

    function addWhere($column, $value, $operator=NULL, $conj=NULL, $group=NULL){
        return $this->db->addWhere($column, $value, $operator, $conj, $group);
    }

    function addPageTags($tags){
        $this->page_tags = $tags;
    }

    function getLimit(){
        if (empty($this->limit)) {
            $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        $start = ($this->current_page - 1) * $this->limit;
        return array($start, $this->limit);
    }

    function getTotalRows(){
        if (isset($this->error)) {
            return;
        }

        $result = $this->db->select('count');
        $this->db->resetColumns();
        return $result;
    }

    function getRows()
    {
        return $this->display_rows;
    }

    /**
     * Pulls the appropiate rows from the data base.
     *
     * This function pulls the database information then plugs
     * the data it gets into the object.
     */
    function initialize(){
        if (isset($this->error)) {
            return $this->error;
        }
        if (empty($this->limit)) {
            $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        if (!empty($this->search) && isset($this->searchColumn)){
            foreach ($this->searchColumn as $column_name) {
                $this->addWhere($column_name, $this->search, 'REGEXP', 'OR');
            }
        }

        $count = $this->getTotalRows();
    
        if (PEAR::isError($count)) {
            return $count;
        }

        $this->total_rows = &$count;
        $this->total_pages = ceil($this->total_rows / $this->limit);
        if ($this->current_page > $this->total_pages)
            $this->current_page = $this->total_pages;

        if ($this->limit > 0)
            $this->db->setLimit($this->getLimit());

        if (isset($this->orderby)) {
            $this->db->addOrder($this->orderby . ' ' . $this->orderby_dir);
        }

        if (empty($this->class)) {
            $result = $this->db->select();
        } else {
            $result = $this->db->getObjects($this->class);
        }

        if (PEAR::isError($result)) {
            return $result;
        }

        $this->display_rows = &$result;
        return TRUE;
    }

    function getPageLinks(){
        if ($this->total_pages < 1) {
            $current_page = $total_pages = 1;
        } else {
            $current_page = $this->current_page;
            $total_pages = $this->total_pages;
        }

        $limit_pages = ($this->total_pages > DBPAGER_PAGE_LIMIT) ? TRUE : FALSE;
    
        if ($limit_pages){
            $limitList[] = 1;
            $limitList[] = 2;

            $limitList[] = $this->current_page - 1;
            $limitList[] = $this->current_page;
            $limitList[] = $this->current_page + 1;
      
            $paddingPoint = $limitList[] = $this->total_pages - 1;
            $limitList[] = $this->total_pages;
        }

        $values = $this->getLinkValues();

        if ($current_page != 1){
            $values['page'] = $this->current_page - 1;
            foreach ($values as $key => $value) {
                $link_pairs1[] = "$key=$value";
            }
            $pages[] = '<a href="' . $this->link . '&amp;' . implode('&amp;', $link_pairs1) . '">' . $this->page_turner_left . "</a>\n";
        }

        for ($i=1; $i <= $total_pages; $i++){
            if ($limit_pages && !in_array($i, $limitList)){

                if (!isset($padding1)){
                    $pages[] = '...';
                    $padding1 = TRUE;
                    continue;
                }

                if (isset($padding1) && !isset($padding2) && isset($recock)){
                    $pages[] = '...';
                    $padding2 = TRUE;
                }
                continue;
            }
            if (isset($padding1)) {
                $recock = TRUE;
            }


            $values['page'] = $i;

            if ($this->current_page != $i) {
                $link_pairs2 = array();
                foreach ($values as $key => $value) {
                    $link_pairs2[] = "$key=$value";
                }
                $pages[] = '<a href="' . $this->link . '&amp;' . implode('&amp;', $link_pairs2) . "\">$i</a>\n";
            } else {
                $pages[] = $i;
            }

            if ( $limit_pages &&
                 !isset($padding2) &&
                 !in_array($i, $limitList)
                 )
                {
                    $pages[] = '...';
                    $padding2 = TRUE;
                }
        }

        if ($this->current_page != $this->total_pages){
            $values['page'] = $this->current_page + 1;
            foreach ($values as $key => $value) {
                $link_pairs3[] = "$key=$value";
            }
            $pages[] = '<a href="' . $this->link . '&amp;' . implode('&amp;', $link_pairs3) . '">' . $this->page_turner_right . "</a>\n";
        }

        return implode(' ', $pages);
    }

    function getSortButtons(&$template){
        foreach ($this->_class_vars as $varname){
            $vars = array();
            $values = $this->getLinkValues();
            $buttonname = $varname . '_SORT';

            $values['orderby'] = $varname;

            if ($this->orderby == $varname){
                if ($this->orderby_dir == 'desc'){
                    unset($values['orderby_dir']);
                    $button = '<img src="images/core/list/up_pointer.png" border="0" />';
                } elseif ($this->orderby_dir =="asc") {
                    $values['orderby_dir'] = 'orderby_dir=desc';
                    $button = '<img src="images/core/list/down_pointer.png" border="0" />';
                } else {
                    $button = '<img src="images/core/list/sort_none.png" border="0" />';
                    $values['orderby_dir'] = 'orderby_dir=asc';
                }

            } else {
                $button = '<img src="images/core/list/sort_none.png" border="0" />';
                $values['orderby_dir'] = 'asc';
            }

            foreach ($values as $key=>$value) {
                $vars[] = "$key=$value";
            }

            $link = '<a href="' . $this->link . '&amp;' . implode('&amp;', $vars) . '">' . $button . '</a>';

            $template[strtoupper($buttonname)] = $link;
        }
        return $template;
    }

    function getLinkValues(){
        if (isset($GLOBALS['DBPager_Link_Values'])) {
            return $GLOBALS['DBPager_Link_Values'];
        }

        if (empty($this->limit)) {
            $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        $values['page'] = $this->current_page;
        $values['limit'] = $this->limit;

        if (isset($this->search)) {
            $values['search'] = $this->search;
        }

        if (isset($this->orderby)) {
            $values['orderby'] = $this->orderby;
            if (isset($this->orderby_dir))
                $values['orderby_dir'] = $this->orderby_dir;
        }

        // pull get values from link setting
        $url = parse_url($this->link);
        parse_str(str_replace('&amp;', '&', $url['query']), $output);

        
        // pull any extra values in current url
        $extra = PHPWS_Text::getGetValues();

        // if extra values exist, add them to the values array
        // ignore matches in the output and other values
        if (!empty($extra)) {
            $diff = array_diff_assoc($extra, $output);
            $diff = array_diff_assoc($diff, $values);
            $values = array_merge($diff, $values);
        }

        $values = array_merge($output, $values);
        $GLOBALS['DBPager_Link_Values'] = $values;
        return $values;
    }


    function getLimitList(){
        $values = $this->getLinkValues();
        unset($values['limit']);
        foreach ($values as $key => $value) {
            $link_pairs[] = "$key=$value";
        }

        foreach ($this->limitList as $limit){
            $link_pairs['a'] = "limit=$limit";
            $links[] = '<a href="index.php?' . implode('&amp;', $link_pairs) . '">' . $limit . '</a>';
        }

        return implode(' ', $links);
    }


    function getPageRows(){
        $count = 0;

        if (!isset($this->display_rows)) {
            return NULL;
        }

        foreach ($this->display_rows as $disp_row){
            if (isset($this->class) && isset($this->runMethods)){
                foreach ($this->runMethods as $run_function) {
                    $disp_row->{$run_function}();
                }
            }

            if (isset($this->class)) {
                foreach ($this->_class_vars as $varname) {
                    $template[$count][strtoupper($varname)] = $disp_row->{$varname};
                }
                if (!empty($this->row_tags)) {
                    extract($this->row_tags);
                    if (!in_array(strtolower($method), $this->_methods)) {
                        continue;
                    }

                    if (empty($variable)) {
                        $row_result = $disp_row->{$method}();
                    } else {
                        $row_result = call_user_func_array(array(&$disp_row, $method), $variable);
                    }

                    $template[$count] = array_merge($template[$count], $row_result);
                }

            } else {
                foreach ($disp_row as $key => $value) {
                    $template[$count][strtoupper($key)] = $value;
                }
            }

            $count++;
        }

        return $template;
    }

    function getPageDrop(){
        if (empty($this->total_pages)) {
            $page_list[1] = 1;
        } else {
            for ($i = 1; $i <= $this->total_pages; $i++)
                $page_list[$i] = $i;
        }

        $form = & new PHPWS_Form('page_list');
        $form->setMethod('get');
        $this->_setHiddenVars($form);
        $form->addSelect('page', $page_list);
        $form->setExtra('page', 'onchange="this.form.submit()"');
        if (isset($_REQUEST['page']))
            $form->setMatch('page', (int)$_REQUEST['page']);
        if (!javascriptEnabled())
            $form->addSubmit('go', _('Go'));
        $template = $form->getTemplate();
        if (PEAR::isError($template)) {
            PHPWS_Error::log($template);
            return NULL;
        }
        return implode("\n", $template);
    }


    function getSearchBox(){
        $form = & new PHPWS_Form('search_list');
        $form->setMethod('get');
        $this->_setHiddenVars($form, FALSE);
        $form->addText('search', $this->search);
        $form->setLabel('search', _('Search'));
        $template = $form->getTemplate();
        if (PEAR::isError($template)) {
            PHPWS_Error::log($template);
            return NULL;
        }
        return implode("\n", $template);
    }

    function _setHiddenVars(&$form, $addSearch=TRUE){
        if (empty($this->limit)) {
            $this->limit = DBPAGER_DEFAULT_LIMIT;
        }

        $link = str_replace('index.php?', '', $this->link);
        $link_list = explode('&', html_entity_decode($link));
        foreach ($link_list as $var){
            if (empty($var)) {
                continue;
            }
            $i = explode('=', $var);
            if ($i[0] == 'authkey')
                continue;
            $form->addHidden($i[0], $i[1]);
        }

        $form->addHidden('limit', $this->limit);
        if ($addSearch == TRUE && isset($this->search))
            $form->addHidden('search', $this->search);
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

        if (PEAR::isError($pages))
            return $pages;

        $template['PAGES']     = $pages;
        $template['PAGE_LABEL']  = _('Page');
        $template['LIMIT_LABEL'] = _('Limit');
        $template['PAGE_DROP'] = $this->getPageDrop();
        $template['TOTAL_ROWS']  = $start_row . ' - ' . $end_row . ' ' . _('of') . ' ' . $total_row;
        $template['LIMITS']    = $this->getLimitList();

        if (isset($this->searchColumn)) {
            $template['SEARCH']    = $this->getSearchBox();
        }

    }

    function get()
    {
        $template = array();

        if (empty($this->display_rows)) {
            $result = $this->initialize();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if (!isset($this->module)) {
            return PHPWS_Error::get(DBPAGER_MODULE_NOT_SET, 'core', 'DBPager::get()');
        }

        if (!isset($this->template)) {
            return PHPWS_Error::get(DBPAGER_TEMPLATE_NOT_SET, 'core', 'DBPager::get()');
        }

        $rows = $this->getPageRows();

        if (isset($this->toggles)) {
            $max_tog = count($this->toggles);
        }

        $count = 0;
        if (isset($rows)) {
            $this->_getNavigation($template);
            foreach ($rows as $rowitem){
                if (isset($max_tog)) {
                    $rowitem['TOGGLE'] = $this->toggles[$count];
                    $count++;
          
                    if ($count >= $max_tog) {
                        $count = 0;
                    }
                } else {
                    $rowitem['TOGGLE'] = NULL;
                }

                $template['listrows'][] = $rowitem;
            }
      
            $this->getSortButtons($template);
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

}

?>