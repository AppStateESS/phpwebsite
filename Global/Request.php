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

    const GET = 'GET';

    /**
     * Constant defining a HEAD request was sent.
     */
    const HEAD = 'HEAD';

    /**
     * Constant defining a POST request was sent.
     */
    const POST = 'POST';

    /**
     * Constant defining a PUT request was sent.
     */
    const PUT = 'PUT';

    /**
     * Constant defining a DELETE request was sent.
     */
    const DELETE = 'DELETE';

    /**
     * Constant defining a OPTIONS request was sent.
     */
    const OPTIONS = 'OPTIONS';

    /**
     * Constant defining a PATCH request was sent.
     */
    const PATCH = 'PATCH';

    /**
     * An array of commands derived from the url
     * @var array
     */
    protected $commands;

    /**
     * Holds the key/value data available from the various request methods
     * @var vars
     */
    protected $vars = null;

    /**
     * Holds the raw Data field from the Request.  This could be JSON data
     * (application/json) or it could be raw form data
     * (application/x-www-form-urlencoded or multipart/form-data) - it is up to
     * the programmer to decide.
     */
    private $data = null;

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
     * The state of the current command
     * GET is the default state
     * @var boolean
     */
    private $method = null;

    /**
     * An instance of Http\Accept, which should be used to determine the type of
     * data that will be sent to the client.
     * @var Http\Accept
     */
    private $accept;

    /**
     * Builds the current page request object.
     *
     * @param $url string The URL
     * @param $vars array|null Request Variables ($_REQUEST, etc)
     * @param $data mixed The raw content area of the HTTP request (JSON and
     *                    Form data)
     * @param $accept Http\Accept
     */
    public function __construct($url, $method, array $vars = null, $data = null, Http\Accept $accept = null)
    {
        $this->setUrl($url);
        $this->setMethod($method);

        if (is_null($vars)) {
            $vars = array();
        }
        $this->setVars($vars);

        $this->setData($data);

        // @todo I am a bit worried about the default here; in fact, it should
        // probably not be allowed to be null at all.
        if (is_null($accept))
            $accept = new Http\Accept('text/html');
        $this->setAccept($accept);
        $this->buildCommands();
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
            $this->url = '/';
            return;
        }

        // Ensure consistency in URLs
        $this->url = $this->sanitizeUrl($url);
    }

    /**
     * Builds the commands parameter based on the current url.
     */
    public function buildCommands()
    {
        $url = preg_replace('@^/@', '', $this->url);
        $cmds = explode('/', $url);
        if ($cmds[0] == $this->module) {
            array_shift($cmds);
        }
        $this->commands = $cmds;
    }

    /**
     * Shifts a command from the front of the stack
     * @return type
     */
    public function shiftCommand()
    {
        return array_shift($this->commands);
    }

    /**
     * Turns all of the various and wonderful things you can do with a URL into
     * a consistent query, for example /a/./b/ becomes /a/b/.
     * @param string $url The URL to sanitize
     * @return string The sanitized URL
     */
    public function sanitizeUrl($url)
    {
        // Repeated Slashes become One Slash
        $url = preg_replace('@//+@', '/', $url);

        // Fix all instances of dot as "current directory"
        $url = preg_replace('@^(\./)+@', '', $url);
        $url = preg_replace('@(/\.)+$@', '/', $url);
        $url = preg_replace('@/(\./)+@', '/', $url);

        // Ensure Preceding Slash
        if (substr($url, 0, 1) != '/') {
            $url = '/' . $url;
        }

        // Remove Trailing Slash
        if (substr($url, -1, 1) == '/' && strlen($url) > 1) {
            $url = substr($url, 0, -1);
        }

        // Strip parameters
        $url = preg_replace('/(\?|&)\w.*$/', '', $url);
        return $url;
    }

    /**
     * @return string The currently set url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the Data component, which should hold JSON and Form Post data
     *
     * @param $data mixed The Data
     */
    protected function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Returns the raw POST data from the request.
     *
     * @return string The raw POST data.
     */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * Tries to json_decode the raw POST data from the request.  Please note
     * that the programmer must decide if this is what they were expecting.
     *
     * Same as a call to json_decode($request->getRawData());
     *
     * @return array The json_decoded POST data.
     */
    public function getJsonData()
    {
        return json_decode($this->getRawData());
    }

    /**
     * @return boolean True if the request is from a POST
     */
    public function isPost()
    {
        return $this->method == self::POST;
    }

    /**
     * @return boolean True if the request is from a GET
     */
    public function isGet()
    {
        return $this->method == self::GET;
    }

    /**
     * @return boolean True is the request is from a PUT
     */
    public function isPut()
    {
        return $this->method == self::PUT;
    }

    // TODO: Add isX methods for Delete, Head, Options, Patch
    //
    public function setVars(array $vars)
    {
        $this->vars = $vars;

        // 1.x Compatibility
        if (array_key_exists('module', $vars)) {
            $this->setModule($vars['module']);
        }
    }

    /**
     * Checks to see if the $variable_name exists in the $vars parameter. If so,
     * it is returned, else the $then parameter is returned.
     * @param string $variable_name
     * @param mixed $then
     * @return mixed
     */
    public function ifNotVarThen($variable_name, $then)
    {
        return $this->isVar($variable_name) ? $this->getVar($variable_name) : $then;
    }

    /**
     * @param string $variable_name
     * @return boolean True if the variable is on the REQUEST
     */
    public function isVar($variable_name)
    {
        return array_key_exists($variable_name, $this->vars);
    }

    /**
     * Returns true is variable is not set or is empty (0, '', null)
     * @param string $variable_name
     * @return boolean true
     */
    public function isEmpty($variable_name)
    {
        return array_key_exists($variable_name, $this->vars) && empty($this->vars[$variable_name]);
    }

    /**
     * @param $variable_name string The name of the request variable to get
     * @param $default string|null The default value to return if not set
     * @return string The value of the requested variable
     */
    public function getVar($variable_name, $default = null)
    {
        if (!$this->isVar($variable_name)) {
            if (isset($default)) {
                return $default;
            } else {
                throw new Exception(t('Variable "%s" not found', $variable_name));
            }
        }

        return $this->vars[$variable_name];
    }

    /**
     * Returns all the variables set in the vars variable.
     * @return array
     */
    public function getRequestVars()
    {
        return $this->vars;
    }

    /**
     * @param $variable_name string The name of the request variable to set
     * @param $value string The value for the request variable
     * @return void
     */
    public function setVar($variable_name, $value)
    {
        $this->vars[$variable_name] = $value;
    }

    /**
     * Manually sets the state of the request.
     * @param integer $state
     * @return void | Exception if unknown type
     */
    public function setMethod($method)
    {
        if (in_array($method,
                        array(self::PUT, self::POST, self::GET, self::DELETE, self::OPTIONS, self::PATCH, self::HEAD))) {
            $this->method = $method;
        } else {
            throw new \Exception(t('Unknown state type'));
        }
    }

    /**
     * Returns the current request method in plain text
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
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
     * Sets the Accept object
     * @param $accept Http\Accept The Accept object for this request
     */
    public function setAccept(Http\Accept $accept)
    {
        $this->accept = $accept;
    }

    /**
     * Gets the Accept object
     * @return Http\Accept The Accept object for this request
     */
    public function getAccept()
    {
        return $this->accept;
    }

    public function getCurrentToken()
    {
        preg_match('@^(/[^/]*)@', $this->getUrl(), $matches);

        if ($matches[0] == '/')
            return '/';

        return substr($matches[0], 1);
    }

    /**
     * Pops the last token off the Request and returns a new Request object. So
     * <code>
     * # using a url like "moduleName/Alpha/Beta"
     *
     * echo $request->getCurrentToken();
     * // prints "Alpha"
     *
     * $new_r = $request->getNextRequest();
     * echo $new_r->getCurrentToken();
     * // prints "Beta"
     * </code>
     *
     * @return \Request
     */
    public function getNextRequest()
    {
        $url = preg_replace('@^/[^/]*@', '', $this->getUrl());

        return new Request(
                $url, $this->getMethod(), $this->getRequestVars(),
                $this->getRawData(), $this->getAccept());
    }

    /**
     * Checks to see the current require was an ajax request
     * @return boolean
     */
    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    public static function show()
    {
        if (!empty($_POST)) {
            ob_start();
            var_dump($_POST);
            $post_vars = ob_get_clean();
        } else {
            $post_vars = t('Empty');
        }

        if (!empty($_GET)) {
            ob_start();
            var_dump($_GET);
            $get_vars = ob_get_clean();
        } else {
            $get_vars = t('Empty');
        }

        $content[] = '<h2>$_POST</h2>' . $post_vars;
        $content[] = '<hr>';
        $content[] = '<h2>$_GET</h2>' . $get_vars;

        echo implode('', $content);
    }

}

?>
