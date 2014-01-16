<?php

namespace Database;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Union {

    private $db_stack;

    public function __construct(array $db_array)
    {
        foreach ($db_array as $db) {
            if (!($db instanceof \Database\DB)) {
                throw new \Exception(t('createUnion only accepts \Database\DB object arrays'));
            }
        }
        $this->db_stack = $db_array;
    }

    public function select()
    {
        foreach ($this->db_stack as $db) {
            $query[] = $db->selectQuery();
        }
        $f_query = '(' . implode(') UNION (', $query) . ')';

        $qdb = \Database::newDB();
        $qdb->loadStatement($f_query);
        return $qdb->fetchAll();
    }

}

?>
