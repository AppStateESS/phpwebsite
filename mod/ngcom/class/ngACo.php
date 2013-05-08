<?php

	// me 
	define ('NGCOM', 'ngcom');
	define ('NGCOMMID', 'nac');
	define ('NGCOMTITLE', 'ngCom ');


class ngACo {

	const PATXAOP = '/[a-zA-Z]*[a-zA-Z]/';
	
	var $context = '';
	var $conuser = '';

    public function __construct()
	{
		if (defined('NGCOM')) {
			$this->context=PHPWS_Core::getCurrentModule();
			$this->conuser=Current_user::isLogged();
		}
	}
	
	public function index()
	{
		// BG only
		if (isset($_REQUEST['xaop'])) {
			$xaop=preg_replace(self::PATXAOP, '', $_REQUEST['xaop'])?'':$_REQUEST['xaop'];
			switch ($xaop) 
			{
				case 'fcf':
					$_SESSION['BG']=$this->fcFo();
					return;
					break;
				case 'fcfi':
					$_SESSION['BG']=$this->fcFo('i');
					return;
					break;
				case 'fcfd':
					$_SESSION['BG']=$this->fcFo('d');
					return;
					break;
				case 'fcfm':
					$_SESSION['BG']=$this->fcFo('m');
					return;
					break;
				case 'fcp':
					$_SESSION['BG']=$this->fcPi();
					return;
					break;
				case 'fcpi':
					$_SESSION['BG']=$this->fcPi('i');
					return;
					break;
				case 'fcpd':
					$_SESSION['BG']=$this->fcPi('d');
					return;
					break;
				case 'fcpm':
					$_SESSION['BG']=$this->fcPi('m');
					return;
					break;
				default:
					$_SESSION['BG']=' ';
					return;
					break;
			}
		}
	}
	
	protected function fcFo($fclass='i') {
		if ($this->context==NGCOM) {
				$fold=strtr($fclass,'idm','123');
				$fcfs = Cabinet::listFolders($fold);
				$fonas='<option value=" "> </option>';
				if ($fcfs) {
					foreach ($fcfs as $fcf) {
						$_SESSION['FG'][NGCOM]['fcf'][$fcf['title']]=$fcf['id'];
						$fonas.='<option value="'.$fcf['title'].'">'.$fcf['title'].'</option>';
					}
				}
				return json_encode(array('mid'=>NGCOMMID,'fonas'=>$fonas));
		}
	}

	protected function fcPi($fclass='i') {
		if ($this->context==NGCOM) {
			$rqfo=false;
			if (isset($_REQUEST['s'])
			&& preg_match('/^[0-9a-zA-Z_\s]*$/',urldecode($_REQUEST['s']))===1)
			{
				(string)$rqfo=urldecode($_REQUEST['s']);
			}
			$picas='';
			$pinas='<option value=" "> </option>';
			if ($rqfo) {
				$foid=$_SESSION['FG'][NGCOM]['fcf'][$rqfo];
				if ($foid) {
					$was = strtr($fclass, array('i'=>'images','d'=>'documents','m'=>'multimedia'));
					$fcdb = new PHPWS_DB($was);
					$fcdb->addWhere('folder_id', $foid);
					$fcdb->addOrder('title');
					$rs = $fcdb->select('all');
					if ($rs) {
						foreach ($rs as $fcp) {
							$pinas.='<option value="'.$fcp['file_directory'].$fcp['file_name']
							.		'" data-pre="'.$fcp['id'].'">'
							.		$fcp['file_name'].'</option>';
							if ($fclass=='i') {
								$picas.='<img onclick="nacFcTn(this)" src="'.$fcp['file_directory'].'tn/'.$fcp['file_name']
								.		'" alt="'.$fcp['alt'].'" />' ;
							}
						}
					}
				}
			} 
			return json_encode(array('mid'=>NGCOMMID,'pinas'=>$pinas,'picas'=>$picas));
		}
	}
	
}

?>