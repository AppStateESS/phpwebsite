<?php

class Boost_Form {

    function boostTab(&$panel){
        if (!isset($_REQUEST['tab']))
            return $panel->getCurrentTab();
        else
            return $_REQUEST['tab'];
    }

    function setTabs(&$panel){
        $link = _('index.php?module=boost&amp;action=admin');
    
        $core_links['title'] = _('Core Modules');
        $other_links['title'] = _('Other Modules');

        $other_links['link'] = $core_links['link']  = $link;

        $tabs['core_mods'] = $core_links;
        $tabs['other_mods'] = $other_links;

        $panel->quickSetTabs($tabs);
    }

    function listModules($type){
        Layout::addStyle('boost');
        PHPWS_Core::initCoreClass('Module.php');
        PHPWS_Core::initCoreClass('Text.php');
        PHPWS_Core::initCoreClass('File.php');
        PHPWS_Core::initModClass('boost', 'Boost.php');

        $allow_update = TRUE;

        $dir_content = array();
        if (!PHPWS_Boost::checkDirectories($dir_content)) {
            $tpl['DIRECTORIES'] = implode('<br />', $dir_content);
            $allow_update = FALSE;
        }

        $core_mods      = PHPWS_Core::coreModList();
        $dir_mods       = PHPWS_File::readDirectory(PHPWS_SOURCE_DIR . 'mod/', TRUE);
        $installed_mods = PHPWS_Core::installModList();

        foreach ($core_mods as $core_title){
            unset($dir_mods[array_search($core_title, $dir_mods)]);
        }

        if ($type == 'core_mods'){
            $allowUninstall = FALSE;
            $modList = $core_mods;
        } else {
            $allowUninstall = TRUE;
            $modList = $dir_mods;
        }

        $tpl['TITLE_LABEL'] = _('Module Title');
        $tpl['COMMAND_LABEL'] = ('Commands');
        $tpl['ABOUT_LABEL'] = _('More information');
        
        if ($type == 'core_mods' && Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
            $tpl['WARNING'] = _('WARNING: Only deities can uninstall core modules. Doing so may corrupt your installation!');
        }

        sort($modList);
        $count = 0;
        foreach ($modList as $title) {
            $template = $link_command = NULL;
            $link_command['opmod'] = $title;
            $mod = & new PHPWS_Module($title);
            if (!$mod->isFullMod()) {
                continue;
            }
            $proper_name = $mod->getProperName();
            if (!isset($proper_name)) {
                $proper_name = $title;
            }

            $template['TITLE'] = $proper_name;
            $template['ROW'] = ($count % 2) + 1;
            if (!$mod->isInstalled()){
                if ($mod->checkDependency()) {
                    $link_title = _('Install');
                    $link_command['action'] = 'install';
                } else {
                    $link_title = _('Missing dependency');
                    $link_command['action'] = 'show_dependency';
                }
            } else {
                if ($type != 'core_mods' || Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
                    if ($dependents = $mod->isDependedUpon()) {
                        $link_command['action'] = 'show_depended_upon';
                        $depend_warning = sprintf(_('This module is depended upon by: %s'), implode(', ', $dependents));
                        $template['UNINSTALL'] = PHPWS_Text::secureLink(_('Depended upon'), 'boost', $link_command, NULL, $depend_warning);
                    } else {
                        $uninstallVars = array('opmod'=>$title, 'action'=>'uninstall');
                        $js['QUESTION'] = _('Are you sure you want to uninstall this module? All data will be deleted.');
                        $js['ADDRESS'] = PHPWS_Text::linkAddress('boost', $uninstallVars, TRUE);
                        $js['LINK'] = _('Uninstall');
                        $template['UNINSTALL'] = javascript('confirm', $js);
                    }
                }

                if ($mod->needsUpdate()) {
                    if ($mod->checkDependency()) {
                        $link_title = _('Update');
                        $link_command['action'] = 'update';
                    } else {
                        $link_title = _('Missing dependency');
                        $link_command['action'] = 'show_dependency';
                    }
                } else {
                    $version_check = $mod->getVersionHttp();
          
                    if (isset($version_check)){
                        $link_title = _('Check');
                        $link_command['action'] = 'check';
                    } else
                        $link_title = _('No Action');
                }
            }

            if ($mod->isAbout()){
                $address = 'index.php?module=boost&amp;action=aboutView&amp;aboutmod=' . $mod->getTitle();
                $aboutView = array('label'=>_('About'), 'address'=>$address);
                $template['ABOUT'] = Layout::getJavascript('open_window', $aboutView);
            }

            if (isset($link_command['action']) && $allow_update){
                $template['COMMAND'] = PHPWS_Text::secureLink($link_title, 'boost', $link_command);
            } else
                $template['COMMAND'] = $link_title;
      
            $tpl['mod-row'][] = $template;
            $count++;
        }

   
        $result = PHPWS_Template::process($tpl, 'boost', 'module_list.tpl');
        return $result;
    }
}

?>