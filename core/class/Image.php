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

    var $width     = NULL;
    var $height    = NULL;
    var $alt       = NULL;
    var $border    = 0;

    function PHPWS_Image($id=NULL){
        $this->_classtype = 'image';
        if (empty($id))
            return;
    
        $this->setId((int)$id);
        $result = $this->init();
    }

    function getTag(){
        $tag[] = '<img';

        $path = $this->getPath();
        if (PEAR::isError($path))
            return $path;
        $tag[] = 'src="' . $path . '"';
        $tag[] = 'alt="' . $this->getAlt(TRUE) . '"';
        $tag[] = 'title="' . $this->getTitle(TRUE) . '"';
        $tag[] = 'width="' . $this->getWidth() . '"';
        $tag[] = 'height="' . $this->getHeight() . '"';
        $tag[] = 'border="' . $this->getBorder() . '"';
        $tag[] = '/>';
        return implode(' ', $tag);
    }

    function getLink($newTarget=FALSE){
        $tag[] = '<a href="';
        $tag[] = $this->getPath();
        $tag[] = '"';
        if ($newTarget)
            $tag[] = ' target="_blank"';

        $tag[] = '>';
        $tag[] = $this->getTitle();
        $tag[] = '</a>';

        return implode('', $tag);
    }

    function getJSView(){
        $values['address'] = $this->getPath();
        $values['label']   = $this->getTitle();
        $values['width'] = $this->getWidth();
        $values['height'] = $this->getHeight();
        return Layout::getJavascript('open_window', $values);
    }

    function setType($type){
        if (is_numeric($type)){
            $new_type = image_type_to_mime_type($type);
            $this->type = $new_type;
        } else {
            $this->type = $type;
        }
    }

    function getType(){
        return $this->type;
    }
  

    function setWidth($width){
        $this->width = $width;
    }

    function getWidth(){
        return $this->width;
    }

    function setHeight($height){
        $this->height = $height;
    }

    function getHeight(){
        return $this->height;
    }

    function setBounds($path=NULL){
        if (empty($path))
            $path = $this->getPath();

        $bound = @getimagesize($path);

        if (!is_array($bound))
            return PHPWS_Error::get(PHPWS_BOUND_FAILED, 'core', 'PHPWS_image::setBounds', $this->getPath());

        $size = @filesize($path);
        $this->setSize($size);

        $this->setWidth($bound[0]);
        $this->setHeight($bound[1]);
        $this->setType($bound[2]);
    }

    function setAlt($alt){
        $this->alt = $alt;
    }

    function getAlt($check=FALSE){
        if ((bool)$check && empty($this->alt) && isset($this->title))
            return $this->title;

        return $this->alt;
    }


    function setBorder($border){
        $this->border = $border;
    }

    function getBorder(){
        return $this->border;
    }


    function allowWidth($imagewidth=NULL){
        if (!isset($imagewidth))
            $imagewidth = $this->getWidth();

        return ($imagewidth <= MAX_IMAGE_WIDTH) ? TRUE : FALSE;
    }

    function allowHeight($imageheight=NULL){
        if (!isset($imageheight))
            $imageheight = $this->getHeight();

        return ($imageheight <= MAX_IMAGE_HEIGHT) ? TRUE : FALSE;
    }


    function checkBounds(){
        if (!$this->allowSize())
            $errors[] = PHPWS_Error::get(PHPWS_IMG_SIZE, 'core', 'PHPWS_Image::checkBounds', array($this->getSize()));

        if (!$this->allowType())
            $errors[] = PHPWS_Error::get(PHPWS_IMG_WRONG_TYPE, 'core', 'PHPWS_image::checkBounds');

        if (!$this->allowWidth())
            $errors[] = PHPWS_Error::get(PHPWS_IMG_WIDTH, 'core', 'PHPWS_image::checkBounds', array($this->getWidth()));

        if (!$this->allowHeight())
            $errors[] = PHPWS_Error::get(PHPWS_IMG_HEIGHT, 'core', 'PHPWS_image::checkBounds', array($this->getHeight()));

        if (isset($errors))
            return $errors;
        else
            return TRUE;
    }

    function importPost($varName){
        $result = $this->getFILES($varName);

        if (PEAR::isError($result))
            return $result;

        $this->setBounds($this->getTmpName());
        $result = $this->checkBounds();
        return $result;
    }

    function save(){
        if (empty($this->alt)) {
            if (empty($this->title)) {
                $this->title = $this->filename;
            }
            $this->alt = $this->title;
        }

        $result = $this->write();
        if (PEAR::isError($result)) {
            return $result;
        }

        $db = & new PHPWS_DB('images');

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
}

?>