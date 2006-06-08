<?php

/**
 * Postgres specific library
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

define ('DB_USE_AFTER', FALSE);

class PHPWS_SQL {

    function export(&$info){
        switch ($info['type']){

        case 'int8':
        case 'int4':
        case 'int':
            $setting = 'INT';
            $info['flags'] = preg_replace('/unique primary/', 'PRIMARY KEY', $info['flags']);
            break;

        case 'int2':
            $setting = 'SMALLINT';
            break;

        case 'text':
        case 'blob':
            $setting = 'TEXT';
            if (stristr($info['flags'], 'not_null')) {
                $info['flags'] = 'NOT NULL';
            }
            break;

        case 'varchar':
            $setting = 'VARCHAR(255)';
            if (stristr($info['flags'], 'not_null')) {
                $info['flags'] = 'NOT NULL';
            }
            break;

        case 'bpchar':
            $setting = 'CHAR(255)';

            if (empty($info['flags'])) {
                $info['flags'] = 'NULL';
            }
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

        default:
            exit(_('Unknown column type:') . ' ' . $info['type']);
            break;
        }
        return $setting;
    }

    function renameColumn($table, $column_name, $new_name, $specs)
    {
        $sql = sprintf('ALTER TABLE %s RENAME COLUMN %s TO %s',
                       $table, $column_name, $new_name);
        return $sql;
    }


    function getLimit($limit){
        $sql[] = 'LIMIT';

        if (isset($limit['offset'])) {
            $sql[] = $limit['offset'];
            $sql[] = 'OFFSET';
        }

        $sql[] = $limit['total'];
    
        return implode(' ', $sql);
    }

    function readyImport(&$query){

        $from = array('/datetime/i',
                      '/double\((\d+),(\d+)\)/Uie'
                      );
        $to   = array('timestamp without time zone',
                      "'numeric(' . (\\1 + \\2) . ', \\2)'"
                      );
        $query = preg_replace($from, $to, $query);

        if (preg_match('/id int [\w\s]* primary key[\w\s]*,/iU', $query)){
            $tableName = PHPWS_DB::extractTableName($query);

            $query = preg_replace('/primary key/i', '', $query);
            $query = preg_replace('/if exists /i', '', $query);
            $query = preg_replace('/\);/', ', PRIMARY KEY (id));', $query);
        }
    }

    function randomOrder()
    {
        return 'random()';
    }

    function dropSequence($table)
    {
        $result = $GLOBALS['PEAR_DB']->query("DROP SEQUENCE $table");
        if (PEAR::isError($result)) {
            return $result;
        }

        return TRUE;
    }


    function dropTableIndex($name, $table=NULL)
    {
     	return sprintf('DROP INDEX %s', $name);
    }

}

?>
