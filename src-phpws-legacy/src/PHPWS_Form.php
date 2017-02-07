<?php

namespace phpws;

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
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Don Seiler <don at seiler dot us>
 * @package Core
 *
 */
\phpws\PHPWS_Core::configRequireOnce('core', 'Form.php', true);

class PHPWS_Form
{

    public $id = 'phpws_form';

    /**
     * Array of form elements
     * @var    array
     * @access private
     */
    private $_elements = array();

    /**
     * Directory destination of submitted form.
     * Note: if none is provided, getTemplate will try to use the core
     * home_http directory
     *
     * @var    string
     * @access private
     */
    private $_action = null;

    /**
     * How the form is sent.
     * @var    string
     * @access private
     */
    private $_method = 'post';

    /**
     * Tells whether to multipart encode the form
     * @var    string
     * @access private
     */
    private $_encode = null;

    /**
     * Holds an extra template to merge with the final
     * @var    array
     * @access private
     */
    private $_template = null;
    public $types = array();
    public $tagReplace = array();
    public $allowFormName = false;
    public $use_auth_key = true;
    private $_autocomplete = true;
    public $max_file_size = 0;
    private $_multipart = false;

    /**
     * If true, form will use a generic fieldset to comply with XHTML
     */
    public $use_fieldset = FORM_DEFAULT_FIELDSET;
    public $legend = FORM_GENERIC_LEGEND;

    /**
     * If true, then getTemplate will print multiple valued elements
     * using the sigma template multi row format
     */
    public $row_repeat = false;

    /**
     * Indicates if a field in the form has been set as required
     */
    public $required_field = false;
    public $use_breaker = false;

    /**
     * Indicates if the form should be protected by Javascript, ie if
     * they try to leave the page, the browser prompts them.
     * True by default - this changes functionality, but really most
     * forms should be protected.  Set to false on things like the
     * login form, search form, etc where it would be confusing to
     * accidentally type something and then get an error.
     */
    public $protected = true;

    /**
     * Constructor for class
     */
    public function __construct($id = null)
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

    public function setEncode($encode = true)
    {
        if ($encode) {
            $this->_encode = ' enctype="multipart/form-data"';
        } else {
            $this->_encode = false;
        }
    }

    public function getEncode()
    {
        return ($this->_encode ? $this->_encode : '');
    }

    public function setFormId($id)
    {
        $this->id = $id;
    }

    public function useFieldset($fieldset = true)
    {
        $this->use_fieldset = (bool) $fieldset;
    }

    public function setLegend($legend)
    {
        $this->use_fieldset = true;
        $this->legend = strip_tags($legend);
    }

    public function setMaxFileSize($file_size)
    {
        if ($file_size <= 1) {
            return;
        }
        $this->max_file_size = (int) $file_size;
    }

    public function setProtected($protected = true)
    {
        $this->protected = $protected == true;
    }

    public function isProtected()
    {
        return $this->protected;
    }

    /**
     * Some browsers will not try to autocomplete a text field
     * when auto complete is turned off. This can be useful for
     * user signup if you don't want the users name saved. Again,
     * you are at the whim of the browser's settings.
     */
    public function turnOffAutocomplete()
    {
        $this->_autocomplete = false;
    }

    public function useBreaker($use_it = true)
    {
        $this->use_breaker = (bool) $use_it;
    }

    public function turnOnAutocomplete()
    {
        $this->_autocomplete = true;
    }

    public function noAuthKey()
    {
        $this->use_auth_key = false;
    }

    /**
     * Uses the template row repeat method on multiple elements
     */
    public function useRowRepeat()
    {
        $this->row_repeat = true;
    }

    public function reset()
    {
        $this->_elements = array();
        $this->_action = null;
        $this->_method = 'post';
        $this->_encode = null;
    }

    public function allowFormName()
    {
        $this->allowFormName = true;
    }

    public function getFormId()
    {
        return $this->id;
    }

    public function setMethod($method)
    {
        if ($method != 'post' && $method != 'get') {
            return;
        }
        $this->_method = $method;
    }

    public function addText($name, $value = null)
    {
        return $this->add($name, 'text', $value);
    }

