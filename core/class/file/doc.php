<?php

class PHPWS_doc extends File_Common {
  var $_table    = "doc";


  function setType($type){
    $this->type = $type;
  }

  function getType(){
    return $this->type;
  }

  function getTitle(){
    return $this->title;
  }

}

?>