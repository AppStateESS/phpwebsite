<?php

/**
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
class PageCache extends Data {

    private $title;
    private $html;
    private $css;

    public function __construct($title = null, $content = null)
    {
        $this->title = new \Variable\String($title, 'title');
        $this->html = new \Variable\String($content, 'content');
        $this->html->noLimit();
        $this->css = new \Variable\String(null, 'css');
        $this->css->noLimit();
        $this->javascript = new \Variable\String(null, 'javascript');
        $this->javascript->noLimit();
    }

    public function getCSS()
    {
        return $this->css->__toString();
    }

    public function getHtml()
    {
        return $this->html->__toString();
    }

    public function importContent($filename)
    {
        if (!is_file($filename)) {
            throw new \Exception(t('File not found'));
        }
        $this->setHtml(file_get_contents($filename));
    }

    public function importCSS($filename)
    {
        if (!is_file($filename)) {
            throw new \Exception(t('File not found'));
        }
        $this->setCss(file_get_contents($filename));
    }

    public function setCSS($css)
    {
        $this->css->set(compress($css, 'css'));
    }

    public function setHtml($html)
    {
        $this->html->set(compress($html, 'html'));
    }

}

?>
