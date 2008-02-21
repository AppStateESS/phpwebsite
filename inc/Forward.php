<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!(strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) !== false)) {
    $url =  PHPWS_Core::getCurrentUrl();

    if ($url == 'index.php') {
        return;
    }

    $aUrl = explode('/', $url);
    $module = array_shift($aUrl);


    if (strpos($module, '.html')) {
        $forward = str_replace('.html', '', $module);

        if (UTF8_MODE) {
            $preg = '/[^\w\-\pL]/ui';
        } else {
            $preg = '/[^\w-/ui';
        }

        $GLOBALS['Forward']= preg_replace($preg, '', $forward);
        return;
    }

    if (preg_match('/[^\w\-]/', $module)) {
        return;
    }

    $_REQUEST['module'] = $_GET['module'] = & $module;

    if (UTF8_MODE) {
        $preg = '/[\w\pL]/ui';
    } else {
        $preg = '/[\w]/ui';
    }
    $count = 1;
    foreach ($aUrl as $var) {
        if (!empty($var) && preg_match($preg, $var)) {
            $varname = 'var' . $count;
            $_GET[$varname] = $var;
            $count++;
        }
    }
}

?>