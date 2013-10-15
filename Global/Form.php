<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
// @todo drop if not used
//define('INT_FORM_DROP_LIMIT', 25);

/**
 * This class is used to assist in the creation of a HTML form.
 */
class Form extends Tag {

    /**
     * Array of input objects
     * @var array
     */
    private $inputs = array();

    /**
     * The url target of the form
     * @var string
     */
    protected $action;

    /**
     * The method (get/post) in which the form should sent.
     * @var string
     */
    protected $method = 'post';

    /**
     * Indicates if the form is being posted via Ajax.
     * @var boolean
     */
    private $ajax_post = false;

    /**
     * If true, then __toString will print all inputs with their labels
     * REGARDLESS of the input's print_label setting
     * @var boolean
     */
    private $print_labels = true;

    /**
     * Names of classes used for the div blocks
     * @var string
     */
    private $group_class = array();

    /**
     * Controls how the form-data should be encoded when submitted.
     * @var string
     */
    protected $enctype = null;
    protected static $css_addition;

    const enctype_application = 1;
    const enctype_multipart = 2;
    const enctype_text = 3;

    /**
     *
     * @staticvar int $default_id  Helps in the creation of form ids if none is set
     */
    public function __construct()
    {
        parent::__construct('form');
        $this->action = \Server::getCurrentUrl();
        static $default_id = 1;
        $this->setId('form-' . $default_id);
        $default_id++;
        $this->addClass('phpws-form');
    }

    private function loadCSSAddition()
    {
        $filename = PHPWS_SOURCE_DIR . 'themes/' . self::$css_addition . '/form_css.php';
        if (is_file($filename)) {
            include $filename;
            if (!empty($input_classes) && is_array($input_classes)) {
                foreach ($input_classes as $i) {
                    $this->addInputClass($i);
                }
            }
            if (!empty($button_classes) && is_array($button_classes)) {
                foreach ($button_classes as $i) {
                    $this->addButtonClass($i);
                }
            }
            if (!empty($label_classes) && is_array($label_classes)) {
                foreach ($label_classes as $l) {
                    $this->addLabelClass($l);
                }
            }
            if (!empty($group_classes) && is_array($group_classes)) {
                foreach ($group_classes as $g) {
                    $this->addGroupClass($g);
                }
            }
        }
    }

    public static function appendCSS($addition)
    {
        self::$css_addition = $addition;
    }

    /**
     *
     * @param type $type
     * @throw Exception If the wrong enctype is set
     */
    public function setEnctype($type)
    {
        switch ($type) {
            case NULL:
                $this->enctype = null;
                break;
            case self::enctype_application:
                $this->enctype = 'application/x-www-form-urlencoded';
                break;
            case self::enctype_multipart:
                $this->enctype = 'multipart/form-data';
                break;
            case self::enctype_text:
                $this->enctype = 'text/plain';
                break;
            default:
                throw new Exception('Unknown enctype');
        }
    }

    /**
     * Returns the string format (not the const) of the form enctype.
     * @return string
     */
    public function getEnctype()
    {
        return $this->enctype;
    }

    /**
     * Creates an input object based on the type parameter, adds it to the
     * form input queue, and returns the resultant object.
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param string $label
     * @return object
     */
    public function addInput($type, $name, $value = null, $label = null)
    {
        if (!is_string_like($name) || preg_match('/[^\w\-\[\]]/', $name)) {
            throw new \Exception(t('Improperly formatted input name: %s', $name));
        }

        $class_name = 'Form\Input\\' . ucfirst(strtolower($type));
        $input = new $class_name($name, $value);
        if (isset($label)) {
            $input->setLabel($label);
        }
        $this->inputs[$name][] = $input;
        return $input;
    }

    /**
     * @param boolean $post
     */
    public function setAjaxPost($post)
    {
        $this->ajax_post = (bool) $post;
    }

    /**
     * Adds a Select object to the form input queue.
     * @see \Form\Choice\Select
     * @param string $name Name of select input
     * @param array $value Associate array of values; key as value, value as output
     * @param string $label Label associated to input
     * @return \Form\Choice\Select
     */
    public function addSelect($name, array $value = null, $label = null)
    {
        if (preg_match('/[^\w\-\[\]]/', $name)) {
            throw new \Exception(t('Improperly formatted input name'));
        }
        $select = new \Form\Choice\Select($name, $value);
        if (!empty($label)) {
            $select->setLabel($label);
        }
        $this->inputs[$name][] = $select;
        return $select;
    }

