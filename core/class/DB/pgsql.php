<?php

/**
 * Postgres specific library
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class pgsql_PHPWS_SQL {
    public $portability = null;

    public function __construct()
    {
        $this->portability = DB_PORTABILITY_RTRIM;
    }

    public function export(&$info){
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

    public function renameColumn($table, $column_name, $new_name, $specs)
    {
        $table = PHPWS_DB::addPrefix($table);
        $sql = sprintf('ALTER TABLE %s RENAME COLUMN %s TO %s',
        $table, $column_name, $new_name);
        return $sql;
    }


    public function getLimit($limit)
    {
        $sql[] = 'LIMIT';
        $sql[] = $limit['total'];
        if (isset($limit['offset'])) {
            $sql[] = 'OFFSET';
            $sql[] = $limit['offset'];
        }
        return implode(' ', $sql);
    }

    public function readyImport(&$query){

        $from = array('/datetime/i', '/double\((\d+),(\d+)\)/Uie');
        $to   = array('timestamp without time zone', "'numeric(' . (\\1 + \\2) . ', \\2)'");
        $query = @preg_replace($from, $to, $query);

        if (preg_match('/id int [\w\s]* primary key[\w\s]*,/iU', $query)){
            $tableName = PHPWS_DB::extractTableName($query);

            $query = preg_replace('/primary key/i', '', $query);
            $query = preg_replace('/if exists /i', '', $query);
            $query = preg_replace('/\);/', ', PRIMARY KEY (id));', $query);
        }
    }

    public function randomOrder()
    {
        return 'random()';
    }

    public function dropSequence($table)
    {
        $table = PHPWS_DB::addPrefix($table);
        $result = $GLOBALS['PHPWS_DB']['connection']->query("DROP SEQUENCE $table");
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        return TRUE;
    }


    public function dropTableIndex($name, $table=NULL)
    {
        return sprintf('DROP INDEX %s', $name);
    }

    public function getLike()
    {
        return 'ILIKE';
    }

    public function getRegexp()
    {
        return '~*';
    }

    public function getNotRegexp()
    {
        return '!~*';
    }


    /**
     * Postgres doesn't accept "after" or "before"
     */
    public function addColumn($table, $column, $parameter, $after=null)
    {
        $parameter = strtolower($parameter);
        $parameter = preg_replace('/ {2,}/', ' ', trim($parameter));

        $pararray = explode(' ', $parameter);

        switch ($parameter[0]) {
            case 'smallint':
            case 'int':
            case 'integer':
            case 'bigint':
            case 'decimal':
            case 'numeric':
            case 'real':
            case 'double':
            case 'serial':
            case 'bigserial':
                $number = true;

            default:
                $number = false;
        }

        $length = count($pararray);

        for ($i=0; $i < $length; $i++) {
            if ($pararray[$i] == 'default' && isset($pararray[$i+1])) {
                if ($number) {
                    $default_value = preg_replace('/\'"`/', '', $pararray[$i+1]);
                } else {
                    $default_value = preg_replace('/"`/', '\'', $pararray[$i+1]);
                }
                $extra[1] = "ALTER TABLE $table ALTER $column SET DEFAULT $default_value;";
                $extra[2] = "UPDATE $table set $column = $default_value;";
                $unset_it[] = $i;
                $i++;
                $unset_it[] = $i;
            }

            if ($pararray[$i] == 'not' && $pararray[$i+1] == 'null') {
                $extra[3] = "ALTER TABLE $table ALTER $column SET NOT NULL;";
                $unset_it[] = $i;
                $i++;
                $unset_it[] = $i;
                continue;
            }

            if ($pararray[$i] == 'null') {
                $extra[3] = "ALTER TABLE $table ALTER $column DROP NOT NULL;";
                $unset_it[] = $i;
                continue;
            }

            if ($pararray[$i] == 'after') {
                $unset_it[] = $i;
                $i++;
                $unset_it[] = $i;
                continue;
            }


        }

        if (isset($unset_it)) {
            foreach ($unset_it as $key) {
                unset($pararray[$key]);
            }
        }

        if (!empty($pararray)) {
            $new_para = implode(' ', $pararray);
        } else {
            $new_para = null;
        }

        $extra[0] = "ALTER TABLE $table ADD $column $new_para;\n";
        ksort($extra);

        return $extra;
    }

    public function alterTableColumn($table, $column, $parameter)
    {
        $backup = '_bak_' . $column;

        $sql[] = "ALTER TABLE $table RENAME $column to $backup";
        $sql[] = "ALTER TABLE $table ADD COLUMN $column $parameter";
        $sql[] = "UPDATE $table SET $column=$backup";
        $sql[] = "ALTER TABLE $table DROP COLUMN $backup";
        return $sql;
    }

    public function lockTables($locked)
    {
        foreach ($locked as $lck) {
            if ($lck['status'] == 'read') {
                $tbls[] = sprintf('%s ROW EXCLUSIVE MODE', $lck['table']);
            } elseif ($lck['status'] == 'write') {
                $tbls[] = sprintf('%s EXCLUSIVE MODE', $lck['table']);
            }
        }

        return sprintf("BEGIN WORK;\nLOCK TABLES %s", implode(', ', $tbls));
    }

    public function unlockTables()
    {
        return 'COMMIT WORK;';
    }
}

?>
