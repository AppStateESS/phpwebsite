<?php

/**
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
class PageCache extends Data
{

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
        $this->css->set(self::compress($css, 'css'));
    }

    public function setHtml($html)
    {
        $this->html->set(self::compress($html, 'html'));
    }

    /**
     * Removes spaces from css and html content.
     *
     * @param string $text Text to be compressed
     * @param string $type Either 'css' or 'html'
     * @return string
     */
    public static function compress($text, $type = null)
    {
        // remove comments
        switch ($type) {
            case 'css':
                $text = preg_replace('@/\*.*\*/@Um', ' ', $text);
                break;
            case 'html':
                $text = preg_replace('/<\!--.*-->/U', ' ', $text);
                break;
        }
        $text = str_replace(array(chr(9), chr(10), chr(11), chr(13)), ' ', $text);
        // faster than preg_replace('/\s{2,}')
        while (strstr($text, '  ')) {
            $text = str_replace('  ', ' ', $text);
        }

        if ($type == 'css') {
            $text = str_replace('; ', ';', $text);
            $text = str_replace(' ;', ';', $text);
            $text = str_replace('} ', '}', $text);
            $text = str_replace(' }', '}', $text);
            $text = str_replace('{ ', '{', $text);
            $text = str_replace(' {', '{', $text);
            $text = str_replace(': ', ':', $text);
            $text = str_replace(' :', ':', $text);
        } elseif ($type == 'html') {
            $text = str_replace('> <', '><', $text);
        }

        return $text;
    }

}