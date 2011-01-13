<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Hilmar Runge <ngwebsite.net>
 */

class ngBoost_Form {

    public static function listModules($type)
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

            PHPWS_Core::isBranch() ? $alnk='' 
			: $alnk = '<a href="javascript:ngBu(\'core\')">'.dgettext('ngboost','Backup').'</a>';
            $template['VERSION'] .= NGSP3 . '<span id="ngmsgt11'.'core'.'">' . $alnk . '</span>';

			// (
            if (isset($_SESSION['Boost_Needs_Update']['core'])) {
                $link_title = $_SESSION['Boost_Needs_Update']['core'];
                if (version_compare($core_file->version, $_SESSION['Boost_Needs_Update']['core'], '<')) {
                    $link_title = sprintf(dgettext('ngboost', '%s - New'), $link_title);
                }
			// )
            } else {
                $link_title = dgettext('ngboost', 'Check');
            }

            if ($core_file->isAbout()) {
                $alnk='<a href="javascript:ngAbout(\''.$core_file->title.'\')">'.dgettext('ngboost','about').'</a>';
                $template['ABOUT'] = $alnk;
            }


            $link_command['opmod'] = 'core';
            $link_command['action'] = 'check';
            if (ini_get('allow_url_fopen')) {
                $template['LATEST'] = '<a href="javascript:ngCheck(\'core\')">'
                .'<span id="ngchk'.'core'.'">'.$link_title.'</span></a>';
            } else {
                $template['LATEST'] = dgettext('ngboost', 'Check disabled');
            }

			// (
            if (version_compare($core_db->version, $core_file->version, '<')) {
                if ($core_file->checkDependency()) {
                    if ($allow_update) {
                        $link_command['action'] = 'update_core';
                        $core_links[] = PHPWS_Text::secureLink(dgettext('ngboost', 'Update'), 'boost', $link_command);
                    } else {
                        $core_links[] = dgettext('ngboost', 'Update');
                    }
                    $tpl['WARNING'] = dgettext('ngboost', 'The Core requires updating! You should do so before any modules.');
                    $core_update_needed = true;
                } else {
                    $link_command['action'] = 'show_dependency';
                    $core_links[] = PHPWS_Text::secureLink(dgettext('ngboost', 'Missing dependency'), 'boost', $link_command);
                }

                $template['VERSION'] =sprintf('%s &gt; %s', $core_db->version, $core_file->version);
                $template['COMMAND'] = ltrim(implode(' ', $core_links));
            } else {
                $template['COMMAND'] = dgettext('ngboost', 'None');
            }
			// )

