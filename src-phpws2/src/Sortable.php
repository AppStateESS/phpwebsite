<?php

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
 * This class assists with sorting items within a table.
 * 
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

namespace phpws2;

class Sortable
{

    protected $table_name;

    /**
     * The table column that all sorted rows share.
     * @var string
     */
    protected $anchor_column;

    /**
     * Value matched to anchor_column
     * @var string
     */
    protected $anchor_value;

    /**
     * Name of table column that contains the sort data.
     * @var string
     */
    protected $sort_column;

    /**
     * @var \phpws2\Database\DB
     */
    private $db;

    /**
     * @var \phpws2\Database\Table
     */
    private $tbl;
    
    private $start_count = 1;

    public function __construct($table_name, $sort_column)
    {
        $this->setTableName($table_name);
        $this->setSortColumn($sort_column);
    }

    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }

    public function setAnchor($column, $value)
    {
        if (empty($column) || empty($value)) {
            throw new \Exception('Both column and value must not be empty');
        }
        $this->anchor_column = $column;
        $this->anchor_value = $value;
    }

    public function setSortColumn($column)
    {
        $this->sort_column = $column;
    }

    public function startAtZero()
    {
        $this->start_count = 0;
    }
    
    private function getDB($anchor = true)
    {
        $db = Database::getDB();
        $tbl = $db->addTable($this->table_name);

        if ($anchor && $this->anchor_column && $this->anchor_value) {
            $tbl->addFieldConditional($this->anchor_column, $this->anchor_value);
        }
        return array('db' => $db, 'tbl' => $tbl);
    }

    private function getMoveRow()
    {
        $this->getDB(true);
        $this->tbl->addFieldConditional('id', $moving_id);
        return $this->db->selectOneRow();
    }

    public function reorder()
    {
        $db = $tbl = null;

        extract($this->getDB());
        $tbl->addField('id');
        $tbl->addOrderBy($tbl->getField($this->sort_column));
        $count = $this->start_count;
        while ($id = $db->selectColumn()) {
            $this->updateSort($count, $id);
            $count++;
        }
    }

    private function updateSort($count, $id)
    {
        $db = $tbl = null;

        extract($this->getDB());
        $tbl->addValue($this->sort_column, $count);
        $tbl->addFieldConditional('id', $id);
        $db->update();
    }

    /**
     * @param integer $moving_id Id of item moved
     * @param integer $sorted_id Position to move item to
     */
    public function moveTo($moving_id, $sorted_to)
    {
        $db = $tbl = null;
        extract($this->getDB());
        $tbl->addOrderBy($tbl->addField($this->sort_column));
        $tbl->addField('id');
        $result = $db->select();
        $count = $this->start_count - 1;
        foreach ($result as $row) {
            $id = $row['id'];
            $sort = $row[$this->sort_column];
            $count++;
            if ($id == $moving_id) {
                $count--;
            } elseif ($count == $sorted_to) {
                $count++;
                $this->updateSort($count, $id);
            } else {
                $this->updateSort($count, $id);
            }
        }
        $this->updateSort($sorted_to, $moving_id);
    }

}
