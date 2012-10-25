<?php

/**
 * @author Hilmar Runge <ngwebsite.net>
 */

class ngBoost_Form extends ngBoost_Action {

 	/** @var bool $this->isbranch state */
	var $isbranch;
	var $release;
	
	var $distro;
	var $xml;
	
	function __construct() 
	{
		parent::__construct();
		$this->isbranch = (bool)PHPWS_Core::isBranch();
		$this->release = PHPWS_Core::releaseVersion();
	}

	protected function _ngTabTitle()
    {
        return '<h4>Release '.$this->release.' '.gettext('Distro').' '.str_replace(',',' ',$_SESSION[NGBOOST]['FG']['xmlrel']).'</h4>';
	}
	
 	/** @param string $type 'core' or 'noco' */
 	/** @return string full templated module list */
	protected function ngTabModules($type)
    {
		// echo '<pre>';print_r($_SESSION[NGBOOST]['ml']);echo '</pre>';		
		if (!isset($_SESSION[NGBOOST]['FG']['xmlrel'])) {
			$_SESSION[NGBOOST]['FG']['xmlrel']=$this->ngConfigGet('release');
		}
        $tpl['PHPWS_VERSION'] = $this->_ngTabTitle();
        $tpl['TITLE_LABEL']   = dgettext(NGBOOST, 'Module');
        $tpl['VERSION_LABEL'] = dgettext(NGBOOST, 'Current version');
        $tpl['LATEST_LABEL']  = dgettext(NGBOOST, 'Latest version');
        $tpl['COMMAND_LABEL'] = dgettext(NGBOOST, 'Commands');
        $tpl['ELSE_LABEL']    = dgettext(NGBOOST, 'Action');
        $tpl['ABOUT_LABEL']   = dgettext(NGBOOST, 'More information');
		
		switch ($type)
		{
		case 'core':
			$tpl['NGTABLE'] = 'ngbsttbco';
			$zebra='1';
			foreach ($_SESSION[NGBOOST]['ml'] as $amod => $mod) {
				if ($mod['co'] === ISTRUE) {
					$zebra=='0'?$zebra='1':$zebra='0';
					$tpl['mod-row'][] = $this->ngTplSetModuleRow($amod,$zebra);
				}
			}
			break;
		case 'noco':
			$tpl['NGTABLE'] = 'ngbsttbmo';
			$zebra='1';
			foreach ($_SESSION[NGBOOST]['ml'] as $amod => $mod) {
				if ($mod['co'] === ISFALSE) {
					if ($this->isbranch && $mod['br']===ISFALSE) continue;
					$zebra=='0'?$zebra='1':$zebra='0';
					$tpl['mod-row'][] = $this->ngTplSetModuleRow($amod,$zebra);
				}
			}
			break;
		case 'pato':
			break;
		}
        if (ini_get('allow_url_fopen')) {
            $tpl['CHECK_FOR_UPDATES'] = '<a href="javascript:ngCheckAll()">'.dgettext(NGBOOST,'CheckAll').'</a>';
            $this->isbranch
            ?$alnk =  '<a href="javascript:ngBuBranch()">'.dgettext(NGBOOST,'BackupBranch').'</a>'
            :$alnk = '<a href="javascript:ngBuAll()">'.dgettext(NGBOOST,'BackupAll').'</a>';
            $tpl['CHECK_FOR_UPDATES'] = $alnk . NGSP3 . $tpl['CHECK_FOR_UPDATES'];
			
        } else {
            $tpl['CHECK_FOR_UPDATES'] = dgettext(NGBOOST, 'Server configuration prevents version checking.');
        }
		$tpl['CHECK_FOR_UPDATES'].='<span class="ngallmsg">&nbsp;</span>';
		
		/** enforcing a tr filler because css is not able to style the tbody correctly this time (2011 Jan) */
		for ($i=count($tpl['mod-row']); $i<17; $i++) {
			$tpl['mod-row'][]=array(
				'ZEBRA'=>'0',
				'MOD'=>$i,'TITLE'=>'&nbsp;',
				'VERSION_ID'=>'ngcurrt11'.$i,
				'LATEST_ID'=>'nglatet12'.$i
			);
		}
		
        return PHPWS_Template::process($tpl, 'ngboost', 'module_list.tpl');
 	}

