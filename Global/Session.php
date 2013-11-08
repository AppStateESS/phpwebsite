<?php

/**
 * Session is a replacement class for the $_SESSION array.
 * Obviously, the
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
 * echo 'Foo is here!';
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
     * Holds the singleton instance of this class.
     *
     * @var unknown
     */
    private static $instance;

    /**
     * The name of the top-level session variable everything is stored in.
     *
     * @var unknown
     */

    const SESSION_KEY = 'PHPWS';

    /**
     * Values of the PHPWS Session
     *
     * @var array
     */
    private $values;

    /**
     * Indicates if session_start has been called
     *
     * @var boolean
     */
    private $started = false;

    /**
     * Name of the current session
     *
     * @var string
     */
    private $sessionName = null;

    /**
     * Returns the static session object for use.
     *
     * @staticvar Session $session
     * @return \Session
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Session();
        }

        return self::$instance;
    }

    /**
     * Private constructor for singleton pattern.
     * Creates a session object
     */
    private function __construct()
    {
        // If the session hasn't been started, then start it
        if (!$this->started) {
            $this->start();
        }

        // Grab a reference to the session variable we're using
        $this->values = & $_SESSION[self::SESSION_KEY];
    }

    /**
     * Starts the session
     */
    private function start()
    {
        // Check that the session hasn't already been started
        if ($this->started) {
            throw new \Exception(t('Session has already been started'));
        }

        // Generate the session name, if not already set
        if (!isset($this->sessionName)) {
            // NB: This session name will not work if used behind a reverse proxy.
            // It also won't work for two users who are behind the same proxy.
            $this->sessionName = md5(SITE_HASH . $_SERVER['REMOTE_ADDR']);
        }

        // Set the session name and start the session
        session_name($this->sessionName);
        session_start();

        $this->started = true;

        // Initialize a variable on the session to store everything
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = array();
        }
    }

    /**
     * Closes the session.
     */
    public function close()
    {
        $this->started = false;
        session_write_close();
    }

    /**
     * Checks the status of the php Session. Returns true if a session has be started, false otherwise.
     * @return boolean
     */
    public function isActive()
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Sets a Session variable
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!$this->isActive()) {
            throw new Exception('Session is not active.');
        }

        $this->values[$name] = $value;
    }

    /**
     * Returns a Session variable if it is set.
     *
     * @param string $name
     * @return mixed
     * @throws \Exception Thrown if session variable is not set.
     */
    public function __get($name)
    {
        if (!$this->isActive()) {
            throw new Exception('Session is not active.');
        }

        if (!isset($this->values[$name])) {
            throw new \Exception(t('Variable "%s" not set in the Session', $name));
        }

        return $this->values[$name];
    }

    /**
     * Returns true if the Session variable is set.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (!$this->isActive()) {
            throw new Exception('Session is not active.');
        }

        return isset($this->values[$name]);
    }

    /**
     * Removes a Session variable from the value stack.
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (!$this->isActive()) {
            throw new Exception('Session is not active.');
        }

        unset($this->values[$name]);
    }

    /**
     * Completely resets the SESSION superglobal used by the Session class.
     */
    public function reset()
    {
        if (!$this->isActive()) {
            throw new Exception('Session is not active.');
        }

        unset($_SESSION[self::SESSION_KEY]);
        $_SESSION[self::SESSION_KEY] = array();
    }

}

?>