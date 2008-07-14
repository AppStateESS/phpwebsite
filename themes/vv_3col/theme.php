<?php

  /**
   * Sample of theme.php
   *
   * To enable this file
   * 1) Open config/layout/config.php
   * 2) Set LAYOUT_THEME_EXEC to "TRUE"
   * 3) Create a {SAMPLE} tag in your theme.tpl file if not 
   *    already present.
   *
   * The plug function puts the contents of the first parameter into
   * a tag named after the second parameter. The tag must be capitalized
   * in the theme.tpl file.
   */ 

$year = date('Y');
Layout::plug($year, 'year');

$host = $_SERVER['HTTP_HOST'];
Layout::plug($host, 'host');

?>