    /**
     * Creates a multiple choice select input and adds it to form object.
     * @see \Form\Choice\Select
     * @param string $name Name of select input
     * @param array $value Associate array of values; key as value, value as output
     * @param string $label  Label associated to input
     * @return \Form\Choice\Multiple
     */
    public function addMultiple($name, array $value = null, $label = null)
    {
        if (preg_match('/[^\w\-\[\]]/', $name)) {
            throw new \Exception(t('Improperly formatted input name'));
        }
        $multiple = new \Form\Choice\Multiple($name, $value);
        if (!empty($label)) {
            $multiple->setLabel($label);
        }
        $this->inputs[$name][] = $multiple;
        return $multiple;
    }

    /**
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @return  \Form\Input\Hidden
     */
    public function addHidden($name, $value)
    {
        return $this->addInput('hidden', $name, $value);
    }

    /**
     *
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @param string $label
     * @return \Form\Choice\Radio
     */
    public function addRadio($name, $value, $label = null)
    {
        return $this->addInput('radio', $name, $value, $label);
    }

    /**
     * Creates a checkbox field input object
     *
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @param string $label
     * @return \Form\Input\Checkbox
     */
    public function addCheckbox($name, $value, $label = null)
    {
        return $this->addInput('checkbox', $name, $value, $label);
    }

    /**
     * Creates a text field input object
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @param string $label
     * @return \Form\Input\Text
     */
    public function addTextField($name, $value = null, $label = null)
    {
        return $this->addInput('text', $name, $value, $label);
    }

    /**
     *
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @param string $label
     * @return \Form\Input\Textarea
     */
    public function addTextArea($name, $value = null, $label = null)
    {
        return $this->addInput('textarea', $name, $value, $label);
    }

    /**
     * Creates a submit button
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @return \Form\Input\Submit
     */
    public function addSubmit($name = null, $value = null)
    {
        if (empty($name)) {
            $name = 'submit';
        }

        if (empty($value)) {
            $value = t('Submit');
        }
        return $this->addInput('submit', $name, $value);
    }

    /**
     * Creates a button that doesn't submit the form
     * @see Form::addInput()
     * @param string $name
     * @param string $value
     * @return \Form\Input\Button
     */
    public function addButton($name, $value)
    {
        return $this->addInput('button', $name, $value);
    }

    /**
     * Adds an Email input
     * @param string $name
     * @param string $value
     * @return \Form\Input\Email
     */
    public function addEmail($name, $value = null)
    {
        return $this->addInput('email', $name, $value);
    }

    /**
     * Adds an Date input
     * @param string $name
     * @param string $value
     * @return \Form\Input\Email
     */
    public function addDate($name, $value = null)
    {
        return $this->addInput('date', $name, $value);
    }

    /**
     * Adds an Datetime input
     * @param string $name
     * @param string $value
     * @return \Form\Input\Email
     */
    public function addDatetime($name, $value = null)
    {
        return $this->addInput('datetime', $name, $value);
    }