	protected function ngRowModules()
    {
			$zebra='0';
			foreach ($_SESSION[NGBOOST]['ml'] as $amod => $mod) {
				if ($mod['co'] === ISTRUE) {
					$row = $this->ngTplSetModuleRow($amod,$zebra);
				} else {
					if ($this->isbranch && $mod['br']===ISFALSE) continue;
					$row = $this->ngTplSetModuleRow($amod,$zebra);
				}
				$tpl[]=$amod.'--'.$this->ngTplGetModuleRow($row);
			}
		return implode("\n",$tpl);
	}

	
 	/** @return full $_SESSION[NGBOOST]['ml'] array */
    protected function _ngGetModules()
    {
		if (!$this) return;
		if ($this->context<>NGBOOST) return;
        $moco = PHPWS_Core::coreModList();		// n=>modname
        $moin = PHPWS_Core::installModList();	// n=>modname
        $mofs = PHPWS_Boost::getAllMods();		// n=>modname
 		$mobr = PHPWS_Core::isBranch()?Branch::getBranchMods():array();
		sort($mofs);
        $monc = array_diff($mofs, $moco);		// n=>modname
		
		/** collect all data about all modules */
		// core
			$fscore = new PHPWS_Module('core');
			$dbcore = new PHPWS_Module('core', false);
			$_SESSION[NGBOOST]['ml']['core']['co']  = ISTRUE;
			$_SESSION[NGBOOST]['ml']['core']['in']  = ISTRUE;
			$_SESSION[NGBOOST]['ml']['core']['vfs'] = $fscore->version;
			$_SESSION[NGBOOST]['ml']['core']['vdb'] = $dbcore->version;
			$_SESSION[NGBOOST]['ml']['core']['t']   = $fscore->getProperName();
			$_SESSION[NGBOOST]['ml']['core']['chk'] = $fscore->version_http;
			$_SESSION[NGBOOST]['ml']['core']['isa'] = $fscore->isAbout() ? ISTRUE : ISFALSE;
 
		// noncore
		foreach ($mofs as $m) {
			$_SESSION[NGBOOST]['ml'][$m]['co'] = in_array($m,$moco)?ISTRUE:ISFALSE;
			$_SESSION[NGBOOST]['ml'][$m]['in'] = in_array($m,$moin)?ISTRUE:ISFALSE;
			$_SESSION[NGBOOST]['ml'][$m]['br'] = in_array($m,$mobr)?ISTRUE:ISFALSE;
			$mod = new PHPWS_Module($m);
			$_SESSION[NGBOOST]['ml'][$m]['vfs'] = $mod->version;
			$_SESSION[NGBOOST]['ml'][$m]['t'] = $mod->getProperName();
            $dbmod = new PHPWS_Module($m, false);
            $_SESSION[NGBOOST]['ml'][$m]['vdb'] = $dbmod->version;
            $_SESSION[NGBOOST]['ml'][$m]['chk'] = $mod->version_http;
			$_SESSION[NGBOOST]['ml'][$m]['isa'] = $mod->isAbout() ? ISTRUE : ISFALSE;
			$tmp = $mod->getDependencies();
			if (isset($tmp['MODULE'])) {
				foreach ($tmp['MODULE'] as $t) {
					if (is_array($t)) {
						$_SESSION[NGBOOST]['ml'][$m]['don'][] = array('mo'=>$t['TITLE'],'v'=>$t['VERSION'],'dl'=>$t['URL']);
					}
				}
			}
			$tmp = $mod->isDependedUpon();
			if (!empty($tmp)) {
				$_SESSION[NGBOOST]['ml'][$m]['dme']=$tmp;
			}
		}
		
		// distro
		$distro = ngBoost_Action::ngGetDistro();
		if (!empty($distro)) {
			$xmlfile = $distro . 'distros.xml';
			$xml = @simplexml_load_file($xmlfile);
			if (is_object($xml)) {
				$myrel = $this->ngConfigGet('release');
				foreach ($xml->distro as $distro) {
					if (isset($distro->name)) {
						$oval=(string)$distro->name;
					} else {
						$oval=(string)$distro->release;
					}
					$xrel=(string)$distro->release; //
					if ($myrel==$oval) {
						foreach ($distro->modules->module as $mod) {
							if (isset($mod->lastnew)) {
								$_SESSION[NGBOOST]['ml'][(string)$mod->name]['vxm']=(string)$mod->lastnew;
							}
						}
					}
				}
			}
		}		
	}
	
