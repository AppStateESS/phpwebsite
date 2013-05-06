<?php

/**
 * @author Hilmar Runge <ngwebsite.net>
 */

	// me 
	define ('NGBOOST','ngboost');
    define('NGANYHELP', '<div style="text-align:right;">'
                    . '<img class="ngAnyHelp" src="'.PHPWS_SOURCE_HTTP
                    . 'mod/ngboost/img/help.16.gif" alt=" ? " />'
                    . '</div>');
    define('NGJQMCLOSE', '<div class="ngjqmclose"><div class="ngjqmtitle"></div>'
					.  '<div class="ngjqmimg">'
                    .  '<img id="ngjqmclose" class="jqmClose" src="'.PHPWS_SOURCE_HTTP
                    .  'mod/ngboost/img/close.16.gif" alt=" X " /></div></div>');
    define('ISTRUE',  't');
    define('ISFALSE', 'f');
	// may be shared
	if (!defined('NGSAYOK')) {
		define('NGSAYOK', '<img id="ngok" src="'.PHPWS_SOURCE_HTTP.'mod/ngboost/img/ok.10.gif" alt=" ok " />');
	}
	if (!defined('NGSAYKO')) {
		define('NGSAYKO', '<img id="ngko" src="'.PHPWS_SOURCE_HTTP.'mod/ngboost/img/ko.10.gif" alt=" fail " />');
	}
	if (!defined('NGSP3')) {
		define ('NGSP3', '&nbsp;&nbsp;&nbsp;');
	}
	if (!defined('NGBR')) {
		define ('NGBR', '<br />');
	}
	

class ngBoost_Action {

	const PATXAOP = '/[a-zA-Z]*[a-zA-Z]/';
	const PATXMOD = '/[a-z]*[a-z0-9]/';
	const PATXA32 = '/[a-z0-9]/';
	const PATXVSN = '/^[a-zA-Z0-9\.\,]*$/';
	//const PATSORT = '/[a-zA-Z0-9\.\_\-\ ]/';
	
	var $context = '';
	
