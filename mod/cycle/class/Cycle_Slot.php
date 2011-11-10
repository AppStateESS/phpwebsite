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
    public $feature_x = 10;
    public $feature_y = 10;
    public $f_width = 200;
    public $f_height = 200;
    public $destination_url;
    private $new_slot = true;
    public $errors;

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
        $this->destination_url = PHPWS_Text::checkLink($dest_url);

        if (!PHPWS_Text::isValidInput($this->destination_url, 'url')) {
            throw new Exception('Invalid destination url');
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

    public function setFWidth($width)
    {
        $this->f_width = (int) $width;
    }

    public function setFHeight($height)
    {
        $this->f_height = (int) $height;
    }

    public function post()
    {
        if (empty($_POST['destination_url'])) {
            $this->destination_url = null;
        } else {
            try {
                $this->setDestinationUrl($_POST['destination_url']);
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        $this->setFeatureText($_POST['feature_text']);
        $this->setFeatureX($_POST['feature_x']);
        $this->setFeatureY($_POST['feature_y']);
        $this->setThumbnailText($_POST['thumbnail_text']);
        $this->setFWidth($_POST['f_width']);
        $this->setFHeight($_POST['f_height']);

        if (isset($_POST['show_thumbnails'])) {
            PHPWS_Settings::set('cycle', 'show_thumbnails', 1);
        } else {
            PHPWS_Settings::set('cycle', 'show_thumbnails', 0);
        }

        if (!empty($_POST['bg_width']) && !empty($_POST['bg_height'])) {
            $bg_width = (int) $_POST['bg_width'];
            $bg_height = (int) $_POST['bg_height'];
            if ($bg_width < 150 || $bg_height < 100) {
                $this->errors[] = 'Background dimensions are too small';
            } else {
                PHPWS_Settings::set('cycle', 'bg_width', $bg_width);
                PHPWS_Settings::set('cycle', 'bg_height', $bg_height);
            }
        }

        if (!empty($_POST['tn_width']) && !empty($_POST['tn_height'])) {
            $tn_width = (int) $_POST['tn_width'];
            $tn_height = (int) $_POST['tn_height'];
            if ($tn_width < 10 || $tn_height < 10) {
                $this->errors[] = 'Thumbnail dimensions are too small';
            } else {
                PHPWS_Settings::set('cycle', 'tn_width', $tn_width);
                PHPWS_Settings::set('cycle', 'tn_height', $tn_height);
            }
        }

        PHPWS_Settings::save('cycle');


        $cycle_thumb_width = PHPWS_Settings::get('cycle', 'tn_width');
        $cycle_thumb_height = PHPWS_Settings::get('cycle', 'tn_height');

        $cycle_picture_width = PHPWS_Settings::get('cycle', 'bg_width');
        $cycle_picture_height = PHPWS_Settings::get('cycle', 'bg_height');

        if (empty($_FILES['background_image']['name']) && empty($this->background_path)) {
            $this->errors[] = 'Missing background image';
            return;
        }
        $directory = 'images/cycle/';

        if (!empty($_FILES['background_image']['name'])) {
            $bg_upload = & $_FILES['background_image'];

            if (!in_array($bg_upload['type'], array('image/png', 'image/jpg', 'image/jpeg'))) {
                $this->errors[] = 'Only images may be uploaded.';
                return;
            }

            $ext = PHPWS_File::getFileExtension($bg_upload['name']);

            $filename = 'bg' . $this->slot_order . '.' . $ext;

            if (!$this->scaleImage($bg_upload['tmp_name'], $directory . $filename)) {
                throw new Exception('Failed to upload image.');
            }

            $this->background_path = $directory . $filename;
        }

        if (!empty($_FILES['thumbnail_image']['name'])) {
            $ti_upload = & $_FILES['thumbnail_image'];

            if (!in_array($ti_upload['type'], array('image/png', 'image/jpg', 'image/jpeg'))) {
                $this->errors[] = 'Only images may be uploaded.';
            }

            $ext = PHPWS_File::getFileExtension($ti_upload['name']);

            $filename = 'tn' . $this->slot_order . '.' . $ext;
            if (!PHPWS_File::scaleImage($ti_upload['tmp_name'], $directory . $filename, $cycle_thumb_width, $cycle_thumb_height)) {
                throw new Exception('Failed to upload image.');
            }

            $this->thumbnail_path = $directory . $filename;
        } elseif (empty($this->thumbnail_path)) {
            $ext = PHPWS_File::getFileExtension($this->background_path);
            $filename = 'tn' . $this->slot_order . '.' . $ext;

            $bg_file = str_replace('images/cycle/', '', $this->background_path);

            if (!PHPWS_File::scaleImage($this->background_path, $directory . $filename, $cycle_thumb_width, $cycle_thumb_height)) {
                throw new Exception('Thumbnail scale failure.');
            }

            $this->thumbnail_path = $directory . $filename;
        }
    }

    public function scaleImage($source_dir, $dest_dir)
    {
        $cycle_thumb_width = PHPWS_Settings::get('cycle', 'tn_width');
        $cycle_thumb_height = PHPWS_Settings::get('cycle', 'tn_height');

        $cycle_picture_width = PHPWS_Settings::get('cycle', 'bg_width');
        $cycle_picture_height = PHPWS_Settings::get('cycle', 'bg_height');

        $size = getimagesize($source_dir);
        if (empty($size)) {
            return false;
        }


        $width = & $size[0];
        $height = & $size[1];
        $file_type = & $size['mime'];

        $wdiff = $width / $cycle_picture_width;
        $hdiff = $height / $cycle_picture_height;

        if ($wdiff < $hdiff) {
            $diff = $wdiff;
            $new_width = $cycle_picture_width;
            $new_height = floor($height / $diff);
        } else {
            $diff = $hdiff;
            $new_height = $cycle_picture_height;
            $new_width = floor($width / $diff);
        }

        $source_image = PHPWS_File::_imageCopy($source_dir, $file_type);
        $resampled_image = PHPWS_File::_resampleImage($new_width, $new_height);

        imagecopyresampled($resampled_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        imagedestroy($source_image);


        $result = PHPWS_File::_writeImageCopy($resampled_image, $dest_dir, $file_type);

        if (!$result) {
            imagedestroy($resampled_image);
            return false;
        }

        chmod($dest_dir, 0644);
        imagedestroy($resampled_image);

        return PHPWS_File::cropImage($dest_dir, $dest_dir, $cycle_picture_width, $cycle_picture_height);
    }

    public function save()
    {
        $db = new PHPWS_DB('cycle_slots');
        $db->addWhere('slot_order', $this->slot_order);
        $db->delete();
        $vars = get_object_vars($this);
        unset($vars['new_slot']);
        unset($vars['error']);

        $db->addValue($vars);
        return $db->insert();
    }

}

?>
