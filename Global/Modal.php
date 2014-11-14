<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Modal {

    /**
     * DOM id identifying the modal
     * @var string
     */
    private $id;

    /**
     * Body content contained within the modal
     * @var string
     */
    private $content;

    /**
     * Title used in header of modal
     * @var string
     */
    private $title;

    /**
     * Size of modal
     * 0 : default
     * 1 : small
     * 2 : large
     * @var integer
     */
    private $size = 1;

    /**
     *
     * @var array
     */
    private $button;

    /**
     * Indicates a modal was created. Used for a simple error check prior to
     * assumption of existence.
     * @var boolean
     */
    public static $modal_started = false;

    public function __construct($id, $content = null, $title = null)
    {
        self::$modal_started = true;
        $this->id = new \Variable\Attribute($id, 'id');
        $this->title = new \Variable\TextOnly($title, 'title');
        $this->content = new \Variable\String($content, 'content');
    }

    public function setContent($content)
    {
        $this->content->set($content);
    }

    public function setTitle($title)
    {
        $this->title->set($title);
    }

    public function addButton($button)
    {
        $this->button[] = $button;
    }

    /**
     * Adds the modal-lg class to the modal to make it wider.
     */
    public function sizeLarge()
    {
        $this->size = 2;
    }

    /**
     * Adds the modal-sm class to the modal to make it narrow.
     */
    public function sizeSmall()
    {
        $this->size = 1;
    }

    public function sizeDefault()
    {
        $this->size = 0;
    }

    public function __toString()
    {
        $tpl['id'] = $this->id;
        $tpl['content'] = $this->content;
        if (!empty($this->title)) {
            $tpl['title'] = $this->title;
        }

        if ($this->button) {
            $tpl['button'] = implode("\n", $this->button);
        }

        switch ($this->size) {
            case 0:
                $tpl['size'] = null;
                break;

            case 1:
                $tpl['size'] = ' modal-sm';
                break;

            case 2:
                $tpl['size'] = ' modal-lg';
                break;
        }

        $template = new \Template($tpl,
                PHPWS_SOURCE_DIR . 'Global/Templates/Modal/modal.html');
        return $template->render();
    }

}

?>