    public function addTextField($name, $value = null)
    {
        return $this->add($name, 'text', $value);
    }

    public function addTextarea($name, $value = null)
    {
        return $this->add($name, 'textarea', $value);
    }

    public function addFile($name)
    {
        return $this->add($name, 'file');
    }

    public function addSubmit($name, $value = null)
    {
        if (empty($value)) {
            $value = $name;
            $name = 'submit';
        }
        return $this->add($name, 'submit', $value);
    }

    public function addReset($name, $value = null)
    {
        if (empty($value)) {
            $value = $name;
            $name = 'reset';
        }
        return $this->add($name, 'reset', $value);
    }

    public function addButton($name, $value = null)
    {
        if (empty($value)) {
            $value = $name;
            $name = 'button';
        }
        return $this->add($name, 'button', $value);
    }

    public function addPassword($name, $value = null)
    {
        return $this->add($name, 'password', $value);
    }

    public function addSelect($name, $value)
    {
        return $this->add($name, 'select', $value);
    }

    public function addDropBox($name, $value)
    {
        return $this->add($name, 'select', $value);
    }

    public function addMultiple($name, $value)
    {
        return $this->add($name, 'multiple', $value);
    }

    public function addRadio($name, $value = null)
    {
        return $this->add($name, 'radio', $value);
    }

    public function addRadioButton($name, $value = null)
    {
        return $this->add($name, 'radio', $value);
    }

    public function addRadioAssoc($name, $assoc)
    {
        if (!is_array($assoc)) {
            return false;
        }

        $this->add($name, 'radio', array_keys($assoc));
        $this->setLabel($name, $assoc);
    }

    public function addCheck($name, $value = 1)
    {
        return $this->add($name, 'check', $value);
    }

    public function addCheckBox($name, $value = 1)
    {
        return $this->add($name, 'check', $value);
    }

    public function addCheckAssoc($name, $assoc)
    {
        if (!is_array($assoc)) {
            return false;
        }

        $this->add($name, 'check', array_keys($assoc));
        $this->setLabel($name, $assoc);
    }

    public function addHidden($name, $value = null)
    {
        if (is_array($name)) {
            return $this->duplicate($name, 'hidden');
        } else {
            return $this->add($name, 'hidden', $value);
        }
    }

