<?php

require_once PHPWS_Core::getConfigFile("core", "formConfig.php");
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
  var $_method = NULL;

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

    $this->_elements[$name] = new PHPWS_Element;
    $this->_elements[$name]->name = $name;

    if ($type)
      $this->setType($name, $type);

    $this->setValue($name, $value);
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
   * Sets form element type
   *
   * The types are restricted are:
   * radio
   * hidden
   * file
   * text or textfield
   * password
   * textarea
   * checkbox
   * select or dropbox
   * multiple
   * submit
   * button
   *
   * Select and dropbox are for a drop-down selection box and multiple is
   * for a multiple selection drop box.
   *
   * @author            Matthew McNaney <matt at tux dot appstate dot edu>
   * @param string name Name of element to set the type
   * @param string type Type of form to set to the element
   */
  function setType($name, $type){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setType", array($name));

    if ($type == "file")
      $this->_encode = " enctype=\"multipart/form-data\"";

    $result = $this->_elements[$name]->_setType($type);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setExtra($extra);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setWidth($width);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setHeight($height);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setRows($rows);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setCols($cols);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setTab($order);
    return (PEAR::isError($result) ? $result : TRUE);
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

    $result = $this->_elements[$name]->_setSize($size);
    return (PEAR::isError($result) ? $result : TRUE);
  }

  /**
   * Sets the display of the password
   *
   * If you set an element showPass to TRUE, then the symbols
   * will appear in the password field. The password will NOT
   * appear in the source BUT the number of characters will be indicated.
   * Be careful turning this on.
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name Name of element to set the type
   * @param string show Set to TRUE to make the field have characters, FALSE otherwise.
   */
  function showPass($name, $show){
    if ($show)
      $this->_elements[$name]->showPass=TRUE;
    else
      $this->_elements[$name]->showPass=FALSE;
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
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setTag", array($name));

    $result = $this->_elements[$name]->_setTag($tag);
    return (PEAR::isError($result) ? $result : TRUE);
  }

  /**
   * Sets the value of an element
   *
   * This does different things depending on the element.
   * For text and textarea, the value will appear in the field.
   *
   * For a radio button, these are the options. So you would send an array of 
   * different choices:
   * $form->setValue("myChoice", array("yes", "no");
   * This would create two radio buttons (myChoice_1, myChoice_2) with values "yes" and "no"
   * respectively.
   * 
   * You will also send an array to select and multiple. The key of the array cooresponds to
   * the value of the option and the value of the array would be the text in the drop down box.
   *
   * If you want to send a list, and want what appears to be the value, you will need to copy the value
   * to the index or run reindexValue afterwards.
   * Example:
   * 
   * $form->setValue("mySelect", array("red", "blue", "green");
   * This would create
   * <input value="0">red</input>
   * <input value="1">blue</input>
   * etc.
   * So you would send array("red"=>"red", "blue"=>"blue", "green"=>"green") instead or
   * $form->reindexValue("mySelect");
   *
   * @author             Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string name  Name of element to set the value
   * @param mixed  value Value to set to the element
   */
  function setValue($name, $value){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::setValue", array($name));

    if (!empty($value))
      $result = $this->_elements[$name]->_setValue($value);
    elseif ($this->_elements[$name]->getType() == "checkbox")
      $result = $this->_elements[$name]->_setValue(1);

    return (isset($result) && PEAR::isError($result) ? $result : TRUE);
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

    if (($this->_elements[$name]->type != "multiple" && $this->_elements[$name]->type != "select")
	|| !is_array($this->_elements[$name]->value))
      return PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, "core", "PHPWS_Form::reindexValue");

    $oldValueArray = $this->_elements[$name]->value;
    foreach ($oldValueArray as $value)
      $newValueArray[(string)$value] = $value;

    $this->_elements[$name]->value = $newValueArray;
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


    if ($this->_elements[$name]->getType() == "multiple" && !is_array($match)){
      $this->error = "The match for <b>$name</b> must be an array to match a multiple form";
      return FALSE;
    }

    $this->_elements[$name]->_setMatch($match);
    $this->_elements[$name]->optionMatch = $optionMatch;
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


    $this->_elements[$name]->_setMaxSize($maxsize);
  }


  /**
   * Allows you to change the name of an element
   *
   * @author Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param string  oldName Name of element to change
   * @param string  newName Name to change the oldName to
   */
  function changeName($oldName, $newName){
    if (isset($this->elements[$newName]))
      return PHPWS_Error::get(PHPWS_FORM_NAME_IN_USE, "core", "PHPWS_Form::changeName");

    $this->_elements[$newName] = $this->elements[$oldName];
    $this->drop($oldName);
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

  /**
   * Indicates whether an element's type is set
   *
   * @author         Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param  string  name Name to check if type exists
   * @return boolean TRUE if the element's type exists, FALSE otherwise
   */
  function testType($name){
    return isset($this->_elements[$name]);
  }

  /**
   * retrieves a HTML form element
   *
   * This returns just the element asked for. It is not in a template format.
   * If there is problem, it will be registered to the error variable.
   *
   * @author              Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param  string  name Name to retrieve
   * @return string       HTML form element
   */
  function get($name){
    if (!$this->testName($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, "core", "PHPWS_Form::get", array($name));

    if(!$this->testType($name))
      return PHPWS_Error::get(PHPWS_FORM_MISSING_TYPE, "core", "PHPWS_Form::get", array($name));

    $element = $this->_elements[$name]->_getInput();

    if (PEAR::isError($element))
      return $element;

    if (is_array($element)){
      foreach ($element as $data)
	$formElements[] = $data;
      return implode("", $formElements);
    } else
      return $element;
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

  function mergeTemplate($template){
    if (!is_array($template))
      return;

    if (!isset($this->_template))
      $this->_template = $template;
    else
      $this->_template = array_merge($this->_template, $template);
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
      $count = 0;
      $formElement = $element->_getInput();

      if (PEAR::isError($formElement))
	return $formElement;

      if (!is_null($element->tag))
	$elementName = $element->tag;

      if ($element->type == "hidden"){
	($helperTags == TRUE) ? $template["START_FORM"] .= $formElement : $template["HIDDEN"] .= $formElement;
	continue;
      }

      if (is_array($formElement)){
	foreach ($formElement as $data){
	  $count++;
	  $elementName = str_replace("][", "_", $elementName);
	  $elementName = str_replace("[", "_", $elementName);
	  $elementName = str_replace("]", "_", $elementName);
	  $template[strtoupper($elementName) . "_$count"] = $data;
	}
      } else
	$template[strtoupper($elementName)] = $formElement;
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

  /**
   * Creates a templated file box for images
   *
   * This function will return a image template for phpWebSite.
   * The template includes the image file box and a drop down box of current images.
   * It is up to the module developer to catch the data.
   *
   * If 'name' is not supplied, the form will use 'IMAGE'.
   * If the image_directory is not supplied, the function will try and use the core
   * value. If the image directory is not correct or not writable, FALSE is returned.
   *
   * The file box will be named NEW_ + the name (ie NEW_myModImage, or by default NEW_IMAGE).
   * The select box will be named CURRENT_ + the name.
   *
   * If you have a current image you want to match in the select box, send it to the match
   * parameter and it will be highlighted in the select box.
   *
   * If a user wants to dump an image from an element, they can choose <None>. Make sure you
   * are checking for that.
   *
   * There is also a REMOVE button sent back to the template. Whether you allow them to delete
   * images is up to you.
   * 
   * @author                         Matthew McNaney<matt@NOSPAM.tux.appstate.edu>
   * @param  string name             Name of image variable
   * @param  string image_directory  Directory path of current images
   * @param  string match            Name of the image to select by default
   * @return array                   Template of image form
   */
  function imageForm($name=NULL, $image_directory=NULL, $match=NULL){
    if (is_null($image_directory))
      $image_directory = $GLOBALS["core"]->home_dir . "images/" . $GLOBALS["core"]->current_mod;

    if (is_null($name))
      $name = "IMAGE";

    if (!is_dir($image_directory) || !is_writable($image_directory))
      return PHPWS_Error::get(PHPWS_DIR_NOT_WRITABLE, "core", "PHPWS_Form::imageForm");

    if ($current_images = $GLOBALS["core"]->readDirectory($image_directory, FALSE, TRUE)){
      foreach ($current_images as $imageName){
	if (preg_match("/\.+(jpg|png|gif)$/i", $imageName))
	  $imageList[$imageName] = $imageName;
      }
	  
      if ($imageList)
	$current_images = array("none"=>"&lt;" . _("None")."&gt;") + $imageList;
      else
	$current_images = array("none"=>"&lt;" . _("None")."&gt;");
    }

    $this->add("NEW_".$name, "file");
    $this->setSize("NEW_".$name, 30);
    if ($current_images){
      $this->add("CURRENT_".$name, "select", $current_images);
      $this->setMatch("CURRENT_".$name, $match);
      $this->add("REMOVE_".$name, "submit", _("Remove Image"));
    }
    return TRUE;
  }

  /**
   * Saves an image uploaded via a form
   *
   * saveImage uses the _FILES array to save an uploaded image file to your
   * server. Here is an example of it being used in a function.
   * The name of the file is sample.jpg with a 534 width and 300 height.
   *
   * if ($_FILES["NEW_IMAGE"]["name"]){
   *   $image = PHPWS_Form::saveImage("NEW_IMAGE", $image_directory, 1024, 768, 100000);
   *   if (is_array($image))
   *	$this->image = $image;
   *   else {
   *	$GLOBALS["CNT_Module_Var"]["content"] .= $image;
   *   }
   * }
   *
   * In this example, it is using the imageForm standard of NEW_IMAGE as the
   * post variable name. But it could obviously be whatever you name it. Since
   * it exists, I send it to the saveImage method. I tell the function the name
   * of the post variable, what directory to save it to, the maximum width and height,
   * and finally the maximum file size. These are set by default to 640x480x80000.
   *
   * I then catch the result. If the result is an array, it was successful. The array
   * looks like so:
   * $image['name'] = "sample.jpg";
   * $image['width'] = 534;
   * $image[['height'] = 300;
   *
   * I can then save these values however.
   *
   * If the result is a string, then there was an error and I am posting it to my module's
   * content variable.
   *
   * @author                         Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param  string postVar          Name of the file form variable
   * @param  string image_directory  Path where the file is to get saved
   * @param  int    widthLimit       Maximum allowed image width
   * @param  int    heightLimit      Maximum allowed image height
   * @param  int    sizeLimit        Maximum number of bytes allowed for the image.
   * @param  array  allowedImages    Array of images allowed. Defaults to all known.
   * @return mixed                   Array with image info if successful, error string if not
   */
  function saveImage($postVar, $image_directory, $widthLimit=NULL, $heightLimit=NULL, $sizeLimit=NULL, $allowedImages=NULL, $autoIncrement=TRUE){
    $loop = 0;
    $template["SUBMIT"] = $GLOBALS["core"]->formSubmit();
    
    $fileTypes = NULL;
    if (is_null($widthLimit))
      $widthLimit = MAX_IMAGE_WIDTH;
    
    if (is_null($heightLimit))
      $heightLimit = MAX_IMAGE_HEIGHT;
    
    if (is_null($sizeLimit))
      $sizeLimit = MAX_IMAGE_SIZE;
    
    if (!($filename = $_FILES[$postVar]['name']))
      return PHPWS_Error::get(PHPWS_FORM_NO_FILE, "core", "PHPWS_Form::saveImage", array($postVar));
    else {
      $filename = preg_replace("/[^a-z0-9\._\s]/i", "", $filename);
      $filename = str_replace (" ", "_", $filename);
      if (is_file($image_directory . $filename)){
	if ($autoIncrement){
	  for($i=1;$i < 1000; $i++){
	    $tempNameArray = explode(".", $filename);
	    $tempNameArray[0] = $tempNameArray[0] . "_" . $i;
	    $checkFile = implode(".", $tempNameArray);
	    if (is_file($image_directory . $checkFile))
	      continue;
	    else {
	      $filename = $checkFile;
	      break;
	    }
	  }
	} else
	  return PEAR::raiseError("Image file already exists. Try another filename.");
      }
    }
      
    $tmp_file = $_FILES[$postVar]['tmp_name'];
    
    $imageSize = getimagesize($tmp_file);
    $fileSize = $_FILES[$postVar]['size'];
    
    if (!is_dir($image_directory) || !is_writable($image_directory))
      return PHPWS_Error::get(PHPWS_DIR_NOT_WRITABLE, "core", "PHPWS_Form::saveImage");
    
    if ($fileSize > $sizeLimit)
      return PHPWS_Error::get(PHPWS_FORM_IMG_TOO_BIG, "core", "PHPWS_Form::saveImage", array($sizeLimit));

    if ($widthLimit && ($imageSize[0] > $widthLimit))
      return PHPWS_Error::get(PHPWS_FORM_WIDTH_TOO_BIG, "core", "PHPWS_Form::saveImage", array($widthLimit));
    
    if ($heightLimit && ($imageSize[1] > $heightLimit))
      return PHPWS_Error::get(PHPWS_FORM_HEIGHT_TOO_BIG, "core", "PHPWS_Form::saveImage", array($heightLimit));
    
    if (is_null($allowedImages) || !is_array($allowedImages))
      $allowedImages = array ("image/jpeg", "image/gif", "image/png", "image/jpg", "image/x-png", "image/pjpeg");
  
    foreach ($allowedImages as $type){
      if ($loop)
	$fileTypes .= ", ";
      $fileTypes .= $type;
      $loop = 1;
    }
  
    if (!in_array($_FILES[$postVar]["type"], $allowedImages))
      return PEAR::raiseError("Submitted image must be $fileTypes file.");
  
    if (move_uploaded_file($tmp_file, $image_directory . $filename)){
      $image["name"] = $filename;
      $image["width"] = $imageSize[0];
      $image["height"] = $imageSize[1];
      
      return $image;
    } else 
      return PEAR::raiseError("PHPWS_Form", "saveImage", "An unknown error occurred when trying to save your image.");
    
  }
  
  /**
   * Creates templated select boxes for dates
   *
   * This function will return a templte of select form elements
   * for phpWebSite.
   *
   * The template includes a drop down box for each of year, month, day.
   * It is up to the module developer to catch the data.
   *
   * If 'name' is not supplied, the form will use 'DATE'.
   *
   * The year box will be named the name + _YEAR.
   * The month box will be named the name + _MONTH.
   * The day box will be named the name + _DAY.
   *
   * If you have a current date you want to match in the select
   * boxes, send it to the match parameter and it will be
   * highlighted in the select boxes.
   *
   * @author                         Don Seiler <don@NOSPAM.seiler.us>
   * @param  string name             Name to label each form select box
   * @param  integer match           Unix timestamp of date
   * @param  integer yearStart       Date to start the year list
   * @param  integer yearEnd         Date to end the year list
   * @param  boolean useBlanks       Bit on whether or not to use blank options
   * @return array                   Template of date form
   */
  function dateForm($name=NULL, $match=NULL, $yearStart=NULL, $yearEnd=NULL, $useBlanks=FALSE){
    if (is_null($name))
      $name = "DATE";

    $match_y = NULL;
    $match_m = NULL;
    $match_d = NULL;
    if (is_null($match) && !$useBlanks) {
      $match = time();
      $match_y = date("Y", $match);
      $match_m = date("n", $match);
      $match_d = date("j", $match);
    } elseif (is_null($match) && $useBlanks) {
      $match_y = NULL;
      $match_m = NULL;
      $match_d = NULL;
    } else {
      if(empty($match))
	$match = time();
      $match_y = date("Y", $match);
      $match_m = date("n", $match);
      $match_d = date("j", $match);
    }

    if (is_numeric($yearStart) && is_numeric($yearEnd))
      $length = $yearEnd - $yearStart;
    elseif (is_numeric($yearStart) && ($yearStart < date("Y")))
      $length = 10;
    elseif (($yearStart - (int)date("Y")) > 10) {
      $length = $yearStart - (int)date("Y") + 3;
      $yearStart = (int)date("Y");
    }

    if(isset($length) && $length > 0 && $length < 1000)
      $years = $GLOBALS["core"]->yearArray($yearStart, $length);
    else
      $years = $GLOBALS["core"]->yearArray();

    $months = $GLOBALS["core"]->monthArray();
    $days = $GLOBALS["core"]->dayArray();

    if($useBlanks) {
      array_unshift($years, NULL);
      array_unshift($months, NULL);
      array_unshift($days, NULL);
    }

    $this->add($name . "_YEAR", "select", $years);
    $this->reindexValue($name . "_YEAR");
    $this->setMatch($name . "_YEAR", $match_y);
    $this->add($name . "_MONTH", "select", $months);
    $this->reindexValue($name . "_MONTH");
    $this->setMatch($name . "_MONTH", $match_m);
    $this->add($name . "_DAY", "select", $days);
    $this->reindexValue($name . "_DAY");
    $this->setMatch($name . "_DAY", $match_d);
    return TRUE;
  }// END FUNC dateForm
    

  /**
   * Creates templated select boxes for times
   *
   * This function will return a templte of select form elements
   * for phpWebSite.
   *
   * The template includes a drop down box for each of hour, minute, am/pm.
   * It is up to the module developer to catch the data.
   *
   * If 'name' is not supplied, the form will use 'TIME'.
   *
   * The hour box will be named the name + _HOUR.
   * The minute box will be named the name + _MINUTE.
   * The am/pm box will be named the name + _AMPM.
   *
   * If you have a current time you want to match in the select
   * boxes, send it to the match parameter and it will be
   * highlighted in the select boxes.
   *
   * @author                         Don Seiler <don@NOSPAM.seiler.us>
   * @param  string name             Name to label each form select box
   * @param  integer match           Unix timestamp of date
   * @param  integer increment       How many minutes to increment
   * @return array                   Template of date form
   */
  function timeForm($name=NULL, $match=NULL, $increment=15){
    if (is_null($name))
      $name = "DATE";


    if (preg_match("/g/", $GLOBALS["core"]->time_format))
      $hours = $GLOBALS["core"]->interval(12, 1);
    elseif (preg_match("/G/", $GLOBALS["core"]->time_format)){
      $hours= $GLOBALS["core"]->interval(23, 0);
      $military = 1;
    }
    elseif (preg_match("/h/", $GLOBALS["core"]->time_format)){
      $hour = $GLOBALS["core"]->interval(12, 1);
      foreach ($hours as $key=>$old_hour){
	if ((int)$old_hour < 10)
	  $hours[$key] = "0".(string)$old_hour;
      }
    } elseif (preg_match("/H/", $GLOBALS["core"]->time_format)){
      $hours = $GLOBALS["core"]->interval(23, 0);
      $military = 1;
      foreach ($hours as $key=>$old_hour){
	if ((int)$old_hour < 10)
	  $hours[$key] = "0".(string)$old_hour;
      }
    }

    $match_h = NULL;
    $match_m = NULL;
    $match_ampm = NULL;
    if (is_null($match) || empty($match)) {
      $match = time();
    }

    if($military)
      $match_h = date("G", $match);
    else
      $match_h = date("g", $match);
    $match_m = date("i", $match);
    $match_ampm = date("A", $match);

    $ampm = array(0=>"AM", 1=>"PM");
    if($match_ampm == "AM")
      $match_ampm = 0;
    else
      $match_ampm = 1;

    $minutes = $GLOBALS["core"]->interval(59,0,$increment);
    $m = NULL;
    foreach ($minutes as $key=>$old_min) {
      if((int)$old_min < 10)
	$minutes[$key] = "0" . (string)$old_min;

      if(is_null($m) && ($old_min > $match_m)) {
	$match_m = $old_min;
	$m = 1;
      }
    }

    $this->add($name . "_HOUR", "select", $hours);
    $this->reindexValue($name . "_HOUR");
    $this->setMatch($name . "_HOUR", $match_h);
    $this->add($name . "_MINUTE", "select", $minutes);
    $this->reindexValue($name . "_MINUTE");
    $this->setMatch($name . "_MINUTE", $match_m);
    if(!isset($military) || ($military != 1)) {
      $this->add($name . "_AMPM", "select", $ampm);
      //$this->reindexValue($name . "_AMPM");
      $this->setMatch($name . "_AMPM", $match_ampm);
    }
    return TRUE;
  }// END FUNC timeForm

  /**
   * Return a textual error message for a DB error code
   * Function is copied from DB.php in PEAR libs.
   *
   * @param integer $value error code
   *
   * @return string error message, or false if the error code was
   * not recognized
   */
  function errorMessage($value, $funcName=NULL){
    static $errorMessages;

    if (!isset($errorMessages)) {
      $errorMessages = array(
			     PHPWS_FORM_ERROR           => 'Unknown error',
			     PHPWS_FORM_ERR_FILE_POST   => 'Variable missing from _FILES post',
			     PHPWS_FORM_ERR_FILE_EXISTS => 'Image file already exists',
			     PHPWS_FORM_ERR_NO_DIR      => 'Save directory does not exist or is not writable',
			     PHPWS_FORM_ERR_BIG_IMAGE   => 'Submitted image was larger than size limit'
			     );
    }
    
    if (PEAR::isError($value)) {
      $value = $value->getCode();
    }

    $message[] = "<b>Error:</b> in Database.php - ";

    if (isset($errorMessages[$value]))
      $message[] = $errorMessages[$value];
    else
      $message[] = $errorMessages[PHPWS_FORM_ERROR];

    if (isset($funcName))
      $message[] = " in function <b>$funcName()</b>";
    
    $message[] = ".";

    return implode("", $message);
  }
}

class PHPWS_Element{

  /**
   * Name of an element
   */
  var $name;

  /**
   * Value of an element
   */
  var $value;

  /**
   * Type of form element
   */
  var $type;

  /**
   * Substitute template tag name
   */
  var $tag;

  /**
   * Size of the element
   *
   * Used for select and text elements
   */
  var $size;

  /**
   * maximum size of text field
   */
  var $maxsize;

  /**
   * Value to match for radio, checkbox, select elements
   */
  var $match;

  /**
   * Rows of a textarea
   */
  var $rows;

  /**
   * Columns of a textarea
   */
  var $cols;


  /**
   * Extra information padded to an element
   */
  var $extra;

  /**
   * Order of tab elements
   */
  var $tab;

  /**
   * if 1, password fields will fill in size
   */
  var $showPass;


  /**
   * If TRUE, match to option instead of value
   */
  var $optionMatch;

  function PHPWS_Element(){
    $this->name        = NULL;
    $this->value       = NULL;
    $this->type        = NULL;
    $this->tag         = NULL;
    $this->size        = NULL;
    $this->maxsize     = NULL;
    $this->match       = NULL;
    $this->rows        = NULL;
    $this->cols        = NULL;
    $this->extra       = NULL;
    $this->showPass    = FALSE;
    $this->optionMatch = FALSE;
  }

  function _setExtra($extra){
    $this->extra = $extra;
  }


  function _setType($type){
    $formTypes = array("radio",
		       "hidden",
		       "file",
		       "text",
		       "textfield",
		       "password",
		       "textarea",
		       "checkbox",
		       "dropbox",
		       "select",
		       "multiple",
		       "submit",
		       "button"
		       );

    if (in_array($type, $formTypes)){
      if ($type == "dropbox")
	$this->type = "select";
      elseif($type == "textfield")
	$this->type = "text";
      else
	$this->type = $type;
      return TRUE;
    }
    else
      return PHPWS_Error::get(PHPWS_FORM_UNKNOWN_TYPE, "core", "PHPWS_Form::setType");
  }

  function _setTab($order){
    if (!is_numeric($order))
      return PHPWS_Error::get(PHPWS_VAR_TYPE, "core", "PHPWS_Form::setTab");
    
    $this->tab = $order;
  }

  function getTab(){
    return $this->tab;
  }


  function _setTag($tag){
    PHPWS_Core::initCoreClass("Text.php");
    if (!PHPWS_Text::isValidInput($tag))
      return PHPWS_Error::get(PHPWS_STRICT_TEXT, "core", "PHPWS_Form::setTag");

    $this->tag = $tag;
    return TRUE;
  }

  function _setMatch($match){
    $this->match = $match;
  }

  function _setRows($rows){
    if (!is_numeric($rows) || $rows < 1 || $rows > 100)
      return PHPWS_Error::get(PHPWS_INVALID_VALUE, "core", "PHPWS_Form::setRows");

    $this->rows = $rows;
    return TRUE;
  }

  function _setWidth($width){
    if (!is_numeric($width) || $width < 1 || $width > 100)
      return PHPWS_Error::get(PHPWS_INVALID_VALUE, "core", "PHPWS_Form::setWidth");

    $this->width = $width;
    return TRUE;
  }

  function _setHeight($height){
    if (!is_numeric($height) || $height < 1 || $height > 100)
      return PHPWS_Error::get(PHPWS_INVALID_VALUE, "core", "PHPWS_Form::setHeight");

    $this->height = $height;
    return TRUE;
  }


  function _setCols($cols){
    if (!is_numeric($cols) || $cols < 1 || $cols > 100)
      return PHPWS_Error::get(PHPWS_INVALID_VALUE, "core", "PHPWS_Form::setCols");

    $this->cols = $cols;
    return TRUE;
  }

  function getType(){
    return $this->type;
  }

  function _setValue($value){
    $this->value = $value;
  }

  function _setSize($size){
    if (is_numeric($size))
      $this->size = $size;
    else 
      return PHPWS_Error::get(PHPWS_VAR_TYPE, "core", "PHPWS_Form::setSize");
  }

  function _setMaxSize($maxsize){
    if (is_numeric($maxsize))
      $this->maxsize = $maxsize;
    else
      return PHPWS_Error::get(PHPWS_VAR_TYPE, "core", "PHPWS_Form::setMaxSize");
  }


  function _getInput(){
    $value = $misc = NULL;
    $size = $maxsize = $extra = $tab = $style = NULL;
    $name = "name=\"" . $this->name . "\" ";
    $type = "type=\"" . $this->type . "\" ";

    if (isset($this->tab))
      $tab = " tabindex=\"" . $this->tab . "\" ";

    switch ($this->type){
    case "text":
    case "password":
    case "file":
      if (isset($this->width))
	$style[] = "width : " . $this->width . "%";

      if (isset($this->height))
	$style[] = "height : " . $this->height . "%";
      
      if (isset($this->size))
	$size = "size=\"" . $this->size . "\" ";
      elseif (USE_DEFAULT_SIZES == TRUE)
	$size = "size=\"" . DFLT_TEXT_SIZE . "\" ";

      isset($this->maxsize) ? $maxsize = $this->maxsize : $maxsize = DFLT_MAX_SIZE;

      $maxsize = "maxsize=\"" . $maxsize . "\" ";
      break;

    case "select":
    case "multiple":
      if (isset($this->size))
	$size = "size=\"" . $this->size . "\" ";
      elseif (USE_DEFAULT_SIZES == TRUE)
	$size = "size=\"" . DFLT_MAX_SELECT . "\" ";
      break;

    case "textarea":
      if (isset($this->width)){
	$style[] = "width : " . $this->width . "%";
	$cols = NULL;
      }
      else {
	if (isset($this->cols))
	  $cols = "cols=\"" . $this->cols . "\"";
	else
	  $cols = "cols=\"" . DFLT_COLS . "\"";
      }

      if (isset($this->height)){
	$style[] = "height : " . $this->height . "%";
	$rows = NULL;
      }
      else {
	if (isset($this->rows))
	  $rows = "rows=\"" . $this->rows . "\" ";
	else
	  $rows = "rows=\"" . DFLT_ROWS . "\" ";
      }

      break;
    }

    if ($this->extra)
      $extra = $this->extra . " ";
    else
      $extra = NULL;

    if (isset($this->value)){
      switch ($this->type){
      case "hidden":       
      case "text":
      case "submit":
      case "button":
      case "checkbox":
	$value = str_replace("\"", "&#x0022;", $this->value);
	$value = "value=\"" . $value . "\" ";
	break;

      case "password":
	if ($this->showPass){
	  $value = "value=\"" . preg_replace("/./", "*", $this->value) . "\" ";
	}
	break;

      case "select":
	$value = $this->value;
	break;
	
      case "textarea":
	$value = $this->value;
	$value = htmlspecialchars($value, ENT_NOQUOTES);
	$value = str_replace("&#x0024;", "&", $value);
	$value = str_replace("&amp;#39;", "'", $value);

	if (ord(substr($value, 0, 1)) == 13)
	  $value = "\n" . $value;
	break;
      }
    }

    if ($this->type == "checkbox" && !empty($this->match) && $this->match == $this->value)
      $misc = "checked=\"checked\" ";

    if (isset($style))
      $style = "style=\"" . implode ("; ", $style) . "\" ";

    switch ($this->type){
    case "textarea":
      $formElement =  "<textarea wrap=\"virtual\" " . $name . $style . $rows . $cols . $tab . ">" . $value . "</textarea>\n";
      break;

    case "select":
      if (is_array($this->value))
	$formElement = $this->_getSelect();
      else {
	$this->error = "Missing select values for <b>".$this->name."</b>.";
	return FALSE;
      }
      break;
      
    case "multiple":
      if (is_array($this->value))
	$formElement = $this->_getSelect(1);
      else {
	$this->error = "Missing multiple select values for <b>".$this->name."</b>.";
	return FALSE;
      }
      break;
      
    case "radio":
      if (!is_array($this->value) || count($this->value) < 2){
	$value = "value=\"".$this->value."\" ";
	$formElement =  "<input " . $type . $name . $value . $misc . $extra . $tab . "/>\n";
      } else {
	foreach ($this->value as $radioValue){
	  $misc = NULL;
	  $value = "value=\"$radioValue\" ";

	  if ($this->match == $radioValue)
	    $misc = "checked=\"checked\" ";

	  $formElement[] =  "<input " . $type . $name . $value . $misc . $extra . $tab . "/>\n";
	}
      }
      break;
    
    default:
      $formElement =  "<input " . $type . $name . $value . $misc . $style . $size . $maxsize . $extra . $tab . "/>\n";
      break;
    }

    return $formElement;
  }

  function _getSelect($multiple=0){
    $selectVals = $this->value;
    $tab = $misc = NULL;
    
    if (isset($this->tab))
      $tab = " tabindex=\"" . $this->tab . "\" ";
    
    if ($multiple){
      $name = "name=\"" . $this->name . "[]\" ";
      $misc = "multiple=\"multiple\" ";
      if ($this->size)
	$misc .= " size=\"" . $this->size . "\" ";
    } else
      $name = "name=\"".$this->name."\"";
    
    if ($this->extra)
      $extra = $this->extra . " ";

    $data = "<select " . $misc . $name . $tab . ">\n";
    foreach($selectVals as $value=>$option){
      $match = NULL;

      if ($multiple){
	if (!is_array($this->match))
	  return PHPWS_Error::get(PHPWS_FORM_INVALID_MATCH, "core", "PHPWS_Form::getSelect");

	elseif (!$this->optionMatch && in_array((string)$value, $this->match))
	  $match = " selected=\"selected\"";
	elseif ($this->optionMatch && in_array($option, $this->match))
	  $match = " selected=\"selected\"";
      }
      else {
	if (!$this->optionMatch && $this->match == $value)
	  $match = " selected=\"selected\"";
	elseif ($this->optionMatch && $this->match == $option)
	  $match = " selected=\"selected\"";
      }

      $data .= "<option value=\"" . $value . "\"" . $match . ">" . $option . "</option>\n";
    }

    $data .= "</select>\n";

    return $data;
  }

}

?>
