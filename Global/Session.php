<?php

/**
 * Controlling session for Beanie. Allows for session to be shared and
 * divided among sites. Using APC.
 *
 *
 * @todo Untested and incomplete
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
        static $session;

        if (empty($session)) {
            $session = new Session;
        }
        return $session;
    }

    public function __set($name, $value)
    {
        $this->values[$name] = $value;
    }

    public function __get($name)
    {
        if (!isset($this->values[$name])) {
            throw new \Exception(t('Variable "%s" not set in the Session', $name));
        }
        return $this->values[$name];
    }

    public function __isset($name)
    {
        return isset($this->values[$name]);
    }

    public function __unset($name)
    {
        unset($this->values[$name]);
    }

    public function reset()
    {
        unset($_SESSION['Beanie_Session']);
    }

    public function destroy($name)
    {
        unset($this->values[$name]);
    }

}

?>