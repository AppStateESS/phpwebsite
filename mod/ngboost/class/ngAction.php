<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Hilmar Runge <ngwebsite.net>
 */

    define('NGANYHELP', '<div style="text-align:right;">'
                    . '<img class="ngAnyHelp" src="'.PHPWS_SOURCE_HTTP
                    . 'mod/ngboost/img/help.16.gif" alt=" ? " />'
                    . '</div>');
    define('NGJQMCLOSE', '<div style="text-align:right;">'
                    .  '<img id="ngjqmclose" class="jqmClose" src="'.PHPWS_SOURCE_HTTP
                    .  'mod/ngboost/img/close.16.gif" alt=" X " />'
                    .  '</div>');
    define('NGSAYOK', '<img id="ngok" src="'.PHPWS_SOURCE_HTTP.'mod/ngboost/img/ok.10.gif" alt=" ok " />');
    define('NGSAYKO', '<img id="ngko" src="'.PHPWS_SOURCE_HTTP.'mod/ngboost/img/ko.10.gif" alt=" fail " />');
    define('NGSP3',	'&nbsp;&nbsp;&nbsp;');
    define('NGBR',	'<br />');
	

class ngBoost_Action {

    public function __construct()
    {
	
		PHPWS_Core::initModClass('ngboost', 'ngForm.php');
		PHPWS_Core::initModClass('controlpanel', 'Panel.php');
					
		PHPWS_Core::initModClass('boost', 'Boost.php');
	}

    public function index()
    {
		if ($this) {
			switch ($_REQUEST['action']){
			case 'admin':
				if (isset($_REQUEST['xaop'])) {
					$this->indexBG();
				} else {
					$this->main();
				}
				break;
			case 'check_all':
				$this->ngCheckAll();
				break;
			}
		}
    }

    protected function main()
    {
		if ($this) {
			// FG interactive
			$boostPanel = new PHPWS_Panel('ngboost');
			$boostPanel->enableSecure();

			javascriptMod('ngboost', 'ng');
			Layout::addStyle('ngboost','style.css');

			$tpl['MOCO'] = NGANYHELP.ngBoost_Form::listModules('core_mods');
			$tpl['MOOT'] = NGANYHELP.ngBoost_Form::listModules('other_mods');
			$tpl['MONO'] = NGANYHELP.'Modules not suitable for this version - to do';
			$tpl['MONE'] = NGANYHELP.'New and community modules available for this version - to do';
			$tpl['MORE'] = NGANYHELP.ngBoost_Form::ngTabRepo();
			$tpl['DBAC'] = NGANYHELP.ngBoost_Form::ngTabDB();
			$tpl['TUNE'] = NGANYHELP.ngBoost_Form::ngTabTune();

			Layout::add(PHPWS_ControlPanel::display('<h2>Modules</h2>
			<div id="ngmsg" style="font-family: monospace;" class="jqmWindow">&nbsp;</div>
			<div id="ngpar" class="jqmWindow">&nbsp;</div>'
			. PHPWS_Template::process($tpl, 'ngboost', 'cptabs.tpl') ));
		}
	}
	
