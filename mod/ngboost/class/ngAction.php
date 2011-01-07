<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Hilmar Runge <ngwebsite.net>
 */

PHPWS_Core::initModClass('boost', 'Boost.php');

class ngBoost_Action {

    function __construct()
    {
    }

    public static function index()
    {
        if (!isset($_REQUEST['xaop'])) {
            return;
        }
        switch ($_REQUEST['xaop']) {
            // The BG actions
            case 'a':
                ngBoost_Action::ngShowAbout($_REQUEST['p']);
                return;
                break;
            case 'bm':
                ngBoost_Action::ngBU($_REQUEST['p']);
                return;
                break;
            case 'B':
                ngBoost_Action::ngBuAll();
                return;
                break;
            case 'Br':
                ngBoost_Action::ngBuBranch();
                return;
                break;
            case 'bt':
                ngBoost_Action::ngBuTbl($_REQUEST['tn']);
                return;
                break;
            case 'btn':
                ngBoost_Action::ngBuTblAll($_REQUEST['rs']);
                return;
                break;
            case 'btm':
                ngBoost_Action::ngBuTblMod($_REQUEST['rs']);
                return;
                break;
            case 'bt1':
                ngBoost_Action::ngBuTbl1($_REQUEST['rs']);
                return;
                break;
            case 'c':
                ngBoost_Action::ngCheck($_REQUEST['p']);
                return;
                break;
            case 'C':
                ngBoost_Action::ngCheckAll();
                return;
                break;
            case 'crp':
                ngBoost_Action::ngCheckRepo();
                return;
                break;
            case 'd':
                ngBoost_Action::ngShowDep($_REQUEST['p']);
                return;
                break;
            case 'h':
                ngBoost_Action::ngAnyHelp($_REQUEST['h']);
                return;
                break;
            case 'dy':
                ngBoost_Action::ngBuDel($_REQUEST['fn']);
                return;
                break;
            case 'in':
                ngBoost_Action::ngInstall($_REQUEST['p']);
                return;
                break;
            case 'ldb':
                ngBoost_Action::ngListDB();
                return;
                break;
            case 'lrp':
                ngBoost_Action::ngListRepo();
                return;
                break;
            case 'ltar':
                ngBoost_Action::ngListTar($_REQUEST['fn']);
                return;
                break;
            case 're':
                $fn=$_SESSION['FG']['ngfn'][$_REQUEST['fn']];
                if (substr($fn,-4)=='.tgz' || substr($fn,-7)=='.tar.gz') {
                    ngBoost_Action::ngReTar($_REQUEST['fn']);
                } else {
                    if (substr($fn,-5)=='.data') {
                        ngBoost_Action::ngReTbl($_REQUEST['fn']);
                    } else {
                        $_SESSION['BG']=dgettext('ngboost','invalid file type');
                    }
                }
                return;
                break;
            case 'un':
                ngBoost_Action::ngUnInstall($_REQUEST['p']);
                return;
                break;
            case 'uc':
                // JUST TO WORK OUT - - -
                ngBoost_Action::ngUpdateCore();
                return;
                break;
            case 'up':
                ngBoost_Action::ngUpdate($_REQUEST['p']);
                return;
                break;
            case 'u':
                ngBoost_Action::ngShowDepUpon($_REQUEST['p']);
                return;
                break;
            case 'tget':
                ngBoost_Action::ngPickupTgz($_REQUEST['m']);
                return;
                break;
        }
    }

    public function ngShowAbout($mod)
    {
        if ($mod == 'core') {
            $c1 = file_get_contents(PHPWS_SOURCE_DIR.'core/boost/about.html');
        } else {
            $c1 = file_get_contents(PHPWS_SOURCE_DIR.'mod/'.$mod.'/boost/about.html');
        }

        $c2 = explode('<h1>', $c1);
        $c3 = explode('</body>',$c2[1]);
        $_SESSION['BG'] = NGJQMCLOSE.'<h1>' . str_replace('onclick="window.close()"', 'class="jqmClose"',$c3[0]);
    }

