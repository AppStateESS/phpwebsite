<?php
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
 * $form->add('testarea');
 *
 * This would create a form element named 'testarea'. You can set the type and value via
 * the setType and setValue functions or you can just include them in the add.
 * Example:
 * $form->add('testarea', 'textarea', 'something something');
 *
 * For many form elements, that may be all you need.
 *
 * @version $Id$
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @author Don Seiler <don at seiler dot us>
 * @package Core
 *
 */

PHPWS_Core::configRequireOnce('core', 'formConfig.php', true);
PHPWS_Core::initCoreClass('Editor.php');

class PHPWS_Form {
    var $id = 'phpws_form';
  
    /**
     * Array of form elements
     * @var    array
     * @access private
     */
    var $_elements = array();

    /**
     * Directory destination of submitted form.
     * Note: if none is provided, getTemplate will try to use the core
     * home_http directory
     * 
     * @var    string
     * @access private
     */
    var $_action = null;

    /**
     * How the form is sent.
     * @var    string
     * @access private
     */
    var $_method = 'post';

    /**
     * Tells whether to multipart encode the form
     * @var    string
     * @access private
     */
    var $_encode = null;

    /**
     * Holds an extra template to merge with the final
     * @var    array
     * @access private
     */
    var $_template = null;

    var $types = array();

    var $tagReplace = array();

    var $allowFormName = false;
  
    var $use_auth_key = true;

    var $_autocomplete = true;

    var $max_file_size = 0;

    var $_multipart = false;

    /**
     * If true, form will use a generic fieldset to comply with XHTML
     */
    var $use_fieldset = FORM_DEFAULT_FIELDSET;

    var $legend = FORM_GENERIC_LEGEND;

    /**
     * If true, then getTemplate will print multiple valued elements
     * using the sigma template multi row format
     */
    var $row_repeat = false;

    /**
     * Constructor for class
     */
    function PHPWS_Form($id=null)
    {
        if (!defined('ABSOLUTE_UPLOAD_LIMIT') || ABSOLUTE_UPLOAD_LIMIT > FORM_MAX_FILE_SIZE) {
            $this->max_file_size = FORM_MAX_FILE_SIZE;
        } else {
            $this->max_file_size = ABSOLUTE_UPLOAD_LIMIT;
        }

        if (isset($id)) {
            $this->id = $id;
        }
        $this->reset();
    }

    function setEncode($encode=true)
    {
        if ($encode) {
            $this->_encode = ' enctype="multipart/form-data"';
        } else {
            $this->_encode = false;
        }
            
    }

    function setFormId($id)
    {
        $this->id = $id;
    }

 
    function useFieldset($fieldset=true)
    {
        $this->use_fieldset = (bool)$fieldset;
    }

    function setLegend($legend)
    {
        $this->use_fieldset = true;
        $this->legend = strip_tags($legend);
    }

    function setMaxFileSize($file_size)
    {
        if ($file_size <= 1) {
            return;
        }
        $this->max_file_size = (int)$file_size;
    }

    /**
     * Some browsers will not try to autocomplete a text field
     * when auto complete is turned off. This can be useful for
     * user signup if you don't want the users name saved. Again,
     * you are at the whim of the browser's settings.
     */
    function turnOffAutocomplete()
    {
        $this->_autocomplete = false;
    }

    function turnOnAutocomplete()
    {
        $this->_autocomplete = true;
    }

    function noAuthKey()
    {
        $this->use_auth_key = false;
    }

    /**
     * Uses the template row repeat method on multiple elements
     */
    function useRowRepeat()
    {
        $this->row_repeat = true;
    }

    function reset()
    {
        $this->_elements = array();
        $this->_action   = null;
        $this->_method   = 'post';
        $this->_encode   = null;
    }

    function allowFormName()
    {
        $this->allowFormName = true;
    }

    function getFormId()
    {
        return $this->id;
    }

    function setMethod($method)
    {
        if ($method != 'post' && $method != 'get') {
            return;
        }
        $this->_method = $method;
    }

    function addText($name, $value=null)
    {
        return $this->add($name, 'text', $value);
    }

    function addTextField($name, $value=null)
    {
        return $this->add($name, 'text', $value);
    }

    function addTextarea($name, $value=null)
    {
        return $this->add($name, 'textarea', $value);
    }

    function addFile($name)
    {
        return $this->add($name, 'file');
    }

    function addSubmit($name, $value=null)
    {
        if (empty($value)) {
            $value = $name;
            $name = 'submit';
        }
        return $this->add($name, 'submit', $value);
    }

    function addButton($name, $value=null)
    {
        if (empty($value)) {
            $value = $name;
            $name = 'button';
        }
        return $this->add($name, 'button', $value);
    }

    function addPassword($name, $value=null)
    {
        return $this->add($name, 'password', $value);
    }

    function addSelect($name, $value)
    {
        return $this->add($name, 'select', $value);
    }

    function addDropBox($name, $value)
    {
        return $this->add($name, 'select', $value);
    }

    function addMultiple($name, $value)
    {
        return $this->add($name, 'multiple', $value);
    }

    function addRadio($name, $value=null)
    {
        return $this->add($name, 'radio', $value);
    }

    function addRadioButton($name, $value=null)
    {
        return $this->add($name, 'radio', $value);
    }

    function addRadioAssoc($name, $assoc)
    {
        if (!is_array($assoc)) {
            return false;
        }

        $this->add($name, 'radio', array_keys($assoc));
        $this->setLabel($name, array_values($assoc));
    }