            $template['ROW']     = 1;
            $tpl['mod-row'][] = $template;
			
        } else {
		
			// OTHER MODS
            $allowUninstall = true;
            $modList = $dir_mods;
        }

        $tpl['TITLE_LABEL']   = dgettext('ngboost', 'Module Title');
        $tpl['COMMAND_LABEL'] = dgettext('ngboost', 'Commands');
        $tpl['ABOUT_LABEL']   = dgettext('ngboost', 'More information');
        $tpl['VERSION_LABEL'] = dgettext('ngboost', 'Current version');

        if ($type == 'core_mods' && Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
            $tpl['WARNING'] = dgettext('ngboost', 'WARNING: Only deities can uninstall core modules. Doing so may corrupt your installation!');
        }

        if (empty($modList)) {
            return dgettext('ngboost', 'No modules available.');
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
            PHPWS_Core::isBranch() ? $alnk = ''
			: $alnk = '<a href="javascript:ngBu(\''.$title.'\')">'.dgettext('ngboost','Backup').'</a>';
            $template['VERSION'] .= NGSP3 . '<span id="ngmsgt11'.$title.'">' . $alnk . '</span>';
            $template['TITLE'] = $proper_name;
            $template['ROW'] = ($count % 2) + 1;

            $version_check = $mod->getVersionHttp();

            if (isset($version_check)) {
                if (isset($_SESSION['Boost_Needs_Update'][$mod->title])) {
                    $link_title = $_SESSION['Boost_Needs_Update'][$mod->title];
                    if (version_compare($mod->version, $_SESSION['Boost_Needs_Update'][$mod->title], '<')) {
                        $link_title = sprintf(dgettext('ngboost', '%s - New'), $link_title);
                    }
                } else {
                    $link_title = dgettext('ngboost', 'Check');
                }

                $link_command['action'] = 'check';
                if (ini_get('allow_url_fopen')) {
                    $template['LATEST'] = '<a href="javascript:ngCheck(\''.$mod->title.'\')">'
                    .	'<span id="ngchk'.$mod->title.'">'.$link_title.'</span></a>';
                } else {
                    $template['LATEST'] = dgettext('ngboost', 'Check disabled');
                }
            }

            if (!$mod->isInstalled()) {
                if ($mod->checkDependency()) {
 					$link_title = dgettext('boost', 'Install');
					$link_command['action'] = 'install';
					$ngALnk11085 = '<a id="ngin'.$mod->title.'" href="javascript:ngInstall(\''.$mod->title.'\')">'
                    .	dgettext('ngboost', 'Install').'</a>';
                } else {
 					$link_title = dgettext('boost', 'Missing dependency');
					$link_command['action'] = 'show_dependency';
					$ngALnk11071 = '<a href="javascript:ngShowDep(\''.$mod->title.'\')">'
                    .	dgettext('ngboost', 'Missing dependency').'</a>';
                }

                if ($GLOBALS['Boost_Ready']) {
					// if either or !!!
                    if (isset($ngALnk11071) || isset($ngALnk11085)) {
                        $links[] = @$ngALnk11071 . @$ngALnk11085;
                        unset($ngALnk11071);
                        unset($ngALnk11085);
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
                            $tpl['WARNING'] = dgettext('ngboost',
							'Boost requires updating! You should do so before any other module!');
                        }
                        $link_title = dgettext('ngboost', 'Update');
                        $link_command['action'] = 'update';
						$ngALnk11092 = '<a id="ngup'.$mod->title.'" href="javascript:ngUpdate(\''.$mod->title.'\')">'
						.	dgettext('ngboost', 'Update').'</a>';
                    } else {
                        $link_title = dgettext('ngboost', 'Missing dependency');
                        $link_command['action'] = 'show_dependency';
                    }
                    if ($allow_update) {
                        if ($ngALnk11092) {
                            $links[] = $ngALnk11092;
                            unset($ngALnk11092);
                        }
                    } else {
                        $links[] = & $link_title;
                    }
                }

                if ($type != 'core_mods' || Current_User::isDeity() && DEITIES_CAN_UNINSTALL) {
                    if ($dependents = $mod->isDependedUpon()) {
                        $link_command['action'] = 'show_depended_upon';
                        $depend_warning = sprintf(dgettext('ngboost', 'This module is depended upon by: %s'),
							implode(', ', $dependents));
                        $links[] = '<a href="javascript:ngShowDepUpon(\''.$mod->title.'\')">'
                        .	dgettext('ngboost', 'Depended upon').'</a>';
                    } else {
                        $links[] = '<a id="ngun'.$mod->title.'" href="javascript:ngUnInstall(\''.$mod->title.'\')">'
                        .	dgettext('ngboost', 'Uninstall').'</a>';
                    }
                }
            }

            if ($mod->isAbout()) {
                $alnk='<a href="javascript:ngAbout(\''.$mod->title.'\')">about</a>';
                $template['ABOUT'] = $alnk;
            }

            if (!empty($links)) {
                $template['COMMAND'] = ltrim(implode(' ', $links));
            } else {
                $template['COMMAND'] = dgettext('ngboost', 'None');
            }

            $tpl['mod-row'][] = $template;
            $count++;
        }

        $tpl['OLD_MODS'] = ngBoost_Form::oldModList();

        if (ini_get('allow_url_fopen')) {
            $tpl['CHECK_FOR_UPDATES'] = '<a href="javascript:ngCheckAll()">Check all</a>';
			
            PHPWS_Core::isBranch()
            ?$alnk =  '<a href="javascript:ngBuBranch()">Backup Branch</a>'
            :$alnk = '<a href="javascript:ngBuAll()">Backup all</a>';
			
            $tpl['CHECK_FOR_UPDATES'] = $alnk . NGSP3 . $tpl['CHECK_FOR_UPDATES'];
			
        } else {
            $tpl['CHECK_FOR_UPDATES'] = dgettext('ngboost', 'Server configuration prevents version checking.');
        }


        $tpl['LATEST_LABEL'] = dgettext('ngboost', 'Latest version');

        $release_version = PHPWS_Core::releaseVersion();
        $tpl['PHPWS_VERSION'] = $release_version;

        $result = PHPWS_Template::process($tpl, 'ngboost', 'module_list.tpl');
        return $result;
    }
	

    public static function ngTabRepo()
    {
        $alnk='<a href="javascript:ngPlain(\'crp\')">VerifyRepositoryPath</a>'
            . NGSP3
            . '<a href="javascript:ngPlain(\'lrp\')">ListRepository</a>'
            . '<p id="ngmsgt51"></p>';
        return $alnk;
    }

    public function ngTabLTar($fn)
    {
        // security, do not let see filenames as js parameters
        $fnc=md5($fn);
        $_SESSION['FG']['ngfn'][$fnc]=$fn;

        if (substr($fn,-4)=='.tgz' || substr($fn,-7)=='.tar.gz') {
            $dir = '<a href="javascript:ngPop(\'ltar\',\'fn\',\'' . $fnc . '\')">dir</a>';
        } else {
            $dir = '<span class="ngpseudo">dir</span>';
        }

        $alnk = $dir
        .	'&nbsp;'
        .	'<a href="javascript:ngPop(\'re\',\'fn\',\'' . $fnc
        .	'\')">recover</a>'
        .	'&nbsp;'
        .	'<a href="javascript:ngPop(\'dy\',\'fn\',\'' . $fnc
        . 	'\')">purge</a>';

        return $alnk;
    }

    public static function ngTabDB()
    {
        $alnk = '<a href="javascript:ngPlain(\'ldb\')">ListTables</a>'
        .		'<p id="ngmsgt61"></p>';
        return $alnk;
    }

    public static function ngTabTune()
    {
        $alnk = '<a href="javascript:ngPlain(\'ts\')">Distro</a>' .NGSP3
		.		'<a href="javascript:ngPatos()">Patos</a>'
		//	.	NGSP3
		//	.	'<a href="javascript:ngPlain(\'fs\')">FS.perms</a>'
        .		'<p id="ngmsgt71"></p>';
        return $alnk;
    }

    public function ngTabListTable($mod,$table)
    {
        $tc = md5($table);
        $_SESSION['FG']['ngtn'][$tc] = $table;
        $alnk = '<a href="javascript:ngBuT(\'' . $tc . '\',\'' .md5($mod). '\')">backup table</a>';
        return $alnk;
    }

    public function ngTabListTables($op,$mod,$tables)
    {
        $modtc=md5($mod);
        $_SESSION['FG']['0m'.$modtc] = $tables;
        $alnk = '<a href="javascript:ngBuTs1(\'' . $op . '\',\''. $modtc . '\')">backup modules tables</a>';
        return $alnk;
    }

    //

    public static function oldModList()
    {
        if (!isset($GLOBALS['Boost_Old_Mods'])) {
            return null;
        }

        $old_mods = & $GLOBALS['Boost_Old_Mods'];

        $content[] = dgettext('ngboost', 'The following modules are from an earlier version of phpWebSite and will not function.');
        $content[] = dgettext('ngboost', 'Please remove them from the mod directory.');
        foreach ($old_mods as $mod) {
            include sprintf('%smod/%s/conf/boost.php', PHPWS_SOURCE_DIR, $mod);
            $directory = sprintf('%smod/%s/', PHPWS_SOURCE_DIR, $mod);
            $content[] = sprintf(' - %s : %s', $mod_pname, $directory);
        }

        return implode('<br />', $content);
    }
}
?>