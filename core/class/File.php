<?php

class PHPWS_File {

  function create($type){
    if ($type != "image" || $type != "doc")
      exit("Wrong file factory.");
    
    $className = "PHPWS_" . $type;
    $fileName = $type . ".php";

    require PHPWS_SOURCE_DIR . "core/class/file/$fileName";

    $object = & new $className;

  }

?>