<?php

/**
 * Session is a replacement class for the $_SESSION array. Obviously, the
 * superglobal is still being used, but the variables are controlled through
 * this class.
 *
 * First pull the singleton
 *
 * $session = Session::singleton();
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
class Session extends Data {

    /**
     * @var array
     */
    private $values;

    /**
     * Creates a session object
     */
    private function __construct()
    {
        if (isset($_SESSION['Beanie_Session'])) {
            $this->values = & $_SESSION['Beanie_Session'];
        } else {
            $_SESSION['Beanie_Session'] = & $this->values;
        }
    }

    /**
     * Starts the session
     */
    public static function start()
    {
        define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
        session_name(SESSION_NAME);
        session_start();
    }

    /**
     * Returns the static session object for use.
     * @staticvar Session $session
     * @return \Session
     */
    public static function singleton()
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
        unset($_SESSION['Beanie_Session']);
        self::start();
    }
}

?>