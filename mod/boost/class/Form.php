<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Boost_Form {

    public function boostTab(PHPWS_Panel $panel)
    {
        if (!isset($_REQUEST['tab']))
            return $panel->getCurrentTab();
        else
            return $_REQUEST['tab'];
    }

    public function setTabs(PHPWS_Panel $panel)
    {
        $link = 'index.php?module=boost&amp;action=admin';

        $core_links['title'] = dgettext('boost', 'Core Modules');
        $other_links['title'] = dgettext('boost', 'Other Modules');

        $other_links['link'] = $core_links['link']  = $link;

        $tabs['core_mods'] = $core_links;
        $tabs['other_mods'] = $other_links;

        $panel->quickSetTabs($tabs);
    }

    public function listModules($type)
    {
        Layout::addStyle('boost');
        PHPWS_Core::initCoreClass('Module.php');
        PHPWS_Core::initCoreClass('Text.php');
        PHPWS_Core::initCoreClass('File.php');
        PHPWS_Core::initModClass('boost', 'Boost.php');

        $allow_update = true;
        $core_update_needed = false;

        $dir_content = array();
        if (!PHPWS_Boost::checkDirectories($dir_content)) {
            $tpl['DIRECTORIES'] = implode('<br />', $dir_content);
            $allow_update = false;
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
            $dir_mods = PHPWS_Boost::getAllMods();
        }

        $dir_mods = array_diff($dir_mods, $core_mods);

        if ($type == 'core_mods') {
            $allowUninstall = false;
            $modList = $core_mods;

            $core_file = new PHPWS_Module('core');
            $core_db   = new PHPWS_Module('core', false);

            $template['TITLE']   = $core_db->proper_name;
            $template['VERSION'] = $core_db->version;

            if (isset($_SESSION['Boost_Needs_Update']['core'])) {
                $link_title = $_SESSION['Boost_Needs_Update']['core'];
                if (version_compare($core_file->version, $_SESSION['Boost_Needs_Update']['core'], '<')) {
                    $link_title = sprintf(dgettext('boost', '%s - New'), $link_title);
                }
            } else {
                  $link_title = dgettext('boost', 'Check');
            }

            if ($core_file->isAbout()) {
                $address = PHPWS_Text::linkAddress('boost',
                                                   array('action' => 'aboutView', 'aboutmod'=> $core_file->title),
                                                   true);
                $aboutView = array('label'=>dgettext('boost', 'About'), 'address'=>$address);
                $template['ABOUT'] = Layout::getJavascript('open_window', $aboutView);
            }


            $link_command['opmod'] = 'core';
            $link_command['action'] = 'check';
            if (ini_get('allow_url_fopen')) {
                $template['LATEST'] = PHPWS_Text::secureLink($link_title, 'boost', $link_command);
            } else {
                $template['LATEST'] = dgettext('boost', 'Check disabled');
            }

            if (version_compare($core_db->version, $core_file->version, '<')) {
                if ($core_file->checkDependency()) {
                    if ($allow_update) {
                        $link_command['action'] = 'update_core';
                        $core_links[] = PHPWS_Text::secureLink(dgettext('boost', 'Update'), 'boost', $link_command);
                    } else {
                        $core_links[] = dgettext('boost', 'Update');
                    }
                    $tpl['WARNING'] = dgettext('boost', 'The Core requires updating! You should do so before any modules.');
                    $core_update_needed = true;
                } else {
                    $link_command['action'] = 'show_dependency';
                    $core_links[] = PHPWS_Text::secureLink(dgettext('boost', 'Missing dependency'), 'boost', $link_command);
                }

                $template['VERSION'] =sprintf('%s &gt; %s', $core_db->version, $core_file->version);
                $template['COMMAND'] = implode(' | ', $core_links);
            } elseif ($allow_update) {
                $js_file['QUESTION'] = dgettext('boost', 'Clicking OK will copy the core\\\'s configuration, image and (if on a branch site) javascript directories locally.\nNo backups will occur and all local files will be overwritten.\nAre you certain you want to do this?');
                $js_file['ADDRESS']  = PHPWS_Text::linkAddress('boost', array('opmod'=>'core', 'action'=>'copy_local'), true);
                $js_file['LINK'] = dgettext('boost', 'Revert');
                $template['COMMAND'] = javascript('confirm', $js_file);
            } else {
                $template['COMMAND'] = dgettext('boost', 'None');
            }


            $template['ROW']     = 1;
            $tpl['mod-row'][] = $template;
        } else {
            $allowUninstall = true;
            $modList = $dir_mods;
        }

        $tpl['TITLE_LABEL']   = dgettext('boost', 'Module Title');
        $tpl['COMMAND_LABEL'] = dgettext('boost', 'Commands');
        $tpl['ABOUT_LABEL']   = dgettext('boost', 'More information');
        $tpl['VERSION_LABEL'] = dgettext('boost', 'Current version');

        if ($type == 'core_mods' && Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
            $tpl['WARNING'] = dgettext('boost', 'WARNING: Only deities can uninstall core modules. Doing so may corrupt your installation!');
        }

        if (empty($modList)) {
            return dgettext('boost', 'No modules available.');
        }

        sort($modList);
        $count = 1;

        foreach ($modList as $title) {
            $links = array();
            $template = $link_command = NULL;
            $link_command['opmod'] = $title;

            $mod = new PHPWS_Module($title);

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

            $version_check = $mod->getVersionHttp();

            if (isset($version_check)) {
                if (isset($_SESSION['Boost_Needs_Update'][$mod->title])) {
                    $link_title = $_SESSION['Boost_Needs_Update'][$mod->title];
                    if (version_compare($mod->version, $_SESSION['Boost_Needs_Update'][$mod->title], '<')) {
                        $link_title = sprintf(dgettext('boost', '%s - New'), $link_title);
                    }
                } else {
                        $link_title = dgettext('boost', 'Check');
                }

                $link_command['action'] = 'check';
                if (ini_get('allow_url_fopen')) {
                    $template['LATEST'] = PHPWS_Text::secureLink($link_title, 'boost', $link_command);
                } else {
                    $template['LATEST'] = dgettext('boost', 'Check disabled');
                }
            }

            if (!$mod->isInstalled()) {
                if ($mod->checkDependency()) {
                    $link_title = dgettext('boost', 'Install');
                    $link_command['action'] = 'install';
                } else {
                    $link_title = dgettext('boost', 'Missing dependency');
                    $link_command['action'] = 'show_dependency';
                }

                if ($GLOBALS['Boost_Ready']) {
                    if (javascriptEnabled()) {
                        $js['width'] = 640;
                        $js['height'] = 480;
                        $js['address'] = PHPWS_Text::linkAddress('boost', $link_command, true);
                        $js['label'] = $link_title;
                        $links[] = javascript('open_window', $js);
                        unset($js);
                    } else {
                        $links[] = PHPWS_Text::secureLink($link_title, 'boost', $link_command);
                    }
                } else {
                    $links[] = & $link_title;
                }
            } else {
                if ($mod->needsUpdate()) {
                    $db_mod = new PHPWS_Module($mod->title, false);
                    $template['VERSION'] = $db_mod->version . ' &gt; ' . $mod->version;

                    if ($mod->checkDependency()) {
                        if ($title == 'boost' && !$core_update_needed) {
                            $tpl['WARNING'] = dgettext('boost', 'Boost requires updating! You should do so before any other module!');
                        }
                        $link_title = dgettext('boost', 'Update');
                        $link_command['action'] = 'update';
                    } else {
                        $link_title = dgettext('boost', 'Missing dependency');
                        $link_command['action'] = 'show_dependency';
                    }
                    if ($allow_update) {
                        $js['width'] = 640;
                        $js['height'] = 480;
                        $js['address'] = PHPWS_Text::linkAddress('boost', $link_command, true);
                        $js['label'] = $link_title;
                        $links[] = javascript('open_window', $js);
                        unset($js);
                    } else {
                        $links[] = & $link_title;
                    }
                }

                if ($type != 'core_mods' || Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
                    if ($dependents = $mod->isDependedUpon()) {
                        $link_command['action'] = 'show_depended_upon';
                        $depend_warning = sprintf(dgettext('boost', 'This module is depended upon by: %s'), implode(', ', $dependents));
                        $links[] = PHPWS_Text::secureLink(dgettext('boost', 'Depended upon'), 'boost', $link_command, NULL, $depend_warning);
                    } else {
                        $links[] = PHPWS_Boost::uninstallLink($title);
                    }
                }
                if ($allow_update) {
                    $js_file['QUESTION'] = dgettext('boost', 'Clicking OK will copy this module\\\'s configuration, image, templates, and javascript folders locally.\nNo backups will occur and all local files will be overwritten.\nAre you certain you want to do this?');
                    $js_file['ADDRESS'] = PHPWS_Text::linkAddress('boost', array('opmod'=>$title, 'action'=>'copy_local'), true);
                    $js_file['LINK'] = dgettext('boost', 'Revert');
                    $links[] = javascript('confirm', $js_file);
                } else {
                    $links[] = dgettext('boost', 'Revert');
                }
            }

            if ($mod->isAbout()) {
                $address = PHPWS_Text::linkAddress('boost',
                                                   array('action' => 'aboutView', 'aboutmod'=> $mod->title),
                                                   true);
                $aboutView = array('label'=>dgettext('boost', 'About'), 'address'=>$address);
                $template['ABOUT'] = Layout::getJavascript('open_window', $aboutView);
            }

            if (!empty($links)) {
                $template['COMMAND'] = implode(' | ', $links);
            } else {
                $template['COMMAND'] = dgettext('boost', 'None');
            }

            $tpl['mod-row'][] = $template;
            $count++;
        }

        $tpl['OLD_MODS'] = Boost_Form::oldModList();

        if (ini_get('allow_url_fopen')) {
            $tpl['CHECK_FOR_UPDATES'] = PHPWS_Text::secureLink(dgettext('boost', 'Check all'), 'boost',
                                                               array('action' => 'check_all', 'tab' => $type));
        } else {
            $tpl['CHECK_FOR_UPDATES'] = dgettext('boost', 'Server configuration prevents version checking.');
        }

        
        $tpl['LATEST_LABEL'] = dgettext('boost', 'Latest version');

        $release_version = PHPWS_Core::releaseVersion();
        $tpl['PHPWS_VERSION'] = $release_version;

        $result = PHPWS_Template::process($tpl, 'boost', 'module_list.tpl');
        return $result;
    }

    public function oldModList()
    {
        if (!isset($GLOBALS['Boost_Old_Mods'])) {
            return null;
        }

        $old_mods = & $GLOBALS['Boost_Old_Mods'];

        $content[] = dgettext('boost', 'The following modules are from an earlier version of phpWebSite and will not function.');
        $content[] = dgettext('boost', 'Please remove them from the mod directory.');
        foreach ($old_mods as $mod) {
            include sprintf('%smod/%s/conf/boost.php', PHPWS_SOURCE_DIR, $mod);
            $directory = sprintf('%smod/%s/', PHPWS_SOURCE_DIR, $mod);
            $content[] = sprintf(' - %s : %s', $mod_pname, $directory);
        }

        return implode('<br />', $content);
    }


}

?>