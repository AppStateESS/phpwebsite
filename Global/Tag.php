<?php

/**
 * The Tag class helps developers create custom HTML 5 tags.
 *
 * Though not abstract, the class can be extended by custom html tag classes
 * (see the Input class for examples). Any public variable in the child class
 * will be shown in the tag.
 *
 * <code>
 * class Custom extends Tag {
 *      public $fruit = 'banana';
 *      private $vegetable = 'carrot';
 *      public function __construct()
 *      {
 *          $tag_type = 'foobar';
 *          $text = 'I am the text';
 *          parent::__construct($tag_type, $text);
 *      }
 * }
 * $foo = new Custom; // echo uses __toString function in tag
 *
 * echo $foo; // result will be <foobar fruit="banana">I am the text</foobar>
 * </code>
 *
 * Note that all tag parameters are put into quotes. Though the HTML 5 standard
 * doesn't require it, it makes it easier for parameters that may have spaces.
 *
 * One difference to the above rules, if a variable is
 * 1) boolean
 * 2) set to TRUE
 * 3) not ignored
 * then that property will listed in the tag ALONE. For example, the checkbox
 * input extends Tag and has a "checked" variable. When it is true, then checked
 * is added as a parameter like so:
 * <input type="checkbox" value="1" checked>
 *
 * This conforms with HTML 5.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Tag extends Data {

    /**
     * Tag type (e.g. p, b, div, etc.)
     * @var string
     */
    protected $tag_type = null;

    /**
     * Tag identifier
     * @var string
     */
    protected $id = null;

    /**
     * Title of image tag
     * @var string
     */
    protected $title = null;

    /**
     * Inline style definition for tag
     * @var string
     */
    protected $style = null;

    /**
     * Class definition of tag
     * @var array
     */
    private $class = null;

    /**
     * If true, the tag is open (e.g. <p></p>), closed (e.g. <br />) otherwise)
     * @var boolean
     */
    protected $open = true;

    /**
     * Text of the tag. Output determined by open status
     * @var string
     */
    private $text = null;

    /**
     * Variables you do not want included in the tag formation.
     * For example, if you have a variable $foo = 'bar' and you don't want
     * <tag foo=bar>
     * then you would $tag->addIgnoreVariable('foo');
     * @var array
     */
    private $ignore_variables = null;

    /**
     * Javascript option added as an attribute to the tag
     * @var string
     */
    protected $events = array();

    /**
     * Any data the developer wants added inside the tag that is not covered
     * by variables can be added here.
     * Example:
     *
     * @var string
     */
    private $miscellaneous;

    /**
     * Array of data attributes added to a tag
     * @var array
     */
    private $data;

    /**
     * @param string $tag_type The tag element e.g. "p" for paragraph
     * @param string $text  The tag content, what the tag wraps aroun
     */
    public function __construct($tag_type = null, $text = null)
    {
        if (isset($tag_type)) {
            $this->setTagType($tag_type);
        }

        if (isset($text)) {
            $this->setText($text);
        }
        $this->ignore_variables = array('open', 'tag_type', 'error', 'text', 'ignore_variables', 'events', 'parent', 'data');
    }

    public function addEvent(Event $event)
    {
        $this->events[] = $event;
    }

    /**
     * Adds variable names that the tag will NOT output when __toString is called.
     * <code>
     * $var->addIgnoreVariables('foo', 'bar');
     * </code>
     * @param string
     */
    protected function addIgnoreVariables()
    {
        $vars = func_get_args();
        if (empty($vars)) {
            throw new \Exception(t('No variables received in addIgnoreVariables'));
        }
        foreach ($vars as $v) {
            $this->ignore_variables[] = $v;
        }
    }

    /**
     * Adds a data attribute to the tag:
     * Example:
     * <code>
     * $tag = new Tag('p', 'hello');
     * $tag->addData('foo-bar', 1);
     * var_dump($tag->__toString());
     * // echoes <p data-foo-bar="1">hello</p>
     * </code>
     * @param string $name
     * @param string $value
     */
    public function addData($name, $value)
    {
        if (is_numeric($value)) {
            $value = (string) $value;
        }
        $oName = new \Variable\Attribute($name, 'dataNameAttribute');
        $oValue = new \Variable\String($value, 'dataValueAttribute');
        $this->data[$oName->get()] = $oValue->get();
    }

    /**
     * Returns data strings for buildTag
     * @return string
     */
    private function getData()
    {
        foreach ($this->data as $name => $value) {
            $attr[] = "data-$name=\"$value\"";
        }
        return implode(' ', $attr);
    }

    /**
     * Sets the tag element type (e.g. paragraph tag type is "p")
     * @param string $tag_type
     */
    protected function setTagType($tag_type)
    {
        $this->tag_type = new \Variable\Attribute($tag_type, 'tag_type');
    }

    /**
     * Builds an array of data for creating the final tag in toString. Gives
     * extended classes a chance to add data.
     * @return array
     */
    protected function buildTag()
    {
        $data = array();
        if (empty($this->tag_type)) {
            trigger_error(t('Tag type not set'), E_USER_ERROR);
        }
        $data[] = '<' . $this->tag_type;
        if (empty($this->ignore_variables)) {
            trigger_error(t('The Tag parent class "%s" failed to call __construct',
                            get_parent_class($this)), E_USER_ERROR);
            exit();
        }

        $tag_parameters = array_diff_key(get_object_vars($this),
                array_flip($this->ignore_variables));
        $tag_parameters['class'] = $this->getClass();
        $tag_parameters['style'] = $this->getStyle();

        if (!empty($tag_parameters)) {
            foreach ($tag_parameters as $pname => $param) {
                if (is_null($param)) {
                    continue;
                }

                if (is_string_like($param)) {
                    $param = htmlentities($param, ENT_COMPAT);
                    $data[] = sprintf('%s="%s"', $pname, $param);
                } elseif (is_bool($param)) {
                    if ($param) {
                        $data[] = $pname;
                    }
                } else {
                    throw new \Exception(t('Unaccepted tag parameter "%s" is of type (%s)',
                            $pname, gettype($param)));
                }
            }
        }
        if (!empty($this->data)) {
            $data[] = $this->getData();
        }

        if (!empty($this->events)) {
            $data[] = implode(' ', $this->events);
        }

        if (!empty($this->miscellaneous)) {
            $data[] = $this->miscellaneous;
        }
        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $data = $this->buildTag();

        $result = implode(' ', $data);
        if ($this->open) {
            $result .= ">{$this->text}</{$this->tag_type}>";
        } else {
            $result .= '>';
        }
        return $result;
    }

    /**
     * Sets a CSS class for the tag
     * @param string $class
     */
    public function addClass($class)
    {
        if (preg_match('/[^\w\-\s]/', $class)) {
            throw new \Exception(t('Improper class name'));
        }
        $this->class[] = $class;
    }

    /**
     * Implodes the class array with spaces and returns the string.
     * @return string
     */
    public function getClass()
    {
        if (!empty($this->class)) {
            return implode(' ', $this->class);
        } else {
            return null;
        }
    }

    /**
     * Adds a style parameter in the tag
     * @param array|string $style
     */
    public function addStyle($style)
    {
        if (is_array($style)) {
            foreach ($style as $s) {
                $this->addStyle($s);
            }
            return;
        }

        if (preg_match('/[^\w\-;\s:!()\/\.]/i', $style)) {
            throw new \Exception(t('Improperly formatted style settings'));
        }
        if (!preg_match('/;$/', $style)) {
            $style .= ';';
        }
        $this->style[] = $style;
    }

    /**
     * Implodes the style array with spaces and returns the string.
     * @return string
     */
    public function getStyle()
    {
        if (!empty($this->style)) {
            return implode(' ', $this->style);
        } else {
            return null;
        }
    }

    /**
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = new \Variable\Attribute($id);
    }

    /**
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the open status of the tag
     * @see Tag::$open
     * @param boolean $open
     */
    protected function setOpen($open)
    {
        $this->open = (bool) $open;
    }

    /**
     * Set the tag's title parameter
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = htmlentities(strip_tags($title));
    }

    public function setMiscellaneous($misc)
    {
        $this->miscellaneous = strip_tags($misc);
    }

    /**
     * Set the text content of the tag.
     * @param string $text
     */
    public function setText($text)
    {
        if (!is_string_like($text)) {
            throw new \Exception(t('setText did not receive a string parameter'));
        }
        $this->text = (string) $text;
    }

    /**
     * Returns true if the passed string is a properly formatted SGML element type.
     * Name or Id
     * @param string $proper
     */
    public function isProper($proper)
    {
        return preg_match('/^[a-z][\[\]\w\-\:\.]*/i', $proper);
    }

}

?>