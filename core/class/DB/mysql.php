<?php

/**
 * Mysql specific library
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class PHPWS_SQL {

    function export(&$info){
        switch ($info['type']){
        case 'int':
            if (!isset($info['len']) || $info['len'] > 6)
                $setting = 'INT';
            else
                $setting = 'SMALLINT';
            break;
    
        case 'blob':
            $setting = 'TEXT';
            $info['flags'] = NULL;
            break;
    
        case 'string':
            $setting = 'CHAR(' . $info['len'] . ')';
            break;
    
        case 'date':
            $setting = 'DATE';
            break;
    
        case 'real':
            $setting = 'FLOAT';
            break;
    
        case 'timestamp':
            $setting = 'TIMESTAMP';
            $info['flags'] = NULL;
            break;

        }

        return $setting;
    }


    function renameColumn($table, $column_name, $new_name, $specs)
    {
        $sql = sprintf('ALTER TABLE %s CHANGE %s %s %s',
                       $table, $column_name, $new_name, $specs['parameters']);
        return $sql;
    }

    function getLimit($limit){
        $sql[] = 'LIMIT ' . $limit['total'];
    
        if (isset($limit['offset'])) {
            $sql[] = ', ' . $limit['offset'];
        }

        return implode(' ', $sql);
    }

    function readyImport(&$query){
        return;
    }

    function randomOrder()
    {
        return 'rand()';
    }

    function dropTable($table, $check_existence = TRUE, $sequence_table = TRUE)
    {
        $table = PHPWS_DB::getPrefix() . $table;

        // was using IF EXISTS but not cross compatible
        if ($check_existence && !PHPWS_DB::isTable($table)) {
            return TRUE;
        }

        $result = PHPWS_DB::query("DROP TABLE $table");

        if (PEAR::isError($result)) {
            return $result;
        }

        if ($sequence_table && PHPWS_DB::isTable($table . '_seq')) {
            $result = PHPWS_DB::query("DROP TABLE $table" . "_seq");
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return TRUE;
    }

    function listTables()
    {
        return $GLOBALS['PEAR_DB']->getlistOf('tables');
    }
}

?>
