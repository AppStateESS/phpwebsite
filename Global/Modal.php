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
     * If set, the pixel height of the modal
     * @var integer
     */
    private $height;

    /**
     *
     * @var array
     */
    private $button;

    /**
     * Pixel width of modal
     * @var integer
     */
    private $width_pixel;

    /**
     * Inline style width setting for modal box.
     * Percentage based.
     * @var integer
     */
    private $width_percentage;

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
     * Set pixel width of modal. Will not work with setWidthPercentage or size.
     * @param integer $width
     */
    public function setWidthPixel($width)
    {
        $width = (int) $width;
        if ($width < 50) {
            throw new \Exception('Pixel width is too small. Must be greater than 50 pixels');
        }
        $this->size = 0;
        $this->width_percentage = null;
        $this->width_pixel = $width;
    }

    /**
     * A percentage width for the modal. Overrules the size setting and pixel width
     * @param integer $width
     */
    public function setWidthPercentage($width)
    {
        $width = (int) $width;
        if ($width < 1 || $width > 100) {
            throw new \Exception('Wrong percentage value entered for modal width');
        }
        $this->size = 0;
        $this->width_pixel = null;
        $this->width_percentage = $width;
    }

    public function setHeight($height)
    {
        $height = (int) $height;
        if ($height) {
            $this->height = $height;
        }
    }

    /**
     * Adds the modal-lg class to the modal to make it wider.
     * Resets pixel and percentage width.
     */
    public function sizeLarge()
    {
        $this->width = null;
        $this->width_percentage = null;
        $this->size = 2;
    }

    /**
     * Adds the modal-sm class to the modal to make it narrow.
     * Resets pixel and percentage width.
     */
    public function sizeSmall()
    {
        $this->width = null;
        $this->width_percentage = null;
        $this->size = 1;
    }

    /**
     * Resets size, pixel and percentage width.
     */
    public function sizeDefault()
    {
        $this->width = null;
        $this->width_percentage = null;
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

        if (!empty($this->height)) {
            $tpl['height'] = 'height:' . $this->height . 'px;';
        } else {
            $tpl['height'] = null;
        }

        if (!empty($this->width_percentage)) {
            $tpl['width_percentage'] = 'width:' . $this->width_percentage . '%;';
            $tpl['width_pixel'] = null;
            $tpl['size'] = null;
        } elseif (!empty($this->width_pixel)) {
            $tpl['width_percentage'] = null;
            $tpl['width_pixel'] = 'width:' . $this->width_pixel . 'px;';
            $tpl['size'] = null;
        } else {
            $tpl['width_percentage'] = null;
            $tpl['width_pixel'] = null;
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
        }

        $template = new \Template($tpl,
                PHPWS_SOURCE_DIR . 'Global/Templates/Modal/modal.html');
        return $template->render();
    }

}

?>
