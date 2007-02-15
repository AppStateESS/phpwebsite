<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Boost_Action {

    function checkupdate($mod_title)
    {
        translate('boost');
        PHPWS_Core::initCoreClass('Module.php');
        $module = new PHPWS_Module($mod_title);
    
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
        if (!isset($version_info['VERSION'])) {
            $template['STABLE_VERSION'] = _('Source XML error');
            $version_info['VERSION'] = $module->getVersion();
        } else {
            $_SESSION['Boost_Needs_Update'][$mod_title] = $version_info['VERSION'];
            $template['STABLE_VERSION'] = $version_info['VERSION'];
        } 

        if (version_compare($version_info['VERSION'], $module->getVersion(), '>')) {
            $template['CHANGES_LABEL'] = _('Changes');
            $template['CHANGES'] = PHPWS_Text::parseOutput($version_info['CHANGES']);
            $template['UPDATE_AVAILABLE'] = _('An update is available!') . '<br />';
            $template['UPDATE_PATH_LABEL'] = _('Download here');
            $template['UPDATE_PATH'] = '<a href="' . $version_info['DOWNLOAD'] . '">' . $version_info['DOWNLOAD'] . '</a>';
            $template['MD5_LABEL'] = _('MD5 Sum');
            $template['MD5'] = $version_info['MD5SUM'];

            if (isset($version_info['DEPENDENCY'][0]['MODULE'])) {
                $template['DEPENDENCY_LABEL'] = _('Dependencies');
                $template['DEP_TITLE_LABEL'] = _('Module title');
                $template['DEP_VERSION_LABEL'] = _('Version required');
                $template['DEP_STATUS_LABEL'] = _('Status');

                foreach ($version_info['DEPENDENCY'][0]['MODULE'] as $dep_mod) {
                    $check_mod = new PHPWS_Module($dep_mod['TITLE'], false);

                    if ($check_mod->_error) {
                        $status = _('Not installed');
                        $row['DEP_STATUS_CLASS'] = 'red';
                    } elseif (version_compare($check_mod->version, $dep_mod['VERSION'], '<')) {
                        $status = _('Needs upgrading');
                        $row['DEP_STATUS_CLASS'] = 'red';
                    } else {
                        $status = _('Passed!');
                        $row['DEP_STATUS_CLASS'] = 'green';
                    }
                    $row['DEP_TITLE'] = $dep_mod['PROPERNAME'];
                    $row['DEP_VERSION'] = $dep_mod['VERSION'];
                    $row['DEP_ADDRESS'] = sprintf('<a href="%s">%s</a>',
                                                  $dep_mod['URL'], _('Download'));
                    $row['DEP_STATUS'] = $status;
                    $template['dependent-mods'][] = $row;
                }
            }
        }
        else {
            $template['NO_UPDATE'] = _('No update required.');
        }

        $template['TITLE'] = _('Module') . ': ' . $module->getProperName(TRUE);
        translate();
        return PHPWS_Template::process($template, 'boost', 'check_update.tpl');
    }

    function installModule($module_title)
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
    
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title));
        return $boost->install();
    }

    function uninstallModule($module_title)
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
    
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title));

        $content = $boost->uninstall();

        return $content;
    }

    function updateCore()
    {
        translate('boost');
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $content[] = _('Updating core');

        require_once PHPWS_SOURCE_DIR . 'core/boost/update.php';

        $ver_info = PHPWS_Core::getVersionInfo(false);

        $content[] = _('Processing update file.');
        $result = core_update($content, $ver_info['version']);

        if ($result === true) {
            $db = new PHPWS_DB('core_version');
            $file_ver = PHPWS_Core::getVersionInfo();
            $db->addValue('version', $file_ver['version']);
            $result = $db->update();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('An error occurred updating the core.');
            } else {
                $content[] = _('Core successfully updated.');
            }
        } elseif (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('An error occurred updating the core.');
        } else {
            $content[] = _('An error occurred updating the core.');
        }
        translate();
        return implode('<br />', $content);
    }

    function updateModule($module_title)
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title), FALSE);

        $content = array();
        if ($boost->update($content)) {
            $boost->updateBranches($content);
        }
        return implode('<br />', $content);    
    }

    function showDependedUpon($base_mod)
    {
        translate('boost');
        PHPWS_Core::initCoreClass('Module.php');
        $module = new PHPWS_Module($base_mod);
        $dependents = $module->isDependedUpon();
        if (empty($dependents)) {
            return _('This module does not have dependents.');
        }

        $template['TITLE'] = sprintf(_('%s Dependencies'), $module->getProperName());

        $content[] = _('The following modules depend on this module to function:');
        foreach ($dependents as $mod) {
            $dep_module = new PHPWS_Module($mod);
            $content[] = $dep_module->getProperName();
        }
        
        $template['CONTENT'] = implode('<br />', $content);
        translate();
        return PHPWS_Template::process($template, 'boost', 'main.tpl');
    }

    function showDependency($base_module_title)
    {
        translate('boost');
        PHPWS_Core::initCoreClass('Module.php');
        $module = new PHPWS_Module($base_module_title);
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

            $mod_obj = new PHPWS_Module($module['TITLE'], FALSE);

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

            $tpl['URL'] = sprintf('<a href="%s" target="_blank">%s</a>', $module['URL'], _('More info'));

            if ($pass) {
                $tpl['STATUS_GOOD'] = _('Passed!');
            } else {
                $tpl['STATUS_BAD'] = _('Failed');
            }
            $template['module-row'][] = $tpl;
        }
        translate();
        return PHPWS_Template::process($template, 'boost', 'dependency.tpl');
    }

    /**
     * Checks all modules for update status
     */
    function checkAll()
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $all_mods = PHPWS_Boost::getAllMods();
        if (empty($all_mods)) {
            return;
        }

        PHPWS_Core::initCoreClass('Module.php');

        $all_mods[] = 'core';

        foreach ($all_mods as $mod_title) {
            $module = new PHPWS_Module($mod_title);
            $file = $module->getVersionHttp();
            if (empty($file)) {
                continue;
            }
            
            $full_xml_array = PHPWS_Text::xml2php($file, 2);

            if (empty($full_xml_array)) {
                continue;
            }

            $version_info = PHPWS_Text::tagXML($full_xml_array);
            if (empty($version_info) || empty($version_info['VERSION'])) {
                continue;
            }
  
            $_SESSION['Boost_Needs_Update'][$mod_title] = $version_info['VERSION'];
        }
    }
}

?>