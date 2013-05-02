<?php

namespace Form\Input;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Text extends \Form\Input {

    /**
     * The character size of the text field
     * @var integer
     */
    protected $size;
    /**
     * The maximum number of characters allowed in the text field
     * @var integer
     */
    protected $maxlength;
    /**
     * If true, autocomplete will be set to 'yes' in the input. An autocomplete
     * of 'no' instructs browsers to NOT fill in the text field with what it
     * believes should go there. Note that not setting autocomplete prevents
     * it from being displayed.
     * @var boolean
     */
    protected $autocomplete;
    /**
     * Supported in Opera only
     * @var string
     */
    protected $list;
    /**
     * Supported in Opera only
     * @var string
     */
    protected $pattern;
    /**
     * If set, this is the text that fills in an unset text field. Usually it
     * is a suggestion of what to type.
     * @var string
     */
    protected $placeholder = null;

    /**
     * Flags the text field as read only; cannot be edited by user
     * @var boolean
     */
    protected $readonly;

    /**
     * Sets the size and maxsize parameter the text input
     * @param integer $size
     * @param integer $maxsize
     */
    public function setSize($size, $maxsize=null)
    {
        $this->size = (int) $size;
        if ($maxsize) {
            if ($maxsize < $this->size) {
                throw new \Exception(t('Max size may not be less than input size'));
            }
            $this->maxsize = $maxsize;
        }
    }

    /**
     * Sets the autocomplete parameter to on or off depending on the parameter
     * sent. NULL will prevent the parameter from appearing on the input.
     * @param boolean $ac
     */
    public function setAutocomplete($ac=null)
    {
        if ($ac) {
            $this->autocomplete = 'on';
        } elseif (!isset($ac)) {
            unset($this->autocomplete);
        } else {
            $this->autocomplete = 'off';
        }
    }

    /**
     * Sets a text fields placeholder text.
     * @see Form\Input\Text::$placeholder
     * @param string $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $placeholder = preg_replace('/[^\w\s.,:&!?#]/', '', $placeholder);

        $this->placeholder = $placeholder;
    }
}

?>