 	/** @var bool $this->isbranch state */
	var $isbranch;
	
	
    public function __construct()
    {
		$this->context=PHPWS_Core::getCurrentModule();
		// &	if (Current_user::isLogged()) { } // ractest
		PHPWS_Core::initModClass('ngboost', 'ngForm.php');
    PHPWS_Core::initCoreClass('ngBackup.php');
				
		PHPWS_Core::initModClass('controlpanel', 'Panel.php');
		PHPWS_Core::initModClass('boost', 'Boost.php');
		
		$this->isbranch = (bool)PHPWS_Core::isBranch();
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
			case 'sunset':
				// will not operate without sunset pato applied
				if (file_exists(PHPWS_SOURCE_DIR.'mod/ngboost/class/ngUnSet.php')) {
					PHPWS_Core::initModClass('ngboost', 'ngUnSet.php');
					$ngunset = new ngBoost_UnSet('ngboost');
					$ngunset->ngSunSet();
				}
				break;
			}
		}
    }

    protected function main()
    {
		if ($this) {
		//if ($this->context==NGBOOST) {}

			javascript('jquery_ui');
			javascriptMod('ngboost', 'ngboost');
			Layout::addStyle('ngboost','style.css');

			/** 	general verification of directories (img files log) */
			$chkdir = array();
			if (!PHPWS_Boost::checkDirectories($chkdir)) {
				$tpl['DIRECTORIES'] = implode('<br />', $chkdir);
			}
			// )
			
			unset($_SESSION[NGBOOST]['BUSIGN']);
		
			$_SESSION[NGBOOST]['ml']=array();
			$ngboostform = new ngBoost_Form;
			$ngboostform->_ngGetModules();
		
		//	$ngboostform = new ngBoost_Form('ngboost');
			$tpl['MOCO'] = NGANYHELP.$ngboostform->ngTabModules('core');
			$tpl['MOOT'] = NGANYHELP.$ngboostform->ngTabModules('noco');
			$tpl['DIST'] = NGANYHELP.$ngboostform->ngTabDistro();
			$tpl['REPO'] = NGANYHELP.$ngboostform->ngTabRepo();
			$tpl['DBAC'] = NGANYHELP.$ngboostform->ngTabDB();
			$tpl['TUNE'] = NGANYHELP.$ngboostform->ngTabTune();

			Layout::add(PHPWS_ControlPanel::display('<h2>ngBoost (boost2.0)</h2>
			<div id="ngmsg" class="jqmWindow">&nbsp;</div>
			<div id="ngpar" class="jqmWindow">&nbsp;</div>'
			. PHPWS_Template::process($tpl, 'ngboost', 'cptabs.tpl') ));
		}
	}
	
    protected function indexBG()
    {
		// BG actions
		$xaop=isset($_REQUEST['xaop'])?(preg_replace(self::PATXAOP, '', $_REQUEST['xaop'])?'':$_REQUEST['xaop']):'';
		$xmod=isset($_REQUEST['p'])?(preg_replace(self::PATXMOD, '', $_REQUEST['p'])?'':$_REQUEST['p']):'';
		$xa32=isset($_REQUEST['rs'])?(preg_replace(self::PATXA32, '', $_REQUEST['rs'])?'':$_REQUEST['rs']):'';
		$xvsn=isset($_REQUEST['v'])?(preg_replace(self::PATXVSN, '', $_REQUEST['v'])?'':$_REQUEST['v']):'';
        switch ($xaop) {
        case 'a':
            $this->ngShowAbout($xmod);
            return;
            break;
        case 'bm':
            $this->ngBU($xmod);
            return;
            break;
        case 'bmt':
            $this->ngBuModDB($xmod);
            return;
            break;
        case 'B':
            $this->ngBuAll();
            return;
            break;
        case 'br':
            $this->ngBuBranch();
            return;
            break;
        case 'bt':
            $this->ngBuTbl($_REQUEST['tn']);
            return;
            break;
        case 'btn':
            $this->ngBuTblAll();
            return;
            break;
        case 'btm':
            $this->ngBuTblMod($xa32);
            return;
            break;
        case 'bt1':
            $this->ngBuTbl1($xa32);
            return;
            break;
        case 'c':
            $this->ngCheck($xmod);
            return;
            break;
        case 'cn':
            $this->ngCheckNew($xmod);
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
            $this->ngShowDep($xmod);
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
        case 'fsp':
			$this->ngFsPerms();
            return;
            break;
        case 'fspy':
			$this->ngFsPermsUnlock();
            return;
            break;
        case 'fspz':
			$this->ngFsPermsLock();
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
            $this->ngInstall($xmod);
            return;
            break;
        case 'lbu':
            $this->ngListRepoBus();
            return;
            break;
        case 'ls':
            $this->ngListRepoBusDir($_REQUEST['fn']);
            return;
            break;
        case 'lbl':
            $this->ngListBoostLog();
            return;
            break;
        case 'lel':
            $this->ngListErrorLog();
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
		case 'ml':
			$this->ngListML();
			return;
			break;
        case 'pa':
            $this->ngApplyPato($xmod);
            return;
            break;
        case 'pas':
            $this->ngListPatos();
            return;
            break;
        case 'po':
            $this->ngListPato($xmod);
            return;
            break;
        case 'sr':
            $this->ngSelRel($xvsn);
            return;
            break;
        case 'sry':
            $this->ngMyRel($xvsn);
            return;
            break;
        case 're':
            $fn=$_SESSION[NGBOOST]['FG']['ngfn'][$_REQUEST['fn']];
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
        case 'tsl':
			$this->ngTuneSourceList();
            return;
            break;
        case 'un':
			$xconfirm=preg_replace(self::PATXMOD, '', $_REQUEST['confirm'])?'':$_REQUEST['confirm'];
            $this->ngUnInstall($xmod,$xconfirm);
            return;
            break;
        case 'urm':
			$xconfirm=preg_replace(self::PATXMOD, '', $_REQUEST['confirm'])?'':$_REQUEST['confirm'];
            $this->ngRemoMod($xmod,$xconfirm);
            return;
            break;
        case 'up':
			if ($xmod=='core') {
				$this->ngUpdateCore();
			} else {
				$this->ngUpdate($xmod);
			}
            return;
            break;
        case 'u':
            $this->ngShowDepUpon($xmod);
            return;
            break;
        case 'tget':
            $this->ngPickupTgz();
            return;
            break;
        case 'tchk':
            $this->ngCheckTgz();
            return;
            break;
        case 'tdec':
            $this->ngDecomTgz();
            return;
            break;
        case 'texp':
            $this->ngExpandTgz();
            return;
            break;
        case 'xrp':
            $this->ngXrefRepo();
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
		if (isset($c2[1])) {
			$c3 = explode('</body>',$c2[1]);
			$c4 = NGJQMCLOSE.'<div class="ngscauto">'
			.	'<h1>' . str_replace('onclick="window.close()"', 'class="jqmClose"',$c3[0]);
			$_SESSION['BG']=str_replace('class="ngjqmtitle">','class="ngjqmtitle">about &#171;'.$mod.'&#187;',$c4)
			.	'</div>';
		} else {
			$_SESSION['BG']=str_replace('class="ngjqmtitle">','class="ngjqmtitle">about &#171;'.$mod.'&#187;',NGJQMCLOSE)
			.	'<div class="ngscauto">' . str_replace('onclick="window.close()"', 'class="jqmClose"',$c1);
		}
    }

    protected function ngShowDep($mod)
    {
		$cnt=$this->_showDependency($mod);
		$jqmclose = $this->_varyJQM($mod,dgettext('ngboost','Dependencies of'));
         $_SESSION['BG'] = $jqmclose.$cnt;
   }

    protected function ngShowDepUpon($mod)
    {
		$cnt=$this->_showDependedUpon($mod);
		$jqmclose = $this->_varyJQM($mod,dgettext('ngboost','Dependents of'));
        $_SESSION['BG'] = $jqmclose.$cnt;
    }

    protected function ngCheck($mod)
    {
        $cnt = $this->_checkupdate($mod);
		$_SESSION['BG'] =
		$mod
        . '--'
        . $_SESSION[NGBOOST]['FG']['ngvx'][$mod]
        . '--'
        . str_replace('class="ngjqmtitle">','class="ngjqmtitle">check &#171;'.$mod.'&#187;',NGJQMCLOSE)
		. $cnt;
    }

    protected function ngCheckAll()
    {
		$ngboostform = new ngBoost_Form;
		$ngboostform->_ngGetModules();
		$_SESSION['BG'] = implode('--', array_keys($_SESSION[NGBOOST]['ml']));
    }

    protected function ngCheckNew($mod)
    {
        $vsn = $this->_checknew($mod);
		if ($vsn) {
			$_SESSION['BG'] =
			$mod
			. '--'
			.$vsn
			. '--'
			. '<a href="javascript:ngPickup(\''.$mod.'\',\''.'1'.'\')">get</a>'
			. $this->ngTplChkUpd('0'.$mod);
		} else {
			$_SESSION['BG'] =
			$mod
			. '--unknown--check.xml error';
		}
	}
	
    protected function ngCheckRepo()
    {
        $rp=$this->ngGetRepositoryPath();
        $_SESSION['BG']='State of repository path ';
        $rp ? $_SESSION['BG'] .= NGSAYOK : $_SESSION['BG'] .= NGSAYKO;
    }

    protected function ngInstall($mod)
    {
		$jqmclose = $this->_varyJQM($mod,'install');
		$result = $this->_installModule($mod);

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'no--'.$mod.'--';
            $content[] = $jqmclose.dgettext('ngboost', 'An error occurred while installing this module.')
                        . ' ' . dgettext('ngboost', 'Please check your error logs.');
        } else {
            $content[] = 'ok--'.$mod.'--';
            $content[] = $jqmclose.'<div class="ngscauto">'.$result.'</div>';
			$_SESSION[NGBOOST]['ml'][$mod]['in']='t';
			$_SESSION[NGBOOST]['ml'][$mod]['vdb']=$_SESSION[NGBOOST]['ml'][$mod]['vfs'];
        }

        $_SESSION['BG'] = implode('',$content);
    }

    protected function ngUnInstall($mod,$confirm)
    {
		$jqmclose = $this->_varyJQM($mod,'uninstall');
		$addmsg='';

        if ($confirm === $mod) {
			if ($mod=='ngboost') {
				// puh, thats me
				$addmsg='<div class="ngmsg"><b>*** '
				.dgettext('ngboost','After 5 seconds going to classic boost').' ***</b></div>';
			}
            // 1st status feedback, 2nd mod, 3rd flip action translated
            $content[] = 'ok--'.$mod.'--'.dgettext('ngboost', 'Install').'--';
            $content[] = $jqmclose.$addmsg.'<div class="ngscauto">'.$this->_uninstallModule($_REQUEST['p']).'</div>';
			$_SESSION[NGBOOST]['ml'][$mod]['in']='f';
			$_SESSION[NGBOOST]['ml'][$mod]['vdb']=null;
        } else {
            $content[] = 'no--'.$mod.'--'.dgettext('ngboost', 'Uninstall').'--';
            $content[] = $jqmclose.dgettext('ngboost', 'Uninstall not confirmed');
        }

        $_SESSION['BG'] = implode('', $content);
    }

    protected function ngRemoMod($mod,$confirm)
    {
        if ($confirm === $mod) {
			if ($_SESSION[NGBOOST]['ml'][$mod]['in'] === ISFALSE && $this->isbranch==false) {
				$dir=PHPWS_SOURCE_DIR.'mod/'.$mod.'/';
				$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
								RecursiveIteratorIterator::CHILD_FIRST);
				foreach ($iterator as $path) {
					if ($path->isDir()) {
						@rmdir($path->__toString());
					} else {
						@unlink($path->__toString());
					}
				}
				@rmdir($dir);
				// 1st status feedback, 2nd mod
				$_SESSION['BG'] = 'ok--'.$mod.'--0';
			}
        }
    }

    protected function ngUpdate($mod)
    {
		$jqmclose = $this->_varyJQM($mod,'update');
		$car=$this->_updateModule($mod);

        $_SESSION['BG'] = 'ok--'.$mod.'--'
        . 	$jqmclose.'<div class="ngscauto">'.implode('<br />',$car).'</div>';
    }

    protected function ngUpdateCore()
    {
		$jqmclose = $this->_varyJQM('core','update');
		$car=$this->_updateCore();

        $_SESSION['BG'] = 'ok--core--'
        . 	$jqmclose.$car;
    }

    protected function ngGetRepositoryPath()
    {
        return ngBackup::getRepositoryPath();
    }

    protected function ngListRepo()
    {
                $rp=$this->ngGetRepositoryPath();
                $_SESSION['BG']='<b>'.dgettext('ngboost','Content of repository').'</b> ('.$this->_reportMem().')<br />';
                $filenames=array();
                $dir=opendir($rp);
                while (FALSE !== ($file = readdir ($dir))) {
                    if ($file != '..' and substr($file,0,1) != '.') {
                        $filenames[]=$file;
                    }
                }
                closedir($dir);
                if (count($filenames)==0) {
                    $_SESSION['BG'] .= NGSAYKO . '  <i>' . 'is empty' . '</i><br />';
                } else {
                    sort($filenames);
                    $cl='bgcolor1';
                    $_SESSION['BG'] .= '<table id="ngbsttbrp" class="ngtable">'
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
	
    protected function ngXrefRepo()
    {
        $_SESSION['BG']='<b>'.dgettext('ngboost','Branch list of repository').'</b> ('.$this->_reportMem().')<br />';
		if ($this->isbranch) {
			$_SESSION['BG'].=dgettext('ngboost', 'Xref is not supported in branches');
		} else {
			if (Current_User::authorized('branch')) {
				PHPWS_Core::initModClass('branch', 'Branch_Admin.php');
				$branch_admin = new Branch_Admin;
				$branches=$branch_admin->getBranches(true);
				if (is_array($branches)) {
					$rp=$this->ngGetRepositoryPath();
					$cl='bgcolor1';
					$_SESSION['BG'] .= '<table id="ngbsttbrp" class="ngtable">'
					.	'<thead class="ngthead"><tr><th>' .dgettext('ngboost','Branch')
					.	'</th class="ngthead"><th>'.dgettext('ngboost','Dir')
					.	'<th>'.dgettext('ngboost','Perms').'</th>'
					.	'</th><th>'.dgettext('ngboost','Url').'</th><th>Repository</th></tr></thead>'
					.	'<tbody class="ngtbody">';
					foreach ($branches as $branch) {
						$cl=='bgcolor1' ? $cl='bgcolor2' : $cl='bgcolor1';
						$subdir=str_replace('/','.',trim(str_replace($_SERVER['SERVER_NAME'],'',$branch->url),'/'));
						$u=explode('/',$_SERVER['DOCUMENT_ROOT']);
						array_pop($u);
						$compath=implode('/',$u);
						$reposit=$compath.'/.repository/'.$subdir.'/';
						$_SESSION['BG'].='<tr><td>'.$branch->branch_name.'</td><td>'
						.	str_replace($_SERVER['DOCUMENT_ROOT'].'/','',$branch->directory).'</td>'
						.	'<td>'.ngBackup::_cvFilePerms(fileperms($branch->directory)).'</td>'
						.	'<td><a href="http://'.$branch->url.'/">'.$branch->url.'</a></td>'
						.	'<td>'.(file_exists($reposit)?NGSAYOK.' '.count(glob($reposit.'*'))
						.		' '.dgettext('ngboost','files'):NGSAYKO)
						.	'</td></tr>';
					}
					$_SESSION['BG'] .= '</tbody></table>';
				} else {
					$_SESSION['BG'] .= dgettext('ngboost', 'No branches defined on this site');
				}
			}
		}
	}

    protected function ngListRepoBus()
    {
                $rp=$this->ngGetRepositoryPath();
                $_SESSION['BG']='<b>'.dgettext('ngboost','Backup sets of repository').'</b> ('.$this->_reportMem().')<br />';
                $filenames=array();
                $dir=opendir($rp);
                while (FALSE !== ($file = readdir ($dir))) {
                    if (substr($file,0,11) == '.ngbu.full.' and substr($file,-4)=='.txt') {
                        $filenames[]=$file;
                    }
                }
                closedir($dir);
                if (count($filenames)==0) {
                    $_SESSION['BG'] .= NGSAYKO . '  <i>' . 'no backup sets available' . '</i><br />';
                } else {
                    sort($filenames);
                    $cl='bgcolor1';
                    $_SESSION['BG'] .= '<table id="ngbsttbrp" class="ngtable">'
                    .	'<thead class="ngthead"><tr><th>' .dgettext('ngboost','BackupSet')
                    .	'</th><th>'.dgettext('ngboost','Commands').'</th></tr></thead>'
                    .	'<tbody class="ngtbody">';
                    $fct=0;
                    foreach ($filenames as $f) {
                        $cl=='bgcolor1' ? $cl='bgcolor2' : $cl='bgcolor1';
                        $fct++;
                        $_SESSION['BG'] .= '<tr class="'.$cl.'"><td>' . substr($f,6,-4) . '</td>'
                        . '<td id="ngop'.md5($f).'">' . ngBoost_Form::ngTabLBus($f) . '</td></tr>';
                    }
                    $_SESSION['BG'] .= '</tbody><tr class="ngtrfoot"><td style="text-align:right;">'
                    . $fct . ' ' . dgettext('ngboost','backup sets') . '</td>'
                    . '<td></td></tr>';

                    $_SESSION['BG'] .= '</table>';
                }
	}
	
    protected function ngListRepoBusDir($fnc)
    {
        $_SESSION['BG']='';
        if (isset($_SESSION[NGBOOST]['FG']['ngfnbus'][$fnc])) {
            $fn=$_SESSION[NGBOOST]['FG']['ngfnbus'][$fnc];
			$rp=ngBackup::getRepositoryPath();
			if (file_exists($rp.$fn)) {
				$re=str_replace("\n",'<br />',file_get_contents($rp.$fn));
                $_SESSION['BG'] = NGJQMCLOSE . '<h3>'.substr($fn,6,-4)
				.	'</h3><div style="height:400px; overflow:auto;">'.$re.'</div>';
			} else {
                $_SESSION['BG'] = NGJQMCLOSE . 'No File' . NGSAYKO;
			}
        }
    }
    protected function ngListDB()
    {
        // associate tables to mods
        $mods=array_keys($_SESSION[NGBOOST]['ml']);
        foreach ($mods as $mod) {
					$ar[$mod]=$this->ngListDBmod($mod);
        }
        $ngbu = new ngBackup();
        $returnPrefix=false;
        $tl = $ngbu->getTableList($returnPrefix);

        $_SESSION['BG']='<b>'.dgettext('ngboost','Database tables of installation').'</b> ('
        .	$this->_reportMem().')<br />'
        .	'<table id="ngbsttbdb" class="ngtable"><thead class="ngthead">'
        .	'<tr><th>' .dgettext('ngboost','Module')
        .	'</th><th>'.dgettext('ngboost','Table(s)')
        .	'</th><th>'.dgettext('ngboost','Commands')
        .	'</th><th>'.dgettext('ngboost','Actions, Messages, Feedback')
        .	' </th></tr></thead><tbody class="ngtbody">';
        unset($_SESSION[NGBOOST]['FG']['ngtn']);
        unset($_SESSION[NGBOOST]['FG']['ngtm']);
		
        foreach ($ar as $mod => $tbs) {
            if (count($tbs)==0) {
            } else {

                $_SESSION[NGBOOST]['FG']['ngtn']['0m'.md5($mod)]=$tbs;

                $cl=='bgcolor1'?$cl='bgcolor2':$cl='bgcolor1';
                $_SESSION['BG'] .= '<tr class="'.$cl.'"><td>'.$mod.'</td>';
                $_SESSION['BG'] .= '<td align="right"><sub><i>'.count($tbs).' Table(s)</i></sub></td>';
                if ($_SESSION[NGBOOST]['ml'][$mod]['in']==ISTRUE) {
                    $_SESSION[NGBOOST]['FG']['ngtm'][md5($mod)]=$mod;
                    $_SESSION['BG'] .= '<td>' . ngBoost_Form::ngTabListTables('m',$mod,$tbs) . '</td>';
                } else {
                    $_SESSION['BG'] .= '<td>' . dgettext('ngboost','Module not installed') . '</td>';
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
    protected function ngListDBmod($mod)
    {
        // associate tables to mods
        $ar=array();
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
                $ar[]=trim($in[0]);
            }
        }
		return $ar;
 	}
	
    protected function ngListBoostLog()
    {
		$_SESSION['BG'] = '<b>Boost Log</b> ('.$this->_reportMem().')'
		.	'<pre class="ngplain">'
		.	htmlentities(@file_get_contents(PHPWS_SOURCE_DIR.'logs/boost.log'))
		.	'</pre>';
	}

    protected function ngListErrorLog()
    {
		$_SESSION['BG'] = '<b>Error Log</b> ('.$this->_reportMem().')'
		.	'<pre class="ngplain">'
		.	htmlentities(@file_get_contents(PHPWS_SOURCE_DIR.'logs/error.log'))
		.	'</pre>';
	}

    protected function ngListML()
    {
		$ngboostform = new ngBoost_Form('ngboost');
		$ngboostform->_ngGetModules();
		$_SESSION['BG'] = $ngboostform->ngRowModules();
	}
	
    protected function ngListPato($pato)
    {
				$distro = $this->ngConfigGet('distro');
				$distropath = str_replace('/modules/','/patos/',ngBoost_Action::ngGetDistro());
				$xmlfile = $distropath . $pato . '/pato.xml';
				$xml = @simplexml_load_file($xmlfile);
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

    protected function ngListPatos()
    {
		if ($this->isbranch) {
			$_SESSION['BG']=dgettext('ngboost', 'List patos is not supported in branches');
		} else {
			$_SESSION['BG']='';
			$distro = $this->ngConfigGet('distro');
			$distropath = str_replace('/modules/','/patos/',ngBoost_Action::ngGetDistro());
			$xdirfile = $distropath . 'patos.xml';
			$xdir = @simplexml_load_file($xdirfile);
			if ($xdir) {
				if (is_object($xdir)) {
				
					$_SESSION['BG'] .= '<table id="ngbsttbpat" class="ngtable">'
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
								$alnk = '<a id="ngpata'.$patdir.'" class="ngpata" href="javascript:ngPatoDesc(\''.$xml->pato->title.'\')">more</a>';
								$_SESSION['BG'].='<tr class="'.$cl.'">'
								.	'<td>'.strip_tags($xml->pato->title).'</td>'
								.	'<td>'.strip_tags($xml->pato->scope).'</td>'
								.	'<td>'.$distro.'</td>'
								.	'<td>'.strip_tags($xml->pato->version).'</td>'
								.	'<td>'.strip_tags($xml->pato->shortdesc) . NGSP3 . $alnk
								.		'<p id="ngpatx'.$patdir.'"></p></td>'
								.	'<td><span id="ngpato'.$patdir.'">'
								.	'<a href="javascript:ngPatoApply(\''.$patdir.'\')">'.'Apply'.'</a></span>'
								.'</td></tr>';
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
	}
	
    protected function ngApplyPato($pato)
    {
			if ($this->isbranch) {
				$_SESSION['BG']='notOnBranch';
			} else {
				$distro = $this->ngConfigGet('distro');
				$distropath = str_replace('/modules/','/patos/',ngBoost_Action::ngGetDistro());
				$xmlfile = $distropath . $pato . '/pato.xml';
				$xml = @simplexml_load_file($xmlfile);
				
				// check dependencies ... TODO
				
				if (is_object($xml)) {
					$tgzf = $xml->pato->download;
					$md5 = (string)$xml->pato->md5sum;
					if (!file_exists($tgzf)) {
						$rc = @copy($distropath.$pato.'/'.$tgzf, $tgzf);
					} else {
						$rc=true;
					}
					if ($rc) {
						$tgzmd5=@md5_file($tgzf);
						if (strtoupper($tgzmd5)===strtoupper($md5)) {
							$tar=substr($tgzf,0,-3).'tar';
							if (file_exists($tar)) {
								@unlink($tar);
							}
							$fz=gzopen($tgzf,'r');
							if ($fz) {
								$fp=fopen($tar,'w');
								while (!gzeof($fz)) {
									fwrite($fp,gzgets($fz,4096),4096);
								}
								fclose($fp);
								gzclose($fz);
								@unlink($tgzf);
								require_once 'Archive/Tar.php';
								$tarO = new Archive_Tar($tar);
								$ar=$tarO->listContent();
								if ($ar) {
									foreach ($ar as $a) {
										$sel[]=rtrim($a['filename']);
									}
									$rc=$tarO->extractList($sel,'','');
									@unlink($tar);
									$_SESSION['BG']='applied';
								} else {
									$_SESSION['BG']='brokenTar';
								}
							} else {
								$_SESSION['BG']='invalidTgz';
							}
						} else {
							$_SESSION['BG']='chkSumError';
						}
					} else {
						$_SESSION['BG']='notPickedUp';
					}
				} else {
					$_SESSION['BG']='noCheckFile';
				}
			}
	}
	
    protected function ngListTar($fnc)
    {
        $_SESSION['BG']='';
        if (isset($_SESSION[NGBOOST]['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION[NGBOOST]['FG']['ngfn'][$fnc];
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
        if (isset($_SESSION[NGBOOST]['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION[NGBOOST]['FG']['ngfn'][$fnc];
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
        $ngbu = new ngBackup();
        $r=$ngbu->backupMod($mod);
        $cc=substr($r,0,1);
        $re=substr($r,1);
        if ($cc==0) {
            $_SESSION['BG'] = $mod . '--' . NGSAYOK . '--' . 'Backup' . $re . NGSAYOK . NGBR;
        } else {
            $_SESSION['BG'] = $mod . '--' . NGSAYKO . '--' . 'Backup' . $cc . NGSAYKO . NGBR;
        }
    }

    protected function ngBuAll()
    {
		$ngboostform = new ngBoost_Form;
		$ngboostform->_ngGetModules();
        $_SESSION['BG'] = implode('--', array_keys($_SESSION[NGBOOST]['ml']));
		$_SESSION[NGBOOST]['BUSIGN']['fs']='ngbu.full.fs.'.date("Ymd-His");
    }

    protected function ngBuBranch()
    {
		$_SESSION[NGBOOST]['BUSIGN']['fs']='ngbu.full.fs.'.date("Ymd-His");
		$this->ngBU('core1v1');
    }

    protected function ngBuDel($fnc)
    {
        $_SESSION['BG']=' ';
        if (isset($_SESSION[NGBOOST]['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION[NGBOOST]['FG']['ngfn'][$fnc];
            $cc=@unlink($this->ngGetRepositoryPath().$fn);
            if ($cc) {
                $_SESSION['BG'] = '#ngop'.$fnc.'--purged--' . NGJQMCLOSE . $fn. ' ' . 'purged' . ' ' . NGSAYOK;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Purge' . NGSAYKO . $cc . ',' . $fn;
            }
        }
    }

    private function ngBuModDB($mod)
    {
		$tables=$this->ngListDBmod($mod);
        $ngbu = new ngBackup();
		$filestamp=date("Ymd-His.");
		$fb=array();
		foreach ($tables as $table) {
            $ngbu->mod = $mod;
            $fb[]=$mod.' '.$ngbu->exportTable($table, $filestamp).' '.$table;
        }
		$_SESSION['BG'] = implode(NGBR,$fb);
    }
	
    protected function ngBuTbl($tnc)
    {
        $_SESSION['BG']=' ';
        if (isset($_SESSION[NGBOOST]['FG']['ngtn'][$tnc])) {
            $tn=$_SESSION[NGBOOST]['FG']['ngtn'][$tnc];
            $ngbu = new ngBackup();
            // retranslate
            $ngbu->mod = $_SESSION[NGBOOST]['FG']['ngtm'][$_REQUEST['m']];
            $msg=$ngbu->exportTable($tn);
            $_SESSION['BG'] = $msg;
        }
    }

    protected function ngBuTblMod($mod)
    {
        $_SESSION['BG'] .= $mod;
        foreach ($_SESSION[NGBOOST]['FG']['ngtn']['0m'.$mod] as $tb) {
            $_SESSION['BG'] .= '--' . md5($tb);
        }
    }
    protected function ngBuTblAll()
    {
        $_SESSION['BG']=implode('--',array_keys($_SESSION[NGBOOST]['FG']['ngtm']));
		$_SESSION[NGBOOST]['BUSIGN']['db']='ngbu.full.db.'.date("Ymd-His");
    }

    protected function ngReTbl($fnc)
    {
        $_SESSION['BG']='';
        if (isset($_SESSION[NGBOOST]['FG']['ngfn'][$fnc])) {
            $fn=$_SESSION[NGBOOST]['FG']['ngfn'][$fnc];
            $ngbu = new ngBackup();
            $r=$ngbu->importTable($fn);
            $cc=substr($r,0,1);
            $re=substr($r,2);
            if ($cc==0) {
                $_SESSION['BG'] = NGJQMCLOSE . $re . NGSAYOK;
            } else {
                $_SESSION['BG'] = NGJQMCLOSE . 'Restore' . NGSAYKO . $cc . ',' . $re;
            }
        }
    }

    protected function ngMyRel($xvsn)
    {
		$tf=$this->ngConfigSet('release', (string)$xvsn);
		$st=($tf?'t':'f');
		$_SESSION['BG'] = $xvsn.' '.$st;
	}
	
    protected function ngSelRel($xvsn)
    {
		$_SESSION[NGBOOST]['FG']['xmlrel']=$xvsn;
		$_SESSION['BG'] = $_SESSION[NGBOOST]['FG']['xmlrel'];
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
        $ngbu = new ngBackup();
        $r=$ngbu->backupMod('');
        $cc=substr($r,0,1);
        $re=substr($r,1);
		$_SESSION['BG'] = $r;
		}
		if (1==2) {
		$_SESSION[NGBOOST]['FG']['ngfn']['.sysbu'] = '.sysbu.20110111-132843.fs.tgz';
		$this->ngListTar('.sysbu');
		}
		$_SESSION['BG'] = 'FFU';
		
	}
	
    protected function ngTuneSources()
    {
		if ($this->isbranch) {
			$cnt=dgettext('ngboost', 'Distro server setting is not supported in branches');
		} else {
			$chka = $chkn = $chks = $none = '';
			$in = $this->ngConfigGet('distro');
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
			default:
				$none=' checked="checked" ';
			}

			$cnt = '<h4>Distro selection</h4>';
			$onc = ' type="radio" name="distro" onclick="javascript:ngOnC()" ';
		
			$cnt.='<label><input'.$onc.$chka.'value="asu"  />' . htmlentities(BYASU) . '</label>' . NGBR
			.	  '<label><input'.$onc.$chkn.'value="ngws" />' . htmlentities(BYNGWS). '</label>' . NGBR
			.	  '<label><input'.$onc.$chks.'value="sf"   />' . htmlentities(BYSF)  . '</label>' . NGBR
			.	  '<label><input'.$onc.$none.'value="none"   />' . 'none'  . '</label>';
		}
		$_SESSION['BG'] = $cnt;
	}
	
    protected function ngTuneSourceList()
    {
		if ($this->isbranch) {
			$cnt=dgettext('ngboost', 'List distro is not supported in branches');
		} else {
			$ngboostform = new ngBoost_Form('ngboost');
			$in = $this->ngConfigGet('distro');
			switch ($in) {
			case 'asu':
				$ngboostform->distro=$in.' @ '.htmlentities(BYASU);
				break;
			case 'ngws':
				$ngboostform->distro=$in.' @ '.htmlentities(BYNGWS);
				break;
			case 'sf':
				$ngboostform->distro=$in.' @ '.htmlentities(BYSF);
				break;
			default:
				$ngboostform->distro='none';
			}
			clearstatcache();
			$xmlfile = ngBoost_Action::ngGetDistro() . 'distros.xml';
			$xml = @simplexml_load_file($xmlfile);
			if (is_object($xml)) {
				$ngboostform->xml=$xml;
				$cnt = $ngboostform->ngTabListDistros();
			} else {
				$cnt=dgettext('ngboost', 'this distro server does not have the xml file');
			}
		}
		$_SESSION['BG'] = $cnt;
	}

    protected function ngTuneSourceSet()
    {
		$_SESSION['BG'] = ' ';
		if (!$this->isbranch) {
			$p	= urlencode($_REQUEST['p']);
			if ($p=='asu' || $p=='ngws' || $p=='sf'|| $p=='none') {
				$this->ngConfigSet('distro', $p);
				$_SESSION['BG'] = dgettext('ngboost','Distro set to') . ' ' . $p;
			}
		}
	}

    protected function ngFsPerms()
    {
		$_SESSION['BG'] = '<h4>'.gettext('File System')
		.	'</h4><pre>'
		.	'convert ... '.ngBackup::_cvFilePerms(fileperms(PHPWS_SOURCE_DIR.'convert'))
		.	NGBR
		.	'setup   ... '.ngBackup::_cvFilePerms(fileperms(PHPWS_SOURCE_DIR.'setup'))
		.	'</pre>';
		if (!$this->isbranch) {
			$_SESSION['BG'].=NGSP3.'<a href="javascript:ngFsPermsLock()">lock</a>'
						   . NGSP3.'<a href="javascript:ngFsPermsUnlock()">unlock</a>';
		}
	}
    protected function ngFsPermsLock()
    {
		if (!$this->isbranch) {
			chmod(PHPWS_SOURCE_DIR.'convert', 0000);
			chmod(PHPWS_SOURCE_DIR.'setup', 0000);
			$this->ngFsPerms();
		}
	}
    protected function ngFsPermsUnLock()
    {
		if (!$this->isbranch) {
			chmod(PHPWS_SOURCE_DIR.'convert', 0750);
			chmod(PHPWS_SOURCE_DIR.'setup', 0750);
			$this->ngFsPerms();
		}
	}

    protected function ngPickupTgz()
    {
		$mod=$_REQUEST['m'];
		$ix=$_REQUEST['x'];
        if (isset($_SESSION[NGBOOST]['FG']['dml'][$mod][$ix])) {
            $tgzf = array_pop(explode('/', $_SESSION[NGBOOST]['FG']['dml'][$mod][$ix]));
			$next = dgettext('ngboost','check');
 			$cc='0';
			if (!file_exists($tgzf)) {
                $rc = @copy($_SESSION[NGBOOST]['FG']['dml'][$mod][$ix], $tgzf);
                if ($rc) {
				} else {
					$cc='4';
                    $next = dgettext('ngboost','transfer error').' '.$tgzf;
				}
			}
			$_SESSION['BG'] = $ix . '--r--' . $cc . '--' . $mod . '--' . $next;
        } else {
            $_SESSION['BG'] = $ix . '--r--9--' . $mod . '--Error R ' . $mod . '=' . $tgzf;
        }
    }

    protected function ngCheckTgz()
    {
		$mod=$_REQUEST['m'];
		$ix=$_REQUEST['x'];
		$cc='4';
        if (isset($_SESSION[NGBOOST]['FG']['dml'][$mod][$ix])) {
            $tgzf = array_pop(explode('/', $_SESSION[NGBOOST]['FG']['dml'][$mod][$ix]));
			$next = dgettext('ngboost','decompress');
			if (file_exists($tgzf)) {
				$tgzmd5=@md5_file($tgzf);
				//$xmlexpl = str_replace('.tar.gz','.xml',str_replace($mod.'_','check.',$tgzf));
				// mod.v_v_v.check.xml from 3.0.16 primary choice
				$xmlexpl = str_replace('.tar.gz','.check.xml',str_replace($mod.'_',$mod.'.',$tgzf));
				$xmlpath = ngBoost_Action::ngGetDistro() . $mod . '/';
				$xmlfile = $xmlpath.$xmlexpl;
				$xml = @simplexml_load_file($xmlfile);
				if ($xml===false) {
					// 1st fallback to logic up to 3.0.15 check.v_v_v.xml
					$xmlexpl = str_replace('.tar.gz','.xml',str_replace($mod.'_','check.',$tgzf));
					$xmlfile = $xmlpath.$xmlexpl;
					$xml = @simplexml_load_file($xmlfile);
					if ($xml===false) {
						// 2nd fallback to check.xml
						$xmlfile = $xmlpath.'check.xml';
						$xml = @simplexml_load_file($xmlfile);
					}
				}
				if (is_object($xml)) {
					if (isset($xml->module->parts)) {
						$xmlmd5 = (string)$xml->module->parts->part[(int)$ix]->md5sum;
					} else {
						$xmlmd5 = (string)$xml->module->md5sum;
					}
					if (strtoupper($tgzmd5)===strtoupper($xmlmd5)) {
						$cc='0';
						$next = dgettext('ngboost','decompress');
					} else {
						@unlink($tgzf);
						$next = dgettext('ngboost', 'checksum verification error').'=F)'.$tgzmd5.' X)'.$xmlmd5.' '.$xmlexpl.' '.$tgzf;
					}
				} else {
					$next = dgettext('ngboost', 'check.xml');
				}
			} else {
				$next = dgettext('ngboost', 'local tgz not found');
			}
        } else {
			$next = dgettext('ngboost', 'invalid call');
		}
		$_SESSION['BG'] = $ix . '--c--' . $cc . '--' . $mod . '--' . $next;
	}
	
    protected function ngDecomTgz()
    {
		$mod=$_REQUEST['m'];
		$ix=$_REQUEST['x'];
		$cc='4';
        if (isset($_SESSION[NGBOOST]['FG']['dml'][$mod][$ix])) {
			$next = dgettext('ngboost','expand');
            $tgz = array_pop(explode('/', $_SESSION[NGBOOST]['FG']['dml'][$mod][$ix]));
			if (file_exists($tgz)) {
				$tar=substr($tgz,0,-3);
				if (file_exists($tar)) {
					@unlink($tar);
				}
				$fz=gzopen($tgz,'r');
				if ($fz) {
					$fp=fopen($tar,'w');
					while (!gzeof($fz)) {
						fwrite($fp,gzgets($fz,4096),4096);
					}
					fclose($fp);
					gzclose($fz);
					@unlink($tgz);
					$cc='0';
				} else {
					$next = dgettext('ngboost', 'local tgz invalid');
				}
			} else {
				$next = dgettext('ngboost', 'local tgz not found');
			}
		} else {
			$next = dgettext('ngboost', 'invalid call');
		}
		$_SESSION['BG'] = $ix . '--x--' . $cc . '--' . $mod . '--' . $next;
	}
	
    protected function ngExpandTgz()
    {
		$mod=$_REQUEST['m'];
		$ix=$_REQUEST['x'];
		$cc='4';
        if (isset($_SESSION[NGBOOST]['FG']['dml'][$mod][$ix])) {
            $tgz = array_pop(explode('/', $_SESSION[NGBOOST]['FG']['dml'][$mod][$ix]));
			// tar.gz = tar
			$tar = substr($tgz,0,-3);
			if (file_exists($tar)) {
				require_once 'Archive/Tar.php';
				$tarO = new Archive_Tar($tar);
				$ar=$tarO->listContent();
				if ($ar) {
					foreach ($ar as $a) {
						$sel[]=rtrim($a['filename']);
					}
					$rc=$tarO->extractList($sel,'','');
					@unlink($tar);
					$next = dgettext('ngboost','done');
					$cc='0';
				} else {
					$next = dgettext('ngboost', 'local tar broken');
				}
			} else {
				$next = dgettext('ngboost', 'local tar not found:').$tar;
			}
		} else {
			$next = dgettext('ngboost', 'invalid call');
		}
		$_SESSION['BG'] = $ix . '--u--' . $cc . '--' . $mod . '--' . $next;
	}
	
    public function ngGetDistro()
    {
		$in = $this->ngConfigGet('distro');
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
	
    protected function ngConfig()
    {
		$mycfg=PHPWS_SOURCE_DIR.'mod/ngboost/conf/ngboost.jso';
		if (file_exists($mycfg)) {
			return json_decode(stripslashes(file_get_contents($mycfg)),true);
		} else {
			file_put_contents($mycfg,json_encode(array()));
			return array();
		}
	}
	
    protected function ngConfigGet($k)
    {
		$mycfg=$this->ngConfig();
		if (array_key_exists($k,$mycfg)) {
			return $mycfg[$k];
		} else {
			return false;
		}
	}
	
    protected function ngConfigSet($k,$v)
    {
		$mycfg=$this->ngConfig();
		$mycfg[$k]=$v;
		$cc=file_put_contents(PHPWS_SOURCE_DIR.'mod/ngboost/conf/ngboost.jso',addslashes(json_encode($mycfg)));
		return $cc;
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
            $helpfile = 'cp.distro.html';
            break;
        case 'ngbstcptab4':
            $helpfile = 'cp.repo.html';
            break;
        case 'ngbstcptab5':
            $helpfile = 'cp.db.html';
            break;
        case 'ngbstcptab6':
            $helpfile = 'cp.tune.html';
            break;
        }
		

 		$cnt= '<div class="ngbstcphelp">';

        if ($helpfile) {
            $helppf = PHPWS_SOURCE_DIR.'mod/ngboost/docs/'.$helpfile;
            if (file_exists($helppf)) {
                $cnth = file_get_contents($helppf);
				$ar=explode('</h1>',$cnth);
				$cnt.=$ar[1] . '</div>';
				$title=str_replace('<h1>','',$ar[0]);
				$pre=str_replace('class="ngjqmtitle">','class="ngjqmtitle">'.$title,NGJQMCLOSE);
				$_SESSION['BG'] = $pre.$cnt;
                return;
            }
        }

        $_SESSION['BG'] .= dgettext('ngboost','currently no help available') . '</div>';
    }

    protected function ngTplChkUpd($mod)
    {
		return 	NGSP3
		.		'<span id="ngpickuprm'.$mod.'"></span>'
		.		'<span id="ngpickuprp'.$mod.'"></span>'
		.		'<span id="ngpickupcm'.$mod.'"></span>'
		.		'<span id="ngpickupcp'.$mod.'"></span>'
		.		'<span id="ngpickupxm'.$mod.'"></span>'
		.		'<span id="ngpickupxp'.$mod.'"></span>'
		.		'<span id="ngpickupum'.$mod.'"></span>'
		.		'<span id="ngpickupup'.$mod.'"></span>'
		.		'<span id="ngpickupzz'.$mod.'"></span>';
	}
	
    //
	
    private function _checknew($mod)
    {
		$version=false;
		$mox = $mod=='core'?'base':$mod;
		$distropath = ngBoost_Action::ngGetDistro();
		if (!empty($distropath)) {
			$file = $distropath.$mox.'/check.xml';
			$xml = @simplexml_load_file($file);
			if (is_object($xml)) {
				$version = (string)$xml->module->version;
				$tgz = array_pop(explode('/', (string)$xml->module->download));
				$_SESSION[NGBOOST]['FG']['dml'][$mox]['0'] = $distropath . $mox . '/' . trim($tgz);
			}
		}
		return $version;
	}
	
    private function _checkupdate($mod)
    {
		$mox = $mod=='core'?'base':$mod;
		$file= $_SESSION[NGBOOST]['ml'][$mod]['chk'];
		// http://phpwebsite.appstate.edu/downloads/modules/mod/check.xml
		
		$distropath = ngBoost_Action::ngGetDistro();
		if (!empty($distropath)) {
			$file = $distropath.$mox.'/check.xml';
		}
		
		$xml = @simplexml_load_file($file);
		if (is_object($xml)) {
			$version = (string)$xml->module->version;
			$template['LOCAL_VERSION_LABEL'] = dgettext('ngboost', 'Current local version');
			$template['LOCAL_VERSION'] = $_SESSION[NGBOOST]['ml'][$mod]['vfs'];
			$template['STABLE_VERSION_LABEL'] = dgettext('ngboost', 'Latest distro version');
			if (empty($version)) {
				$template['STABLE_VERSION'] = dgettext('ngboost', 'Source XML error');
				$version = $_SESSION[NGBOOST]['ml'][$mod]['vfs'];
			} else {
				$template['STABLE_VERSION'] = $version;
			}
			$_SESSION[NGBOOST]['FG']['ngvx'][$mod] = $version;

			if (version_compare($version, $_SESSION[NGBOOST]['ml'][$mod]['vfs'], '>')) {
				$template['CHANGES_LABEL'] = dgettext('ngboost', 'Changes');
				if (isset($xml->module->changes)) {
					$template['CHANGES'] = htmlspecialchars((string)$xml->module->changes);
				} else {
					$template['CHANGES'] = dgettext('ngboost', 'No change infos provided');
				}
				$template['UPDATE_AVAILABLE'] = dgettext('ngboost', 'A new release is available');
				$template['PU_LINK_LABEL'] = '<b>'.dgettext('ngboost', 'Copy from distribution server to my site').'</b>';
				$_SESSION[NGBOOST]['FG']['dml'][$mox]=array();
				// multipart srcs
				if (isset($xml->module->parts)) {
					$i=0;
					foreach ($xml->module->parts->part as $part) {
						$tgz = array_pop(explode('/', (string)$part->download));
						$mtgz[] = $tgz.$this->ngTplChkUpd($i.$mox);
						$mmd5[] = (string)$part->md5sum;
						$i++;
						if (!empty($distropath)) {
							$_SESSION[NGBOOST]['FG']['dml'][$mox][] = $distropath . $mox . '/' . $tgz;
							$full = $distropath . $mox . '/' . array_pop(explode('/', (string)$xml->module->download));
						} else {
							$full = (string)$xml->module->download;
						}
					}
				} else {
					$tgz = array_pop(explode('/', (string)$xml->module->download));
					$mtgz=array($tgz.$this->ngTplChkUpd('0'.$mox));
					$mmd5=array((string)$xml->module->md5sum);
					if (!empty($distropath)) {
						$_SESSION[NGBOOST]['FG']['dml'][$mox][] = $full = $distropath . $mox . '/' . $tgz;
					} else {
						// native xml D/L resource (cannot be multipart)
						$_SESSION[NGBOOST]['FG']['dml'][$mod][] = $full = (string)$xml->module->download;
					}
				}

				$template['PU_LINK'] = '<span id="ngpickupa"><a href="javascript:ngPickup(\''.$mox.'\',\''.count($mtgz).'\')">'
                .	$_SESSION[NGBOOST]['ml'][$mod]['t'].NGSP3.$version.'</a></span>'
				.	NGBR.':... '.implode(NGBR.':... ',$mtgz).'<div id="ngpickup'.$mox.'"></div>';
			//	$template['DL_PATH_LABEL'] = dgettext('ngboost', 'or download by yourself from here');
				$template['DL_PATH'] = $full;
				$template['MD5_LABEL'] = dgettext('ngboost', 'MD5 Sum');
				foreach ($mtgz as $i => $v) {
					$template['MD5'] .= $v.NGSP3.$mmd5[$i].NGBR;
				}
				$template['dependent-mods']=array();
				if (isset($xml->module->dependency)) {
					$template['DEPENDENCY_LABEL'] = dgettext('ngboost', 'Dependencies');
					$template['DEP_TITLE_LABEL'] = dgettext('ngboost', 'Module title');
					$template['DEP_VERSION_LABEL'] = dgettext('ngboost', 'Version required');
					$template['DEP_STATUS_LABEL'] = dgettext('ngboost', 'Status');
					foreach ($xml->module->dependency->module as $dep_mod) {
						if ($_SESSION[NGBOOST]['ml'][$dep_mod]['in']===ISFALSE) {
							$status = dgettext('ngboost', 'Not installed');
							$row['DEP_STATUS_CLASS'] = 'red';
						} elseif (version_compare(
									(string)$dep_mod->version,
									$_SESSION[NGBOOST]['ml'][$dep_mod]['vdb'],
									'<')) {
							$status = dgettext('ngboost', 'Needs upgrading');
							$row['DEP_STATUS_CLASS'] = 'red';
						} else {
							$status = dgettext('ngboost', 'Passed');
							$row['DEP_STATUS_CLASS'] = 'green';
						}
						$row['DEP_TITLE'] = (string)$dep_mod->properName;
						$row['DEP_VERSION'] = (string)$dep_mod->version;
						$row['DEP_STATUS'] = $status;
						$template['dependent-mods'][] = $row;
					}
				}
			} else {
				$template['NO_UPDATE'] = dgettext('ngboost', 'No new release(s) available');
                
			}
			$template['TITLE'] = dgettext('ngboost', 'Module') . ': ' . $_SESSION[NGBOOST]['ml'][$mod]['t'];
			if (!$this->isbranch) {
				return PHPWS_Template::process($template, 'ngboost', 'check_update.tpl');
			} else {
				return dgettext('ngboost', 'Check done - further maintenance is supported thru the hub only') . NGBR;
			}
		}
		return dgettext('ngboost', 'check.xml not found') . NGBR.NGSAYKO.NGSP3 . $file;
	}

    private static function _installModule($mod)
    {
        $classicboost = new PHPWS_Boost;
        $classicboost->loadModules(array($mod));
        return $classicboost->install();
    }

    private static function _uninstallModule($mod)
    {
        $classicboost = new PHPWS_Boost;
		$classicboost->loadModules(array($mod));
        return $classicboost->uninstall();
    }

    public function _updateCore()
    {
        PHPWS_Core::initModClass('boost', 'Boost.php');
        $content[] = dgettext('ngboost', 'Updating core');

        require_once PHPWS_SOURCE_DIR . 'core/boost/update.php';

        $ver_info = PHPWS_Core::getVersionInfo(false);

        $content[] = dgettext('ngboost', 'Processing update file.');
        $result = core_update($content, $ver_info['version']);
		
		$umsg = dgettext('ngboost', 'An error occurred updating the core');

        if ($result === true) {
            $db = new PHPWS_DB('core_version');
            $file_ver = PHPWS_Core::getVersionInfo();
            $db->addValue('version', $file_ver['version']);
            $result = $db->update();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = $umsg.' (DB).';
            } else {
                $content[] = dgettext('ngboost', 'Core successfully updated').'.';
            }
        } elseif (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = $umsg.' (isPWSE).';
        } else {
            $content[] = $umsg.' (noPWSE)';
        }

        return implode('<br />', $content);
    }

    private static function _updateModule($mod)
    {
        $classicboost = new PHPWS_Boost;
        $classicboost->loadModules(array($mod), FALSE);

        $car = array();
        if ($classicboost->update($car)) {
            $classicboost->updateBranches($car);
        }
        return $car;
    }

    private function _showDependedUpon($mod)
    {
        if (!is_array($_SESSION[NGBOOST]['ml'][$mod]['dme'])) {
            $content[]=dgettext('ngboost', 'This module does not have dependents').'.';
        } else {
			$content[]='<p>'.sprintf(dgettext('ngboost',
							'The following modules depend on "%s" to function'),$mod).':</p>';
			foreach ($_SESSION[NGBOOST]['ml'][$mod]['dme'] as $dmod) {
				$content[] = '<span class="nglistline"><b>'.$dmod.'</b> ('.$_SESSION[NGBOOST]['ml'][$dmod]['t'].')</span>'.NGBR;
			}
		}
		return implode('', $content);
    }

    private function _showDependency($mod)
    {
        $template['TITLE'] = sprintf(dgettext('ngboost',
							'Module "%s" requires module(s) installed with the proper version(s)'), $mod);
        $template['MODULE_NAME_LABEL']     = dgettext('ngboost', 'Module Needed');
        $template['VERSION_NEEDED_LABEL']  = dgettext('ngboost', 'Version required');
        $template['CURRENT_VERSION_LABEL'] = dgettext('ngboost', 'Current Version');
        $template['STATUS_LABEL']          = dgettext('ngboost', 'Status');

        foreach ($_SESSION[NGBOOST]['ml'][$mod]['don'] as $dmod) {
            $pass = TRUE;
            $tpl = array();
            $tpl['MODULE_NAME']    = $dmod['mo'];
            $tpl['VERSION_NEEDED'] = $dmod['v'];

            if ($_SESSION[NGBOOST]['ml'][$dmod['mo']]['in']===ISTRUE
			||  $_SESSION[NGBOOST]['ml'][$dmod['mo']]['co']===ISTRUE) {
                $tpl['CURRENT_VERSION'] = $_SESSION[NGBOOST]['ml'][$dmod['mo']]['vdb'];
            } else {
                $pass = FALSE;
                $tpl['CURRENT_VERSION'] = dgettext('ngboost', 'Not installed');
            }

            if ($pass && version_compare($dmod['v'], $_SESSION[NGBOOST]['ml'][$dmod['mo']]['vdb'], '>')) {
                $pass = FALSE;
            }

            if ($pass) {
                $tpl['STATUS_GOOD'] = dgettext('ngboost', 'Passed');
            } else {
                $tpl['STATUS_BAD'] = dgettext('ngboost', 'Failed');
            }
            $template['module-row'][] = $tpl;
        }
        return PHPWS_Template::process($template, 'ngboost', 'dependency.tpl');
    }
	
    private function _varyJQM($mod,$titel)
    {
        return str_replace('class="ngjqmtitle">','class="ngjqmtitle">'.$titel.' &#171;'.$mod.'&#187;',NGJQMCLOSE);
	}
	
    private function _reportMem()
    {
		return 1==1?'CurrentMemUse=' . round( (memory_get_usage() / 1024) / 1024, 0).'MB':'';
	}
}

?>