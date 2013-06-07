<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * This class handles requests made by the previous page. The request will be
 * either a GET (requesting a view or response), a POST (submission of information
 * leading to change in the system), or a PUT (creation of a new item n the system).
 */
class Request extends Data {
    /**
     * Constant defining a GET request was sent.
     */

    const GET = 1;

    /**
     * Constant defining a POST request was sent.
     */
    const POST = 2;
    /**
     * Constant defining a PUT request was sent.
     */
    const PUT = 3;

    /**
     * Instantiated object of this class
     * @var Request
     */
    private static $singleton;

// @todo not sure if below would just be part of put (delete) or get(search)
    /*
      const DELETE = 4;
      const SEARCH = 5;
     */

    /**
     * Holds the current post. Normally, a copy of the _POST superglobal but
     * it may be set manually for testing purposes.
     * @var type
     */
    private $post = null;

    /**
     * Holds the current get. Normally, a copy of the _GET superglobal but
     * it may be set manually for testing purposes.
     * @var type
     */
    private $get = null;

    /**
     * The command requested from the previous page. This can be any string that
     * directs the current module to action.
     * @var array
     */
    private $command = null;

    /**
     * The currently requested module. This will be contained in the
     * POST/GET/PUT.
     *
     * @var string
     */
    private $module = null;

    /**
     * A copy of the current url.
     * @var string
     */
    private $url = null;

    /**
     * The id of the item/content/element that is acted upon on the current command.
     * @todo Is the id always going to be an id especially since md5 id was discussed? Might drop
     * @var integer
     */
    private $id = null;

    /**
     * The state of the current command
     * GET is the default state
     * @var boolean
     */
    private $state = self::GET;

    /**
     * Builds the current page request object. Private as it is a singleton.
     * @see Request::singleton()
     * @param type $force_reload
     */
    private function __construct()
    {
        // loadUrl should be before loadGet
        $this->loadUrl();
        $this->loadGet();
        $this->loadPost();
        $this->loadId();
        $this->loadState();
    }

    /**
     * (Re)loads the superglobal _GET into the get variable
     * @return void
     */
    public function loadGet()
    {
        if (!empty($_GET)) {
            $this->get = $_GET;
            if (!empty($this->get['module'])) {
                $this->setModule($this->get['module']);
            }
        }
    }

    /**
     * (Re)loads the superglobal _POST into the post variable
     * @return void
     */
    public function loadPost()
    {
        if (!empty($_POST)) {
            $this->post = $_POST;
            if (!empty($_POST['module'])) {
                $this->setModule($_POST['module']);
            }
        }
    }

    /**
     * Receives the page url, parses it, and sets the module and commands based
     * on what it finds.
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        if (preg_match('/index\.php$/', $url)) {
            return;
        }
        if (!empty($url) && !preg_match('/^index\.php/i', $url)) {
            $variables = explode('/', $url);
            $url1 = preg_replace('/\?.*$/', '', $url);

            // strips beginning, end, and double slashes
            $url2 = preg_replace('@//+@', '/', $url1);
            $url3 = preg_replace('@^/|/$@', '', $url2);
            $url_arr = explode('/', $url3);
            $this->setModule(array_shift($url_arr));
        } else {
            $var_pairs = explode('&', str_ireplace('index.php?', '', $url));
            foreach ($var_pairs as $var) {
                list($key, $value) = explode('=', $var);
                if ($key == 'module') {
                    $this->setModule($value);
                } else {
                    $variables[$key] = $value;
                }
            }
        }
        if (isset($variables)) {
            $this->setCommand($variables);
        }
        $this->url = $url;
    }

    /**
     * @return string The currently set url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the url variable based on the current url.
     * @return void
     */
    public function loadUrl()
    {
        $this->setUrl(Server::getCurrentUrl());
    }

    /**
     * Pops the last command off the GET process string
     * @return string
     */
    public static function pop()
    {
        $request = self::singleton();
        return $request->popCommand();
    }

    /**
     * Shifts the first command off the GET process string
     * @return string
     */
    public static function shift()
    {
        $request = self::singleton();

        return $request->shiftCommand();
    }

