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

PHPWS_Core::initCoreClass("File.php");

class PHPWS_Image extends PHPWS_File{
  var $_width     = NULL;
  var $_height    = NULL;
  var $_alt       = NULL;
  var $_border    = 0;

  function PHPWS_Image($id=NULL){
    $this->setTable("images");

    $this->addExclude(array("_border"));

    if (isset($id)){
      $this->setId($id);
      $this->init();
    }
  }

  function getTag(){
    $tag[] = "<img";
    $tag[] = "src=\"" . $this->getPath() . "\"";
    $tag[] = "alt=\"" . $this->getAlt() . "\"";
    $tag[] = "title=\"" . $this->getTitle() . "\"";
    $tag[] = "width=\"" . $this->getWidth() . "\"";
    $tag[] = "height=\"" . $this->getHeight() . "\"";
    $tag[] = "border=\"" . $this->getBorder() . "\"";
    $tag[] = "/>";
    return implode(" ", $tag);
  }


  function _setWidth($width){
    $this->_width = $width;
  }

  function getWidth(){
    return $this->_width;
  }

  function _setHeight($height){
    $this->_height = $height;
  }

  function getHeight(){
    return $this->_height;
  }

  function setBounds(){
    $bound = getimagesize($this->getPath());
    $this->_setWidth($bound[0]);
    $this->_setHeight($bound[1]);
    $this->_setType($bound[2]);
  }

  function setAlt($alt){
    $this->_alt = $alt;
  }

  function getAlt(){
    return $this->_alt;
  }

  function setBorder($border){
    $this->_border = $border;
  }

  function getBorder(){
    return $this->_border;
  }

  function save(){
    $this->setBounds();
    $height = $this->getHeight();
    $width  = $this->getWidth();
    $alt    = $this->getAlt();
    $type   = $this->getType();

    if (!isset($alt)){
      if ($title = $this->getTitle())
	$this->setAlt($title);
      else {
	$this->setTitle($this->getFilename());
	$this->setAlt($this->getFilename());
      }
    }

    $this->commit();
  }

  /**
   * Creates a thumbnail of a jpeg or png image.
   *
   * @author   Jeremy Agee <jagee@NOSPAM.tux.appstate.edu>
   * @modified Adam Morton <adam@NOSPAM.tux.appstate.edu>
   * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
   * @modified Matthew McNaney <matt at NOSPAM dot tux dot appstate dot edu>
   * @param    string  $fileName          The file name of the image you want thumbnailed.
   * @param    string  $directory         Path to the file you want thumbnailed
   * @param    string  $tndirectory       The path to where the new thumbnail file is stored
   * @param    integer $maxHeight         Set width of the thumbnail if you do not want to use the default 
   * @param    integer $maxWidth          Set height of the thumbnail if you do not want to use the default
   * @return   array   0=>thumbnailFileName, 1=>thumbnailWidth, 2=>thumbnailHeight 
   * @access   public
   */
  function makeThumbnail($fileName, $directory, $tndirectory, $maxWidth=125, $maxHeight=125){
    $image = new PHPWS_Image;

    $image->setDirectory($directory);
    $image->setName = $fileName;

    $image = implode("", array($directory, $fileName));

    if (!is_file($image))
      return PEAR::raiseError("Image <b>$image</b> not found.");

    $imageInfo = getimagesize($image);

    if($imageInfo[0] > $imageInfo[1]) {
      $scale = $maxWidth / $imageInfo[0];
    } else{
      $scale = $maxHeight / $imageInfo[1];
    }

    $thumbnailWidth = round($scale * $imageInfo[0]);
    $thumbnailHeight = round($scale * $imageInfo[1]);
    $thumbnailImage = NULL;
    if(PHPWS_Image::chkgd2())
        $thumbnailImage = ImageCreateTrueColor($thumbnailWidth, $thumbnailHeight);
    else
        $thumbnailImage = ImageCreate($thumbnailWidth, $thumbnailHeight);
  
    if ($imageInfo[2] == 2) {
      $fullImage = ImageCreateFromJPEG($image);
    } else if ($imageInfo[2] == 3){
      $fullImage = ImageCreateFromPNG($image);
    }
  
    ImageCopyResized($thumbnailImage, $fullImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, ImageSX($fullImage), ImageSY($fullImage));
    ImageDestroy($fullImage);

    $thumbnailFileName = explode('.', $fileName);

    if ($imageInfo[2] == 2) {
      $thumbnailFileName = $thumbnailFileName[0] . "_tn.jpg";
      imagejpeg($thumbnailImage, $tndirectory . $thumbnailFileName);
    } else if ($imageInfo[2] == 3){
      $thumbnailFileName = $thumbnailFileName[0] . "_tn.png";
      imagepng($thumbnailImage, $tndirectory . $thumbnailFileName);
    }

    
    return array($thumbnailFileName, $thumbnailWidth, $thumbnailHeight);
  } // END FUNC makeThumbnail()

  function chkgd2(){
    if(function_exists("gd_info")) {
      $gdver = gd_info();
      if(strstr($gdver["GD Version"], "1.") != FALSE) {
	return FALSE;
      } else {
	return TRUE;
      }
    } else {
      ob_start();
      phpinfo(8);
      $phpinfo=ob_get_contents();
      ob_end_clean();
      $phpinfo=strip_tags($phpinfo);
      $phpinfo=stristr($phpinfo,"gd version");
      $phpinfo=stristr($phpinfo,"version");
      $end=strpos($phpinfo," ");
      $phpinfo=substr($phpinfo,0,$end);
      $phpinfo=substr($phpinfo,7);
      if(version_compare("2.0", "$phpinfo")==1)
	return FALSE;
      else
	return TRUE;
    }
  }// END FUNC chkgd2()
  
  
}


?>