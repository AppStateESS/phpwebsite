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
require_once 'PEAR/Exception.php';

class Tag {
    /**
     * Tag type (e.g. p, b, div, etc.)
     * @var string
     */
    private $tag_type = null;

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
     * Value of the tag. Output of value determined by open status
     * @var string
     */
    private $value = null;

    /**
     * If there was an error, it will be listed here
     * @var array
     */
    private $error = array();

    /**
     * Sets the tag element type (e.g. paragraph tag type is "p")
     * @param string $tag_type
     * @return void
     */
    protected function setTagType($tag_type)
    {
        if (preg_match('/[^a-zA-Z]/', $tag_type)) {
            throw new PEAR_Exception(dgettext('core', 'Tag type must be alphabetic characters only'));
        }
        $this->tag_type = $tag_type;
    }

    public function __toString()
    {
        if (empty($this->tag_type)) {
            trigger_error(dgettext('core', 'Tag type not set'));
            return '';
        }
        $data[] = "<$this->tag_type";

        $tag_parameters = get_object_vars($this);
        unset($tag_parameters['open']);
        unset($tag_parameters['tag_type']);
        unset($tag_parameters['error']);
        unset($tag_parameters['value']);
        $tag_parameters['class'] = $this->getClass();
        $tag_parameters['style'] = $this->getStyle();

        if (!empty($tag_parameters)) {
            foreach ($tag_parameters as $pname=>$param) {
                if (is_null($param)) {
                    continue;
                }
                if (is_string($param)) {
                    $param = htmlentities($param, ENT_COMPAT);
                }
                $data[] = sprintf('%s="%s"', $pname, $param);
            }
        }
        $result = implode(' ', $data);

        if($this->open) {
            $result .= ">{$this->value}</{$this->tag_type}>";
        } else {
            $result .= sprintf(' value="%s" />', htmlentities($this->value, ENT_COMPAT, 'UTF-8'));
        }

        return $result;
    }

    public function listErrors()
    {
        return $this->error;
    }

    public function setClass($class)
    {
        if (preg_match('/[^\w\-\s]/', $class)) {
            trigger_error(dgettext('core', 'Improper class name'));
        }
        $this->class[] = $class;
    }

    public function getClass()
    {
        if (!empty($this->class)) {
            return implode(' ', $this->class);
        } else {
            return null;
        }
    }

    public function setStyle($style)
    {
        if (preg_match('/[^\w\-;\s:!]/', $style)) {
            trigger_error(dgettext('core', 'Improperly formatted style settings'));
        }
        if (!preg_match('/;$/', $style)) {
            $style .= ';';
        }
        $this->style[] = $style;
    }

    public function getStyle()
    {
        if (!empty($this->style)) {
            return implode(' ', $this->style);
        } else {
            return null;
        }
    }
    public function setId($id)
    {
        if (!$this->isProper($id)) {
            trigger_error(dgettext('core', 'Improper id name'));
        }
        $this->id = $id;
    }

    protected function setOpen($open)
    {
        $this->open = (bool)$open;
    }

    public function setTitle($title)
    {
        $this->title = htmlentities(strip_tags($title));
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Returns true if the passed string is a properly formatted SGML element type.
     * Name or Id
     * @var string $string
     * @see
     */
    public function isProper($string)
    {
        return preg_match('/^[a-z][\w\-\:\.]*/', $string);
    }
}

?>