    protected function indexBG()
    {
		// BG actions
        switch ($_REQUEST['xaop']) {
        case 'a':
            $this->ngShowAbout($_REQUEST['p']);
            return;
            break;
        case 'bm':
            $this->ngBU($_REQUEST['p']);
            return;
            break;
        case 'B':
            $this->ngBuAll();
            return;
            break;
        case 'Br':
            $this->ngBuBranch();
            return;
            break;
        case 'bt':
            $this->ngBuTbl($_REQUEST['tn']);
            return;
            break;
        case 'btn':
            $this->ngBuTblAll($_REQUEST['rs']);
            return;
            break;
        case 'btm':
            $this->ngBuTblMod($_REQUEST['rs']);
            return;
            break;
        case 'bt1':
            $this->ngBuTbl1($_REQUEST['rs']);
            return;
            break;
        case 'c':
            $this->ngCheck($_REQUEST['p']);
            return;
            break;
        case 'C':
            $this->ngCheckAll();
            return;
            break;
        case 'crp':
            $this->ngCheckRepo();
            return;
            break;
        case 'd':
            $this->ngShowDep($_REQUEST['p']);
            return;
            break;
        case 'fs':
			$this->ngTuneFS();
            return;
            break;
        case 'fsd':
			$this->ngTuneFSdispl();
            return;
            break;
        case 'h':
            $this->ngAnyHelp($_REQUEST['h']);
            return;
            break;
        case 'dy':
            $this->ngBuDel($_REQUEST['fn']);
            return;
            break;
        case 'in':
            $this->ngInstall($_REQUEST['p']);
            return;
            break;
        case 'ldb':
            $this->ngListDB();
            return;
            break;
        case 'lrp':
            $this->ngListRepo();
            return;
            break;
        case 'ltar':
            $this->ngListTar($_REQUEST['fn']);
            return;
            break;
        case 'pas':
            $this->ngListPatos();
            return;
            break;
        case 'po':
            $this->ngListPato($_REQUEST['p']);
            return;
            break;
        case 're':
            $fn=$_SESSION['FG']['ngfn'][$_REQUEST['fn']];
            if (substr($fn,-4)=='.tgz' || substr($fn,-7)=='.tar.gz') {
                $this->ngReTar($_REQUEST['fn']);
            } else {
                if (substr($fn,-5)=='.data') {
					$this->ngReTbl($_REQUEST['fn']);
                } else {
                    $_SESSION['BG']=dgettext('ngboost','invalid file type');
                }
            }
            return;
            break;
        case 'ts':
			$this->ngTuneSources();
            return;
            break;
        case 'tS':
			$this->ngTuneSourceSet();
            return;
            break;
        case 'un':
            $this->ngUnInstall($_REQUEST['p']);
            return;
            break;
        case 'uc':
            // JUST TO WORK OUT - - -
            ngBoost_Action::ngUpdateCore();
            return;
            break;
        case 'up':
            $this->ngUpdate($_REQUEST['p']);
            return;
            break;
        case 'u':
            $this->ngShowDepUpon($_REQUEST['p']);
            return;
            break;
        case 'tget':
            $this->ngPickupTgz($_REQUEST['m']);
            return;
            break;
        }
    }

    protected function ngShowAbout($mod)
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

    protected function ngShowDep($mod)
    {
        $_SESSION['BG'] = NGJQMCLOSE.ngBoost_Action::showDependency($mod);
    }

    protected function ngShowDepUpon($mod)
    {
        $_SESSION['BG'] = NGJQMCLOSE.ngBoost_Action::showDependedUpon($mod);
    }

    protected function ngCheck($mod)
    {
        $cnt = ngBoost_Action::checkupdate($mod);

        $_SESSION['BG'] =
            $mod
            . '--'
            . $_SESSION['Boost_Needs_Update'][$mod]
            . '--'
            . NGJQMCLOSE.$cnt;
    }

    protected function ngCheckAll()
    {
			$mods = implode('--', PHPWS_Boost::getAllMods());
			$_SESSION['BG'] = 'core'.'--'.$mods;
    }

    protected function ngCheckRepo()
    {
        $rp=$this->ngGetRepositoryPath();
        $_SESSION['BG']='State of repository path ';
        $rp ? $_SESSION['BG'] .= NGSAYOK : $_SESSION['BG'] .= NGSAYKO;
    }

    protected function ngInstall($mod)
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

    protected function ngUnInstall($mod)
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

    protected function ngUpdate($mod)
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

    protected function ngGetRepositoryPath()
    {
        PHPWS_Core::initCoreClass('ngBackup.php');
        return ngBackup::getRepositoryPath();
    }

    protected function ngListRepo()
    {
                $rp=$this->ngGetRepositoryPath();
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
                        . '<td id="ngop'.md5($f).'">' . ngBoost_Form::ngTabLTar($f) . '</td></tr>';
                    }
                    $_SESSION['BG'] .= '</tbody><tr class="ngtrfoot"><td style="text-align:right;">'
                    . $fct . ' ' . dgettext('ngboost','files') . '</td>'
                    . '<td style="text-align:right;">' . sprintf("%u",round($szsum/1024/1024,0)) . '</td>'
                    . '<td>' . dgettext('ngboost','MB') . '</td></tr>';