    function addCheck($name, $value=1)
    {
        return $this->add($name, 'check', $value);
    }

    function addCheckBox($name, $value=1)
    {
        return $this->add($name, 'check', $value);
    }

    function addHidden($name, $value=null)
    {
        if (is_array($name)) {
            return $this->duplicate($name, 'hidden');
        } else {
            return $this->add($name, 'hidden', $value);
        }
    }

    function duplicate($dup, $type)
    {
        foreach ($dup as $name=>$value) {
            $result = $this->add($name, $type, $value);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Removes an element from the form
     */
    function dropElement($name)
    {
        unset($this->_elements[$name]);
    }

    function getPostedDate($name)
    {
        $year   = (int)date('Y');
        $month  = (int)date('m');
        $day    = (int)date('j');
        $hour   = 0;
        $minute = 0;

        if (isset($_POST[sprintf('%s_year', $name)])) {
            $year = $_POST[sprintf('%s_year', $name)];
        }

        if (isset($_POST[sprintf('%s_month', $name)])) {
            $month = $_POST[sprintf('%s_month', $name)];
        }

        if (isset($_POST[sprintf('%s_day', $name)])) {
            $day = $_POST[sprintf('%s_day', $name)];
        }

        if (isset($_POST[sprintf('%s_24hour', $name)])) {
            $hour = $_POST[sprintf('%s_24hour', $name)];
        } elseif (isset($_POST[sprintf('%s_12hour', $name)])) {
            $hour = $_POST[sprintf('%s_12hour', $name)];
            if (isset($_POST[sprintf('%s_ampm', $name)])) {
                $hour += 12 * (int)$_POST[sprintf('%s_ampm', $name)];
            }
        }

        if (isset($_POST[sprintf('%s_minute', $name)])) {
            $minute = $_POST[sprintf('%s_minute', $name)];
        }
        
        return mktime($hour, $minute, 0, $month, $day, $year);
    }

    function dateSelect($name, $month_format= '%B', $years_past=1, $years_ahead=3)
    {
        $allowed_month_formats = array('%b', '%B', '%m');

        if (!in_array($month_format, $allowed_month_formats)) {
            $month_format = '%B';
        }

        if ($years_past < 0) {
            $years_past = 1;
        }

        if ($years_ahead < 0) {
            $years_ahead = 3;
        }

        $current_year = (int)date('Y');

        for ($i = $current_year - $years_past; $i <= $current_year + $years_ahead; $i++) {
            $years[$i] = $i;
        }

        for ($i = 1; $i < 13; $i++) {
            $months[$i] = strftime($month_format, mktime(0,0,0,$i,1,2004));
        }

        for ($i = 1; $i < 32; $i++) {
            $days[$i] = $i;
        }

        for ($i = 1; $i < 13; $i++) {
            $hours_12[$i] = strftime('%I', mktime($i));
        }

        for ($i = 0; $i < 24; $i++) {
            $hours_24[$i] = strftime('%H', mktime($i));
        }

        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = strftime('%M', mktime(1, $i));;
        }

        $am_pm[0] = strftime('%p', mktime(1));
        $am_pm[1] = strftime('%p', mktime(15));

        $this->addSelect(sprintf('%s_year', $name), $years);
        $this->addSelect(sprintf('%s_month', $name), $months);
        $this->addSelect(sprintf('%s_day', $name), $days);
        $this->addSelect(sprintf('%s_12hour', $name), $hours_12);
        $this->addSelect(sprintf('%s_24hour', $name), $hours_24);
        $this->addSelect(sprintf('%s_minute', $name), $minutes);
        $this->addSelect(sprintf('%s_ampm', $name), $am_pm);
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
    function add($name, $type=null, $value=null)
    {
        if (preg_match('/[^\[\]\w]+/i', $name)) {
            return PHPWS_Error::get(PHPWS_FORM_BAD_NAME, 'core', 'PHPWS_Form::add', array($name));
        }
        $result = PHPWS_Form::createElement($name, $type, $value);

        if (PEAR::isError($result)) {
            return $result;
        }

        if (is_array($result)) {
            foreach ($result as $element){
                if ($type != 'radio') {
                    $element->isArray = true;
                }
                
                $element->_form = &$this;

                $this->_elements[$name][$element->value] = $element;
                $this->_elements[$name][$element->value]->key = $element->value;
                $this->_elements[$name][$element->value]->setId();
            }
        } else {
            if (isset($this->_elements[$name])) {
                $this->_elements[$name][0]->isArray = true;
                $result->isArray = true;
            }
            $result->_form = &$this;
            $this->_elements[$name][] = $result;

            $current_key = $this->getKey($name);
            $this->_elements[$name][$current_key]->key = $current_key;
            $this->_elements[$name][$current_key]->setId();
        }

        $this->types[$name] = $type;
        return true;
    }

    function getKey($name)
    {
        end($this->_elements[$name]);
        $current_key = key($this->_elements[$name]);
        return $current_key;
    }

    function useEditor($name, $value=true, $limited=false, $width=0, $height=0)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::useEditor', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            if ($this->_elements[$name][$key]->type != 'textarea') {
                break;
            }
            $this->_elements[$name][$key]->_use_editor = $value;
            $this->_elements[$name][$key]->_limit_editor = $limited;
            if ($width > 100 && $height > 100) {
                $this->_elements[$name][$key]->_editor_dm = array($width, $height);
            }
        }
    }


    function setValue($name, $value)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setValue', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setValue($value);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }
    

    /**
     * Sets an element's disabled status
     */
    function setDisabled($name, $value=true)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setDisabled', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setDisabled($value);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Sets an element's readonly status
     */
    function setReadOnly($name, $value=true)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setReadonly', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setReadOnly($value);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }


    function makeLabel($name, $label)
    {
        $required = $this->getRequired();
        return sprintf('%s<label class="%s-label" for="%s">%s</label>', $required, $this->type, $name, $label);
    }

    
    function setOptgroup($name, $value, $label)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setOptgroup', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if ($element->type != 'select' && $element->type != 'multiple') {
                continue;
            }
            $result = $this->_elements[$name][$key]->setOptgroup($value, $label);

            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }


    function setLabel($name, $label=null)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setLabel', array($name));
        }

        if (empty($label)) {
            $label = $name;
        }

        foreach ($this->_elements[$name] as $key => $element){
            if (is_array($label) && isset($label[$element->key])) {
                $result = $this->_elements[$name][$key]->setLabel($label[$element->key]);
            }
            else {
                $result = $this->_elements[$name][$key]->setLabel($label);
            }

            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Adds a prefix to a label to indicate it is a required field
     */
    function setRequired($name)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setLabel', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element){
            $result = $this->_elements[$name][$key]->setRequired();

            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }



    function getId($name)
    {
        if (count($this->_elements[$name]) > 1) {
            foreach ($this->_elements[$name] as $element) {
                $ids[] = $element->id;
            }
            return $ids;
        } else {
            return $this->_elements[$name][0]->id;
        }
    }

    function addTplTag($tag, $data)
    {
        $this->_template[$tag] = $data;
    }

    /**
     * Removes a form element from the class
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     */
    function drop($name)
    {
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
    function setExtra($name, $extra)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setExtra', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setExtra($extra);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Lets you enter a width style to a text field or area
     *
     * Instead of setting a column number, you may prefer applying
     * a style width. The width will size itself depending on side of
     * or its contained (i.e. a table cell)
     *
     * @author             Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name  Name of element to set the type
     * @param string width Percentage of width wanted on element
     */
    function setWidth($name, $width)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setWidth', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setWidth($width);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
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
     * @author              Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name   Name of element to set the type
     * @param string height Percentage of height wanted on element
     */
    function setHeight($name, $height)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setHeight', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setHeight($height);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Allows you to set the numbers of rows for a textarea
     *
     * Rows must be more than 1 and less than 100
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name Name of element to set the rows
     * @param string rows Number rows to use in a textarea
     */
    function setRows($name, $rows)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setRows', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setRows($rows);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Sets the title for a form element.
     *
     * A tag outputs its title when 'moused-over'. This works in newer browsers.
     * It is not required but can help inform the user on its function.
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name  Name of element to set the rows
     * @param string title Title text
     */
    function setTitle($name, $title)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setTitle', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setTitle($title);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Allows you to set the numbers of columns for a textarea
     *
     * Columns must be more than 10 and less than 500
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name Name of element to set the rows
     * @param string rows Number columns to use in a textarea
     */
    function setCols($name, $cols)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setCols', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setCols($cols);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Sets the tabindex for a form element
     *
     * Tab indexing allows use of the tab key to move among
     * form elements (like Windows). Just give the name of the element
     * and what order you want it in. PHPWS_Form does not check your settings
     * so be careful you don't use the same number more than once.
     *
     * @author               Matthew McNaney <matt at tux dot appstate dot edu>
     * @param  string  name  Name of element to set the type
     * @param  integer order Numeric order of tab queue
     */
    function setTab($name, $order)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setTab', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setTab($order);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Sets the number of characters for text boxes, number of rows
     * for select boxes
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name Name of element to set the type
     * @param string size Size to make the element
     */
    function setSize($name, $size, $maxsize=null)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setSize', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setSize((int)$size);
            if (PEAR::isError($result)) {
                return $result;
            }
            if (!empty($maxsize)) {
                $result = $this->_elements[$name][$key]->setMaxSize((int)$maxsize);
            }
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Allows the password value to appear in the form.
     */
    function allowValue($name)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setSize', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->allowValue();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Changed the template tag name for the form element
     *
     * Should be used if you do not want to use the name of the post
     * variable for your template. A good function to use to convert
     * old templates. 
     *
     * @author                Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name     Name of element to set the type
     * @param string template Name of template tag to print for this element
     */
    function setTag($name, $tag)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setTag', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setTag($tag);
            if (PEAR::isError($result)) {
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
     * $form->add('testing', 'multiple', $list);
     * $form->reindexValue('testing');
     * $form->setMatch('testing', array('orange', 'banana'));
     *
     * This would change the index array to array('apple'=>'apple', 'orange'=>'orange', etc.
     *
     * @author                    Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name         Name of element to set the type
     */
    function reindexValue($name)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::reindexValue', array($name));
        }

        if ($this->types[$name] != 'multiple' && $this->types[$name] != 'select') {
            return PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, 'core', 'PHPWS_Form::reindexValue');
        }

        foreach ($this->_elements[$name] as $key=>$element) {
            if (empty($element->value)) {
                continue;
            }
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
     * optionMatch to true.
     *
     * @author                    Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string name         Name of element to set the type
     * @param string match        Value to match against the element's value
     * @param boolean optionMatch If true, then a select box will try to match
     *                            the value to the option not the value
     */
    function setMatch($name, $match, $optionMatch=false)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setMatch', array($name));
        }

        if ($this->types[$name] == 'multiple' && !is_array($match)) {
            return PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, 'core', 'PHPWS_Form::reindexValue');
        }

        foreach ($this->_elements[$name] as $key=>$element) {
            if ($this->_elements[$name][$key]->type == 'hidden') {
                continue;
            }
            $result = $this->_elements[$name][$key]->setMatch($match);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }

    function setClass($name, $class_name)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setMatch', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setClass($class_name);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Sets the max text size for a text, password, file element
     *
     * @author Matthew McNaney<matt at tux dot appstate dot edu>
     * @param string  name Name of element to set the maxsize
     * @param integer maxsize The max number of characters allowed in the element's field
     */
    function setMaxSize($name, $maxsize)
    {
        if (!$this->testName($name)) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setMaxSize', array($name));
        }

        foreach ($this->_elements[$name] as $key=>$element){
            $result = $this->_elements[$name][$key]->setMaxSize($maxsize);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Merges another template array into the one created
     * by the form
     */
    function mergeTemplate($template)
    {
        if (!is_array($template)) {
            return;
        }

        if (!isset($this->_template)) {
            $this->_template = $template;
        }
        else {
            $this->_template = array_merge($this->_template, $template);
        }
    }

    /**
     * Indicates whether an element exists
     *
     * @author         Matthew McNaney<matt at tux dot appstate dot edu>
     * @param  string  name Name to check if exists
     * @return boolean true if the element exists, false otherwise
     */
    function testName($name)
    {
        return isset($this->_elements[$name]);
    }


    function &createElement($name, $type, $value)
    {
        switch ($type){
        case 'text':
        case 'textfield':
            $obj = new Form_TextField($name, $value);
            return $obj;
            break;
      
        case 'textarea':
            $obj = new Form_TextArea($name, $value);
            return $obj;
            break;

        case 'submit':
            $obj = new Form_Submit($name, $value);
            return $obj;
            break;

        case 'button':
            $obj = new Form_Button($name, $value);
            return $obj;
            break;
            

        case 'password':
            $obj = new Form_Password($name, $value);
            return $obj;
            break;

        case 'file':
            $this->_multipart = true;
            $this->_encode = ' enctype="multipart/form-data"';
            $obj = new Form_File($name);
            return $obj;
            break;
      
        case 'select':
        case 'dropbox':
            $obj = new Form_Select($name, $value);
            return $obj;
            break;

        case 'multiple':
            $obj = new Form_Multiple($name, $value);
            return $obj;
            break;

        case 'radio':
        case 'radiobutton':
            if (is_array($value)) {
                foreach ($value as $key=>$sub) {
                    $radio = new Form_RadioButton($name, $sub);
                    $radio->key = $sub;
                    $radio->place = $key;
                    $allRadio[$sub] = $radio;
                }
                return $allRadio;
            } else {
                $obj = new Form_RadioButton($name, $value);
                return $obj;
            }
            break;

        case 'check':
        case 'checkbox':
            if (is_array($value)) {
                $check_count=0;
                foreach ($value as $sub) {
                    $check[$check_count] = new Form_Checkbox($name, $sub);
                    $check[$check_count]->place = $check_count;
                    $check_count++;
                }
                return $check;
            } else {
                $obj = new Form_Checkbox($name, $value);
                return $obj;
            }
            break;

        case 'hidden':
            if (is_array($value)) {
                foreach ($value as $key=>$sub) {
                    $hidden = new Form_Hidden($name, $sub);
                    $hidden->setId();
                    $hidden->isArray = true;
                    $hidden->key = $sub;
                    $hidden->place = $key;
                    $allHidden[$sub] = $hidden;
                }
                return $allHidden;
            } else {
                $obj = new Form_Hidden($name, $value);
                return $obj;
            }
            break;

        default:
            $error = PHPWS_Error::get(PHPWS_FORM_UNKNOWN_TYPE, 'core', 'PHPWS_Form::createElement');
            return $error;
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
     * @author                   Matthew McNaney<matt at tux dot appstate dot edu>
     * @param  string  directory Directory that a form will post to
     */
    function setAction($directory)
    {
        $this->_action = $directory;
    }

    function get($name, $all=false)
    {
        if (!isset($this->_elements[$name])) {
            return PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::get', array($name));
        }

        if (count($this->_elements[$name]) > 1) {
            $multiple = true;
        }

        if ($all == false) {
            foreach ($this->_elements[$name] as $key => $element) {
                $content[] = $element->get($key);
            }
            return implode("\n", $content);
        } else {
            foreach ($this->_elements[$name] as $key => $element){
                $content['elements'][$key] = $element->get($key);
                $content['labels'][$key] = $element->getLabel(true, true);
            }
            return $content;
        }
    }

    function grab($name)
    {
        if (count($this->_elements[$name]) > 1) {
            return $this->_elements[$name];
        }
        else {
            return $this->_elements[$name][0];
        }
    }

    function replace($name, $elements)
    {
        $this->_elements[$name] = array();

        if (is_array($elements)) {
            $this->_elements[$name] = $elements;
        }
        else {
            $this->_elements[$name][] = $elements;
        }
    }


    /**
     * Returns all the elements of a form in a template array
     *
     * This is the fruit of your labor. After calling this you will get an associative array
     * of all you form elements. The keys of the template are the capitalized names of the elements.
     * The template also includes START_FORM and END_FORM tags to make creating the form easier.
     * Hidden variables will AUTOMATICALLY be added to the START_FORM tag. If helperTags == false
     * they will be placed in a tag named HIDDEN.
     * It will also create a DEFAULT_SUBMIT button.
     * 
     * Hidden variables will be added on to START_FORM. They will NOT have their own template tag.
     *
     * @author                     Matthew McNaney<matt at tux dot appstate dot edu>
     * @param  boolean phpws       If true and the action is missing, phpWebSite will attempt to use your directory settings instead
     * @param  boolean helperTags  If true START and END_FORM tags will be created, otherwise they will not
     * @param  array   template    If a current template is supplied, form will add to it.
     * @return array   template    Array of completed form
     */
    function getTemplate($phpws=true, $helperTags=true, $template=null)
    {
        if (count($this->_elements) < 1) {
            return PHPWS_Error::get(PHPWS_FORM_NO_ELEMENTS, 'core', 'PHPWS_Form::getTemplate');
        }

        if (!is_null($template) && !is_array($template)) {
            return PHPWS_Error::get(PHPWS_FORM_NO_TEMPLATE, 'core', 'PHPWS_Form::getTemplate');
        }

        if ($helperTags) {
            $template['START_FORM'] = $this->getStart() . "\n";
            if ($this->use_fieldset) {
                $template['START_FORM'] .= "<fieldset>\n";
                $template['START_FORM'] .= '<legend>' . $this->legend . "</legend>\n";
            } else {
                $template['START_FORM'] .= "<div>\n";
            }

            if (FORM_USE_FILE_RESTRICTIONS && $this->_multipart) {
                $template['START_FORM'] .= sprintf('<input type="hidden" name="MAX_FILE_SIZE" value="%d" />', $this->max_file_size) . "\n";
            }
        }

        unset($this->_elements['authkey']);
        if (class_exists('Current_User')) {
            if ($authkey = Current_User::getAuthKey()) {
                $this->addHidden('authkey', $authkey);
            }
        }

        foreach ($this->_elements as $elementName=>$element){
            $multiple = false;
            $count = 1;
            $mult_count = 0;

            if (count($element) > 1) {
                $multiple = true;
            }

            foreach ($element as $subElement){
                $subtpl = array();

                if ($this->types[$elementName] == 'hidden') {
                    if ($helperTags) {
                        $template['START_FORM'] .= $subElement->get() . "\n";
                    } else {
                        $hidden_vars[] = $subElement->get();
                    }
                    continue;
                }

                $tagName = $subElement->getTag();
                $label = $subElement->getLabel(true);

                if ($this->row_repeat && $multiple) {
                    if (!empty($label)) {
                        $subtpl[$tagName . '_LABEL'] = $label;
                    }
                    $subtpl[$tagName] = $subElement->get();
                    $template[strtolower($tagName) . '_repeat'][] = $subtpl;
                    continue;
                }

                if ($multiple) {
                    $tagName .= "_$count";
                }
                
                if (!empty($label)) {
                    $template[$tagName . '_LABEL'] = $label;
                }

                $template[$tagName] = $subElement->get();
                $count++;
            }
        }      

        if ($helperTags) {
            if (isset($this->_action)) {
                if ($this->use_fieldset) {
                    $end_form[] = '</fieldset>';
                } else {
                    $end_form[] = '</div>';
                }
                $end_form[] = '</form>';
                $template['END_FORM'] = implode("\n", $end_form);
            }

        } elseif (isset($hidden_vars)) {
            $template['HIDDEN'] = implode("\n", $hidden_vars);
        }
        

        if (isset($this->_template)) {
            $template = array_merge($this->_template, $template);
        }

        if ($phpws == true) {
            return $template;
        } else {
            return implode("\n", $template);
        }
    }

    
    function getMerge()
    {
        $form = $this->getTemplate();
        return implode("\n", $form);
    }

    function getMethod($tagMode=false)
    {
        if ($tagMode == true) {
            return 'method="' . $this->_method . '"';
        }
        else {
            return $this->_method;
        }
    }

    function getStart()
    {
        if (!isset($this->_action)) {
            $this->_action = 'index.php';
        }

        if ($this->_multipart) {
            $this->_action .= '?check_overpost=1';
        }
        
        if (isset($this->id)) {
            if ($this->allowFormName) {
                $formName = 'name="' . $this->id . '" id="' . $this->id . '" ';
            } else {
                $formName = 'id="' . $this->id . '" ';
            }
        } else {
            $formName = null;
        }

        if (!$this->_autocomplete) {
            $autocomplete = 'autocomplete="off" ';
        } else {
            $autocomplete = null;
        }

        return '<form class="phpws-form" ' . $autocomplete . $formName . 'action="' . $this->_action . '" ' . $this->getMethod(true) . $this->_encode . '>';
    }

    function formTextField($name, $value, $size=30, $maxsize=255, $label=null)
    {
        $element = new Form_Textfield($name, $value);
        $element->setSize($size, $maxsize);
        return $element->get();
    }

    function formTextArea ($name, $value=null, $rows=DFLT_ROWS, $cols=DFLT_COLS, $label=null)
    {
        $element = new Form_TextArea($name, $value);
        $element->setRows($rows);
        $element->setCols($cols);
        return $element->get();
    }

    function formFile($name)
    {
        $element = new Form_File($name);
        $element->get();
    }


    function formRadio($name, $value, $match=null, $match_diff=null, $label=null) 
    {
        $element = new Form_RadioButton($name, $value);
        $element->setMatch($match);
        return $element->get() . ' ' . $label;
    }

    function formSubmit($value, $name=null, $class=null)
    {
        $element = new Form_Submit($name, $value);
        return $element->get();
    }

    function formSelect($name, $opt_array, $match = null, $ignore_index = false, $match_to_value = false, $onchange = null, $label = null)
    {
        $element = new Form_Select($name, $opt_array);
        $element->setMatch($match);
        if ($onchange) {
            $element->setExtra(sprintf('onchange="%s"', $onchange));
        }
        return $element->get();
    }

    function formMultipleSelect($name, $opt_array, $match = null, $ignore_index = false, $match_to_value = false, $onchange = null, $label = null)
    {
        $element = new Form_Multiple($name, $opt_array);
        $element->setMatch($match);
        if ($onchange) {
            $element->setExtra(sprintf('onchange="%s"', $onchange));
        }
        return $element->get();
    }

    function formHidden($name, $value=null)
    {
        $element = new Form_Hidden($name, $value);
        return $element->get();
    }

    function formCheckBox($name, $value = 1, $match = null, $match_diff = null, $label = null) 
    {
        $element = new Form_Checkbox($name, $value);
        $element->setMatch($match);
        return $element->get() . ' ' . $label;
    }


    function makeForm($name, $action, $elements, $method='post', $breaks=false, $file=false)
    {
        return sprintf('<form name="%s" method="%s" action="%s">%s</form>', 
                       $name, $method, $action, implode("\n", $elements));
    }

}// End of PHPWS_Form Class


class Form_TextField extends Form_Element {
    var $type = 'textfield';

    function get()
    {
        return '<input type="text" '
            . $this->getName(true) 
            . $this->getTitle(true)
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getValue(true) 
            . $this->getWidth(true)
            . $this->getClass(true)
            . $this->getData() . ' />';
    }

}

class Form_Submit extends Form_Element {
    var $type = 'submit';

    function get()
    {
        
        return '<input type="submit" '
            . $this->getName(true) 
            . $this->getValue(true) 
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getWidth(true)
            . $this->getClass(true)
            . $this->getData() . ' />';
    }

}

class Form_Button extends Form_Element {
    var $type = 'button';

    function get()
    {
        
        return '<input type="button" '
            . $this->getName(true) 
            . $this->getValue(true) 
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getWidth(true)
            . $this->getClass(true)
            . $this->getData() . ' />';
    }

}

class Form_Hidden extends Form_Element {
    var $type = 'hidden';

    function get()
    {
        return '<input type="hidden" ' 
            . $this->getName(true)
            . $this->getValue(true)
            . '/>';
    }
}

class Form_File extends Form_Element {
    var $type = 'file';

    function get()
    {
        
        return '<input type="file" '
            . $this->getName(true) 
            . $this->getTitle(true)
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getWidth(true)
            . $this->getClass(true)
            . $this->getData()
            . ' />';
    }
}

class Form_Password extends Form_Element {
    var $type = 'password';

    function Form_Password($name, $value=null)
    {
        $this->setName($name);
        $this->setValue($value);
        $this->allowValue = false;
    }

    function get()
    {
        
        return '<input type="password" '
            . $this->getName(true) 
            . $this->getTitle(true)
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getValue(true)
            . $this->getWidth(true)
            . $this->getClass(true)
            . $this->getData()
            . ' />';
    }
}

class Form_TextArea extends Form_Element {
    var $type          = 'textarea';
    var $rows          = DFLT_ROWS;
    var $cols          = DFLT_COLS;
    var $height        = null;
    var $_use_editor   = false;
    var $_limit_editor = false;
    var $_editor_dm    = null;

    function setRows($rows)
    {
        if (!is_numeric($rows) || $rows < 1 || $rows > 100) {
            return PHPWS_Error::get(PHPWS_INVALID_VALUE, 'core', 'PHPWS_Form::setRows');
        }

        $this->rows = $rows;
        return true;
    }

    function getRows($formMode=false)
    {
        if ($formMode) {
            return sprintf('rows="%s"', $this->rows);
        }
        else {
            return $this->rows;
        }
    }

    function setCols($cols)
    {
        if (!is_numeric($cols) || $cols < 1 || $cols > 100) {
            return PHPWS_Error::get(PHPWS_INVALID_VALUE, 'core', 'PHPWS_Form::setCols');
        }

        $this->cols = $cols;
        return true;
    }

    function getCols($formMode=false)
    {
        if ($formMode) {
            return sprintf('cols="%s"', $this->cols);
        }
        else {
            return $this->cols;
        }
    }

    function setHeight($height)
    {
        $this->height = $height;
    }

    function getHeight()
    {
        return $this->height;
    }

    function get()
    {
        if ($this->_use_editor && Editor::willWork()) {
            $text = PHPWS_Text::decodeText($this->value);
            $text = PHPWS_Text::encodeXHTML($text);

            $editor = new Editor($this->name, $text, $this->id);

            if ($this->_editor_dm) {
                $editor->width = (int)$this->_editor_dm[0];
                $editor->height = (int)$this->_editor_dm[1];
            }
            $editor->useLimited($this->_limit_editor);
            $result = $editor->get();
            if (!empty($result)) {
                return $result;
            }
        }

        $value = $this->getValue();

        $value = preg_replace('/<br\s?\/?>(\r\n)?/', "\n", $value);

        if (ord(substr($value, 0, 1)) == 13) {
            $value = "\n" . $value;
        }

        if (isset($this->width)) {
            $style[] = 'width : ' . $this->width;
        } else {
            $dimensions[] = $this->getCols(true);
        }

        if (isset($this->height)) {
            $style[] = 'height : ' . $this->height;
        } else {
            $dimensions[] = $this->getRows(true);
        }

        if (isset($style)) {
            $dimensions[] = 'style="' . implode('; ', $style) . '"';
        }

        return '<textarea '
            . $this->getName(true) 
            . $this->getTitle(true)
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getClass(true)
            . implode(' ', $dimensions) . ' '
            . $this->getData()
            . sprintf('>%s</textarea>', $value);
    }

}

class Form_Select extends Form_Element {
    var $type     = 'select';
    var $match    = null;
    var $optgroup = null;

    function get()
    {
        
        $content[] = '<select '
            . $this->getName(true)
            . $this->getClass(true)
            . $this->getDisabled()
            . $this->getData() . '>';

        if (empty($this->value) || !is_array($this->value)) {
            return null;
        }

        foreach($this->value as $value=>$label){
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }
            
            if ($this->optgroup && isset($this->optgroup[$value])) {
                if (isset($current_opt)) {
                    $content[] = '</optgroup>';
                }
                $current_opt = $value;
                $content[] = sprintf('<optgroup label="%s">', $this->optgroup[$value]);
            }


            if ($this->isMatch($value)) {
                $content[] = sprintf('<option value="%s" selected="selected">%s</option>', $value, $label);
            } else {
                $content[] = sprintf('<option value="%s">%s</option>', $value, $label);
            }
        }
        if (isset($current_opt)) {
            $content[] = '</optgroup>';
        }

        $content[] = '</select>';

        return implode("\n", $content);
    }

    function setOptgroup($value, $label)
    {
        $this->optgroup[$value] = $label;
    }

    function setMatch($match)
    {
        $this->match = $match;
    }

    function isMatch($match)
    {
        if (!isset($this->match)) {
            return false;
        }

        return ((string)$this->match == (string)$match) ? true : false;
    }

}

class Form_Multiple extends Form_Element {
    var $type = 'multiple';
    var $isArray = true;
    var $match = null;
    var $optgroup = null;

    function get()
    {
        $content[] = '<select ' . $this->getName(true) . 'multiple="multiple" ' 
            . $this->getData()
            . $this->getWidth(true)
            . $this->getDisabled()
            . $this->getClass(true)
            . '>';
        foreach($this->value as $value=>$label) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }

            if ($this->optgroup && isset($this->optgroup[$value])) {
                if (isset($current_opt)) {
                    $content[] = '</optgroup>';
                }
                $current_opt = $value;
                $content[] = sprintf('<optgroup label="%s">', $this->optgroup[$value]);
            }


            if ($this->isMatch($value)) {
                $content[] = sprintf('<option value="%s" selected="selected">%s</option>', $value, $label);
            }
            else {
                $content[] = sprintf('<option value="%s">%s</option>', $value, $label);
            }
        }
        if (isset($current_opt)) {
            $content[] = '</optgroup>';
        }

        $content[] = '</select>';

        return implode("\n", $content);
    }

    function setMatch($match)
    {
        if (!is_array($match)) {
            $this->match[] = $match;
        }
        else {
            $this->match = $match;
        }
    }

    function setOptgroup($value, $label)
    {
        $this->optgroup[$value] = $label;
    }

    function isMatch($match)
    {
        if (!isset($this->match)) {
            return false;
        }

        return (in_array($match, $this->match)) ? true : false;
    }

}



