<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class DB_Group_In {

    public $children;
    public $parent;
    public $name;
    public $db;

    public function buildSQL($first = false)
    {
        if (!$first) {
            if (isset($this->db->where[$this->name]['conj'])) {
                $sql[] = $this->db->where[$this->name]['conj'];
            } else {
                $sql[] = 'AND';
            }
        }
        $sql[] = '(';
        $first = true;
        foreach ($this->db->where[$this->name]['values'] as $where) {
            if ($first) {
                $first = false;
            } else {
                $sql[] = $where->conj;
            }
            $sql[] = $where->get();
        }
        if (!empty($this->children)) {
            //$sql[] = '(';
            foreach ($this->children as $kid) {
                $sql[] = $kid->buildSQL();
            }
            //$sql[] = ')';
        }
        $sql[] = ')';
        return implode(' ', $sql);
    }

}

?>
