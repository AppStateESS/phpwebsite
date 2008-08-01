<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!(strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) !== false)) {
    forwardInfo();
}

if (!defined('UTF8_MODE')) {
    define ('UTF8_MODE', false);
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
    $continue = 1;
    $i = 0;
    while(isset($aUrl[$i])) {
        $key = $aUrl[$i];
        $i++;
        if (isset($aUrl[$i])) {
            $value = $aUrl[$i];
            if (preg_match('/&/', $value)) {
                $remain = explode('&', $value);
                $j = 1;
                $value = $remain[0];
                while (isset($remain[$j])) {
                    $sub = explode('=', $remain[$j]);
                    $_REQUEST[$sub[0]] = $_GET[$sub[0]] = $sub[1];
                    $j++;
                }
            }

            $_GET[$key] = $_REQUEST[$key] = $value;
        }
        $i++;
    }
}

?>