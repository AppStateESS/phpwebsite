<?php

/**
 * debugger for the phpWebSite core
 *
 * @author Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @version $Id$
 * @package Core
 */

class PHPWS_Debug {

  function test($value){
    switch(gettype($value)){
    case "object":
      return PHPWS_Debug::testObject($value);
      break;
      
    case "array":
      return PHPWS_Debug::testArray($value);
      break;

    default:
      return $value;
    }
  }

  function request(){
    return PHPWS_Debug::test($_REQUEST);
  }

  function post(){
    return PHPWS_Debug::test($_POST);
  }

  function get(){
    return PHPWS_Debug::test($_GET);
  }
  
  function server(){
    return PHPWS_Debug::test($_SERVER);
  }

  function env(){
    return PHPWS_Debug::test($_ENV);
  }

  function cookie(){
    return PHPWS_Debug::test($_COOKIE);
  }

  function files(){
    return PHPWS_Debug::test($_FILES);
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
      $info[] = "<b>Class Name :</b> " . get_class($value);
      $info[] = PHPWS_Debug::testArray($objectInfo, $displayTags);
      return implode("<br />", $info);
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

} // END CLASS PHPWS_Debug

?>