    public function ngShowDep($mod)
    {
        $_SESSION['BG'] = NGJQMCLOSE.ngBoost_Action::showDependency($mod);
    }

    public function ngShowDepUpon($mod)
    {
        $_SESSION['BG'] = NGJQMCLOSE.ngBoost_Action::showDependedUpon($mod);
    }

    public function ngCheck($mod)
    {
        $cnt = ngBoost_Action::checkupdate($mod);

        $_SESSION['BG'] =
            $mod
            . '--'
            . $_SESSION['Boost_Needs_Update'][$mod]
            . '--'
            . NGJQMCLOSE.$cnt;
    }

    public function ngCheckAll()
    {
        $mods = implode('--', PHPWS_Boost::getAllMods());

        $_SESSION['BG'] = 'core'.'--'.$mods;
    }

    public function ngCheckRepo()
    {
        $rp=ngBoost_Action::ngGetRepositoryPath();
        $_SESSION['BG']='State of repository path ';
        $rp ? $_SESSION['BG'] .= NGSAYOK : $_SESSION['BG'] .= NGSAYKO;
    }

    public function ngInstall($mod)
    {
        $result = ngBoost_Action::installModule($_REQUEST['p']);

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            // 1st status feedback, 2nd mod, 3rd flip action translated
            $content[] = 'no--'.$_REQUEST['p'].'--'.dgettext('ngboost', 'Install').'--';
            $content[] = NGJQMCLOSE.dgettext('ngboost', 'An error occurred while installing this module.')
                        . ' ' . dgettext('ngboost', 'Please check your error logs.');
        } else {
            // 1st status feedback, 2nd mod, 3rd flip action translated
            $content[] = 'ok--'.$_REQUEST['p'].'--'.dgettext('ngboost', 'Uninstall').'--';
            $content[] = NGJQMCLOSE.$result;
        }

