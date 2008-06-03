<?php
/**
 * A database class
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */
require_once 'DB.php';

// Changing LOG_DB to true will cause ALL DB traffic to get logged
// This can log can get very large, very fast. DO NOT enable it
// on a live server. It is for development purposes only.
define ('LOG_DB', false);

define ('DEFAULT_MODE', DB_FETCHMODE_ASSOC);

if (!defined('DB_ALLOW_TABLE_INDEX')) {
    define ('DB_ALLOW_TABLE_INDEX', true);
 }

if (!defined('ALLOW_TABLE_LOCKS')) {
    define('ALLOW_TABLE_LOCKS', false);
}

class PHPWS_DB {
    var $tables      = null;
    var $where       = array();
    var $order       = array();
    var $values      = array();
    var $mode        = DEFAULT_MODE;
    var $limit       = null;
    var $index       = null;
    var $columns     = null;
    var $qwhere      = null;
    var $indexby     = null;
    var $group_by     = null;
    var $locked      = null;

    /**
     * Holds module and class file names to be loaded on
     * the success of a loadObject or getObjects select query.
     */
    var $load_class  = null;

    /**
     * allows you to group together where queries
     */
    var $group_in    = array();
    // This variable holds a sql query string
    var $sql         = null;
    var $_allColumns = null;
    var $_columnInfo = null;
    var $_lock       = false;

    // contains the database specific factory class
    var $_distinct   = false;
    var $_test_mode  = false;
    var $_join       = null;
    var $_join_tables = null;
    var $table_as     = array();


    function PHPWS_DB($table=null)
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

    function setTestMode($mode=true)
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

