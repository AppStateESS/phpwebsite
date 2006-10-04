<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


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
        $installed_mods = PHPWS_Core::installModList();

        if (PHPWS_Core::isBranch()) {
            $branch_mods = Branch::getBranchMods();
            if (empty($branch_mods)) {
                $dir_mods = array();
            } else {
                $dir_mods = $branch_mods;
            }
        } else {
            $all_mods = PHPWS_File::readDirectory(PHPWS_SOURCE_DIR . 'mod/', TRUE);
            $all_mods = array_diff($all_mods, $core_mods);
            foreach ($all_mods as $key=> $module) {
                if (is_file(PHPWS_SOURCE_DIR . 'mod/' . $module . '/boost/boost.php')) {
                    $dir_mods[] = $module;
                }
            }
        }

        if ($type == 'core_mods') {
            $allowUninstall = FALSE;
            $modList = $core_mods;
            
            $core_file = PHPWS_Core::getVersionInfo();
            $core_db = PHPWS_Core::getVersionInfo(false);

            $link_title = _('Check');
            $link_command['opmod'] = 'core';

            $template['TITLE']   = $core_file['proper_name'];
            $template['VERSION'] = $core_file['version'];

            $link_command['action'] = 'check';
            $core_links[] = PHPWS_Text::secureLink(_('Check'), 'boost', $link_command);

            if (version_compare($core_db['version'], $core_file['version'], '<')) {
                $link_command['action'] = 'update_core';
                $core_links[] = PHPWS_Text::secureLink(_('Update'), 'boost', $link_command);
                $template['VERSION'] =sprintf('%s &gt; %s', $core_db['version'], $core_file['version']); 
            }

            $template['COMMAND'] = implode(' | ', $core_links);
            $template['ROW']     = 1;
            $tpl['mod-row'][] = $template;

        } else {
            $allowUninstall = TRUE;
            $modList = $dir_mods;
        }

        $tpl['TITLE_LABEL'] = _('Module Title');
        $tpl['COMMAND_LABEL'] = ('Commands');
        $tpl['ABOUT_LABEL'] = _('More information');
        $tpl['VERSION_LABEL'] = _('Current version');
        
        if ($type == 'core_mods' && Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
            $tpl['WARNING'] = _('WARNING: Only deities can uninstall core modules. Doing so may corrupt your installation!');
        }

        if (empty($modList)) {
            return _('No modules available.');
        }

        sort($modList);
        $count = 1;
        foreach ($modList as $title) {
            $template = $link_command = NULL;
            $link_command['opmod'] = $title;
            if ($title == 'core') {
                $mod = PHPWS_Core::loadAsMod();
            } else {
                $mod = & new PHPWS_Module($title);
            }

            if (!$mod->isFullMod()) {
                continue;
            }
            $proper_name = $mod->getProperName();
            if (!isset($proper_name)) {
                $proper_name = $title;
            }

            $template['VERSION'] = $mod->version;
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
                    $db_mod = &new PHPWS_Module($mod->title, false);
                    $template['VERSION'] = $db_mod->version . ' &gt; ' . $mod->version;
                    if ($mod->checkDependency()) {
                        if ($title == 'boost') {
                            $tpl['WARNING'] = _('Boost requires updating! You should do so before any other module!');
                        }
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
                $address = PHPWS_Text::linkAddress('boost',
                                                   array('action' => 'aboutView', 'aboutmod'=> $mod->title),
                                                   true);
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