    protected function ngTplSetModuleRow($amod,$zebra)
    {	
		if (!$this) return array('');
		
        $template['ZEBRA']     	= $zebra;
		$template['MOD']		= $amod;
        $template['TITLE']		= $amod;
        $template['VERSION_ID'] = 'ngcurrt11'.$amod;
		$template['VERSION']=
			$_SESSION[NGBOOST]['ml'][$amod]['in']===ISTRUE
			&& version_compare($_SESSION[NGBOOST]['ml'][$amod]['vdb'], $_SESSION[NGBOOST]['ml'][$amod]['vfs'], '<')
			?$_SESSION[NGBOOST]['ml'][$amod]['vdb'] . ' > ' . $_SESSION[NGBOOST]['ml'][$amod]['vfs']
			:$_SESSION[NGBOOST]['ml'][$amod]['vfs'];
		$template['LATEST_ID']	= 'nglatet12'.$amod;

		if (empty($_SESSION[NGBOOST]['FG']['ngvx'][$amod])) {
			// 3.0.16
		//	if (isset($_SESSION[NGBOOST]['ml'][$amod]['vxm'])) {
		//		$atxt=$_SESSION[NGBOOST]['ml'][$amod]['vxm'];
		//		$_SESSION[NGBOOST]['FG']['ngvx'][$amod]=$atxt; // check needed? no!
		//	} else {
			// /3.0.16
				$atxt=dgettext(NGBOOST, 'Check');
		//	}
		} else {
			$atxt=$_SESSION[NGBOOST]['FG']['ngvx'][$amod];
			if (version_compare($_SESSION[NGBOOST]['ml'][$amod]['vfs'], $_SESSION[NGBOOST]['FG']['ngvx'][$amod], '<')) {
				if (!$this->isbranch) {
					$atxt.=' - '.dgettext(NGBOOST, 'GetNew');
				}
			}
		}
		
        $template['LATEST'] = 
		ini_get('allow_url_fopen')
			?'<a href="javascript:ngCheck(\''.$amod.'\')">'
			.'<span id="ngchk'.$amod.'">'.$atxt.'</span></a>'
			: dgettext(NGBOOST, 'Check disabled');
		
		$acmd=array();
		if ($_SESSION[NGBOOST]['ml'][$amod]['in']===ISTRUE) {
			// is installed
			version_compare($_SESSION[NGBOOST]['ml'][$amod]['vdb'], $_SESSION[NGBOOST]['ml'][$amod]['vfs'], '<')
			?$acmd[]='<a id="ngup'.$amod.'" href="javascript:ngUpdate(\''.$amod.'\')">'
			.dgettext(NGBOOST, 'Update').'</a>'
			:$nop='';
			// other mods are depended upon me (only important when installed to prevent uninstall)
			if (isset($_SESSION[NGBOOST]['ml'][$amod]['dme']) && is_array($_SESSION[NGBOOST]['ml'][$amod]['dme'])) {
				$acmd[]= '<a href="javascript:ngShowDepUpon(\''.$amod.'\')">'
						.dgettext(NGBOOST, 'DependedUpon').'</a>';
			} else {
				// may be uninstalled
				if ($_SESSION[NGBOOST]['ml'][$amod]['co']===ISFALSE) {
					$acmd[]='<a id="ngun'.$amod.'" href="javascript:ngUnInstall(\''.$amod.'\')">'
						.dgettext(NGBOOST, 'Uninstall').'</a>';
				} else {
					$acmd[]=dgettext(NGBOOST, 'IsInstalled');
				}
			}
 		} else {
			// is not installed, have dependencies?
			$hasd=false;
			if (isset($_SESSION[NGBOOST]['ml'][$amod]['don']) && is_array($_SESSION[NGBOOST]['ml'][$amod]['don'])) {
				foreach ($_SESSION[NGBOOST]['ml'][$amod]['don'] as $deps) {
					if ($_SESSION[NGBOOST]['ml'][$deps['mo']]['in']===ISFALSE
					||  version_compare($_SESSION[NGBOOST]['ml'][$deps['mo']]['vdb'], $deps['v'], '<')) {
						$hasd=true;
						$acmd[]='<a href="javascript:ngShowDep(\''.$amod.'\')">'
								.dgettext(NGBOOST, 'MissingDependency').'</a>';
						// one is enough
						break;
					}
				}
			}
			if (!$hasd) {
				// has no dependencies, may become installed
				$acmd[]='<a id="ngin'.$amod.'" href="javascript:ngInstall(\''.$amod.'\')">'
						.dgettext(NGBOOST, 'Install').'</a>';
			}
		}
			
		$template['COMMAND']=(count($acmd)==0) ? dgettext(NGBOOST, 'None') : implode(NGSP3,$acmd);
			
        $template['ABOUT'] =
		$_SESSION[NGBOOST]['ml'][$amod]['isa'] == ISTRUE
		?'<a href="javascript:ngAbout(\''.$amod.'\')">'.dgettext(NGBOOST,'about').'</a>':'';
		$template['ABOUT'] .= '&nbsp;'.$_SESSION[NGBOOST]['ml'][$amod]['t'];
		
 		$template['ELSE'] = 
		$this->isbranch
			?''
			:'<a href="javascript:ngBu(\''.$amod.'\')">'.dgettext(NGBOOST,'Backup').'</a>';
        $template['ELSE'] .= '&nbsp;<span id="ngmsgbu'.$amod.'"></span>';
			
		return $template;
	}
	
