<?php

namespace phpws2;

/**
 * Class for creating DB objects and initializing the database connection.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * The factory class for DB and DSN creation.
 */
class Database
{

    /**
     * This is the DSN connection used by default for this site. Although the DSN
     * can be changed per construction of this class, this is the fallback DSN.
     * It is initialized in the Config/Configuration.php file. This variable remains
     * static as there is no need to have more than one default connection.
     * @var \phpws2\Database\DSN
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
     * Alias for newDB
     * @param \phpws2\Database\DSN $dsn
     * @return Database\DB
     */
    public static function getDB(\phpws2\Database\DSN $dsn = null)
    {
        return self::newDB($dsn);
    }

    /**
     * Creates a new DB object based on the dsn parameter OR the default
     * dsn.
     * @param \phpws2\Database\DSN $dsn
     * @return \phpws2\Database\DB
     * @throws \Exception
     */
    public static function newDB(\phpws2\Database\DSN $dsn = null)
    {
        if (is_null($dsn)) {
            if (empty(self::$default_dsn)) {
                if (defined('PHPWS_DSN')) {
                    if (defined('PHPWS_TABLE_PREFIX')) {
                        $tbl_prefix = PHPWS_TABLE_PREFIX;
                    } else {
                        $tbl_prefix = null;
                    }
                    Database::phpwsDSNLoader(PHPWS_DSN, $tbl_prefix);
                    $dsn = self::$default_dsn;
                } else {
                    throw new \Exception(\Canopy\Translation::t('Default DSN not set.'));
                }
            } else {
                $dsn = self::$default_dsn;
            }
        }

        $dbtype = $dsn->getDatabaseType()->get();

        if ($dbtype == 'mysqli') {
            $dbtype = 'mysql';
        }

        $class_name = '\phpws2\Database\Engine\\' . $dbtype . '\DB';

        $db = new $class_name($dsn);
        return $db;
    }

    /**
     * Returns the DSN object currently stored in the default_dsn static variable.
     * @return \phpws2\Database\DSN
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
     * @return \phpws2\Database\DSN
     */
    public static function newDSN($database_type, $username, $password = null, $database_name = null, $host = null, $port = null)
    {
        $dsn = new \phpws2\Database\DSN($database_type, $username, $password,
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

    public static function parseDSN($dsn)
    {
        $dbtype = $dbname = $dbuser = $dbpass = $dbhost = $dbport = null;
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

        $dsn_array = array('dbtype' => $dbtype, 'dbuser' => $dbuser, 'dbpass' => $dbpass, 'dbhost' => $dbhost,
            'dbport' => $dbport, 'dbname' => $dbname);
        return $dsn_array;
    }

    public static function phpwsDSNLoader($dsn, $table_prefix = null)
    {
        $dsn_array = self::parseDSN($dsn);
        extract($dsn_array);
        self::setDefaultDSN(self::newDSN($dbtype, $dbuser, $dbpass, $dbname,
                        $dbhost, $dbport));
        if ($table_prefix) {
            self::$default_dsn->setTablePrefix($table_prefix);
        }
    }

    /**
     * Sets the default dsn static variable.
     * @see \phpws2\Database\DSN::$dsn
     * @param \phpws2\Database\DSN $dsn
     */
    public static function setDefaultDSN(\phpws2\Database\DSN $dsn)
    {
        self::$default_dsn = $dsn;
    }

    /**
     * Loads a file, extracts dsn variables and constructs a DSN object.
     *
     * @param string $filename Path to dsn configuration file.
     * @return \phpws2\Database\DSN
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
            throw new \Exception(\Canopy\Translation::t('DSN file does not exist: %s', $filename));
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
    public static function getLastQuery($all = false, $html = true)
    {
        if ($all) {
            return implode(($html ? '<br>' : "\n"), self::$last_query);
        } else {
            $end = count(self::$last_query) - 1;
            return self::$last_query[$end];
        }
    }

}
