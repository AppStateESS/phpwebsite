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
 * Description of Form_TextArea
 */
class Form_TextArea extends Form_Element
{

    public $type = 'textarea';
    public $rows = DFLT_ROWS;
    public $cols = DFLT_COLS;
    public $height = null;
    public $use_editor = false;
    public $limit_editor = false;
    public $_editor_dm = null;
    public $_force_name = null;

    public function setRows($rows)
    {
        if (!is_numeric($rows) || $rows < 1 || $rows > 100) {
            return \phpws\PHPWS_Error::get(PHPWS_INVALID_VALUE, 'core', 'PHPWS_Form::setRows');
        }

        $this->rows = $rows;
        return true;
    }

    public function getRows($formMode = false)
    {
        if ($formMode) {
            return sprintf('rows="%s"', $this->rows);
        } else {
            return $this->rows;
        }
    }

    public function setCols($cols)
    {
        if (!is_numeric($cols) || $cols < 1 || $cols > 100) {
            return \phpws\PHPWS_Error::get(PHPWS_INVALID_VALUE, 'core', 'PHPWS_Form::setCols');
        }

        $this->cols = $cols;
        return true;
    }

    public function getCols($formMode = false)
    {
        if ($formMode) {
            return sprintf('cols="%s"', $this->cols);
        } else {
            return $this->cols;
        }
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function get()
    {
        $breaker = null;

        if ($this->use_editor) {
            $text = PHPWS_Text::decodeText($this->value);
            return javascript('ckeditor', array('ID' => $this->id, 'NAME' => $this->name, 'VALUE' => $text));
        }

        $value = preg_replace('/<br\s?\/?>(\r\n)?/', "\n", $this->value);

        if (ord(substr($value, 0, 1)) == 13) {
            $value = "\n" . $value;
        }

        if (isset($this->width)) {
            $style[] = 'width : ' . $this->width;
        } else {
            $dimensions[] = $this->getCols(true);
        }

        if (isset($this->height)) {
            $style[] = 'height : ' . $this->height;
        } else {
            $dimensions[] = $this->getRows(true);
        }

        if (isset($style)) {
            $dimensions[] = 'style="' . implode('; ', $style) . '"';
        }

        if (!USE_BREAKER && !empty($this->_form->use_breaker)) {
            $check_name = sprintf('%s_breaker', $this->name);
            $checkbox = new Form_Checkbox($check_name);
            $checkbox->_form = $this->_form;
            $checkbox->setLabel(_('Break newlines'));
            $checkbox->setId($check_name);
            $breaker = sprintf('<div class="textarea-breaker">%s %s</div>', $checkbox->get(), $checkbox->getLabel(true, true));
        }

        return $breaker .
                '<textarea '
                . $this->getName(true)
                . $this->getTitle(true)
                . $this->getPlaceholder()
                . $this->getDisabled()
                . $this->getReadOnly()
                . implode(' ', $dimensions) . ' '
                . $this->getData()
                . sprintf('>%s</textarea>', $value);
    }

}
