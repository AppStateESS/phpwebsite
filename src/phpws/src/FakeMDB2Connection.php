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
    public $phptype;

    public function __construct($dsn)
    {
        $config = new \Doctrine\DBAL\Configuration;
        $params = $this->parseDSN($dsn);
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($params,
                        $config);
        $this->connection->connect();
    }

    public function parseDSN($dsn)
    {
        $dbtype = $dbname = $dbuser = $dbpass = $dbhost = $dsport = null;
        $section = explode('/', $dsn);
        $count = 0;
        foreach ($section as $sec) {
            switch ($count) {
                case 0:
                    $dbtype = str_replace(':', '', $sec);
                    break;

                case 1:
                    // double slash
                    break;

                case 2:
                    $creds_server = explode('@', $sec);
                    if (strpos($creds_server[0], ':') !== false) {
                        list($dbuser, $dbpass) = explode(':', $creds_server[0]);
                    } else {
                        $dbuser = $creds_server[0];
                    }

                    if (strpos($creds_server[1], ':') !== false) {
                        list($dbhost, $dbport) = explode(':', $creds_server[1]);
                        if (empty($dbhost)) {
                            $dbhost = 'localhost';
                        }
                    } else {
                        $dbhost = $creds_server[1];
                    }
                    break;

                case 3:
                    $dbname = $sec;
                    break;
            }
            $count++;
        }

        $this->phptype = $this->dbsyntax = $dbtype;
        if ($dbtype == 'mysqli' || $dbtype == 'mysql') {
            $dbtype = 'pdo_mysql';
        } elseif ($dbtype == 'pgsql') {
            $dbtype = 'pdo_pgsql';
        }
        $dsn_array = array('driver' => $dbtype, 'user' => $dbuser, 'password' => $dbpass, 'host' => $dbhost,
            'port' => $dbport, 'dbname' => $dbname);
        return $dsn_array;
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

    public function quote($value)
    {
        return $this->connection->quote($value);
    }

    public function queryOne($sql)
    {
        $this->last_query = $sql;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        if ($result === false) {
            $result = null;
        }
        return $result;
    }

    public function queryAll($sql)
    {
        $this->last_query = $sql;
        return $this->connection->fetchAll($sql);
    }

    public function queryCol($sql)
    {
        $this->last_query = $sql;
        $result = null;
        $stmt = $this->connection->executeQuery($sql);
        while ($row = $stmt->fetchColumn()) {
            $result[] = $row;
        }
        return $result;
    }

    public function queryRow($sql)
    {
        $this->last_query = $sql;
        $stmt = $this->connection->executeQuery($sql);
        return $stmt->fetch();
    }

    public function disconnect()
    {

    }

    public function exec($sql)
    {
        $this->last_query = $sql;
        return $this->connection->executeUpdate($sql);
    }

    public function query($sql)
    {
        $this->last_query = $sql;
        return $this->connection->executeQuery($sql);
    }

    public function listTableFields($table)
    {
        static $table_fields = array();
        if (!isset($table_fields[$this->connection->getDatabase()][$table])) {
            $tableColumns = null;
            $sm = $this->connection->getSchemaManager();
            $columns = $sm->listTableColumns($table);
            $table_fields[$this->connection->getDatabase()][$table] = array_keys($columns);
        }
        return $table_fields[$this->connection->getDatabase()][$table];
    }

    public function loadModule($var1, $var2 = null, $var3 = null)
    {

    }

    public function listTables()
    {
        static $table_list = array();
        if (!isset($table_list[$this->connection->getDatabase()])) {
            $allTables = null;
            $sm = $this->connection->getSchemaManager();
            $tables = $sm->listTables();
            foreach ($tables as $tbl) {
                $allTables[] = $tbl->getName();
            }
            $table_list[$this->connection->getDatabase()] = $allTables;
        }
        return $table_list[$this->connection->getDatabase()];
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

    private function checkSequenceTable($sequence_name)
    {
        if ($this->tableExists($sequence_name)) {
            return;
        }

        if ($this->isMysql()) {
            $query = "CREATE TABLE $sequence_name (id int not null auto_increment, primary key (id))";
        } else {
            $query = "CREATE SEQUENCE $sequence_name INCREMENT 1 START 1";
        }

        $this->connection->executeQuery($query);
    }

    public function tableExists($sequence_table)
    {
        if ($this->isMysql()) {
            return $this->connection->getSchemaManager()->tablesExist(array($sequence_table));
        } else {
            $seqtb = $this->connection->getSchemaManager()->listSequences();
            foreach ($seqtb as $seq) {
                if ($seq->getName() === $sequence_table) {
                    return true;
                }
            }
            return false;
        }
    }

    public function isMysql()
    {
        return in_array($this->connection->getDriver()->getName(),
                array('pdo_mysql', 'mysqli'));
    }

    public function nextID($table_name)
    {
        $sequence_table = $table_name . '_seq';
        $this->checkSequenceTable($sequence_table);

        if ($this->isMysql()) {
            $this->connection->executeQuery("insert into $sequence_table (id) values (null)");
            $value = $this->connection->lastInsertId();
            $this->connection->executeQuery("delete from $sequence_table where id < $value");
        } else {
            $value = $this->queryOne("select nextval('$sequence_table')");
        }
        return $value;
    }

    public function isConnected()
    {
        return $this->connection->isConnected();
    }

}
