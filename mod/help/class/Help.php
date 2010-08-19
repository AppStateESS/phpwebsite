<?php
/**
 * @version $Id$
 * @author Jeremy Agee
 * @modifed Matthew McNaney
 */

$config = PHPWS_Core::getConfigFile('help', 'config.php');

if (PHPWS_Error::isError($config)){
    PHPWS_Error::log($config);
} else {
    include_once $config;
}

class PHPWS_Help{

    public static function show_link($module, $help, $label=NULL){
        if (!isset($label)) {
            $label = DEFAULT_HELP_LABEL;
        }

        $vars['label'] = $label;
        $vars['address'] = 'index.php?module=help&amp;pre=1&amp;helpMod=' . $module . '&amp;option=' . $help;
        $link = Layout::getJavascript('open_window', $vars);
        $result = PHPWS_Template::process(array('LINK'=> $link), 'help', 'link.tpl');

        return $result;
    }

    public static function get($module, $help, $label=NULL)
    {
        if (!isset($label))
        $label = DEFAULT_HELP_LABEL;

        $vars['label'] = $label;
        $vars['address'] = 'index.php?module=help&amp;helpMod=' . $module . '&amp;option=' . $help;
        $link = Layout::getJavascript('open_window', $vars);
        $result = PHPWS_Template::process(array('LINK'=> $link), 'help', 'link.tpl');

        return $result;
    }

    public static function show_help(){
        if (!isset($_REQUEST['helpMod'])){
            exit();
        }

        $module = $_REQUEST['helpMod'];
        $option = strtolower($_REQUEST['option']);

        $filename = PHPWS_SOURCE_DIR . sprintf('mod/%s/conf/help.%s.ini', $module, CURRENT_LANGUAGE);
        $default = PHPWS_SOURCE_DIR . sprintf('mod/%s/conf/help.ini', $module);

        if (is_file($filename)) {
            $help_info = @parse_ini_file($filename, TRUE);
        } elseif (is_file($default)) {
            $help_info = @parse_ini_file($default, TRUE);
        } else {
            echo dgettext('help', 'No help file exists for this module.');
            exit();
        }


        if (!isset($help_info[$option])) {
            echo dgettext('help', 'No help exists for this topic.');
        }

        if (isset($help_info[$option]['title'])) {
            Layout::addPageTitle($help_info[$option]['title']);
            $template['TITLE'] = $help_info[$option]['title'];
        }

        if (isset($help_info[$option]['content'])) {
            $template['CONTENT'] = $help_info[$option]['content'];
        }

        $content = PHPWS_Template::process($template, 'help', 'help.tpl');

        Layout::nakedDisplay($content);
    }

}

?>