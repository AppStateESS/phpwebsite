<?php
namespace View;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class HtmlView implements \View {

    private $content;

    public function __construct($content)
    {
        $this->setContent($content);
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function render()
    {
        return $this->getContent();
    }

    public function getContentType()
    {
        return 'text/html';
    }

}

?>
