<?php
/**
 * A database class
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */
require_once 'DB.php';

// Changing LOG_DB to TRUE will cause ALL DB traffic to get logged
// This can log can get very large, very fast. DO NOT turn it on
// on a live server. It is for development purposes only.
define ('LOG_DB', false);

define ('DEFAULT_MODE', DB_FETCHMODE_ASSOC);

class PHPWS_DB {
    var $tables      = NULL;
    var $where       = array();
    var $order       = array();
    var $values      = array();
    var $mode        = DEFAULT_MODE;
    var $limit       = NULL;
    var $index       = NULL;
    var $columns     = NULL;
    var $qwhere      = NULL;
    var $indexby     = NULL;
    var $groupby     = NULL;

    /**
     * allows you to group together where queries
     */
    var $group_in    = array();
    // This variable holds a sql query string
    var $sql         = NULL;
    var $_allColumns = NULL;
    var $_columnInfo = NULL;
    var $_lock       = FALSE;

    // contains the database specific factory class
    var $_distinct   = FALSE;
    var $_test_mode  = FALSE;
    var $_join       = NULL;
    var $_join_tables = NULL;


    function PHPWS_DB($table=NULL)
    {
        PHPWS_DB::touchDB();
        if (isset($table)) {
            $result = $this->setTable($table);

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
        }
        $this->setMode('assoc');
    }

    /**
     * Lets you enter a raw select query
     */ 
    function setSQLQuery($sql)
    {
        $this->sql = $sql;
    }
    function touchDB()
    {
        if (!PHPWS_DB::isConnected()) {
            return PHPWS_DB::loadDB();
        }
    }

    function setTestMode($mode=TRUE)
    {
        $this->_test_mode = (bool)$mode;
    }

    function isConnected()
    {
        if (!empty($GLOBALS['PHPWS_DB']['connection'])) {
            return true;
        } else {
            return false;
        }
    }

    function getDbName($dsn) {
        $aDSN = explode('/', $dsn);
        return array_pop($aDSN);
    }

    function _updateCurrent($key)
    {
        $GLOBALS['PHPWS_DB']['lib']        = & $GLOBALS['PHPWS_DB']['dbs'][$key]['lib'];
        $GLOBALS['PHPWS_DB']['dsn']        = & $GLOBALS['PHPWS_DB']['dbs'][$key]['dsn'];
        $GLOBALS['PHPWS_DB']['connection'] = & $GLOBALS['PHPWS_DB']['dbs'][$key]['connection'];
        $GLOBALS['PHPWS_DB']['tbl_prefix'] = & $GLOBALS['PHPWS_DB']['dbs'][$key]['tbl_prefix'];
        $GLOBALS['PHPWS_DB']['type']       = & $GLOBALS['PHPWS_DB']['dbs'][$key]['type'];
    }

    function loadDB($dsn=null, $tbl_prefix=null, $force_reconnect=false, $show_error=TRUE)
    {
        if (!isset($dsn)) {
            $dsn = PHPWS_DSN;
            if (defined('PHPWS_TABLE_PREFIX')) {
                $tbl_prefix = PHPWS_TABLE_PREFIX;
            }
        }

        $key = substr(md5($dsn), 0, 10);
        $dbname = PHPWS_DB::getDbName($dsn);
        $GLOBALS['PHPWS_DB']['key'] = $key;

        if (!empty($GLOBALS['PHPWS_DB']['dbs'][$key]['connection']) && !$force_reconnect) {
            PHPWS_DB::_updateCurrent($key);
            return true;
        }
        
        $connect = DB::connect($dsn);

        if (PEAR::isError($connect)){
            PHPWS_Error::log($connect);
            if ($show_error) {
                PHPWS_Core::errorPage();
            } else {
                return $connect;
            }
        }
        
        PHPWS_DB::logDB(sprintf(_('Connected to database "%s"'), $dbname));

        // Load the factory files
        $type = $connect->dbsyntax;
        $result = PHPWS_Core::initCoreClass('DB/' . $type .'.php');
        if ($result == FALSE) {
            PHPWS_DB::logDB(_('Failed to connect.'));
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', 'PHPWS_DB::loadDB', 
                             PHPWS_SOURCE_DIR . 'core/class/DB/' . $type . '.php');
            PHPWS_Core::errorPage();
        }

