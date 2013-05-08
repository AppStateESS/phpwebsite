<?php
/**
 *	Filemanager PHP class
 *
 *	filemanager.class.php
 *	class for the filemanager.php connector
 *
 *	@license	MIT License
 *	@author		Riaan Los <mail (at) riaanlos (dot) nl>
 *	@author		Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright	Authors
 */

class Filemanager {

  protected $config = array();
  protected $language = array();
  protected $get = array();
  protected $post = array();
  protected $properties = array();
  protected $item = array();
  protected $languages = array();
  protected $root = '';
  protected $doc_root = '';

  public function __construct($config) {
    $this->config = $config;
    $this->root = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
    $this->properties = array(
  	  	  'Date Created'=>null,
  	  	  'Date Modified'=>null,
  	  	  'Height'=>null,
  	  	  'Width'=>null,
  	  	  'Size'=>null
    );
    if (isset($this->config['doc_root'])) {
      $this->doc_root = $this->config['doc_root'];
    } else {
      $this->doc_root = $_SERVER['DOCUMENT_ROOT'];
    }

    $this->setParams();
    $this->availableLanguages();
    $this->loadLanguageFile();
  }

  public function error($string,$textarea=false) {
    $array = array(
			'Error'=>$string,
			'Code'=>'-1',
			'Properties'=>$this->properties
    );
    if($textarea) {
      echo '<textarea>' . json_encode($array) . '</textarea>';
    } else {
      echo json_encode($array);
    }
    die();
  }

  public function lang($string) {
    if(isset($this->language[$string]) && $this->language[$string]!='') {
      return $this->language[$string];
    } else {
      return 'Language string error on ' . $string;
    }
  }

  public function getvar($var) {
    if(!isset($_GET[$var]) || $_GET[$var]=='') {
      $this->error(sprintf($this->lang('INVALID_VAR'),$var));
    } else {
      $this->get[$var] = $this->sanitize($_GET[$var]);
      return true;
    }
  }
  public function postvar($var) {
    if(!isset($_POST[$var]) || $_POST[$var]=='') {
      $this->error(sprintf($this->lang('INVALID_VAR'),$var));
    } else {
      $this->post[$var] = $_POST[$var];
      return true;
    }
  }

  public function getinfo() {
    $this->item = array();
    $this->item['properties'] = $this->properties;
    $this->get_file_info();

    $array = array(
			'Path'=> $this->getPath(),
			'Filename'=>$this->item['filename'],
			'File Type'=>$this->item['filetype'],
			'Preview'=>$this->item['preview'],
			'Properties'=>$this->item['properties'],
			'Error'=>"",
			'Code'=>0
    );
    return $array;
  }

  public function getRealPath($path)
  {
    // Remove the protocol and hostname from the base url, since it is not compared below
    $base = preg_replace("|https?://{$_SERVER['HTTP_HOST']}|", '', $this->config['base_url']);

    // Grab the doc root
    $root = $this->doc_root;

    // If the last character of the doc root is not /, make it /
    if(substr($root, -1, 1) != '/') $root .= '/';

    // Replace the base URL with the doc root
    return preg_replace("|^$base|", $root, $path);
  }

  public function hasPath()
  {
    return isset($this->get['path']);
  }

  public function getPath()
  {
    // Consistently decode the provided path
    $path = rawurldecode($this->get['path']);

    // Replace two or more slashes with just one slash
    $path = preg_replace('|//+|', '/', $path);

    // If anyone tries to insert naughty characters and break out of the
    // web root, let's just go ahead and spoil their fun.
    $path = preg_replace('|\.\./|', '/', $path);

    return $path;
  }

