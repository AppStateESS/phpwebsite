<?php

/**
 * Image class that builds off the File_Command class
 * Assists with image files
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::requireInc('filecabinet', 'defines.php');
PHPWS_Core::initModClass('filecabinet', 'File_Common.php');

if (!defined('FC_MIN_POPUP_SIZE')) {
    define('FC_MIN_POPUP_SIZE', 400);
}

class PHPWS_Image extends File_Common {
    public $width            = null;
    public $height           = null;
    public $alt              = null;
    public $url              = null;

    public $_classtype       = 'image';
    public $_max_width       = 0;
    public $_max_height      = 0;

    public function __construct($id=null)
    {
        $this->loadAllowedTypes();
        $this->setMaxWidth(PHPWS_Settings::get('filecabinet', 'max_image_dimension'));
        $this->setMaxHeight(PHPWS_Settings::get('filecabinet', 'max_image_dimension'));
        $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_image_size'));

        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->id = 0;
            $this->_errors[] = $result;
        } elseif (empty($result)) {
            $this->id = 0;
            $this->_errors[] = PHPWS_Error::get(FC_IMG_NOT_FOUND, 'filecabinet', 'PHPWS_Image');
        }
        $this->loadExtension();
    }

    public function init()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new PHPWS_DB('images');
        return $db->loadObject($this);
    }

    public function loadDimensions()
    {
        if (empty($this->file_directory) ||
            empty($this->file_name) ||
            !is_file($this->getPath())) {
            return false;
        }

        $dimen = getimagesize($this->getPath());
        if (!is_array($dimen)) {
            return false;
        }
        $this->width  = $dimen[0];
        $this->height = $dimen[1];
        if (empty($this->size)) {
            $this->loadFileSize();
        }
        return true;
    }

    public function allowImageType($type)
    {
        $image = new PHPWS_Image;
        return $image->allowType($type);
    }

    public function allowDimensions()
    {
        if ($this->allowWidth() && $this->allowHeight()) {
            return true;
        } else {
            return false;
        }
    }


    public function allowHeight($imageheight=null)
    {
        if (!isset($imageheight)) {
            $imageheight = &$this->height;
        }

        return ($imageheight <= $this->_max_height) ? TRUE : FALSE;
    }


    public function allowWidth($imagewidth=null)
    {
        if (!isset($imagewidth)) {
            $imagewidth = &$this->width;
        }

        return ($imagewidth <= $this->_max_width) ? TRUE : FALSE;
    }

    public function popupSize()
    {
        $padded_width = $this->width + 25;
        $padded_height = $this->height + 100;

        if (!empty($this->description)) {
            $padded_height += round( (strlen(strip_tags($this->description)) / ($this->width / 12)) * 12);
        }

        if ( $padded_width > FC_MAX_IMAGE_POPUP_WIDTH || $padded_height > FC_MAX_IMAGE_POPUP_HEIGHT ) {
            return array(FC_MAX_IMAGE_POPUP_WIDTH, FC_MAX_IMAGE_POPUP_HEIGHT);
        }

        $final_width = $final_height = 0;

        for ($lmt = FC_MIN_POPUP_SIZE; $lmt += 50; $lmt < 1300) {
            if (!$final_width && $padded_width < $lmt) {
                $final_width = $lmt;
            }

            if (!$final_height && $padded_height < $lmt ) {
                $final_height = $lmt;
            }

            if ($final_width && $final_height) {
                return array($final_width, $final_height);
            }
        }

        return array(FC_MAX_IMAGE_POPUP_WIDTH, FC_MAX_IMAGE_POPUP_HEIGHT);
    }

    public function getJSView($thumbnail=FALSE, $link_override=null)
    {
        if ($link_override) {
            $values['label'] = $link_override;
        } else {
            if ($thumbnail) {
                $values['label'] = $this->getThumbnail();
            } else {
                $values['label'] = sprintf('<img src="images/mod/filecabinet/viewmag+.png" title="%s" />',
                                           dgettext('filecabinet', 'View full image'));
            }
        }

        $values['address']     = $this->popupAddress();
        $values['width']       = FC_MAX_IMAGE_POPUP_WIDTH + 50;
        $values['height']      = FC_MAX_IMAGE_POPUP_HEIGHT + 100;
        $values['window_name'] = 'image_view';

        return Layout::getJavascript('open_window', $values);
    }

    public function popupAddress()
    {
        if (MOD_REWRITE_ENABLED) {
            return sprintf('filecabinet/mtype/image/id/%s', $this->id);
        } else {
            return sprintf('index.php?module=filecabinet&amp;mtype=image&amp;id=%s', $this->id);
        }

    }

    public function thumbnailDirectory()
    {
        return $this->file_directory . 'tn/';
    }

    public function thumbnailPath()
    {
        return $this->thumbnailDirectory() . $this->file_name;
    }

    public function tnFileName()
    {
        return $this->file_name;
    }

    public function captioned($id=null, $linked=true, $base=false)
    {
        if (empty($this->description)) {
            return $this->getTag(null, $linked);
        }

        $width = $this->width - 6;

        $tpl['IMAGE']   = $this->getTag($id, $linked, $base);
        $tpl['CAPTION'] = $this->getDescription();
        $tpl['WIDTH']   = $width . 'px';
        return PHPWS_Template::process($tpl, 'filecabinet', 'captioned_image.tpl');
    }

    public function getTag($id=null, $linked=true, $base=false)
    {
        $tag[] = '<img';
        if ($base) {
            $tag[] = 'src="' . PHPWS_HOME_HTTP . $this->getPath() . '"';
        } else {
            $tag[] = 'src="' . $this->getPath() . '"';
        }
        $tag[] = 'alt="'    . $this->getAlt(TRUE) . '"';
        $tag[] = 'title="'  . htmlspecialchars($this->title, ENT_QUOTES) . '"';
        $tag[] = 'width="'  . $this->width     . 'px"';
        $tag[] = 'height="' . $this->height    . 'px"';
        if ($id) {
            $tag[] = 'id="' . $id .'"';
        }
        $tag[] = '/>';

        $image_tag = implode(' ', $tag);

        if ($linked && !empty($this->url)) {
            if($this->url == 'folder') {
                $link =   $link = sprintf('index.php?module=filecabinet&amp;uop=view_folder&amp;folder_id=%s', $this->folder_id);
                $image_tag =  sprintf('<a href="%s" title="%s">%s</a>', $link, dgettext('filecabinet', 'View all images in folder'),
                                      $image_tag);
            } else {
                $image_tag = sprintf('<a href="%s">%s</a>', $this->url, $image_tag);
            }
        }

        return $image_tag;
    }

    public function getThumbnail($css_id=null, $linked=false)
    {
        if (empty($css_id)) {
            $css_id = $this->id;
        }
        $thumbpath = $this->thumbnailPath();
        if (!is_file($thumbpath)) {
            return dgettext('filecabinet', 'No image found');
        }

        $dimensions = getimagesize($thumbpath);

        if (PHPWS_Settings::get('filecabinet', 'force_thumbnail_dimensions')) {
            $max_size = PHPWS_Settings::get('filecabinet', 'max_thumbnail_size');
            $ratio = $dimensions[0] / $dimensions[1];
            if ($ratio == 1) {
                $dimensions = array($max_size, $max_size);
            } elseif ($ratio > 1) {
                $dimensions[0] = $max_size;
                $dimensions[1] = floor($max_size / $ratio);
            } else {
                $dimensions[0] = floor($max_size / $ratio);
                $dimensions[1] = $max_size;
            }
        }

        $image_tag = sprintf('<img src="%s" title="%s" id="image-thumbnail-%s" alt="%s" width="%s" height="%s" />',
                             $thumbpath,
                             htmlspecialchars($this->title, ENT_QUOTES), $css_id, $this->alt,
                             $dimensions[0], $dimensions[1]);

        if ($linked && !empty($this->url)) {
            if($this->url == 'folder') {
                $link =   $link = sprintf('index.php?module=filecabinet&amp;uop=view_folder&amp;folder_id=%s', $this->folder_id);
                $image_tag =  sprintf('<a href="%s" title="%s">%s</a>', $link, dgettext('filecabinet', 'View all images in folder'),
                                      $image_tag);
            } else {
                $image_tag = sprintf('<a href="%s">%s</a>', $this->url, $image_tag);
            }
        }
        return $image_tag;
    }

    public function loadAllowedTypes()
    {
        $this->_allowed_types = explode(',', PHPWS_Settings::get('filecabinet', 'image_files'));
    }


    public function resize($dst, $max_width, $max_height, $crop_to_fit=false)
    {
        if (!$this->width || !$this->height) {
            return false;
        }

        $src_proportion = $this->width / $this->height;
        $new_width = $this->width;
        $new_height = $this->height;

        if ($crop_to_fit) {
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
                if ($crop_to_fit && $crop_width > $new_width) {
                    $new_width = $max_width;
                    $new_height = round($new_width / $src_proportion);
                }
            } else {
                $new_width   = $max_width;
                $new_height  = round($new_width * $src_proportion);
                $crop_width = $new_width;
                $crop_height = $max_height;
            }

            PHPWS_File::scaleImage($this->getPath(), $dst, $new_width, $new_height);
            // testing purposes
            /*
            printf('<hr>w=%s h=%s<br>mw=%s mh=%s<br>nw=%s nh=%s<br>cw=%s ch=%s<hr>',
                   $this->width, $this->height, $max_width, $max_height,
                   $new_width, $new_height, $crop_width, $crop_height);
            */

            return PHPWS_File::cropImage($dst, $dst, $crop_width, $crop_height);
        } else {
            return PHPWS_File::scaleImage($this->getPath(), $dst, $max_width, $max_height);
        }
    }

    public function makeThumbnail()
    {
        $max_tn = PHPWS_Settings::get('filecabinet', 'max_thumbnail_size');
        if ($this->width <= $max_tn && $this->height <= $max_tn) {
            return @copy($this->getPath(), $this->thumbnailPath());
        } else {
            return $this->resize($this->thumbnailPath(), $max_tn, $max_tn, true);
        }
    }


    public function delete()
    {
        // deleteAssoc call occurs in commonDelete
        $result = $this->commonDelete();
        if (PEAR::isError($result)) {
            return $result;
        }

        $tn = $this->thumbnailPath();
        if (!@unlink($tn)) {
            PHPWS_Error::log(FC_COULD_NOT_DELETE, 'filecabinet', 'PHPWS_Image::delete', $path);
        }

        $path = $this->getResizePath();
        if ($path) {
            PHPWS_File::rmdir($path);
        }
        return true;
    }

    public function deleteAssoc()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('file_type', FC_IMAGE, '=', 'or', 1);
        $db->addWhere('file_type', FC_IMAGE_RESIZE, '=', 'or', 1);
        $db->addWhere('file_type', FC_IMAGE_CROP, '=', 'or', 1);
        $db->addWhere('file_id', $this->id);
        return $db->delete();
    }


    public function pinTags()
    {
        $tpl['TN'] = $this->getJSView(true);
        $tpl['TITLE'] = htmlspecialchars($this->title, ENT_QUOTES);
        return $tpl;
    }

    public function editLink($icon=false)
    {
        $vars['iop'] = 'upload_image_form';
        $vars['image_id'] = $this->id;
        $vars['folder_id'] = $this->folder_id;

        $jsvars['width'] = 550;
        $jsvars['height'] = 600 + PHPWS_Settings::get('filecabinet', 'max_thumbnail_size');
        $link = new PHPWS_Link(null, 'filecabinet', $vars);
        $link->setSecure();
        $link->setSalted();
        $jsvars['address'] = $link->getAddress();

        $jsvars['window_name'] = 'edit_link';

        if ($icon) {
            $jsvars['label'] =sprintf('<img src="images/mod/filecabinet/edit.png" title="%s" />', dgettext('filecabinet', 'Edit image'));
        } else {
            $jsvars['label'] = dgettext('filecabinet', 'Edit');
        }
        return javascript('open_window', $jsvars);
    }


    public function deleteLink($icon=false)
    {
        $vars['iop'] = 'delete_image';
        $vars['image_id'] = $this->id;
        $vars['folder_id'] = $this->folder_id;

        $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this image?');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', $vars, true);

        if ($icon) {
            $js['LINK'] = '<img src="images/mod/filecabinet/delete.png" />';
        } else {
            $js['LINK'] = dgettext('filecabinet', 'Delete');
        }
        return javascript('confirm', $js);
    }

    public function rowTags()
    {
        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $clip = sprintf('<img src="images/mod/filecabinet/clip.png" title="%s" />', dgettext('filecabinet', 'Clip image'));
            $links[] = PHPWS_Text::secureLink($clip, 'filecabinet',
                                              array('iop'      => 'clip_image',
                                                    'image_id' => $this->id));
            $links[] = $this->editLink(true);
            $links[] = $this->deleteLink(true);
        }

        if (isset($links)) {
            $tpl['ACTION'] = implode('', $links);
        }
        $tpl['SIZE'] = $this->getSize(TRUE);
        $tpl['FILE_NAME'] = $this->file_name;
        $tpl['THUMBNAIL'] = $this->getJSView(TRUE);
        $tpl['TITLE']     = htmlspecialchars($this->title, ENT_QUOTES);
        $tpl['DIMENSIONS'] = sprintf('%s x %s', $this->width, $this->height);

        return $tpl;
    }

    public function getManagerIcon($fmanager)
    {
        if ( ($fmanager->max_width < $this->width) || ($fmanager->max_height < $this->height) ) {
            return sprintf('<a href="#" onclick="slider(%s); return false">%s</a>',
                           $this->id, $this->getThumbnail());
        } else {
            $vars = $fmanager->linkInfo(false);
            $vars['fop']       = 'pick_file';
            $vars['file_type'] = FC_IMAGE;
            $vars['id']        = $this->id;
            $link = PHPWS_Text::linkAddress('filecabinet', $vars, true);
            return sprintf('<a href="%s">%s</a>', $link, $this->getThumbnail());
        }
    }

    public function managerTpl($fmanager)
    {
        if ($fmanager->file_assoc->file_type == FC_IMAGE &&
            $fmanager->file_assoc->file_id == $this->id) {
            $tpl['HIGHLIGHT'] = 'highlight';
        }

        $tpl['ID'] = $this->id;
        $tpl['TITLE'] = $this->getTitle(true);

        $tpl['INFO']  = sprintf('%s x %s - %s', $this->width, $this->height,
                                $this->getSize(true));

        if (is_file($this->getPath())) {
            $tpl['ICON']  = $this->getManagerIcon($fmanager);
            $links[] = $this->getJSView(false);
        } else {
            $tpl['ICON'] = dgettext('filecabinet', 'Image missing');
        }

        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $links[] = $this->editLink(true);
            $links[] = $this->deleteLink(true);
        }

        if (isset($links)) {
            $tpl['LINKS'] = implode(' ', $links);
        }

        $tpl['RESIZE'] = $this->resizeMenu($fmanager);

        return $tpl;
    }

    public function resizeMenu($fmanager)
    {
        $tpl['ID'] = $this->id;

        $tpl['MESSAGE'] = sprintf(dgettext('filecabinet', 'This image is larger than the %sx%s limit. What do you wish to do?'),
                                  $fmanager->max_width, $fmanager->max_height);
        $vars = $fmanager->linkInfo(false);
        $vars['fop'] = 'pick_file';
        $vars['mw'] = $fmanager->max_width;
        $vars['mh'] = $fmanager->max_height;
        $vars['id'] = $this->id;
        $vars['file_type'] = 1;

        if (!$fmanager->force_resize) {
            $choices[] = PHPWS_Text::secureLink(dgettext('filecabinet', 'Use original image'), 'filecabinet', $vars);
        }

        $vars['file_type'] = 7;
        $choices[] = PHPWS_Text::secureLink(dgettext('filecabinet', 'Resize image maintaining aspect'), 'filecabinet', $vars);

        $vars['file_type'] = 9;
        $choices[] = PHPWS_Text::secureLink(dgettext('filecabinet', 'Resize and crop excess'), 'filecabinet', $vars);

        $choices[] = sprintf('<a href="#" onclick="slider(%s); return false;">%s</a>', $this->id,
                            dgettext('filecabinet', 'Cancel'));

        $tpl['CHOICES'] = implode('</li><li>',$choices);
        return PHPWS_Template::process($tpl, 'filecabinet', 'file_manager/resize.tpl');
    }

    /**
     * Rotates an image
     */
    public function rotate($save=true, $degrees=0)
    {
        if (!$degrees) {
            $degrees = $this->_getDegrees();
        }

        if (!$degrees) {
            return true;
        }

        $tmp_file = $this->file_directory . mktime() . $this->file_name;

        if (PHPWS_File::rotateImage($this->getPath(), $tmp_file, $degrees)) {
            @copy($tmp_file, $this->getPath());
            $this->loadDimensions();
            $this->makeThumbnail();
            @unlink($tmp_file);

            if ($save) {
                return $this->save();
            }

            return true;
        } else {
            @unlink($tmp_file);
            return false;
        }

    }


    public function _getDegrees()
    {
        switch (@$_REQUEST['rotate']) {
        case '90cw':
            return 270;

        case '90ccw':
            return 90;

        case '180':
            return 180;

        default:
            return 0;
        }
    }


    public function save($no_dupes=true, $write=true, $thumbnail=true)
    {
        if (empty($this->file_directory)) {
            if ($this->folder_id) {
                $folder = new Folder($_POST['folder_id']);
                if ($folder->id) {
                    $this->setDirectory($folder->getFullDirectory());
                } else {
                    return PHPWS_Error::get(FC_MISSING_FOLDER, 'filecabinet', 'PHPWS_Image::save');
                }
            } else {
                return PHPWS_Error::get(FC_DIRECTORY_NOT_SET, 'filecabinet', 'PHPWS_Image::save');
            }
        }

        if (!$this->folder_id) {
            return PHPWS_Error::get(FC_MISSING_FOLDER, 'filecabinet', 'PHPWS_Image::save');
        }

        if (!is_writable($this->file_directory)) {
            return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Image::save', $this->file_directory);
        }


        if (empty($this->alt)) {
            if (empty($this->title)) {
                $this->loadTitleFromFilename();
            }
            $this->alt = $this->title;
        }

        if ($write) {
            $result = $this->write();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if ($thumbnail) {
            $this->makeThumbnail();
        }

        $db = new PHPWS_DB('images');

        if ((bool)$no_dupes && empty($this->id)) {
            $db->addWhere('file_name',  $this->file_name);
            $db->addWhere('folder_id', $this->folder_id);
            $db->addColumn('id');
            $result = $db->select('one');
            if (PEAR::isError($result)) {
                return $result;
            } elseif (isset($result) && is_numeric($result)) {
                $this->id = $result;
                return TRUE;
            }

            $db->reset();
        }

        return $db->saveObject($this);
    }


    public function setAlt($alt)
    {
        $this->alt = strip_tags($alt);
    }

    public function getAlt($check=FALSE)
    {
        if ((bool)$check && empty($this->alt) && isset($this->title)) {
            return htmlspecialchars($this->title, ENT_QUOTES);
        }

        return $this->alt;
    }

    public function setMaxWidth($width)
    {
        $this->_max_width = (int)$width;
    }

    public function setMaxHeight($height)
    {
        $this->_max_height = (int)$height;
    }

    public function prewriteResize()
    {
        if (!empty($_POST['fw']) && !empty($_POST['fh'])) {
            $req_width  = $_POST['fw'];
            $req_height = $_POST['fh'];
        } elseif (isset($_POST['resize'])) {
            $req_height = $req_width  = $_POST['resize'];
        } else {
            $req_width = $this->_max_width;
            $req_height = $this->_max_height;
        }

        if ($req_width < $this->width || $req_height < $this->height) {
            $resize_width  = &$req_width;
            $resize_height = &$req_height;
        } elseif ($this->width > $this->_max_width || $this->height > $this->_max_height) {
            $resize_width  = &$this->_max_width;
            $resize_height = &$this->_max_height;
        } else {
            // The request is greater in size than the original.
            return true;
        }

        $tmp_file = $this->_upload->upload['tmp_name'];
        $cpy_file = $tmp_file . '.rs';

        $result = PHPWS_File::scaleImage($tmp_file, $cpy_file, $resize_width, $resize_height);

        if (!PHPWS_Error::logIfError($result) && !$result) {
            return PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'PHPWS_Image::prewriteResize', array($this->width, $this->height, $this->_max_width, $this->_max_height));
        } else {
            if (!@copy($cpy_file, $tmp_file)) {
                return PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'PHPWS_Image::prewriteResize', array($this->width, $this->height, $this->_max_width, $this->_max_height));
            } else {
                list($this->width, $this->height, $image_type, $image_attr) = getimagesize($tmp_file);
                $image_name = $this->file_name;
                $a_image = explode('.', $image_name);
                $ext = array_pop($a_image);
                $this->file_name = sprintf('%s_%sx%s.%s', implode('.', $a_image), $this->width, $this->height, $ext);
            }
        }
        return true;
    }

    public function prewriteRotate()
    {
        $degrees = $this->_getDegrees();
        if (!$degrees) {
            return true;
        }

        $tmp_file = $this->_upload->upload['tmp_name'];
        $cpy_file = $tmp_file . '.rs';

        $result = PHPWS_File::rotateImage($tmp_file, $cpy_file, $degrees);

        if (!PHPWS_Error::logIfError($result) && !$result) {
            return PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'PHPWS_Image::prewriteRotate', array($this->width, $this->height, $this->_max_width, $this->_max_height));
        } else {
            if (!@copy($cpy_file, $tmp_file)) {
                return PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'PHPWS_Image::prewriteRotate', array($this->width, $this->height, $this->_max_width, $this->_max_height));
            } else {
                list($this->width, $this->height, $image_type, $image_attr) = getimagesize($tmp_file);
                return true;
            }
        }
        return true;
    }

    public function getExtension()
    {
        switch ($this->file_type) {
        case 'image/jpeg':
        case 'image/jpg':
        case 'image/pjpeg':
            return 'jpg';

        case 'image/png':
        case 'image/x-png':
            return 'png';

        case 'image/gif':
            return 'gif';

        case 'image/wbmp':
            return 'bmp';

        default:
            return null;
        }
    }

    public function getResizePath()
    {
        return sprintf('%sresize/%s/', $this->file_directory, $this->id);
    }

    public function makeResizePath()
    {
        $base_dir = sprintf('%sresize/', $this->file_directory);
        $full_dir = $base_dir . $this->id . '/';
        if(is_dir($base_dir)) {
            if (is_dir($full_dir)) {
                if (!is_writable($full_dir)) {
                    return false;
                }
            } else {
                if (!@mkdir($full_dir)) {
                    PHPWS_Error::log(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Image::makeResizePath', $dir);
                    return false;
                }
            }
        } else {
            if (!@mkdir($base_dir)) {
                PHPWS_Error::log(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Image::makeResizePath', $dir);
                return false;
            }

            if (!@mkdir($full_dir)) {
                PHPWS_Error::log(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Image::makeResizePath', $dir);
                return false;
            }
        }

        return $full_dir;
    }

}

?>