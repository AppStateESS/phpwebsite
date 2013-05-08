<?php

require_once 'Global/Backward/Inc/defines.php';

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class PHPWS_Form {

    /**
     * A \Form object from beanie
     * @var \Form
     */
    private $form;
    private $inputs;
    private $action;
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
     * In the case of a file input added, multipart is switched to true
     * @var boolean
     */
    private $multipart = false;

    /**
     * Unlike original Form class from phpwebsite, Backward version creates its
     * own beanie Form object to handle output.
     * @param string $id
     */
    public function __construct($id = null)
    {
        if (!defined('ABSOLUTE_UPLOAD_LIMIT') || ABSOLUTE_UPLOAD_LIMIT > FORM_MAX_FILE_SIZE) {
            $this->max_file_size = FORM_MAX_FILE_SIZE;
        } else {
            $this->max_file_size = ABSOLUTE_UPLOAD_LIMIT;
        }

        $this->form = new \Form;
        if ($id) {
            $this->form->setId($id);
        }
    }

    /**
     * If true, the form will be set to multipart/form-data for file uploading
     * @param boolean $encode
     */
    public function setEncode($encode = true)
    {
        if ($encode) {
            $this->form->setEnctype(\Form::enctype_multipart);
        } else {
            $this->form->setEnctype(null);
        }
    }

    /**
     * Changes the form id
     * @param string $id
     */
    public function setFormId($id)
    {
        $this->form->setId($id);
    }

    public function useBreaker($use_it = true)
    {
        $this->use_breaker = (bool) $use_it;
    }

    public function addInput($type, $name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->inputs[$key] = $this->form->addInput($type, $key, $val);
            }
        } else {
            $this->inputs[$name] = $this->form->addInput($type, $name, $value);
        }
    }

    public function addHidden($name, $value = null)
    {
        $this->addInput('hidden', $name, $value);
    }

    public function addSubmit($name, $value = null)
    {
        if (empty($value)) {
            $value = $name;
            $name = 'submit';
        }
        $this->inputs[$name] = $this->form->addSubmit($name, $value);
    }

    public function addCheck($name, $value = 1)
    {
        $this->addInput('checkbox', $name, $value);
    }

    public function setLabel($name, $label)
    {
        $this->inputs[$name]->setLabel($label);
    }

    /**
     *
     * @param string $name
     * @param string $match
     * @param mixed $void Not used in original class
     */
    public function setMatch($name, $match, $void = null)
    {
        $this->inputs[$name]->setSelection($match);
    }

    public function addFile($name)
    {
        $this->addInput('file', $name);
        $this->multipart = true;
    }

    public function addSelect($name, $value)
    {
        $this->inputs[$name] = $this->form->addSelect($name, $value);
    }

    public function setMethod($method)
    {
        $method = strtolower($method);

        if ($method == 'post') {
            $this->form->usePostMethod();
        } elseif ($method == 'get') {
            $this->form->useGetMethod();
        } else {
            throw new \Exception('Method may only be get or post');
        }
    }

    public function setExtra($name, $extra)
    {
        $this->inputs[$name]->setMiscellaneous($extra);
    }

    /**
     *
     * @param type $phpws
     * @param type $helperTags
     * @param type $template
     * @return type
     * @throws Exception
     */
    public function getTemplate($phpws = true, $helperTags = true, $template = null)
    {
        if (!is_null($template) && !is_array($template)) {
            throw new Exception(t('Submitted template is not an array'));
        }

        $form_template = $this->form->getFormAsArray();

        foreach ($form_template as $key => $value) {
            switch ($key) {
                case 'form_start':
                    if ($helperTags) {
                        $template['START_FORM'] = $value;
                    }
                    break;

                case 'form_end':
                    if ($helperTags) {
                        $template['END_FORM'] = $value;
                    }
                    break;

                case 'hidden':
                    $template['START_FORM'] .= implode("\n", $value);
                    break;

                default:
                    $template[strtoupper($key)] = $value;
            }
        }

        return $template;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getStart()
    {
        if (!isset($this->action)) {
            $this->action = 'index.php';
        }
        if ($this->multipart) {
            $this->action .= '?check_overpost=1';
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
        if (function_exists('javascript') && $this->required_field) {
            javascript('jquery');
            javascript('required_input');
        }

        // Add "Protection" javascript if requested.
        $protected = '';
        if (function_exists('javascript') && $this->protected) {
            javascript('protect_form');
            $protected = " form-protected";
        }

        return '<form class="phpws-form' . $protected . '" ' . $autocomplete . $formName . 'action="' . $this->_action . '" ' . $this->getMethod(true) . $this->_encode . '>';
    }

}

?>