    protected function ngTplGetModuleRow($row)
    {	
        return '<td>'.$row['TITLE'].'</td>'
        . '<td id="'.$row['VERSION_ID'].'">'.$row['VERSION'].'</td>'
        . '<td id="'.$row['LATEST_ID'].'">'.$row['LATEST'].'</td>'
        . '<td>'.$row['COMMAND'].'&nbsp;&nbsp;'.$row['UNINSTALL']. '</td>'
		. '<td>'.$row['ELSE'].'</td>'
        .' <td>'.$row['ABOUT'].'</td>';
	}
	
    protected function ngTabDistro()
    {
        $alnk = '<a href="javascript:ngPlain(\'ts\')">SelectDistroServer</a>' . NGSP3
        .		'<a href="javascript:ngPlain(\'tsl\')">ListDistro</a>' . NGSP3
		.		'<a href="javascript:ngPatos()">ListPatos</a>'
        .		'<p id="ngmsgt31"></p>';
        return $this->_ngTabTitle().$alnk;
	}

    protected function ngTabRepo()
    {
		$alnk='<a href="javascript:ngPlain(\'crp\')">VerifyRepositoryPath</a>'
            . NGSP3
            . '<a href="javascript:ngPlain(\'lrp\')">ListRepository</a>'
			. NGSP3
            . '<a href="javascript:ngPlain(\'xrp\')">XrefRepository</a>'
			. NGSP3
            . '<a href="javascript:ngPlain(\'lbu\')">ListBackupSets</a>'
            . '<p id="ngmsgt41"></p>';
        return $this->_ngTabTitle().$alnk;
    }

