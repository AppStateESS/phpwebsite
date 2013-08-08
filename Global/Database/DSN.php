<?php

namespace Database;

/**
 * The DSN object stores information used to create a database connection.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class DSN extends \Data {

    /**
     * Type of database engine
     * @var string
     */
    protected $database_type;

    /**
     * Name of the database
     * @var Variable\String
     */
    protected $database_name;

    /**
     * Username that has access to the database
     * @var Variable\String
     */
    protected $username;

    /**
     * Password used to give user name access
     * @var Variable\String
     */
    protected $password;

    /**
     * Host database lives on
     * @var Variable\String
     */
    protected $host;

    /**
     * Character prefix set before table names to allow multiple installations
     * per database.
     * @var Variable\String
     */
    protected $table_prefix;

    /**
     * Port of access for database
     * @var Variable\Integer
     */
    protected $port;

    /**
     * Construction of object initializes a connection resource to the link
     * variable
     * @param string $dsn
     */
    public function __construct($database_type, $username, $password=null, $database_name=null, $host=null, $port=null)
    {
        $this->database_type = \Variable::factory('string', $database_type, 'database_type');
        $this->database_type->setLimit(12);
        $this->database_type->setLabel(t('Database type'));
        $this->database_type->wordCharactersOnly();

        $this->database_name = \Variable::factory('string', $database_name, 'database_name');
        $this->database_name->setLimit(58);
        $this->database_name->setLabel(t('Database name'));
        $this->database_name->wordCharactersOnly();

        $this->username = \Variable::factory('string', $username, 'username');
        $this->username->setLimit(255);
        $this->username->setLabel(t('Database user name'));
        $this->username->wordCharactersOnly();

        $this->password = \Variable::factory('string', $password, 'password');
        $this->password->setLimit(255);
        $this->password->setLabel(t('Database password'));
        $this->password->setInputType('password');

        $this->table_prefix = \Variable::factory('string', null, 'table_prefix');
        $this->table_prefix->allowNull(true);
        $this->table_prefix->setLimit(5);
        $this->table_prefix->setLabel(t('Table prefix'));
        $this->table_prefix->wordCharactersOnly();

        $this->host = \Variable::factory('string', null, 'host');
        $this->host->setLimit(255);
        $this->host->setLabel('Database host');
        $this->host->allowNull(true);
        $this->host->set($host);

        $this->port = \Variable::factory('integer', $port, 'port');
        $this->port->setLabel('Database port');
        $this->port->setRange('1', '65535');
        $this->port->allowNull(true);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getDatabaseType()
    {
        return $this->database_type;
    }

    public function getDatabaseName()
    {
        return (string)$this->database_name;
    }

    public function getTablePrefix()
    {
        return $this->table_prefix;
    }

    public function hasPrefix()
    {
        return !empty($this->table_prefix);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }


    /**
     * Returns a string formatted for a database connection
     *
     * @return string The DSN format needed by the PDO object
     */
    public function getPDOString()
    {
        $pdo_string[] = $this->database_type . ':';
        if (!$this->host->isEmpty()) {
            $pdo_string[] = 'host=' . $this->host . ';';
        }

        if (!$this->port->isEmpty()) {
            $pdo_string[] = 'port=' . $this->port . ';';
        }

        if (!$this->database_name->isEmpty()) {
            $pdo_string[] = 'dbname=' . $this->database_name;
        }

        return implode('', $pdo_string);
    }


    public function setUsername($username)
    {
        $this->username->set($username);
    }

    public function setPassword($password)
    {
        $this->password->set($password);
    }

    public function setDatabaseName($database_name)
    {
        $this->database_name->set($database_name);
    }

    public function setDatabaseType($database_type)
    {
        $this->database_type->set($database_type);
    }

    public function setTablePrefix($table_prefix)
    {
        $this->table_prefix->set($table_prefix);
    }

    public function setHost($host)
    {
        $this->host->set($host);
    }

    public function setPort($port)
    {
        $this->port->set($port);
    }

}

?>
