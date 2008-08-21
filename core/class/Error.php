<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

require_once 'PEAR.php';

class PHPWS_Error {
    public function isError($item){
        return PEAR::isError($item);
    }

    public function logIfError($item)
    {
        if (PEAR::isError($item)) {
            PHPWS_Error::log($item);
            return true;
        } else {
            return false;
        }
    }

    public function get($value, $module, $funcName=NULL, $extraInfo=NULL){
        setLanguage(DEFAULT_LANGUAGE);

        $errorFile = PHPWS_Core::getConfigFile($module, 'error.php');
        if (empty($module)) {
            return PHPWS_Error::get(PHPWS_NO_MODULE, 'core', 'PHPWS_Error::get', 'Value: ' . $value . ', Function: ' . $funcName);
        }

        if (!($errorFile)) {
            // prevent infinite loop
            if ($module == 'core') {
                echo _('Core could not locate its errorDefines.php file.');
                die;
            }
            return PHPWS_Error::get(PHPWS_NO_ERROR_FILE, 'core', 'PHPWS_Error::get', 'Module: ' . $module);
        }

        include $errorFile;
        if (!isset($errors))
            return FALSE;

        if (PEAR::isError($value)) {
            $value = $value->getCode();
        }

        if ($module != 'core') {
            $fullError[] = $module;
        } else {
            $fullError[] = 'Core';
        }

        if (isset($funcName)) {
            $fullError[] = " - $funcName()";
        }

        if (isset($errors[$value])) {
            $message = $errors[$value];
        } else {
            $message = _('Unknown error code.');
        }

        $fullError[] = ' - ' . $message;

        if (isset($extraInfo)){
            if (is_array($extraInfo)) {
                $message = vsprintf($message, $extraInfo);
            } else {
                $fullError[] = ' [' . $extraInfo . ']';
            }
        }

        $error = PEAR::raiseError($message, $value, NULL, NULL, implode('', $fullError));
        setLanguage(CURRENT_LANGUAGE);
        return $error;
    }

    public function log($value, $module=NULL, $funcName=NULL, $extraInfo=NULL){
        if ((bool)PHPWS_LOG_ERRORS == FALSE) {
            return;
        }

        if (!PEAR::isError($value)) {
            $error = PHPWS_Error::get($value, $module, $funcName, $extraInfo);
        }
        else {
            $error = $value;
        }

        $final = PHPWS_Error::printError($error);

        PHPWS_Core::log($final, 'error.log', _('Error'));
    }


    public function printError($error){
        $code  = $error->getcode();
        $message = $error->getuserinfo();

        if (!isset($message)) {
            $message = $error->getmessage();
        }

        $final = '[' . $code . '] ' . $message;

        return $final;
    }
}

?>