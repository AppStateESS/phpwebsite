<?php
if (!defined("PHPWS_SOURCE_DIR"))
     define("PHPWS_SOURCE_DIR", "./");

if (!defined("PHPWS_SOURCE_HTTP"))
     define("PHPWS_SOURCE_HTTP", "./");

/* Initialize language settings */
if (!function_exists("bindtextdomain")){
  define("PHPWS_TRANSLATION", FALSE);
  function _($text) {
    return $text;
  }
} else {
  define("PHPWS_TRANSLATION", TRUE);
  initLanguage();
  translate("core");
  textdomain("messages");
}

/* Load the Core class */
require_once PHPWS_SOURCE_DIR . "core/class/Core.php";
require_once PHPWS_Core::getConfigFile("core", "errorDefines.php");

/***** PHPWS Classes ****/
PHPWS_Core::initCoreClass("Database.php");
PHPWS_Core::initCoreClass("Text.php");
PHPWS_Core::initCoreClass("Item.php");
PHPWS_Core::initCoreClass("Debug.php");
PHPWS_Core::initCoreClass("Error.php");

if (PHPWS_TRANSLATION == FALSE)
     PHPWS_Core::initCoreClass("Crutch.php");

if (!defined("USE_ROOT_CONFIG"))
     define("USE_ROOT_CONFIG", FALSE);

/* Initializes language */
function initLanguage(){
  if (!defined("DEFAULT_LANGUAGE"))
    define("DEFAULT_LANGUAGE", "en");

  if (!defined("CORE_COOKIE_TIMEOUT"))
    define("CORE_COOKIE_TIMEOUT", 3600);

  if (isset($_COOKIE["phpws_default_language"])){
    $language = $_COOKIE["phpws_default_language"];
    $locale = setlocale(LC_ALL, $language);
    if ($locale == FALSE)
      $locale = setlocale(LC_ALL, DEFAULT_LANGUAGE);
  } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
    $userLang = explode(",", preg_replace("/(;q=\d\.*\d*)/", "", $_SERVER['HTTP_ACCEPT_LANGUAGE']));
    $locale_found = FALSE;
    
    foreach ($userLang as $language){
      $locale = setlocale(LC_ALL, $language);
      if ($locale == FALSE && strlen($language) == 2){
	$locale = setlocale(LC_ALL, doubleLanguage($language));

	if ($locale != FALSE){
	  $locale_found = TRUE;
	  setcookie("phpws_default_language", $locale, CORE_COOKIE_TIMEOUT);
	  break;
	}
      }
    }

    if ($locale_found == FALSE){
      $locale = setlocale(LC_ALL, DEFAULT_LANGUAGE);
    }
  }

  if ($locale != FALSE)
    define("CURRENT_LANGUAGE", $locale);
  else
    define("CURRENT_LANGUAGE", DEFAULT_LANGUAGE);

  loadLanguageDefaults($locale);
}

function loadLanguageDefaults($language){
  $rootDir = "config/core/i18n/";

  if (is_file($rootDir . $language . ".php")){
    require_once $rootDir . $language . ".php";
  }
  else {
    $rootLanguage = explode("_", $language);
    if (is_file($rootDir . $rootLanguage . "_default.php"))
      require_once $rootDir . $rootLanguage . "_default.php";
    else
      require_once $rootDir . "default.php";
  }
}

function doubleLanguage($language){
  return $language . "_" . strtoupper($language);
}

function translate($module){
  if (!defined("PHPWS_TRANSLATION") || !PHPWS_TRANSLATION)
    return;

  if ($module == "core")
    $directory = PHPWS_SOURCE_DIR . "locale";
  else
    $directory = PHPWS_SOURCE_DIR . "mod/$module/locale";

  $final = bindtextdomain("messages", $directory);
  textdomain("messages");
}


?>