    /**
     * Adds an Color input
     * @param string $name
     * @param string $value
     * @return \Form\Input\Email
     */
    public function addColor($name, $value = null)
    {
        return $this->addInput('color', $name, $value);
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @param string $label
     * @return \Form\Input\File
     */
    public function addFile($name, $value = null, $label = null)
    {
        return $this->addInput('file', $name, $value, $label);
    }

    /**
     * Value is included though not really used
     * @param string $name
     * @param string $value
     * @param string $label
     * @return \Form\Input\Password
     */
    public function addPassword($name, $value = null, $label = null)
    {
        return $this->addInput('password', $name, $value, $label);
    }

    public function includeCSS()
    {
        \Layout::addToStyleList('Global/Templates/Form/style.css');
    }

    /**
     * Prints out a rudimentary form based on the inputs in the queue.
     * A form toString will ALWAYS print the label if the input has one.
     * Button and submit has_label variables are false.
     * @return string
     */
    public function __toString()
    {
        $this->includeCSS();
        $text = null;
        if (!empty(self::$css_addition)) {
            $this->loadCSSAddition();
        }

        if (empty($this->id)) {
            $this->loadId();
        }
        if (!empty($this->inputs)) {
            $value = array();
            foreach ($this->inputs as $input_list) {
                foreach ($input_list as $input) {
                    if ($input->getType() == 'hidden') {
                        $hiddens[] = $input->__toString();
                    } else {
                        $value[] = $input->printWithLabel();
                    }
                }
            }
            if (!empty($hiddens)) {
                $text .= implode("\n", $hiddens);
            }
            $group_classes = implode(' ', $this->group_class);
            $text .= "\n<div class=\"$group_classes\">" . implode("</div>\n<div class=\"$group_classes\">",
                            $value) . "</div>\n";
            $this->setText($text);
        }
        $result = parent::__toString();

        if ($this->ajax_post) {
            $form_post = Javascript::factory('form_post');
            $form_post->parseForm($this);
        }

        return $result;
    }

    /**
     * Sets the action url.
     * @param string $action
     */
    public function setAction($action)
    {
        if (!preg_match('/^http:/i', $action)) {
            $action = \Server::getSiteUrl() . $action;
        }
        $this->action = $action;
    }

    /**
     * @see Form::$action
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Creates form inputs based on variables in passed object.
     * Assumes variables are of the Variable class or subclass.
     * @param object $object
     */
    public function addObject($object)
    {
        $plug = false;
        if (!is_object($object)) {
            throw new \Exception(t('Variable passed is not an object'));
        }
        $variables = get_object_vars($object);
        if (empty($variables)) {
            throw new \Exception(t('No public variables were found in object'));
        }

        foreach ($variables as $var_obj) {
            if (is_object($var_obj) && is_subclass_of($var_obj, 'Variable')) {
                $plug = true;
                $this->plugInput($var_obj->getInput());
            }
        }
        if (!$plug) {
            throw new \Exception(t('None of the current object\'s variables were of subclass "Variable"'));
        }
    }

    /**
     * Returns an associative array containing all the elements of the form.
     * @return array
     */
    public function getInputStringArray()
    {
        $this->includeCSS();
        if (!empty(self::$css_addition)) {
            $this->loadCSSAddition();
        }

        $value['form_start'] = str_replace('</form>', '', parent::__toString());
        $value['form_end'] = '</form>';
        if (!empty($this->inputs)) {
            foreach ($this->inputs as $input_name => $input_list) {
                $multiple = count($input_list) > 1;
                foreach ($input_list as $input) {
                    if ($input->getType() == 'hidden') {
                        $value['hidden'][] = $input->__toString();
                    } else {
                        if ($input->getLabelLocation()) {
                            $label = $input_name . '_label';
                            if ($multiple) {
                                $value[$label][] = $input->getLabel();
                            } else {
                                $value[$label] = $input->getLabel();
                            }
                            $group_label = $input->getLabel();
                        } else {
                            $group_label = null;
                        }
                        if ($multiple) {
                            $value[$input_name][] = $input->__toString();
                        } else {
                            $value[$input_name] = $input->__toString();
                        }
                        $group_name = $input_name . '_group';
                        $groups[$group_name][] = $group_label . ' ' . $input->__toString();
                    }
                }
            }
        }
        foreach ($groups as $gname => $g) {
            $gclass = str_replace('_', '-', $gname);
            $value[$gname] = '<div class="' . $gclass . ' ' . implode("\n",
                            $this->group_class) . '">' . implode("\n", $g) . '</div>';
        }
        if (isset($value['hidden'])) {
            $value['form_start'] .= "\n" . implode("\n", $value['hidden']);
        }
        return $value;
    }

    /**
     * Imports the elements of the form object into a template file and
     * returns the output
     *
     * @param string $template File location of template file
     * @return string
     */
    public function printTemplate($template)
    {
        if (empty($this->id)) {
            $this->loadId();
        }

        $value = $this->getInputStringArray();

        if ($this->ajax_post) {
            $form_post = Javascript::factory('form_post');
            $form_post->parseForm($this);
        }

        ob_start();
        extract($value);
        include $template;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Pushes an Input or Choice object on to the Form input queue.
     * @param object $input
     */
    public function plugInput($input)
    {
        if (!$input instanceof \Form\Base) {
            throw new \Exception(t('plugInput only accepts Input and Choice class objects'));
        }
        $this->inputs[$input->getName()][] = $input;
    }

    /**
     * Pulls the input object from a Variable object and pushes it on to the
     * Form input queue.
     * @see Variable::getInput()
     * @param Variable $variable
     */
    public function addVariable(Variable $variable)
    {
        $this->plugInput($variable->getInput());
    }

    /**
     * Sets the print_labels conditional
     * @param boolean $print_labels
     */
    public function setPrintLabels($print_labels)
    {
        $this->print_labels = (bool) $print_labels;
    }

    /**
     * @see Form::$print_labels
     * @return boolean True is print_labels is activated
     */
    public function getPrintLabels()
    {
        return $this->print_labels;
    }

    public function pullInputs()
    {
        return $this->inputs;
    }

    /**
     * Returns an array of input (\Form\Choice or \Form\Input) objects from the
     * Form if it has been previously set.
     *
     * If you expect only one answer then:
     * $input = $form->getInput('first_name')[0]; (php 5.4 and above only)
     * or
     * $input = current($form->getInput('first_name')); (php 5.3 and under)
     *
     * See also __get.
     *
     * @param string $name
     * @return array Array of all \Form\Base extended objects under the passed name.
     * @throws \Exception
     */
    public function getInput($name)
    {
        if (!isset($this->inputs[$name])) {
            throw new \Exception(t('Input "%s" does not exist', $name));
        }
        return $this->inputs[$name];
    }

    /**
     * Like getInput BUT only returns a single input. If the input requested via
     * the name parameter has multiple objects, an exception will be thrown instead.
     *
     * See also __get.
     *
     * @param string $name
     * @return \Form\Base
     * @throws \Exception
     */
    public function getSingleInput($name)
    {
        if (!isset($this->inputs[$name])) {
            throw new \Exception(t('Input "%s" does not exist', $name));
        }
        if (count($this->inputs[$name]) > 1) {
            throw new \Exception(t('Input "%s" contains multiple objects', $name));
        }
        return $this->inputs[$name][0];
    }

    public function useGetMethod()
    {
        $this->method = 'get';
    }

    public function usePostMethod()
    {
        $this->method = 'post';
    }

    /**
     * Returns the array of inputs associated with the name if the count > 1
     * or a single input object if not.
     * @param string $name
     * @return \Form\Base | array
     */
    public function __get($name)
    {
        $input = $this->getInput($name);
        if (count($input) == 1) {
            return $input[0];
        } else {
            return $input;
        }
    }

    /**
     * Loads the javascript file that helps enforce required fields. This may
     * eventually be incorporated into __toString or getInputStringArray.
     */
    public static function requiredScript()
    {
        javascript('jquery');
        \Layout::addJSHeader("<script type='text/javascript' src='" .
                PHPWS_SOURCE_HTTP . "Global/Templates/Form/required.js'></script>");
    }

    private function pushAllowedClass($allowed, $class_name)
    {
        if (empty($this->inputs)) {
            throw new \Exception('Input list is empty');
        }
        foreach ($this->inputs as $ilist) {
            foreach ($ilist as $i) {
                if (in_array($i->getType(), $allowed)) {
                    $i->addClass($class_name);
                }
            }
        }
    }

    /**
     * Adds a class to every button input currently in the form class
     *
     * @param string $class_name
     */
    public function addButtonClass($class_name)
    {
        static $allowed = array('button', 'submit');
        $this->pushAllowedClass($allowed, $class_name);
    }

    /**
     * Adds a class to every non-button input currently in the form class
     *
     * @param string $class_name
     */
    public function addInputClass($class_name)
    {
        static $allowed = array('text', 'textarea', 'email', 'color', 'date',
    'datetime', 'file', 'password', 'search', 'telephone', 'url', 'select');
        $this->pushAllowedClass($allowed, $class_name);
    }

    public function addLabelClass($class_name)
    {
        if (empty($this->inputs)) {
            throw new \Exception('Input list is empty');
        }
        foreach ($this->inputs as $ilist) {
            foreach ($ilist as $i) {
                $i->addLabelClass($class_name);
            }
        }
    }

    public function addGroupClass($class_name)
    {
        $this->group_class[] = $class_name;
    }

}

?>