<?php

if (isset($_SERVER['WINDIR']) || preg_match("/(microsoft|win32)/i", $_SERVER['SERVER_SOFTWARE'])){
  ini_set("include_path", ".;.\\lib\\pear\\");
}
else {
  ini_set("include_path", ".:./lib/pear/");
}

define("PHPWS_SOURCE_DIR", "./");
define("PHPWS_LOG_DIRECTORY", "./logs/");
define("LOG_PERMISSION", 0644);
define("LOG_TIME_FORMAT", "%X %x");
define("PHPWS_LOG_ERRORS", TRUE);

define("DEFAULT_LANGUAGE", "en");

?>