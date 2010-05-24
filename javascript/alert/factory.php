<?php
/**
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

class javascript_alert extends Javascript {
    protected $use_jquery = true;
    private $label = null;
    private $content = null;

    public function loadDemo()
    {
        $demo_code = <<<EOF
\$js = new Javascript('alert');
\$js->setLabel('Click on me');
\$js->setContent('Hello world!');
EOF;
        $this->setDemoCode($demo_code);
        $this->setLabel('Click on me');
        $this->setContent('Hello world!');
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setContent($content)
    {
        $this->content = $this->quote($content);
    }

    public function loadScript()
    {
        $head_script = <<<EOF
function notice(alert_text){if (alert_text == '') {return;}alert(alert_text);}
EOF;
        $this->addHeadScript($head_script, true);

        $body_script = <<<EOF
<a href="#" onclick="javascript:notice('{$this->content}'); return false">{$this->label}</a>
EOF;
        $this->setBodyScript($body_script);
    }
}

?>