    public function duplicate($dup, $type)
    {
        foreach ($dup as $name => $value) {
            $result = $this->add($name, $type, $value);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Removes an element from the form
     */
    public function dropElement($name)
    {
        unset($this->_elements[$name]);
    }

    public static function getPostedDate($name)
    {
        $year = (int) date('Y');
        $month = (int) date('m');
        $day = (int) date('j');
        $hour = 0;
        $minute = 0;

        if (isset($_REQUEST[sprintf('%s_year', $name)])) {
            $year = $_REQUEST[sprintf('%s_year', $name)];
        }

        if (isset($_REQUEST[sprintf('%s_month', $name)])) {
            $month = $_REQUEST[sprintf('%s_month', $name)];
        }

        if (isset($_REQUEST[sprintf('%s_day', $name)])) {
            $day = $_REQUEST[sprintf('%s_day', $name)];
        }

        if (isset($_REQUEST[sprintf('%s_24hour', $name)])) {
            $hour = $_REQUEST[sprintf('%s_24hour', $name)];
        } elseif (isset($_REQUEST[sprintf('%s_12hour', $name)])) {
            $hour = $_REQUEST[sprintf('%s_12hour', $name)];
            if (isset($_REQUEST[sprintf('%s_ampm', $name)])) {
                $hour += 12 * (int) $_REQUEST[sprintf('%s_ampm', $name)];
            }
        }

        if (isset($_REQUEST[sprintf('%s_minute', $name)])) {
            $minute = $_REQUEST[sprintf('%s_minute', $name)];
        }

        return mktime($hour, $minute, 0, $month, $day, $year);
    }

    public static function testDate($name)
    {
        $month = @ $_REQUEST[$name . '_month'];
        $day = @ $_REQUEST[$name . '_day'];
        $year = @ $_REQUEST[$name . '_year'];
        return checkdate($month, $day, $year);
    }

    public function dateSelect($name, $current_date = 0, $month_format = '%B', $years_past = 1, $years_ahead = 3)
    {
        $allowed_month_formats = array('%b', '%B', '%m');
        if ($current_date < 1) {
            $current_date = time();
        }

        if (!in_array($month_format, $allowed_month_formats)) {
            $month_format = '%B';
        }

        if ($years_past < 0) {
            $years_past = 1;
        }

        if ($years_ahead < 0) {
            $years_ahead = 3;
        }

        $current_year = (int) date('Y');

        for ($i = $current_year - $years_past; $i <= $current_year + $years_ahead; $i++) {
            $years[$i] = $i;
        }

        for ($i = 1; $i < 13; $i++) {
            $months[strftime('%m', mktime(0, 0, 0, $i, 1, 2004))] = strftime($month_format, mktime(0, 0, 0, $i, 1, 2004));
        }

        for ($i = 1; $i < 32; $i++) {
            $day = strftime('%d', mktime(0, 0, 0, 1, $i, 2004));
            $days[$day] = $day;
        }

        for ($i = 1; $i < 13; $i++) {
            $hour = strftime('%I', mktime($i));
            $hours_12[$hour] = $hour;
        }

        for ($i = 0; $i < 24; $i++) {
            $hour = strftime('%H', mktime($i));
            $hours_24[$hour] = $hour;
        }

        for ($i = 0; $i < 60; $i += 5) {
            $minute = strftime('%M', mktime(1, $i));
            $minutes[$minute] = $minute;
        }

        $am_pm[0] = strftime('%p', mktime(1));
        $am_pm[1] = strftime('%p', mktime(15));


        $this->addSelect($name . '_year', $years);
        $this->setMatch($name . '_year', (int) strftime('%Y', $current_date));

        $this->addSelect($name . '_month', $months);
        $this->setMatch($name . '_month', (int) strftime('%m', $current_date));

        $this->addSelect($name . '_day', $days);
        $this->setMatch($name . '_day', (int) strftime('%d', $current_date));

        $this->addSelect($name . '_12hour', $hours_12);
        $this->setMatch($name . '_12hour', strftime('%I', $current_date));

        $this->addSelect($name . '_24hour', $hours_24);
        $this->setMatch($name . '_24hour', strftime('%H', $current_date));

        $this->addSelect($name . '_minute', $minutes);
        $this->setMatch($name . '_minute', strftime('%M', $current_date));

        $am_pm_match = (int) strftime('%H', $current_date) < 12 ? 0 : 1;

        $this->addSelect($name . '_ampm', $am_pm);
        $this->setMatch($name . '_ampm', $am_pm_match);
    }

    /**
     * Adds a form element to the class
     *
     * The type and value parameters are optional, though it is a timesaver.
     * See setType for the form types.
     * See setValue for value information.
     *
     * @author             Matthew McNaney <mcnaney at gmail dot com>
     * @param string name  The name of the form element
     * @param string type  The type of form element (text, check, radio, etc)
     * @param mixed  value The default value of the form element
     */
    public function add($name, $type = null, $value = null)
    {
        if (preg_match('/[^\[\]\w]+/i', $name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_BAD_NAME, 'core', 'PHPWS_Form::add', array($name));
        }
        $result = PHPWS_Form::createElement($name, $type, $value);

        if (\phpws\PHPWS_Error::isError($result)) {
            return $result;
        }

        if (is_array($result)) {
            foreach ($result as $element) {
                if ($type != 'radio') {
                    $element->isArray = true;
                }

                $element->_form = $this;

                $this->_elements[$name][$element->value] = $element;
                $this->_elements[$name][$element->value]->key = $element->value;
                $this->_elements[$name][$element->value]->setId();
            }
        } else {
            if (isset($this->_elements[$name])) {
                $this->_elements[$name][0]->isArray = true;
                $result->isArray = true;
            }
            $result->_form = $this;
            $this->_elements[$name][] = $result;

            $current_key = $this->getKey($name);
            $this->_elements[$name][$current_key]->key = $current_key;
            $this->_elements[$name][$current_key]->setId();
        }

        $this->types[$name] = $type;
        return true;
    }

    public function getKey($name)
    {
        end($this->_elements[$name]);
        $current_key = key($this->_elements[$name]);
        return $current_key;
    }

    public function useEditor($name, $value = true, $limited = false, $width = 0, $height = 0, $force_name = null)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::useEditor', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if ($this->_elements[$name][$key]->type != 'textarea') {
                break;
            }
            $this->_elements[$name][$key]->use_editor = $value;
            $this->_elements[$name][$key]->limit_editor = $limited;
            if ($width > 100 && $height > 100) {
                $this->_elements[$name][$key]->_editor_dm = array($width, $height);
            }
            if (!empty($force_name)) {
                $this->_elements[$name][$key]->_force_name = $force_name;
            }
        }
    }

    public function setValue($name, $value)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setValue', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setValue($value);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Allows you to set an HTML5 Placeholder on an input element
     * Note: Our implementation allows you to set a placeholder on any
     * legal Form_Element, although in practice, it is really only
     * useful on text inputs and will be ignored in all other cases.
     */
    public function setPlaceholder($name, $placeholder)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setPlaceholder', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setPlaceholder($placeholder);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Sets an element's disabled status
     */
    public function setDisabled($name, $value = true)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setDisabled', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setDisabled($value);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Sets all elements in this form to disabled by default. If $value = false, all elements will be enabled.
     *
     * @param boolean $value - True (default) to disable elements. False to enable elements.
     */
    public function setAllDisabled($value = true)
    {
        foreach ($this->_elements as $key => $element) {
            $this->setDisabled($key, $value);
        }
    }

    public function setAutoComplete($name, $value = false)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setAutoComplete', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if ($this->_elements[$name][$key]->type == 'password') {
                $result = $this->_elements[$name][$key]->setAutoComplete($value);
                if (\phpws\PHPWS_Error::isError($result)) {
                    return $result;
                }
            }
        }
    }

    /**
     * Sets an element's readonly status
     */
    public function setReadOnly($name, $value = true)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setReadonly', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setReadOnly($value);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function makeLabel($element, $label)
    {
        $required = $element->getRequired();
        return sprintf('<label class="%s-label" id="%s-label" for="%s">%s</label>%s', $element->type, $element->id, $element->id, $label, $required);
    }

    public function setOptgroup($name, $value, $label)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setOptgroup', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if ($element->type != 'select' && $element->type != 'multiple') {
                continue;
            }
            $result = $this->_elements[$name][$key]->setOptgroup($value, $label);

            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function setLabel($name, $label = null)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setLabel', array($name));
        }

        if (empty($label)) {
            $label = $name;
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if (is_array($label) && isset($label[$element->key])) {
                $result = $this->_elements[$name][$key]->setLabel($label[$element->key]);
            } else {
                $result = $this->_elements[$name][$key]->setLabel($label);
            }

            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Adds a prefix to a label to indicate it is a required field
     */
    public function setRequired($name, $required = true)
    {
        if (is_array($name)) {
            foreach ($name as $sub) {
                \phpws\PHPWS_Error::logIfError($this->setRequired($sub, $required));
            }
            return true;
        }

        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setLabel', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setRequired($required);

            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function getId($name)
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

    public function addTplTag($tag, $data)
    {
        $this->_template[$tag] = $data;
    }

    /**
     * Removes a form element from the class
     * @author Matthew McNaney <mcnaney at gmail dot com>
     */
    public function drop($name)
    {
        unset($this->_elements[$name]);
    }

    /**
     * Allows you to enter extra information to an element.
     *
     * This is useful for style components, javascript, etc.
     *
     * @author            Matthew McNaney <mcnaney at gmail dot com>
     * @param string name Name of element to set the type
     * @param string type Extra text to add to element
     */
    public function setExtra($name, $extra)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setExtra', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setExtra($extra);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Adds an extra tag (or any arbitrary string) to the HTML output
     * for the given element name.
     *
     * @param string $name Element Name
     * @param string $tag String/tag to add.
     * @return boolean
     */
    public function addExtraTag($name, $tag)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setExtra', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->addExtraTag($tag);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author             Matthew McNaney <mcnaney at gmail dot com>
     * @param string name  Name of element to set the type
     * @param string width Percentage of width wanted on element
     */
    public function setWidth($name, $width)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setWidth', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setWidth($width);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author              Matthew McNaney <mcnaney at gmail dot com>
     * @param string name   Name of element to set the type
     * @param string height Percentage of height wanted on element
     */
    public function setHeight($name, $height)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setHeight', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setHeight($height);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author Matthew McNaney <mcnaney at gmail dot com>
     * @param string name Name of element to set the rows
     * @param string rows Number rows to use in a textarea
     */
    public function setRows($name, $rows)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setRows', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setRows($rows);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author Matthew McNaney <mcnaney at gmail dot com>
     * @param string name  Name of element to set the rows
     * @param string title Title text
     */
    public function setTitle($name, $title)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setTitle', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setTitle($title);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author Matthew McNaney <mcnaney at gmail dot com>
     * @param string name Name of element to set the rows
     * @param string rows Number columns to use in a textarea
     */
    public function setCols($name, $cols)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setCols', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setCols($cols);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author               Matthew McNaney <mcnaney at gmail dot com>
     * @param  string  name  Name of element to set the type
     * @param  integer order Numeric order of tab queue
     */
    public function setTab($name, $order)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setTab', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setTab($order);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Sets the number of characters for text boxes, number of rows
     * for select boxes
     *
     * @author Matthew McNaney <mcnaney at gmail dot com>
     * @param string name Name of element to set the type
     * @param string size Size to make the element
     */
    public function setSize($name, $size, $maxsize = null)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setSize', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setSize((int) $size);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
            if (!empty($maxsize)) {
                $result = $this->_elements[$name][$key]->setMaxSize((int) $maxsize);
            }
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Allows the password value to appear in the form.
     */
    public function allowValue($name)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setSize', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->allowValue();
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author                Matthew McNaney <mcnaney at gmail dot com>
     * @param string name     Name of element to set the type
     * @param string template Name of template tag to print for this element
     */
    public function setTag($name, $tag)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setTag', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setTag($tag);
            if (\phpws\PHPWS_Error::isError($result)) {
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
     * @author                    Matthew McNaney <mcnaney at gmail dot com>
     * @param string name         Name of element to set the type
     */
    public function reindexValue($name)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::reindexValue', array($name));
        }

        if ($this->types[$name] != 'multiple' && $this->types[$name] != 'select') {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, 'core', 'PHPWS_Form::reindexValue');
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if (empty($element->value)) {
                continue;
            }

            $newValueArray = array_combine($element->value, $element->value);
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
     * @author                    Matthew McNaney <mcnaney at gmail dot com>
     * @param string name         Name of element to set the type
     * @param string match        Value to match against the element's value
     * @param boolean optionMatch If true, then a select box will try to match
     *                            the value to the option not the value
     */
    public function setMatch($name, $match, $optionMatch = false)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setMatch', array($name));
        }

        if ($this->types[$name] == 'multiple' && !is_array($match)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_WRONG_ELMT_TYPE, 'core', 'PHPWS_Form::reindexValue');
        }

        foreach ($this->_elements[$name] as $key => $element) {
            if ($this->_elements[$name][$key]->type == 'hidden') {
                continue;
            }
            $result = $this->_elements[$name][$key]->setMatch($match);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function setClass($name, $class_name)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setClass', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setClass($class_name);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Adds a CSS class to the given element name. Will not overwrite
     * previously added classes.
     *
     * @param string $name Element name
     * @param string $className CSS class name
     */
    public function addCssClass($name, $className)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setClass', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->addCssClass($className);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function setId($name, $id_name)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setId', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setId($id_name);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function setStyle($name, $style_name)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setStyle', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setStyle($style_name);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    /**
     * Sets the max text size for a text, password, file element
     *
     * @author Matthew McNaney<mcnaney at gmail dot com>
     * @param string  name Name of element to set the maxsize
     * @param integer maxsize The max number of characters allowed in the element's field
     */
    public function setMaxSize($name, $maxsize)
    {
        if (!$this->testName($name)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::setMaxSize', array($name));
        }

        foreach ($this->_elements[$name] as $key => $element) {
            $result = $this->_elements[$name][$key]->setMaxSize($maxsize);
            if (\phpws\PHPWS_Error::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    /**
     * Merges another template array into the one created
     * by the form
     */
    public function mergeTemplate($template)
    {
        if (!is_array($template)) {
            return;
        }

        if (!isset($this->_template)) {
            $this->_template = $template;
        } else {
            $this->_template = array_merge($this->_template, $template);
        }
    }

    /**
     * Indicates whether an element exists
     *
     * @author         Matthew McNaney<mcnaney at gmail dot com>
     * @param  string  name Name to check if exists
     * @return boolean true if the element exists, false otherwise
     */
    public function testName($name)
    {
        return isset($this->_elements[$name]);
    }

    public function createElement($name, $type, $value)
    {
        switch ($type) {
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
                if ($name == 'submit') {
                    // prevents problems with setRequires javascript
                    $obj->name = 'submit_form';
                    $obj->tag = 'SUBMIT';
                }
                return $obj;
                break;

            case 'button':
                $obj = new Form_Button($name, $value);
                return $obj;
                break;

            case 'reset':
                $obj = new Form_Reset($name, $value);
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
                    foreach ($value as $key => $sub) {
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
                    $check_count = 0;
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
                    if (empty($value)) {
                        $value = null;
                        $obj = new Form_Hidden($name, $value);
                        return $obj;
                    } else {
                        foreach ($value as $key => $sub) {
                            $hidden = new Form_Hidden($name, $sub);
                            $hidden->setId();
                            $hidden->isArray = true;
                            $hidden->key = $sub;
                            $hidden->place = $key;
                            $allHidden[$sub] = $hidden;
                        }
                        return $allHidden;
                    }
                } else {
                    $obj = new Form_Hidden($name, $value);
                    return $obj;
                }
                break;

            default:
                $error = \phpws\PHPWS_Error::get(PHPWS_FORM_UNKNOWN_TYPE, 'core', 'PHPWS_Form::createElement');
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
     * @author                   Matthew McNaney<mcnaney at gmail dot com>
     * @param  string  directory Directory that a form will post to
     */
    public function setAction($directory)
    {
        $this->_action = $directory;
    }

    public function get($name, $all = false)
    {
        if (!isset($this->_elements[$name])) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_MISSING_NAME, 'core', 'PHPWS_Form::get', array($name));
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
            foreach ($this->_elements[$name] as $key => $element) {
                $content['elements'][$key] = $element->get($key);
                $content['labels'][$key] = $element->getLabel(true, true);
            }
            return $content;
        }
    }

    public function grab($name)
    {
        if (count($this->_elements[$name]) > 1 || !isset($this->_elements[$name][0])) {
            return $this->_elements[$name];
        } else {
            return $this->_elements[$name][0];
        }
    }

    public function replace($name, $elements)
    {
        $this->_elements[$name] = array();

        if (is_array($elements)) {
            $this->_elements[$name] = $elements;
        } else {
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
     * @author                     Matthew McNaney<mcnaney at gmail dot com>
     * @param  boolean phpws       If true and the action is missing, phpWebSite will attempt to use your directory settings instead
     * @param  boolean helperTags  If true START and END_FORM tags will be created, otherwise they will not
     * @param  array   template    If a current template is supplied, form will add to it.
     * @return array   template    Array of completed form
     */
    public function getTemplate($phpws = true, $helperTags = true, $template = null)
    {
        if (count($this->_elements) < 1) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_NO_ELEMENTS, 'core', 'PHPWS_Form::getTemplate');
        }

        if (!is_null($template) && !is_array($template)) {
            return \phpws\PHPWS_Error::get(PHPWS_FORM_NO_TEMPLATE, 'core', 'PHPWS_Form::getTemplate');
        }

        if ($helperTags) {
            $template['START_FORM'] = $this->getStart() . "\n";
            if ($this->use_fieldset) {
                $template['START_FORM'] .= "<fieldset class=\"phpws-form-container\">\n";
                $template['START_FORM'] .= '<legend>' . $this->legend . "</legend>\n";
            } else {
                $template['START_FORM'] .= "<div class=\"phpws-form-container\">\n";
            }

            if (FORM_USE_FILE_RESTRICTIONS && $this->_multipart) {
                $template['START_FORM'] .= sprintf('<input type="hidden" name="MAX_FILE_SIZE" value="%d" />', $this->max_file_size) . "\n";
            }
        }

        $template['FORM_ACTION'] = $this->getFormAction();
        $template['FORM_ID'] = $this->getFormId();
        $template['FORM_NAME'] = $this->getFormName();
        $template['FORM_AUTOCOMPLETE'] = $this->getAutocompleteValue();
        $template['FORM_CLASS'] = $this->getFormClass();
        $template['FORM_METHOD'] = $this->getMethod();
        $template['FORM_ENCODE'] = $this->getEncode();

        unset($this->_elements['authkey']);
        if (class_exists('\Current_User') && $this->use_auth_key) {
            if ($authkey = \Current_User::getAuthKey()) {
                $this->addHidden('authkey', $authkey);
            }
        }

        foreach ($this->_elements as $elementName => $element) {
            $multiple = false;
            $count = 1;
            $mult_count = 0;

            if (count($element) > 1) {
                $multiple = true;
            }

            if ($this->required_field) {
                $template['REQUIRED_LEGEND'] = '<span class="required-input">*</span> ' . _('Required field');
            }

            foreach ($element as $subElement) {
                $subtpl = array();

                if ($this->types[$elementName] == 'hidden') {
                    if ($helperTags) {
                        $template['START_FORM'] .= $subElement->get() . "\n";
                        if (!isset($template['HIDDEN_FIELDS']))
                            $template['HIDDEN_FIELDS'] = '';
                        $template['HIDDEN_FIELDS'] .= $subElement->get() . "\n";
                    } else {
                        $hidden_vars[] = $subElement->get();
                    }
                    continue;
                }

                $tagName = $subElement->getTag();
                $label = $subElement->getLabel(true);
                $labelText = $subElement->getLabel(false);

                if ($this->row_repeat && $multiple) {
                    if (!empty($label)) {
                        $subtpl[$tagName . '_LABEL'] = $label;
                        $subtpl[$tagName . '_LABEL_TEXT'] = $labelText;
                    }
                    $subtpl[$tagName] = $subElement->get();
                    $subtpl[$tagName . '_ID'] = $subElement->getId();
                    $template[strtolower($tagName) . '_repeat'][] = $subtpl;
                    continue;
                }

                if ($multiple) {
                    $tagName .= "_$count";
                }

                if (!empty($label)) {
                    $template[$tagName . '_LABEL'] = $label;
                    $template[$tagName . '_LABEL_TEXT'] = $labelText;
                }

                $template[$tagName . '_ID'] = $subElement->getId();
                $template[$tagName . '_VALUE'] = $subElement->value; // NB: Calling 'getValue()' gives 'value="myValue"'...
                $template[$tagName . '_NAME'] = $subElement->getName();

                $template[$tagName] = $subElement->get();
                $count++;
            }
        }

        if ($helperTags) {
            if ($this->use_fieldset) {
                $end_form[] = '</fieldset>';
            } else {
                $end_form[] = '</div>';
            }
            $end_form[] = '</form>';
            $template['END_FORM'] = implode("\n", $end_form);
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

    public function getMerge()
    {
        $form = $this->getTemplate();
        return implode("\n", $form);
    }

    public function getMethod($tagMode = false)
    {
        if ($tagMode == true) {
            return 'method="' . $this->_method . '"';
        } else {
            return $this->_method;
        }
    }

    public function getFormAction()
    {
        $multipart = '';
        if ($this->_multipart) {
            $multipart = '?check_overpost=1';
        }

        if (!isset($this->_action)) {
            return 'index.php' . $multipart;
        }

        return $this->_action . $multipart;
    }

    public function getFormName()
    {
        if ($this->allowFormName) {
            return $this->getFormId();
        }

        return null;
    }

    public function getAutocompleteValue()
    {
        return $this->_autocomplete ? 'on' : 'off';
    }

    public function getFormClass()
    {
        $class = ' ';
        if (function_exists('javascript') && $this->protected) {
            $class .= 'form-protected ';
        }

        return $class;
    }

    /**
     * Provided for compatibility.  Any new forms should use the
     * individual elements of this function instead:
     */
    public function getStart()
    {
        $action = $this->getFormAction();
        $id = $this->getFormId();
        $name = $this->getFormName();
        $autocomplete = $this->getAutocompleteValue();
        $class = $this->getFormClass();
        $method = $this->getMethod();
        $encode = $this->getEncode();

        if (function_exists('javascript') && $this->required_field) {
            javascript('jquery');
            javascript('required_input');
        }

        return '<form ' .
                'class="phpws-form' . $class . '" ' .
                'autocomplete="' . $autocomplete . '" ' .
                ($name ? 'name="' . $name . '" ' : '') .
                'id="' . $id . '" ' .
                'action="' . $action . '" ' .
                'method="' . $method . '" ' .
                ($encode ? $encode . ' ' : '') .
                '>';
    }

    public static function formTextField($name, $value, $size = 30, $maxsize = 255, $label = null)
    {
        $element = new Form_TextField($name, $value);
        $element->setSize($size, $maxsize);
        return $element->get();
    }

    public static function formTextArea($name, $value = null, $rows = DFLT_ROWS, $cols = DFLT_COLS, $label = null, $class = 'form-control')
    {
        $element = new Form_TextArea($name, $value);
        $element->setRows($rows);
        $element->setCols($cols);
        $element->setClass($class);
        return $element->get();
    }

    public static function formFile($name)
    {
        $element = new Form_File($name);
        $element->get();
    }

    public static function formRadio($name, $value, $match = null, $match_diff = null, $label = null)
    {
        $element = new Form_RadioButton($name, $value);
        $element->setMatch($match);
        return $element->get() . ' ' . $label;
    }

    public static function formSubmit($value, $name = null, $class = null)
    {
        $element = new Form_Submit($name, $value);
        return $element->get();
    }

    public static function formSelect($name, $opt_array, $match = null, $ignore_index = false, $match_to_value = false, $onchange = null, $label = null)
    {
        $element = new Form_Select($name, $opt_array);
        $element->setMatch($match);
        if ($onchange) {
            $element->setExtra(sprintf('onchange="%s"', $onchange));
        }
        return $element->get();
    }

    public static function formMultipleSelect($name, $opt_array, $match = null, $ignore_index = false, $match_to_value = false, $onchange = null, $label = null)
    {
        $element = new Form_Multiple($name, $opt_array);
        $element->setMatch($match);
        if ($onchange) {
            $element->setExtra(sprintf('onchange="%s"', $onchange));
        }
        return $element->get();
    }

    public static function formHidden($name, $value = null)
    {
        $element = new Form_Hidden($name, $value);
        return $element->get();
    }

    public static function formCheckBox($name, $value = 1, $match = null, $match_diff = null, $label = null)
    {
        $element = new Form_Checkbox($name, $value);
        $element->setMatch($match);
        return $element->get() . ' ' . $label;
    }

    public static function makeForm($name, $action, $elements, $method = 'post', $breaks = false, $file = false)
    {
        return sprintf('<form name="%s" method="%s" action="%s">%s</form>', $name, $method, $action, implode("\n", $elements));
    }

    /**
     * Accepts an associative array or object. Looks for form elements with the
     * same names as the variables in the object or the keys in the array. If
     * matching elements are found, their value, match, or checked parameters
     * are set based upon the element type.
     */
    public function plugIn($values)
    {
        if (is_object($values)) {
            $aVal = \phpws\PHPWS_Core::stripObjValues($values);
        } else {
            $aVal = & $values;
        }
        if (empty($aVal) || !is_array($aVal)) {
            return false;
        }

        foreach ($aVal as $name => $element) {

            if (!isset($this->types[$name])) {
                continue;
            }

            $element_type = $this->types[$name];
            if (!empty($element_type)) {
                switch ($element_type) {
                    case 'hidden':
                    case 'text':
                    case 'textarea':
                    case 'submit':
                    case 'button':
                        $this->setValue($name, $element);
                        break;

                    case 'select':
                    case 'multiple':
                    case 'radio':
                    case 'check':
                        $this->setMatch($name, $element);
                        break;
                }
            }
        }
    }

}
