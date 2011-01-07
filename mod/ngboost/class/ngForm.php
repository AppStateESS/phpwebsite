<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Hilmar Runge <ngwebsite.net>
 */

class Boost_Form {

    function __construct()
    {
    }

    public static function boostTab(PHPWS_Panel $panel)
    {
        if (!isset($_REQUEST['tab'])) {
            return $panel->getCurrentTab();
        }  else {
            return $_REQUEST['tab'];
        }
    }

    public static function setTabs(PHPWS_Panel $panel)
    {
        $link = 'index.php?module=boost&amp;action=admin';

        $core_links['title'] = dgettext('boost', 'Core Modules');
        $other_links['title'] = dgettext('boost', 'Other Modules');

        $other_links['link'] = $core_links['link']  = $link;

        $tabs['core_mods'] = $core_links;
        $tabs['other_mods'] = $other_links;

        $panel->quickSetTabs($tabs);
    }

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

            // H 20101126.2 (
            PHPWS_Core::isBranch()
                ?$alnk=''
                :$alnk = '<a href="javascript:ngBu(\'core\')">Backup</a>';
            $template['VERSION'] .= NGSP3 . '<span id="ngmsgt11'.'core'.'">'
                . $alnk
                . '</span>';
            // )

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
                // H 20101108.3 (
                $alnk='<a href="javascript:ngAbout(\''.$core_file->title.'\')">about</a>';
                $template['ABOUT'] = $alnk;
                // )
            }


            $link_command['opmod'] = 'core';
            $link_command['action'] = 'check';
            if (ini_get('allow_url_fopen')) {

                $template['LATEST'] = PHPWS_Text::secureLink($link_title, 'boost', $link_command);

            // H 20101105.1 (	builds the check link for core mods - core
                // only for observation
                $lnk = new PHPWS_Link();
                $lnk->title = $link_title;
                $lnk->module = 'ngboost';
                $lnk->secure = true;
                $lnk->addValues($link_command);
                // test($lnk->getAddress());
                // index.php?module=ngboost&amp;opmod=core&amp;action=check&amp;authkey=94c119dd554b2b592a4c4edc56a24121
            // )
            // H 20101108.2 (
                    $template['LATEST'] = '<a href="javascript:ngCheck(\'core\')">'
                        .'<span id="ngchk'.'core'.'">'.$link_title.'</span></a>';
            // )

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

                    // H 20101107.2 (
                    //	$alnk='<a href="javascript:about(\''.$mod->title.'\',\''.Current_User::getAuthKey().'\')">about</a>';
                    // )

                }

                $template['VERSION'] =sprintf('%s &gt; %s', $core_db->version, $core_file->version);
                $template['COMMAND'] = implode(' | ', $core_links);
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

        if ($type == 'core_mods' && Current_User::isDeity() && NG_DEITIES_CAN_UNINSTALL) {
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
            // H 20101124.3 (
            PHPWS_Core::isBranch()
            ?$alnk = ''
            :$alnk = '<a href="javascript:ngBu(\''.$title.'\')">Backup</a>';
            $template['VERSION'] .= NGSP3 . '<span id="ngmsgt11'.$title.'">'
                . $alnk
                . '</span>';
            // )
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
                    // H 20101107.5 (
                    $template['LATEST'] = '<a href="javascript:ngCheck(\''.$mod->title.'\')">'
                        .'<span id="ngchk'.$mod->title.'">'.$link_title.'</span></a>';
                    // )
                } else {
                    $template['LATEST'] = dgettext('boost', 'Check disabled');
                }
            }

            if (!$mod->isInstalled()) {
                if ($mod->checkDependency()) {
                    $link_title = dgettext('boost', 'Install');
                    $link_command['action'] = 'install';
                    // H 20101108.5 (
                    $ngALnk11085 = '<a id="ngin'.$mod->title.'" href="javascript:ngInstall(\''.$mod->title.'\')">'
                        .dgettext('boost', 'Install').'</a>';
                    // )
                } else {
                    $link_title = dgettext('boost', 'Missing dependency');

                    $link_command['action'] = 'show_dependency';
                    // H 20101107.1 (
                    $ngALnk11071 = '<a href="javascript:ngShowDep(\''.$mod->title.'\')">'
                        .dgettext('boost', 'Missing dependency').'</a>';
                    // )

                }

                if ($GLOBALS['Boost_Ready']) {
                    if (javascriptEnabled()) {
                        $js['width'] = 640;
                        $js['height'] = 480;
                        $js['address'] = PHPWS_Text::linkAddress('boost', $link_command, true);
                        $js['label'] = $link_title;
                        // H20101107.1 (
                        if (isset($ngALnk11071) && isset($ngALnk11085)) {
                            $links[] = @$ngALnk11071 . @$ngALnk11085;
                            unset($ngALnk11071);
                            unset($ngALnk11085);
                        } else {
                        // )
                            $links[] = javascript('open_window', $js);
                        // (
                        }
                        // )
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
                    // H 20101109.2 (
                    $ngALnk11092 = '<a id="ngup'.$mod->title.'" href="javascript:ngUpdate(\''.$mod->title.'\')">'
                        .dgettext('boost', 'Update').'</a>';
                    // )
                    } else {
                        $link_title = dgettext('boost', 'Missing dependency');
                        $link_command['action'] = 'show_dependency';
                    }
                    if ($allow_update) {
                        $js['width'] = 640;
                        $js['height'] = 480;
                        $js['address'] = PHPWS_Text::linkAddress('boost', $link_command, true);
                        $js['label'] = $link_title;
                        // H 20101109.2 (
                        if ($ngALnk11092) {
                            $links[] = $ngALnk11092;
                            unset($ngALnk11092);
                        } else {
                        // )
                            $links[] = javascript('open_window', $js);
                            unset($js);
                        }
                    } else {
                        $links[] = & $link_title;
                    }
                }

                if ($type != 'core_mods' || Current_User::isDeity() && NG_DEITIES_CAN_UNINSTALL) {
                    if ($dependents = $mod->isDependedUpon()) {
                        $link_command['action'] = 'show_depended_upon';
                        $depend_warning = sprintf(dgettext('boost', 'This module is depended upon by: %s'), implode(', ', $dependents));

                        // H20101107.4 (
                        $ngALnk11074 = '<a href="javascript:ngShowDepUpon(\''.$mod->title.'\')">'
                            .dgettext('boost', 'Depended upon').'</a>';
                        $links[] = $ngALnk11074;
                        // - $links[] = PHPWS_Text::secureLink(dgettext('boost', 'Depended upon'), 'boost', $link_command, NULL, $depend_warning);
                        // )
                    } else {
                        // H 201011086 (
                        $ngALnk11086 = '<a id="ngun'.$mod->title.'" href="javascript:ngUnInstall(\''.$mod->title.'\')">'
                            .dgettext('boost', 'Uninstall').'</a>';
                        $links[] = $ngALnk11086;
                        // - $links[] = PHPWS_Boost::uninstallLink($title);
                        // )
                    }
                }
            }

            if ($mod->isAbout()) {
                // H 20071105.2
                // -	$address = PHPWS_Text::linkAddress('boost',
                // -	array('action' => 'aboutView', 'aboutmod'=> $mod->title),
                // -	true);
                // - 	$aboutView = array('label'=>dgettext('boost', 'About'), 'address'=>$address);

                // H 20101105.2 (
                //	strange ? mod-title has the mod-name
                $alnk='<a href="javascript:ngAbout(\''.$mod->title.'\')">about</a>';
                $template['ABOUT'] = $alnk;
                //)

                // -	$template['ABOUT'] = Layout::getJavascript('open_window', $aboutView);
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
            $tpl['CHECK_FOR_UPDATES'] = PHPWS_Text::secureLink(dgettext('boost', 'Check all'), 'ngboost',
            array('action' => 'check_all', 'tab' => $type));
            // H 20101108.1 (
            $tpl['CHECK_FOR_UPDATES'] = '<a href="javascript:ngCheckAll()">Check all</a>';
            // )
            // H 20101125.5 (
            PHPWS_Core::isBranch()
            ?$alnk =  '<a href="javascript:ngBuBranch()">Backup Branch</a>'
            :$alnk = '<a href="javascript:ngBuAll()">Backup all</a>';
            $tpl['CHECK_FOR_UPDATES'] = $alnk . NGSP3 . $tpl['CHECK_FOR_UPDATES'];
            // )
        } else {
            $tpl['CHECK_FOR_UPDATES'] = dgettext('boost', 'Server configuration prevents version checking.');
        }


        $tpl['LATEST_LABEL'] = dgettext('boost', 'Latest version');

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