<?php

namespace Database;

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

class ForeignKey extends Constraint implements TableCreateConstraint {

    const CASCADE = 1;
    const RESTRICT = 2;
    const NO_ACTION = 3;
    const SET_NULL = 4;
    const SET_DEFAULT = 5;

    private $references;
    private $on_delete = self::RESTRICT;
    private $on_update = self::RESTRICT;

    public function __construct($columns, $references, $on_delete = null, $on_update = null, $name = null)
    {
        parent::__construct($columns, $name);
        $this->setReferences($references);
        if ($on_delete) {
            $this->setOnDelete($on_delete);
        }
        if ($on_update) {
            $this->setOnUpdate($on_update);
        }
    }

    public function getConstraintType()
    {
        return 'FOREIGN KEY';
    }

    public function setColumns($columns)
    {
        if (is_array($columns) && !empty($this->references) && count($this->references) != count($columns)) {
            throw new \Exception(t('Source columns do not equal reference column count'));
        }
        parent::setColumns($columns);
    }

    public function setReferences($references)
    {
        $this->check($references);
        if (is_array($references)) {
            if (!empty($this->columns) && count($this->columns) != count($references)) {
                throw new \Exception(t('Source columns do not equal reference column count'));
            }
            $this->references = $references;
        } else {
            $this->references[] = $references;
        }
    }

    public function setOnUpdate($option)
    {
        if (!$this->correctOption($option)) {
            throw new \Exception(t('Unknown update reference option'));
        }
        $this->on_update = $option;
    }

    public function setOnDelete($option)
    {
        if (!$this->correctOption($option)) {
            throw new \Exception(t('Unknown delete reference option'));
        }
        $this->on_delete = $option;
    }

    private function correctOption($option)
    {
        static $action_types = array(self::CASCADE, self::RESTRICT, self::SET_NULL,
    self::NO_ACTION, self::SET_DEFAULT);
        return in_array($option, $action_types);
    }

    private function getOptionString($option)
    {
        switch ($option) {
            case self::CASCADE:
                return 'CASCADE';

            case self::RESTRICT:
                return 'RESTRICT';

            case self::SET_NULL:
                return 'SET NULL';

            case self::SET_DEFAULT:
                return 'SET NULL';

            case self::NO_ACTION:
                return 'NO ACTION';
        }
    }

    public function getConstraintString()
    {
        $sql[] = parent::getConstraintString();

        foreach ($this->references as $rk) {
            $reference_keys[] = $rk->getName();
        }
        $reference_table_name = $rk->getTable()->getFullName();

        $sql[] = 'REFERENCES';
        $sql[] = $reference_table_name . '(' . implode(', ', $reference_keys) . ')';

        $sql[] = 'ON DELETE ' . $this->getOptionString($this->on_delete);

        $sql[] = 'ON UPDATE ' . $this->getOptionString($this->on_update);
        return implode(' ', $sql);
    }

    public function add()
    {
        $sql[] = 'ALTER TABLE';
        $sql[] = $this->source_table->getFullName();
        $sql[] = 'ADD';
        $sql[] = $this->__toString();
        $query = implode(' ', $sql);
        $this->source_table->db->query($query);
    }

}

?>
