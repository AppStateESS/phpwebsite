<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
require_once QR_LIB_DIR . 'qrlib.php';

class QR
{
    /**
     * URL of the site getting a symbol
     * @var string
     */
    private $url;

    /**
     * md5 hash of url
     * @var string
     */
    private $tag;

    /**
     * Filename of image
     * @var string
     */
    private $file;
    private $error_correction = QR_ERROR_CORRECTION_LEVEL;
    private $size = 6;

    public function __construct($key_id, $size = null)
    {
        $this->key_id = (int) $key_id;
        if ($size) {
            $this->setSize($size);
        }
    }

    public function setSize($size)
    {
        $size = (int) $size;
        if ($size < 13 && $size > 2) {
            $this->size = $size;
        }
    }

    private function load()
    {
        if ($this->key_id) {
            $key = new Key($this->key_id);
        } else {
            $key = Key::getHomeKey();
        }

        $this->url = PHPWS_Core::getHomeHttp() . $key->url;
        $this->tag = md5($this->url);
        $this->file = QR_IMAGE_DIR . $this->tag . '_' . $this->size . '.png';
        $this->image = QR_IMAGE_HTTP . $this->tag . '_' . $this->size . '.png';
    }

    public function get()
    {
        $this->load();
        if (!is_file($this->file)) {
            QRcode::png($this->url, $this->file, $this->error_correction, $this->size, 2);
        }
        return '<img src="' . $this->image . '" />';
    }

}

?>
