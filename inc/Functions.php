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

// Taken from php.net
// Written by dave at codexweb dot co dot za
// Edited by Matthew McNaney
if (!function_exists('html_entity_decode')) {
  if (!defined("ENT_COMPAT")) define("ENT_COMPAT", 1);
  if (!defined("ENT_NOQUOTES")) define("ENT_NOQUOTES", 4);
  if (!defined("ENT_QUOTES")) define("ENT_QUOTES", 2);

  function html_entity_decode ($string, $opt = ENT_COMPAT) {

   $trans_tbl = get_html_translation_table (HTML_ENTITIES);
   $trans_tbl = array_flip ($trans_tbl);

   if ($opt == 2)
     $trans_tbl["&#039;"] = $trans_tbl["&apos;"] = "'";

   if ($opt == 4)
     unset($trans_tbl["&quot;"]);
   return strtr ($string, $trans_tbl);
  }

}

?>