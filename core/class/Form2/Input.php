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

require_once PHPWS_SOURCE_DIR . 'core/class/Form2/Base.php';

class Input extends Base {

    /**
     * The input type (textarea, checkbox, etc.) for an input tag. Not
     * used in textarea
     * @var string;
     */
    protected $type = null;

    /**
     * Indicates if the current radio or checkbox input was previously checked.
     * Will be changed to the string "checked" if so.
     * @var string
     */
    protected $checked = null;

    public function __construct($type, $name, $value=null)
    {
        switch ($type) {
            case 'textarea':
                parent::__construct('textarea', 'textarea', $value, true);
                break;

            case 'text':
            case 'hidden':
            case 'radio':
            case 'checkbox':
            case 'button':
            case 'file':
            case 'password':
            case 'submit':
                parent::__construct('input', $type, $value, false);
                $this->setType($type);
                break;
        }

        $this->setName($name);
    }

    private function setType($type)
    {
        $this->type = $type;
    }

    public function setChecked($check=true)
    {
        switch ($this->type) {
            case 'checkbox':
            case 'radio':
                $this->checked = 'checked';
                break;

            default:
                throw new PEAR_Exception(sprintf(dgettext('core', 'Cannot setChecked on %s input type'), $this->type));
                break;
        }
    }

    public function __toString()
    {
        return parent::__toString($this->type != 'submit');
    }
}

?>