<?php

/**
 * debugger for the phpWebSite core
 *
 * @author Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @version $Id$
 * @package Core
 */

class PHPWS_Debug {

  /**
   * flag whether or not class is active or not
   * @var bool 
   */
  var $active = 0;

  /**
   * flag whether or not to dump post variables
   * @var bool 
   */
  var $dumpPost = 0;

  /**
   * flag whether or not to dump get variables
   * @var bool 
   */
  var $dumpGet = 0;

  /**
   * flag whether or not to dump request variables
   * @var bool 
   */
  var $dumpRequest = 0;

  /**
   * flag whether of not to dump the core dump
   * @var bool 
   */
  var $dumpCore = 0;

  /**
   * array of session objects to dump
   * @var array 
   */
  var $sessionObjects = array();

  /**
   * array of session arrays to dump
   * @var array
   */
  var $sessionArrays = array();

  /**
   * array of other session vars to dump
   * @var array
   */
  var $sessionOthers = array();

  /**
   * flag whether or not to dump file variables
   * @var bool
   */
  var $dumpFile = 0;

  /**
   * flag whether or not to dump cookie variables
   * @var bool
   */
  var $dumpCookie = 0;

  /**
   * flag whether or not to dump server variables
   * @var bool 
   */
  var $dumpServer = 0;

  /**
   * flag whether of not to dump error message variable
   * @var bool 
   */
  var $errorMsg = 0;

  /**
   * flag whether of not to dump debug display before execution
   * @var bool 
   */
  var $beforeExecution = 0;

  /**
   * flag whether of not to dump debug display after execution
   * @var bool 
   */
  var $afterExecution = 0;

  /**
   * flag whether of not to display time info
   * @var bool 
   */
  var $showTimer = 0;

  var $showBlock = 0;

  /**
   * Constructor (not needed at this point)
   */
  function PHPWS_debug() {}

  /**
   * various get and set functions, pretty self-explanitory
   */
  function setShowTimer($activity) {$this->showTimer = $activity;}
  function setDumpCore($activity) {$this->dumpCore = $activity;}
  function setDumpPost($activity) {$this->dumpPost = $activity;}
  function setDumpGet($activity) {$this->dumpGet = $activity;}
  function setDumpRequest($activity) {$this->dumpRequest = $activity;}
  function setDumpFile($activity) {$this->dumpFile = $activity;}
  function setDumpCookie($activity) {$this->dumpCookie = $activity;}
  function setDumpServer($activity) {$this->dumpServer = $activity;}
  function setBeforeExecution($activity) {$this->beforeExecution = $activity;}
  function setAfterExecution($activity) {$this->afterExecution = $activity;}
  function setShowBlock($activity) {$this->showBlock = $activity;}

  function getShowTimer() {return $this->showTimer;}
  function getDumpCore() {return $this->dumpCore;}
  function getDumpPost() {return $this->dumpPost;}
  function getDumpGet() {return $this->dumpGet;}
  function getDumpRequest() {return $this->dumpRequest;}
  function getDumpFile() {return $this->dumpFile;}
  function getDumpCookie() {return $this->dumpCookie;}
  function getDumpServer() {return $this->dumpServer;}
  function getBeforeExecution() {return $this->beforeExecution;}
  function getAfterExecution() {return $this->afterExecution;}
  function getShowBlock() {return $this->showBlock;}

  function setActive($activity) {$this->active = $activity;}
  function isActive() {return $this->active;}

  function setActivity() {
    PHPWS_Core::toggle($this->active);
  }

