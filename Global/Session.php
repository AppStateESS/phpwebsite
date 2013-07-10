<?php

/**
 * Session is a replacement class for the $_SESSION array. Obviously, the
 * superglobal is still being used, but the variables are controlled through
 * this class.
 *
 * First pull the singleton
 *
 * $session = Session::getInstance();
 *
 * Now setting and getting variables is simple:
 *
 * $session->foo = 'bar';
 *
 * echo $session->foo; // echoes bar
 *
 * The Session class is using overloading. So _set and _get are controlling
 * the setting and getting. You can also use isset and unset like so:
 *
 * if (isset($session->foo)) {
 *      echo 'Foo is here!';
 * }
 *
 * unset($session->foo);
 *
 * If a session variable is not set (or unset) and get is called on it an
 * exception will be thrown.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
require_once PHPWS_SOURCE_DIR . 'Global/Data.php';

class Session extends Data {

    /**
     * Values of the PHPWS Session
     * @var array
     */
    private $values;

    /**
     * Indicates if session_start has been called
     * @var boolean
     */
    private static $started = false;

    /**
     * Name of the current session
     * @var string
     */
    private static $session_name = null;

    /**
     * Creates a session object
     */
    private function __construct()
    {
        if (!self::$started) {
            self::start();
        }
        $this->values = & $_SESSION['PHPWS'];
    }

    /**
     * Starts the session
     */
    public static function start($session_name = null)
    {
        if (self::$started) {
            throw new \Exception(t('Session has already been started'));
        }
        if (!isset($session_name)) {
            self::$session_name = md5(SITE_HASH . $_SERVER['REMOTE_ADDR']);
        } else {
            self::$session_name = $session_name;
        }
        session_name(self::$session_name);
        session_start();
        self::$started = true;
        if (!isset($_SESSION['PHPWS'])) {
            $_SESSION['PHPWS'] = array();
        }
    }

    /**
     * Returns the static session object for use.
     * @staticvar Session $session
     * @return \Session
     */
    public static function getInstance()
    {
        static $session = null;

        if (empty($session)) {
            $session = new Session;
        }
        return $session;
    }

    /**
     * Sets a Session variable
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Returns a Session variable if it is set.
     * @param string $name
     * @return mixed
     * @throws \Exception Thrown if session variable is not set.
     */
    public function __get($name)
    {
        if (!isset($this->values[$name])) {
            throw new \Exception(t('Variable "%s" not set in the Session', $name));
        }
        return $this->values[$name];
    }

    /**
     * Returns true if the Session variable is set.
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->values[$name]);
    }

    /**
     * Removes a Session variable from the value stack.
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->values[$name]);
    }

    /**
     * Completely resets the SESSION superglobal used by the Session class.
     */
    public function reset()
    {
        unset($_SESSION['PHPWS']);
        $_SESSION['PHPWS'] = array();
        $this->values = array();
    }

}

?>