<?php

function export(&$info){
  switch ($info['type']){
  case "int":
    if ($info['len'] > 6)
      $setting = "INT";
    else
      $setting = "SMALLINT";
    break;
    
  case "blob":
    $setting = "TEXT";
    $info['flags'] = NULL;
    break;
    
  case "string":
    $setting = "CHAR(" . $info['len'] . ")";
    break;
    
  case "date":
    $setting = "DATE";
    break;
    
  case "real":
    $setting = "FLOAT";
    break;
    
  case "timestamp":
    $setting = "TIMESTAMP";
    $info['flags'] = NULL;
    break;
  }

  return $setting;
}


?>