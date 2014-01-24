<?php
namespace Properties;
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

\PHPWS_Core::requireConfig('properties', 'defines.php');

class Photo {
    public $id = 0;
    public $cid = 0;
    public $pid = 0;
    public $width = 0;
    public $height = 0;
    public $path;
    public $title;
    public $main_pic = false;

    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }
        $db = new \PHPWS_DB('prop_photo');
        $db->addWhere('id', (int)$id);
        $db->loadObject($this);
    }

    public function setTitle($title)
    {
        $title = strip_tags(trim($title));
        if (empty($title)) {
            throw new \Exception('Photo title may not be empty.');
        }
        $this->title = $title;
    }

    public function uploadNew($use_icon=true)
    {
        if ($use_icon) {
            $label = \Icon::show('image');
        } else {
            $label = 'Add photo';
        }

        $content = <<<EOF
<a style="cursor : pointer" id="{$this->pid}" class="photo-upload">$label</a>
EOF;
        return $content;
    }


    public function setContactId($cid)
    {
        $this->cid = (int)$cid;
    }

    public function setPropertyId($pid)
    {
        $this->pid = (int)$pid;
    }


    public function post()
    {
        $this->setTitle($_POST['title']);
        $this->setPropertyId($_POST['pid']);

        $photo = & $_FILES['photo'];
        if (!$this->pid) {
            throw new \Exception('Photo missing property id');
        }

        \PHPWS_Core::initModClass('properties', 'Property.php');
        $property = new Property($this->pid);
        $this->setContactId($property->contact_id);

        $destination_directory = 'images/properties/c' . $this->cid . '/';
        if (!is_dir($destination_directory)) {
            if (!@mkdir($destination_directory)) {
                throw new \Exception('Could not create photo directory');
            }
        }
        $filename = time() . '.' . \PHPWS_File::getFileExtension($photo['name']);

        $path = $destination_directory . $filename;

        if (!empty($photo['error'])) {
            switch ($photo['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new \Exception('Image file size is too large.');

                case UPLOAD_ERR_NO_FILE:
                    throw new \Exception('No file upload');

                case UPLOAD_ERR_NO_TMP_DIR:
                    \PHPWS_Core::log('Temporary file directory is not writable', 'error.log', _('Error'));
                    throw new \Exception('File could not be written');
            }
        }
        list($this->width, $this->height, $image_type, $image_attr) = getimagesize($photo['tmp_name']);

        if (!in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            throw new \Exception('Unacceptable image file type.');
        }
        if (\PHPWS_File::scaleImage($photo['tmp_name'], $path, PROPERTIES_MAX_WIDTH, PROPERTIES_MAX_HEIGHT)) {
            $this->path = $path;
            $this->save();
            $tn = Photo::thumbnailPath($path);

            $this->resize($path, $tn, PROP_THUMBNAIL_WIDTH, PROP_THUMBNAIL_HEIGHT);
        } else {
            throw new \Exception('Could not save image');
        }
    }

    public function form()
    {
        $form = new \PHPWS_Form('photo-form');
        $form->addHidden('module', 'properties');
        if (isset($_SESSION['Contact_User'])) {
            $form->addHidden('cop', 'post_photo');
            $form->addHidden('k', $_SESSION['Contact_User']->getKey());
        } else {
            $form->addHidden('aop', 'post_photo');
        }
        if (isset($_GET['v'])) {
            $form->addHidden('v', 1);
        }
        $form->addHidden('pid', $_GET['pid']);
        $form->addText('title');
        $form->setLabel('title', 'Title');
        $form->addFile('photo');
        $form->addSubmit('submit', 'Upload photo');
        $tpl = $form->getTemplate();

        $tpl['WIDTH'] = PROP_THUMBNAIL_WIDTH;
        $tpl['HEIGHT'] = PROP_THUMBNAIL_HEIGHT;
        $tpl['AUTH'] = \Current_User::getAuthKey();
        $tpl['THUMBNAILS'] = Photo::getThumbs($_GET['pid']);

        if (isset($_SESSION['Contact_User'])) {
            $tpl['CMD'] = 'k=' . $_SESSION['Contact_User']->getKey() . '&cop';
        } else {
            $tpl['CMD'] = 'aop';
        }

        return \PHPWS_Template::process($tpl, 'properties', 'photo_form.tpl');
    }


    public static function thumbnailPath($path)
    {
        return preg_replace('@(images/properties/c\d+/\d+).(\w+)@', '\\1_tn.\\2', $path);
    }


    public static function getThumbs($pid)
    {
        $db = new \PHPWS_DB('prop_photo');
        $db->addWhere('pid', $pid);
        $db->addColumn('id');
        $db->addColumn('path');
        $db->addColumn('main_pic');
        $photos = $db->select();
        if (!empty($photos)) {
            $delete_icon = '<img src="' . PHPWS_SOURCE_HTTP . 'mod/properties/img/bigx.png" />';
            foreach ($photos as $p) {
                extract($p);
                $path = Photo::thumbnailPath($path);
                if ($main_pic) {
                    $default = '<div id="default">Default image</div>';
                } else {
                    $default = null;
                }
                $thumbnails[] = '<div id="p' . $id . '" class="photo" style="background-image : url(' . $path . ')">
                <a class="delete-photo" id="' . $id . '">' . $delete_icon . '</a>' . $default . '</div>';
            }
            return implode('', $thumbnails);
        }
    }

    public function save()
    {
        $db = new \PHPWS_DB('prop_photo');
        $db->addWhere('pid', $this->pid);
        $result = $db->select();
        if (empty($result)) {
            $this->main_pic = 1;
        }
        $db->reset();
        $result = $db->saveObject($this);
        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Failed to save photo');
        }

        return true;
    }

    public function resize($orig, $dst, $max_width, $max_height)
    {
        if (!$this->width || !$this->height) {
            return false;
        }

        $src_proportion = $this->width / $this->height;
        $new_width = $this->width;
        $new_height = $this->height;

        if ($max_width > $this->width) {
            $crop_width = $new_width;
            $crop_height = $max_height;
        } elseif($max_height > $this->height) {
            $crop_width = $max_width;
            $crop_height = $new_height;
        } elseif($max_width <= $max_height) {
            $new_height = $max_height;
            $new_width  = round($new_height * $src_proportion);
            $crop_width = $max_width;
            $crop_height = $new_height;
            if ($crop_width > $new_width) {
                $new_width = $max_width;
                $new_height = round($new_width / $src_proportion);
            }
        } else {
            $new_width   = $max_width;
            $new_height  = round($new_width * $src_proportion);
            $crop_width = $new_width;
            $crop_height = $max_height;
        }

        \PHPWS_File::scaleImage($orig, $dst, $new_width, $new_height);

        return \PHPWS_File::cropImage($dst, $dst, $crop_width, $crop_height);
    }

    public function delete()
    {
        $db = new \PHPWS_DB('prop_photo');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            return;
        }
        @unlink($this->path);
        @unlink(Photo::thumbnailPath($this->path));

        // if deleted pick is main pic, add new main
        if ($this->main_pic) {
            $db->reset();
            $db->addWhere('pid', $this->pid);
            $db->addValue('main_pic', 1);
            $db->setLimit(1);
            $db->update();
        }
    }

    public function makeMain()
    {
        $db = new \PHPWS_DB('prop_photo');
        $db->addWhere('pid', $this->pid);
        $db->addValue('main_pic', 0);
        $db->update();
        $db->reset();
        $db->addWhere('id', $this->id);
        $db->addValue('main_pic', 1);
        $db->update();
    }
}
?>