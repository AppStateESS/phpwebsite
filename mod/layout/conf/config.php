<?php

// If you wish to allow insertion of php code in a theme (i.e. the inclusion of
// a theme's 'theme.php' file) then set the following define to TRUE.
// MAKE SURE to carefully inspect all of your current themes' theme.php files
// BEFORE changing this to TRUE.

$table = "
<table cellpadding='3' cellspacing='1' style='background-color : black'>
  <tr>
    <td style='background-color : black; color : white'><b>{TITLE}</b></td>
  </tr>
  <tr>
    <td style='background-color : white ; color : black'>{CONTENT}</td>
  </tr>
</table>";

define("DEFAULT_TEMPLATE", $table);
define("DEFAULT_THEME_VAR", "BODY");
define("DEFAULT_LAYOUT_HOLD", 20);
define("DEFAULT_CONTENT_VAR", "_MAIN");

if (!defined("ALLOW_THEME_PHP_INSERTION"))
     define("ALLOW_THEME_PHP_INSERTION", FALSE);

?>