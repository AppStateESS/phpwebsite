<?php

class PHPWS_Error {

  function &get($value, $module, $funcName=NULL, $extraInfo=NULL){
    $errorFile = PHPWS_Core::getConfigFile($module, "error.php");

    if (PEAR::isError($errorFile))
      return PHPWS_Error::get(PHPWS_NO_ERROR_FILE, "core", "PHPWS_Error::get", "Module: $module");

    include $errorFile;
    if (!isset($errors))
      return FALSE;

    if (PEAR::isError($value))
      $value = $value->getCode();

    if ($module != "core")
      $fullError[] = $module;
    else
      $fullError[] = "Core";

    if (isset($funcName))
      $fullError[] = "::$funcName()";

    if (isset($errors[$value]))
      $message = $errors[$value];
    else
      $message = $errors[PHPWS_UNKNOWN];

    $fullError[] = " - " . $message;

    if (isset($extraInfo)){
      if (is_array($extraInfo))
	vsprintf($message, $extraInfo);
      else
	$fullError[] = " [" . $extraInfo . "]";
    }

    $error = &PEAR::raiseError($message, $value, NULL, NULL, implode("", $fullError));

    return $error;
  }

  function log($value, $module=NULL, $funcName=NULL, $extraInfo=NULL){
    if ((bool)PHPWS_LOG_ERRORS == FALSE)
      return;

    if (!PEAR::isError($value)) 
      $error = &PHPWS_Error::get($value, $module, $funcName, $extraInfo);
    else
      $error = $value;

    $final = PHPWS_Error::printError($error);

    PHPWS_Core::log($final, "error.log", _("Error"));
  }


  function printError($error){
    $code  = $error->getcode();
    $message = $error->getuserinfo();
    
    if (!isset($message))
      $message = $error->getmessage();
    
    $final = "[" . $code . "] $message"; 

    return $final;
  }

}

?>