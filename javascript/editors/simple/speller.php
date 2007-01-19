<?php

session_start();

$html   = array();
$html[] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
$html[] = "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\">";
$html[] = "<head>";
$html[] = "<title>Spell Checker</title>";

if(isset($_REQUEST["style"])) {
  $html[] = "<link rel=\"stylesheet\" href=\"{$_REQUEST['style']}\" type=\"text/css\" />";  
}

$html[] = "<script type=\"text/javascript\">";
$html[] = "//<![CDATA[";
$html[] = "var element = null;\n";
$html[] = "function suggest(id, possibilities) {";
$html[] = "   element = document.getElementById(id);";
$html[] = "   var suggestion = document.getElementById('suggestion');";
$html[] = "   var suggestions = document.getElementById('suggestions');\n";
$html[] = "   suggestions.options.length = 0;";
$html[] = "   if(possibilities.length == 0) {";
$html[] = "      suggestions.options[0] = new Option('No Suggestions', 'No Suggestions');";
$html[] = "      suggestion.value = element.value;";
$html[] = "   } else {";
$html[] = "      for(i = 0; i < possibilities.length; i++) {";
$html[] = "         suggestions.options[i] = new Option(possibilities[i], possibilities[i]);";
$html[] = "      }";
$html[] = "      suggestions.options[0].selected = true;";
$html[] = "      suggestion.value = possibilities[0];";
$html[] = "      suggestion.focus();";
$html[] = "   }";
$html[] = "}\n";
$html[] = "function swap() {";
$html[] = "   var suggestion = document.getElementById('suggestion');";
$html[] = "   var suggestions = document.getElementById('suggestions');\n";
$html[] = "   suggestion.value = suggestions.options[suggestions.selectedIndex].value;";
$html[] = "}\n";
$html[] = "function repair() {";
$html[] = "   var suggestion = document.getElementById('suggestion');";
$html[] = "   if(element) {";
$html[] = "      element.value = suggestion.value;";
$html[] = "   } else {";
$html[] = "      alert('No replacement to be done.');";
$html[] = "   }";
$html[] = "}\n";
$html[] = "function gettext() {";
$html[] = "   var section = opener.document.getElementById('{$_REQUEST['section']}');";
$html[] = "   var hidden = document.getElementById('text');";
$html[] = "   hidden.value = section.value;";
$html[] = "   var form = document.getElementById('ssc');";
$html[] = "   form.submit();";
$html[] = "}\n";
$html[] = "function settext(text) {";
$html[] = "   var section = opener.document.getElementById('{$_REQUEST['section']}');";
$html[] = "   var hidden = document.getElementById('text');";
$html[] = "   section.value = hidden.value.replace(/\[\[new\]\]/g, \"\\r\\n\");";
$html[] = "   window.close();";
$html[] = "}\n";
$html[] = "//]]>";
$html[] = "</script>\n";
$html[] = "<style type=\"text/css\">";
$html[] = "body {";
$html[] = "   padding: 4px;";
$html[] = "}\n";
$html[] = "select {";
$html[] = "   width: 12em;";
$html[] = "}\n";
$html[] = ".clear {";
$html[] = "   clear:both;";
$html[] = "}\n";
$html[] = "#changebox {";
$html[] = "   float: right;";
$html[] = "   border-style: solid;";
$html[] = "   border-width: thin;";
$html[] = "   padding: 4px;";
$html[] = "   margin-bottom: 1em;";
$html[] = "}\n";
$html[] = "</style>";
$html[] = "</head>";

$entitiesPattern = array("&#39;",
			 "&#x0024;",
			 "&#x0022;");
$entitiesReplace = array('\'',
			 '\$',
			 '\"');


