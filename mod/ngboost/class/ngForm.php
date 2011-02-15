<?php

/**
 * @author Hilmar Runge <ngwebsite.net>
 */
 

class ngBoost_Form extends ngBoost_Action {

 	/** @var bool $this->isbranch state */
	var $isbranch;
	
	var $distro;
	var $xml;
	
	function __construct() 
	{
		parent::__construct();
		$this->isbranch = (bool)PHPWS_Core::isBranch();
	}
	
	
 	/** @param string $type 'core' or 'noco' */
 	/** @return string full templated module list */
	protected function ngTabModules($type)
    {
		
		$_SESSION[NGBOOST]['ml']=array();
		$this->_ngGetModules();
		
		// echo '<pre>';print_r($_SESSION[NGBOOST]['ml']);echo '</pre>';
		
        $tpl['PHPWS_VERSION'] = PHPWS_Core::releaseVersion();
        $tpl['TITLE_LABEL']   = dgettext('ngboost', 'Module');
        $tpl['VERSION_LABEL'] = dgettext('ngboost', 'Current version');
        $tpl['LATEST_LABEL']  = dgettext('ngboost', 'Latest version');
        $tpl['COMMAND_LABEL'] = dgettext('ngboost', 'Commands');
        $tpl['ELSE_LABEL']    = dgettext('ngboost', 'Action');
        $tpl['ABOUT_LABEL']   = dgettext('ngboost', 'More information');
		
		switch ($type)
		{
		case 'core':
			$zebra='1';
			foreach ($_SESSION[NGBOOST]['ml'] as $amod => $mod) {
				if ($mod['co'] === ISTRUE) {
					$zebra=='0'?$zebra='1':$zebra='0';
					$tpl['mod-row'][] = $this->ngTplSetModuleRow($amod,$zebra);
				}
			}
			break;
		case 'noco':
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
            $tpl['CHECK_FOR_UPDATES'] = '<a href="javascript:ngCheckAll()">'.dgettext('ngboost','CheckAll').'</a>';
            $this->isbranch
            ?$alnk =  '<a href="javascript:ngBuBranch()">'.dgettext('ngboost','BackupBranch').'</a>'
            :$alnk = '<a href="javascript:ngBuAll()">'.dgettext('ngboost','BackupAll').'</a>';
            $tpl['CHECK_FOR_UPDATES'] = $alnk . NGSP3 . $tpl['CHECK_FOR_UPDATES'];
			
        } else {
            $tpl['CHECK_FOR_UPDATES'] = dgettext('ngboost', 'Server configuration prevents version checking.');
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
		$_SESSION[NGBOOST]['ml']=array();
		$this->_ngGetModules();
		
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
			foreach ($tmp['MODULE'] as $t) {
				if (is_array($t)) {
					$_SESSION[NGBOOST]['ml'][$m]['don'][] = array('mo'=>$t['TITLE'],'v'=>$t['VERSION'],'dl'=>$t['URL']);
				}
			}
			$tmp = $mod->isDependedUpon();
			if (!empty($tmp)) {
				$_SESSION[NGBOOST]['ml'][$m]['dme']=$tmp;
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

		if (empty($_SESSION['FG']['ngvx'][$amod])) {
			$atxt=dgettext('ngboost', 'Check');
		} else {
			$atxt=$_SESSION['FG']['ngvx'][$amod];
			if (version_compare($_SESSION[NGBOOST]['ml'][$amod]['vfs'], $_SESSION['FG']['ngvx'][$amod], '<')) {
				if (!$this->isbranch) {
					$atxt.=' - '.dgettext('ngboost', 'GetNew');
				}
			}
		}
		
        $template['LATEST'] = 
		ini_get('allow_url_fopen')
        ?'<a href="javascript:ngCheck(\''.$amod.'\')">'
        .'<span id="ngchk'.$amod.'">'.$atxt.'</span></a>'
        :dgettext('ngboost', 'Check disabled');
		
		$acmd=array();
		if ($_SESSION[NGBOOST]['ml'][$amod]['in']===ISTRUE) {
			// is installed
			version_compare($_SESSION[NGBOOST]['ml'][$amod]['vdb'], $_SESSION[NGBOOST]['ml'][$amod]['vfs'], '<')
			?$acmd[]='<a id="ngup'.$amod.'" href="javascript:ngUpdate(\''.$amod.'\')">'
			.dgettext('ngboost', 'Update').'</a>'
			:$nop='';
			// other mods are depended upon me (only important when installed to prevent uninstall)
			if (isset($_SESSION[NGBOOST]['ml'][$amod]['dme']) && is_array($_SESSION[NGBOOST]['ml'][$amod]['dme'])) {
				$acmd[]= '<a href="javascript:ngShowDepUpon(\''.$amod.'\')">'
						.dgettext('ngboost', 'DependedUpon').'</a>';
			} else {
				// may be uninstalled
				if ($_SESSION[NGBOOST]['ml'][$amod]['co']===ISFALSE) {
					$acmd[]='<a id="ngun'.$amod.'" href="javascript:ngUnInstall(\''.$amod.'\')">'
						.dgettext('ngboost', 'Uninstall').'</a>';
				} else {
					$acmd[]=dgettext('ngboost', 'IsInstalled');
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
								.dgettext('ngboost', 'MissingDependency').'</a>';
						// one is enough
						break;
					}
				}
				// has no dependencies, may become installed
				if (!$hasd) {
				$acmd[]='<a id="ngin'.$amod.'" href="javascript:ngInstall(\''.$amod.'\')">'
					.dgettext('ngboost', 'Install').'</a>';
				}
			}
		}
			
		
		$template['COMMAND']=(count($acmd)==0)?dgettext('ngboost', 'None'):implode(NGSP3,$acmd);
			
        $template['ABOUT'] =
		$_SESSION[NGBOOST]['ml'][$amod]['isa'] == ISTRUE
		?'<a href="javascript:ngAbout(\''.$amod.'\')">'.dgettext('ngboost','about').'</a>'
        :'';
		$template['ABOUT'] .= '&nbsp;'.$_SESSION[NGBOOST]['ml'][$amod]['t'];
		
 		$template['ELSE'] = 
		$this->isbranch
		?''
		:'<a href="javascript:ngBu(\''.$amod.'\')">'.dgettext('ngboost','Backup').'</a>';
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
	
    protected static function ngTabDistro()
    {
        $alnk = '<a href="javascript:ngPlain(\'tsl\')">List</a>' . NGSP3
        .		'<a href="javascript:ngPlain(\'ts\')">SelectDistro</a>'
        .		'<p id="ngmsgt31"></p>';
        return $alnk;
	}
	
    protected static function ngTabRepo()
    {
        $alnk='<a href="javascript:ngPlain(\'crp\')">VerifyRepositoryPath</a>'
            . NGSP3
            . '<a href="javascript:ngPlain(\'lrp\')">ListRepository</a>'
            . '<p id="ngmsgt41"></p>';
        return $alnk;
    }

    protected function ngTabLTar($fn)
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

    protected static function ngTabDB()
    {
        $alnk = '<a href="javascript:ngPlain(\'ldb\')">ListTables</a>'
        .		'<p id="ngmsgt51"></p>';
        return $alnk;
    }

    protected static function ngTabTune()
    {
        $alnk = '<a href="javascript:ngPatos()">Patos</a>' . NGSP3
		.		'<a href="javascript:ngListLog()">ListBoostLog</a>'
		//	.	NGSP3
		//	.	'<a href="javascript:ngPlain(\'fs\')">FS.perms</a>'
        .		'<p id="ngmsgt61"></p>';
        return $alnk;
    }

    protected function ngTabListDistros()
    {
		$tpl['DISTROSERVER'] = dgettext('ngboost', 'DistroServer') . ' ' . $this->distro;
        $tpl['DISTRO_LABEL']  = dgettext('ngboost', 'Distro');
        $tpl['MODULE_LABEL']  = dgettext('ngboost', 'Module');
        $tpl['VERSION_LABEL'] = dgettext('ngboost', 'Version');
        $tpl['ISHERE_LABEL'] = dgettext('ngboost', 'IsLocal');
        $tpl['OP_LABEL'] = dgettext('ngboost', 'Commands');
		foreach ($this->xml->distro as $distro) {
			$zebra=='0'?$zebra='1':$zebra='0';
			$title=(string)$distro->title;
			foreach ($distro->modules->module as $modo) {
				$mod = (string)$modo->name;
				$is=((file_exists('mod/'.$mod) && is_dir('mod/'.$mod)) || $mod=='base')?'Y':'N';
				$tpl['row'][]=array('DISTRO'=>$title,
									'MODULE'=>$mod,
									'VERSION'=>(string)$modo->version,
									'ISHERE'=>$is,
									'ZEBRA'=>$zebra);
				$title='';
			}
		}
        return PHPWS_Template::process($tpl, 'ngboost', 'distro_list.tpl');
	}
	
    protected function ngTabListTable($mod,$table)
    {
        $tc = md5($table);
        $_SESSION['FG']['ngtn'][$tc] = $table;
        $alnk = '<a href="javascript:ngBuT(\'' . $tc . '\',\'' .md5($mod). '\')">backup table</a>';
        return $alnk;
    }

    protected function ngTabListTables($op,$mod,$tables)
    {
        $modtc=md5($mod);
        $_SESSION['FG']['0m'.$modtc] = $tables;
        $alnk = '<a href="javascript:ngBuTs1(\'' . $op . '\',\''. $modtc . '\')">backup modules tables</a>';
        return $alnk;
    }
}
?>