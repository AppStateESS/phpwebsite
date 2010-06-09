<?php
namespace core;
/**
 * There are two important GLOBAL values in Javascript. This prevents repeats of headers and includes.
 * $this->includes is an array of scripts to be included
 * $GLOBALS['Javascript_Headers'] is an array of script to be added to the head of the page.
 * Both are accessed by Layout.
 *
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */


Core::initCoreClass('jsmin.php');

abstract class Javascript extends Data {
    /**
     * @var string
     */
    protected $script_name = null;
    /**
     * If true, jquery is initialized
     * @var unknown_type
     */
    protected $use_jquery = true;

    protected $head_script = null;

    protected $body_script = '';

    protected $demo_code = null;

    protected $example = null;

    protected $includes = null;

    /**
     * Indicates whether object has been prepared for output
     * factory object should trip this flag after preparation
     * @var boolean
     */
    protected $prepared = false;

    /**
     * An array of scripts to include in the head.
     *
     * @var array
     */

    abstract public function loadDemo();

    /**
     * The prepare function should ready the head and body scripts for usage.
     */
    abstract public function prepare();

    /**
     * Indicates whether jquery has been included. See loadJQuery
     * @var boolean
     */
    protected static $jquery_loaded = false;

    /**
     * Tracks factory scripts includes and prevents repeat
     * @var array
     */
    protected static $script_list = null;

    /**
     * List of current head script keys. Prevents repeats in head of document
     * @var array
     */
    protected static $current_heads = array();


    public function isPrepared()
    {
        return $this->prepared;
    }

    public function setBodyScript($body_script)
    {
        $this->body_script = $body_script;
    }

    public function setHeadScript($head_script, $wrap=false, $jsmin=false)
    {
        if ($jsmin) {
            $head_script = $this->jsmin($head_script);
        }

        if ($wrap) {
            $head_script = $this->wrapScript($head_script);
        }

        $this->head_script = $head_script;
    }

    /**
     * Returns a string consisting of all current Javascript headers and includes
     */
    public static function getObjects()
    {
        return $GLOBALS['Javascript_Objects'];
    }

    public static function getHeaders()
    {
        $head = null;
        if (isset($GLOBALS['Javascript_Objects'])) {
            foreach ($GLOBALS['Javascript_Objects'] as $js) {
                if (!$js->isPrepared()) {
                    $js->prepare();
                }
                if ($include = $js->getIncludes()) {
                    $includes[] = $include;
                }
                if ($header = $js->getHeadScript()) {
                    $headers[] = $header;
                }
            }
            if (isset($includes)) {
                $head .= implode("\n", $includes);
            }

            if (isset($headers)) {
                $head .= implode("\n", $headers);
            }
        }
        return $head;
    }

    public function getIncludes()
    {
        if (!empty($this->includes)) {
            return implode("\n", $this->includes) . "\n";
        }
    }

    public function getHeadScript()
    {
        return $this->head_script;
    }

    public function getBodyScript()
    {
        if (!$this->isPrepared()) {
            $this->prepare();
        }
        return $this->body_script;
    }

    public static function factory($script_name)
    {
        if (!isset(self::$script_list[$script_name])) {
            $js_path = PHPWS_SOURCE_DIR . 'javascript/' . $script_name . '/factory.php';
            if (!is_file($js_path)) {
                throw new PEAR_Exception(dgettext('core', 'Could not find javascript factory file.'));
            }
            require_once $js_path;
            self::$script_list[$script_name] = 1;
        }

        $factory_class_name = 'javascript_' . $script_name;
        $js = new $factory_class_name;
        $js->script_name = $script_name;
        if ($js->use_jquery) {
            $js->loadJQuery();
        }

        $GLOBALS['Javascript_Objects'][] = $js;
        return $js;
    }

    /**
     * Example code displayed during demo mode. This should consist of
     * the expected php code to make your script function.
     * @param string $code
     */
    protected function setDemoCode($code)
    {
        $this->demo_code = $code;
    }

    /**
     * Example html that may be required for your code demo
     * @param string $example
     */
    protected function setDemoExample($example)
    {
        $this->example = $example;
    }

    protected function getDemoCode()
    {
        return '<fieldset><legend>' . $this->script_name . '</legend><pre>' . $this->demo_code . '</pre></fieldset>';
    }


