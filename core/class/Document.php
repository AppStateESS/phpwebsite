<?php

PHPWS_Core::initCoreClass("File_Common.php");

class PHPWS_doc extends File_Common {

  function setType($type){
    $this->type = $type;
  }

  function getType(){
    return $this->type;
  }

  function getTitle(){
    return $this->title;
  }

  function checkBounds(){
    if (!$this->allowSize())
      $errors[] = PHPWS_Error::get(PHPWS_IMG_SIZE, "core", "PHPWS_doc::checkBounds", array($this->getSize()));

    if (!$this->allowType())
      $errors[] = PHPWS_Error::get(PHPWS_IMG_WRONG_TYPE, "core", "PHPWS_doc::checkBounds");

    if (isset($errors))
      return $errors;
    else
      return TRUE;
  }


  function loadUpload($varName){
    $result = $this->getFILES($varName);

    if (PEAR::isError($result))
      return $result;

    $result = $this->checkBounds();
    return $result;
  }

  function setBounds($path=NULL){
    if (empty($path))
      $path = $this->getPath();

    $size = @filesize($path);

    if (empty($size))
      return PHPWS_Error::get(PHPWS_BOUND_FAILED, "core", "PHPWS_doc::setBounds", $path);

    $this->setSize($size);

    $type = mime_content_type($path);

    $this->setType($type);
  }


}

?>