    function loadDB($dsn=null, $tbl_prefix=null, $force_reconnect=false, $show_error=true)
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
        if ($result == false) {
            PHPWS_DB::logDB(_('Failed to connect.'));
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', 'PHPWS_DB::loadDB', 
                             PHPWS_SOURCE_DIR . 'core/class/DB/' . $type . '.php');
            PHPWS_Core::errorPage();
        }

        $class_name = $type . '_PHPWS_SQL';
        $dblib = new $class_name;
        if (!empty($dblib->portability)) {
            $connect->setOption('portability', $dblib->portability);
        }

        $GLOBALS['PHPWS_DB']['dbs'][$key]['lib']        = $dblib;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['dsn']        = $dsn;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['connection'] = $connect;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['tbl_prefix'] = $tbl_prefix;
        $GLOBALS['PHPWS_DB']['dbs'][$key]['type']       = $type;
        PHPWS_DB::_updateCurrent($key);
        
        return true;
    }

    function logDB($sql)
    {
        if (!defined('LOG_DB') || LOG_DB != true) {
            return;
        }

        PHPWS_Core::log($sql, 'db.log');
    }

    function query($sql, $prefix=true)
    {
        if (isset($this) && !empty($this->_test_mode)) {
            exit($sql);
        }

        PHPWS_DB::touchDB();
        if ($prefix) {
            $sql = PHPWS_DB::prefixQuery($sql);
        }

        PHPWS_DB::logDB($sql);

        return $GLOBALS['PHPWS_DB']['connection']->query($sql);
    }

    function getColumnInfo($col_name, $parsed=false)
    {
        if (!isset($this->_columnInfo)) {
            $this->getTableColumns();
        }

        if (isset($this->_columnInfo[$col_name])) {
            if ($parsed == true) {
                return $this->parsePearCol($this->_columnInfo[$col_name], true);
            } else {
                return $this->_columnInfo[$col_name];
            }
        }
        else {
            return null;
        }
    }

    function inDatabase($table, $column=null)
    {
        $table = PHPWS_DB::addPrefix($table);

        PHPWS_DB::touchDB();
        static $database_info = null;

        $column = trim($column);
        $answer = false;

        if (!empty($database_info[$table])) {
            if (empty($column)) {
                return true;
            } else {
                return in_array($column, $database_info[$table]);
            }
        }

        $result = $GLOBALS['PHPWS_DB']['connection']->tableInfo(strip_tags($table));
        if (PEAR::isError($result)) {
            if ($result->getCode() == DB_ERROR_NEED_MORE_DATA) {
                return false;
            } else {
                return $result;
            }
        }

        foreach ($result as $colInfo) {
            $list_columns[] = $colInfo['name'];

            if ($colInfo['name'] == $column) {
                $answer = true;
            }
        }

        $database_info[$table] = $list_columns;

        return $answer;
    }

    /**
     * Gets information on all the columns in the current table
     */
    function getTableColumns($fullInfo=false)
    {
        static $table_check = null;

        $table_compare = implode(':', $this->tables);

        if (!$table_check || $table_check == $table_compare) {
            if (isset($this->_allColumns) && $fullInfo == false) {
                return $this->_allColumns;
            } elseif (isset($this->_columnInfo) && $fullInfo == true) {
                return $this->_columnInfo;
            }
        }

        $table_check = $table_compare;
        foreach ($this->tables as $table) {
            if (!isset($table)) {
                return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::getTableColumns');
            }
            
            $table = $this->addPrefix($table);
            
            $columns =  $GLOBALS['PHPWS_DB']['connection']->tableInfo($table);
            
            if (PEAR::isError($columns)) {
                return $columns;
            }
            
            foreach ($columns as $colInfo) {
                $col_name = & $colInfo['name'];
                $this->_columnInfo[$col_name] = $colInfo;
                $this->_allColumns[$col_name] = $col_name;
            }
        }

        if ($fullInfo == true) {
            return $this->_columnInfo;
        } else {
            return $this->_allColumns;
        }
    }

    /**
     * Returns true is the columnName is contained in the
     * current table
     */
    function isTableColumn($column_name)
    {
        $columns = $this->getTableColumns();
        if (PEAR::isError($columns)) {
            return $columns;
        }
        if (strpos($column_name, '.')) {
            $a = explode('.', $column_name);
            $column_name = array_pop($a);
        }

        return in_array($column_name, $columns);
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

    function addJoin($join_type, $join_from, $join_to, $join_on_1=null, $join_on_2=null)
    {
        if (!preg_match('/left|right/i', $join_type)) {
            return false;
        }
        $this->_join_tables[] = array('join_type' => $join_type,
                                      'join_from' => $join_from,
                                      'join_to'   => $join_to,
                                      'join_on_1' => $join_on_1,
                                      'join_on_2' => $join_on_2);
    }

    function addTable($table, $as=null)
    {
        if (is_array($table)) {
            foreach ($table as $tbl_name) {
                $this->addTable($tbl_name);
            }
            return;
        }
        if (PHPWS_DB::allowed($table)) {
            if ($as) {
                $this->table_as[$as] = $table;
            } elseif (empty($this->tables) || !in_array($table, $this->tables)) {
                $this->tables[] = $table;
            }
        } else {
            return PHPWS_Error::get(PHPWS_DB_BAD_TABLE_NAME, 'core', 'PHPWS_DB::addTable', $table);
        }
        return true;
    }

    function setTable($table)
    {
        $this->tables = array();
        $this->_join_tables = null;
        $this->_columnInfo = null;
        $this->_allColumns = null;
        return $this->addTable($table);
    }

    function setIndex($index)
    {
        $this->index = $index;
    }

    function getIndex($table=null)
    {
        if (isset($this->index)) {
            return $this->index;
        }

        if (empty($table)) {
            $table = $this->getTable(false);
        }

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

        return null;
    }

    function _getJoinOn($join_on_1, $join_on_2, $table1, $table2) {
        if (empty($join_on_1) || empty($join_on_2)) {
            return null;
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
            return null;
        }

        $join_info['tables'] = array();

        foreach ($this->_join_tables as $join_array) {
            extract($join_array);

            if ($result = $this->_getJoinOn($join_on_1, $join_on_2, $join_from, $join_to)) {
                $join_on = 'ON ' . $result;
            }

            if (isset($this->table_as[$join_to])) {
                $join_to = sprintf('%s as %s', $this->table_as[$join_to], $join_to);
            }

            if (isset($this->table_as[$join_from])) {
                $join_from = sprintf('%s as %s', $this->table_as[$join_from], $join_from);
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

    /**
     * if format is true, all tables in the array are returned. This
     * is used for select queries. If false, the first table is popped
     * off and returned
     */
    function getTable($format=true)
    {
        if (empty($this->tables)) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::getTable');
        }

        if ($format == true) {
            $join_info = $this->getJoin();

            foreach ($this->tables as $table) {
                if ($join_info && in_array($table, $join_info['tables'])) {
                    continue;
                }

                $table_list[] = $table;
            }

            if ($join_info) {
                $table_list[] = $join_info['join'];
            } elseif (!empty($this->table_as)) {
                foreach ($this->table_as as $sub => $table) {
                    $table_list[] = sprintf('%s as %s', $table, $sub);
                }
            }
            return implode(',', $table_list);
        } else {
            foreach ($this->tables as $table);
            return $table;
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
            return false;
        }

        $this->where[$group]['conj'] = $conj;
    }

    function addGroupBy($group_by)
    {
        if (PHPWS_DB::allowed($group_by)) {
            if (!strpos($group_by, '.')) {
                $group_by = $this->tables[0] . '.' . $group_by;
            }

            if (empty($this->group_by) || !in_array($group_by, $this->group_by)) {
                $this->group_by[] = $group_by;
            }
        }
        return true;
    }

    function getGroupBy($dbReady=false)
    {
        if ((bool)$dbReady == true) {
            if (empty($this->group_by)) {
                return null;
            } else {
                return 'GROUP BY ' . implode(', ', $this->group_by);
            }
        }
        return $this->group_by;
    }

    /**
     * Puts the first group label into the second
     */
    function groupIn($sub, $main)
    {
        $group_names = array_keys($this->where);
        if (!in_array($sub, $group_names) || !in_array($main, $group_names)) {
            return false;
        }
        $this->group_in[$sub] = $main;
        return true;
    }


    function addWhere($column, $value=null, $operator=null, $conj=null, $group=null, $join=false)
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
            return true;
        } else {
            if (!PHPWS_DB::allowed($column)) {
                return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core',
                                        'PHPWS_DB::addWhere', $column);
            }
        }

        if (is_array($value) && !empty($value)) {
            if (!empty($operator) && $operator != 'IN' && $operator != 'NOT IN' &&
                $operator != 'BETWEEN' && $operator != 'NOT BETWEEN') {
                $search_in = true;
            } else {
                if (empty($operator)) {
                    $operator = 'IN';
                }
                $search_in = false;
            }

            foreach ($value as $newVal) {
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
                return true;
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

                if (isset($this->table_as[$join_table])) {
                    $source_table = $join_table;
                    $column = & $join_column;
                } elseif (PHPWS_DB::inDatabase($join_table, $join_column)) {
                    $column = & $join_column;
                    $source_table = $join_table;
                    $this->addTable($join_table);
                }
            }
	}

        $where->setColumn($column);
        $where->setTable($source_table);

	if (is_string($value)) {
            if (substr_count($value, '.') == 1) {
                list($join_table, $join_column) = explode('.', $value);
                if (isset($this->table_as[$join_table])) {
                    $where->setJoin(true);
                } elseif ($this->inDatabase($join_table, $join_column)) {
                    $where->setJoin(true);
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
                         'ILIKE',
                         'NOT LIKE',
                         'NOT ILIKE',
                         'REGEXP',
                         'RLIKE',
                         'IN',
                         'NOT IN',
                         'BETWEEN',
                         'NOT BETWEEN',
                         'IS',
                         'IS NOT',
                         '~');

        return in_array(strtoupper($operator), $allowed);
    }

    function setQWhere($where, $conj='AND')
    {
        $conj = strtoupper($conj);
        if (empty($conj) || ($conj != 'OR' &&  $conj != 'AND')) {
            return false;
        }
        
        $where = preg_replace('/where/i', '', $where);
        $this->qwhere['where'] = $where;
        $this->qwhere['conj']  = $conj;
    }


    /**
     * Grabs the where variables from the object and creates a sql query
     */
    function getWhere($dbReady=false)
    {
        $sql = array();
        $ignore_list = $where = null;

        if (empty($this->where)) {
            if (isset($this->qwhere)) {
                return ' (' . $this->qwhere['where'] .')';
            }
            return null;
        }
        $startMain = false;
        if ($dbReady) {
            foreach ($this->where as $group_name => $groups) {
                $hold = null;
                $subsql = array();
                if (!isset($groups['values'])) {
                    continue;
                }

                $startSub = false;

                foreach ($groups['values'] as $whereVal) {
                    if ($startSub == true) {
                        $subsql[] = $whereVal->conj;
                    }
                    $subsql[] = $whereVal->get();
                    $startSub = true;
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

            $start_main = false;

            if (!empty($where_list)) {
                $sql[] = $this->_buildGroup($where_list, $ignore_list, true);
            }
            
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
    function _buildGroup($where_list, &$ignore_list, $first=false) {
        if (!$ignore_list) {
            $ignore_list = array();
        } 

        foreach ($where_list as $group_name => $group_info) {
            if (isset($ignore_list[$group_name])) {
                continue;
            }
            $ignore_list[$group_name] = true;
            extract($group_info);

            if (!$first) {
                $sql[] = $group_conj;
            } else {
                $first=false;
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

    function setDistinct($distinct=true)
    {
        $this->_distinct = (bool)$distinct;
    }

    function addColumn($column, $max_min=null, $as=null, $count=false, $distinct=false)
    {
        if (!in_array(strtolower($max_min), array('max', 'min'))) {
            $max_min = null;
        }

        $table = $this->tables[0];
        if (strpos($column, '.')) {
            list($table, $column) = explode('.', $column);
            if (!isset($this->table_as[$table])) {
                $this->addTable($table);
            }
        }

        if (!empty($as)) {
            if (!PHPWS_DB::allowed($as)) {
                return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addColumn', $as);
            }
        }

        if (!PHPWS_DB::allowed($column)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addColumn', $column);
        }

        if ($distinct && !$count) {
            $this->addGroupBy($table . '.' . $column);
        }

        $col['table']    = $table;
        $col['name']     = $column;
        $col['max_min']  = $max_min;
        $col['count']    = (bool)$count;
        $col['distinct'] = (bool)$distinct;
        if ($column != '*') {
            $col['as']       = $as;
        }

        $this->columns[] = $col;
    }

    function getAllColumns()
    {
        $columns[] = $this->getColumn(true);
        return $columns;
    }

    function getColumn($format=false)
    {
        if ($format) {
            if (empty($this->columns)) {
                return $this->tables[0] . '.*';
            } else {
                foreach ($this->columns as $col) {
                    $as = null;
                    extract($col);
                    if ($count) {
                        if ($distinct) {
                            $table_name = sprintf('count(distinct(%s.%s))', $table, $name);
                        } else {
                            $table_name = sprintf('count(%s.%s)', $table, $name);
                        }
                    } else {
                        if ($distinct) {
                            $table_name = sprintf('distinct(%s.%s)', $table, $name);
                        } else {
                            $table_name = sprintf('%s.%s', $table, $name);
                        }
                    }
                    if ($max_min) {
                        $columns[] = strtoupper($max_min) . "($table_name)";
                    } else {
                        if (!empty($as)) {
                            $columns[] = "$table_name AS $as";
                        } else {
                            $columns[] = "$table_name";
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
                $this->order[] = $GLOBALS['PHPWS_DB']['lib']->randomOrder();
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

    function getOrder($dbReady=false)
    {
        if (empty($this->order)) {
            return null;
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

    function addValue($column, $value=null)
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
            return null;
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
            return null;
        }

        return $this->values;
    }


    function setLimit($limit, $offset=null)
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
            $_limit = & $limit;
            $_offset = & $offset;
        }

        $this->limit['total'] = preg_replace('/[^\d\s]/', '', $_limit);

        if (isset($_offset)) {
            $this->limit['offset'] = preg_replace('/[^\d\s]/', '', $_offset);
        }

        return true;
    }

    function getLimit($dbReady=false)
    {
        if (empty($this->limit)) {
            return null;
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
        $this->columns = null;
    }


    function affectedRows()
    {
        $query =  PHPWS_DB::lastQuery();
        $process = strtolower(substr($query, 0, strpos($query, ' ')));

        if ($process == 'select') {
            return false;
        }

        return $GLOBALS['PHPWS_DB']['connection']->affectedRows();
    }

    /**
     * Resets where, values, limits, order, columns, indexby, and qwhere
     * Does NOT reset locked tables but does remove any tables beyond the
     * initiating one
     */
    function reset()
    {
        $this->resetWhere();
        $this->resetValues();
        $this->resetLimit();
        $this->resetOrder();
        $this->resetColumns();
        $this->indexby = null;
        $this->qwhere  = null;
        $tmp_table = $this->tables[0];
        $this->tables = null;
        $this->tables = array($tmp_table);
    }

    function lastQuery()
    {
        return $GLOBALS['PHPWS_DB']['connection']->last_query;
    }

    function insert($auto_index=true)
    {
        PHPWS_DB::touchDB();
        $maxID = true;
        $table = $this->getTable(false);
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

        $table = $this->getTable(false);
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::insert');
        }

        $values = $this->getAllValues();
        $where = $this->getWhere(true);

        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }

        if (empty($values)) {
            return PHPWS_Error::get(PHPWS_DB_NO_VALUES, 'core', 'PHPWS_DB::update');
        }

        foreach ($values as $index=>$data) {
            $columns[] = $index . ' = ' . PHPWS_DB::dbReady($data);
        }

        $limit = $this->getLimit(true);
        $order = $this->getOrder(true);

        $query = "UPDATE $table SET " . implode(', ', $columns) ." $where $order $limit";
        $result = PHPWS_DB::query($query);

        if (DB::isError($result)) {
            return $result;
        } else {
            return true;
        }
    }

    function count()
    {
        return $this->select('count');
    }

    function getSelectSQL($type)
    {
        if ($type == 'count' && empty($this->columns)) {
            $columns = null;
        } else {
            $columns = implode(', ', $this->getAllColumns());
        }

        $table = $this->getTable();

        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::select');
        }

        $where   = $this->getWhere(true);
        $order   = $this->getOrder(true);
        $limit   = $this->getLimit(true);
        $group_by = $this->getGroupBy(true);

        $sql_array['columns'] = &$columns;
        $sql_array['table']   = &$table;
        $sql_array['where']   = &$where;
        $sql_array['group_by'] = &$group_by;
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
    function select($type=null, $sql=null)
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

            // extract will get $columns, $table, $where, $group_by
            // $order, and $limit
            extract($sql_array);

            if ($type == 'count') {
                if (empty($columns)) {
                    // order and group_by are not needed if count is
                    // using all rows
                    $order = null;
                    $group_by = null;
                    $columns = 'COUNT(*)';
                } else {
                    $add_group = $columns;
                    $columns .= ', COUNT(*)';   

                    if (empty($group_by)) {
                        $group_by = "GROUP BY $add_group";
                    } 
                }
            }

            if (!empty($where)) {
                $where = 'WHERE ' . $where;
            }

            if ($this->isDistinct()) {
                $distinct = 'DISTINCT';
            } else {
                $distinct = null;
            }


            $sql = "SELECT $distinct $columns FROM $table $where $group_by $order $limit";
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
            PHPWS_DB::logDB($sql);
            return $GLOBALS['PHPWS_DB']['connection']->getAssoc($sql, null,null, $mode);
            break;

        case 'col':
            if (empty($sql) && empty($this->columns)) {
                return PHPWS_Error::get(PHPWS_DB_NO_COLUMN_SET, 'core', 'PHPWS_DB::select');
            }

            if (isset($indexby)) {
                PHPWS_DB::logDB($sql);
                $result = $GLOBALS['PHPWS_DB']['connection']->getAll($sql, null, $mode);
                if (PEAR::isError($result)) {
                    return $result;
                }

                return PHPWS_DB::_indexBy($result, $indexby, true);
            }
            PHPWS_DB::logDB($sql);
            return $GLOBALS['PHPWS_DB']['connection']->getCol($sql);
            break;

        case 'min':
        case 'max':
        case 'one':
            PHPWS_DB::logDB($sql);
            return $GLOBALS['PHPWS_DB']['connection']->getOne($sql, null, $mode);
            break;

        case 'row':
            PHPWS_DB::logDB($sql);
            return $GLOBALS['PHPWS_DB']['connection']->getRow($sql, array(), $mode);
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
            $result = $GLOBALS['PHPWS_DB']['connection']->getAll($sql, null, $mode);
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

    function _indexBy($sql, $indexby, $colMode=false){
        $rows = array();

        if (!is_array($sql)) {
            return $sql;
        }
        $stacked = false;

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
            if (is_array($rows[$index]) && !isset($rows[$index][0])) {
                $hold = $rows[$index];
                $rows[$index] = array();
                $rows[$index][] = $hold;
                $stacked = true;
            }
            if (!$stacked) {
                $hold = $rows[$index];
                $rows[$index] = array();
                $rows[$index][] = $hold;
                $stacked = true;
            }
            if (!is_array($rows[$index])) {
                $i = $rows[$index];
                $rows[$index] = array();
                $rows[$index][] = $i;
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

        if ($amount == 0) {
            return true;
        }

        $table = $this->getTable(false);
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::incrementColumn');
        }

        $where = $this->getWhere(true);
        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }

        if ($amount < 0) {
            $math = $amount;
        } else {
            $math = "+ $amount";
        }

        $query = "UPDATE $table SET $column_name = $column_name $math $where";
        $result = PHPWS_DB::query($query);

        if (DB::isError($result)) {
            return $result;
        } else {
            return true;
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
        $table = $this->getTable(false);
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::delete');
        }

        $where = $this->getWhere(true);
        $limit = $this->getLimit(true);
        $order = $this->getOrder(true);

        if (!empty($where)) {
            $where = 'WHERE ' . $where;
        }
        $sql = "DELETE FROM $table $where $order $limit";
        return PHPWS_DB::query($sql);
    }
  
    /**
     * Static call only
     * check_existence - of table
     * sequence_table  - if true, drop sequence table as well
     */
    function dropTable($table, $check_existence=true, $sequence_table=true)
    {
        PHPWS_DB::touchDB();

        // was using IF EXISTS but not cross compatible
        if ($check_existence && !PHPWS_DB::isTable($table)) {
            return true;
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

        return true;
    }

    function isSequence($table)
    {
        $table = PHPWS_DB::addPrefix($table);
        return is_numeric($GLOBALS['PHPWS_DB']['connection']->nextId($table));
    }

    function truncateTable()
    {
        $table = $this->getTable(false);
        if(!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::truncateTable()');
        }

        $sql = "TRUNCATE TABLE $table";

        return PHPWS_DB::query($sql);
    }

    function dropTableIndex($name=null)
    {
        $table = $this->getTable(false);
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
    function createTableIndex($column, $name=null, $unique=false)
    {
        if(!DB_ALLOW_TABLE_INDEX) {
            return false;
        }

        $table = $this->getTable(false);
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

        if ($unique) {
            $unique_idx = 'UNIQUE ';
        } else {
            $unique_idx = ' ';
        }
 
        $sql = sprintf('CREATE %sINDEX %s ON %s (%s)', $unique_idx, $name, $table, $column);

        return $this->query($sql);
    }

    function createPrimaryKey($column='id')
    {
        $table = $this->getTable(false);
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::createTableIndex');
        }
        $sql = sprintf('alter table %s add primary key(%s)', $table, $column);
        return $this->query($sql);
    }

    function createTable()
    {
        $table = $this->getTable(false);
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
     * Factory files must handle the prefixing.
     */
    function renameTableColumn($old_name, $new_name)
    {
        $table = $this->getTable(false);
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::renameTableColumn');
        }

        $specs = $this->getColumnInfo($old_name, true);
        if (empty($specs)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::renameTableColumn', $old_name);
        }

        $sql = $GLOBALS['PHPWS_DB']['lib']->renameColumn($table, $old_name, $new_name, $specs);

        return $this->query($sql, false);
    }

    /**
     * Adds a column to the database table
     *
     * Returns error object if fails. Returns false if table column already
     * exists. Returns true is successful.
     *
     * @param string  column    Name of column to add
     * @param string  parameter Specifics of table column
     * @param string  after     If supported, add column after this column
     * @param boolean indexed   Create an index on the column if true
     * @returns mixed
     */
    function addTableColumn($column, $parameter, $after=null, $indexed=false)
    {
        $table = $this->getTable(false);
        if (!$table) {
            return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, 'core', 'PHPWS_DB::addTableColumn');
        }

        if (!PHPWS_DB::allowed($column)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_COL_NAME, 'core', 'PHPWS_DB::addTableColumn', $column);
        }

        if ($this->isTableColumn($column)) {
            return false;
        }

        $sql = $GLOBALS['PHPWS_DB']['lib']->addColumn($table, $column, $parameter, $after);

        foreach ($sql as $val) {
            $result = PHPWS_DB::query($val);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if ($indexed == true && DB_ALLOW_TABLE_INDEX) {
            $indexSql = "CREATE INDEX $column on $table($column)";
            $result = PHPWS_DB::query($indexSql);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }


    function dropTableColumn($column)
    {
        $table = $this->getTable(false);
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
        if (empty($GLOBALS['PHPWS_DB']['dbs'])) {
            return;
        }

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

    function importFile($filename, $report_errors=true)
    {
        if (!is_file($filename)) {
            return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'core', 'PHPWS_DB::importFile');
        }
        $data = file_get_contents($filename);
        return PHPWS_DB::import($data, $report_errors);
    }

    /**
     * Imports a SQL dump into the database.
     * This function can not be called statically.
     * @returns True if successful, false if not successful and report_errors = false or
     *               Error object if report_errors = true
     */
    function import($text, $report_errors=true)
    {
        PHPWS_DB::touchDB();

        // first_import makes sure at least one query was completed
        // successfully
        $first_import = false;

        $sqlArray = PHPWS_Text::sentence($text);
        $error = false;

        foreach ($sqlArray as $sqlRow){
            if (empty($sqlRow) || preg_match("/^[^\w\d\s\\(\)]/i", $sqlRow)) {
                continue;
            }

            $sqlCommand[] = $sqlRow;

            if (preg_match("/;$/", $sqlRow)) {
                $query = implode(' ', $sqlCommand);
                $sqlCommand = array();

                if(!DB_ALLOW_TABLE_INDEX &&
                   preg_match('/^create index/i', $query)) {
                    continue;
                }

                PHPWS_DB::homogenize($query);

                $result = PHPWS_DB::query($query);

                if (DB::isError($result)) {
                    if ($report_errors) {
                        return $result;
                    } else {
                        PHPWS_Error::log($result);
                        $error = true;
                    }
                }
                $first_import = true;
            }
        }

        if (!$first_import) {
            if ($report_errors) {
                return PHPWS_Error::get(PHPWS_DB_IMPORT_FAILED, 'core', 'PHPWS_DB::import');
            } else {
                PHPWS_Error::log(PHPWS_DB_IMPORT_FAILED, 'core', 'PHPWS_DB::import');
                $error = true;                    
            }
        }

        if ($error) {
            return false;
        } else {
            return true;
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

            $command = preg_replace('/ default (\'\'|""|``)/i', '', $command);

            if (preg_match ('/\s(smallint|int)\s/i', $command)) {
                if(!preg_match('/\snull/i', $command)) {
                    $command = str_ireplace(' int ', ' INT NOT NULL ', $command);
                    $command = str_ireplace(' smallint ', ' SMALLINT NOT NULL ', $command);
                }
                
                if(!preg_match('/\sdefault/i', $command)) {
                    $command = str_ireplace(' int ', ' INT DEFAULT 0 ', $command);
                    $command = str_ireplace(' smallint ', ' SMALLINT DEFAULT 0 ', $command);
                }
                
                $command = preg_replace('/ default \'(\d+)\'/Ui', ' DEFAULT \\1', $command);
            }



            $command = preg_replace($from, $to, $command);
            $newlist[] = $command;
        }

        $query = implode(',', $newlist);
        $GLOBALS['PHPWS_DB']['lib']->readyImport($query);
    }


    function parsePearCol($info, $strip_name=false)
    {
        $setting = $GLOBALS['PHPWS_DB']['lib']->export($info);
        if (isset($info['flags'])) {
            if (stristr($info['flags'], 'multiple_key')) {
                if (DB_ALLOW_TABLE_INDEX) {
                    $column_info['index'] = 'CREATE INDEX ' .  $info['name'] . ' on ' . $info['table'] 
                        . '(' . $info['name'] . ')';
                }
                $info['flags'] = str_replace(' multiple_key', '', $info['flags']);
            }
            $preFlag = array('/not_null/i', '/primary_key/i', '/default_(\w+)?/i', '/blob/i', '/%3a%3asmallint/i');
            $postFlag = array('NOT NULL', 'PRIMARY KEY', "DEFAULT '\\1'", '', '');
            $multipleFlag = array('multiple_key', '');
            $flags = ' ' . preg_replace($preFlag, $postFlag, $info['flags']);

        }
        else {
            $flags = null;
        }

        if ($strip_name == true) {
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

    function export($structure=true, $contents=true)
    {
        PHPWS_DB::touchDB();
        $table = $this->addPrefix($this->tables[0]);

        if ($structure == true) {
            $columns =  $GLOBALS['PHPWS_DB']['connection']->tableInfo($table);
            $column_info = $this->parseColumns($columns);
            $index = $this->getIndex();

            $sql[] = "CREATE TABLE $table ( " .  implode(', ', $column_info['parameters']) . ' );';
            if (isset($column_info['index'])) {
                $sql = array_merge($sql, $column_info['index']);
            }
        }

        if ($contents == true) {
            if ($rows = $this->select()) {
                if (PEAR::isError($rows)) {
                    return $rows;
                }
                foreach ($rows as $dataRow){
                    foreach ($dataRow as $key=>$value){
                        $allKeys[] = $key;
                        $allValues[] = PHPWS_DB::quote($value);
                    }
          
                    $sql[] = "INSERT INTO $table (" . implode(', ', $allKeys) . ') VALUES (' . implode(', ', $allValues) . ');';
                    $allKeys = $allValues = array();
                }
            }
        }

        if (!empty($sql)) {
            return implode("\n", $sql);
        } else {
            return null;
        }
    }

    function quote($text)
    {
        return $GLOBALS['PHPWS_DB']['connection']->quote($text);
    }

    function extractTableName($sql_value)
    {
        $temp = explode(' ', trim($sql_value));
        
        if (!is_array($temp)) {
            return null;
        }
        foreach ($temp as $whatever){
            if (empty($whatever)) {
                continue;
            }
            $format[] = $whatever;
        }

        if (empty($format)) {
            return null;
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
    function dbReady($value=null) 
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
     * Adds module title and class name to the load_class variable.
     * This list is called on the successful query of a loadObject or
     * getObjects. The list of files is erased as the files would not
     * need to be required again.
     */
    function loadClass($module, $file)
    {
        $this->load_class[] = array($module, $file);
    }

    /**
     * Requires the classes, if any, in the load_class variable
     */
    function requireClasses()
    {
        if ($this->load_class && is_array($this->load_class)) {
            foreach ($this->load_class as $files) {
                if (!is_array($files)) {
                    continue;
                }
                PHPWS_Core::initModClass($files[0], $files[1]);
            }
            $this->load_class = null;
        }
    }

    /**
     * @author Matt McNaney <matt at tux dot appstate dot edu>
     * @param  object $object        Object variable filled with result.
     * @param  object $require_where If true, require a where parameter or
     *                               have the id set
     * @return mixed                 Returns true if object properly populated and false otherwise
     *                               Returns error object if something goes wrong
     * @access public
     */
    function loadObject(&$object, $require_where=true)
    {
        if (!is_object($object)) {
            return PHPWS_Error::get(PHPWS_DB_NOT_OBJECT, 'core', 'PHPWS_DB::loadObject');
        }

        if ($require_where && empty($object->id) && empty($this->where)) {
            return PHPWS_Error::get(PHPWS_DB_NO_ID, 'core', 'PHPWS_DB::loadObject', get_class($object));
        }

        if ($require_where && empty($this->where)) {
            $this->addWhere('id', $object->id);
        }

        $variables = $this->select('row');

        if (PEAR::isError($variables)) {
            return $variables;
        } elseif (empty($variables)) {
            return false;
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
     *
     * --- Any extra parameters after class_name are piped into ---
     * ---          the object constructor.                     ---
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string $class_name Name of class used in object
     * @return array $items      Array of objects
     * @access public
     */
    function getObjects($class_name)
    {
        $items = null;
        $result = $this->select();

        if (PEAR::isError($result) || !isset($result)) {
            return $result;
        }

        $this->requireClasses();

        if (!class_exists($class_name)) {
            return PHPWS_Error::get(PHPWS_CLASS_NOT_EXIST, 'core', 'PHPWS_DB::getObjects', $class_name);
        }

        $num_args = func_num_args();
        $args = func_get_args();
        array_shift($args);
        foreach ($result as $indexby => $itemResult) {
            $genClass = new $class_name;

            if ($num_args > 1) {
                // reference is necessary for genClass in php 4
                call_user_func_array(array(&$genClass, $class_name), $args);
            }

            PHPWS_Core::plugObject($genClass, $itemResult);
            $items[$indexby] = $genClass;
        }

        return $items;
    }

    function saveObject(&$object, $stripChar=false, $autodetect_id=true)
    {
        if (!is_object($object)) {
            return PHPWS_Error::get(PHPWS_WRONG_TYPE, 'core', 'PHPWS_DB::saveObject', _('Type') . ': ' . gettype($object));
        }

        $object_vars = get_object_vars($object);

        if (!is_array($object_vars)) {
            return PHPWS_Error::get(PHPWS_DB_NO_OBJ_VARS, 'core', 'PHPWS_DB::saveObject');
        }

        foreach ($object_vars as $column => $value){
            if ($stripChar == true) {
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

        if (isset($this->qwhere) || !empty($this->where)) {
            $result = $this->update();
        } else {
            $result = $this->insert($autodetect_id);

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
            return false;
        }

        $reserved = array('ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'AUTO_INCREMENT', 'BDB',
                          'BERKELEYDB', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BTREE', 'BY', 'CASCADE',
                          'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'COLLATE', 'COLUMN', 'COLUMNS', 'CONSTRAINT', 'CREATE',
                          'CROSS', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'DATABASE', 'DATABASES', 'DATE',
                          'DAY_HOUR', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DEFAULT',
                          'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DISTINCT', 'DISTINCTROW',
                          'DOUBLE', 'DROP', 'ELSE', 'ENCLOSED', 'ERRORS', 'ESCAPED', 'EXISTS', 'EXPLAIN', 'false', 'FIELDS',
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
                          'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'true', 'TYPES', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED',
                          'UPDATE', 'USAGE', 'USE', 'USER_RESOURCES', 'USING', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARYING',
                          'WARNINGS', 'WHEN', 'WHERE', 'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL');

        if(in_array(strtoupper($value), $reserved)) {
            return false;
        }

        if(preg_match('/[^\w\*\.]/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Crutch function from old database
     */
    function sqlFriendlyName($name) {
        if (!PHPWS_DB::allowed($name)) {
            return false;
        }

        return preg_replace('/\W/', '', $name);
    }

    function updateSequenceTable()
    {      
        $this->addColumn('id', 'max');
        
        $max_id = $this->select('one');
        
        if (PEAR::isError($max_id)) {
            return $max_id;
        }

        if ($max_id > 0) {
            $seq_table = $this->getTable(false) . '_seq';
            if (!$this->isTable($seq_table)) {
                $table = $this->addPrefix($this->getTable(false));
                $GLOBALS['PHPWS_DB']['connection']->nextId($table);
            }

            $seq = new PHPWS_DB($seq_table);
	    $result = $seq->select('one');
            if (PHPWS_Error::logIfError($result)) {
                return false;
            }
            
            $seq->addValue('id', $max_id);
            if (!$result) {
                return $seq->insert(false);
            } else {
                return $seq->update();
            }
        }

        return true;
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


    /**
     * @author Matthew McNaney
     * @author Hilmar
     */
    function prefixQuery($sql)
    {
        if (!$GLOBALS['PHPWS_DB']['tbl_prefix']) {
            return $sql;
        }
        $tables = PHPWS_DB::pullTables($sql);

        if (empty($tables)) {
            return $sql;
        }

        foreach ($tables as $tbl) {
            $tbl = trim($tbl);
            $sql = PHPWS_DB::prefixVary($sql, $tbl);
        }
        return $sql;
    }

    /**
     * Prefix tablenames, but not within 'quoted values', called from prefixQuery
     * @author Hilmar
     */
    function prefixVary($sql, $tbl)
    {
        $repl = true;
        $ar = explode("'", $sql);

        foreach ($ar as $v) {
            if ($repl) {
                $subsql[] = preg_replace("/([\s\W])$tbl(\W)|([\s\W])$tbl$/",
                                         '$1${3}' . $GLOBALS['PHPWS_DB']['tbl_prefix'] . $tbl . '$2', $v);
                $repl = false;
            } else {
                $subsql[] = $v;
                if (substr($v, -1, 1) == "\\") continue;
                $repl = true;
            }
        }
        $sql = implode('\'', $subsql);

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
            if (preg_match('/^create (unique )?index/i', $sql)) {
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
            $table =  preg_replace('/insert |into | values|\(.*\)/i', '', $sql);
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
            $table = preg_replace('/\w+\.\w+/', ' ', $table);
            $table = preg_replace('/(as \w+)/i', '', $table);
            $table = preg_replace('/ {2,}/', ' ', trim($table));
            $tables = explode(' ', $table);
            return $tables;
            break;

        case 'update':
            $aTable = explode(' ', $sql);
            $tables[] = preg_replace('/\W/', '', $aTable[1]);
            break;

        case 'lock':
            $sql = preg_replace('/lock tables/i', '', $sql);
            $aTable = explode(',', $sql);

            foreach ($aTable as $tbl) {
                $tables[] = substr($tbl, 0, strpos(trim($tbl) + 1, ' '));
            }
            break;
        }

        return $tables;
    }

    function setLock($table, $status='write')
    {
        if (!is_string($table) || !is_string($status)) {
            return false;
        }

        $status = strtolower($status);

        if ($status != 'read' && $status != 'write') {
            return false;
        }

        if (in_array($table, $this->tables)) {
            $this->locked[] = array('table' => $table,
                                    'status' => $status);
        }
    }

    function lockTables()
    {
        if (!ALLOW_TABLE_LOCKS) {
            return true;
        }

        if (empty($this->locked)) {
            return false;
        }

        $query = $GLOBALS['PHPWS_DB']['lib']->lockTables($this->locked);
        return $this->query($query);
    }

    function unlockTables()
    {
        if (!ALLOW_TABLE_LOCKS) {
            return true;
        }

        $query = $GLOBALS['PHPWS_DB']['lib']->unlockTables();
        return $this->query($query);
    }


    function begin()
    {
        // If transaction started already, return false.
        if (isset($GLOBALS['DB_Transaction']) && $GLOBALS['DB_Transaction']) {
            return false;
        }
        $GLOBALS['DB_Transaction'] = true;
        return PHPWS_DB::query('BEGIN');
    }

    function commit()
    {
        // if transaction not started, return false.
        if (!$GLOBALS['DB_Transaction']) {
            return false;
        }
        $GLOBALS['DB_Transaction'] = false;
        return PHPWS_DB::query('COMMIT');
    }

    function rollback()
    {
        // if transaction not started, return false.
        if (!$GLOBALS['DB_Transaction']) {
            return false;
        }
        $GLOBALS['DB_Transaction'] = false;
        return PHPWS_DB::query('ROLLBACK');
    }

}

class PHPWS_DB_Where {
    var $table      = null;
    var $column     = null;
    var $value      = null;
    var $operator   = '=';
    var $conj       = 'AND';
    var $join       = false;

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
            return false;
        }

        if (!PHPWS_DB::checkOperator($operator)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_OP, 'core', 'PHPWS_DB::addWhere', _('DB Operator:') . $operator);
        }

        if ($operator == 'LIKE' || $operator == 'ILIKE') {
            $operator = $GLOBALS['PHPWS_DB']['lib']->getLike();
        } elseif ($operator == 'NOT LIKE' || $operator == 'NOT ILIKE') {
            $operator = 'NOT ' . $GLOBALS['PHPWS_DB']['lib']->getLike();
        } elseif ($operator == '~' || $operator == 'REGEXP' || $operator == 'RLIKE') {
            $operator = $GLOBALS['PHPWS_DB']['lib']->getRegexp();
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
            return false;
        }

        $this->conj = $conj;
    }

    function getValue()
    {
        $value = $this->value;

        if (is_array($value)) {
            switch ($this->operator){
            case 'IN':
            case 'NOT IN':
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
            case 'NOT BETWEEN':
                $value = sprintf("'{%s}' AND '{%s}'", $this->value[0], $this->value[1]);
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

?>