class Form_Checkbox extends Form_Element {
    var $match = false;
    var $type  = 'checkbox';

    function setMatch($match)
    {
        $this->match = $match;
    }

    function getMatch()
    {
        if (is_array($this->match) && in_array($this->value, $this->match)) {
            return 'checked="checked" ';
        }

        if ((string)$this->match == (string)$this->value) {
            return 'checked="checked" ';
        }
        else {
            return null;
        }
    }

    function get()
    {
        return '<input type="checkbox" ' . $this->getName(true)
            . $this->getTitle(true)
            . $this->getValue(true)
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getClass(true)
            . $this->getMatch()
            . $this->getData()
            . ' />';
    }

}


class Form_RadioButton extends Form_Element {
    var $type  = 'radio';
    var $match = false;

    function setMatch($match)
    {
        $this->match = $match;
    }

    function getMatch()
    {
        if ((string)$this->match == (string)$this->value) {
            return 'checked="checked" ';
        }
        else {
            return null;
        }
    }

    function get()
    {
        return '<input type="radio" ' . $this->getName(true)
            . $this->getTitle(true)
            . $this->getValue(true)
            . $this->getDisabled()
            . $this->getReadOnly()
            . $this->getClass(true)
            . $this->getMatch()
            . $this->getData()
            . ' />';
    }

}


