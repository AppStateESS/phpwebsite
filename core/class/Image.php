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

// This is 12 in php php > 4.3
define("IMAGETYPE_SWC",    13); 

class PHPWS_Image{
  var $directory = NULL;
  var $filename  = NULL;
  var $width     = NULL;
  var $height    = NULL;
  var $title     = NULL;
  var $alt       = NULL;
  var $border    = 0;

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

  function setDirectory($directory){
    if (!preg_match("/\/$/", $directory))
      $directory .= "/";
    $this->directory = $directory;
  }

  function getDirectory(){
    return $this->directory;
  }

  function setFilename($filename){
    $this->filename = $filename;
  }

  function getFilename(){
    return $this->filename;
  }

  function getPath(){
    if (empty($this->filename))
      return PHPWS_Error::get(PHPWS_FILENAME_NOT_SET, "core", "PHPWS_Image::getPath");

    if (empty($this->directory))
      return PHPWS_Error::get(PHPWS_DIRECTORY_NOT_SET, "core", "PHPWS_Image::getPath");

    return $this->getDirectory() . $this->getFilename();
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

  function setBounds(){
    $bound = @getimagesize($this->getPath());
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

  function setTitle($title){
    $this->title($title);
  }

  function getTitle($check=FALSE){
    if ((bool)$check && empty($this->title) && isset($this->alt))
      return $this->alt;

    return $this->title;
  }

  function setBorder($border){
    $this->_border = $border;
  }

  function getBorder(){
    return $this->border;
  }
  
}


?>