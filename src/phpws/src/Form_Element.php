<?php

/*
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace phpws;

/**
 * Description of Form_Element
 */
class Form_Element
{

    public $key = 0;
    public $type = null;
    public $name = null;
    public $value = null;
    public $placeholder = null;
    public $disabled = false;
    public $read_only = false;
    public $css_class = null;
    public $style = null;
    public $tab = null;
    public $width = null;
    public $allowValue = true;
    public $isArray = false;
    public $tag = null;
    public $label = null;
    public $id = null;
    public $title = null;
    public $required = false;
    public $_form = null;
    // When multiple values are sent to an element, this variable
    // stores the position for labels and titles
    public $place = 0;

    public function __construct($name, $value = null)
    {
        $this->setName($name);
        if (isset($value)) {
            $this->setValue($value);
        }
    }

    public function setDisabled($disable)
    {
        $this->disabled = (bool) $disable;
    }

    public function setReadOnly($read_only)
    {
        $this->read_only = (bool) $read_only;
    }

    public function getDisabled()
    {
        if ($this->disabled) {
            return 'disabled="disabled" ';
        }
        return null;
    }

    public function getReadOnly()
    {
        if ($this->read_only) {
            return 'readonly="readonly" ';
        }
        return null;
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function getTitle($formMode = false)
    {
        if ($formMode) {
            if (isset($this->title)) {
                if (is_array($this->title)) {
                    $key = $this->place;
                    $title = $this->title[$key];
                } else {
                    $title = $this->title;
                }

                return sprintf('title="%s" ', $title);
            } elseif (isset($this->label)) {
                $title = strip_tags($this->getLabel(true, false));
                return sprintf('title="%s" ', $title);
            } else {
                return null;
            }
        }
    }

    public function allowValue()
    {
        $this->allowValue = true;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setRequired($required = true)
    {
        $this->_form->required_field = true;
        $this->required = (bool) $required;
    }

    public function getRequired()
    {
        if ($this->required) {
            return '<span class="required-input">*</span>';
        } else {
            return null;
        }
    }

    public function getLabel($formMode = false, $tagMode = true)
    {
        if ($formMode) {
            if (isset($this->label)) {
                if (is_array($this->label)) {
                    $key = $this->place;
                    if (isset($this->label[$key])) {
                        $label = $this->label[$key];
                    } else {
                        $label = null;
                    }
                } else {
                    $label = $this->label;
                }

                if ($tagMode) {
                    if (empty($this->_form)) {
                        trigger_error('Error in Form::getLabel');
                        return null;
                    }
                    return $this->_form->makeLabel($this, $label);
                } else {
                    return $label;
                }
            } else {
                return null;
            }
        } else {
            return $this->label;
        }
    }

    public function setName($name)
    {
        $this->name = preg_replace('/[^\[\]\w]/', '', $name);
    }

    public function setId($id = null)
    {
        if (empty($id)) {
            $id = $this->getName();
            // changed 20070312
            // Square brackets are not allowed as id names.
            $id = preg_replace('/\[(\w+)\]/', '_\\1', $id);

            // changed 6/14/06
            if ($this->type == 'radio') {
                $id .= '_' . $this->key;
            }

            if ($this->_form) {
                $this->id = $this->_form->id . '_' . $id;
            } else {
                $this->id = $id;
            }
        } else {
            $this->id = $id;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName($formMode = false, $show_id = true)
    {
        if ($this->isArray) {
            if ($this->type == 'multiple') {
                $name = $this->name . '[]';
            } else {
                $name = $this->name . '[' . $this->key . ']';
            }
        } else {
            $name = $this->name;
        }

        if ($formMode) {
            if ($show_id) {
                $id = $this->id;
                return sprintf('name="%s" id="%s" ', $name, $id);
            }
        } else {
            return $name;
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        if ($this->allowValue) {
            $value = str_replace('"', '&quot;', $this->value);
            return 'value="' . $value . '" ';
        } else {
            return null;
        }
    }

    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    public function getPlaceholder()
    {
        if ($this->placeholder) {
            $placeholder = str_replace('"', '&quot;', $this->placeholder);
            return 'placeholder="' . $placeholder . '" ';
        }
        return '';
    }

    public function setClass($css_class)
    {
        $this->css_class = $css_class;
    }

    /**
     * Adds a CSS class to this element. Does not overwrite previously added classes.
     * @param unknown $className
     */
    public function addCssClass($className)
    {
        if (!isset($this->css_class)) {
            $this->css_class = $className;
        } else {
            $this->css_class .= (' ' . $className);
        }
    }

    public function getClass($formMode = false)
    {
        if (!$formMode) {
            return $this->css_class;
        }

        $class = (isset($this->css_class) ? $this->css_class : null);

        if ($this->required) {
            $class .= ' input-required';
        }

        return $class;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function getStyle($formMode)
    {
        if ($formMode) {
            return (isset($this->style)) ? 'style="' . $this->style . '"' : null;
        } else {
            return $this->style;
        }
    }

    public function setTab($order)
    {
        $this->tab = (int) $order;
    }

    public function getTab($formMode = false)
    {
        if ($formMode) {
            return sprintf('tabindex="%s"', $this->tab);
        } else {
            return $this->tab;
        }
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * Adds an extra tag (or any arbitrary string)to the HTML markup
     * for this form element. Will not overwrite any existing extra tags.
     *
     * @param string $tag
     */
    public function addExtraTag($tag)
    {
        if (!isset($this->extra)) {
            $this->extra = $tag;
        } else {
            $this->extra .= (' ' . $tag);
        }
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function setSize($size, $maxsize = 0)
    {
        $this->size = (int) $size;
        if ($maxsize) {
            $this->setMaxSize($maxsize);
        }
    }

    public function getSize($formMode = false)
    {
        if ($formMode) {
            return 'size="' . $this->size . '" ';
        } else {
            return $this->size;
        }
    }

    public function setMaxSize($maxsize)
    {
        $this->maxsize = (int) $maxsize;
    }

    public function getMaxSize($formMode = false)
    {
        if ($formMode) {
            if (isset($this->maxsize)) {
                return 'maxlength="' . $this->maxsize . '"';
            } else {
                return null;
            }
        } else {
            return $this->maxsize;
        }
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth($formMode = false)
    {
        if ($formMode) {
            if (isset($this->width)) {
                return 'style="width : ' . $this->width . '" ';
            } else {
                return null;
            }
        } else {
            return $this->width;
        }
    }

    public function setHeight($height)
    {
        $this->height = (int) $height;
    }

    public function getData()
    {
        if (isset($this->style)) {
            $extra[] = $this->getStyle(true);
        }

        // Don't check isset here. Required needs to be checked in
        // the getClass function.
        $extra[] = 'class="' . $this->getClass(true) . '"';

        if (isset($this->extra)) {
            $extra[] = $this->getExtra();
        }

        if (isset($this->size)) {
            $extra[] = $this->getSize(true);
        }

        if (isset($this->maxsize)) {
            $extra[] = $this->getMaxSize(true);
        }

        if (isset($this->tab)) {
            $extra[] = $this->getTab(true);
        }

        if (isset($extra)) {
            return implode(' ', $extra);
        } else {
            return null;
        }
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    public function getTag()
    {
        if (isset($this->tag)) {
            return strtoupper($this->tag);
        } else {
            $name = str_replace('][', '_', $this->name);
            $name = str_replace('[', '_', $name);
            $name = str_replace(']', '', $name);

            return strtoupper($name);
        }
    }

}