    /**
     * Loads the id contained in the command variable.
     */
    private function loadId()
    {
        if (empty($this->command)) {
            return;
        }
        foreach ($this->command as $com) {
            if (is_numeric($com)) {
                $this->id = $com;
                break;
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the state of the request (GET, POST, PUT)
     * @return void
     */
    public function loadState()
    {
        if (!empty($this->post)) {
            if (!empty($this->id)) {
                $this->state = self::PUT;
            } else {
                $this->state = self::POST;
            }
        } elseif (!empty($this->module)) {
            $this->state = self::GET;
        }

        if (!empty($this->get)) {
            $this->state = self::GET;
        }
    }

    /**
     * @return boolean True if the request is from a POST
     */
    public function isPost()
    {
        return $this->state == self::POST;
    }

    /**
     * @return boolean True if the request is from a GET
     */
    public function isGet()
    {
        return $this->state == self::GET;
    }

    /**
     * @return boolean True is the request is from a PUT
     */
    public function isPut()
    {
        return $this->state == self::PUT;
    }

    public function isPostVar($variable_name)
    {
        return isset($this->post[$variable_name]);
    }

    public function isGetVar($variable_name)
    {
        return isset($this->get[$variable_name]);
    }

    public function isReqVar($variable_name)
    {
        return ($this->isPostVar($variable_name) || $this->isGetVar($variable_name));
    }

    /**
     * @todo decide if using
      public function isDelete() {
      return $this->state == self::DELETE;
      }
     */

    /**
     * Manually sets the state of the request.
     * @param integer $state
     * @return void | Exception if unknown type
     */
    public function setState($state)
    {
        if (in_array($state, array(self::PUT, self::POST, self::GET))) {
            $this->state = $state;
        } else {
            throw new \Exception(t('Unknown state type'));
        }
    }

    /**
     * Returns the current state in plain text
     * @return string
     */
    public function getState()
    {
        switch ($this->state) {
            case self::PUT:
                return 'put';

            case self::POST:
                return 'post';
            /*
              case self::DELETE:
              return 'delete';

              case self::SEARCH:
              return 'search';
             */
            default:
            case self::GET:
                return 'get';
        }
    }

    /**
     * Sets a variable in the post array
     * @param string $variable_name
     * @param string $value
     */
    public function setPost($variable_name, $value)
    {
        $this->post[$variable_name] = $value;
    }

    /**
     * @param $variable_name
     * @return array|Exception if variable missing
     */
    public function getPost($variable_name = null)
    {
        if (is_null($variable_name)) {
            return $this->post;
        } else {
            if (!isset($this->post[$variable_name])) {
                throw new \Exception(t('Post variable missing'));
            }
            return $this->post[$variable_name];
        }
    }

    public static function post($variable_name = null)
    {
        $request = self::singleton();
        return $request->getPost($variable_name);
    }

    public static function get($variable_name = null)
    {
        $request = self::singleton();
        return $request->getGet($variable_name);
    }

    /**
     * Sets a variable in the get array
     * @param array $get
     */
    public function setGet($variable_name, $value)
    {
        $this->get[$variable_name] = $value;
    }

    /**
     * @return array|Exception
     */
    public function getGet($variable_name = null)
    {
        if (is_null($variable_name)) {
            return $this->get;
        } else {
            if (!isset($this->get[$variable_name])) {
                throw new \Exception(t('Get variable not found'));
            }
            return $this->get[$variable_name];
        }
    }

    /**
     * Sets the commands expected of the module
     * @param array $command
     */
    public function setCommand(array $command)
    {
        $this->command = $command;
    }

    /**
     * Returns the commands expected of the module
     * @return array
     */
    public function getCommand()
    {
        return $this->command;
    }

    public function getCommandAsDirectory()
    {
        return implode('/', $this->command);
    }

    public function getCommandAsNamespace()
    {
        return implode('\\', $this->command);
    }

    /**
     *
     * @return boolean True if the command variable contains data.
     */
    public function hasCommand()
    {
        return !empty($this->command);
    }

    /**
     * The current module needed to be acted upon
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     *
     * @return string The current module needed to be acted on
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Pops the first value off the command stack. If the stack needs resetting,
     * call loadUrl
     */
    public function shiftCommand()
    {
        if (empty($this->command) || !is_array($this->command)) {
            return null;
        }
        return array_shift($this->command);
    }

    /**
     * Pops the last value off the command stack.
     * loadUrl to reset
     */
    public function popCommand()
    {
        if (empty($this->command) || !is_array($this->command)) {
            return null;
        }
        return array_pop($this->command);
    }

    /**
     * Method used to create the request object.
     * @staticvar string $request
     * @param boolean $force_reset If false, the current request is returned (if not null)
     * @return \Request
     */
    public static function singleton($force_reset = false)
    {
        if (empty(self::$singleton) || $force_reset) {
            self::$singleton = new Request;
        }

        return self::$singleton;
    }

    /**
     * Passes the get or post state off to the class requested.
     *
     * Example:
     * The address below is entered:
     * site.com/Foo/Bar/Alpha
     *
     * In Foo/Module.php, the get() method could use:
     * return \Request::pass('Foo');
     *
     * Request would then call the next value in the call - Bar.
     * If Foo/Bar can be constructed and the state method, get(), exists, then
     * it will be called.
     *
     * @param string $namespace Namespace containing the class to pass off the state to
     * @return \Response
     * @throws \Exception
     */
    public static function pass($namespace)
    {
        $state = self::$singleton->getState();

        $class_name = $namespace;

        while ($command = self::$singleton->shiftCommand()) {
            $class_name .= '\\' . $command;
            if (class_exists($class_name) && method_exists($class_name, $state)) {
                $obj = new $class_name;
                return $obj->$state();
            }
        }
        throw new \Exception(t('Pass command called by %s module cannot continue',
                $class_name));
    }

    /**
     * Checks to see the current require was an ajax request
     * @return boolean
     */
    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

}

?>