        $_SESSION['BG'] = implode('',$content);
    }

    public function ngUnInstall($mod)
    {
        if (@$_REQUEST['confirm'] == $_REQUEST['p']) {
            // 1st status feedback, 2nd mod, 3rd flip action translated
            $content[] = 'ok--'.$mod.'--'.dgettext('ngboost', 'Install').'--';
            $content[] = NGJQMCLOSE.ngBoost_Action::uninstallModule($_REQUEST['p']);
        } else {
            $content[] = 'no--'.$mod.'--'.dgettext('ngboost', 'Uninstall').'--';
            $content[] = NGJQMCLOSE.dgettext('ngboost', 'Uninstall not confirmed');
        }

        $_SESSION['BG'] = implode('', $content);
    }

    public static function ngUpdate($mod)
    {
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($mod), FALSE);

        $results = array();

        if ($boost->update($results)) {
            $boost->updateBranches($results);
        }

        $content[] = 'ok--'.$mod.'--';
        $content[] = NGJQMCLOSE.implode('<br />',$results);

        $_SESSION['BG'] = implode('',$content);
    }

    public static function ngUpdateCore()
    {
        // just TO DO !!!!!!!!!!!!!!
    }

    public static function ngGetRepositoryPath()
    {
        PHPWS_Core::initCoreClass('ngBackup.php');
        return ngBackup::getRepositoryPath();
    }

    public function ngListRepo()
    {
                $rp=ngBoost_Action::ngGetRepositoryPath();
                $_SESSION['BG']='<b>'.dgettext('ngboost','Content of repository').'</b><br />';
                $filenames=array();
                $dir=opendir($rp);
                while (FALSE !== ($file = readdir ($dir))) {
                    if ($file != '.' and $file != '..') {
                        $filenames[]=$file;
                    }
                }
                closedir($dir);
                if (count($filenames)==0) {
                    $_SESSION['BG'] .= NGSAYKO . '  <i>' . 'is empty' . '</i><br />';
                } else {
                    sort($filenames);
                    $cl='bgcolor1';
                    $_SESSION['BG'] .= '<table class="ngtable">'
                    .	'<thead class="ngthead"><tr><th>' .dgettext('ngboost','File')
                    .	'</th class="ngthead"><th style="text-align:right;">'.dgettext('ngboost','Size')
                    .	'</th><th>'.dgettext('ngboost','Commands').'</th></tr></thead>'
                    .	'<tbody class="ngtbody">';
                    $szsum=$sz=$fct=0;
                    foreach ($filenames as $f) {
                        $cl=='bgcolor1' ? $cl='bgcolor2' : $cl='bgcolor1';
                        $fct++;
                        $sz=@filesize($rp.$f);
                        $szsum=$szsum + $sz;
                        $_SESSION['BG'] .= '<tr class="'.$cl.'"><td>' . $f . '</td>'
                        . '<td style="text-align:right;">' . sprintf("%u",$sz) . '</td>'
                        . '<td id="ngop'.md5($f).'">' . Boost_Form::ngTabLTar($f) . '</td></tr>';
                    }
                    $_SESSION['BG'] .= '</tbody><tr class="ngtrfoot"><td style="text-align:right;">'
                    . $fct . ' ' . dgettext('ngboost','files') . '</td>'
                    . '<td style="text-align:right;">' . sprintf("%u",round($szsum/1024/1024,0)) . '</td>'
                    . '<td>' . dgettext('ngboost','MB') . '</td></tr>';

                    $_SESSION['BG'] .= '</table>';
                }

    }

    public function ngListDB()
    {
        // associate tables to mods
        $mods=PHPWS_Boost::getAllMods();
        $mods[]='core';
        sort($mods);
        foreach ($mods as $mod) {
            $ar[$mod]=array();
            if ($mod == 'core') {
                $f = PHPWS_SOURCE_DIR.'core/boost/install.sql';
            } else {
                $f = PHPWS_SOURCE_DIR.'mod/'.$mod.'/boost/install.sql';
            }
            if (file_exists($f)) {
                $insql = strtolower(file_get_contents($f));
                $its = explode('create table',$insql);
                $n=0;
                foreach ($its as $it) {
                    $n++;
                    if ($n==1) continue;
                    $in = explode ('(',$it);
                    $ar[$mod][]=trim($in[0]);
                }
            }
        }

        PHPWS_Core::initCoreClass('ngBackup.php');
        $ngbu = new ngBackup();
        $returnPrefix=false;
        $tl = $ngbu->getTableList($returnPrefix);
        $ml = PHPWS_Core::getModules(true,true);

        $_SESSION['BG']='<b>'.dgettext('ngboost','Database tables of installation').'</b><br />'
        .	'<table class="ngtable"><thead class="ngthead">'
        .	'<tr><th>' .dgettext('ngboost','Module')
        .	'</th><th>'.dgettext('ngboost','Table(s)')
        .	'</th><th>'.dgettext('ngboost','Commands')
        .	'</th><th>'.dgettext('ngboost','Actions, Messages, Feedback')
        .	' </th></tr></thead><tbody class="ngtbody">';
        unset($_SESSION['FG']['ngtn']);
        unset($_SESSION['FG']['ngtm']);
        foreach ($ar as $mod => $tbs) {
            if (count($tbs)==0) {
            } else {

                $_SESSION['FG']['ngtn']['0m'.md5($mod)]=$tbs;

                $cl=='bgcolor1'?$cl='bgcolor2':$cl='bgcolor1';
                $_SESSION['BG'] .= '<tr class="'.$cl.'"><td>'.$mod.'</td>';
                $_SESSION['BG'] .= '<td align="right"><sub><i>'.count($tbs).' Table(s)</i></sub></td>';
                if ($mod == 'core' || in_array($mod,$ml)) {

                    $_SESSION['FG']['ngtm'][md5($mod)]=$mod;

                    $_SESSION['BG'] .= '<td>' . Boost_Form::ngTabListTables('m',$mod,$tbs) . '</td>';
                } else {
                    $_SESSION['BG'] .= '<td>' . 'Module not installed' . '</td>';
                }
                $_SESSION['BG'] .= '<td><span id="ngt6m'.md5($mod).'"></span></td></tr>';
            }
            foreach ($tbs as $tb) {
                $cl=='bgcolor1'?$cl='bgcolor2':$cl='bgcolor1';
                $_SESSION['BG'] .= '<tr class="'.$cl.'"><td>'.'&nbsp;'.'</td>';
                $_SESSION['BG'] .= '<td>' . $tb . '</td>';
                $_SESSION['BG'] .= '<td>';
                if (in_array($tb,$tl)) {
                    $_SESSION['BG'] .= Boost_Form::ngTabListTable($mod,$tb);
                } else {
                    $_SESSION['BG'] .= 'not available';
                }
                $_SESSION['BG'] .= '</td>';
                $_SESSION['BG'] .= '<td><span id="ngt6t'.md5($tb).'"></span></td>';
                $_SESSION['BG'] .= '</tr>';
            }
        }
        $_SESSION['BG'] .= '</tbody></table><hr />'
        .	'<div style="text-align:center;">'
        .	'<a href="javascript:ngBuTs1(\'n\',\''.md5('all').'\')">BackupAllTables</a>'
        .	'</div>';
        return;
        if ($tl) {
            if (is_array($tl)) {
                $_SESSION['BG'] = '<div style="width:99%; max-height:228px; overflow:auto;"><table>';
                foreach ($tl as $tn) {
                    $cl=='bgcolor1'?$cl='bgcolor2':$cl='bgcolor1';
                    $_SESSION['BG'] .= '<tr class="'.$cl.'"><td>'.$tn.'</td>'
                    .	'<td>' . Boost_Form::ngTabLTabs($tn) . '</td>'
                    .	'<td><span id="ngt6'.md5($tn).'"></span></td>'
                    .	'</tr>';
                }
                $_SESSION['BG'] .= '</table></div>';
                return;
            }
        }

        $_SESSION['BG'] = 'Error accessing db tables';
    }

    public function ngListTar($fnc)
    {
        $_SESSION['BG']='';
        if (isset($_SESSION['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION['FG']['ngfn'][$fnc];
            PHPWS_Core::initCoreClass('ngBackup.php');
            $ngbu = new ngBackup();
            $r=$ngbu->tarList($fn);
            $cc=substr($r,0,1);
            $re=substr($r,1);
            if ($cc==0) {
                $_SESSION['BG'] = NGJQMCLOSE . $re;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'List Tar' . NGSAYKO . $cc;
            }
        }
    }

    public function ngReTar($fnc)
    {
        $_SESSION['BG']='';
        if (isset($_SESSION['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION['FG']['ngfn'][$fnc];
            PHPWS_Core::initCoreClass('ngBackup.php');
            $ngbu = new ngBackup();
            $r=$ngbu->restoreMod($fn);
            $cc=substr($r,0,1);
            $re=substr($r,1);
            if ($cc==0) {
                $_SESSION['BG'] = NGJQMCLOSE . $re . NGSAYOK;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Restore' . NGSAYKO . $cc . ',' . $re;
            }
        }
    }

    public function ngBU($mod)
    {
        PHPWS_Core::initCoreClass('ngBackup.php');
        $ngbu = new ngBackup();
        // context is ngboost
        $r=$ngbu->backupMod($mod);
        //$r=ngBackup::backupMod($mod);
        $cc=substr($r,0,1);
        $re=substr($r,1);
        if ($cc==0) {
            $_SESSION['BG'] = $mod . '--' . 'Backup' . NGSAYOK . $re;
        } else {
            $_SESSION['BG'] = $mod . '--' . 'Backup' . NGSAYKO . $cc;
        }
    }

    public function ngBuAll($mod)
    {
        $mods = 'core--' . implode('--', PHPWS_Boost::getAllMods());
        $_SESSION['BG'] = $mods;
    }

    public function ngBuDel($fnc)
    {
        $_SESSION['BG']=' ';
        if (isset($_SESSION['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION['FG']['ngfn'][$fnc];
            $cc=@unlink(ngBoost_Action::ngGetRepositoryPath().$fn);
            if ($cc) {
                $_SESSION['BG'] = '#ngop'.$fnc.'--purged--' . NGJQMCLOSE . $fn. ' ' . 'purged' . ' ' . NGSAYOK;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Purge' . NGSAYKO . $cc . ',' . $fn;
            }
        }
    }

    public function ngBuTbl($tnc)
    {
        $_SESSION['BG']=' ';
        if (isset($_SESSION['FG']['ngtn'][$tnc])) {
            $tn=$_SESSION['FG']['ngtn'][$tnc];
            $ngbu = new ngBackup();
            // retranslate
            $ngbu->mod = $_SESSION['FG']['ngtm'][$_REQUEST['m']];
            $msg=$ngbu->exportTable($tn);
            $_SESSION['BG'] = $msg;
        }
    }

    public function ngBuTblMod($mod)
    {
        $_SESSION['BG'] .= $mod;
        foreach ($_SESSION['FG']['ngtn']['0m'.$mod] as $tb) {
            $_SESSION['BG'] .= '--' . md5($tb);
        }
    }
    public function ngBuTblAll($rs)
    {
        $_SESSION['BG']=implode('--',array_keys($_SESSION['FG']['ngtm']));
    }

    public function ngReTbl($fnc)
    {
        $_SESSION['BG']='';
        if (isset($_SESSION['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION['FG']['ngfn'][$fnc];
            PHPWS_Core::initCoreClass('ngBackup.php');
            $ngbu = new ngBackup();
            $r=$ngbu->importTable($fn);
            $cc=substr($r,0,1);
            $re=substr($r,1);
            if ($cc==0) {
                $_SESSION['BG'] = NGJQMCLOSE . $re . NGSAYOK;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Restore' . NGSAYKO . $cc . ',' . $re;
            }
        }
    }

    public function ngPickupTgz($mod)
    {
        $repo = ngBoost_Action::ngGetRepositoryPath();
        if (isset($_SESSION['FG'][$mod])) {
            $tgzf = array_pop(explode('/', $_SESSION['FG'][$mod]));
            if ($repo) {
                if (file_exists($repo.'/'.$tgzf)) {
                    $_SESSION['BG'] = $mod.'--Info: '.$tgzf . ' just exists in repository';
                } else {
                    $cc = @copy($_SESSION['FG'][$mod], $repo.'/'.$tgzf);
                    if ($cc) {
                        $_SESSION['BG'] = $mod.'--OK: ' . $tgzf . 'successfully copied';
                        // to compare chekcsum <<<>>>
                    } else {
                        $_SESSION['BG'] = $mod.'--Fail: '.$_SESSION['FG'][$mod] . ' to ' . $repo . '/' . $tgzf;
                    }
                }
            } else {
                $_SESSION['BG'] = $mod.'--Repository error ' . $mod;
            }
            unset($_SESSION['FG'][$mod]);
        } else {
            $_SESSION['BG'] = $mod.'--Error ' . $mod;
        }
    }

    public function ngAnyHelp($help)
    {
        $helpfile = false;
        switch ($help) {
            case 'ngbstcptab1':
                $helpfile = 'cp.core.html';
                break;
            case 'ngbstcptab2':
                $helpfile = 'cp.app.html';
                break;
            case 'ngbstcptab3':
                $helpfile = 'cp.obs.txt';
                break;
            case 'ngbstcptab4':
                $helpfile = 'cp.new.txt';
                break;
            case 'ngbstcptab5':
                $helpfile = 'cp.repo.html';
                break;
            case 'ngbstcptab6':
                $helpfile = 'cp.db.txt';
                break;
        }

        $_SESSION['BG'] = NGJQMCLOSE . '<div style="max-height:360px; overflow:auto">';

        if ($helpfile) {
            $helppf = PHPWS_SOURCE_DIR.'mod/ngboost/docs/'.$helpfile;
            if (file_exists($helppf)) {
                $_SESSION['BG'] .= file_get_contents($helppf) . '</div>';
                return;
            }
        }

        $_SESSION['BG'] .= dgettext('ngboost','currently no help available') . '</div>';
    }

    //

    public static function checkupdate($mod_title)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $module = new PHPWS_Module($mod_title);

        $file = $module->getVersionHttp();
        if (empty($file)) {
            return dgettext('boost', 'Update check file not found.');
        }

        $full_xml_array = PHPWS_Text::xml2php($file, 2);

        if (empty($full_xml_array)) {
            return dgettext('boost', 'Update check file not found.');
        }
        $version_info = PHPWS_Text::tagXML($full_xml_array);

        $template['LOCAL_VERSION_LABEL'] = dgettext('boost', 'Local version');
        $template['LOCAL_VERSION'] = $module->getVersion();
        $template['STABLE_VERSION_LABEL'] = dgettext('boost', 'Current stable version');
        if (!isset($version_info['VERSION'])) {
            $template['STABLE_VERSION'] = dgettext('boost', 'Source XML error');
            $version_info['VERSION'] = $module->getVersion();
        } else {
            $_SESSION['Boost_Needs_Update'][$mod_title] = $version_info['VERSION'];
            $template['STABLE_VERSION'] = $version_info['VERSION'];
        }

        if (version_compare($version_info['VERSION'], $module->getVersion(), '>')) {
            $template['CHANGES_LABEL'] = dgettext('boost', 'Changes');
            $template['CHANGES'] = htmlspecialchars($version_info['CHANGES']);
            $template['UPDATE_AVAILABLE'] = dgettext('boost', 'An update is available');
            $template['PU_LINK_LABEL'] = dgettext('boost', 'Copy into repository');
            // H 20101111 (
                $tgz = array_pop(explode('/', $version_info['DOWNLOAD']));
                // prevent passing url as js/request parameter
                $_SESSION['FG'][$module->title] = $version_info['DOWNLOAD'];
                $lnk = '<a href="javascript:ngPickup(\''.$module->title.'\')">'
                        .$tgz . '</a><div id="ngpickup'.$module->title.'"></div>';
            // )
            $template['PU_LINK'] = $lnk;
            $template['DL_PATH_LABEL'] = dgettext('boost', 'or download by yourself from here');
            $template['DL_PATH'] = '<a href="' . $version_info['DOWNLOAD'] . '">' . $version_info['DOWNLOAD'] . '</a>';
            $template['MD5_LABEL'] = dgettext('boost', 'MD5 Sum');
            $template['MD5'] = $version_info['MD5SUM'];

            if (isset($version_info['DEPENDENCY'][0]['MODULE'])) {
                $template['DEPENDENCY_LABEL'] = dgettext('boost', 'Dependencies');
                $template['DEP_TITLE_LABEL'] = dgettext('boost', 'Module title');
                $template['DEP_VERSION_LABEL'] = dgettext('boost', 'Version required');
                $template['DEP_STATUS_LABEL'] = dgettext('boost', 'Status');

                foreach ($version_info['DEPENDENCY'][0]['MODULE'] as $dep_mod) {
                    $check_mod = new PHPWS_Module($dep_mod['TITLE'], false);

                    if ($check_mod->_error) {
                        $status = dgettext('boost', 'Not installed');
                        $row['DEP_STATUS_CLASS'] = 'red';
                    } elseif (version_compare($check_mod->version, $dep_mod['VERSION'], '<')) {
                        $status = dgettext('boost', 'Needs upgrading');
                        $row['DEP_STATUS_CLASS'] = 'red';
                    } else {
                        $status = dgettext('boost', 'Passed!');
                        $row['DEP_STATUS_CLASS'] = 'green';
                    }
                    $row['DEP_TITLE'] = $dep_mod['PROPERNAME'];
                    $row['DEP_VERSION'] = $dep_mod['VERSION'];
                    $row['DEP_ADDRESS'] = sprintf('<a href="%s">%s</a>',
                    $dep_mod['URL'], dgettext('boost', 'Download'));
                    $row['DEP_STATUS'] = $status;
                    $template['dependent-mods'][] = $row;
                }
            }
        }
        else {
            $template['NO_UPDATE'] = dgettext('boost', 'No update required.');
        }

        $template['TITLE'] = dgettext('boost', 'Module') . ': ' . $module->getProperName(TRUE);
        // mod ref tpl process !!!
        return PHPWS_Template::process($template, 'ngboost', 'check_update.tpl');
    }

    public static function installModule($module_title)
    {
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title));
        return $boost->install();
    }

    public static function uninstallModule($module_title)
    {
        $boost = new PHPWS_Boost;
        $boost->loadModules(array($module_title));

        $content = $boost->uninstall();

        return $content;
    }

    public function updateCore()
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $content[] = dgettext('boost', 'Updating core');

        require_once PHPWS_SOURCE_DIR . 'core/boost/update.php';

        $ver_info = PHPWS_Core::getVersionInfo(false);

        $content[] = dgettext('boost', 'Processing update file.');
        $result = core_update($content, $ver_info['version']);

        if ($result === true) {
            $db = new PHPWS_DB('core_version');
            $file_ver = PHPWS_Core::getVersionInfo();
            $db->addValue('version', $file_ver['version']);
            $result = $db->update();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = dgettext('boost', 'An error occurred updating the core.');
            } else {
                $content[] = dgettext('boost', 'Core successfully updated.');
            }
        } elseif (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = dgettext('boost', 'An error occurred updating the core.');
        } else {
            $content[] = dgettext('boost', 'An error occurred updating the core.');
        }

        return implode('<br />', $content);
    }

    public static function updateModule($module_title)
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

    public function showDependedUpon($base_mod)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $module = new PHPWS_Module($base_mod);
        $dependents = $module->isDependedUpon();
        if (empty($dependents)) {
            return dgettext('boost', 'This module does not have dependents.');
        }

        $template['TITLE'] = sprintf(dgettext('boost', '%s Dependencies'), $module->getProperName());
        // H20101107.4 -	$content[] = PHPWS_Text::backLink() . '<br />';
        $content[] = dgettext('boost', 'The following modules depend on this module to function:');
        foreach ($dependents as $mod) {
            $dep_module = new PHPWS_Module($mod);
            $content[] = $dep_module->getProperName();
        }

        // H20101107.4 -	$content[] = PHPWS_Boost::uninstallLink($base_mod);
        $template['CONTENT'] = implode('<br />', $content);

        return PHPWS_Template::process($template, 'boost', 'main.tpl');
    }

    public function showDependency($base_module_title)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $module = new PHPWS_Module($base_module_title);
        $depend = $module->getDependencies();
        $template['TITLE'] = sprintf(dgettext('boost', '%s Module Dependencies'), $module->getProperName());

        $template['MODULE_NAME_LABEL']     = dgettext('boost', 'Module Needed');
        $template['VERSION_NEEDED_LABEL']  = dgettext('boost', 'Version required');
        $template['CURRENT_VERSION_LABEL'] = dgettext('boost', 'Current Version');
        $template['URL_LABEL']             = dgettext('boost', 'Module Web Site');
        $template['STATUS_LABEL']          = dgettext('boost', 'Status');

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
                $tpl['CURRENT_VERSION'] = dgettext('boost', 'Not installed');
            }

            if ($pass && version_compare($module['VERSION'], $mod_obj->getVersion(), '>')) {
                $pass = FALSE;
            }

            $tpl['URL'] = sprintf('<a href="%s" target="_blank">%s</a>', $module['URL'], dgettext('boost', 'More info'));

            if ($pass) {
                $tpl['STATUS_GOOD'] = dgettext('boost', 'Passed!');
            } else {
                $tpl['STATUS_BAD'] = dgettext('boost', 'Failed');
            }
            $template['module-row'][] = $tpl;
        }

        return PHPWS_Template::process($template, 'boost', 'dependency.tpl');
    }

  /**
     * deprecated
     */
    public function copyLocal($module_title)
    {
        return true;
    }

    // deprecated
    public function copyCore()
    {
        return true;
    }
}

?>