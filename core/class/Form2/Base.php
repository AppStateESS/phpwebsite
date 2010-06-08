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
    private $label = null;

    /**
     * The input type (textarea, checkbox, etc.) for an input tag. Not
     * used in textarea and select
     * @var string;
     */
    protected $type = null;


    public function __construct($tag_type, $input_type=null, $value=null, $open=true)
    {
        static $default_ids = array();

        if (empty($input_type)) {
            $input_type = $tag_type;
        }
        if (!isset($default_ids[$input_type])) {
            $default_ids[$input_type] = 1;
        }
        $this->setId("$input_type-" . $default_ids[$input_type]);
        $default_ids[$input_type]++;
        parent::__construct($tag_type, $value, $open);
    }

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

    public function __toString()
    {
        if (isset($this->id) && !empty($this->label)) {
            return sprintf('<label for="%s">%s</label> %s', $this->id, $this->label, parent::__toString());
        } else {
            return parent::__toString();
        }
    }

    protected function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

}

?>