<?php

//require_once PHPWS_Core::getConfigFile("core", "formConfig.php");
require_once "config/core/formConfig.php";
/**
 * Creates HTML form elements and/or an entire form
 *
 * This class is stand alone. You must construct an object within your
 * function to get it to work:
 * $form = new PHPWS_Form;
 *
 * This class allows you to easily create a form and then fetch elements of
 * that form. It also allows you to export a template of the form that you
 * can use within phpWebSite.
 *
 * Once the object is created just start adding elements to it with the add function.
 * Example:
 * $form->add("testarea");
 *
 * This would create a form element named 'testarea'. You can set the type and value via
 * the setType and setValue functions or you can just include them in the add.
 * Example:
 * $form->add("testarea", "textarea", "something something");
 *
 * For many form elements, that may be all you need.
 *
 * @version $Id$
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @author Don Seiler <don at seiler dot us>
 * @package Core
 *
 */
class PHPWS_Form {
  /**
   * Optional name of form
   * @var string
   * @access private
   */
  var $_formName = NULL;

  
  /**
   * Array of form elements
   * @var    array
   * @access private
   */
  var $_elements = NULL;

  /**
   * Directory destination of submitted form.
   * Note: if none is provided, getTemplate will try to use the core
   * home_http directory
   * 
   * @var    string
   * @access private
   */
  var $_action = NULL;

  /**
   * How the form is sent.
   * @var    string
   * @access private
   */
  var $_method = "post";

  /**
   * Tells whether to multipart encode the form
   * @var    string
   * @access private
   */
  var $_encode = NULL;

  /**
   * Holds an extra template to merge with the final
   * @var    array
   * @access private
   */
  var $_template = NULL;

  var $types = array();

  var $tagReplace = array();

  /**
   * Constructor for class
   */
  function PHPWS_Form($formName=NULL){
    $this->_formName = $formName;
    $this->reset();
  }

  function reset(){
    $this->_elements = array();
    $this->_action   = NULL;
    $this->_method   = "method=\"post\"";
    $this->_encode   = NULL;
  }

  function extractKeyValue($name){
    $value['index'] = preg_replace("/\w+\[(\w*)\]$/", "\\1", $name);
    $value['name'] = preg_replace("/\[\w*\]$/", "", $name);
    return $value;
  }

  /**
   * Adds a form element to the class
   *
   * The type and value parameters are optional, though it is a timesaver.
   * See setType for the form types.
   * See setValue for value information.
   *
   * @author             Matthew McNaney <matt at tux dot appstate dot edu>
   * @param string name  The name of the form element
   * @param string type  The type of form element (text, check, radio, etc)
   * @param mixed  value The default value of the form element
   */
  function add($name, $type=NULL, $value=NULL){
    if (preg_match("/[^\[\]a-z0-9_]/i", $name))
      return PHPWS_Error::get(PHPWS_FORM_BAD_NAME, "core", "PHPWS_Form::add", array($name));

    $result = PHPWS_Form::createElement($name, $type, $value);

    if (preg_match("/\[\w*\]$/Ui", $name)){
      $keyValue = PHPWS_Form::extractKeyValue($name);
      extract($keyValue);
      $this->_elements[$name][$index] = $result;
    }
    elseif (is_array($result)){
      foreach ($result as $element){
	if ($type != "radio")
	  $element->isArray = TRUE;
	$this->_elements[$name][] = $element;
      }
    }
    else {
      if (isset($this->_elements[$name])){
	$this->_elements[$name][0]->isArray = TRUE;
	$result->isArray = TRUE;
      }
      $this->_elements[$name][] = $result;
    }

    $this->types[$name] = $type;
    return TRUE;
  }

  /**
   * Removes a form element from the class
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   */
  function drop($name){
    unset($this->_elements[$name]);
  }

  /**
   * Allows you to enter extra information to an element.
   *
   * This is useful for style components, javascript, etc.
   *
   * @author            Matthew McNaney <matt at tux dot appstate dot edu>
   * @param string name Name of element to set the type
   * @param string type Extra text to add to element
   */
  function setExtra($name, $extra){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setExtra", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setExtra($extra);
      if (PEAR::isError($result))
	return $result;
    }