    protected function getDemoExample()
    {
        if (!empty($this->example)) {
            return $this->example;
        }
    }

    public function wrapScript($script)
    {
        return <<<EOF
<script type="text/javascript">
//<![CDATA[
$script
//]]>
</script>
EOF;
    }

    public function wrapInclude($script)
    {
        return <<<EOF
<script type="text/javascript" src="$script"></script>
EOF;
    }

    public function loadJQuery()
    {
        if (!self::$jquery_loaded) {
            $this->addInclude(PHPWS_SOURCE_HTTP . 'javascript/jquery/jquery.js', true, true);
            self::$jquery_loaded = true;
        }
    }

    /**
     * Adds a file to the includes array which will be added to the head
     * of the page. This method will assume the script is in the current "script_name"
     * directory. If $strict_path is true, then the path prefixing will not be added.
     * @param string $file_name : Name of script file to include
     * @param boolean $strict_path : If true, assume the file_name is the full path to
     *                               the script file.
     * @param boolean $prepend : forces the scripts to the front of the head_script array
     */
    public function addInclude($file_name, $strict_path=false, $prepend=false)
    {
        if (!$strict_path) {
            $file_name =  PHPWS_SOURCE_HTTP . 'javascript/' . $this->script_name . '/' . $file_name;
        }

        if (!$this->currentlyInHead($file_name)) {
            if ($prepend && !empty($this->includes)) {
                array_unshift($this->includes, $this->wrapInclude($file_name));
            } else {
                $this->includes[] = $this->wrapInclude($file_name);
            }
        }
    }

    /**
     * Indicates whether the current script is already in the head queue. This
     * prevents repeats in the head section of the page. The sample can be
     * an include or a full script. If true, then the script has already been
     * added and should not be used again. If false, then the script has
     * not yet been used.
     * @param string $sample : Script to check against
     * @return boolean : True if already used
     */
    private function currentlyInHead($sample)
    {
        $key = md5($sample);

        $result = in_array($key, self::$current_heads);
        if ($result) {
            return true;
        } else {
            self::$current_heads[] = $key;
            return false;
        }
    }

    /**
     * Prepares text for display in script. Put slashes on single quotes and
     * changes double quotes to &quot;
     * @param $text
     */
    public function quote($text)
    {
        $text = str_replace("'", "\'", $text);
        return str_replace('"', '&quot;', $text);
    }

    /**
     * Reduces the script using jsmin class
     */
    public function jsmin($script)
    {
        return JSMin::minify($script);
    }

    public function __toString()
    {
        return $this->getBodyScript();
    }


    public function demo()
    {
        $this->loadDemo();
        if (empty($this->demo_code)) {
            $demo_code = dgettext('core', 'No demo code available');
        } else {
            $demo_code = $this->getDemoCode();
        }

        $includes = $this->getIncludes();
        $head_script = $this->getHeadScript();
        $body_script = $this->getBodyScript();
        $example_code = $this->getDemoExample();

        $title = dgettext('core', 'Javascript demonstration:') . " $this->script_name";
        echo <<<EOF
<html>
    <head>
        <style type="text/css">
            body {
            font-family : monospace;
            font-size : 12px;
            }
            fieldset {
            border : 1px solid gray;
            background-color : white;
            }

            fieldset legend {
            padding : 3px;
            border-left : 1px solid gray;
            border-top : 1px solid gray;
            border-right : 1px solid gray;
            margin-bottom : 20px;
            }

            pre {
            margin : 0px;
            }
        </style>
        <title>$title</title>
        $includes
        $head_script
    </head>
    <body>
    $demo_code
    $example_code
    $body_script
    </body>
</html>
EOF;
    }

    /**
     * Displays parameters in javascript format
     * @param unknown_type $label
     * @param array $parameters
     */
    public static function displayParams(array $parameters) {

        foreach ($parameters as $key=>$value) {
            switch (gettype($value)) {
                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'string':
                    $value = "'$value'";
                    break;

                case 'array':
                    //@TODO this needs testing
                    $value = Javascript::displayParams($key, $value);
                    break;
            }
            $param[] = "$key: $value";
        }

        return implode(",\n", $param);
    }

}
?>