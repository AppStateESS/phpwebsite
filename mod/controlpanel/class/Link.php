<?php

class PHPWS_ControlPanel_Link extends PHPWS_Item {

  var $_module = NULL;
  var $_url = NULL;
  var $_description = NULL;
  var $_image = array();
  var $_admin = FALSE;
  var $_showImage = TRUE;
  var $_showDescription = TRUE;

  function PHPWS_ControlPanel_Link($id = NULL) {
    $this->setTable("mod_controlpanel_link");
    $excludeVars = array();
    $excludeVars[] = "_showImage";
    $excludeVars[] = "_showDescription";
    $this->addExclude($excludeVars);

    if(isset($id)) {
      $this->setId($id);
      $this->init();
    }
  }

  function get() {
    PHPWS_Core::initCoreClass("Text.php");
    if(!isset($this->_module) || !isset($this->_url)) {
      $message = Translate::get("The module or url was not set for the link.");
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel_Link::get", $message);
    }

    $tags["NAME"] = $this->getLabel();
    $tags["URL"] = $this->_url;

    if($this->_showDescription) {
      $tags["DESCRIPTION"] = PHPWS_Text::parseOutput($this->_description);
    }

    if($this->_showImage) {
      $tags["IMAGE"] = $this->getImage();
    }

    return PHPWS_Template::process($tags, "controlpanel", "link/view.tpl");
  }

  function save() {
    $message = NULL;
    if(!isset($this->_module)) {
      $message = Translate::get("The module for this link is not set.");
    }

    if(!isset($this->_url)) {
      $message = Translate::get("The url for this link is not set.");
    }

    if(!$this->getLabel()) {
      $message = Translate::get("The name for this link is not set.");
    }

    if(isset($message)) {
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel_Link::save", $message);
    } else {
      return $this->commit();
    }
  }

  function setModule($module) {
    $message = NULL;
    if(PHPWS_Core::moduleExists($module)) {
      $this->_module = $module;
    } else {
      $message = $_SESSION["translate"]->it("The module provided does not exist.");
    }

    if(isset($message)) {
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel_Link::setModule", $message);
    } else {
      return TRUE;
    }
  }

  function getModule() {
    return $this->_module;
  }

  function setURL($url) {
    $this->_url = $url;
  }

  function getURL() {
    return $this->_url;
  }

  function setDescription($description) {
    $message = NULL;
    if(is_string($description)) {
      $this->_description = PHPWS_Text::parseInput($description);
    } else {
      $message = $_SESSION["translate"]->it("Description must be a string.");
    }

    if(isset($message)) {
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel_Link::setDescription", $message);
    } else {
      return TRUE;
    }
  }

  function getDescription() {
    return $this->_description;
  }

  function setImage($image) {
    $message = NULL;
    if(is_array($image)) {
      if(array_key_exists("name", $image) && array_key_exists("alt", $image)) {
	$dir = "./images/mod/" . $this->_module . "/";
	if(is_file($dir . $image["name"])) {
	  $size = getimagesize($dir . $image["name"]);
	  $image["width"] = $size[0];
	  $image["height"] = $size[1];
	  $this->_image = $image;
	} else {
	  $message = $_SESSION["translate"]->it("The image file does not exist.");
	}
      } else {
	$message = $_SESSION["translate"]->it("Image array was malformed.");
      }
    } else {
      $message = $_SESSION["translate"]->it("The image passed in must be an array.");
    }

    if(isset($message)) {
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel_Link::setImage", $message);
    } else {
      return TRUE;
    }
  }

  function getImage() {
    if(isset($this->_module)) {
      if(array_key_exists("name", $this->_image) && array_key_exists("alt", $this->_image)) {
	$dir = "./images/mod/" . $this->_module . "/";
	$html = "<img src=\"" . $dir . $this->_image["name"] . "\" width=\"" . $this->_image["width"] .
	   "\" height=\"" . $this->_image["height"] . "\" alt=\"" . $this->_image["alt"] . "\" border=\"0\" />";
	return $html;
      } else {
	return NULL;
      }
    } else {
      return NULL;
    }
  }

  function setAdmin($bool) {
    $message = NULL;
    if(is_bool($bool)) {
      $this->_admin = $bool;
    } else {
      $message = $_SESSION["translate"]->it("You must pass a boolean value to this function.");
    }

    if(isset($message)) {
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel_Link::setAdmin", $message);
    } else {
      return TRUE;
    }
  }

  function getAdmin() {
    return $this->_admin;
  }

  function showImage($bool) {
    if(is_bool($bool)) {
      $this->_showImage = $bool;
    } else {
      return FALSE;
    }
  }

  function showDescription($bool) {
    if(is_bool($bool)) {
      $this->_showDescription = $bool;
    } else {
      return FALSE;
    }
  }

  function isViewable() {
    if($this->_admin) {
      return $_SESSION["User"]->allow_access($this->_module);
    } else {
      return TRUE;
    }
  }

}// END CLASS PHPWS_ControlPanel_Link

?>