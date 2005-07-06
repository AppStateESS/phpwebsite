<?php

class Boost_Action {

    function checkupdate($mod_title){
        PHPWS_Core::initCoreClass('Module.php');
        $module = & new PHPWS_Module($mod_title);

        $file = $module->getVersionHttp();
        if (empty($file)) {
            return _('Update check file not found.');
        }

        $full_xml_array = PHPWS_Text::xml2php($file, 2);

        if (empty($full_xml_array)) {
            return _('Update check file not found.');
        }
        $version_info = PHPWS_Text::tagXML($full_xml_array);

        $template['LOCAL_VERSION_LABEL'] = _('Local version');
        $template['LOCAL_VERSION'] = $module->getVersion();
        $template['STABLE_VERSION_LABEL'] = _('Current stable version');
        $template['STABLE_VERSION'] = $version_info['VERSION'];

        if (version_compare($version_info['VERSION'], $module->getVersion(), '>')){
            $template['UPDATE_AVAILABLE'] = _('An update is available!') . '<br />';
            $template['UPDATE_PATH_LABEL'] = _('Download here');
            $template['UPDATE_PATH'] = '<a href="' . $version_info['DOWNLOAD'] . '">' . $version_info['DOWNLOAD'] . '</a>';
            $template['MD5_LABEL'] = _('MD5 Sum');
            $template['MD5'] = $version_info['MD5SUM'];
        }
        else {
            $template['NO_UPDATE'] = _('No update required.');
        }

        $template['TITLE'] = _('Module') . ': ' . $module->getProperName(TRUE);

        return PHPWS_Template::process($template, 'boost', 'check_update.tpl');
    }

    function installModule($module_title){
        PHPWS_Core::initModClass('boost', 'Boost.php');
    
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title));
        return $boost->install();
    }

    function uninstallModule($module_title){
        PHPWS_Core::initModClass('boost', 'Boost.php');
    
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title));

        $content = $boost->uninstall();

        return $content;
    }

    function updateModule($module_title){
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title), FALSE);
        $content = $boost->update();
        return $content;
    }

    function showDependency($base_module_title) {
        PHPWS_Core::initCoreClass('Module.php');
        $module = & new PHPWS_Module($base_module_title);
        $depend = $module->getDependencies();
        $template['TITLE'] = sprintf(_('%s Module Dependencies'), $module->getProperName());

        $template['MODULE_NAME_LABEL']     = _('Module Needed');
        $template['VERSION_NEEDED_LABEL']  = _('Version Needed');
        $template['CURRENT_VERSION_LABEL'] = _('Current Version');
        $template['URL_LABEL']             = _('Module Web Site');
        $template['STATUS_LABEL']          = _('Status');

        foreach ($depend['MODULE'] as $module) {
            
            $pass = TRUE;
            $tpl = array();
            $mod_obj = & new PHPWS_Module($module['TITLE'], FALSE);
            $tpl['MODULE_NAME']    = $module['PROPERNAME'];
            $tpl['VERSION_NEEDED'] = $module['VERSION'];

            if ($mod_obj->isInstalled()) {
                $tpl['CURRENT_VERSION'] = $mod_obj->getVersion();
            } else {
                $pass = FALSE;
                $tpl['CURRENT_VERSION'] = _('Not installed');
            }

            if ($pass && version_compare($module['VERSION'], $mod_obj->getVersion(), '>')) {
                $pass = FALSE;
            }

            $tpl['URL'] = sprintf('<a href="%s">%s</a>', $module['URL'], _('More info'));

            if ($pass) {
                $tpl['STATUS_GOOD'] = _('Passed!');
            } else {
                $tpl['STATUS_BAD'] = _('Failed');
            }
            $template['module-row'][] = $tpl;
        }

        return PHPWS_Template::process($template, 'boost', 'dependency.tpl');
    }
}

?>