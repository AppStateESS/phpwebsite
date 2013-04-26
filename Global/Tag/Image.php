<?php
namespace Tag;
/**
 * Helps in the creation of an img tag.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Image extends \Tag {

    /**
     * Complete path of image file
     * @var string
     */
    protected $src = null;

    /**
     * Pixel width of image. Format: 123px
     * @var string
     */
    protected $width = null;

    /**
     * Pixel height of image. Format: 123px
     * @var string
     */
    protected $height = null;

    /**
     * Alternate text description of image
     * @var string
     */
    protected $alt = null;

    /**
     * Image title
     * @var string
     */
    protected $title = null;

    /**
     * This is a closed tag.
     * @var boolean
     */
    protected $open = false;

    /**
     * Physical path to image
     */
    private $directory;

    private $relative_path;

    /**
     * Creates a image object.
     * @param string $src Path to the image
     */
    public function __construct($directory, $src = null)
    {
        # @todo check this, was using an old tag format for construction
        parent::__construct('img');
        $this->setDirectory($directory);
        if (empty($src)) {
            $this->loadSrc();
        } else {
            $this->setSrc($src);
        }
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setDirectory($directory)
    {
        $this->directory = new \Variable\File($directory);
    }

    public function getSrc()
    {
        if (empty($this->src)) {
            $this->loadSrc();
        }
        return $this->src;
    }

    /**
     * Sets the src variable: the path to the image. The path is validated and
     * an error is thrown if formatted incorrectly
     * @param <type> $src
     */
    public function setSrc($src)
    {
        if (preg_match('/[^\w\.\/\s:\-~]/', $src)) {
            throw new \Exception(t('Improperly formatted image src'));
        }
        $this->src = $src;
        if (empty($this->alt)) {
            $path = explode('/', $this->src);
            $this->alt = end($path);
        }
    }


    private function loadRelativePath()
    {
        $this->relative_path = str_replace(realpath(null) . '/', '', $this->directory);
    }

    public function getRelativePath()
    {
        if (empty($this->relative_path)) {
            $this->loadRelativePath();
        }
        return $this->relative_path;
    }
    /**
     * Creates a src url path based on the current directory.
     */
    private function loadSrc()
    {
        $this->loadRelativePath();
        $this->setSrc(\Server::getSiteUrl() . $this->getRelativePath());
    }

    /**
     * Returns the img tag string
     * @return string
     */
    public function __toString()
    {
        if (!$this->width || !$this->height) {
            $this->loadDimensions();
        }

        return parent::__toString();
    }

    /**
     * An alternative to __toString, view allows the passing of pixel width and
     * height. If either is left blank, the alternate value is set to match
     * the aspect ratio of the changed value.
     * @param integer $width
     * @param integer $height
     * @return string
     */
    public function view($width = null, $height = null)
    {
        $this->loadDimensions();
        $width = intval($width);
        $height = intval($height);
        if ($width || $height) {
            $bwidth = $this->width;
            $bheight = $this->height;
            if ($width) {
                $this->width = intval($width);
                if (!$height) {
                    $ratio = $this->width / $bwidth;
                    $this->height = floor($this->height * $ratio);
                }
            }

            if ($height) {
                $this->height = intval($height);
                if (!$width) {
                    $ratio = $this->height / $bheight;
                    $this->width = floor($this->width * $ratio);
                }
            }
            $content = $this->__toString();
            $this->width = $bwidth;
            $this->height = $bheight;
            return $content;
        } else {
            return $this->__toString();
        }
    }

    /**
     * The width of the image.
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = intval($width);
    }

    /**
     * The height of the image.
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = intval($height);
    }

    /**
     * Loads the src image dimensions. Returns false if it failed.
     * @param boolean $force If true, reset dimensions
     * @return boolean
     */
    public function loadDimensions($force = false)
    {
        if (!$force && $this->width && $this->height) {
            return true;
        }
        if (empty($this->src)) {
            trigger_error(t('Src variable is empty'));
            return false;
        }
        $dimen = getimagesize($this->directory);
        if (!is_array($dimen)) {
            trigger_error(sprintf(t('%s not found'), $this->src));
            $this->src = 'Image/Icon/not_found.gif';
            return false;
        }

        $this->setWidth($dimen[0]);
        $this->setHeight($dimen[1]);
        return true;
    }

    /**
     * Sets the alternate text parameter for the image.
     * @param string $alt
     */
    public function setAlt($alt)
    {
        $this->alt = htmlentities($alt);
    }

}

?>