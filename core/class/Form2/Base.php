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

class Base extends Tag {
    /**
     * @var string
     */
    protected $name = null;
    protected $label = null;


    public function setName($name)
    {
        if (!$this->isProper($name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper name'));
        }
        $this->name = $name;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function __toString($with_label=false)
    {
        if ($with_label && isset($this->id)) {
            return sprintf('<label for="%s">%s</label> %s', $this->id, $this->label, parent::__toString());
        } else {
            return parent::__toString();
        }
    }
}

?>