        $class_name = $type . '_PHPWS_SQL';
        $GLOBALS['PHPWS_DB']['dbs'][$key]['lib']        = new $class_name;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['dsn']        = $dsn;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['connection'] = $connect;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['tbl_prefix'] = $tbl_prefix;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['type']       = $type;
        PHPWS_DB::_updateCurrent($key);
        return TRUE;
    }

    function logDB($sql)
    {
        if (!defined('LOG_DB') || LOG_DB != TRUE) {
            return;
        }

        PHPWS_Core::log($sql, 'db.log');
    }

    function query($sql)
    {
        if (isset($this) && !empty($this->_test_mode)) {
            exit($sql);
        }

        PHPWS_DB::touchDB();
        $sql = PHPWS_DB::prefixQuery($sql);
        PHPWS_DB::logDB($sql);
        return $GLOBALS['PHPWS_DB']['connection']->query($sql);
    }

    function getColumnInfo($col_name, $parsed=FALSE)
    {
        if (!isset($this->_columnInfo)) {
            $this->getTableColumns();
        }

        if (isset($this->_columnInfo[$col_name])) {
            if ($parsed == TRUE) {
                return $this->parsePearCol($this->_columnInfo[$col_name], TRUE);
            } else {
                return $this->_columnInfo[$col_name];
            }
        }
        else {
            return NULL;
        }
    }

    function inDatabase($table, $column=NULL)
    {
        $table = PHPWS_DB::addPrefix($table);

        PHPWS_DB::touchDB();
        static $database_info = NULL;

        $column = trim($column);
        $answer = FALSE;

        if (!empty($database_info[$table])) {
            if (empty($column)) {
                return TRUE;
            } else {
                return in_array($column, $database_info[$table]);
            }
        }

        $result = $GLOBALS['PHPWS_DB']['connection']->tableInfo(strip_tags($table));
        if (PEAR::isError($result)) {
            if ($result->getCode() == DB_ERROR_NEED_MORE_DATA) {
                return FALSE;
            } else {
                return $result;
            }
        }

        foreach ($result as $colInfo) {
            $list_columns[] = $colInfo['name'];

            if ($colInfo['name'] == $column) {
                $answer = TRUE;
            }
        }

        $database_info[$table] = $list_columns;

        return $answer;
    }

    /**
     * Gets information on all the columns in the current table
     */
    function getTableColumns($fullInfo=FALSE)
    {
        if (isset($this->_allColumns) && $fullInfo == FALSE) {
            return $this->_allColumns;
        } elseif (isset($this->_columnInfo) && $fullInfo == TRUE) {
            return $this->_columnInfo;
        }

        $table = $this->tables[0];

        if (!isset($table)) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::getTableColumns');
        }

        $table = $this->addPrefix($table);

        $columns =  $GLOBALS['PHPWS_DB']['connection']->tableInfo($table);

        if (PEAR::isError($columns)) {
            return $columns;
        }

        foreach ($columns as $colInfo) {
            $this->_columnInfo[$colInfo['name']] = $colInfo;
            $this->_allColumns[] = $colInfo['name'];
        }

        if ($fullInfo == TRUE) {
            return $this->_columnInfo;
        } else {
            return $this->_allColumns;
        }
    }

    /**
     * Returns true is the columnName is contained in the
     * current table
     */
    function isTableColumn($columnName)
    {
        $columns = $this->getTableColumns();

        if (PEAR::isError($columns)) {
            return $columns;
        }

        return in_array($columnName, $columns);
    }

    function setMode($mode)
    {
        switch (strtolower($mode)){
        case 'ordered':
            $this->mode = DB_FETCHMODE_ORDERED;
            break;

        case 'object':
            $this->mode = DB_FETCHMODE_OBJECT;
            break;

        case 'assoc':
            $this->mode = DB_FETCHMODE_ASSOC;
            break;
        }

    }

    function getMode()
    {
        return $this->mode;
    }

    function isTable($table)
    {
        PHPWS_DB::touchDB();
        $tables = PHPWS_DB::listTables();

        $table = PHPWS_DB::addPrefix($table);
        return in_array($table, $tables);
    }

    function listTables()
    {
        PHPWS_DB::touchDB();
        return $GLOBALS['PHPWS_DB']['connection']->getlistOf('tables');
    }

    function listDatabases()
    {
        PHPWS_DB::touchDB();
        return $GLOBALS['PHPWS_DB']['connection']->getlistOf('databases');
    }

    function addJoin($join_type, $join_from, $join_to, $join_on_1=NULL, $join_on_2=NULL)
    {
        if (!preg_match('/left|right/i', $join_type)) {
            return FALSE;
        }
        $this->_join_tables[] = array('join_type' => $join_type,
                                      'join_from' => $join_from,
                                      'join_to'   => $join_to,
                                      'join_on_1' => $join_on_1,
                                      'join_on_2' => $join_on_2);
    }

    function addTable($table)
    {
        if (is_array($table)) {
            foreach ($table as $tbl_name) {
                $this->addTable($tbl_name);
            }
            return;
        }

        if (PHPWS_DB::allowed($table) && !in_array($table, $this->tables)) {
            $this->tables[] = $table;
        }
        else {
            return PHPWS_Error::get(PHPWS_DB_BAD_TABLE_NAME, 'core', 'PHPWS_DB::setTable', $table);
        }
        return TRUE;
    }

    function setTable($table)
    {
        $this->tables = array();
        $this->_join_tables = NULL;
        $this->_columnInfo = NULL;
        $this->_allColumns = NULL;
        return $this->addTable($table);
    }

    function setIndex($index)
    {
        $this->index = $index;
    }

    function getIndex()
    {
        if (isset($this->index)) {
            return $this->index;
        }

        $table = $this->getTable();
        $table = $this->addPrefix($table);

        $columns =  $GLOBALS['PHPWS_DB']['connection']->tableInfo($table);
    
        if (PEAR::isError($columns)) {
            return $columns;
        }
    
        foreach ($columns as $colInfo) {
            if ($colInfo['name'] == 'id' && preg_match('/primary/', $colInfo['flags']) && preg_match('/int/', $colInfo['type'])) {
                return $colInfo['name'];
            }
        }

        return NULL;
    }

    function _getJoinOn($join_on_1, $join_on_2, $table1, $table2) {
        if (empty($join_on_1) || empty($join_on_2)) {
            return NULL;
        }

        if (is_array($join_on_1) && is_array($join_on_2)) {
            foreach ($join_on_1 as $key => $value) {
                $retVal[] = sprintf('%s.%s = %s.%s',
                                    $table1, $value,
                                    $table2, $join_on_2[$key]);
            }
            return implode(' AND ', $retVal);
        } else {
            return sprintf('%s.%s = %s.%s',
                           $table1, $join_on_1,
                           $table2, $join_on_2);
        }
    }

    function getJoin()
    {
        if (empty($this->_join_tables)) {
            return NULL;
        }

        $join_info['tables'] = array();

        foreach ($this->_join_tables as $join_array) {
            extract($join_array);

            if ($result = $this->_getJoinOn($join_on_1, $join_on_2, $join_from, $join_to)) {
                $join_on = 'ON ' . $result;
            }

            if (in_array($join_from, $join_info['tables'])) {
                $allJoin[] = sprintf('%s %s %s',
                                     strtoupper($join_type) . ' JOIN',
                                     $join_to,
                                     $join_on);
            } else {
                $allJoin[] = sprintf('%s %s %s %s',
                                     $join_from,
                                     strtoupper($join_type) . ' JOIN',
                                     $join_to,
                                     $join_on);
            }
            $join_info['tables'][] = $join_from;
            $join_info['tables'][] = $join_to;
        }

        $join_info['join'] = implode(' ', $allJoin);

        return $join_info;
    }

    function getTable($format=TRUE)
    {
        if (empty($this->tables)) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::getTable');
        }
        if ($format == TRUE) {
            $join_info = $this->getJoin();

            foreach ($this->tables as $table) {
                if ($join_info && in_array($table, $join_info['tables'])) {
                    continue;
                }
                $table_list[] = $table;
            }

            if ($join_info) {
                $table_list[] = $join_info['join'];
            }
            return implode(',', $table_list);
        } else {
            return $this->tables;
        }
    }

    function resetTable()
    {
        $this->tables = array();
    }

    function setGroupConj($group, $conj)
    {
        $conj = strtoupper($conj);
        if (empty($conj) || ($conj != 'OR' &&  $conj != 'AND')) {
            return FALSE;
        }

        $this->where[$group]['conj'] = $conj;
    }

    function addGroupBy($group_by)
    {
        if (PHPWS_DB::allowed($group_by)) {
            if (!strpos($group_by, '.')) {
                $group_by = $this->tables[0] . '.' . $group_by;
            }

            $this->groupBy[] = $group_by;
        }
    }

    function getGroupBy($dbReady=FALSE)
    {
        if ((bool)$dbReady == TRUE) {
            if (empty($this->groupBy)) {
                return NULL;
            } else {
                return 'GROUP BY ' . implode(', ', $this->groupBy);
            }
        }
        return $this->groupBy;
    }

    /**
     * Puts the first group label into the second
     */
    function groupIn($sub, $main)
    {
        $group_names = array_keys($this->where);
        if (!in_array($sub, $group_names) || !in_array($main, $group_names)) {
            return FALSE;
        }
        $this->group_in[$sub] = $main;
        return TRUE;
    }


    function addWhere($column, $value=NULL, $operator=NULL, $conj=NULL, $group=NULL, $join=FALSE)
    {
        PHPWS_DB::touchDB();
        $where = new PHPWS_DB_Where;
        $where->setJoin($join);
        $operator = strtoupper($operator);
        if (is_array($column)) {
            foreach ($column as $new_column => $new_value) {
                $result = $this->addWhere($new_column, $new_value, $operator, $conj, $group);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
            return TRUE;
        } else {
            if (!PHPWS_DB::allowed($column)) {
                return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core',
                                        'PHPWS_DB::addWhere', $column);
            }
        }

        if (is_array($value) && !empty($value)) {
            if (!empty($operator) && $operator != 'IN' && $operator != 'BETWEEN') {
                $search_in = TRUE;
            } else {
                if (empty($operator)) {
                    $operator = 'IN';
                }
                $search_in = FALSE;
            }
            
            foreach ($value as $newVal){
                if ($search_in) {
                    $result = $this->addWhere($column, $newVal, $operator, $conj, $group);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                } else {
                    $newVal = $GLOBALS['PHPWS_DB']['connection']->escapeSimple($newVal);
                    $new_value_list[] = $newVal;
                }
            }

            if (!$search_in && isset($new_value_list)) {
                $value = &$new_value_list;
            } else {
                return TRUE;
            }
        } else {
            if (is_null($value) || (is_string($value) && strtoupper($value) == 'NULL')) {
                if (empty($operator) || ( $operator != 'IS NOT' && $operator != '!=')) {
                    $operator = 'IS';
                } else {
                    $operator = 'IS NOT';
                }
                $value = 'NULL';
            } else {
                $value = $GLOBALS['PHPWS_DB']['connection']->escapeSimple($value);
            }
        }

        $source_table = $this->tables[0];

	if (is_string($column)) {
            if (substr_count($column, '.') == 1) {
                list($join_table, $join_column) = explode('.', $column);
                if (PHPWS_DB::inDatabase($join_table, $join_column)) {
                    $column = &$join_column;
                    $source_table = $join_table;
                    $where->setTable($join_table);
                    $this->addTable($join_table);
                }
            }
	}

        $where->setColumn($column);
        $where->setTable($source_table);

	if (is_string($value)) {
            if (substr_count($value, '.') == 1) {
                list($join_table, $join_column) = explode('.', $value);
                if (PHPWS_DB::inDatabase($join_table, $join_column)) {
                    $where->setJoin(TRUE);
                    $this->addTable($join_table);
                }
            }
	}

        $where->setValue($value);

        $where->setConj($conj);
        $where->setOperator($operator);


        if (isset($group)) {
            $this->where[$group]['values'][] = $where;
        }
        else {
            $this->where[0]['values'][] = $where;
        }

    }

    function checkOperator($operator)
    {
        $allowed = array('>',
                         '>=',
                         '<',
                         '<=',
                         '=',
                         '!=',
                         '<>',
                         '<=>',
                         'LIKE',
                         'REGEXP',
                         'IN',
                         'NOT IN',
                         'BETWEEN',
                         'IS',
                         'IS NOT');

        return in_array(strtoupper($operator), $allowed);
    }

    function setQWhere($where, $conj='AND')
    {
        $conj = strtoupper($conj);
        if (empty($conj) || ($conj != 'OR' &&  $conj != 'AND')) {
            return FALSE;
        }
        
        $where = preg_replace('/where/i', '', $where);
        $this->qwhere['where'] = $where;
        $this->qwhere['conj']  = $conj;
    }


    /**
     * Grabs the where variables from the object and creates a sql query
     */
    function getWhere($dbReady=FALSE)
    {
        $sql = array();
        $ignore_list = $where = NULL;

        if (empty($this->where)) {
            if (isset($this->qwhere)) {
                return ' (' . $this->qwhere['where'] .')';
            }
            return NULL;
        }
        $startMain = FALSE;
        if ($dbReady) {
            foreach ($this->where as $group_name => $groups) {
                $hold = NULL;
                $subsql = array();
                if (!isset($groups['values'])) {
                    continue;
                }

                $startSub = FALSE;

                foreach ($groups['values'] as $whereVal) {
                    if ($startSub == TRUE) {
                        $subsql[] = $whereVal->conj;
                    }
                    $subsql[] = $whereVal->get();
                    $startSub = TRUE;
                }

                $where_list[$group_name]['group_sql'] = $subsql;
                
                if (@$conj = $groups['conj']) {
                    $where_list[$group_name]['group_conj'] = $conj;
                } else {
                    $where_list[$group_name]['group_conj'] = 'AND';
                }

                if (@$search_key = array_search($group_name, $this->group_in, true)) {
                    $where_list[$search_key]['group_in'][$group_name] = &$where_list[$group_name];
                }
            }

            $start_main = FALSE;

            $sql[] = $this->_buildGroup($where_list, $ignore_list, TRUE);
            
            if (isset($this->qwhere)) {
                $sql[] = $this->qwhere['conj'] . ' (' . $this->qwhere['where'] . ')';
            }

            if (isset($sql)) {
                $where = implode(' ', $sql);
            }

            return $where;
        } else {
            return $this->where;
        }
    }

    /**
     * Handles the imbedding of where groups
     */
    function _buildGroup($where_list, &$ignore_list, $first=FALSE) {
        if (!$ignore_list) {
            $ignore_list = array();
        } 

        foreach ($where_list as $group_name => $group_info) {
            if (isset($ignore_list[$group_name])) {
                continue;
            }
            $ignore_list[$group_name] = TRUE;
            extract($group_info);

            if (!$first) {
                $sql[] = $group_conj;
            } else {
                $first=FALSE;
            }

            if (!empty($group_in)) {
                $sql[] = '( ( ' . implode(' ', $group_sql) . ' )';
                $result = $this->_buildGroup($group_in, $ignore_list);
                if ($result) {
                    $sql[] = $result;
                }
                $sql[] = ' )';
            } else {
                $sql[] = '( ' . implode(' ', $group_sql) . ' )';
            }
        }
        if (!empty($sql)) {
            return implode (' ', $sql);
        }
    }

    function resetWhere()
    {
        $this->where = array();
    }

    function isDistinct()
    {
        return (bool)$this->_distinct;
    }

    function setDistinct($distinct=TRUE)
    {
        $this->_distinct = (bool)$distinct;
    }

    function addColumn($column, $max_min=NULL, $as=NULL)
    {
        if (!in_array(strtolower($max_min), array('max', 'min'))) {
            $max_min = NULL;
        }

        $table = $this->tables[0];
        if (strpos($column, '.')) {
            list($table, $column) = explode('.', $column);
            $this->addTable($table);
        }

        if (!empty($as)) {
            if (!PHPWS_DB::allowed($as)) {
                return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addColumn', $as);
            }
        }

        if (!PHPWS_DB::allowed($column)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addColumn', $column);
        }

        $col['table']    = $table;
        $col['name']     = $column;
        $col['max_min']  = $max_min;
        if ($column != '*') {
            $col['as']       = $as;
        }

        $this->columns[] = $col;
    }

    function getAllColumns()
    {
        $columns[] = $this->getColumn(TRUE);
        return $columns;
    }

    function getColumn($format=FALSE)
    {
        if ($format) {
            if (empty($this->columns)) {
                return $this->tables[0] . '.*';
            } else {
                foreach ($this->columns as $col) {
                    $as = null;
                    extract($col);
                    if ($max_min) {
                        $columns[] = strtoupper($max_min) . "($table.$name)";
                    } else {
                        if (!empty($as)) {
                            $columns[] = "$table.$name AS $as";
                        } else {
                            $columns[] = "$table.$name";
                        }
                    }
                }
                return implode(', ', $columns);
            }
        } else {
            return $this->columns;
        }
    }

    function setIndexBy($indexby)
    {
        $this->indexby = $indexby;
    }

    function getIndexBy()
    {
        return $this->indexby;
    }

    /**
     * Allows you to add an order or an array of orders to
     * a db query
     *
     * sending random or rand with or without the () will query random
     * element
     */

    function addOrder($order)
    {
        if (is_array($order)) {
            foreach ($order as $value) {
                $this->addOrder($value);
            }
        } else {
            $order = preg_replace('/[^\w\s\.]/', '', $order);

            if (preg_match('/(random|rand)(\(\))?/i', $order)) {
                $this->order[] = ignore::randomOrder();
            } else {
                if (strpos($order, '.')) {
                    list($table, $new_order) = explode('.', $order);
                    $this->order[] = array('table' => $table, 'column' => $new_order);
                } else {
                    $this->order[] = array('table' => $this->tables[0], 'column' => $order);
                }
            }
        }
    }

    function getOrder($dbReady=FALSE)
    {
        if (empty($this->order)) {
            return NULL;
        }

        if ($dbReady) {
            foreach ($this->order as $aOrder) {
                if (is_array($aOrder)) {
                    $order_list[] = $aOrder['table'] . '.' . $aOrder['column'];
                } else {
                    // for random orders
                    $order_list[] = $aOrder;
                }
            }
            return 'ORDER BY ' . implode(', ', $order_list);
        } else {
            return $this->order;
        }
    }

    function resetOrder()
    {
        $this->order = array();
    }

    function addValue($column, $value=NULL)
    {
        if (is_array($column)) {
            foreach ($column as $colKey=>$colVal){
                $result = $this->addValue($colKey, $colVal);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        } else {
            if (!PHPWS_DB::allowed($column)) {
                return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addValue', $column);
            }

            $this->values[$column] = $value;
        }
    }

    function getValue($column)
    {
        if (empty($this->values) || !isset($this->values[$column])) {
            return NULL;
        }

        return $this->values[$column];
    }

    function resetValues()
    {
        $this->values = array();
    }

    function getAllValues()
    {
        if (!isset($this->values) || empty($this->values)) {
            return NULL;
        }

        return $this->values;
    }


    function setLimit($limit, $offset=NULL)
    {
        unset($this->limit);

        if (is_array($limit)) {
            $_limit = $limit[0];
            $_offset = $limit[1];
        }
        elseif (preg_match('/,/', $limit)) {
            $split = explode(',', $limit);
            $_limit = trim($split[0]);
            $_offset = trim($split[1]);
        }
        else {
            $_limit = $limit;
            $_offset = $offset;
        }

        $this->limit['total'] = preg_replace('/[^\d\s]/', '', $_limit);

        if (isset($_offset)) {
            $this->limit['offset'] = preg_replace('/[^\d\s]/', '', $_offset);
        }

        return TRUE;
    }

    function getLimit($dbReady=FALSE)
    {
        if (empty($this->limit)) {
            return NULL;
        }
    
        if ($dbReady) {
            return $GLOBALS['PHPWS_DB']['lib']->getLimit($this->limit);
        }
        else {
            return $this->limit;
        }
    }

    function resetLimit()
    {
        $this->limit = '';
    }

    function resetColumns()
    {
        $this->columns = NULL;
    }


    function affectedRows()
    {
        $query =  PHPWS_DB::lastQuery();
        $process = strtolower(substr($query, 0, strpos($query, ' ')));

        if ($process == 'select') {
            return FALSE;
        }

        return $GLOBALS['PHPWS_DB']['connection']->affectedRows();
    }

    function reset()
    {
        $this->resetWhere();
        $this->resetValues();
        $this->resetLimit();
        $this->resetOrder();
        $this->resetColumns();
        $this->indexby = NULL;
        $this->qwhere  = NULL;
    }

    function lastQuery()
    {
        return $GLOBALS['PHPWS_DB']['connection']->last_query;
    }

    function insert($auto_index=TRUE)
    {
        PHPWS_DB::touchDB();
        $maxID = TRUE;
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::insert');
        }

        $values = $this->getAllValues();

        if (!isset($values)) {
            return PHPWS_Error::get(PHPWS_DB_NO_VALUES, 'core', 'PHPWS_DB::insert');
        }

        if ($auto_index) {
            $idColumn = $this->getIndex();

            if (PEAR::isError($idColumn)) {
                return $idColumn;
            } elseif(isset($idColumn)) {
                $check_table = $this->addPrefix($table);
                $maxID = $GLOBALS['PHPWS_DB']['connection']->nextId($check_table);
                $values[$idColumn] = $maxID;
            }
        }

        foreach ($values as $index=>$entry){
            $columns[] = $index;
            $set[] = PHPWS_DB::dbReady($entry);
        }

        $query = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $set) . ')';
     
        $result = PHPWS_DB::query($query);

        if (DB::isError($result)) {
            return $result;
        } else {
            return $maxID;
        }
    }

    function update()
    {
        PHPWS_DB::touchDB();
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::update');
        }

        $values = $this->getAllValues();
        $where = $this->getWhere(TRUE);

        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }

        if (empty($values)) {
            return PHPWS_Error::get(PHPWS_DB_NO_VALUES, 'core', 'PHPWS_DB::update');
        }

        foreach ($values as $index=>$data) {
            $columns[] = $index . ' = ' . PHPWS_DB::dbReady($data);
        }

        $query = "UPDATE $table SET " . implode(', ', $columns) ." $where";
        $result = PHPWS_DB::query($query);

        if (DB::isError($result)) {
            return $result;
        } else {
            return TRUE;
        }
    }

    function count()
    {
        return $this->select('count');
    }

    function getSelectSQL($type)
    {
        if ($type == 'count' && empty($this->columns)) {
            $columns = NULL;
        } else {
            $columns = implode(', ', $this->getAllColumns());
        }

        $table = $this->getTable();

        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::select');
        }

        $where   = $this->getWhere(TRUE);
        $order   = $this->getOrder(TRUE);
        $limit   = $this->getLimit(TRUE);
        $groupby = $this->getGroupBy(TRUE);

        $sql_array['columns'] = &$columns;
        $sql_array['table']   = &$table;
        $sql_array['where']   = &$where;
        $sql_array['groupby'] = &$groupby;
        $sql_array['order']   = &$order;
        $sql_array['limit']   = &$limit;

        return $sql_array;
    }


    /**
     * Retrieves information from the database.
     * Select utilizes parameters set previously in the object 
     * (i.e. addWhere, addColumn, setLimit, etc.)
     * You may also set the "type" of result: assoc (associative)
     * col (columns), min (minimum result), max (maximum result), one (a single column from a
     * single row), row (a single row), count (a tally of rows) or all, the default.
     * All returns an associate array containing the requested information.
     *
     */
    function select($type=NULL, $sql=NULL)
    {
        if (empty($sql)) {
            if (!empty($this->sql)) {
                $sql = & $this->sql;
            }
        }
        PHPWS_DB::touchDB();
        if (isset($type) && is_string($type)) {
            $type = strtolower($type);
        }

        $mode = $this->getMode();
        $indexby = $this->getIndexBy();

        if (!isset($sql)) {
            $sql_array = $this->getSelectSQL($type);
            if (PEAR::isError($sql_array)) {
                return $sql_array;
            }

            // extract will get $columns, $table, $where, $groupby
            // $order, and $limit
            extract($sql_array);

            if ($type == 'count') {
                if (empty($columns)) {
                    // order and groupby are not needed if count is
                    // using all rows
                    $order = null;
                    $groupby = null;
                    $columns = 'COUNT(*)';
                } else {
                    $add_group = $columns;
                    $columns .= ', COUNT(*)';   

                    if (empty($groupby)) {
                        $groupby = "GROUP BY $add_group";
                    } 
                }
            }

            if (!empty($where)) {
                $where = 'WHERE ' . $where;
            }

            if ($this->isDistinct()) {
                $distinct = 'DISTINCT';
            } else {
                $distinct = NULL;
            }


            $sql = "SELECT $distinct $columns FROM $table $where $groupby $order $limit";
        } else {
            $mode = DB_FETCHMODE_ASSOC;
        }

        $sql = PHPWS_DB::prefixQuery($sql);

        if ($this->_test_mode) {
            exit($sql);
        }

        // assoc does odd things if the resultant return is two items or less
        // not sure why it is coded that way. Use the default instead

        switch ($type){
        case 'assoc':
            return PHPWS_DB::autoTrim($GLOBALS['PHPWS_DB']['connection']->getAssoc($sql, NULL,NULL, $mode), $type);
            break;

        case 'col':
            if (empty($sql) && empty($this->columns)) {
                return PHPWS_Error::get(PHPWS_DB_NO_COLUMN_SET, 'core', 'PHPWS_DB::select');
            }

            if (isset($indexby)) {
                PHPWS_DB::logDB($sql);
                $result = PHPWS_DB::autoTrim($GLOBALS['PHPWS_DB']['connection']->getAll($sql, NULL, $mode), $type);
                if (PEAR::isError($result)) {
                    return $result;
                }

                return PHPWS_DB::_indexBy($result, $indexby, TRUE);
            }
            PHPWS_DB::logDB($sql);
            return PHPWS_DB::autoTrim($GLOBALS['PHPWS_DB']['connection']->getCol($sql), $type);
            break;

        case 'min':
        case 'max':
        case 'one':
            PHPWS_DB::logDB($sql);
            $value = $GLOBALS['PHPWS_DB']['connection']->getOne($sql, NULL, $mode);
            db_trim($value);
            return $value;
            break;

        case 'row':
            PHPWS_DB::logDB($sql);
            return PHPWS_DB::autoTrim($GLOBALS['PHPWS_DB']['connection']->getRow($sql, array(), $mode), $type);
            break;

        case 'count':
            PHPWS_DB::logDB($sql);
            if (empty($this->columns)) {
                $result = $GLOBALS['PHPWS_DB']['connection']->getRow($sql);
                if (PEAR::isError($result)) {
                    return $result;
                }
                return $result[0];
            } else {
                $result = $GLOBALS['PHPWS_DB']['connection']->getCol($sql);
                if (PEAR::isError($result)) {
                    return $result;
                }

                return count($result);
            }
            break;

        case 'all':
        default:
            PHPWS_DB::logDB($sql);
            $result = PHPWS_DB::autoTrim($GLOBALS['PHPWS_DB']['connection']->getAll($sql, NULL, $mode), $type);
            if (PEAR::isError($result)) {
                return $result;
            }

            if (isset($indexby)) {
                return PHPWS_DB::_indexBy($result, $indexby);
            }

            return $result;
            break;
        }
    }

    function getRow($sql)
    {
        $db = new PHPWS_DB;
        return $db->select('row', $sql);
    }

    function getCol($sql)
    {
        $db = new PHPWS_DB;
        return $db->select('col', $sql);
    }

    function getAll($sql)
    {
        $db = new PHPWS_DB;
        return $db->select('all', $sql);
    }

    function getOne($sql)
    {
        $db = new PHPWS_DB;
        return $db->select('one', $sql);
    }

    function getAssoc($sql)
    {
        $db = new PHPWS_DB;
        return $db->select('assoc', $sql);
    }

    function _indexBy($sql, $indexby, $colMode=FALSE){
        $rows = array();

        if (!is_array($sql)) {
            return $sql;
        }
        $stacked = FALSE;

        foreach ($sql as $item){
            if (!isset($item[(string)$indexby])) {
                return $sql;
            }

            if ($colMode) {
                $col = $this->getColumn();

                $value = $item[$indexby];
                unset($item[$indexby]);
                foreach ($col as $key=>$col_test) {
                    if ($col_test['name'] == $indexby) {
                        unset($col[$key]);
                        break;
                    }
                }

                $column = array_pop($col);
                PHPWS_DB::_expandIndex($rows, $value, $item[$column['name']], $stacked);
            } else {
                PHPWS_DB::_expandIndex($rows, $item[$indexby], $item, $stacked);
            }
        }

        return $rows;
    }

    function _expandIndex(&$rows, $index, $item, &$stacked)
    {
        if (isset($rows[$index])) {
            if (!$stacked) {
                $hold = $rows[$index];
                $rows[$index] = array();
                $rows[$index][] = $hold;
                $stacked = TRUE;
            }
            $rows[$index][] = $item;
        } else {
            $rows[$index] = $item;
        }
    }

    /**
     * increases the value of a table column
     */
    function incrementColumn($column_name, $amount=1)
    {
        $amount = (int)$amount;

        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::incrementColumn');
        }

        $where = $this->getWhere(TRUE);
        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }

        $query = "UPDATE $table SET $column_name = $column_name + $amount $where";
        $result = PHPWS_DB::query($query);

        if (DB::isError($result)) {
            return $result;
        } else {
            return TRUE;
        }
    }

    /**
     * reduces the value of a table column
     */

    function reduceColumn($column_name, $amount=1)
    {
        return $this->incrementColumn($column_name, ($amount * -1));
    }


    function delete()
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::delete');
        }

        $where = $this->getWhere(TRUE);

        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }
        $sql = "DELETE FROM $table $where";
        return PHPWS_DB::query($sql);
    }
  
    /**
     * Static call only
     * check_existence - of table
     * sequence_table  - if true, drop sequence table as well
     */
    function dropTable($table, $check_existence=TRUE, $sequence_table=TRUE)
    {
        PHPWS_DB::touchDB();

        // was using IF EXISTS but not cross compatible
        if ($check_existence && !PHPWS_DB::isTable($table)) {
            return TRUE;
        }

        $result = PHPWS_DB::query("DROP TABLE $table");

        if (PEAR::isError($result)) {
            return $result;
        }

        if ($sequence_table && PHPWS_DB::isSequence($table)) {
            $result = $GLOBALS['PHPWS_DB']['lib']->dropSequence($table . '_seq');
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return TRUE;
    }

    function isSequence($table)
    {
        $table = PHPWS_DB::addPrefix($table);
        return is_numeric($GLOBALS['PHPWS_DB']['connection']->nextId($table));
    }

    function truncateTable()
    {
        $table = $this->getTable();
        if(!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::truncateTable()');
        }

        $sql = "TRUNCATE TABLE $table";

        return PHPWS_DB::query($sql);
    }

    function dropTableIndex($name=NULL)
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::dropTableIndex');
        }
     
        if (empty($name)) {
            $name = str_replace('_', '', $table) . '_idx';
        }
	$sql = $GLOBALS['PHPWS_DB']['lib']->dropTableIndex($name, $table);
        return $this->query($sql);
    }

    /**
     * Creates an index on a table. column variable can be a string or
     * an array of strings representing column names.
     * The name of the index is optional. The function will create one based
     * on the table name. Setting your index name might be a smart thing to do
     * in case you ever need to DROP it.
     */
    function createTableIndex($column, $name=NULL)
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::createTableIndex');
        }

        if (is_array($column)) {
            foreach ($column as $col) {
                if (!$this->isTableColumn($col)) {
                    return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::createTableIndex');
                }
            }
            $column = implode(',', $column);
        } else {
            if (!$this->isTableColumn($column)) {
                return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::createTableIndex');
            }
        }

        if (empty($name)) {
            $name = str_replace('_', '', $table) . '_idx';
        }

        $sql = sprintf('CREATE INDEX %s ON %s (%s)', $name, $table, $column);

        return $this->query($sql);
    }


    function createTable()
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::createTable');
        }

        $values = $this->getAllValues();

        foreach ($values as $column=>$value) {
            $parameters[] = $column . ' ' . $value;
        }


        $sql = "CREATE TABLE $table ( " . implode(', ', $parameters) . ' )';
        return PHPWS_DB::query($sql);
    }

    /**
     * Renames a table column
     * Because databases disagree on their commands to change column
     * names, this function requires different factory files.
     */
    function renameTableColumn($old_name, $new_name)
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::addColumn');
        }

        $specs = $this->getColumnInfo($old_name, TRUE);
        $sql = $GLOBALS['PHPWS_DB']['lib']->renameColumn($table, $old_name, $new_name, $specs);
        return $this->query($sql);
    }

    /**
     * Adds a column to the database table
     *
     * @param string  column    Name of column to add
     * @param string  parameter Specifics of table column
     * @param string  after     If supported, add column after this column
     * @param boolean indexed   Create an index on the column if true
     */
    function addTableColumn($column, $parameter, $after=NULL, $indexed=FALSE)
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::addColumn');
        }

        if (!PHPWS_DB::allowed($column)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addTableColumn', $column);
        }

        if (DB_USE_AFTER && isset($after)) {
            if (strtolower($after) == 'first') {
                $location = 'FIRST';
            } else {
                $location = "AFTER $after";
            }
        } else {
            $location = NULL;
        }

        $sql = "ALTER TABLE $table ADD $column $parameter $location";

        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($indexed == TRUE) {
            $indexSql = "CREATE INDEX $column on $table($column)";
            $result = PHPWS_DB::query($indexSql);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return TRUE;
    }


    function dropTableColumn($column)
    {
        $table = $this->getTable();
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::dropColumn');
        }

        if (!PHPWS_DB::allowed($column)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::dropTableColumn', $column);
        }

        $sql = "ALTER TABLE $table DROP $column";

        return PHPWS_DB::query($sql);
    }


    function getDBType()
    {
        return $GLOBALS['PHPWS_DB']['connection']->phptype;
    }


    function disconnect()
    {
        foreach ($GLOBALS['PHPWS_DB']['dbs'] as $db) {
            if (!empty($db['connection'])) {
                $db['connection']->disconnect();
            }
        }
        unset($GLOBALS['PHPWS_DB']);
    }

    /**
     * Imports a SQL dump file into the database.
     * This function can not be called statically.
     */

    function importFile($filename)
    {
        if (!is_file($filename)) {
            return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'core', 'PHPWS_DB::importFile');
        }
        $data = file_get_contents($filename);
        return PHPWS_DB::import($data);
    }

    /**
     * Imports a SQL dump into the database.
     * This function can not be called statically.
     */
    function import($text, $report_errors=TRUE)
    {
        PHPWS_DB::touchDB();

        // first_import makes sure at least one query was completed
        // successfully
        $first_import = FALSE;

        $sqlArray = PHPWS_Text::sentence($text);
        $error = FALSE;

        foreach ($sqlArray as $sqlRow){
            if (empty($sqlRow) || preg_match("/^[^\w\d\s\\(\)]/i", $sqlRow)) {
                continue;
            }

            $sqlCommand[] = $sqlRow;

            if (preg_match("/;$/", $sqlRow)) {
                $query = implode(' ', $sqlCommand);

                $sqlCommand = array();

                PHPWS_DB::homogenize($query);

                $result = PHPWS_DB::query($query);

                if (DB::isError($result)) {
                    if ($report_errors) {
                        return $result;
                    } else {
                        PHPWS_Error::log($result);
                        $error = TRUE;
                    }
                }
                $first_import = TRUE;
            }
        }

        if (!$first_import) {
            if ($report_errors) {
                return PHPWS_Error::get(PHPWS_DB_IMPORT_FAILED, 'core', 'PHPWS_DB::import');
            } else {
                PHPWS_Error::log(PHPWS_DB_IMPORT_FAILED, 'core', 'PHPWS_DB::import');
                $error = TRUE;                    
            }
        }

        if ($error) {
            return FALSE;
        } else {
            return TRUE;
        }
    }


    function homogenize(&$query)
    {
        $query_list = explode(',', $query);

        $from = array('/int\(\d+\)/iU',
                      '/mediumtext/'
                      );
        $to = array('int',
                    'text');

        foreach ($query_list as $command) {
            if (preg_match ('/\s(smallint|int)\s/i', $command)) {
                if(!preg_match('/\sdefault/i', $command)) {
                    $command = preg_replace('/ int /i', ' int default 0 ', $command);
                }

                if(!preg_match('/\snull/i', $command)) {
                    $command = preg_replace('/ int /i', ' int not null ', $command);
                }

            }

            $command = preg_replace($from, $to, $command);
            $newlist[] = $command;
        }

        $query = implode(',', $newlist);

        $GLOBALS['PHPWS_DB']['lib']->readyImport($query);
    }


    function parsePearCol($info, $strip_name=FALSE)
    {
        $setting = $GLOBALS['PHPWS_DB']['lib']->export($info);

        if (isset($info['flags'])) {
            if (stristr($info['flags'], 'multiple_key')) {
                $column_info['index'] = 'CREATE INDEX ' .  $info['name'] . ' on ' . $info['table'] 
                    . '(' . $info['name'] . ')';
                $info['flags'] = str_replace(' multiple_key', '', $info['flags']);
            }
            $preFlag = array('/not_null/', '/primary_key/', '/default_(\w+)?/', '/blob/', '/%3a%3asmallint/i');
            $postFlag = array('NOT NULL', 'PRIMARY KEY', "DEFAULT '\\1'", '', '');
            $multipleFlag = array('multiple_key', '');
            $flags = ' ' . preg_replace($preFlag, $postFlag, $info['flags']);
        }
        else {
            $flags = NULL;
        }

        if ($strip_name == TRUE) {
            $column_info['parameters'] = $setting . $flags; 
        }
        else {
            $column_info['parameters'] = $info['name'] . " $setting" . $flags; 
        }

        return $column_info;
    }

    function parseColumns($columns)
    {
        foreach ($columns as $info){
            if (!is_array($info)) {
                continue;
            }

            $result = $this->parsePearCol($info);
            if (isset($result['index'])) {
                $column_info['index'][] = $result['index'];
            }

            $column_info['parameters'][] = $result['parameters'];
        }

        return $column_info;
    }

    function export($structure=TRUE, $contents=TRUE)
    {
        PHPWS_DB::touchDB();

        if ($structure == TRUE) {
            $table = $this->addPrefix($this->table);
            $columns =  $GLOBALS['PHPWS_DB']['connection']->tableInfo($table);

            $column_info = $this->parseColumns($columns);
            $index = $this->getIndex();

            $sql[] = "CREATE TABLE $tableName ( " .  implode(', ', $column_info['parameters']) .' );';
            if (isset($column_info['index'])) {
                $sql = array_merge($sql, $column_info['index']);
            }
        }

        if ($contents == TRUE) {
            if ($rows = $this->select()) {
                if (PEAR::isError($rows)) {
                    return $rows;
                }
                foreach ($rows as $dataRow){
                    foreach ($dataRow as $key=>$value){
                        $allKeys[] = $key;
                        $allValues[] = PHPWS_DB::quote($value);
                    }
          
                    $sql[] = "INSERT INTO $tableName (" . implode(', ', $allKeys) . ') VALUES (' . implode(', ', $allValues) . ');';
                    $allKeys = $allValues = array();
                }
            }
        }

        return implode("\n", $sql);
    }

    function quote($text)
    {
        return $GLOBALS['PHPWS_DB']['connection']->quote($text);
    }

    function extractTableName($sql_value)
    {
        $temp = explode(' ', trim($sql_value));
        
        if (!is_array($temp)) {
            return NULL;
        }
        foreach ($temp as $whatever){
            if (empty($whatever)) {
                continue;
            }
            $format[] = $whatever;
        }

        if (empty($format)) {
            return NULL;
        }

        switch (trim(strtolower($format[0]))) {
        case 'insert':
            if (stristr($format[1], 'into')) {
                return preg_replace('/\(+.*$/', '', str_replace('`', '', $format[2]));
            } else {
                return preg_replace('/\(+.*$/', '', str_replace('`', '', $format[1]));
            }
            break;
        
        case 'update':
            return preg_replace('/\(+.*$/', '', str_replace('`', '', $format[1]));
            break;
      
        case 'select':
        case 'show':
            return preg_replace('/\(+.*$/', '', str_replace('`', '', $format[3]));
            break;

        case 'drop':
        case 'alter':
            return preg_replace('/;/', '', str_replace('`', '', $format[2]));
            break;

        default:
            return preg_replace('/\W/', '', $format[2]);
            break;
        }
    }// END FUNC extractTableName


    /**
     * Prepares a value for database writing or reading
     *
     * @author Matt McNaney <matt at NOSPAM dot tux dot appstate dot edu>
     * @param  mixed $value The value to prepare for the database.
     * @return mixed $value The prepared value
     * @access public
     */
    function dbReady($value=NULL) 
    {
        if (is_array($value) || is_object($value)) {
            return PHPWS_DB::dbReady(serialize($value));
        } elseif (is_string($value)) {
            return "'" . $GLOBALS['PHPWS_DB']['connection']->escapeSimple($value) . "'";
        } elseif (is_null($value)) {
            return 'NULL';
        } elseif (is_bool($value)) {
            return ($value ? 1 : 0);
        } else {
            return $value;
        }
    }// END FUNC dbReady()

    /**
     * @author Matt McNaney <matt at tux dot appstate dot edu>
     * @param  object $object        Object variable filled with result.
     * @param  object $require_where If TRUE, require a where parameter or
     *                               have the id set
     * @return boolean               Returns TRUE if plugObject is successful
     * @access public
     */
    function loadObject(&$object, $require_where=TRUE)
    {
        if (!is_object($object)) {
            return PHPWS_Error::get(PHPWS_DB_NOT_OBJECT, 'core', 'PHPWS_DB::loadObject');
        }

        if ($require_where && empty($object->id) && empty($this->where)) {
            return PHPWS_Error::get(PHPWS_DB_NO_ID, 'core', 'PHPWS_DB::loadObject');
        }

        if ($require_where && empty($this->where)) {
            $this->addWhere('id', $object->id);
        }

        $variables = $this->select('row');

        if (PEAR::isError($variables)) {
            return $variables;
        } elseif (empty($variables)) {
            return NULL;
        }

        return PHPWS_Core::plugObject($object, $variables);
    }// END FUNC loadObject

    /**
     * Creates an array of objects constructed from the submitted
     * class name.
     *
     * Use this function instead of select() to get an array of objects.
     * Note that your class variables and column names MUST match exactly.
     * Unmatched pairs will be ignored.
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string $className Name of object class
     * @return array $items     Array of objects
     * @access public
     */
    function getObjects($className)
    {
        if (!class_exists($className)) {
            return PHPWS_Error::get(PHPWS_CLASS_NOT_EXIST, 'core', 'PHPWS_DB::getObjects', $className);
        }

        $items = NULL;
        
        $result = $this->select();

        if (PEAR::isError($result) || !isset($result)) {
            return $result;
        }

        $num_args = func_num_args();
        $args = func_get_args();
        array_shift($args);

        foreach ($result as $indexby => $itemResult) {
            $genClass = new $className;

            if ($num_args > 1) {
                call_user_func_array(array($genClass, $className), $args);
            }

            PHPWS_Core::plugObject($genClass, $itemResult);
            $items[$indexby] = $genClass;
        }

        return $items;
    }

    function saveObject(&$object, $stripChar=FALSE, $autodetect_id=TRUE)
    {
        if (!is_object($object)) {
            return PHPWS_Error::get(PHPWS_WRONG_TYPE, 'core', 'PHPWS_DB::saveObject', _('Type') . ': ' . gettype($object));
        }

        $object_vars = get_object_vars($object);

        if (!is_array($object_vars)) {
            return PHPWS_Error::get(PHPWS_DB_NO_OBJ_VARS, 'core', 'PHPWS_DB::saveObject');
        }

        foreach ($object_vars as $column => $value){
            if ($stripChar == TRUE) {
                $column = substr($column, 1);
            }

            if (!$this->isTableColumn($column)) {
                continue;
            }

            if ($autodetect_id && ($column == 'id' && $value > 0)) {
                $this->addWhere('id', $value);
            }

            $this->addValue($column, $value);
        }

        if (isset($this->qwhere) ||
            ((isset($this->where) && count($this->where)))) {
            $result = $this->update();
        }
        else {
            $result = $this->insert();

            if (is_numeric($result)) {
                if (array_key_exists('id', $object_vars)) {
                    $object->id = (int)$result;
                } elseif (array_key_exists('_id', $object_vars)) {
                    $object->_id = (int)$result;
                }
            }
        }

        $this->resetValues();

        return $result;
    }

  
    function allowed($value)
    {
        if (!is_string($value)) {
            return FALSE;
        }

        $reserved = array('ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'AUTO_INCREMENT', 'BDB',
                          'BERKELEYDB', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BTREE', 'BY', 'CASCADE',
                          'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'COLLATE', 'COLUMN', 'COLUMNS', 'CONSTRAINT', 'CREATE',
                          'CROSS', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'DATABASE', 'DATABASES', 'DATE',
                          'DAY_HOUR', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DEFAULT',
                          'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DISTINCT', 'DISTINCTROW',
                          'DOUBLE', 'DROP', 'ELSE', 'ENCLOSED', 'ERRORS', 'ESCAPED', 'EXISTS', 'EXPLAIN', 'FALSE', 'FIELDS',
                          'FLOAT', 'FOR', 'FOREIGN', 'FROM', 'FULLTEXT', 'FUNCTION', 'GEOMETRY', 'GRANT', 'GROUP',
                          'HASH', 'HAVING', 'HELP', 'HIGH_PRIORITY', 'HOUR_MINUTE', 'HOUR_SECOND',
                          'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INNODB', 'INSERT', 'INT',
                          'INTEGER', 'INTERVAL', 'INTO', 'IS', 'JOIN', 'KEY', 'KEYS', 'KILL', 'LEADING',
                          'LEFT', 'LIKE', 'LIMIT', 'LINES', 'LOAD', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT',
                          'LOW_PRIORITY', 'MASTER_SERVER_ID', 'MATCH', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 
                          'MIDDLEINT', 'MINUTE_SECOND', 'MRG_MYISAM', 'NATURAL', 'NOT', 'NULL', 'NUMERIC', 'ON', 'OPTIMIZE',
                          'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUTER', 'OUTFILE', 'PRECISION', 'PRIMARY', 'PRIVILEGES',
                          'PROCEDURE', 'PURGE', 'READ', 'REAL', 'REFERENCES', 'REGEXP', 'RENAME', 'REPLACE', 'REQUIRE',
                          'RESTRICT', 'RETURNS', 'REVOKE', 'RIGHT', 'RLIKE', 'RTREE', 'SELECT', 'SET', 'SHOW',
                          'SMALLINT', 'SONAME', 'SPATIAL', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT',
                          'SSL', 'STARTING', 'STRAIGHT_JOIN', 'STRIPED', 'TABLE', 'TABLES', 'TERMINATED', 'THEN', 'TINYBLOB',
                          'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRUE', 'TYPES', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED',
                          'UPDATE', 'USAGE', 'USE', 'USER_RESOURCES', 'USING', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARYING',
                          'WARNINGS', 'WHEN', 'WHERE', 'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL');

        if(in_array(strtoupper($value), $reserved)) {
            return FALSE;
        }

        if(preg_match('/[^\w\*\.]/', $value)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Crutch function from old database
     */
    function sqlFriendlyName($name) {
        if (!PHPWS_DB::allowed($name)) {
            return FALSE;
        }

        return preg_replace('/\W/', '', $name);
    }

    /**
     * Postgres adds an extra space on the end of select results.
     * autoTrim removes it.
     */

    function autoTrim($sql, $type)
    {
        if (PEAR::isError($sql) || !is_array($sql)) {
            return $sql;
        }

        if (!count($sql)) {
            return NULL;
        }

        if ($GLOBALS['PHPWS_DB']['connection']->phptype != 'pgsql') {
            return $sql;
        }

        switch ($type){
        case 'col':
            array_walk($sql, 'db_trim');
            break;

        default:
            array_walk($sql, 'db_trim');
            break;
        }

        return $sql;
    }

    function updateSequenceTable()
    {
        $this->addColumn('id', 'max');

        $max_id = $this->select('one');

        if (PEAR::isError($max_id)) {
            return $max_id;
        }

        if ($max_id > 0) {
            $seq_table = $this->getTable() . '_seq';
            if (!$this->isTable($seq_table)) {
                $table = $this->addPrefix($this->getTable());
                $GLOBALS['PHPWS_DB']['connection']->nextId($table);
            }

            $seq = new PHPWS_DB($seq_table);
            $seq->addValue('id', $max_id);
            return $seq->update();
        }

        return TRUE;
    }

    function addPrefix($table)
    {
        if (isset($GLOBALS['PHPWS_DB']['tbl_prefix'])) {
            return $GLOBALS['PHPWS_DB']['tbl_prefix'] . $table;
        }
        return $table;
    }

    function getPrefix()
    {
        if (isset($GLOBALS['PHPWS_DB']['tbl_prefix'])) {
            return $GLOBALS['PHPWS_DB']['tbl_prefix'];
        }
        return null;
    }

    function prefixQuery($sql)
    {
        if (empty($GLOBALS['PHPWS_DB']['tbl_prefix'])) {
            return $sql;
        }
        $tables = PHPWS_DB::pullTables($sql);

        if (empty($tables)) {
            return $sql;
        }
        
        foreach ($tables as $tbl) {
            $tbl = trim($tbl);
            $sql = preg_replace("/$tbl(\W)|$tbl$/", $GLOBALS['PHPWS_DB']['tbl_prefix'] . $tbl . '\\1', $sql);
        }
        return $sql;
    }
    

    function pullTables($sql)
    {
        $sql = preg_replace('/ {2,}/', ' ', trim($sql));
        $sql = preg_replace('/[\n\r]/', ' ', $sql);
        $command = substr($sql, 0,strpos($sql, ' '));

        switch(strtolower($command)) {
        case 'alter':
            if (!preg_match('/alter table/i', $sql)) {
                return false;
            }
            $aQuery = explode(' ', preg_replace('/[^\w\s]/', '', $sql));
            $tables[] = $aQuery[2];
            break;

        case 'create':
            if (preg_match('/^create index/i', $sql)) {
                $start = stripos($sql, ' on ') + 4;
                $para = stripos($sql, '(');
                $length = $para - $start;
                $table =  substr($sql, $start, $length);
            } else {
                $aTable = explode(' ', $sql);
                $table = $aTable[2];
            }
            $tables[] = trim(preg_replace('/\W/', '', $table));
            break;
        
        case 'delete':
            $start = stripos($sql, 'from') + 4;
            $end = strlen($sql) - $start;
            $table = substr($sql, $start, $end);
            $table = preg_replace('/where.*/i', '', $table);
            if (preg_match('/using/i', $table)) {
                $table = preg_replace('/[^\w\s,]/', '', $table);
                $table = preg_replace('/\w+ using/iU', '', $table);
                return explode(',', preg_replace('/[^\w,]/', '', $table));
            }
            $tables[] = preg_replace('/\W/', '', $table);
            break;

        case 'drop':
            $start = stripos($sql, 'on') + 2;
            $length = strlen($sql) - $start;
            if (preg_match('/^drop index/i', $sql)) {
                $table = substr($sql, $start, $length);
                $tables[] = preg_replace('/[^\w,]/', '', $table);
            } else {
                $table = preg_replace('/drop |table |if exists/i', '', $sql);
                return explode(',', preg_replace('/[^\w,]/', '', $table));
            }
            break;

        case 'insert':
            $table =  preg_replace('/insert |into | values|\(.*\)/iU', '', $sql);
            $tables[] = preg_replace('/\W/', '', $table);
            break;

        case 'select':
            $start = stripos($sql, 'from') + 4;
            $table = substr($sql, $start, strlen($sql) - $start);
            if ($where = stripos($table, ' where ')) {
                $table = substr($table, 0, $where);
            }

            if ($order = stripos($table, ' order by')) {
                $table = substr($table, 0, $order);
            }

            if ($group = stripos($table, ' group by')) {
                $table = substr($table, 0, $group);
            }

            if ($having = stripos($table, ' having ')) {
                $table = substr($table, 0, $having);
            }

            if ($limit = stripos($table, ' limit ')) {
                $table = substr($table, 0, $limit);
            }

            $table = str_ireplace(' join ', ' ', $table);
            $table = str_ireplace(' right ', ' ', $table);
            $table = str_ireplace(' left ', ' ', $table);
            $table = str_ireplace(' inner ', ' ', $table);
            $table = str_ireplace(' outer ', ' ', $table);
            $table = str_ireplace(' on ', ' ', $table);
            $table = str_ireplace('=', ' ', $table);
            $table = str_ireplace(',', ' ', $table);
            $table = preg_replace('/ \w+\.\w+ /U', ' ', $table);
            $table = preg_replace('/(as \w+)/i', '', $table);
            $table = preg_replace('/ {2,}/', ' ', trim($table));
            $tables = explode(' ', $table);
            return $tables;
            break;

        case 'update':
            $aTable = explode(' ', $sql);
            $tables[] = preg_replace('/\W/', '', $aTable[1]);
            break;
        }

        return $tables;
    }
}

class PHPWS_DB_Where {
    var $table      = NULL;
    var $column     = NULL;
    var $value      = NULL;
    var $operator   = '=';
    var $conj       = 'AND';
    var $join       = FALSE;

    function setJoinTable($table)
    {
        $this->join_table = $table;
    }

    function setColumn($column)
    {
        $this->column = $column;
    }

    function setOperator($operator)
    {
        if (empty($operator)) {
            return FALSE;
        }

        if (!PHPWS_DB::checkOperator($operator)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_OP, 'core', 'PHPWS_DB::addWhere', _('DB Operator:') . $operator);
        }

        $this->operator = $operator;
    }

    function setJoin($join)
    {
        $this->join = (bool)$join;
    }

    function setValue($value)
    {
        $this->value = $value;
    }

    function setTable($table)
    {
        $this->table = $table;
    }
    

    function setConj($conj)
    {
        $conj = strtoupper($conj);
        if (empty($conj) || ($conj != 'OR' &&  $conj != 'AND')) {
            return FALSE;
        }

        $this->conj = $conj;
    }

    function getValue()
    {
        $value = $this->value;

        if (is_array($value)) {
            switch ($this->operator){
            case 'IN':
                foreach ($value as $temp_val) {
                    if ($temp_val != 'NULL') {
                        $temp_val_list[] = "'$temp_val'";
                    } else {
                        $temp_val_list[] = $temp_val;
                    }
                }
                $value = '(' . implode(', ', $temp_val_list) . ')';

                break;

            case 'BETWEEN':
                $value = sprintf("'{%s}' AND '{%s}'", $this->value[0], $this->$value[1]);
                break;
            }
            return $value;
        }

        // If this is not a joined where, return the escaped value
        if (!$this->join && $value != 'NULL') {
            return sprintf('\'%s\'', $value);
        } else {
            // This is a joined value, return table.value
            return $value;
        }

    }

    function get()
    {
        $column = $this->table . '.' . $this->column;
        $value = $this->getValue();
        $operator = &$this->operator;
        return sprintf('%s %s %s', $column, $operator, $value);
    }
} // END PHPWS_DB_Where


/**
 * See autoTrim for information
 */

function db_trim(&$value)
{
    if (PEAR::isError($value) || !isset($value)) {
        return;
    }

    if (is_array($value)){
        array_walk($value, 'db_trim');
        return;
    }
    
    $value = rtrim($value);
}

?>
