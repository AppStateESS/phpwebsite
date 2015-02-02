<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class DatabasePager extends Pager {

    /**
     *
     * @var \Database\DB
     */
    protected $db;

    /**
     * Associative array containing the names of the tables
     * @var array
     */
    protected $table_headers;

    /**
     *
     * @var boolean
     */
    protected $show_query = false;

    public function __construct(\Database\DB $db)
    {
        $this->db = $db;
        parent::__construct();
    }

    public function getAllRows()
    {
        return $this->rows;
    }

    public function processRows()
    {
        if (empty($this->total_rows)) {
            $this->loadTotalRows();
        }
        if (!empty($this->search_phrase)) {
            $this->loadSearchConditionals();
            if ($this->current_page > $this->getNumberofPages()) {
                $this->current_page = 1;
            }
        }
        if (!empty($this->sort_column) && $this->sort_direction != 0) {
            $this->setRowOrder();
        }

        $this->processLimit();

        if ($this->show_query) {
            $this->addJsonData('select_query',$this->db->selectQuery());
        }
        $this->setRows($this->db->select());
        $this->executeCallback();
    }

    /**
     * If true, record the select query and insert in JSON
     * @param boolean $show
     */
    public function showQuery($show = true)
    {
        $this->show_query = (bool) $show;
    }
    
    private function loadSearchConditionals()
    {
        foreach ($this->table_headers as $field) {
            // If search column is set, then we only match on that column.
            if (!empty($this->search_column) && $this->search_column != $field->getName()) {
                continue;
            }
            if (!isset($conditional)) {
                $conditional = $this->db->createConditional($field,
                        '%' . $this->search_phrase . '%', 'like');
            } else {
                $conditional = $this->db->createConditional($conditional,
                        $this->db->createConditional($field,
                                '%' . $this->search_phrase . '%', 'like'), 'OR');
            }
        }
        $this->db->addConditional($conditional);
    }

    private function loadTotalRows()
    {
        $db_clone = clone($this->db);
        $db_clone->loadPDO();

        // Remove tables fields (including splat) from select query
        $tables = $db_clone->getAllTables();
        foreach ($tables as $t) {
            $t->useInQuery(false);
        }

        // Remove tables fields (including splat) from select query
        $db_clone->clearTableFields();
        $db_clone->clearExpressions();
        $db_clone->clearGroupBy();
        $db_clone->clearOrderBy();

        $db_clone->addExpression('count(*) as _row_count');
        $count_result = $db_clone->selectOneRow();
        $this->setTotalRows($count_result['_row_count']);
    }

    /**
     * An associate array of Database\Field objects cooresponding to columns used in searching and sorting.
     * @param array $table_headers
     */
    public function setTableHeaders(array $table_headers)
    {
        $this->table_headers = $table_headers;
    }

    private function processLimit()
    {
        $offset = ($this->current_page - 1) * $this->rows_per_page;
        $this->db->setLimit($this->rows_per_page, $offset);
    }

    public function setRowOrder()
    {
        if (!isset($this->table_headers[$this->sort_column])) {
            throw new \Exception(t('Sort column "%s" not set in DatabasePager',
                    $this->sort_column));
        }

        /* @var $field \Database\Field */
        $field = $this->table_headers[$this->sort_column];
        if (!is_a($field, '\Database\Field') && !is_a($field,
                        '\Database\Expression')) {
            throw new Exception(t('Sort column "%s" is not a Field or Expression object',
                    $this->sort_column));
        }

        $field->getTable()->addOrderBy($field,
                $this->sort_direction == SORT_ASC ? 'asc' : 'desc');
    }

}

?>