  /**
   * adminSettings
   *
   * displays the debug options
   */
  function adminSettings() {
    translate("core");
    $title = _("phpWebSite Debugger Settings");

    $hiddens = array("module"=>"debug",
		     "DBUG_op"=>"save_settings");

    $elements[0] = $GLOBALS['core']->formHidden($hiddens);
    $elements[0] .= "<h4>" . _("Select the information for debug display.") . "</h4>";

    $template_array['EXECUTION'] = $GLOBALS['core']->formSelect("DBUG_active", array(0=>"Off", 1=>"On"), $this->active, NULL, 1);
    $template_array['EXECUTION'] .= _("Turn On/Off") . "<br />";
    $template_array['EXECUTION'] .= $GLOBALS['core']->formSelect("DBUG_time", array(0=>"Off", 1=>"On"), $this->showTimer, NULL, 1);
    $template_array['EXECUTION'] .= _("PhpWebSite Timer") . "<br />";
    $template_array['EXECUTION'] .= $GLOBALS['core']->formSelect("DBUG_block", array(0=>"Off", 1=>"On"), $this->showBlock, NULL, 1);
    $template_array['EXECUTION'] .= _("Debugger Block");

    $template_array['VARS'] = $GLOBALS['core']->formSelect("DBUG_post", array(0=>"Off", 1=>"On"), $this->dumpPost, NULL, 1);
    $template_array['VARS'] .= _("Post Variables") . "<br />";
    $template_array['VARS'] .= $GLOBALS['core']->formSelect("DBUG_get", array(0=>"Off", 1=>"On"), $this->dumpGet, NULL, 1);
    $template_array['VARS'] .= _("Get Variables") . "<br />";
    $template_array['VARS'] .= $GLOBALS['core']->formSelect("DBUG_request", array(0=>"Off", 1=>"On"), $this->dumpRequest, NULL, 1);
    $template_array['VARS'] .= _("Request Variables");

    $template_array['MORE_VARS'] = $GLOBALS['core']->formSelect("DBUG_file", array(0=>"Off", 1=>"On"), $this->dumpFile, NULL, 1);
    $template_array['MORE_VARS'] .= _("File Variables") . "<br />";
    $template_array['MORE_VARS'] .= $GLOBALS['core']->formSelect("DBUG_cookie", array(0=>"Off", 1=>"On"), $this->dumpCookie, NULL, 1);
    $template_array['MORE_VARS'] .= _("Cookie Variables") . "<br />";
    $template_array['MORE_VARS'] .= $GLOBALS['core']->formSelect("DBUG_server", array(0=>"Off", 1=>"On"), $this->dumpServer, NULL, 1);
    $template_array['MORE_VARS'] .= _("Server Variables");

    $template_array['SESSION_CORE_OPT'] = "<h5>" . _("Session and Core Options") . "</h5>";
    $template_array['SESSION_CORE_OPT'] .= $GLOBALS['core']->formSelect("DBUG_before", array(0=>"Off", 1=>"On"), $this->beforeExecution, NULL, 1);
    $template_array['SESSION_CORE_OPT'] .= _("Display Before Execution") . "<br />";
    $template_array['SESSION_CORE_OPT'] .= $GLOBALS['core']->formSelect("DBUG_after", array(0=>"Off", 1=>"On"), $this->afterExecution, NULL, 1);
    $template_array['SESSION_CORE_OPT'] .= _("Display After Execution") . "<br />";
    $template_array['SESSION_CORE_OPT'] .= $GLOBALS['core']->formSelect("DBUG_core", array(0=>"Off", 1=>"On"), $this->dumpCore, NULL, 1);
    $template_array['SESSION_CORE_OPT'] .= _("Dump Core") . "<br />";
 
    $objects = array();
    $arrays = array();
    $others = array();

    if(is_array($_SESSION)) {
      reset($_SESSION);
      foreach($_SESSION as $var => $value) {
	if(is_object($value)) {
	  $objects[] = $var;
	} else if(is_array($value)) {
	  $arrays[] = $var;
	} else {
	  $others[] = $var;
	}
      }
    }
 
    $template_array['SESSION_OBJECTS'] = _("Session Objects") . "<br />";
    $template_array['SESSION_OBJECTS'] .= $GLOBALS['core']->formMultipleSelect("DBUG_SES_objects", $objects, $this->sessionObjects, 1);

    $template_array['SESSION_ARRAYS'] = _("Session Arrays") . "<br />";
    $template_array['SESSION_ARRAYS'] .= $GLOBALS['core']->formMultipleSelect("DBUG_SES_arrays", $arrays, $this->sessionArrays, 1);

    $template_array['SESSION_OTHERS'] = _("Other Sessions") . "<br />";
    $template_array['SESSION_OTHERS'] .= $GLOBALS['core']->formMultipleSelect("DBUG_SES_others", $others, $this->sessionOthers, 1);
      
    $template_array['SUBMIT_RESET'] = $GLOBALS['core']->formSubmit(_("Save")) . "&#160;&#160;";
    $template_array['SUBMIT_RESET'] .= "<input type=\"reset\" value=\"". _("Reset Form") . "\">";
    $elements[0] .= $GLOBALS['core']->processTemplate($template_array, "debug", "settings.tpl");
    $content = $GLOBALS['core']->makeForm("DBUG_settings", "index.php", $elements, "post", NULL, NULL);
    $_SESSION['OBJ_layout']->popbox($title, $content, NULL, "CNT_debug");
  } // END FUNC adminSettings


  /**
   * saveSettings
   *
   * saves the current debugger settings
   */
  function saveSettings() {
    $this->setActive($_POST['DBUG_active']);
    $this->setShowTimer($_POST['DBUG_time']);
    $this->setBeforeExecution($_POST['DBUG_before']);
    $this->setAfterExecution($_POST['DBUG_after']);
    $this->setDumpPost($_POST['DBUG_post']);
    $this->setDumpGet($_POST['DBUG_get']);
    $this->setDumpRequest($_POST['DBUG_request']);
    $this->setDumpFile($_POST['DBUG_file']);
    $this->setDumpCookie($_POST['DBUG_cookie']);
    $this->setDumpServer($_POST['DBUG_server']);
    $this->setDumpCore($_POST['DBUG_core']);
    $this->setShowBlock($_POST['DBUG_block']);

    if (isset($_POST['DBUG_SES_objects']))
      $this->sessionObjects = $_POST['DBUG_SES_objects'];

    if (isset($_POST['DBUG_SES_objects']))
      $this->sessionArrays = $_POST['DBUG_SES_arrays'];

    if (isset($_POST['DBUG_SES_others']))
      $this->sessionOthers = $_POST['DBUG_SES_others'];
  }


