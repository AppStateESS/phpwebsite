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

    public function __construct(\Database\DB $db)
    {
        $this->db = $db;
        parent::__construct();
    }

    public function getJson()
    {
        $headers = $this->getHeaders();
        if (!empty($this->search_phrase)) {
            if ($this->current_page > $this->getNumberofPages()) {
                $this->current_page = 1;
            }
        }
        if (!empty($this->sort_column) && $this->sort_direction != 0) {
            $this->setRowOrder();
        }

        $this->setRows($this->db->select());
        if (empty($this->rows)) {
            return array('rows' => null, 'error' => t('No rows found'));
        }

        $data = null;
        $data['headers'] = $headers;
        $data['total_rows'] = $this->total_rows;
        $data['current_page'] = $this->current_page;
        $data['rows_per_page'] = $this->rows_per_page;
        $data['page_listing'] = $this->getPageListing();
        $data['pager_search'] = $this->getPageSearch();
        $data['rows'] = $this->rows;
        return $data;
    }

    /**
     * An associate array of Database\Field objects. The key of the array is
     * the cooresponding header title.
     * @param array $table_headers
     */
    public function setTableHeaders(array $table_headers)
    {
        $this->table_headers = $table_headers;
    }

    public function setRowOrder()
    {
        if (!isset($this->table_headers[$this->sort_column])) {
            throw new \Exception(t('Sort column "%s" not set in DatabasePager',
                    $this->sort_column));
        }

        /* @var $field \Database\Field */
        $field = $this->table_headers[$this->sort_column];
        if (!is_a($field, '\Database\Field')) {
            throw new Exception(t('Sort column "%s" is not a Field object',
                    $this->sort_column));
        }

        $field->getTable()->addOrderBy($this->sort_column,
                $this->sort_direction == SORT_ASC ? 'asc' : 'desc');
    }

}

?>