                    $_SESSION['BG'] .= '</table>';
                }

    }

    protected function ngListDB()
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

                    $_SESSION['BG'] .= '<td>' . ngBoost_Form::ngTabListTables('m',$mod,$tbs) . '</td>';
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
                    $_SESSION['BG'] .= ngBoost_Form::ngTabListTable($mod,$tb);
                } else {
                    $_SESSION['BG'] .= 'not available';
                }
                $_SESSION['BG'] .= '</td>';
                $_SESSION['BG'] .= '<td><span id="ngt6t'.md5($tb).'"></span></td>';
                $_SESSION['BG'] .= '</tr>';
            }
        }
        $_SESSION['BG'] .= 	'</tbody></table><hr />'
						.	'<div style="text-align:center;">'
						.	'<a href="javascript:ngBuTs1(\'n\',\''.md5('all').'\')">BackupAllTables</a>'
						.	'</div>';
    }

    protected function ngListPato($pato)
    {
				$distro = ''.PHPWS_Settings::get('ngboost', 'distro');
				$distropath = str_replace('/modules/','/patos/',ngBoost_Action::ngGetDistro());
				$xmlfile = $distropath . $pato . '/pato.xml';
				$xml = @simplexml_load_file($xmlfile);
				if ($xml) {
					if (is_object($xml)) {
						$_SESSION['BG']=strip_tags($xml->pato->longdesc, '<br>');
						$_SESSION['BG'].=NGBR.'<i>Author: </i>'.strip_tags($xml->pato->author);
						$_SESSION['BG'].=NGBR.'<i>Supplies to:</i><pre>'.strip_tags($xml->pato->relate).'</pre>';
						$_SESSION['BG'].='<i>Dependencies:</i>';
						foreach ($xml->pato->dependency->scope as $dep) {
							$_SESSION['BG'].=NGBR.strip_tags($dep->title);
							$_SESSION['BG'].=' <i>Scope:</i> '.strip_tags($dep->type);
							$_SESSION['BG'].=' <i>Resource:</i> '.strip_tags($dep->path);
							if ($dep->version) {
								$_SESSION['BG'].=' <i>Version:</i> '.strip_tags($dep->version);
							}
						}
					}
				}
	}

    protected function ngListPatos()
    {
				$_SESSION['BG']='';
				$distro = ''.PHPWS_Settings::get('ngboost', 'distro');
				$distropath = str_replace('/modules/','/patos/',ngBoost_Action::ngGetDistro());
				$xdirfile = $distropath . 'patos.xml';
				$xdir = @simplexml_load_file($xdirfile);
				if ($xdir) {
					if (is_object($xdir)) {
					
						$_SESSION['BG'] .= '<table class="ngtable">'
						.	'<thead class="ngthead"><tr>'
						.	'<th>' . dgettext('ngboost','PatchOption') . '</th>'
						.	'<th>' . dgettext('ngboost','Scope') . '</th>'
						.	'<th>' . dgettext('ngboost','Distro') . '</th>'
						.	'<th>' . dgettext('ngboost','Version') .'</th>'
						.	'<th>' . dgettext('ngboost','Description') .'</th>'
						.	'<th>' . dgettext('ngboost','Commands') .'</th>'
						.	'</tr></thead>'
						.	'<tbody class="ngtbody">';
						$cl='bgcolor1';
						foreach ($xdir->entry as $patdir) {
							$xmlfile = $distropath . $patdir . '/pato.xml';
							$xml = @simplexml_load_file($xmlfile);
							$cl=='bgcolor1' ? $cl='bgcolor2' : $cl='bgcolor1';
							if ($xml) {
								if (is_object($xml)) {
									$alnk = '<a href="javascript:ngPatoDesc(\''.$xml->pato->title.'\')">more</a>';
									$_SESSION['BG'].='<tr class="'.$cl.'">'
									.	'<td>'.strip_tags($xml->pato->title).'</td>'
									.	'<td>'.strip_tags($xml->pato->scope).'</td>'
									.	'<td>'.$distro.'</td>'
									.	'<td>'.strip_tags($xml->pato->version).'</td>'
									.	'<td>'.strip_tags($xml->pato->shortdesc) . NGSP3 . $alnk
									.		'<p id="ngpat'.$patdir.'" class="ngpat" style="display:none;">'
							//		.		strip_tags($xml->pato->longdesc, '<br>')
									.		'</p><p id="ngpatx'.$patdir.'"></p></td>'
									.	'<td>'.'Apply'.'</td></tr>';
								}
							} else {
								$_SESSION['BG'].='<tr class="'.$cl.'">'
								.	'<td>'.$patdir.'</td><td>'.$distro.'</td><td>---</td><td>---</td><td>'
								.	dgettext('ngboost','not available').'</td><td></td></tr>';
							}
						}
						
						$_SESSION['BG'] .= '</tbody></table>';
						
						
					}
				}
				
	}
	
    protected function ngListTar($fnc)
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

    protected function ngReTar($fnc)
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
                $_SESSION['BG'] = NGJQMCLOSE . $re . NGSAYOK ;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Restore' . NGSAYKO . $cc . ',' . $re;
            }
        }
    }

    protected function ngBU($mod)
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

    protected function ngBuAll($mod)
    {
        $mods = 'core--' . implode('--', PHPWS_Boost::getAllMods());
        $_SESSION['BG'] = $mods;
    }

    protected function ngBuDel($fnc)
    {
        $_SESSION['BG']=' ';
        if (isset($_SESSION['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION['FG']['ngfn'][$fnc];
            $cc=@unlink($this->ngGetRepositoryPath().$fn);
            if ($cc) {
                $_SESSION['BG'] = '#ngop'.$fnc.'--purged--' . NGJQMCLOSE . $fn. ' ' . 'purged' . ' ' . NGSAYOK;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Purge' . NGSAYKO . $cc . ',' . $fn;
            }
        }
    }

    protected function ngBuTbl($tnc)
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

    protected function ngBuTblMod($mod)
    {
        $_SESSION['BG'] .= $mod;
        foreach ($_SESSION['FG']['ngtn']['0m'.$mod] as $tb) {
            $_SESSION['BG'] .= '--' . md5($tb);
        }
    }
    protected function ngBuTblAll($rs)
    {
        $_SESSION['BG']=implode('--',array_keys($_SESSION['FG']['ngtm']));
    }

    protected function ngReTbl($fnc)
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

    protected function ngTuneFS()
    {
		$cnt = '<h4>File system permissions'.NGSP3
        .	'<a href="javascript:ngPlain(\'fsd\')">Display</a>'
		.	'</h4>'
        .	'<p id="ngmsgt71"></p>';
		$_SESSION['BG'] = $cnt;
	}

    protected function ngTuneFSdispl()
    {
		if (1==2) {
        PHPWS_Core::initCoreClass('ngBackup.php');
        $ngbu = new ngBackup();
        $r=$ngbu->backupMod('');
        $cc=substr($r,0,1);
        $re=substr($r,1);
		$_SESSION['BG'] = $r;
		}
		if (1==2) {
		$_SESSION['FG']['ngfn']['.sysbu'] = '.sysbu.20110111-132843.fs.tgz';
		$this->ngListTar('.sysbu');
		}
		$_SESSION['BG'] = 'FFU';
		
	}
	
    protected function ngTuneSources()
    {
		$chka = $chkn = $chks = '';
		$in = ''.PHPWS_Settings::get('ngboost', 'distro');
		switch ($in) {
		case 'asu':
			$chka=' checked="checked" ';
			break;
		case 'ngws':
			$chkn=' checked="checked" ';
			break;
		case 'sf':
			$chks=' checked="checked" ';
			break;
		}

		$cnt = '<h4>Distro selection</h4>';
		$onc = ' type="radio" name="distro" onclick="javascript:ngOnC()" ';
		
		$cnt.='<label><input'.$onc.$chka.'value="asu"  />' .htmlentities(BYASU) . '</label>' . NGBR
		.	 '<label><input'.$onc.$chkn.'value="ngws" />' .htmlentities(BYNGWS). '</label>' . NGBR
		.	 '<label><input'.$onc.$chks.'value="sf"   />' .htmlentities(BYSF)  . '</label>';
		$_SESSION['BG'] = $cnt;
	}
	
    protected function ngTuneSourceSet()
    {
		$_SESSION['BG'] = ' ';
		$p	= urlencode($_REQUEST['p']);
		if ($p=='asu' || $p=='ngws' || $p=='sf') {
			PHPWS_Settings::set('ngboost', 'distro', $p);
			PHPWS_Settings::save('ngboost');
			$_SESSION['BG'] = dgettext('ngboost','Distro set to') . ' ' . $p;
		}
	}

    protected function ngPickupTgz($mod)
    {
        $repo = $this->ngGetRepositoryPath();
        if (isset($_SESSION['FG'][$mod])) {
            $tgzf = array_pop(explode('/', $_SESSION['FG'][$mod]));
            if ($repo) {
                if (!file_exists($repo.'/'.$tgzf)) {
                    $cc = @copy($_SESSION['FG'][$mod], $repo.'/'.$tgzf);
                    if ($cc) {
						$msg = dgettext('ngboost','successfully copied');
					} else {
                        $_SESSION['BG'] = $mod.'--Fail: '.$_SESSION['FG'][$mod] . ' to ' . $repo . '/' . $tgzf;
						return;
					}
                } else {
                    $msg = dgettext('ngboost', 'just exists in repository');
				}
				$tgzmd5=@md5_file($repo.'/'.$tgzf);
				$xmlfile = ngBoost_Action::ngGetDistro() . $mod . '/check.xml';
				$xml = @simplexml_load_file($xmlfile);
				if ($xml) {
					if (is_object($xml)) {
						if (strtoupper($tgzmd5)==strtoupper($xml->module->md5sum)) {
							$_SESSION['BG'] = $mod.'--OK: ' . $tgzf 
							. ' ' . $msg . ', ' . dgettext('ngboost', 'verified');
						} else {
							@unlink($repo.'/'.$tgzf);
							$_SESSION['BG'] = $mod.'--KO: ' . $tgzf 
							. ' ' . dgettext('ngboost', 'checksum verification error');
						}
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

    public function ngGetDistro()
    {
		$in = ''.PHPWS_Settings::get('ngboost', 'distro');
		switch ($in) {
		case 'asu':
			$thatfile = BYASU;
			break;
		case 'ngws':
			$thatfile = BYNGWS;
			break;
		case 'sf':
			$thatfile = BYSF;
			break;
		default:
			$thatfile = '';
		}
		return $thatfile;
	}
	
    protected function ngAnyHelp($help)
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
        case 'ngbstcptab7':
            $helpfile = 'cp.tune.html';
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
		// H 20110110 (
		// $file just refers to the check.xml
		
		$thatfile = ngBoost_Action::ngGetDistro();
		if (!empty($thatfile)) {
			$file = $thatfile.$mod_title.'/check.xml';
		}
		// H)
		
        if (empty($file)) {
            return dgettext('ngboost', 'Update check file not found.');
        }

        $full_xml_array = PHPWS_Text::xml2php($file, 2);

        if (empty($full_xml_array)) {
            return dgettext('ngboost', 'Update check file not found.');
        }
        $version_info = PHPWS_Text::tagXML($full_xml_array);
        $template['LOCAL_VERSION_LABEL'] = dgettext('ngboost', 'Local version');
        $template['LOCAL_VERSION'] = $module->getVersion();
        $template['STABLE_VERSION_LABEL'] = dgettext('ngboost', 'Current stable version');
        if (!isset($version_info['VERSION'])) {
            $template['STABLE_VERSION'] = dgettext('ngboost', 'Source XML error');
            $version_info['VERSION'] = $module->getVersion();
        } else {
            $_SESSION['Boost_Needs_Update'][$mod_title] = $version_info['VERSION'];
            $template['STABLE_VERSION'] = $version_info['VERSION'];
        }

        if (version_compare($version_info['VERSION'], $module->getVersion(), '>')) {
            $template['CHANGES_LABEL'] = dgettext('ngboost', 'Changes');
            $template['CHANGES'] = htmlspecialchars($version_info['CHANGES']);
            $template['UPDATE_AVAILABLE'] = dgettext('ngboost', 'An update is available');
            $template['PU_LINK_LABEL'] = dgettext('ngboost', 'Copy into repository');
            // H 20101111 (
                $tgz = array_pop(explode('/', $version_info['DOWNLOAD']));
                // H 20110110 (
				if (!empty($thatfile)) {
					$_SESSION['FG'][$module->title] = $thatfile . $module->title . '/' . $tgz;
				// )
				} else {
					$_SESSION['FG'][$module->title] = $version_info['DOWNLOAD'];
				}
                $lnk = '<a href="javascript:ngPickup(\''.$module->title.'\')">'
                        .$tgz . '</a><div id="ngpickup'.$module->title.'"></div>';
            // )
            $template['PU_LINK'] = $lnk;
            $template['DL_PATH_LABEL'] = dgettext('ngboost', 'or download by yourself from here');
            $template['DL_PATH'] = '<a href="' . $_SESSION['FG'][$module->title] . '">' . $_SESSION['FG'][$module->title] . '</a>';
            $template['MD5_LABEL'] = dgettext('ngboost', 'MD5 Sum');
            $template['MD5'] = $version_info['MD5SUM'];

            if (isset($version_info['DEPENDENCY'][0]['MODULE'])) {
                $template['DEPENDENCY_LABEL'] = dgettext('ngboost', 'Dependencies');
                $template['DEP_TITLE_LABEL'] = dgettext('ngboost', 'Module title');
                $template['DEP_VERSION_LABEL'] = dgettext('ngboost', 'Version required');
                $template['DEP_STATUS_LABEL'] = dgettext('ngboost', 'Status');

                foreach ($version_info['DEPENDENCY'][0]['MODULE'] as $dep_mod) {
                    $check_mod = new PHPWS_Module($dep_mod['TITLE'], false);

                    if ($check_mod->_error) {
                        $status = dgettext('ngboost', 'Not installed');
                        $row['DEP_STATUS_CLASS'] = 'red';
                    } elseif (version_compare($check_mod->version, $dep_mod['VERSION'], '<')) {
                        $status = dgettext('ngboost', 'Needs upgrading');
                        $row['DEP_STATUS_CLASS'] = 'red';
                    } else {
                        $status = dgettext('ngboost', 'Passed!');
                        $row['DEP_STATUS_CLASS'] = 'green';
                    }
                    $row['DEP_TITLE'] = $dep_mod['PROPERNAME'];
                    $row['DEP_VERSION'] = $dep_mod['VERSION'];
                    $row['DEP_ADDRESS'] = sprintf('<a href="%s">%s</a>',
                    $dep_mod['URL'], dgettext('ngboost', 'Download'));
                    $row['DEP_STATUS'] = $status;
                    $template['dependent-mods'][] = $row;
                }
            }
        }
        else {
            $template['NO_UPDATE'] = dgettext('ngboost', 'No update required.');
        }

        $template['TITLE'] = dgettext('ngboost', 'Module') . ': ' . $module->getProperName(TRUE);
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
        return $boost->uninstall();
    }

    public function updateCore()
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $content[] = dgettext('ngboost', 'Updating core');

        require_once PHPWS_SOURCE_DIR . 'core/boost/update.php';

        $ver_info = PHPWS_Core::getVersionInfo(false);

        $content[] = dgettext('ngboost', 'Processing update file.');
        $result = core_update($content, $ver_info['version']);

        if ($result === true) {
            $db = new PHPWS_DB('core_version');
            $file_ver = PHPWS_Core::getVersionInfo();
            $db->addValue('version', $file_ver['version']);
            $result = $db->update();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = dgettext('ngboost', 'An error occurred updating the core.');
            } else {
                $content[] = dgettext('ngboost', 'Core successfully updated.');
            }
        } elseif (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = dgettext('ngboost', 'An error occurred updating the core.');
        } else {
            $content[] = dgettext('ngboost', 'An error occurred updating the core.');
        }

        return implode('<br />', $content);
    }

    public static function updateModule($module_title)
    {
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
            return dgettext('ngboost', 'This module does not have dependents.');
        }

        $template['TITLE'] = sprintf(dgettext('ngboost', '%s Dependencies'), $module->getProperName());
        // H20101107.4 -	$content[] = PHPWS_Text::backLink() . '<br />';
        $content[] = dgettext('ngboost', 'The following modules depend on this module to function:');
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
        $template['TITLE'] = sprintf(dgettext('ngboost', '%s Module Dependencies'), $module->getProperName());

        $template['MODULE_NAME_LABEL']     = dgettext('ngboost', 'Module Needed');
        $template['VERSION_NEEDED_LABEL']  = dgettext('ngboost', 'Version required');
        $template['CURRENT_VERSION_LABEL'] = dgettext('ngboost', 'Current Version');
        $template['URL_LABEL']             = dgettext('ngboost', 'Module Web Site');
        $template['STATUS_LABEL']          = dgettext('ngboost', 'Status');

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
                $tpl['CURRENT_VERSION'] = dgettext('ngboost', 'Not installed');
            }

            if ($pass && version_compare($module['VERSION'], $mod_obj->getVersion(), '>')) {
                $pass = FALSE;
            }

            $tpl['URL'] = sprintf('<a href="%s" target="_blank">%s</a>', $module['URL'], dgettext('ngboost', 'More info'));

            if ($pass) {
                $tpl['STATUS_GOOD'] = dgettext('ngboost', 'Passed!');
            } else {
                $tpl['STATUS_BAD'] = dgettext('ngboost', 'Failed');
            }
            $template['module-row'][] = $tpl;
        }

        return PHPWS_Template::process($template, 'boost', 'dependency.tpl');
    }

}

?>