  /**
   * testObject
   *
   * Outputs variables set in an object
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
   * @param object $objVar object to display
   * @param bool $displayTags whether or not to show html tags
   * @return string table of object variables
   */
  function testObject($objVar, $displayTags=1) {
    if(is_object($objVar)) {
      $objectInfo = (get_object_vars($objVar));
      return PHPWS_Debug::testArray($objectInfo, $displayTags);
    }
    return "PHPWS_Debug: testObject received a/an " . gettype($objVar) . " variable, not an object<br />";
  } // END FUNC testObject


  /**
   * testArray
   *
   * Returns a table displaying the contents of an array
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
   * @param array $arrayVar array to be tested
   * @param bool $displayTags whether or not to show html tags
   * @return string table with contents of array 
   */
  function testArray($arrayVar, $displayTags=1) {
    if(is_array($arrayVar)) {
      if(count($arrayVar)) {
        $info = "<table cellpadding=\"3\" border=\"1\">\n";
	$info .= "<tr>\n";
	$info .= "<td><b>KEY</b></td>\n";
	$info .= "<td><b>VALUE</b></td>\n";

        foreach($arrayVar as $key => $value) {
          if(is_array($value)) {
	    $value = PHPWS_Debug::testArray($value, $displayTags);
          } else if(is_object($value)) {
	    $value = PHPWS_Debug::testObject($value, $displayTags);
	  } else if($displayTags && is_string($value)) {
	    $value = htmlspecialchars($value);
	  } else if($value !== NULL) {
	    ob_start();
	    var_dump($value);
	    $value = ob_get_contents();
	    ob_end_clean();
	  } else {
	    $value = "NULL";
	  }

          $info .= "<tr>\n"; 
	  $info .= "<td valign=\"top\"><b>" . $key . "</b></td>\n";
	  $info .= "<td>" . $value . "</td>\n";
	  $info .= "</tr>\n";
        }
        $info .= "</table>\n";
        return $info;
      } else {
        return "Array contained no values.";
      }
    } else {
      return "PHPWS_Debug: testArray received a/an " . gettype($arrayVar) . " variable, not an array.<br />";
    }
  } // END FUNC testArray

  function displayDebugInfo($beforeExecution) {
    if($beforeExecution) {
      if($this->getDumpPost()) {
	echo "<font size=\"+1\"><b>POST Array</b></font><br />";
	echo $this->testArray($_POST);
	echo "<br /><br />";
      }
      
      if($this->getDumpGet()) {
	echo "<font size=\"+1\"><b>GET Array</b></font><br />";
	echo $this->testArray($_GET);
	echo "<br /><br />";
      }
      
      if($this->getDumpRequest()) {
	echo "<font size=\"+1\"><b>REQUEST Array</b></font><br />";
	echo $this->testArray($_REQUEST);
	echo "<br /><br />";
      }
      
      if($this->getDumpFile()) {
	echo "<font size=\"+1\"><b>FILES Array</b></font><br />";
	echo $this->testArray($_FILES);
	echo "<br /><br />";
      }
      
      if($this->getDumpCookie()) {
	echo "<font size=\"+1\"><b>COOKIE Array</b></font><br />";
	echo $this->testArray($_COOKIE);
	echo "<br /><br />";
      }
      
      if($this->getDumpServer()) {
	echo "<font size=\"+1\"><b>SERVER Array</b></font><br />";
	echo $this->testArray($_SERVER);
	echo "<br /><br />";
      }
    } else {
      if($this->getDumpCore()) {
	echo "<font size=\"+1\"><b>PHPWS_Core</b></font><br />";
	echo $this->testObject($GLOBALS['core']);
	echo "<br /><br />";
      }
      
      if(count($this->sessionObjects) > 0) {
	foreach($this->sessionObjects as $value) {
	  echo "<font size=\"+1\"><b>" . $value . " Session Object</b></font><br />";
	  echo $this->testObject($_SESSION[$value]);
	  echo "<br /><br />";
	}
      }
      
      if(count($this->sessionArrays) > 0) {
	foreach($this->sessionArrays as $value) {
	  echo "<font size=\"+1\"><b>" . $value . " Session Array</b></font><br />";
	  echo $this->testArray($_SESSION[$value]);
	  echo "<br /><br />";
	}
      }
      
      if(count($this->sessionOthers) > 0) {
	foreach($this->sessionOthers as $value) {
	  echo "<font size=\"+1\"><b>" . $value . " Session Variable</b></font><br />";
	  echo var_dump($_SESSION[$value]);
	  echo "<br /><br />";
	}
      }
    }
  } // END FUNC displayDebugInfo
} // END CLASS PHPWS_Debug

?>