class Form_Element {
    var $key         = 0;
    var $type        = null;
    var $name        = null;
    var $value       = null;
    var $disabled    = false;
    var $read_only   = false;
    var $css_class   = null;
    var $style       = null;
    var $tab         = null;
    var $width       = null;
    var $allowValue  = true;
    var $isArray     = false;
    var $tag         = null;
    var $label       = null;
    var $id          = null;
    var $title       = null;
    var $required    = false;
    var $_form       = null;

    // When multiple values are sent to an element, this variable
    // stores the position for labels and titles
    var $place       = 0;
  
    function Form_Element($name, $value=null)
    {
        $this->setName($name);
        if (isset($value)) {
            $this->setValue($value);
        }
    }

    function setDisabled($disable)
    {
        $this->disabled = (bool)$disable;
    }

    function setReadOnly($read_only)
    {
        $this->read_only = (bool)$read_only;
    }

    function getDisabled()
    {
        if ($this->disabled) {
            return 'disabled="disabled" ';
        }
        return null;
    }

    function getReadOnly()
    {
        if ($this->read_only) {
            return 'readonly="readonly" ';
        }
        return null;
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle($formMode=false)
    {
        if ($formMode) {
            if (isset($this->title)) {
                if (is_array($this->title)) {
                    $key = $this->place;
                    $title = $this->title[$key];
                } else {
                    $title = $this->title;
                }

                return sprintf('title="%s" ', $title);
            } elseif (isset($this->label)) {
                $title = $this->getLabel(true, false);
                return sprintf('title="%s" ', $title);
            } else {
                return null;
            }
        }
    }

    function allowValue()
    {
        $this->allowValue = true;
    }


    function setLabel($label)
    {
        $this->label = $label;
    }

    function setRequired()
    {
        $this->required = true;
    }

    function getRequired()
    {
        if ($this->required) {
            return '<span class="required-input">*</span>';
        } else {
            return null;
        }
    }

    function getLabel($formMode=false, $tagMode=true)
    {
        if ($formMode) {
            if (isset($this->label)) {
                if (is_array($this->label)) {
                    $key = $this->place;
                    if (isset($this->label[$key])) {
                        $label = $this->label[$key];
                    } else {
                        $label = null;
                    }
                } else {
                    $label = $this->label;
                }

                if ($tagMode) {
                    return PHPWS_Form::makeLabel($this->id, $label);
                } else {
                    return $label;
                }
            } else {
                return null;
            }
        } else {
            return $this->label;
        }
    }

    function setName($name)
    {
        $this->name = preg_replace('/[^\[\]\w]/', '', $name);
    }

    function setId()
    {
        $id = $this->getName();
        // changed 20070312
        // Square brackets are not allowed as id names.
        $id = preg_replace('/\[(\w+)\]/', '_\\1', $id);

        // changed 6/14/06
        if ($this->type == 'radio') {
            $id .= '_' . $this->key;
        }

        $this->id = $this->_form->id . '_' . $id;
    }

    function getName($formMode=false, $show_id=true)
    {
        if ($this->isArray) {
            if ($this->type == 'multiple') {
                $name = $this->name . '[]';
            } else {
                $name = $this->name . '[' . $this->key . ']';
            }
        } else {
            $name = $this->name;
        }

        if ($formMode) {
            if ($show_id) {
                $id = $this->id;
                return sprintf('name="%s" id="%s" ', $name, $id);
            }
        } else {
            return $name;
        }
    }

    function setValue($value)
    {
        $this->value = $value;
    }

    function getValue($formMode=false)
    {
        if ($formMode) {
            if ($this->allowValue) {
                return 'value="' . $this->value . '" ';
            } else {
                return null;
            }
        }
        else {
            return $this->value;
        }
    }

    function setClass($css_class)
    {
        $this->css_class = $css_class;
    }

    function getClass($formMode=false)
    {
        if ($formMode) {
            return (isset($this->css_class)) ? 'class="' . $this->css_class . '"' : null;
        }
        else {
            return $this->css_class;
        }
    }

    function setStyle($style)
    {
        $this->style = $style;
    }

    function getStyle($formMode)
    {
        if ($formMode) {
            return (isset($this->style)) ? 'style="' . $this->style . '"' : null;
        }
        else {
            return $this->style;
        }
    }

    function setTab($order)
    {
        $this->tab = (int)$order;
    }

    function getTab($formMode=false)
    {
        if ($formMode) {
            return sprintf('tabindex="%s"', $this->tab);
        }
        else {
            return $this->tab;
        }
    }

    function setExtra($extra)
    {
        $this->extra = $extra;
    }

    function getExtra()
    {
        return $this->extra;
    }

    function setSize($size, $maxsize=0)
    {
        $this->size = (int)$size;
        if ($maxsize) {
            $this->setMaxSize($maxsize);
        }
    }

    function getSize($formMode=false)
    {
        if ($formMode) {
            return 'size="' . $this->size . '" ';
        }
        else {
            return $this->size;
        }
    }

    function setMaxSize($maxsize)
    {
        $this->maxsize = (int)$maxsize;
    }

    function getMaxSize($formMode=false)
    {
        if ($formMode) {
            if (isset($this->maxsize)) {
                return 'maxlength="' . $this->maxsize . '"';
            }
            else {
                return null;
            }
        }
        else {
            return $this->maxsize;
        }
    }

    function setWidth($width)
    {
        $this->width = $width;
    }

    function getWidth($formMode=false)
    {
        if ($formMode) {
            if (isset($this->width)) {
                return 'style="width : ' . $this->width . '" ';
            }
            else {
                return null;
            }
        }
        else {
            return $this->width;
        }
    }

    function setHeight($height)
    {
        $this->height = (int)$height;
    }

    function getData()
    {
        if (isset($this->style)) {
            $extra[] = $this->getStyle(true);
        }

        if (isset($this->class)) {
            $extra[] = $this->getClass(true);
        }

        if (isset($this->extra)) {
            $extra[] = $this->getExtra();
        }

        if (isset($this->size)) {
            $extra[] = $this->getSize(true);
        }

        if (isset($this->maxsize)) {
            $extra[] = $this->getMaxSize(true);
        }

        if (isset($this->tab)) {
            $extra[] = $this->getTab(true);
        }

        if (isset($extra)) {
            return implode('', $extra);
        } else {
            return null;
        }
    }

    function setTag($tag)
    {
        $this->tag = $tag;
    }

    function getTag()
    {
        if (isset($this->tag)) {
            return strtoupper($this->tag);
        }
        else {
            $name = str_replace('][', '_', $this->name);
            $name = str_replace('[', '_', $name);
            $name = str_replace(']', '', $name);
      
            return strtoupper($name);
        }
    }

}

?>
