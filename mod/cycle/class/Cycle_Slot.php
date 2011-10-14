<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class Cycle_Slot {

    public $slot_order = 1;
    public $thumbnail_path;
    public $thumbnail_text;
    public $background_path;
    public $feature_text;
    public $feature_x = 0;
    public $feature_y = 0;
    public $destination_url;
    private $new_slot = true;

    public function __construct($slot_order=0)
    {
        if ($slot_order) {
            $db = new PHPWS_DB('cycle_slots');
            $db->addWhere('slot_order', (int) $slot_order);
            $this->new_slot = false;
            if (!$db->loadObject($this)) {
                $this->new_slot = true;
                $this->slot_order = (int) $slot_order;
            }
        }
    }

    public function isNew()
    {
        return $this->new_slot;
    }

    public function delete()
    {
        $db = new PHPWS_DB('cycle_slots');
        $db->addWhere('slot_order', $this->slot_order);
        $db->delete();
    }

    public function setDestinationUrl($dest_url)
    {
        $this->destination_url = $dest_url;
        if (!PHPWS_Text::isValidInput($this->destination_url, 'url')) {
            throw new Exception('Invalid destination url.');
        }
    }

    public function setFeatureText($text)
    {
        $text = trim($text);
        if (empty($text)) {
            $this->feature_text = null;
        } else {
            $this->feature_text = & $text;
        }
    }

    public function setFeatureX($x)
    {
        $this->feature_x = (int) $x;
    }

    public function setFeatureY($y)
    {
        $this->feature_y = (int) $y;
    }

    public function setThumbnailText($text)
    {
        $this->thumbnail_text = trim(strip_tags($text));
    }

    public function post()
    {
        $this->setDestinationUrl($_POST['destination_url']);
        $this->setFeatureText($_POST['feature_text']);
        $this->setFeatureX($_POST['feature_x']);
        $this->setFeatureY($_POST['feature_y']);
        $this->setThumbnailText($_POST['thumbnail_text']);

        if (empty($_FILES['background_image']['name']) && empty($this->background_path)) {
            throw new Exception('Missing background image');
        }
        $directory = 'images/cycle/';

        if (!empty($_FILES['background_image']['name'])) {
            $bg_upload = & $_FILES['background_image'];

            if (!in_array($bg_upload['type'], array('image/png', 'image/jpg', 'image/jpeg'))) {
                throw new Exception('Only images may be uploaded.');
            }

            $ext = PHPWS_File::getFileExtension($bg_upload['name']);

            $filename = 'bg' . $this->slot_order . '.' . $ext;
            if (!PHPWS_File::scaleImage($bg_upload['tmp_name'], $directory . $filename, 600, 250)) {
                throw new Exception('Failed to upload image.');
            }
            $this->background_path = $directory . $filename;
        }

        if (!empty($_FILES['thumbnail_image']['name'])) {
            $ti_upload = & $_FILES['thumbnail_image'];

            if (!in_array($ti_upload['type'], array('image/png', 'image/jpg', 'image/jpeg'))) {
                throw new Exception('Only images may be uploaded.');
            }

            $ext = PHPWS_File::getFileExtension($ti_upload['name']);

            $filename = 'tn' . $this->slot_order . '.' . $ext;
            if (!PHPWS_File::scaleImage($ti_upload['tmp_name'], $directory . $filename, 140, 60)) {
                throw new Exception('Failed to upload image.');
            }

            $this->thumbnail_path = $directory . $filename;
        } else {
            $ext = PHPWS_File::getFileExtension($this->background_path);
            $filename = 'tn' . $this->slot_order . '.' . $ext;

            $bg_file = str_replace('images/cycle/', '', $this->background_path);

            if (!PHPWS_File::scaleImage($this->background_path, $directory . $filename, 140, 60)) {
                exit('thumb fail');
            }

            $this->thumbnail_path = $directory . $filename;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('cycle_slots');
        $db->addWhere('slot_order', $this->slot_order);
        $db->delete();
        $vars = get_object_vars($this);
        unset($vars['new_slot']);

        $db->addValue($vars);
        return $db->insert();
    }

}

?>