    protected function ngTabLTar($fn)
    {
        // security, do not let see filenames as js parameters
        $fnc=md5($fn);
        $_SESSION[NGBOOST]['FG']['ngfn'][$fnc]=$fn;

        if (substr($fn,-4)=='.tgz' || substr($fn,-7)=='.tar.gz') {
            $dir = '<a href="javascript:ngPop(\'ltar\',\'fn\',\'' . $fnc . '\')">Dir</a>';
        } else {
            $dir = '<span class="ngpseudo">Dir</span>';
        }

        $alnk = $dir
        .	'&nbsp;&nbsp;'
        .	'<a href="javascript:ngPop(\'re\',\'fn\',\'' . $fnc
        .	'\')">Recover</a>'
        .	'&nbsp;&nbsp;'
        .	'<a href="javascript:ngPop(\'dy\',\'fn\',\'' . $fnc
        . 	'\')">Purge</a>';

        return $alnk;
    }

    protected function ngTabLBus($fn)
    {
        // security, do not let see filenames as js parameters
        $fnc=md5($fn);
        $_SESSION[NGBOOST]['FG']['ngfnbus'][$fnc]=$fn;

        $alnk = '<a href="javascript:ngPop(\'ls\',\'fn\',\'' . $fnc
        .	'\')">ListBackupSet</a>'
        .	'&nbsp;&nbsp;'
    //    .	'<a href="javascript:ngPop(\'re\',\'fn\',\'' . $fnc		//
     //   .	'\')">RecoverFull</a>'
        .	'RecoverFull'
        .	'&nbsp;&nbsp;'
    //    .	'<a href="javascript:ngPop(\'dy\',\'fn\',\'' . $fnc		//
     //   . 	'\')">PurgeFull</a>';
        .	'PurgeFull';

        return $alnk;
    }

    protected function ngTabDB()
    {
        $alnk = NGSP3.'<a href="javascript:ngPlain(\'ldb\')">ListTables</a>'
        .		'<p id="ngmsgt51"></p>';
        return $this->_ngTabTitle().$alnk;
    }

    protected function ngTabTune()
    {
        $alnk = NGSP3.'<a href="javascript:ngListLog()">ListBoostLog</a>'
		.		NGSP3.'<a href="javascript:ngListLogE()">ListErrorLog</a>'
		.		NGSP3.'<a href="javascript:ngFsPerms()">FileSystemPerms</a>'
		.		'<p id="ngmsgt61"></p>';
        return $this->_ngTabTitle().$alnk;
    }

