<?php

/**
 * Image class that builds off the File_Command class
 * Assists with image files
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */


PHPWS_Core::initCoreClass('File_Common.php');

class PHPWS_Image extends File_Common{

    var $width            = NULL;
    var $height           = NULL;
    var $alt              = NULL;
    var $border           = 0;
    var $thumbnail_source = 0;
    var $_max_size        = MAX_IMAGE_SIZE;
    var $_max_width       = MAX_IMAGE_WIDTH;
    var $_max_height      = MAX_IMAGE_HEIGHT;

    function PHPWS_Image($id=NULL)
    {
        $this->_classtype = 'image';
        if (empty($id)) {
            return;
        }
    
        $this->setId((int)$id);
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->_errors[] = $result;
        } elseif (empty($result)) {
            return PHPWS_Error::get(PHPWS_IMG_NOT_FOUND, 'core', 'PHPWS_Image');
        }
    }

    function getTag($full_path=FALSE)
    {
        $tag[] = '<img';

        $path = $this->getPath($full_path);
        if (PEAR::isError($path)) {
            return $path;
        }
        $tag[] = 'src="'    . $path . '"';
        $tag[] = 'alt="'    . $this->getAlt(TRUE)   . '"';
        $tag[] = 'title="'  . $this->getTitle(TRUE) . '"';
        $tag[] = 'width="'  . $this->width     . '"';
        $tag[] = 'height="' . $this->height    . '"';
        $tag[] = 'border="' . $this->border    . '"';
        $tag[] = '/>';
        return implode(' ', $tag);
    }

    function getPath($full_path=FALSE, $path_type='http')
    {
        if (empty($this->filename)) {
            return PHPWS_Error::get(PHPWS_FILENAME_NOT_SET, 'core', 'File_Common::getPath');
        }

        if (empty($this->directory)) {
            return PHPWS_Error::get(PHPWS_DIRECTORY_NOT_SET, 'core', 'File_Common::getPath');
        }

        if ($full_path) {
            if ($path_type == 'http') {
                $path = PHPWS_Core::getHomeHttp();
            } else {
                $path = PHPWS_Core::getHomeDir();
            }
        } else {
            $path = './';
        }
        return $path . 'images/' . $this->getDirectory() . $this->getFilename();
    }

    function getLink($newTarget=FALSE)
    {
        $tag[] = '<a href="';
        $tag[] = $this->getPath();
        $tag[] = '"';
        if ($newTarget) {
            $tag[] = ' target="_blank"';
        }

        $tag[] = '>';
        $tag[] = $this->getTitle();
        $tag[] = '</a>';

        return implode('', $tag);
    }

    function getJSView()
    {
        $values['address'] = $this->getPath();
        $values['label']   = $this->getTitle();
        $values['width'] = $this->getWidth();
        $values['height'] = $this->getHeight();
        return Layout::getJavascript('open_window', $values);
    }

    function setType($type)
    {
        if (is_numeric($type)) {
            $new_type = image_type_to_mime_type($type);
            $this->type = $new_type;
        } else {
            $this->type = $type;
        }
    }

    function setWidth($width)
    {
        $this->width = $width;
    }

    function setHeight($height)
    {
        $this->height = $height;
    }

    function setBounds($path=NULL)
    {
        if (empty($path)) {
            $path = $this->getPath();
        }

        $bound = @getimagesize($path);

        if (!is_array($bound)) {
            return PHPWS_Error::get(PHPWS_BOUND_FAILED, 'core',
                                    'PHPWS_image::setBounds', $this->getPath());
        }

        $size = @filesize($path);
        $this->setSize($size);
        $this->setWidth($bound[0]);
        $this->setHeight($bound[1]);
        $this->setType($bound[2]);
        return TRUE;
    }

    function setAlt($alt)
    {
        $this->alt = $alt;
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

    function setBorder($border)
    {
        $this->border = $border;
    }

    function getFullDirectory()
    {
        if (empty($this->directory)) {
            $this->directory = $this->module . '/';
        }
        return sprintf('images/%s%s', $this->directory, $this->filename);
    }

    function allowWidth($imagewidth=NULL)
    {
        if (!isset($imagewidth)) {
            $imagewidth = &$this->width;
        }

        return ($imagewidth <= $this->_max_width) ? TRUE : FALSE;
    }

    function allowHeight($imageheight=NULL)
    {
        if (!isset($imageheight)) {
            $imageheight = &$this->height;
        }

        return ($imageheight <= $this->_max_height) ? TRUE : FALSE;
    }


    function checkBounds()
    {
        // This should not be necessary as the form should
        // contain MAX_FILE_SIZE
        if (!$this->allowSize()) {
            $errors[] = PHPWS_Error::get(PHPWS_IMG_SIZE, 'core', 'PHPWS_Image::checkBounds', array($this->getSize(), $this->_max_size));
        }

        if (!$this->allowType()) {
            $errors[] = PHPWS_Error::get(PHPWS_IMG_WRONG_TYPE, 'core', 'PHPWS_image::checkBounds');
        }

        if (!$this->allowWidth()) {
            $errors[] = PHPWS_Error::get(PHPWS_IMG_WIDTH, 'core', 'PHPWS_image::checkBounds', array($this->width, $this->_max_width));
        }

        if (!$this->allowHeight()) {
            $errors[] = PHPWS_Error::get(PHPWS_IMG_HEIGHT, 'core', 'PHPWS_image::checkBounds', array($this->height, $this->_max_height));
        }

        if (isset($errors)) {
            return $errors;
        } else {
            return TRUE;
        }
    }

    function importPost($varName, $form_suffix=FALSE)
    {
        if ($form_suffix) {
            $varName .= '_file';
        }

        $result = $this->getFILES($varName);

        if (PEAR::isError($result)) {
            return $result;
        } elseif (!$result) {
            return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'core', 'PHPWS_Image::importPost');
        }

        $this->setBounds($this->getTmpName());
        return $this->checkBounds();
    }

    function save($no_dupes=TRUE, $write=TRUE)
    {
        if (empty($this->directory)) {
            $this->directory = $this->module . '/';
        }

        if (empty($this->alt)) {
            if (empty($this->title)) {
                $this->title = $this->filename;
            }
            $this->alt = $this->title;
        }

        if ($write) {
            $result = $this->write();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        $db = & new PHPWS_DB('images');

        if ((bool)$no_dupes) {
            $db->addWhere('filename',  $this->filename);
            $db->addWhere('directory', $this->directory);
            $db->addWhere('module',    $this->module);
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
 

    function isImage($type)
    {
        $imageTypes = array('image/jpeg',
                            'image/jpg',
                            'image/pjpeg',
                            'image/png',
                            'image/x-png',
                            'image/gif',
                            'image/wbmp');

        return in_array(trim($type), $imageTypes);
    }

    function delete($delete_thumbnail=TRUE)
    {
        $db = & new PHPWS_DB('images');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        $path = $this->getPath();
        if (PEAR::isError($path)) {
            return $path;
        }

        unlink($path);

        if ($this->thumbnail_source != $this->id) {
            $tn = & new PHPWS_Image;
            $db->reset();
            $db->addWhere('thumbnail_source', $this->id);
            $result = $db->loadObject($tn);
            if (empty($result)) {
                return TRUE;
            } elseif (PEAR::isError($result)) {
                return $result;
            }
            return $tn->delete(FALSE);
        }

        return TRUE;
    }

    function getXML()
    {
        $content[] = '<image>';
        $content[] = '<src>' . $this->getFullDirectory() . '</src>';
        $content[] = '<width>' . $this->width . '</width>';
        $content[] = '<height>' . $this->height . '</height>';
        $content[] = '<title>' . $this->getTitle() . '</title>';
        $content[] = '<alt>' . $this->getAlt() . '</alt>';
        $content[] = '<desc>' . $this->getDescription(TRUE) . '</desc>';
        $content[] = '</image>';
        return implode("\n", $content);
    }


}

?>