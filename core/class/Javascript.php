<?php
/**
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

abstract class Javascript {
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

    protected $body_script = null;

    protected $demo_code = null;

    protected $example = null;

    /**
     * An array of scripts to include in the head.
     *
     * @var array
     */
    protected $includes = null;

    abstract public function loadDemo();


    public function setBodyScript($body_script)
    {
        $this->body_script = $body_script;
    }

    public function getHeadScript()
    {
        if ($this->use_jquery) {
            $this->loadJQuery();
        }
        if (empty($this->head_script)) {
            return null;
        }

        return implode("\n", $this->head_script);
    }

    public function getBodyScript()
    {
        return $this->body_script;
    }

    public static function factory($script_name)
    {
        static $script_list = null;

        if (!isset($script_list[$script_name])) {
            $js_path = PHPWS_SOURCE_DIR . 'javascript/' . $script_name . '/factory.php';
            if (!is_file($js_path)) {
                throw new PEAR_Exception(dgettext('core', 'Could not find javascript factory file.'));
            }
            require_once $js_path;
        }

        $factory_class_name = 'javascript_' . $script_name;

        $js = new $factory_class_name;
        $js->script_name = $script_name;
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
        static $jquery_loaded = false;

        if (!$jquery_loaded) {
            $this->addHeadInclude(PHPWS_SOURCE_HTTP . 'javascript/jquery/jquery.js', true, true);
            $jquery_loaded = true;
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
    public function addHeadInclude($file_name, $strict_path=false, $prepend=false)
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

    public function getIncludes()
    {
        if (!empty($this->includes)) {
            return implode("\n", $this->includes);
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
        static $current_heads = array();

        $key = md5($sample);

        $result = in_array($key, $current_heads);
        if ($result) {
            return true;
        } else {
            $current_heads[] = $key;
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
     * Reduces the script to one line
     */
    public function shrink($script)
    {
        $script = str_replace("\n", '', $script);
        $script = preg_replace('/\s+/', ' ', $script);
        return $script;
    }

    public function __toString()
    {
        return $this->getBodyScript();
    }


    public function demo()
    {
        $this->loadDemo();
        if ($this->use_jquery) {
            $this->loadJQuery();
        }
        if (empty($this->demo_code)) {
            $demo_code = dgettext('core', 'No demo code available');
        } else {
            $demo_code = $this->getDemoCode();
        }
        $includes = $this->getIncludes();
        $head_script = $this->getHeadScript();
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
    </body>
</html>
EOF;
    }

}
?>