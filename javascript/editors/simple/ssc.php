<?php

include(PHPWS_SOURCE_DIR.'conf/javascriptSettings.php');

if ($_SESSION['OBJ_user']->js_on && extension_loaded('pspell') && $ssc_on) {

    if (!isset($GLOBALS['ssc'])) {
	$GLOBALS['ssc'] = true;
	
	$GLOBALS['core']->js_func[] = "
function sscCheckText(section) {
   element = document.getElementById(section);

   if(element.value == \"\") {
      alert('There is no text to be checked for spelling errors.');
   } else {
      loc = './js/ssc/speller.php?ssc_lang={$ssc_lang}&ssc_speed={$ssc_speed}&section=' + section + '&style=' + '../../{$_SESSION['OBJ_layout']->theme_address}' + 'style.css';
      window.open(loc, '_BLANK', 'width=500,height=400,toolbar=no,scrollbars=yes,status=true,top=50,left=50,screenX=50,screenY=50');
   }
}\n";

    }

    $js = "<input type=\"button\" value=\"Spell Check\" onclick=\"sscCheckText('{$section_name}');\" onmouseover=\"window.status='Spell Checker'; return true;\" onmouseout=\"window.status='';\" />";
} else {
    $js = NULL;
}

?>