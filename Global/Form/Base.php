<?php

namespace Form;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Base extends \Tag {

    /**
     * Name of the form element
     * @var string
     */
    protected $name = null;

    /**
     * The label tag associated with this input. Should contain information
     * as to the function of the input or its title.
     * @var \Form\Label
     */
    private $label = null;

    /**
     * Indicates if input uses a label and where to print it. Submit and button
     * do not
     * -1 left of value
     *  1 right of value
     *  0 no label
     * @var boolean
     */
    protected $label_location = -1;

    /**
     * @see Tag::$tag_type
     * @var string
     */
    protected $tag_type = 'input';

    /**
     * The input type parameter
     * @var string
     */
    protected $type = 'text';

    /**
     * Sets the input as required.
     * @var boolean
     */
    protected $required;

    /**
     *
     * @staticvar array $default_ids Contains default ids for input to prevent repeats
     * @param string $name Input name
     * @param mixed $value Default value sent (radio, checkbox) or filled in (text)
     * @param string $label Label associated with input
     */
    public function __construct($name, $value = null, $label = null)
    {
        static $default_ids = array();

        $this->label = new Label;

        $this->setName($name);
        if (!isset($default_ids[$this->tag_type])) {
            $default_ids[$this->tag_type] = 1;
        }

        $id_name = preg_replace('|[\W_]+|', '-', $this->name);
        if (isset($default_ids[$id_name])) {
            $default_ids[$id_name]++;
            $id_name = $id_name . '-' . $default_ids[$id_name];
        } else {
            $default_ids[$id_name] = 1;
        }
        $this->setId($id_name);

        $this->type = strtolower($this->popClass());
        parent::__construct($this->tag_type, $value);
        // this MUST come after the above construct
        $this->addIgnoreVariables('has_label', 'label_location');
        $this->label->addClass($this->getType() . '-label');
    }

    /**
     * Adds a class name to all the labels in the current form.
     * 
     * @param string $class_name
     */
    public function addLabelClass($class_name)
    {
        $this->label->addClass($class_name);
    }


    /**
     * Name parameter of the input element
     * @param string $name
     */
    public function setName($name)
    {
        if (!$this->isProper($name)) {
            throw new \Exception(t('Improper name "%s"', $name));
        }
        $this->name = trim($name);

        $this->setLabelText($name);

        return $this;
    }

    public function setLabelText($text)
    {
        $label = str_replace('[', '', $text);
        $label = str_replace(']', '', $label);
        $label = str_replace('_', ' ', $label);
        $label = ucfirst($label);
        $this->label->setText($label);
    }

    /**
     * @see Base::$label_location
     * @param integer $loc
     */
    public function setLabelLocation($loc = 1)
    {
        $this->label_location = (int) $loc;
        return $this;
    }

    /**
     * @see Base::$label_location
     * @return integer
     */
    public function getLabelLocation()
    {
        return $this->label_location;
    }

    /**
     * Sets the label string
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label->setText($label);
        return $this;
    }

    public function setId($id)
    {
        $this->label->setFor($id);
        parent::setId($id);
    }

    /**
     * @return string The label html associated with this input
     */
    public function getLabel()
    {
        return (string) $this->label;
        /*
          if (empty($this->label)) {
          $label = $this->name;
          $label = str_replace('[', '', $label);
          $label = str_replace(']', '', $label);
          $label = str_replace('_', ' ', $label);
          $label = ucfirst($label);
          } else {
          $label = & $this->label;
          }
          $class_name = $this->getType() . '-label';
          return "<label for=\"$this->id\" class=\"$class_name\">$label</label>";
         *
         */
    }

    /**
     * Returns input's tag parameter: name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns input object's output with label alongside.
     * @return string
     */
    public function printWithLabel()
    {
        switch ($this->label_location) {
            case -1:
                return $this->getLabel() . ' ' . $this->__toString();
                break;

            case 0:
                return parent::__toString();
                break;

            case 1:
                return parent::__toString() . ' ' . $this->getLabel();
                break;
        }
    }

    /**
     * Indicates whether the output should contain a label.
     * @param boolean $bool
     */
    public function setPrintLabel($bool)
    {
        $this->print_label = (bool) $bool;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function getPrintLabel()
    {
        return $this->print_label;
    }

    /**
     * Sets the field as required before form submission.
     * @param boolean $required
     * @return \Form\Base
     */
    public function setRequired($required = true)
    {
        if ($required) {
            $this->required = 'required';
        } else {
            $this->required = null;
        }
        return $this;
    }

}

?>