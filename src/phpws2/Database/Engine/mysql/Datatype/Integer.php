<?php

namespace Database\Engine\mysql\Datatype;

/*
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Integer extends \Database\Datatype {

    /**
     * If true, the integer will be unsigned (i.e. without negative values).
     * @var boolean
     */
    protected $unsigned = true;

    /**
     * If true, MySQL will fill empty left space with zeros
     * @var false
     */
    protected $zerofill = false;

    /**
     * If true, integer will be marked as an auto incrementing column.
     * @var boolean
     */
    protected $auto_increment = false;

    /**
     * The default value for the datatype when created or altered.
     * @var mixed
     */
    protected $default = null;

    // @todo not sure if needed. Would only be used when setting default
    /*
      protected $signed_limit_low = -2147483648;
      protected $signed_limit_high = 2147483648;
      protected $unsigned_limit_high = 4294967295;
     */

    /**
     * Loads an integer variable into the default parameter.
     */
    protected function loadDefault()
    {
        $this->default = new \Variable\Integer(null, 'default');
        $this->default->allowNull(true);
    }

    /**
     * Sets the integers UNSIGNED status
     * @param boolean $signed
     */
    public function setUnsigned($unsigned)
    {
        $this->unsigned = (bool) $unsigned;
    }

    /**
     * @param boolean $str If true, returns "unsigned" string
     * @return string|boolean
     */
    public function getUnsigned($str = false)
    {
        if ($str) {
            if ($this->unsigned) {
                return 'unsigned';
            }
            return null;
        } else {
            return $this->unsigned;
        }
    }

    /**
     * Returns the zerofill value or a string if $str is true
     * @return string|boolean
     */
    public function getZerofill($str = false)
    {
        if ($str) {
            if ($this->zerofill) {
                return 'zerofill';
            } else {
                return null;
            }
        } else {
            return $this->zerofill;
        }
    }

    /**
     * @param boolean $auto
     */
    public function setAutoIncrement($auto = true)
    {
        $this->auto_increment = (bool) $auto;
    }

    /**
     * Returns the auto_increment value or a string if $str is true
     * @return string|boolean
     */
    public function getAutoIncrement($str = false)
    {
        if ($str) {
            if ($this->auto_increment) {
                return 'auto_increment';
            } else {
                return null;
            }
        } else {
            return $this->auto_increment;
        }
    }

    public function getExtraInfo()
    {
        $q[] = $this->getUnsigned(true);
        $q[] = $this->getZerofill(true);
        $q[] = $this->getAutoIncrement(true);
        return implode(' ', $q);
    }

    /**
     * Checks some conditionals for the default value.
     * @return string
     */
    public function getDefaultString()
    {
        if ($this->auto_increment) {
            return null;
        }
        if (is_null($this->default)) {
            return null;
        }
        if ($this->default->isNull() && !$this->is_null) {
            return 'default 0';
        }
        return 'default ' . $this->default;
    }

}

?>
