<?php
if (!defined("PHPWS_SOURCE_DIR"))
     define("PHPWS_SOURCE_DIR", "./");

/* Initialize language settings */
if (!function_exists("bindtextdomain")){
  define("PHPWS_TRANSLATION", FALSE);
} else {
  define("PHPWS_TRANSLATION", TRUE);
  translate("core");
  initLanguage();
  bindtextdomain("messages", "./locale");
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


  $language_set = FALSE;
  $language = DEFAULT_LANGUAGE;

  if (isset($_COOKIE["phpws_default_language"])){
    $language = $_COOKIE["phpws_default_language"];
    setlocale(LC_ALL, $language);
    $language_set = TRUE;
  }
  elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
    $userLang = explode(",", preg_replace("/(;q=\d\.*\d*)/", "", $_SERVER['HTTP_ACCEPT_LANGUAGE']));
    foreach ($userLang as $language){
      if (strlen($language) == 2)
	$language = doubleLanguage($language);


      $newLocale =  setlocale(LC_ALL, $language);
      if ($newLocale != FALSE){
	setcookie("", $language, CORE_COOKIE_TIMEOUT);
	$language_set = TRUE;
	break;
      } else {
	$language = $language . "_" . strtoupper($language);
	$newLocale =  setlocale(LC_ALL, $language);
	if ($newLocale != FALSE){
	  setcookie("phpws_default_language", $language, CORE_COOKIE_TIMEOUT);
	  $language_set = TRUE;
	  break;
	}
      }
    }
  }

  if ($language_set == FALSE)
    setlocale(LC_ALL, $language);

  loadLanguageDefaults($language);

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
  if (PHPWS_TRANSLATION)
    bindtextdomain("messages", "./locale/" . $module);
}


?>