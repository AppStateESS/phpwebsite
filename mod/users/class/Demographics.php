<?php
class PHPWS_User_Demographics {
  var $_name;
  var $_type;


  function installDemographics($file){
    if (!is_file($file))
      return PEAR::raiseError("File not found: $file");
    
    include $file;
    
    if (!isset($demo) || !is_array($demo))
      return PEAR::raiseError("Demo variable missing from config file.");
    
    foreach ($demo as $key=>$value){
      switch ($key){
      case "name":
	$this->setName($value);
	break;
	
      case "type":
	$this->setType($value);
	break;

      }// END key switch
    }
  }

  function setName($name){
    $DB = new PHPWS_DB("users_demographics");
    if ($DB->isTableColumn($name))
      return PEAR::raiseError("Column name exists");

    $this->_name = $name;
  }

  function getName(){
    return $this->_name;
  }

  function setType($type){
    $this->_type($type);
  }

  function getType(){
    return $this->_type;
  }
  
}
?>