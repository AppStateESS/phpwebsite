<?php

/**
 * Mysql specific library
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class mysql_PHPWS_SQL {
    public $portability = null;

    public function export(&$info)
    {
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
            if (!is_numeric($info['len']) || $info['len'] > 255) {
                $length = 255;
            } else {
                $length = $info['len'];
            }
            $setting = "CHAR($length)";
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


    public function renameColumn($table, $column_name, $new_name, $specs)
    {
        $table = PHPWS_DB::addPrefix($table);
        $sql = sprintf('ALTER TABLE %s CHANGE %s %s %s',
                       $table, $column_name, $new_name, $specs['parameters']);
        return $sql;
    }

    public function getLimit($limit)
    {
        if (!isset($limit['total'])) {
            return null;
        }

        if (isset($limit['offset'])) {
            return sprintf('LIMIT %s, %s', $limit['offset'], $limit['total']);
        } else {
            return 'LIMIT ' . $limit['total'];
        }
    }

    public function readyImport(&$query)
    {
        return;
    }

    public function randomOrder()
    {
        return 'rand()';
    }

    public function dropSequence($table)
    {
        $table = PHPWS_DB::addPrefix($table);
        $result = $GLOBALS['PHPWS_DB']['connection']->query("DROP TABLE $table");
        if (PEAR::isError($result)) {
            return $result;
        }

        return TRUE;
    }


    public function dropTableIndex($name, $table)
    {
        $table = PHPWS_DB::addPrefix($table);
        return sprintf('DROP INDEX %s ON %s', $name, $table);
    }

    public function getLike()
    {
        return 'LIKE';
    }

    public function getRegexp()
    {
        return 'REGEXP';
    }

    public function getNotRegexp()
    {
        return 'NOT REGEXP';
    }

    public function addColumn($table, $column, $parameter, $after=null)
    {
        if (!empty($after)) {
            if (strtolower($after) == 'first') {
                $location = 'FIRST';
            } else {
                $location = "AFTER $after";
            }
        } else {
            $location = NULL;
        }

        return array("ALTER TABLE $table ADD $column $parameter $location;");
    }

    public function lockTables($locked)
    {
        foreach ($locked as $lck) {
            $tbls[] = sprintf('%s %s', $lck['table'], strtoupper($lck['status']));
        }

        return sprintf('LOCK TABLES %s', implode(', ', $tbls));
    }

    public function unlockTables()
    {
        return 'UNLOCK TABLES;';
    }


    public function alterTableColumn($table, $column, $parameter)
    {
        $sql = "ALTER TABLE $table CHANGE $column $column $parameter";
        return array($sql);
    }
}

?>
