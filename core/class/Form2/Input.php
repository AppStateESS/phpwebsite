<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class Input extends Tag {

    /**
     * @var string
     */
    protected $name = null;

    /**
     * The input type (textarea, checkbox, etc.) for an input tag. Not
     * used in textarea or select
     * @var string;
     */
    protected $type = null;

    /**
     * Indicates if the current radio or checkbox input was previously checked.
     * Will be changed to the string "checked" if so.
     * @var string
     */
    protected $checked = null;

    /**
     * Indicates if the current option input was previously selected.
     * Will be changed to the string "selected" if so.
     * @var string
     */
    protected $selected = null;

    /**
     * Option inputs differ from other inputs. This variable notes it.
     * @var boolean
     */
    private $is_option = false;

    public function __construct($type, $name, $value=null)
    {
        switch ($type) {
            case 'textarea':
                $this->setTagType('textarea');
                $this->setOpen(true);
                break;

            case 'text':
            case 'hidden':
            case 'radio':
            case 'checkbox':
            case 'submit':
            case 'button':
            case 'file':
            case 'password':
                parent::__construct('input', $value, false);
                $this->setType($type);
                break;

            case 'option':
                parent::__construct('option', $value, true);
                $this->is_option = true;
                break;
        }
        $this->setName($name);

    }

    private function setType($type)
    {
        $this->type = $type;
    }

    public function setName($name)
    {
        if (!$this->isProper($name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper input name'));
        }
        $this->name = $name;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function get($with_label=false)
    {
        if ($with_label && isset($this->id)) {
            return sprintf('<label for="%s">%s</label> %s', $this->id, $this->label, $this->__toString());
        } else {
            return $this->__toString();
        }
    }

    public function setChecked($check=true)
    {
        switch ($this->type) {
            case 'checkbox':
            case 'radio':
                $this->checked = 'checked';
                break;

            default:
                if ($this->is_option) {
                    $this->selected = 'selected';
                } else {
                    throw new PEAR_Exception(sprintf(dgettext('core', 'Cannot setChecked on %s input type'), $this->type));
                }
                break;
        }
    }
}

?>