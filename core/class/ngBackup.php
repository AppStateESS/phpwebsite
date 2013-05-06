<?php

/**
 * @version $Id: ngBackup.php 0004 2012-12-28 Hilmar $
 * @author Hilmar Runge <ngwebsite.net>
 */

  // a pear addition
  require_once 'Archive/Tar.php';
 
  // me 
  define (NGBU,'ngBackup');
	
  class ngBackup {
	
	var $context = NGBU;
	var $msg = '';
	var $rp = false;
		
	function __construct() {
		$this->context=PHPWS_Core::getCurrentModule();
	}

	public function getRepositoryPath()
	{
		// API	string|false=ngBackup::getRepositoryPath();
		
		$subdir=str_replace('/','.',trim(str_replace($_SERVER['SERVER_NAME'],'',PHPWS_Core::getHomeHttp(false)),'/'));
		$u=explode('/',$_SERVER['DOCUMENT_ROOT']);
		array_pop($u);
		$compath=implode('/',$u);
		
		$reposit=$compath.'/.repository/'.$subdir.'/';
		
		if (file_exists($compath)) {
			@mkdir($compath.'/.repository',0750);
			if (!file_exists($reposit)) {
				@mkdir($reposit,0700);
				// anycase, if within any view of the webserver
				if (file_exists($reposit)) {
					file_put_contents($reposit.'.htaccess','Deny from all');
					chmod($reposit.'.htaccess', 0600);
				}
			}
			if (file_exists($reposit)) {
				return $reposit;
			}
		}
		return false;
	}
	
	public function getTableList($returnprefixed=true)
	{
		// API:	$object = new ngBackup();
		//		array|false = $object->getTableList();
		
		if ($this->context) {
			$ar=PHPWS_DB::listTables();
			$this->prefix=PHPWS_DB::getPrefix();
			if ($this->prefix>'') {
				// take only table names starting with own prefix to hide foreign tables of same db
				// from access. But that cannot work in case the own site has no prefix and others in
				// the db do have. Such case is not able to control sufficient.
				// (within one db use always prefixes).
				foreach ($ar as $k=>$v) {
					if (strpos($v,$this->prefix)===0) {
						if ($returnprefixed) {
							$arp[]=$v;
						} else {
							$arp[]=substr($v,strlen($this->prefix));
						}
					}
				}
				return $arp;
			} else {
				// exclude prefixed tables HOW???
				foreach ($ar as $k=>$v) {
					if (1==1) {
					}
				}
				return $ar;
			}
		}
		return false;
	}
	
	public function backupMod($mod)
	{
		// API:	$object = new ngBackup();
		//		cc = $object->backupMod(module|'core');
		
		if (isset($this)) {
			// if (Current_User::allow(NGBU, 'com_export')) {}
			if (1==1) {
				$this->rp=ngBackup::getRepositoryPath();				
				if ($this->rp) {
					if ($mod == 'core') {
						$s1=$this->_backupFS('core1v4');
						$s2=$this->_backupFS('core2v4');
						$s3=$this->_backupFS('core3v4');
						$s4=$this->_backupFS('core4v4');
						return '0' . $s1 . '<br />' . $s2 . '<br />' . $s3 . '<br />' . $s4;
					} else {
						return '0' . $this->_backupFS($mod);
					}
				} else {
					// error repository path
					return 3;
				} 
			} else {
				// missing perms
				return 2;
			}
		}
		// no obj context
		return 1;
	}
			
	public function _backupFS($mod) {
		if (isset($this)) {
			// if (Current_User::allow(NGBU, 'com_export')) { }
			if (1==1) {
				if ($this-rp) {
					// the created tar has not to be within the saved path content, thus using some tmp
					$tmp=sys_get_temp_dir().'/';
					$site=substr(strtr(PHPWS_Core::getHomeHttp(false),'/','.'),0,-1);
					$filestamp=date("Ymd-His");
					$tgz = $mod.'.sysbu.'.$filestamp.'.fs.tgz';
					$tar = new Archive_Tar($tmp.$tgz);
					$tar->setErrorHandling(PEAR_ERROR_PRINT);
					switch ($mod) {
						case 'core1v1':
							// branches
							$tal[]=PHPWS_HOME_DIR . 'admin';
							$tal[]=PHPWS_HOME_DIR . 'config';
							$tal[]=PHPWS_HOME_DIR . 'files';
							$tal[]=PHPWS_HOME_DIR . 'images';
							$tal[]=PHPWS_HOME_DIR . '.htaccess';
							$tal[]=PHPWS_HOME_DIR . 'index.php';
							$tal[]=PHPWS_HOME_DIR . 'phpws_stats.php';
							$tar->createModify($tal, './', PHPWS_HOME_DIR);
							break;
						case 'core1v4':
							$tal[]=PHPWS_SOURCE_DIR . 'admin';
							$tal[]=PHPWS_SOURCE_DIR . 'config';
							$tal[]=PHPWS_SOURCE_DIR . 'convert';
							$tal[]=PHPWS_SOURCE_DIR . 'core';
							$tal[]=PHPWS_SOURCE_DIR . 'docs';
							$tal[]=PHPWS_SOURCE_DIR . 'files';
							$tal[]=PHPWS_SOURCE_DIR . 'images';
							$tal[]=PHPWS_SOURCE_DIR . 'inc';
							$tal[]=PHPWS_SOURCE_DIR . 'locale';
							$tal[]=PHPWS_SOURCE_DIR . 'logs';
							$tal[]=PHPWS_SOURCE_DIR . 'setup';
							// 
							$tal[]=PHPWS_SOURCE_DIR . 'templates';
							//
							$tal[]=PHPWS_SOURCE_DIR . 'README';
							$tal[]=PHPWS_SOURCE_DIR . '.htaccess';
							$tal[]=PHPWS_SOURCE_DIR . 'index.php';
							$tal[]=PHPWS_SOURCE_DIR . 'phpws_stats.php';
							$tar->createModify($tal, './', PHPWS_SOURCE_DIR);
							break;
						case 'core2v4':
							$tal[]=PHPWS_SOURCE_DIR . 'javascript';
							$tar->createModify($tal, './', PHPWS_SOURCE_DIR);
							break;
						case 'core3v4':
							$tal[]=PHPWS_SOURCE_DIR . 'lib';
							$tar->createModify($tal, './', PHPWS_SOURCE_DIR);
							break;
						case 'core4v4':
							$tal[]=PHPWS_SOURCE_DIR . 'themes';
							$tar->createModify($tal, './', PHPWS_SOURCE_DIR);
							break;
						default:
							$tal[]=PHPWS_SOURCE_DIR . 'mod/' . $mod;
							$tar->createModify($tal, './', PHPWS_SOURCE_DIR);
							break;
					}
					@copy($tmp.$tgz,$this->rp.$tgz);
					@unlink($tmp.$tgz);
					@chmod($this->rp.$tgz, 0600);
					if (isset($_SESSION['ngboost']['BUSIGN']['fs'])) {
						file_put_contents($this->rp.'.'.$_SESSION['ngboost']['BUSIGN']['fs'].'.txt',$tgz."\n",FILE_APPEND);
					}
					$this->msg=' '.'to'.' '.$tgz; 
					return $this->msg;
			} else {
					return 3;  
				} 
			} else {
				return 2;
			}
		}
	}

	public function restoreMod($fn) {
		if (isset($this)) {
			if (1==1) {
				$this->rp=ngBackup::getRepositoryPath();				
				if ($this->rp) {
				
					// design sysbu for fs
					list($mod,$sysbu,$stamp,$butype,$more) = explode('.',$fn,5);
					$ar=explode('.',$more);
					$ftype=array_pop($ar);
					$site=implode('.',$ar);
					
					// design distro src
					list($modv,$ftype2) = explode('.',$fn,2);
					list($mod2,$vsn2) = explode('_',$modv,2);
					
					if (($sysbu=='sysbu' && $butype=='fs' && $ftype=='tgz') 
					||  ($ftype2=='tar.gz' && $vsn2)) {
						if (file_exists($this->rp.$fn)) {
							$tar = new Archive_Tar($this->rp.$fn);
							
							// to regard empty dirs in tar
							$ar=$tar->listContent();
							$this->_mkDirsIfEmpty($ar);
							
							$cc=$tar->extract(PHPWS_SOURCE_DIR);
							if ($cc) {
								return '0' . $mod . ' ' . 'restored from' . ' ' . $fn;
							}
						} else {
							return '5'.'File does not exist'.'='.$fn;
						}
					} else {
						// invalid bu file design
						return '4'.'M='.$mod.'('.$mod2.')'.' B='.$butype.' X='.$ftype.$ftype2.' S='.$site;
					}
				} else {
					// error repository path
					return 3;
				}
			} else {
				// missing perms
				return 2;
			}
		}
		// no obj context
		return 1;
	}
	
	public function exportTable($table,$filestamp=false) {
		if (isset($this)) {
			if (Current_User::allow('ngboost')) {
				$this->prefix=PHPWS_DB::getPrefix();
				$bupath=$this->getRepositoryPath();
				if ($bupath) {
					if ($this->prefix > '') {
						if (substr($table,0,strlen($this->prefix)) == $this->prefix) {
							$table=substr($table,strlen($this->prefix));
						}
					}
					if (!$filestamp) {
						$filestamp=date("Ymd-His.");
					}
					$db = new PHPWS_DB($table);
					$sqlx='select * from '.$table.'';
					$rows = $db->query($sqlx);
					$rsn=$rows->numRows();
					if ($rows) {
						if (PEAR::isError($rows)) {
							$msg='3,not exported, pear error';
						} else {
							$this->bufilename=$this->mod.'.sysbu.'.$filestamp.'db.'.$table.'.data';
							$fp=@fopen($bupath.$this->bufilename, 'w');
							if ($fp === FALSE) {
								$msg='3,unable to open file '.$this->bufilename;
							} else {
								for ($i=0; $i < $rsn; $i++) {
									$row=$rows->fetchRow(DB_FETCHMODE_ASSOC);	
									$sql='INSERT INTO ' . $table . ' SET ';
									foreach ($row as $k => $v) {
										// ignore empty fields
										if ($v <> '') {
											$sql .= $k . '="' . urlencode($v) . '", ';
										}
									}
									// commenting the prefix and crc of the insert
									$sql = substr($sql, 0, -2).'--#'.sprintf("%u",crc32(substr($sql, 0, -2))).";\n";
									fwrite($fp, $sql);
								}
								$rows->free();
								fclose($fp);
								chmod($bupath.$this->bufilename, 0600);
								if (isset($_SESSION['ngboost']['BUSIGN']['db'])) {
									file_put_contents($bupath.'.'.$_SESSION['ngboost']['BUSIGN']['db'].'.txt',
										$this->bufilename."\n",FILE_APPEND);
								}
								$msg='0,export done, '.$rsn.' rows';
							}
						}
					} else {
						$msg='1,'.'not exported, table is empty';
					}
				} else {
					$msg='2,'.'error with repository';  
				} 
			} else {
				$msg='2,' . 'no permission';
			}
		} else {
			$msg='3,'.'no obj context';
		}
		return $msg;
	}

	public function importTable($filename) {
		if (isset($this)) {
			//	if (! Current_User::allow(NGCOM, 'fio_import')) {
			//		return $this->uniMsg(NGCOM::BR.'E222 '.NGCOM::MISS);
			//	}
			$this->prefix=PHPWS_DB::getPrefix();
			$bupath=$this->getRepositoryPath();
			if ($bupath) {
				$fr=fopen($bupath.$filename,'r');
				if ($fr) {
					$msg='0,'.dgettext('ngboost','Import start with').' '. $filename;
					$cc0=$cc1=$cc2=$cc3=$ccn=0;
					while (!feof($fr)) {
						$rec=fgets($fr);
						if (empty($rec)) break;
						$ccn++;
						$parts=explode('--#',$rec,2);
						$sql=$parts['0'];
						$crcim=sprintf("%u",crc32($sql));
						// (";" + lf)
						$crcex=substr($parts['1'],0,-2);
						if ($crcex==$crcim) {
							// record unchanged
							$cc2++;
						} else {
							$cc3++;
						}
						$sqla=explode('INSERT INTO ',$sql,2);
						$sqlb=explode(' SET ',$sqla['1'],2);
						$tbl=trim($sqlb['0']);
						if (substr($tbl,0,strlen($this->prefix)) == $this->prefix) {
							$tbl=substr($tbl,strlen($this->prefix));
						}
						// insert ignore is a replace
						$sqlx='INSERT IGNORE INTO '.$this->prefix.$tbl.' SET '.urldecode($sqlb['1'].';');
						$cc=PHPWS_DB::query(($sqlx),false);
						if (is_a($cc,DB_Error)) {
							$cc1++;
							//test($cc->userinfo);
							//test($cc->message);
							$feedback=explode('**',$cc->userinfo);
							$msg.='<br />'.dgettext('ngboost','Import error feedback').' ['.trim($feedback[1]);
							// log entry makes sense? ...
						} else {
							$cc0++;
						}
					}
					fclose($fr);
					
					$msg.='<br />'
					.	dgettext('ngboost','Import done for').' '.$tbl.', '
					.	$ccn.' '.dgettext('ngboost','rows').', '
					.	$cc1.' '.dgettext('ngboost','errors').', '.$cc0.' ok, '.$cc3.' neCRC, '.$cc2.' eqCRC.';
				} else {
					$msg='3,'.dgettext('ngboost','unable to open file').' '.$filename;
				}
			} else {
				$msg='3,'.dgettext('ngboost','error with repository');  
			} 
		} else {
			$msg='3,'.dgettext('ngboost','programming error, no object context');  
		}
		return $msg;
	}

	public function tarList($fn)
	{
		if (isset($this)) {
			if (1==1) {
				if (substr($fn,-4)=='.tgz' || substr($fn,-7)=='.tar.gz') {
					$rp=$this->getRepositoryPath();
					if ($rp) {
						if (file_exists($rp.$fn)) {
							$tar = new Archive_Tar($rp.$fn);
							$ar=$tar->listContent();
							// return '0<pre style="height:400px; overflow:auto;">'.print_r($ar,true).'</pre>';
							if ($ar) {
								$sumsize=0;
								$content='<h3>'.$fn.'</h3><div style="height:400px; overflow:auto;"><table>';
								foreach ($ar as $v) {
									if ($v['typeflag']==5) {$be=(int)0x4000;}
									elseif ($v['typeflag']==2) {$be=(int)0xa000;}
									elseif ($v['typeflag']==0) {$be=(int)0x8000;}
									else {$be=(int)0x0000;}
									$strperms=$this->_cvFilePerms($be + (int)$v['mode']);
									$strfname=rtrim($v['filename'],"\x00..\x1F");
									$content .= '<tr>'
									. '<td nowrap>'.date("Y-m-d H:i",$v['mtime']).'</td>'
									. '<td>'.$strperms.'</td>'
									. '<td align="right">'.$v['size'].'</td>'
									. '<td>'.$strfname.'</td>'
									. '</tr>';
									$sumsize = $sumsize + $v['size'];
								}
								$content.= '</table></div><h3>'
										. count($ar) .' ' . 'entries in archive'
										. ', ' . 'uncompressed' . ' ' .$sumsize. ' bytes.</h3>';
								return '0'.$content;
							}
						}
					}
				}
			} else {
				return 2;
			}
		}
		return 1;
	}

	public function _mkDirsIfEmpty($ar) {
		if (isset($this)) {
			if ($ar && is_array($ar)) {
				foreach ($ar as $a) {
					$fn=rtrim($a['filename']);
					$flag=(int)$a['typeflag'];
					$cmod=(int)$a['mode'];
					if ($flag==5) {$be=(int)0x4000;}
					elseif ($flag==2) {$be=(int)0xa000;}
					elseif ($flag==0) {$be=(int)0x8000;}
					else {$be=(int)0x0000;}
					$perms=$be + $cmod;
					$p=0;
					$p += (($perms & 0x0100) ? 256 : 0);
					$p += (($perms & 0x0080) ? 128 : 0);
					$p += (($perms & 0x0040) ?
						  (($perms & 0x0800) ? 0 : 64 ) :
						  (($perms & 0x0800) ? 0 : 0));
					$p += (($perms & 0x0020) ? 32 : 0);
					$p += (($perms & 0x0010) ? 16 : 0);
					$p += (($perms & 0x0008) ?
						  (($p & 0x0400) ? 0 : 8 ) :
						  (($p & 0x0400) ? 0 : 0));
					$p += (($perms & 0x0004) ? 4 : 0);
					$p += (($perms & 0x0002) ? 2 : 0);
					$p += (($perms & 0x0001) ?
					(($perms & 0x0200) ? 0 : 1 ) :
					(($perms & 0x0200) ? 0 : 0));
					if ($flag==5) {
						@mkdir($fn);
						chmod($fn,$p);
					}
				}
			}
		}
	}
	
	public function _getInstallSql($module) {
		return;
		// just drafty
                $file = $mod->getDirectory() . 'boost/install.sql';
                $db = new PHPWS_DB;
                $result = $db->importFile($mod->getDirectory() . 'boost/install.sql');
	}			

	
	public function _cvFilePerms($perms)
	{
		// format unix perms
		if (($perms & 0xC000) == 0xC000) {$info = 's'; } 	 // Socket
		elseif (($perms & 0xA000) == 0xA000) {$info = 'l'; } // Symbolic Link
		elseif (($perms & 0x8000) == 0x8000) {$info = '-'; } // Regular
		elseif (($perms & 0x6000) == 0x6000) {$info = 'b'; } // Block special
		elseif (($perms & 0x4000) == 0x4000) {$info = 'd'; } // Directory
		elseif (($perms & 0x2000) == 0x2000) {$info = 'c'; } // Character special
		elseif (($perms & 0x1000) == 0x1000) {$info = 'p'; } // FIFO pipe
		else {$info = '?';} // Unknown
		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
				 (($perms & 0x0800) ? 's' : 'x' ) :
				 (($perms & 0x0800) ? 'S' : '-'));
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
				 (($perms & 0x0400) ? 's' : 'x' ) :
				 (($perms & 0x0400) ? 'S' : '-'));
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
				 (($perms & 0x0200) ? 't' : 'x' ) :
				 (($perms & 0x0200) ? 'T' : '-'));
		return $info;
	}
	
  }

?>