  public function getfolder() {
    $array = array();
    $filesDir = array();

    $current_path = $this->getRealPath(rawurldecode($this->getPath()));
    if(!is_dir($current_path)) {
      $this->error(sprintf($this->lang('DIRECTORY_NOT_EXIST'),$this->getPath()));
    }
    if(!$handle = opendir($current_path)) {
      $this->error(sprintf($this->lang('UNABLE_TO_OPEN_DIRECTORY'),$this->getPath()));
    } else {
      while (false !== ($file = readdir($handle))) {
        if($file != "." && $file != "..") {
          array_push($filesDir, $file);
        }
      }
      closedir($handle);

      sort($filesDir); // sorting by names

      // Sort files by modified time, latest to earliest
      // Use SORT_ASC in place of SORT_DESC for earliest to latest
      //      array_multisort(
      //        array_map( 'filemtime', $filesDir ),
      //        SORT_NUMERIC,
      //        SORT_DESC,
      //        $filesDir
      //      );

      foreach($filesDir as $file) {
        if(is_dir($current_path . $file)) {
          if(!in_array($file, $this->config['unallowed_dirs'])) {
            $array[$this->getPath() . $file .'/'] = array(
						'Path'=> $this->getPath() . $file .'/',
						'Filename'=>$file,
						'File Type'=>'dir',
						'Preview'=> $this->config['icons']['path'] . $this->config['icons']['directory'],
						'Properties'=>array(
							'Date Created'=>null,
							'Date Modified'=>null,
							'Height'=>null,
							'Width'=>null,
							'Size'=>null
            ),
						'Error'=>"",
						'Code'=>0
            );
          }
        } else if (!in_array($file, $this->config['unallowed_files'])) {
          $this->item = array();
          $this->item['properties'] = $this->properties;
          ######################### HERE###################
          #  $this->error($this->get['path'] . $file);
          $this->get_file_info($this->getPath() . $file);

          if(!isset($this->params['type']) || (isset($this->params['type']) && strtolower($this->params['type'])=='images' && in_array(strtolower($this->item['filetype']),$this->config['images']))) {
            if($this->config['upload']['imagesonly']== false || ($this->config['upload']['imagesonly']== true && in_array(strtolower($this->item['filetype']),$this->config['images']))) {
              $array[$this->getPath() . $file] = array(
							'Path'=>$this->getPath() . $file,
							'Filename'=>$this->item['filename'],
							'File Type'=>$this->item['filetype'],
							'Preview'=>$this->item['preview'],
							'Properties'=>$this->item['properties'],
							'Error'=>"",
							'Code'=>0
              );
            }
          }
        }
      }
    }

    return $array;
  }

  public function rename() {

    $suffix='';

    $old = rawurldecode($this->get['old']);
    $new = rawurldecode($this->get['new']);

    if(substr($old,-1,1)=='/') {
      $old = substr($old,0,(strlen($old)-1));
      $suffix='/';
    }
    $tmp = explode('/',$old);
    $filename = $tmp[(sizeof($tmp)-1)];
    $path = str_replace('/' . $filename,'',$old);

    if(file_exists ($this->getRealPath($path) . '/' . $new)) {
      if($suffix=='/' && is_dir($this->getRealPath($path) . '/' . $new)) {
        $this->error(sprintf($this->lang('DIRECTORY_ALREADY_EXISTS'),$new));
      }
      if($suffix=='' && is_file($this->getRealPath($path) . '/' . $new)) {
        $this->error(sprintf($this->lang('FILE_ALREADY_EXISTS'),$new));
      }
    }

    if(!rename($this->getRealPath($old),$this->getRealPath($path) . '/' . $new)) {
      if(is_dir($old)) {
        $this->error(sprintf($this->lang('ERROR_RENAMING_DIRECTORY'),$filename,$new));
      } else {
        $this->error(sprintf($this->lang('ERROR_RENAMING_FILE'),$filename,$new));
      }
    }
    $array = array(
			'Error'=>"",
			'Code'=>0,
			'Old Path'=>$old,
			'Old Name'=>$filename,
			'New Path'=>$path . '/' . $new.$suffix,
			'New Name'=>$new
    );
    return $array;
  }

  public function delete() {
    $path = rawurldecode($this->getPath());
    if(is_dir($this->getRealPath($path))) {
      $this->unlinkRecursive($this->getRealPath($path));
      $array = array(
				'Error'=>"",
				'Code'=>0,
				'Path'=>$this->getPath()
      );
      return $array;
    } else if(file_exists($this->getRealPath($path))) {
      unlink($this->getRealPath($path));
      $array = array(
				'Error'=>"",
				'Code'=>0,
				'Path'=>$this->getPath()
      );
      return $array;
    } else {
      $this->error(sprintf($this->lang('INVALID_DIRECTORY_OR_FILE')));
    }
  }