    return TRUE;
  }

  /**
   * Lets you enter a width style to a text field or area
   *
   * Instead of setting a column number, you may prefer applying
   * a style width. The width will size itself depending on side of
   * or its contained (i.e. a table cell)
   *
   * @author             Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name  Name of element to set the type
   * @param string width Percentage of width wanted on element
   */
  function setWidth($name, $width){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setWidth", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setWidth($width);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  /**
   * Lets you enter a height style to a text field or area
   *
   * Instead of setting a row number for a textarea, you may prefer
   * applying a style width. The width will size itself depending on
   * side of or its contained (i.e. a table cell).
   *
   * Note: You can set the height of a text field, but it will look
   * strange and it has no real functionality.
   *
   * @author              Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name   Name of element to set the type
   * @param string height Percentage of height wanted on element
   */
  function setHeight($name, $height){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setHeight", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setHeight($height);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  /**
   * Allows you to set the numbers of rows for a textarea
   *
   * Rows must be more than 1 and less than 100
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name Name of element to set the rows
   * @param string rows Number rows to use in a textarea
   */
  function setRows($name, $rows){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setRows", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setRows($rows);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  /**
   * Allows you to set the numbers of columns for a textarea
   *
   * Columns must be more than 10 and less than 500
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name Name of element to set the rows
   * @param string rows Number columns to use in a textarea
   */
  function setCols($name, $cols){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setCols", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setCols($cols);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  /**
   * Sets the tabindex for a form element
   *
   * Tab indexing allows use of the tab key to move among
   * form elements (like Windows). Just give the name of the element
   * and what order you want it in. PHPWS_Form does not check your settings
   * so be careful you don't use the same number more than once.
   *
   * @author               Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param  string  name  Name of element to set the type
   * @param  integer order Numeric order of tab queue
   */
  function setTab($name, $order){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setTab", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setTab($order);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  /**
   * Sets the number of characters for text boxes, number of rows
   * for select boxes
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name Name of element to set the type
   * @param string size Size to make the element
   */
  function setSize($name, $size){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setSize", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setSize($size);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  /**
   * Changed the template tag name for the form element
   *
   * Should be used if you do not want to use the name of the post
   * variable for your template. A good function to use to convert
   * old templates. 
   *
   * @author                Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name     Name of element to set the type
   * @param string template Name of template tag to print for this element
   */
  function setTag($name, $tag){
    if (preg_match("/\[\w*\]$/Ui", $name)){
      $keyValue = PHPWS_Form::extractKeyValue($name);
      extract($keyValue);
      $this->_elements[$name][$index]->setTag($tag);
    } else {
      foreach ($this->_elements[$name] as $key=>$element){
	$result = $this->_elements[$name][$key]->setTag($tag);
	if (PEAR::isError($result))
	  return $result;
      }
    }
  }

  /**
   * Reindexes the value. Sets the value of the array equal to the index.
   *
   * Use this function after setting the match.
   *
   * Example: 
   * $list = array('apple', 'orange', 'peach', 'banana');
   * $form = new PHPWS_Form;
   * $form->add("testing", "multiple", $list);
   * $form->reindexValue('testing');
   * $form->setMatch('testing', array('orange', 'banana'));
   *
   * This would change the index array to array('apple'=>'apple', 'orange'=>'orange', etc.
   *
   * @author                    Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name         Name of element to set the type
   */
  function reindexValue($name){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::reindexValue", array($name));

    if ($this->types[$name] != "multiple" && $this->types[$name] != "select")
      return PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, "core", "PHPWS_Form::reindexValue");

    foreach ($this->_elements[$name] as $key=>$element){
      $oldValueArray = $element->value;

      foreach ($oldValueArray as $value)
	$newValueArray[(string)$value] = $value;

      $this->_elements[$name][$key]->setValue($newValueArray);
    }
  }

  /**
   * Sets the match value of an element
   *
   * Used when you want a radio button, checkbox, or drop down box
   * to be defaultly selected.
   * In most cases this is just a string, however there is a special circumstance
   * for multiple select forms. The match must be an array. Even if there is just one
   * match, for it to register, it must come as an array.
   *
   * Also, match will ONLY match to the VALUE of a select box unless you set
   * optionMatch to TRUE.
   *
   * @author                    Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name         Name of element to set the type
   * @param string match        Value to match against the element's value
   * @param boolean optionMatch If TRUE, then a select box will try to match
   *                            the value to the option not the value
   */
  function setMatch($name, $match, $optionMatch=FALSE){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setMatch", array($name));


    if ($this->types[$name] == "multiple" && !is_array($match))
      return PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, "core", "PHPWS_Form::reindexValue");

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setMatch($match);
      if (PEAR::isError($result))
	return $result;
    }
  }

  /**
   * Sets the max text size for a text, password, file element
   *
   * @author Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param string  name Name of element to set the maxsize
   * @param integer maxsize The max number of characters allowed in the element's field
   */
  function setMaxSize($name, $maxsize){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setMaxSize", array($name));

    foreach ($this->_elements[$name] as $key=>$element){
      $result = $this->_elements[$name][$key]->setMaxSize($maxsize);
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  function mergeTemplate($template){
    if (!is_array($template))
      return;

    if (!isset($this->_template))
      $this->_template = $template;
    else
      $this->_template = array_merge($this->_template, $template);
  }

  /**
   * Indicates whether an element exists
   *
   * @author         Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param  string  name Name to check if exists
   * @return boolean TRUE if the element exists, FALSE otherwise
   */
  function testName($name){
    return isset($this->_elements[$name]);
  }


  function &createElement($name, $type, $value){
    switch ($type){
    case "text":
    case "textfield":
      return new Form_TextField($name, $value);
      break;
      
    case "textarea":
      return new Form_TextArea($name, $value);
      break;

    case "submit":
      return new Form_Submit($name, $value);
      break;

    case "password":
      return new Form_Password($name, $value);
      break;

    case "file":
      $this->_encode = " enctype=\"multipart/form-data\"";
      return new Form_File($name);
      break;
      
    case "select":
    case "dropbox":
      return new Form_Select($name, $value);
      break;

    case "multiple":
      return new Form_Multiple($name, $value);
      break;

    case "radio":
    case "radiobutton":
      if (is_array($value)){
	foreach ($value as $sub)
	  $radio[] = new Form_RadioButton($name, $sub);
	return $radio;
      } else
	return new Form_RadioButton($name, $value);
      break;

    case "check":
    case "checkbox":
      if (is_array($value)){
	foreach ($value as $sub)
	  $check[] = new Form_Checkbox($name, $sub);
	return $check;
      } else
	return new Form_Checkbox($name, $value);
      break;

    case "hidden":
      return new Form_Hidden($name, $value);
      break;
    }

  }

  /**
   * sets the 'action' or destination directory for a form
   *
   * If you are using this class in phpWebSite, the default directory will be
   * phpwebsite's home address/index.php
   *
   * If you need to send the form elsewhere, set the directory here.
   *
   * @author                   Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param  string  directory Directory that a form will post to
   */
  function setAction($directory){
    $this->_action = $directory;
  }

  function get($name){
    if (!isset($this->_elements[$name]))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::get", array($name));

    if (count($this->_elements[$name]) > 1)
      $multiple = TRUE;

    foreach ($this->_elements[$name] as $element)
      $content[] = $element->get();
    
    return implode("\n", $content);
  }

  function grab($name){
    if (count($this->_elements[$name]) > 1)
      return $this->_elements[$name];
    else
      return $this->_elements[$name][0];
  }

  function replace($name, $elements){
    $this->_elements[$name] = array();

    if (is_array($elements))
      $this->_elements[$name] = $elements;
    else
      $this->_elements[$name][] = $elements;
  }

  /**
   * Returns all the elements of a form in a template array
   *
   * This is the fruit of your labor. After calling this you will get an associative array
   * of all you form elements. The keys of the template are the capitalized names of the elements.
   * The template also includes START_FORM and END_FORM tags to make creating the form easier.
   * Hidden variables will AUTOMATICALLY be added to the START_FORM tag. If helperTags == FALSE
   * they will be placed in a tag named HIDDEN.
   * It will also create a DEFAULT_SUBMIT button.
   * 
   * Hidden variables will be added on to START_FORM. They will NOT have their own template tag.
   *
   * @author                     Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param  boolean phpws       If TRUE and the action is missing, phpWebSite will attempt to use your directory settings instead
   * @param  boolean helperTags  If TRUE START and END_FORM tags will be created, otherwise they will not
   * @param  array   template    If a current template is supplied, form will add to it.
   * @return array   template    Array of completed form
   */
  function getTemplate($phpws=TRUE, $helperTags=TRUE, $template=NULL){
    translate("core");

    if (count($this->_elements) < 1)
      return PHPWS_Error::get(PHPWS_FORM_NO_ELEMENTS, "core", "PHPWS_Form::getTemplate");

    if (!is_null($template) && !is_array($template))
      return PHPWS_Error::get(PHPWS_FORM_NO_TEMPLATE, "core", "PHPWS_Form::getTemplate");


    if ($helperTags){
      $template["START_FORM"] = $this->getStart();
      $template["DEFAULT_SUBMIT"] = "<input type=\"submit\" value=\"" . _("Submit") ."\">\n";
    }

    foreach ($this->_elements as $elementName=>$element){
      $multiple = FALSE;
      $count = 1;

      if (count($element) > 1)
	$multiple = TRUE;

      foreach ($element as $subElement){
	if ($this->types[$elementName] == "hidden"){
	  ($helperTags == TRUE) ? $template["START_FORM"] .= $subElement->get() ."\n" : $template["HIDDEN"] .= $subElement->get() . "\n";
	  continue;
	}

	$tagName = $subElement->getTag();

	if ($multiple)
	  $template[$tagName . "_$count"] = $subElement->get();
	else	
	  $template[$tagName] = $subElement->get();

	$count++;
      }
    }      

    if (isset($this->_action))
      $template["END_FORM"]   = "</form>\n";

    if (isset($this->_template))
      $template = array_merge($this->_template, $template);

    if ($phpws == TRUE)
      return $template;
    else
      return implode("\n", $template);
  }

  function getMerge(){
    $form = $this->getTemplate();
    return implode("\n", $form);
  }

  function getStart(){
    if (!isset($this->_action))
      $this->_action = "index.php";

    if (isset($this->_formName))
      $formName = "name=\"" . $this->_formName . "\" ";
    else
      $formName = NULL;

    if (isset($this->_action))
      return "<form " . $formName . "action=\"" . $this->_action . "\" " . $this->_method . $this->_encode . ">\n";
    
  }

  function formTextField($name, $value, $size=30, $maxsize=255, $label=NULL){
    return CrutchForm::formTextField($name, $value, $size, $maxsize, $label);
  }

  function formTextArea ($name, $value=NULL, $rows=5, $cols=40, $label=NULL){
    return CrutchForm::formTextArea($name, $value, $rows, $cols, $label);
  }

  function formFile($name){
    return CrutchForm::formFile($name);
  }

  function formDate($date_name, $date_match=NULL, $yearStart=NULL, $yearEnd=NULL, $useBlanks=FALSE){
    return CrutchForm::formDate($date_name, $date_match, $yearStart, $yearEnd, $useBlanks);
  }

  function formRadio($name, $value, $match=NULL, $match_diff=NULL, $label=NULL) {
    return CrutchForm::formRadio($name, $value, $match, $match_diff, $label);
  }

  function formSubmit($value, $name=NULL, $class=NULL) {
    return CrutchForm::formSubmit($value, $name, $class);
  }

  function formHidden($name, $value=NULL) {
    return CrutchForm::formHidden($name, $value);
  }

  function makeForm($name, $action, $elements, $method="post", $breaks=FALSE, $file=FALSE) {
    return CrutchForm::makeForm($name, $action, $elements, $method="post", $breaks, $file);
  }


}// End of PHPWS_Form Class


class Form_TextField extends Form_Element{
  function get(){
    return "<input type=\"textfield\" "
      . $this->getName(TRUE) 
      . $this->getValue(TRUE) 
      . $this->getWidth(TRUE)
      . $this->getData() . " />";
  }

}

class Form_Submit extends Form_Element{
  function get(){
    return "<input type=\"submit\" "
      . $this->getName(TRUE) 
      . $this->getValue(TRUE) 
      . $this->getWidth(TRUE)
      . $this->getData() . " />";
  }

}

class Form_Hidden extends Form_Element {
  function get(){
    return "<input type=\"hidden\" " 
      . $this->getName(TRUE)
      . $this->getValue(TRUE)
      . "/>";
  }
}

class Form_File extends Form_Element {
  function get(){
    return "<input type=\"file\" "
      . $this->getName(TRUE) 
      . $this->getWidth(TRUE)
      . $this->getData()
      . " />";
  }
}

class Form_Password extends Form_Element {
  function Form_Password($name, $value=NULL){
    $this->setName($name);
    $this->setValue($value);
    $this->allowValue = FALSE;
  }

  function get(){
    return "<input type=\"password\" "
      . $this->getName(TRUE) 
      . $this->getValue(TRUE)
      . $this->getWidth(TRUE)
      . $this->getData()
      . " />";
  }
}

class Form_TextArea extends Form_Element{
  var $rows        = DFLT_ROWS;
  var $cols        = DFLT_COLS;
  var $height      = NULL;

  function setRows($rows){
    if (!is_numeric($rows) || $rows < 1 || $rows > 100)
      return PHPWS_Error::get(PHPWS_INVALID_VALUE, "core", "PHPWS_Form::setRows");

    $this->rows = $rows;
    return TRUE;
  }

  function getRows($formMode=FALSE){
    if ($formMode)
      return "rows=\"$this->rows\"";
    else
      return $this->rows;
  }

  function setCols($cols){
    if (!is_numeric($cols) || $cols < 1 || $cols > 100)
      return PHPWS_Error::get(PHPWS_INVALID_VALUE, "core", "PHPWS_Form::setCols");

    $this->cols = $cols;
    return TRUE;
  }

  function getCols($formMode=FALSE){
    if ($formMode)
      return "cols=\"$this->cols\"";
    else
      return $this->cols;
  }


  function get(){
    $value = $this->getValue();
    $value = htmlspecialchars($value, ENT_NOQUOTES);
    $value = str_replace("&#x0024;", "&", $value);
    $value = str_replace("&amp;#39;", "'", $value);
    
    if (ord(substr($value, 0, 1)) == 13)
      $value = "\n" . $value;

    if (isset($this->width))
      $style[] = "width : " . $this->width . "%";
    else
      $dimensions[] = $this->getCols(TRUE);

    if (isset($this->height))
      $style[] = "height : " . $this->height . "%";
    else
      $dimensions[] = $this->getRows(TRUE);

    if (isset($style))
      $dimensions[] = "style=\"" . implode("; ", $style) . "\"";

    return "<textarea "
      . $this->getName(TRUE) 
      . implode(" ", $dimensions) . " "
      . $this->getData()
      . ">$value</textarea>";
  }

}

class Form_Select extends Form_Element{
  var $match = NULL;

  function get(){
    $content[] = "<select " . $this->getName(TRUE) . $this->getData() . ">";
    foreach($this->value as $value=>$label){
      if ($this->isMatch($value))
	$content[] = "<option value=\"$value\" selected=\"selected\">$label</option>";
      else
	$content[] = "<option value=\"$value\">$label</option>";
    }
    $content[] = "</select>";

    return implode("\n", $content);
  }

  function setMatch($match){
    $this->match = $match;
  }

  function isMatch($match){
    if (!isset($this->match))
      return FALSE;

    if ($this->name == "ANN_anonymous")
    echo "testing " . $this->match . " to $match <br />";

    return ($this->match == $match) ? TRUE : FALSE;
  }

}

class Form_Multiple extends Form_Element{
  var $match = NULL;

  function get(){
    $content[] = "<select " . $this->getName(TRUE) . "multiple=\"multiple\" " 
      . $this->getData() . ">";
    foreach($this->value as $value=>$label){
      if ($this->isMatch($value))
	$content[] = "<option value=\"$value\" selected=\"selected\">$label</option>";
      else
	$content[] = "<option value=\"$value\">$label</option>";
    }
    $content[] = "</select>";

    return implode("\n", $content);
  }

  function setMatch($match){
    if (!is_array($match))
      $this->match[] = $match;
    else
      $this->match = $match;
  }

  function isMatch($match){
    if (!isset($this->match))
      return FALSE;

    return (in_array($match, $this->match)) ? TRUE : FALSE;
  }

}



class Form_CheckBox extends Form_Element{
  var $match = FALSE;

  function setMatch($match=TRUE){
    $this->match = $match;
  }

  function getMatch(){
    if ($this->match == $this->getValue())
      return "checked=\"checked\"";
    else
      return NULL;
  }

  function get(){
    return "<input type=\"checkbox\" " . $this->getName(TRUE) . " " 
      . $this->getValue(TRUE) . " " 
      . $this->getMatch() . " "
      . $this->getData()
      . " />";
  }

}


class Form_RadioButton extends Form_Element{
  var $match = FALSE;

  function setMatch($match=TRUE){
    $this->match = $match;
  }

  function getMatch(){
    if ($this->match == $this->getValue())
      return "checked=\"checked\"";
    else
      return NULL;
  }

  function get(){
    return "<input type=\"radio\" " . $this->getName(TRUE)
      . $this->getValue(TRUE)
      . $this->getMatch()
      . $this->getData()
      . " />";
  }

}


class Form_Element {
  var $name        = NULL;
  var $value       = NULL;
  var $css_class   = NULL;
  var $style       = NULL;
  var $tab         = NULL;
  var $width       = NULL;
  var $allowValue  = TRUE;
  var $isArray     = FALSE;
  var $tag         = NULL;
  
  function Form_Element($name, $value=NULL){
    $this->setName($name);
    if (isset($value))
      $this->setValue($value);
  }

  function setName($name){
    $this->name = preg_replace("/[^\[\]\w]/", "", $name);
  }

  function getName($formMode=FALSE){
    if ($this->isArray)
      $name = $this->name . "[]";
    else
      $name = $this->name;

    if ($formMode)
      return "name=\"$name\" ";
    else
      return $name;
  }

  function setValue($value){
    $this->value = $value;
  }

  function getValue($formMode=FALSE){
    if ($formMode){
      if ($this->allowValue)
	return "value=\"" . $this->value . "\" ";
      else
	return NULL;
    }
    else
      return $this->value;
  }

  function setClass($css_class){
    $this->css_class = $css_class;
  }

  function getClass($formMode){
    if ($formMode)
      return (isset($this->css_class)) ? "class=\"" . $this->css_class . "\"" : NULL;
    else
      return $this->css_class;
  }

  function setStyle($style){
    $this->style = $style;
  }

  function getStyle($formMode){
    if ($formMode)
      return (isset($this->style)) ? "style=\"" . $this->style . "\"" : NULL;
    else
      return $this->style;
  }

  function setTab($order){
    $this->tab = (int)$order;
  }

  function getTab($formMode=FALSE){
    if ($formMode)
      return "tabindex=\"$this->tab\"";
    else
      return $this->tab;
  }

  function setExtra($extra){
    $this->extra = $extra;
  }

  function getExtra(){
    return $this->extra;
  }

  function setSize($size){
    $this->size = (int)$size;
  }

  function getSize($formMode=FALSE){
    if ($formMode)
      return "size=\"" . $this->size . "\" ";
    else
      return $this->size;
  }

  function setMaxSize($maxsize){
    $this->maxsize = (int)$maxsize;
  }

  function getMaxSize($formMode=FALSE){
    if ($formMode){
      if (isset($this->maxsize))
	return "maxlength=\"" . $this->maxsize . "\"";
      else
	return NULL;
    }
    else
      return $this->maxsize;
  }

  function setWidth($width){
    $this->width = (int)$width;
  }

  function getWidth($formMode=FALSE){
    if ($formMode){
      if (isset($this->width))
	return "style=\"width : " . $this->width . "%\" ";
      else
	return NULL;
    }
    else
      return $this->width;
  }

  function setHeight($height){
    $this->height = (int)$height;
  }

  function getData(){
    if (isset($this->style))
      $extra[] = $this->getStyle(TRUE);

    if (isset($this->class))
      $extra[] = $this->getClass(TRUE);

    if (isset($this->extra))
      $extra[] = $this->getExtra();

    if (isset($this->size))
      $extra[] = $this->getSize(TRUE);

    if (isset($this->maxsize))
      $extra[] = $this->getMaxSize(TRUE);

    if (isset($this->tab))
      $extra[] = $this->getTab(TRUE);

    if (isset($extra))
      return implode("", $extra);
    else
      return NULL;
  }

  function setTag($tag){
    $this->tag =$tag;
  }

  function getTag(){
    if (isset($this->tag))
      return strtoupper($this->tag);
    else {
      $name = str_replace("][", "_", $this->name);
      $name = str_replace("[", "_", $name);
      $name = str_replace("]", "_", $name);
      
      return strtoupper($name);
    }
  }

}

?>
