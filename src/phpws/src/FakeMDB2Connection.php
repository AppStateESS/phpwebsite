<?php

/*
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace phpws;

/**
 * Description of FakeMDB2Connection
 *
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 */
class FakeMDB2Connection
{

    private $connection;
    public $dbsyntax;
    public $last_query;

    public function __construct($dsn)
    {
        $config = new \Doctrine\DBAL\Configuration;
        $params = $this->parseDSN($dsn);
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($params, $config);
    }

    private function parseDSN($dsn)
    {
        $first_colon = strpos($dsn, ':');
        $second_colon = strpos($dsn, ':', $first_colon + 1);
        $third_colon = strpos($dsn, ':', $second_colon + 1);
        $at_sign = strpos($dsn, '@');
        $first_slash = strpos($dsn, '/');
        $second_slash = strpos($dsn, '/', $first_slash + 1);
        $third_slash = strpos($dsn, '/', $second_slash + 1);

        $dbtype = substr($dsn, 0, $first_colon);
        $dbuser = substr($dsn, $second_slash + 1, $second_colon - $second_slash - 1);
        $dbpass = substr($dsn, $second_colon + 1, $at_sign - $second_colon - 1);
        if ($third_colon) {
            $dbhost = substr($dsn, $at_sign + 1, $third_colon - $at_sign - 1);
        } else {
            $dbhost = substr($dsn, $at_sign + 1, $third_slash - $at_sign - 1);
        }

        if (empty($dbhost)) {
            $dbhost = 'localhost';
        }

        $dbname = substr($dsn, $third_slash + 1);

        if ($third_colon) {
            $dbport = substr($dsn, $third_colon + 1, $third_slash - $third_colon - 1);
        } else {
            $dbport = null;
        }

        $this->dbsyntax = $dbtype;

        if ($dbtype == 'mysqli' || $dbtype == 'mysql') {
            $dbtype = 'pdo_mysql';
        }

        return array('driver' => $dbtype, 'user' => $dbuser, 'password' => $dbpass, 'host' => $dbhost,
            'port' => $dbport, 'dbname' => $dbname);
    }

    public function setOption($option_name, $column_name)
    {

    }

    public function escape($value)
    {
        $value = $this->connection->quote($value);
        $value = preg_replace("/^'/", '', $value);
        $value = preg_replace("/'$/", '', $value);
        return $value;
    }

    public function queryOne($sql)
    {
        return $this->connection->fetchColumn($sql);
    }

    public function queryAll($sql)
    {
        return $this->connection->fetchAll($sql);
    }

    public function queryCol($sql)
    {
        $result = null;
        while ($row = $this->connection->fetchColumn($sql)) {
            $result[] = $row;
        }
        return $result;
    }

    public function queryRow($sql)
    {
        return $this->connection->fetchAssoc($sql);
    }

    public function disconnect()
    {

    }

    public function exec($sql)
    {
        return $this->connection->executeQuery($sql);
    }

    public function query($sql)
    {
        return $this->connection->executeQuery($sql);
    }

    public function listTableFields($table)
    {
        $tableColumns = null;
        $sm = $this->connection->getSchemaManager();
        $columns = $sm->listTableColumns($table);
        return array_keys($columns);
    }

    public function loadModule($var1, $var2 = null, $var3 = null)
    {

    }

    public function listTables()
    {
        $allTables = null;
        $sm = $this->connection->getSchemaManager();
        $tables = $sm->listTables();
        foreach ($tables as $tbl) {
            $allTables[] = $tbl->getName();
        }
        return $allTables;
    }

    public function tableInfo($tableName)
    {
        $sm = $this->connection->getSchemaManager();
        $table = $sm->listTableDetails($tableName);

        $indexes = $table->getIndexes();
        if (empty($indexes)) {
            $indexColumns = array();
        } else {
            foreach ($indexes as $idx_name => $idx) {
                $cols = $idx->getColumns();
                foreach ($cols as $c) {
                    $indexColumns[] = $c;
                }
            }
        }


        $primaryKey = $table->getPrimaryKey();
        if (isset($primaryKey)) {
            $pkColumns = $primaryKey->getColumns();
        } else {
            $pkColumns = array();
        }

        $columns = $table->getColumns();
        foreach ($columns as $key => $col) {
            $row = $col->toArray();
            $row['nativetype'] = $row['type'] = $this->getDBType($col);
            $row['mdb2type'] = $this->getMDB2Type($col);

            $row['table'] = $tableName;
            $row['flags'] = '';
            if (in_array($key, $pkColumns)) {
                $row['flags'] .= 'primary_key';
            }

            if (in_array($key, $indexColumns)) {
                $row['flags'] .= ' multiple_key';
            }

            if ($col->getNotnull()) {
                $row['flags'] .= ' not_null';
            }
            if ($col->getUnsigned()) {
                $row['flags'] .= ' unsigned';
            }

            if ($row['notnull'] && $row['nativetype'] == 'varchar') {
                $row['default'] = '';
            }

            $mdbColumns[$key] = $row;
        }
        return $mdbColumns;
    }

    private function getDBType($col)
    {
        $type = $col->getType();

        switch (get_class($type)) {
            case 'Doctrine\DBAL\Types\IntegerType':
                return 'int';
                break;

            case 'Doctrine\DBAL\Types\SmallIntType':
                return 'smallint';
                break;

            case 'Doctrine\DBAL\Types\StringType':
                return 'varchar';
                break;

            case 'Doctrine\DBAL\Types\TextType':
                return 'text';
                break;
        }
    }

    private function getMDB2Type($col)
    {
        $type = $col->getType();
        switch (get_class($type)) {
            case 'Doctrine\DBAL\Types\IntegerType':
            case 'Doctrine\DBAL\Types\SmallIntType':
                return 'integer';
                break;

            case 'Doctrine\DBAL\Types\StringType':
                return 'text';
                break;

            case 'Doctrine\DBAL\Types\TextType':
                if ($col->getLength() > 256) {
                    return 'clob';
                } else {
                    return 'text';
                }
                break;
        }
    }

    public function nextID($table_name)
    {
        $sequence_table = $table_name . '_seq';
        $this->connection->executeQuery("insert into $sequence_table (id) values (null)");
        $value = $this->connection->lastInsertId();
        $this->connection->executeQuery("delete from $sequence_table where id < $value");
        return $value;
    }

}