  public function add() {
    $this->setParams();
    if(!isset($_FILES['newfile']) || !is_uploaded_file($_FILES['newfile']['tmp_name'])) {
      $this->error(sprintf($this->lang('INVALID_FILE_UPLOAD')),true);
    }
    if(($this->config['upload']['size']!=false && is_numeric($this->config['upload']['size'])) && ($_FILES['newfile']['size'] > ($this->config['upload']['size'] * 1024 * 1024))) {
      $this->error(sprintf($this->lang('UPLOAD_FILES_SMALLER_THAN'),$this->config['upload']['size'] . 'Mb'),true);
    }
    if($this->config['upload']['imagesonly'] || (isset($this->params['type']) && strtolower($this->params['type'])=='images')) {
      if(!($size = @getimagesize($_FILES['newfile']['tmp_name']))){
        $this->error(sprintf($this->lang('UPLOAD_IMAGES_ONLY')),true);
      }
      if(!in_array($size[2], array(1, 2, 3, 7, 8))) {
        $this->error(sprintf($this->lang('UPLOAD_IMAGES_TYPE_JPEG_GIF_PNG')),true);
      }
    }
    $_FILES['newfile']['name'] = $this->cleanString($_FILES['newfile']['name'],array('.','-'));
    $currentpath = rawurldecode($this->post['currentpath']);
    if(!$this->config['upload']['overwrite']) {
      $_FILES['newfile']['name'] = $this->checkFilename($this->getRealPath($currentpath),$_FILES['newfile']['name']);
    }
    move_uploaded_file($_FILES['newfile']['tmp_name'], $this->getRealPath($currentpath) . $_FILES['newfile']['name']);
    chmod($this->getRealPath($currentpath) . $_FILES['newfile']['name'], 0644);

    $response = array(
			'Path'=>$this->post['currentpath'],
			'Name'=>$_FILES['newfile']['name'],
			'Error'=>"",
			'Code'=>0
    );
    echo '<textarea>' . json_encode($response) . '</textarea>';
    die();
  }

  public function addfolder() {
    $path = rawurldecode($this->getPath());
    if(is_dir($this->getRealPath($path) . $this->get['name'])) {
      $this->error(sprintf($this->lang('DIRECTORY_ALREADY_EXISTS'),$this->get['name']));

    }
    $newdir = $this->cleanString($this->get['name']);
    if(!mkdir($this->getRealPath($path) . $newdir,0755)) {
      $this->error(sprintf($this->lang('UNABLE_TO_CREATE_DIRECTORY'),$newdir));
    }
    $array = array(
			'Parent'=>$this->getPath(),
			'Name'=>$this->get['name'],
			'Error'=>"",
			'Code'=>0
    );
    return $array;
  }

