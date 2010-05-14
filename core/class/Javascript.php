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

    /**
     * An array of scripts to include in the head.
     *
     * @var array
     */
    protected $script_includes = null;

    abstract public function loadDemo();
    
    /**
     * This method is called just prior to producing the head and body
     * of the script
     */
    abstract public function loadScript();

    public function addHeadScript($script, $wrap=false)
    {
        if (!$this->currentlyInHead($script)) {
            if ($wrap) {
                $this->head_script[] = $this->wrapScript($script);
            } else {
                $this->head_script[] = $script;
            }
        }
    }

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


    protected function setDemoCode($code)
    {
        $this->demo_code = $code;
    }

    protected function getDemoCode()
    {
        return '<fieldset><legend>' . $this->script_name . '</legend><pre>' . $this->demo_code . '</pre></fieldset>';
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
            $this->addHeadInclude(PHPWS_SOURCE_HTTP . 'javascript/jquery/jquery.js', true);
            $jquery_loaded = true;
        }
    }

    /**
     * Adds a file to the script_includes array which will be added to the head
     * of the page. This method will assume the script is in the current "script_name"
     * directory. If $strict_path is true, then the path prefixing will not be added.
     * @param string $file_name : Name of script file to include
     * @param boolean $strict_path : If true, assume the file_name is the full path to
     *                               the script file.
     */
    public function addHeadInclude($file_name, $strict_path=false)
    {
        if (!$strict_path) {
            $file_name =  PHPWS_SOURCE_HTTP . 'javascript/' . $this->script_name . '/' . $file_name;
        }

        if (!$this->currentlyInHead($file_name)) {
            $this->head_script[] = $this->wrapInclude($file_name);
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

    public function demo()
    {
        $this->loadDemo();
        if (empty($this->demo_code)) {
            $demo_code = dgettext('core', 'No demo code available');
        } else {
            $demo_code = $this->getDemoCode();
        }
    
        $this->loadScript();
        
        $head_script = $this->getHeadScript();
        $body_script = $this->getBodyScript();

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
        $head_script
    </head>
    <body>
    $demo_code
    $body_script
    </body>
</html>
EOF;
    }

}
?>