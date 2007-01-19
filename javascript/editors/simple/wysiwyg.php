<?php

include('javascript/editors/simple/settings.php');

if(!$wysiwyg_on) return;

require_once(PHPWS_SOURCE_DIR.'core/Text.php');

if(!isset($GLOBALS['wysiwyg_tab_index']))
     $GLOBALS['wysiwyg_tab_index'] = 30;    // set this number higher if you need to use forms with many elements

if(!isset($GLOBALS['wysiwyg'])) $GLOBALS['wysiwyg'] = 1;

if($GLOBALS['wysiwyg'] == 1) {

$GLOBALS['core']->js_func[] = "
function insert(form, section, tag, desc) {
	var input = document.forms[form].elements[section];
	if((tag=='left')||(tag=='right')||(tag=='center')) {
		var aTag = '<div align=\\\"' + tag + '\\\">';
		var eTag = '</div>';
	} else if(tag=='br')  {
		var aTag = '';
		var eTag = '<' + tag + ' />';
	} else if(tag=='block')  {
		var aTag = '<blockquote><p>';
		var eTag = '</p></blockquote>';
	} else if (tag=='link') {
		var url = prompt(\"Please enter the url\", \"http://\");
		if (!(url) || (url=='http://'))	{url = 'http://www.yourlink.here'}
		var aTag = '<a href=\\\"' + url + '\\\">';
		var eTag = '</a>';
	} else if (tag=='email') {
		var url = prompt(\"Please enter the email address\", \"mailto:\");
		if (!(url) || (url=='mailto:'))	{url = 'mailto:your.email@domain.com'}
		var aTag = '<a href=\\\"' + url + '\\\">';
		var eTag = '</a>';
	} else if (tag=='olist') {
		var aTag = '\\r\\n<ol type=\\\"1\\\">\\r\\n  <li>Item 1</li>\\r\\n  <li>Item 2</li>\\r\\n  <li>Item 3</li>\\r\\n</ol>\\r\\n';
		var eTag = '';
	} else if (tag=='ulist') {
		var aTag = '\\r\\n<ul type=\\\"disc\\\">\\r\\n  <li>Item 1</li>\\r\\n  <li>Item 2</li>\\r\\n  <li>Item 3</li>\\r\\n</ul>\\r\\n';
		var eTag = '';
	} else {
		var aTag = '<' + tag + '>';						// our open tag
		var eTag = '</' + tag + '>';					// our close tag
	}
	input.focus();
	if(typeof document.selection != 'undefined') {	// For Internet Explorer
		if(document.getSelection) { //Try this for MacIE
			var insText = prompt(\"Please enter the text you'd like to\"+ desc +\":\");
		} else { //Or Win IE
			var range = document.selection.createRange();
			var insText = range.text;
		}
		if ((insText.length == 0) && ((tag == 'link') || (tag == 'email')))	{
			insText = prompt(\"Please enter a description\", \"\");
		}
		if(document.getSelection) { //Try this for MacIE
		insText = aTag + insText + eTag;
        form = document.getElementsByName(form)[0];
        eval('form.'+section+'.value=form.'+section+'.value + insText');
		} else { //Or Win IE
			range.text = aTag + insText + eTag;
			range = document.selection.createRange();
			if (insText.length == 0) {
				if((tag == 'olist') || (tag == 'ulist')) { range.move('character', aTag.length + eTag.length -6); }
				else { range.move('character', -eTag.length); }
			} else {
				if((tag == 'olist') || (tag == 'ulist')) { range.move('character', aTag.length + eTag.length -6); }
				else { range.moveStart('character', aTag.length + insText.length + eTag.length); }
			}
			range.select();
		}
	} else if(typeof input.selectionStart != 'undefined') { // For newer Gecko based Browsers
		var start = input.selectionStart;
		var end = input.selectionEnd;
		var insText = input.value.substring(start, end);
		if ((insText.length == 0) && ((tag == 'link') || (tag == 'email')))	{
			insText = prompt(\"Please enter a description\", \"\");
		}
		input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
		var pos;
		if (insText.length == 0) {
			if((tag == 'olist') || (tag == 'ulist')) { pos = start + aTag.length + eTag.length - 6; }
			else { pos = start + aTag.length; }
		} else {
			if((tag == 'olist') || (tag == 'ulist')) { pos = start + aTag.length + eTag.length - 6; }
			else { pos = start + aTag.length + insText.length + eTag.length; }
		}
		input.selectionStart = pos;
		input.selectionEnd = pos;
	} else {	// All the rest
		var pos = input.value.length;
		var insText = prompt(\"Please enter the text you'd like to\"+ desc +\":\");
		input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
	}
}
\n";

}

$js = "<a name=\"wysiwyg{$GLOBALS['wysiwyg']}\"></a>\n";
$js .= "<img src=\"./images/javascript/wysiwyg/bold.gif\" alt=\"Bold\" title=\"Bold\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'strong', 'Bold');\" onmouseover=\"window.status='Add bold'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/italic.gif\" alt=\"Italic\" title=\"Italic\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'em', 'Italic');\" onmouseover=\"window.status='Add italic'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/underline.gif\" alt=\"Underline\" title=\"Underline\"hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'u', 'Underline');\" onmouseover=\"window.status='Add underline'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/aleft.gif\" alt=\"Left\" title=\"Left\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'left', 'Left Justified');\" onmouseover=\"window.status='Add left justified'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/acenter.gif\" alt=\"Center\" title=\"Center\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'center', 'Center');\" onmouseover=\"window.status='Add centered'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/aright.gif\" alt=\"Right\" title=\"Right\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'right', 'Right Justified');\" onmouseover=\"window.status='Add right justified'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/bullet.gif\" alt=\"Bulleted List\" title=\"Bulleted List\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'ulist', 'Unordered List');\" onmouseover=\"window.status='Add unordered list'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/numbered.gif\" alt=\"Nubered List\" title=\"Nubered List\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'olist', 'Ordered List');\" onmouseover=\"window.status='Add ordered list'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/increase.gif\" alt=\"Increase\" title=\"Increase\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'block', 'Blockquote');\" onmouseover=\"window.status='Add block quote'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/email.gif\" alt=\"Email\" title=\"Email\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'email');\" onmouseover=\"window.status='Add email'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/link.gif\" alt=\"Link\" title=\"Link\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'link');\" onmouseover=\"window.status='Add link'; return true;\" onmouseout=\"window.status='';\" />\n";
$js .= "<img src=\"./images/javascript/wysiwyg/break.gif\" alt=\"Break\" title=\"Break\" hight=20 width=21 onclick=\"insert('{$form_name}', '{$section_name}', 'br', 'Break');\" onmouseover=\"window.status='Add break'; return true;\" onmouseout=\"window.status='';\" />\n";

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
      window.open(loc, '_BLANK', 'width=500,height=400,toolbar=no,scrollbars=yes,status=yes,top=50,left=50,screenX=50,screenY=50');
   }
}
";

    }
    
    $js .= "<img src=\"./images/javascript/wysiwyg/spell.gif\" alt=\"Spell Checker\" title=\"Spell Checker\" hight=20 width=21 onclick=\"sscCheckText('{$section_name}');\" onmouseover=\"window.status='Add break'; return true;\" onmouseout=\"window.status='';\" />\n";
}

$js .= "<br />\n";

$GLOBALS['wysiwyg']++;

?>