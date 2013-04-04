<?php

/**
 * Class for creating DB objects and initializing the database connection.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
require_once 'Config/Defines.php';

/**
 * The factory class for DB and DSN creation.
 */
class Database {

    /**
     * This is the DSN connection used by default for this site. Although the DSN
     * can be changed per construction of this class, this is the fallback DSN.
     * It is initialized in the Config/Configuration.php file. This variable remains
     * static as there is no need to have more than one default connection.
     * @var \Database\DSN
     */
    static private $default_dsn;

    /**
     * A stack of the previous queries
     * @var array
     */
    private static $last_query = array();

    public static function logQuery($query)
    {
        self::$last_query[] = $query;
    }

    /**
     * Creates a new DB object based on the dsn parameter OR the default
     * dsn.
     * @param \Database\DSN $dsn
     * @return \Database\DB
     * @throws \Exception
     */
    public static function newDB(\Database\DSN $dsn = null)
    {
        if (is_null($dsn)) {
            if (empty(self::$default_dsn)) {
                throw new \Exception(t('Default DSN not set.'));
            } else {
                $dsn = self::$default_dsn;
            }
        }

        $class_name = '\Database\Engine\\' . $dsn->getDatabaseType() . '\DB';

        $db = new $class_name($dsn);
        return $db;
    }

    /**
     * Returns the DSN object currently stored in the default_dsn static variable.
     * @return \Database\DSN
     */
    public static function getDefaultDSN()
    {
        return self::$default_dsn;
    }

    /**
     * Constructs and returns a new DSN object based on the passed function parameters
     * @param string $database_type
     * @param string $username
     * @param string $password
     * @param string $database_name
     * @param string $host
     * @param string $port
     * @return \Database\DSN
     */
    public static function newDSN($database_type, $username, $password = null, $database_name = null, $host = 'localhost', $port = null)
    {
        $dsn = new \Database\DSN($database_type, $username, $password,
                $database_name, $host, $port);
        return $dsn;
    }

    /**
     * Loads the file indicated by $filename, creates a DSN object, then uses it
     * to set the default DSN.
     *
     * @param type $filename Path to dsn file
     */
    public static function setDefaultDSNFromFile($filename)
    {
        self::setDefaultDSN(self::createDSNFromFile($filename));
    }

    /**
     * Sets the default dsn static variable.
     * @see \Database\DSN::$dsn
     * @param \Database\DSN $dsn
     */
    public static function setDefaultDSN(\Database\DSN $dsn)
    {
        self::$default_dsn = $dsn;
    }

    /**
     * Loads a file, extracts dsn variables and constructs a DSN object.
     *
     * @param string $filename Path to dsn configuration file.
     * @return \Database\DSN
     * @throws \Exception
     */
    public static function createDSNFromFile($filename)
    {
        $database_name = null;
        $database_type = null;
        $username = null;
        $password = null;
        $host = null;
        $port = null;

        if (!is_file($filename)) {
            throw new \Exception(t('DSN file does not exist: %s', $filename));
        }
        include $filename;

        return self::newDSN($database_type, $username, $password,
                        $database_name, $host, $port);
    }

    /**
     * Returns the last query requested if $all is FALSE. If $all is TRUE,
     * all queries in the command stack are returned.
     * @param boolean $all
     * @param boolean $html If true, add breaks on all
     * @return string
     */
    public static function getLastQuery($all = false, $html=true)
    {
        if ($all) {
            return implode(($html ? '<br>' : "\n"), self::$last_query);
        } else {
            $end = count(self::$last_query) - 1;
            return self::$last_query[$end];
        }
    }

}

?>