  public function download() {

    if($this->hasPath() && file_exists($this->getRealPath($this->getPath()))) {
      header("Content-type: application/force-download");
      header('Content-Disposition: inline; filename="' . basename($this->getPath()) . '"');
      header("Content-Transfer-Encoding: Binary");
      header("Content-length: ".filesize($this->getRealPath($this->getPath())));
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . basename($this->getPath()) . '"');
      readfile($this->getRealPath($this->getPath()));
      exit();
    } else {
      $this->error(sprintf($this->lang('FILE_DOES_NOT_EXIST'),$this->getPath()));
    }
  }

  public function preview() {

    if($this->hasPath() && file_exists($this->getRealPath($this->getPath()))) {
      header("Content-type: image/" .$ext = pathinfo($this->getPath(), PATHINFO_EXTENSION));
      header("Content-Transfer-Encoding: Binary");
      header("Content-length: ".filesize($this->getRealPath($this->getPath())));
      header('Content-Disposition: inline; filename="' . basename($this->getPath()) . '"');
      readfile($this->getRealPath($this->getPath()));
      exit();
    } else {
      $this->error(sprintf($this->lang('FILE_DOES_NOT_EXIST'),$this->getPath()));
    }
  }

  private function setParams() {
    $tmp = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
    $tmp = explode('?',$tmp);
    $params = array();
    if(isset($tmp[1]) && $tmp[1]!='') {
      $params_tmp = explode('&',$tmp[1]);
      if(is_array($params_tmp)) {
        foreach($params_tmp as $value) {
          $tmp = explode('=',$value);
          if(isset($tmp[0]) && $tmp[0]!='' && isset($tmp[1]) && $tmp[1]!='') {
            $params[$tmp[0]] = $tmp[1];
          }
        }
      }
    }
    $this->params = $params;
  }


  private function get_file_info($path='',$return=array()) {
    if($path=='') {
      $path = $this->getPath();
    }
    $tmp = explode('/',$path);
    $this->item['filename'] = $tmp[(sizeof($tmp)-1)];

    $tmp = explode('.',$this->item['filename']);
    $this->item['filetype'] = $tmp[(sizeof($tmp)-1)];
    $this->item['filemtime'] = filemtime($this->getRealPath($path));
    $this->item['filectime'] = filectime($this->getRealPath($path));

    $this->item['preview'] = $this->config['icons']['path'] . $this->config['icons']['default'];

    if(is_dir($this->getRealPath($path))) {

      $this->item['preview'] = $this->config['icons']['path'] . $this->config['icons']['directory'];

    } else if(in_array(strtolower($this->item['filetype']),$this->config['images'])) {

      $this->item['preview'] = 'connectors/php/filemanager.php?mode=preview&sn='.session_name().'&path=' . rawurlencode($path);
      //if(isset($get['getsize']) && $get['getsize']=='true') {
      $this->item['properties']['Size'] = filesize($this->getRealPath($path));
      if ($this->item['properties']['Size']) {
        list($width, $height, $type, $attr) = getimagesize($this->getRealPath($path));
      } else {
        $this->item['properties']['Size'] = 0;
        list($width, $height) = array(0, 0);
      }
      $this->item['properties']['Height'] = $height;
      $this->item['properties']['Width'] = $width;
      $this->item['properties']['Size'] = filesize($this->getRealPath($path));
      //}

    } else if(file_exists($this->root . $this->config['icons']['path'] . strtolower($this->item['filetype']) . '.png')) {

      $this->item['preview'] = $this->config['icons']['path'] . strtolower($this->item['filetype']) . '.png';
      $this->item['properties']['Size'] = filesize($this->getRealPath($path));
      if (!$this->item['properties']['Size']) $this->item['properties']['Size'] = 0;

    }

    $this->item['properties']['Date Modified'] = @date($this->config['date'], $this->item['filemtime']);
    //$return['properties']['Date Created'] = date($config['date'], $return['filectime']); // PHP cannot get create timestamp
  }

  private function unlinkRecursive($dir,$deleteRootToo=true) {
    if(!$dh = @opendir($dir)) {
      return;
    }
    while (false !== ($obj = readdir($dh))) {
      if($obj == '.' || $obj == '..') {
        continue;
      }

      if (!@unlink($dir . '/' . $obj)) {
        $this->unlinkRecursive($dir.'/'.$obj, true);
      }
    }

    closedir($dh);

    if ($deleteRootToo) {
      @rmdir($dir);
    }
    return;
  }

  private function cleanString($string, $allowed = array()) {
    $allow = null;

    if (!empty($allowed)) {
      foreach ($allowed as $value) {
        $allow .= "\\$value";
      }
    }

    $mapping = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ő'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ű'=>'U', 'Ý'=>'Y',
        'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n',
        'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ő'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'ű'=>'u',
        'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', ' '=>'_', "'"=>'_', '/'=>''
        );

        if (is_array($string)) {

          $cleaned = array();

          foreach ($string as $key => $clean) {
            $clean = strtr($clean, $mapping);
            $clean = preg_replace("/[^{$allow}_a-zA-Z0-9]/", '', $clean);
            $cleaned[$key] = preg_replace('/[_]+/', '_', $clean); // remove double underscore
          }
        } else {
          $string = strtr($string, $mapping);
          $string = preg_replace("/[^{$allow}_a-zA-Z0-9]/", '', $string);
          $cleaned = preg_replace('/[_]+/', '_', $string); // remove double underscore
        }
        return $cleaned;
  }

  private function sanitize($var) {
    $sanitized = strip_tags($var);
    $sanitized = str_replace('http://', '', $sanitized);
    $sanitized = str_replace('https://', '', $sanitized);
    $sanitized = str_replace('../', '', $sanitized);
    return $sanitized;
  }

  private function checkFilename($path,$filename,$i='') {
    if(!file_exists($path . $filename)) {
      return $filename;
    } else {
      $_i = $i;
      $tmp = explode(/*$this->config['upload']['suffix'] . */$i . '.',$filename);
      if($i=='') {
        $i=1;
      } else {
        $i++;
      }
      $filename = str_replace($_i . '.' . $tmp[(sizeof($tmp)-1)],$i . '.' . $tmp[(sizeof($tmp)-1)],$filename);
      return $this->checkFilename($path,$filename,$i);
    }
  }

  private function loadLanguageFile() {

    // we load langCode var passed into URL if present and if exists
    // else, we use default configuration var
    $lang = $this->config['culture'];
    if(isset($this->params['langCode']) && in_array($this->params['langCode'], $this->languages)) $lang = $this->params['langCode'];

    if(file_exists($this->root. 'scripts/languages/'.$lang.'.js')) {
      $stream =file_get_contents($this->root. 'scripts/languages/'.$lang.'.js');
      $this->language = json_decode($stream, true);
    } else {
      $stream =file_get_contents($this->root. 'scripts/languages/'.$lang.'.js');
      $this->language = json_decode($stream, true);
    }
  }

  private function availableLanguages() {

    if ($handle = opendir($this->root.'/scripts/languages/')) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
          array_push($this->languages, pathinfo($file, PATHINFO_FILENAME));
        }
      }
      closedir($handle);
    }
  }
}
?>
