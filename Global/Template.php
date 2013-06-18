<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Template implements View {

    private $file;
    private $variables;
    private $encode = false;
    private $encode_type = ENT_QUOTES;
    private $content_type;

    /**
     * @param array|null $variables Values shown inside the template
     * @param string|null $file Direct path to template file
     * @param boolean|null $encode If true (default), encode the output
     * @param string|null $contentType The MIME-type of the rendered template, 
     * default is text/html
     */
    public function __construct(array $variables = null, $file = null, $encode = null, $contentType = 'text/html')
    {
        if (isset($file)) {
            $this->setFile($file);
        }

        if (isset($variables)) {
            $this->addVariables($variables);
        }

        if (isset($encode)) {
            $this->setEncode($encode);
        }

        $this->setContentType($contentType);
    }

    public function addVariables(array $variables)
    {
        foreach ($variables as $key => $val) {
            $this->add($key, $val);
        }
    }

    public function setEncodeType($type)
    {
        $this->encode_type = $type;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setEncode($encode)
    {
        $this->encode = (bool) $encode;
    }

    public function add($key, $value)
    {
        $this->variables[$key] = $value;
    }

    public function setModuleTemplate($module, $file)
    {
        $this->setFile(PHPWS_SOURCE_DIR . "mod/$module/templates/$file");
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
            $content = htmlentities($content, $this->encode_type, 'UTF-8');
        }
        return $content;
    }

    public function __toString()
    {
        $template_content_array = $this->encode ? $this->encode($this->variables) : $this->variables;
        extract($template_content_array);
        ob_start();
        include $this->file;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public function get()
    {
        return $this->__toString();
    }

    public function render()
    {
        return $this->__toString();
    }
}

?>