if(isset($_REQUEST['finished'])) {
  /* put text back together here */
  if(isset($_REQUEST['word']) && is_array($_REQUEST['word'])) {
    foreach($_REQUEST['word'] as $id => $value) {
      $_SESSION['text'][$id] = $value;
    }
  }

  $text = implode("", $_SESSION['text']);

  $html[] = "<body onload=\"settext();\">";
  $html[] = "<p>Setting text please be patient...</p>";
  $html[] = "<textarea id=\"text\" name=\"text\">$text</textarea>";

  $_SESSION['text'] = null;
  unset($_SESSION['text']);

} else if(!isset($_REQUEST['text'])) {

  $html[] = "<body onload=\"gettext();\">";
  $html[] = "<form id=\"ssc\" method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">";
  $html[] = "<input id=\"section\" type=\"hidden\" name=\"section\" value=\"{$_REQUEST['section']}\" />";

  if(isset($_REQUEST["style"])) {
    $html[] = "<input id=\"style\" type=\"hidden\" name=\"style\" value=\"{$_REQUEST['style']}\" />";
  }

  $html[] = "<input id=\"text\" type=\"hidden\" name=\"text\" value=\"\" />";
  $html[] = "</form>";

} else {

  $html[] = "<body>";
  $html[] = "<h3>Spell Checker</h3>";
  $html[] = "<hr class=\"clear\" width=\"100%\" />";
  
  $_SESSION['text'] = $_REQUEST['text'];
  sscParser($_SESSION['text']);

  if(!isset($ssc_lang)) {
    $lang = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    $ssc_lang = $lang[0];
  }
  
  if(!isset($ssc_speed)) {
    $ssc_speed = PSPELL_FAST;
  }
  
  $pspell = pspell_new($ssc_lang, null, null, null, $ssc_speed);
  
  $output = array();
  $error  = false;

  $htmlStart = array("<",
		     "&");

  foreach($_SESSION['text'] as $id => $word) {      
    if($word == "[[new]]") {
      $output[] = "<br />";
    } else if(is_numeric($word[0])) {
      $output[] = $word;
    } else if(in_array($word[0], $htmlStart)) {
      $output[] = $word;
      
    } else {
      if(stristr($word, "[entity]")) {
	$word = convertEntity($word, $entitiesPattern, $entitiesReplace);
	$entity = TRUE;
      } else {
	$entity = FALSE;
      }

      if(pspell_check($pspell, $word)) {
	$output[] = $word;
      } else {
	$error       = true;
	$suggestions = pspell_suggest($pspell, $word);
	$size        = sizeof($suggestions);
	for($i = 0; $i < $size; $i++) {
	  $suggestions[$i] = "'".addslashes($suggestions[$i])."'";
	}
	$suggestions = implode(", ", $suggestions);
	
	$output[] = "<input id=\"$id\" type=\"text\" name=\"word[{$id}]\" size=\"10\" maxlength=\"255\" value=\"{$word}\" />\n";
	$output[] = "<input type=\"button\" value=\"&#160;&#63;&#160;\" onclick=\"suggest('{$id}', new Array({$suggestions}));\" />\n";
      }

      if($entity) {
	$_SESSION["text"][$id] = reconvertEntity($_SESSION["text"][$id], $entitiesPattern, $entitiesReplace);
      }
           
    }
  }

  $html[] = "<form id=\"ssc\" method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">";
  $html[] = "<input id=\"section\" type=\"hidden\" name=\"section\" value=\"{$_REQUEST['section']}\" />";
  $html[] = "<div id=\"changebox\" class=\"bg_light\">";
  $html[] = "<b>Change to:</b><br />";
  $html[] = "<input id=\"suggestion\" type=\"text\" name=\"suggestion\" size=\"18\" maxlength=\"255\" value=\"\" /><br />";
  $html[] = "<b>Suggestions:</b><br />";
  $html[] = "<select id=\"suggestions\" size=\"6\" name=\"suggestions\" onchange=\"swap();\">";
  $html[] = "</select><br />\n";
  $html[] = "<input id=\"replace\" type=\"button\" value=\"Replace\" onclick=\"repair();\" />";
  $html[] = "</div>";
  $html[] = "<b>Text to check:</b><br />";
  $html[] = implode("", $output);

  if(!$error) {
    $html[] = "<br /><br />\n<span class=\"errortext\">No spelling errors found.</span>";
  }

  $html[] = "<hr class=\"clear\" width=\"100%\" /><input id=\"finished\" type=\"submit\" name=\"finished\" value=\"Done\" />";
  $html[] = "</form>";
}

