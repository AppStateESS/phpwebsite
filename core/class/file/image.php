<?php

define("IMAGETYPE_GIF",     1);
define("IMAGETYPE_JPEG",    2);
define("IMAGETYPE_PNG",     3);
define("IMAGETYPE_SWF",     4);
define("IMAGETYPE_PSD",     5);
define("IMAGETYPE_BMP",     6);
define("IMAGETYPE_TIFF_II", 7);
define("IMAGETYPE_TIFF_MM", 8);
define("IMAGETYPE_JPC",     9);
define("IMAGETYPE_JP2",    10);
define("IMAGETYPE_JPX",    11);
define("IMAGETYPE_JB2",    12);
define("IMAGETYPE_SWC",    13);
define("IMAGETYPE_IFF",    14);
define("IMAGETYPE_WBMP",   15);
define("IMAGETYPE_XBM",    16);

class PHPWS_image extends File_Common{

  var $width     = NULL;
  var $height    = NULL;
  var $alt       = NULL;
  var $border    = 0;
  var $_table    = "images";

  function getTag(){
    $tag[] = "<img";

    $path = $this->getPath();
    if (PEAR::isError($path))
      return $path;
    $tag[] = "src=\"$path\"";
    $tag[] = "alt=\"" . $this->getAlt(TRUE) . "\"";
    $tag[] = "title=\"" . $this->getTitle(TRUE) . "\"";
    $tag[] = "width=\"" . $this->getWidth() . "\"";
    $tag[] = "height=\"" . $this->getHeight() . "\"";
    $tag[] = "border=\"" . $this->getBorder() . "\"";
    $tag[] = "/>";
    return implode(" ", $tag);
  }


  function setType($type){
    if (is_numeric($type)){
      if (phpversion() >= "4.3.0")
	$new_type = image_type_to_mime_type($type);
      else {
	switch ($type){
	case IMAGETYPE_GIF:
	  $new_type = "image/gif";
	  break;
	case IMAGETYPE_JPEG:
	  $new_type = "image/jpeg";
	  break;
	case IMAGETYPE_PNG:
	  $new_type = "image/png";
	  break;
	case IMAGETYPE_SWF:
	  $new_type = "application/x-shockwave-flash";
	  break;
	case IMAGETYPE_PSD:
	  $new_type = "image/psd";
	  break;
	case IMAGETYPE_BMP:
	  $new_type = "image/bmp";
	  break;
	case IMAGETYPE_TIFF_MM:
	case IMAGETYPE_TIFF_II:
	  $new_type = "image/tiff";
	  break;
	case IMAGETYPE_JPC:
	  $new_type = "application/octet-stream";
	  break;
	case IMAGETYPE_JP2:
	  $new_type = "image/jp2";
	  break;
	case IMAGETYPE_JPX:
	case IMAGETYPE_JB2:
	  $new_type = "application/octet-stream";
	  break;
	case IMAGETYPE_SWC:
	  $new_type = "application/x-shockwave-flash";
	  break;
	case IMAGETYPE_IFF:
	  $new_type = "image/iff";
	  break;
	case IMAGETYPE_WBMP:
	  $new_type = "image/vnd.wap.wbmp";
	  break;
	case IMAGETYPE_XBM:
	  $new_type = "image/xbm";
	  break;
	}
      }
      $this->type = $new_type;
    } else
    $this->type = $type;
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
      $bound = @getimagesize($this->getPath());
    else
      $bound = @getimagesize($path);

    if (!is_array($bound))
      return PHPWS_Error::get(PHPWS_BOUND_FAILED, "core", "PHPWS_Image::setBounds", $this->getPath());

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
      $errors[] = PHPWS_Error::get(PHPWS_IMG_SIZE, "core", "PHPWS_Image::checkBounds", array($this->getSize()));

    if (!$this->allowType())
      $errors[] = PHPWS_Error::get(PHPWS_IMG_WRONG_TYPE, "core", "PHPWS_Image::checkBounds");

    if (!$this->allowWidth())
      $errors[] = PHPWS_Error::get(PHPWS_IMG_WIDTH, "core", "PHPWS_Image::checkBounds", array($this->getWidth()));

    if (!$this->allowHeight())
      $errors[] = PHPWS_Error::get(PHPWS_IMG_HEIGHT, "core", "PHPWS_Image::checkBounds", array($this->getHeight()));

    if (isset($errors))
      return $errors;
    else
      return TRUE;
  }

  function importFiles($varName){
    if (empty($_FILES) || empty($_FILES[$varName]))
      return PHPWS_Error::get(PHPWS_IMG_NO_FILES, "core", "PHPWS_Image::importFiles");

    $this->filename = $_FILES[$varName]['name'];
    $this->setSize($_FILES[$varName]['size']);
    $this->setTmpName($_FILES[$varName]['tmp_name']);

    $this->setBounds($this->getTmpName());
    $result = $this->checkBounds();
    return $result;
  }

  function writeImage(){
    $temp_dir = $this->getTmpName();
    $path = $this->getPath();

    if (empty($temp_dir))
      return PHPWS_Error::get(PHPWS_IMG_NO_TMP, "core", "PHPWS_Image::writeImage", $path);

    if (PEAR::isError($path))
      return $path;

    if(!move_uploaded_file($temp_dir, $path))
      return PHPWS_Error::get(PHPWS_IMG_DIR_NONWRITE, "core", "PHPWS_Image::writeImage", $path);

    return TRUE;
  }
  
}


?>