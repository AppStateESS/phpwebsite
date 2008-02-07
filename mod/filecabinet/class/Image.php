<?php

/**
 * Image class that builds off the File_Command class
 * Assists with image files
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'File_Common.php');

// Normally set in config/core/file_types.php
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', serialize(array('image/jpeg',
                                                  'image/jpg',
                                                  'image/pjpeg',
                                                  'image/png',
                                                  'image/x-png',
                                                  'image/gif',
                                                  'image/wbmp')));
 }

if (!defined('FC_THUMBNAIL_WIDTH')) {
    define('FC_THUMBNAIL_WIDTH', 100);
 }

if (!defined('FC_THUMBNAIL_HEIGHT')) {
    define('FC_THUMBNAIL_HEIGHT', 100);
 }

if (!defined('FC_MIN_POPUP_SIZE')) {
    define('FC_MIN_POPUP_SIZE', 400);
 }

class PHPWS_Image extends File_Common {
    var $width            = NULL;
    var $height           = NULL;
    var $alt              = NULL;
    /**
     * 
     */
    var $parent_id        = 0;
    var $url              = null;
    
    var $_classtype       = 'image';
    var $_max_width       = 0;
    var $_max_height      = 0;

    function PHPWS_Image($id=NULL)
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
    }

    function init()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new PHPWS_DB('images');
        return $db->loadObject($this);
    }

    function loadDimensions()
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

    function allowImageType($type)
    {
        $image = new PHPWS_Image;
        return $image->allowType($type);
    }

    function allowDimensions()
    {
        if ($this->allowWidth() && $this->allowHeight()) {
            return true;
        } else {
            return false;
        }
    }


    function allowHeight($imageheight=NULL)
    {
        if (!isset($imageheight)) {
            $imageheight = &$this->height;
        }

        return ($imageheight <= $this->_max_height) ? TRUE : FALSE;
    }


    function allowWidth($imagewidth=NULL)
    {
        if (!isset($imagewidth)) {
            $imagewidth = &$this->width;
        }

        return ($imagewidth <= $this->_max_width) ? TRUE : FALSE;
    }

    function popupSize()
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

    function getJSView($thumbnail=FALSE, $link_override=null)
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

        $size = $this->popupSize();

        $values['address']     = $this->popupAddress();
        $values['width']       = $size[0] + 50;
        $values['height']      = $size[1] + 100;
        $values['window_name'] = 'image_view';

        return Layout::getJavascript('open_window', $values);
    }

    function popupAddress()
    {
        if (MOD_REWRITE_ENABLED) {
            return sprintf('filecabinet/%s/image', $this->id);
        } else {
            return sprintf('index.php?module=filecabinet&amp;page=image&amp;id=%s', $this->id);
        }

    }

    function thumbnailDirectory()
    {
        return $this->file_directory . 'tn/';
    }

    function thumbnailPath()
    {
        return $this->thumbnailDirectory() . $this->file_name;
    }

    function captioned($id=null, $linked=true)
    {
        if (empty($this->description)) {
            return $this->getTag();
        }

        $width = $this->width - 6;

        $tpl['IMAGE']   = $this->getTag($id, $linked);
        $tpl['CAPTION'] = $this->getDescription();
        $tpl['WIDTH']   = $width . 'px';
        return PHPWS_Template::process($tpl, 'filecabinet', 'captioned_image.tpl');
    }

    function getTag($id=null, $linked=true)
    {
        $tag[] = '<img';
        $tag[] = 'src="'    . $this->getPath() . '"';
        $tag[] = 'alt="'    . $this->getAlt(TRUE)   . '"';
        $tag[] = 'title="'  . $this->title . '"';
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

    function getThumbnail($css_id=null)
    {
        if (empty($css_id)) {
            $css_id = $this->id;
        }
        return sprintf('<img src="%s" title="%s" id="image-thumbnail-%s" />',
                       $this->thumbnailPath(),
                       $this->title, $css_id);
    }


    function loadAllowedTypes()
    {
        $this->_allowed_types = unserialize(ALLOWED_IMAGE_TYPES);
    }


    function resize($dst, $max_width, $max_height)
    {
        if ($this->width > $this->height) {
            $new_width = $new_height = round($this->height * 0.8);
        } else {
            $new_width = $new_height = round($this->width * 0.8);
        }

        if ($new_width < $max_width) {
            $new_width = $max_width;
        }

        if ($new_height < $max_height) {
            $new_height = $max_height;
        }

        if ( ($this->width - $new_width) > PHPWS_Settings::get('filecabinet', 'crop_threshold')  && 
             ($this->width - $new_width) > PHPWS_Settings::get('filecabinet', 'crop_threshold') ) {
            PHPWS_File::cropImage($this->getPath(), $dst, $new_width, $new_height);
            return PHPWS_File::scaleImage($dst, $dst, $max_width, $max_height);
        } else {
            return PHPWS_File::scaleImage($this->getPath(), $dst, $max_width, $max_height);
        }
    }

    function makeThumbnail()
    {
        if ($this->width <= FC_THUMBNAIL_WIDTH && $this->height <= FC_THUMBBNAIL_HEIGHT) {
            return @copy($this->getPath(), $this->thumbnailPath());
        } else {
            return $this->resize($this->thumbnailPath(), FC_THUMBNAIL_WIDTH, FC_THUMBNAIL_HEIGHT);
        }
    }


    function delete()
    {
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

    function deleteAssoc()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('file_type', FC_IMAGE, '=', 'or', 1);
        $db->addWhere('file_type', FC_IMAGE_RESIZE, '=', 'or', 1);
        $db->addWhere('file_id', $this->id);
        return $db->delete();
    }


    function pinTags()
    {
        $tpl['TN'] = $this->getJSView(true);
        return $tpl;
    }
    
    function editLink($icon=false)
    {
        $vars['iop'] = 'upload_image_form';
        $vars['image_id'] = $this->id;
        $vars['folder_id'] = $this->folder_id;
            
        $jsvars['width'] = 550;
        $jsvars['height'] = 600 + FC_THUMBNAIL_HEIGHT;
        $jsvars['address'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);
        $jsvars['window_name'] = 'edit_link';

        if ($icon) {
            $jsvars['label'] =sprintf('<img src="images/mod/filecabinet/edit.png" title="%s" />', dgettext('filecabinet', 'Edit image'));
        } else {
            $jsvars['label'] = dgettext('filecabinet', 'Edit');
        }
        return javascript('open_window', $jsvars);
    }


    function deleteLink($icon=false)
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

    function rowTags()
    {
        if (Current_User::isLogged()) {
            $clip = sprintf('<img src="images/mod/filecabinet/clip.png" title="%s" />', dgettext('filecabinet', 'Clip image'));
            $links[] = PHPWS_Text::secureLink($clip, 'filecabinet',
                                              array('iop'      => 'clip_image',
                                                    'image_id' => $this->id));
        }

        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $links[] = $this->editLink(true);
            $links[] = $this->deleteLink(true);
        }

        if (isset($links)) {
            $tpl['ACTION'] = implode('', $links);
        }
        $tpl['SIZE'] = $this->getSize(TRUE);
        $tpl['FILE_NAME'] = $this->file_name;
        $tpl['THUMBNAIL'] = $this->getJSView(TRUE);
        $tpl['TITLE']     = $this->title;
        $tpl['DIMENSIONS'] = sprintf('%s x %s', $this->width, $this->height);
        
        return $tpl;
    }

    function getManagerIcon($fmanager)
    {
        $force = $fmanager->force_resize ? 'true' : 'false';
        if ( ($fmanager->max_width < $this->width) || ($fmanager->max_height < $this->height) ) {
            return sprintf('<a href="#" onclick="oversized(%s, %s); return false">%s</a>', $this->id, $force, $this->getThumbnail());
        } else {
            $vars = $fmanager->linkInfo(false);
            $vars['fop']       = 'pick_file';
            $vars['file_type'] = FC_IMAGE;
            $vars['id']        = $this->id;
            $link = PHPWS_Text::linkAddress('filecabinet', $vars, true);
            return sprintf('<a href="%s">%s</a>', $link, $this->getThumbnail());
        }
    }
    
    function managerTpl($fmanager)
    {
        if ($fmanager->file_assoc->file_type == FC_IMAGE &&
            $fmanager->file_assoc->file_id == $this->id) {
            $tpl['HIGHLIGHT'] = 'highlight';
        }

        $tpl['ICON']  = $this->getManagerIcon($fmanager);
        $tpl['TITLE'] = $this->title;
        $tpl['INFO']  = sprintf('%s x %s - %s', $this->width, $this->height,
                                $this->getSize(true));

        $links[] = $this->getJSView(false);
        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $links[] = $this->editLink(true);
            $links[] = $this->deleteLink(true);
        }

        if (isset($links)) {
            $tpl['LINKS'] = implode(' ', $links);
        }
        return $tpl;
    }

    /**
     * Rotates an image
     */
    function rotate($save=true, $degrees=0)
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


    function _getDegrees()
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


    function save($no_dupes=true, $write=true, $thumbnail=true)
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
                $this->title = $this->file_name;
            }
            $this->alt = $this->title;
        }

        if ($write) {
            if (!is_writable($this->file_directory)) {
                return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Image::save', $this->file_directory);
            }

            if (!$this->id && is_file($this->getPath())) {
                return PHPWS_Error::get(FC_DUPLICATE_FILE, 'filecabinet', 'PHPWS_Image::save', $this->getPath());
            }

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


    function setAlt($alt)
    {
        $this->alt = strip_tags($alt);
    }

    function getAlt($check=FALSE)
    {
        if ((bool)$check && empty($this->alt) && isset($this->title)) {
            return $this->title;
        }

        return $this->alt;
    }

    function setMaxWidth($width)
    {
        $this->_max_width = (int)$width;
    }

    function setMaxHeight($height)
    {
        $this->_max_height = (int)$height;
    }

    function prewriteResize()
    {
        if (isset($_POST['resize'])) {
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

    function prewriteRotate()
    {
        $degrees = $this->_getDegrees();
        if (!$degrees) {
            return true;
        }

        $tmp_file = $this->_upload->upload['tmp_name'];
        $cpy_file = $tmp_file . '.rs';

        $result = PHPWS_File::rotateImage($tmp_file, $cpy_file, $degrees);

        if (!PHPWS_Error::logIfError($result) && !$result) {
            return PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'File_Common::importPost', array($this->width, $this->height, $this->_max_width, $this->_max_height));                        
        } else {
            if (!@copy($cpy_file, $tmp_file)) {
                return PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'File_Common::importPost', array($this->width, $this->height, $this->_max_width, $this->_max_height));
            } else {
                list($this->width, $this->height, $image_type, $image_attr) = getimagesize($tmp_file);
                return true;
            }
        }
        return true;
    }
    
    function getExtension()
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

    function getResizePath()
    {
        return sprintf('%sresize/%s/', $this->file_directory, $this->id);
    }

    function makeResizePath()
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