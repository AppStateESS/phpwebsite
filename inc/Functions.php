<?php

/**
 * Contains functions for older versions of php
 */

if (!function_exists("file_get_contents")){
  function file_get_contents($filename){
    if (!is_file($filename))
      return FALSE;

    $fd = @fopen($filename, "rb");
    
    if ($fd){
      $data = trim(fread ($fd, filesize ($filename)));
      fclose ($fd);
      return $data;
    } else
      return FALSE;
  }
}

if (!function_exists("file_put_contents")){
  function file_put_contents($filename, $data){
    if($fp = @fopen($fileName, "wb")){
      fwrite($fp, $data);
      fclose($fp);
      return TRUE;
    } else
      return FALSE;
  }
}

if (!function_exists ("mime_content_type")) {
 function mime_content_type ($file) {
  return exec ("file -bi " . escapeshellcmd($file));
 }
}

?>