<?php

/**
 * The Response object communicates the result of a get, post or put.
 * It is returned by a module after the results of parsed Request.
 *
 * There are three states:
 * Success : every thing went well
 * Failure : there were some problems the user needs to correct before
 *           proceeding
 * Error   : the post caused an error
 *
 * A success will communicate any messages (if any). A failure should
 * communicate what caused the failure. An error should give an error message
 * and indicate how to proceed.
 *
 * Response is a singleton object. This allows functions after a post to access
 * the problems.
 *
 *
 * @see Request
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
define('RESPONSE_SUCCESS', 1);
define('RESPONSE_FAILURE', 0);
define('RESPONSE_ERROR', -1);

class Response extends View {

    /**
     * Status of post or put (see defines above)
     * @var integer
     */
    protected $status = RESPONSE_SUCCESS;

    /**
     * Message string (if any). Message should *NOT* be used for
     * errors or problems.
     * @var string
     */
    protected $message;

    /**
     * Array of problems passed on failure
     * @var array
     */
    protected $problems;

    /**
     * Error message passed on error status
     * @var string
     */
    protected $error;

    /**
     * @see Response::goForward()
     * @var string Url the user is pushed to on goForward
     */
    protected $forward_url;
    static $response = null;

    /**
     * Response is a singleton, there shouldn't ever be more than one.
     * @staticvar string $response Assures a single object is returned
     * @return Response
     */
    public static function singleton()
    {
        if (empty(self::$response)) {
            self::$response = new Response;
        }

        return self::$response;
    }

    private static function respondNow($result, $message = null)
    {
        $response = self::singleton();
        $response->setStatus($result);
        if ($message) {
            $response->setMessage($message);
        }
        return $response;
    }

    public static function success($message = null)
    {
        return self::respondNow(RESPONSE_SUCCESS, $message);
    }

    public static function failure($message = null)
    {
        return self::respondNow(RESPONSE_FAILURE, $message);
    }

    public static function error($message = null)
    {
        return self::respondNow(RESPONSE_ERROR, $message);
    }

    /**
     * Sets the status of the current Response. See class definition for more
     * information.
     * @param string $status
     */
    public function setStatus($status)
    {
        if (in_array($status, array(RESPONSE_SUCCESS, RESPONSE_FAILURE, RESPONSE_ERROR))) {
            $this->status = $status;
        }
    }

    /**
     * Sets a message communicated to the user after the response.
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        $_SESSION['Last_Message'] = $this->message;
    }

    /**
     * Appends the current message preceded with a space.
     * @param string $message
     */
    public function appendMessage($message)
    {
        if (!empty($this->message)) {
            $this->setMessage($this->message . ' ' . $message);
        } else {
            $this->setMessage($message);
        }
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the last saved message (or null if none was saved).
     * @return string|null
     */
    public function getLastMessage()
    {
        return isset($_SESSION['Last_Message']) ? $_SESSION['Last_Message'] : null;
    }

    /**
     * Fowards the user to the url stored in forward_url. If no url is set,
     * a null return should be expected.
     */
    public function goForward()
    {
        if (isset($this->forward_url)) {
            \Server::forward($this->forward_url);
        }
    }

    /**
     * Sets the forward_url variable.
     * @param string $url
     */
    public function setForwardUrl($url)
    {
        if (!preg_match('/^http(s)?:/i', $url)) {
            $url = \Server::getSiteUrl() . $url;
        }
        $this->forward_url = $url;
    }

    /**
     * Add a problem string to the queue. The id of the problem will be the
     * variable name.
     * @param string $id
     * @param string $problem
     */
    public function addProblem($id, $problem)
    {
        $this->problems[$id] = $problem;
    }

    /**
     * @return string Success, failure, error
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * Prints the status in JSON
     */
    public function printStatus()
    {
        echo $this->getJSON();
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->status == RESPONSE_SUCCESS;
    }

    /**
     * @return boolean
     */
    public function isFailure()
    {
        return $this->status == RESPONSE_FAILURE;
    }

    /**
     * @return boolean
     */
    public function isError()
    {
        return $this->status == RESPONSE_ERROR;
    }

    /**
     * Returns the array of problems, or null if none found
     * @return array|null
     */
    public function getProblems()
    {
        return $this->problems;
    }

    /**
     * Set the error message for an error status.
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    public function __toString()
    {
        return (string) $this->message;
    }

    public function showMessage()
    {
        $js = Javascript::getScriptObject('Message');
        $js->message = (string) $this->message;
        switch ($this->status) {
            case RESPONSE_SUCCESS:
                $js->type = 'success';
                break;
            case RESPONSE_FAILURE:
                $js->type = 'problem';
                break;
            case RESPONSE_ERROR:
                $js->type = 'error';
                break;
        }
    }

}

?>