<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!(strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) !== false)) {
    forwardInfo();
}

function forwardInfo()
{
    $url =  PHPWS_Core::getCurrentUrl();

    if ($url == 'index.php') {
        return;
    }

    if (UTF8_MODE) {
        $preg = '/[^\w\-\pL]/u';
    } else {
        $preg = '/[^\w\-]/';
    }

    $aUrl = explode('/', $url);
    $module = array_shift($aUrl);
   
    $mods = PHPWS_Core::getModules(true, true);

    if (!in_array($module, $mods)) {
        $GLOBALS['Forward'] = $module;
        return;
    }

    if (preg_match('/[^\w\-]/', $module)) {
        return;
    }

    $_REQUEST['module'] = $_GET['module'] = & $module;

    $count = 1;
    foreach ($aUrl as $var) {
        if (!empty($var) && !preg_match($preg, $var)) {
            $varname = 'var' . $count;
            $_GET[$varname] = $var;
            $count++;
        }
    }

}



?>