$html[] = "</body>";
$html[] = "</html>";

echo implode("\n", $html);

function sscParser(&$text) {
  $patterns = array(
		 "'(<[\/\!]*?[^<>]*?>)'si",
		 "/\?/",
		 "/\!/",
		 "/\(/",
		 "/\)/",
		 "/(?:\r\n|\r|\n)/",
		 "'(&(nbsp|#160);)'i",
		 "'(&(quot|#34);)'i",
		 "'(&(amp|#38);)'i",
		 "'(&(lt|#60);)'i",
		 "'(&(gt|#62);)'i");

  $replacements = array("[ssc]\${1}\$3[ssc]",
			"[ssc]?[ssc]",
			"[ssc]![ssc]",
			"[ssc]([ssc]",
			"[ssc])[ssc]",
			"[ssc][[new]][ssc]",
			"[ssc]\${1}\$3[ssc]",
			"[ssc]\${1}\$3[ssc]",
			"[ssc]\${1}\$3[ssc]",
			"[ssc]\${1}\$3[ssc]",
			"[ssc]\${1}\$3[ssc]");

  $text = preg_replace($patterns, $replacements, $text);

  $text = explode("[ssc]", $text);

  $text = array_filter($text, "nothing");
  
  $htmlStart = array("<",
		     "&");

  $compArr = array();
  foreach($text as $wordOrHTML) {
    if(!in_array($wordOrHTML[0], $htmlStart)) {
      $patterns     = array("/ /",
			    "/\./",
			    "/,/",
			    "/\:/",
			    "/\"/",
			    "/-/",
			    "/@/",
			    "'([0-9]+^;)'");

      $replacements = array("[ssc] [ssc]",
			    "[ssc].[ssc]",
			    "[ssc],[ssc]",
			    "[ssc]:[ssc]",
			    "[ssc]\"[ssc]",
			    "[ssc]-[ssc]",
			    "[ssc]@[ssc]",
			    "[ssc]\${1}\$3[ssc]");


      $words = preg_replace($patterns, $replacements, $wordOrHTML);

      $wordsArr = explode("[ssc]", $words);
      foreach($wordsArr as $word) {
	if(stristr($word, ';') && stristr($word, '&')) {
	  // html entity
	  $word = str_replace("&", "[entity]&", $word);
	  $word = str_replace(";", ";[entity]", $word);
	  $compArr[] = $word;

	} else if(stristr($word, ';')) {
	  // word contains a semicolon
	  $semi = str_replace(";", "[ssc];[ssc]", $word);
	  $semiArr = explode("[ssc]", $semi);
	  foreach($semiArr as $sword) {
	    $compArr[] = $sword;
	  }
	  
	} else { 
	  $compArr[] = $word;
	}
      }
    } else {
      $compArr[] = $wordOrHTML;
    }
  }
  $compArr = array_filter($compArr, "nothing");

  $text = $compArr;
}

function nothing($string) {
  return (strlen($string) > 0);
}

function convertEntity($string, $entitiesP, $entitiesR) {
  $sArr = explode("[entity]", $string);

  $workingString = "";
  foreach($sArr as $s) {
    $s = str_replace($entitiesP, $entitiesR, $s);
    $workingString .= $s;
  }

  return $workingString;
}

function reconvertEntity($string, $entitiesP, $entitiesR) {
  $sArr = explode("[entity]", $string);

  $string = $sArr[0] . str_replace($entitiesR, $entitiesP, $sArr[1]) . $sArr[2];
  return $string;
}
?>