    protected function ngTabListDistros()
    {
		$myrel = $this->ngConfigGet('release');
		if (isset($_SESSION[NGBOOST]['FG']['xmlrel'])) {
			$selrel=$_SESSION[NGBOOST]['FG']['xmlrel'];
		} else {
			if ($myrel > '') {
				$selrel=$myrel;
			} else {
				// take 1st distro entry
				(string)$selrel=(string)$_SESSION[NGBOOST]['FG']['xmlrel']=(string)$this->xml->distro->release;
			}
		}
		$distropath = ngBoost_Action::ngGetDistro();
		$tpl['SELREL']='<select title="select a release" onchange="ngRelSel()" class="inp" id="ngboostrsel">';
		foreach ($this->xml->distro as $distro) {
			$tpl['SELREL'].='<option value="';
			if (isset($distro->name)) {
				$oval=(string)$distro->name;
			} else {
				$oval=(string)$distro->release;
			}
			$tpl['SELREL'].=$oval.'"';
			if ((string)$selrel > '' && (string)$oval == (string)$selrel) {
				$tpl['SELREL'].=' selected="selected"';
			}
			$tpl['SELREL'].='>'.(string)$distro->title;
			$tpl['SELREL'].='</option>';
		}
		$tpl['SELREL'].='</select>';
		$tpl['SETMYREL']='<a href="javascript:ngSetMyRel()">'.gettext('SetAsMyRelease').'</a>'
		.	' ('.gettext('current').' '.($myrel==''?gettext('none'):str_replace(',',' ',$myrel)).')';
		$tpl['DISTROSERVER'] = dgettext(NGBOOST, 'DistroServer') . ' ' . $this->distro;
        $tpl['DISTRO_LABEL']  = dgettext(NGBOOST, 'Release');
        $tpl['MODULE_LABEL']  = dgettext(NGBOOST, 'Module');
        $tpl['VERSION_LABEL'] = dgettext(NGBOOST, 'Version');
        $tpl['LASTNEW_LABEL'] = dgettext(NGBOOST, 'LastNew');
        $tpl['ISHERE_LABEL'] = dgettext(NGBOOST, 'IsLocal');
        $tpl['OP_LABEL'] = dgettext(NGBOOST, 'Commands');
		foreach ($this->xml->distro as $distro) {
			if (isset($distro->name)) {
				$oval=(string)$distro->name;
			} else {
				$oval=(string)$distro->release;
			}
			if ($selrel > '' && $oval <> $selrel) continue;
			$title=str_replace(',',' ',$oval);
			foreach ($distro->modules->module as $modo) {
				$zebra=='0'?$zebra='1':$zebra='0';
				$mod = (string)$modo->name;
				// is installed?
				if ($_SESSION[NGBOOST]['ml'][$mod]['in']  == ISTRUE || $mod == 'base') {
					$is='Y ' . $_SESSION[NGBOOST]['ml'][$mod]['vfs'] . ' active';
					$op='';
				} else {
					if ((file_exists('mod/'.$mod) && is_dir('mod/'.$mod)) || $mod=='base') {
						$is='Y ' . $_SESSION[NGBOOST]['ml'][$mod]['vfs'];
						$op='<span id="ngrmna'.$mod
						.	'"><a href="javascript:ngRemoMod(\''.$mod.'\',\''
						.	count($_SESSION[NGBOOST]['FG'])
						.	'\')">purge</a></span>';
					} else {
						$is='N';
						$_SESSION[NGBOOST]['FG'][$mod][]=$distropath.$mod.'/'.$mod.'_'.strtr((string)$modo->version,'.','_').'.tar.gz';
						$op='<span id="ngpickuprm0'.$mod
						.	'"><a href="javascript:ngPickup(\''.$mod.'\',\''
						.	'1'
						.	'\')">get</a></span><span id="ngpickuprp0'.$mod.'"></span>'
						.		'<span id="ngpickupcm0'.$mod.'"></span>'
						.		'<span id="ngpickupcp0'.$mod.'"></span>'
						.		'<span id="ngpickupxm0'.$mod.'"></span>'
						.		'<span id="ngpickupxp0'.$mod.'"></span>'
						.		'<span id="ngpickupum0'.$mod.'"></span>'
						.		'<span id="ngpickupup0'.$mod.'"></span>'
						.		'<span id="ngpickupzz0'.$mod.'"></span>';
					}
				}
				if (isset($modo->lastnew)) {
					$lastnew=(string)$modo->lastnew;
				} else {
					$lastnew=(string)$modo->version;
				}
				$tpl['row'][]=array('DISTRO'=>'<span id="ngchnf'.$mod.'">'.$title.'</span>',
									'MODULE'=>$mod,
									'VERSION'=>'<span id="ngchnv'.$mod.'">'.(string)$modo->version.'</span>',
									'LASTNEW'=>$lastnew,
									'ISHERE'=>$is,
									'ZEBRA'=>$zebra,
									'OP'=>$op);
			}
		}
        return PHPWS_Template::process($tpl, 'ngboost', 'distro_list.tpl');
	}
	
    protected function ngTabListTable($mod,$table)
    {
        $tc = md5($table);
        $_SESSION[NGBOOST]['FG']['ngtn'][$tc] = $table;
        $alnk = '<a href="javascript:ngBuT(\'' . $tc . '\',\'' .md5($mod). '\')">backup table</a>';
        return $alnk;
    }

    protected function ngTabListTables($op,$mod,$tables)
    {
        $modtc=md5($mod);
        $_SESSION[NGBOOST]['FG']['0m'.$modtc] = $tables;
        $alnk = '<a href="javascript:ngBuTs1(\'' . $op . '\',\''. $modtc . '\')">backup modules tables</a>';
        return $alnk;
    }
}
?>