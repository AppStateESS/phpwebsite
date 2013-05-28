<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Template {

    private $file;
    private $variables;

    public function __construct($file, $variables = null)
    {
        $this->setFile($file);
        if (!empty($variables)) {
            $this->addVariables($variables);
        }
    }

    public function addVariables(array $variables, $encode = true)
    {
        foreach ($variables as $key => $val) {
            $this->add($key, $val, $encode);
        }
    }

    public function add($key, $value, $encode = true)
    {
        if ($encode) {
            $this->variables[$key] = $this->encode($value);
        } else {
            $this->variables[$key] = $value;
        }
    }

    public function setFile($file)
    {
        if (!is_file($file)) {
            throw new \Exception(t('Template file not found: %s', $file));
        }
        $this->file = $file;
    }

    private function encode($content)
    {
        if (is_array($content)) {
            foreach ($content as $k => $v) {
                $content[$k] = $this->encode($v);
            }
        } else {
            $content = htmlentities($content, ENT_HTML5 | ENT_QUOTES);
        }
        return $content;
    }

    public function __toString()
    {
        extract($this->variables);
        ob_start();
        include $this->file;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

}

?>
