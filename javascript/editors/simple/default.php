<?php

  /**
   * @version $Id$
   * @author Matthew McNaney
   * @author Steven Levin
   * @author Shaun Murray
   */

include(PHPWS_SOURCE_DIR . 'javascript/editors/simple/settings.php');

if(!isset($GLOBALS['wysiwyg_tab_index'])) {
    $GLOBALS['wysiwyg_tab_index'] = 30;    // set this number higher if you need to use forms with many elements
 }

if (!isset($GLOBALS['wysiwyg'])) {
    $GLOBALS['wysiwyg'] = 0;
 }


if (extension_loaded('pspell') && $ssc_on) {
    $script = sprintf('
<script type="text/javascript">
//<![CDATA[

function sscCheckText(id) {
   element = document.getElementById(id);
   if(element.value == \'\') {
      alert(\'There is no text to be checked for spelling errors.\');
   } else {
      loc = \'%sjavascript/editors/simple/speller.php?ssc_lang=%s&ssc_speed=%s&section=\' + id;
      window.open(loc, \'_BLANK\', \'width=500,height=400,toolbar=no,scrollbars=yes,status=yes,top=50,left=50,screenX=50,screenY=50\');
   }
}
//]]>
</script>
',PHPWS_SOURCE_HTTP,$ssc_lang, $ssc_speed);
    Layout::addJSHeader($script);
    $data['speller'] =  sprintf('<img src="%sjavascript/editors/simple/images/spell.gif" alt="Spell Checker" title="Spell Checker" height="20" width="21" onclick="sscCheckText(\'%s\');" onmouseover="window.status=\'Add break\'; return true;" onmouseout="window.status=\'\';" />', PHPWS_SOURCE_HTTP, $data['ID']);
 }



$GLOBALS['wysiwyg']++;

$data['number'] = $GLOBALS['wysiwyg'];

?>