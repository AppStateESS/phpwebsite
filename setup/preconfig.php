<?php

if (isset($_SERVER['windir']))
  ini_set("include_path", ".;.\\lib\\pear\\");
else
  ini_set("include_path", ".:./lib/pear/");


define("PHPWS_SOURCE_DIR", "./");
define("PHPWS_LOG_DIRECTORY", "./logs/");
define("LOG_PERMISSION", 644);
define("LOG_TIME_FORMAT", "%X %x");
define("PHPWS_LOG_ERRORS", TRUE);

define("DEFAULT_LANGUAGE", "en");

?>