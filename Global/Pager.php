<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Pager {

    /**
     * Total rows
     * @var integer
     */
    protected $total_rows;

    /**
     * Number of rows to show per page
     * @var integer
     */
    protected $rows_per_page;

    /**
     * Number of page links to show.
     * @var integer
     */
    protected $page_link_number;

    /**
     * Currently page viewed.
     * @var integer
     */
    protected $current_page = 1;

    /**
     * Character, word, or tag used in first page link
     * @var string
     */
    protected $first_marker;

    /**
     * Character, word, or tag used in last page link
     * @var string
     */
    protected $last_marker;

    /**
     * Character, word, or tag used for next page link
     * @var string
     */
    protected $next_page_marker;

    /**
     * Character, word, or tag used for prev page link
     * @var string
     */
    protected $prev_page_marker;

    /**
     * Path to template file
     * @var string
     */
    protected $template;

    public function __construct()
    {
        $this->template = new \Template;
        $this->template->setFile(PHPWS_SOURCE_DIR . 'Global/Templates/Pager/default.html');

        $this->first_marker = t('First');
        $this->last_marker = t('Last');
    }

    /**
     * Sets the total number of items found in the select query.
     * @param integer $rows
     * @throws Exception Thrown if integer not sent.
     */
    public function setRowsPerPage($rows)
    {
        if (!is_integer($rows)) {
            throw new Exception(t('setTotalItems expected an integer'));
        }
        $this->total_items = (int) $rows;
    }

    /**
     *
     * @param integer $page_no
     * @throws Exception Thrown if integer not sent.
     */
    public function setPageLinkNumber($page_no)
    {
        if (!is_integer($page_no)) {
            throw new Exception(t('setPageLinkNumber integer'));
        }
        $this->page_link_number = $page_no;
    }

    /**
     * Set the page to display to the user.
     * @param integer $page
     * @throws Exception
     */
    public function setCurrentPage($page)
    {
        if (!is_integer($page)) {
            throw new Exception(t('setCurrentPage integer'));
        }
    }

    /**
     * First string used as a link to the first page.
     * @param string $marker
     */
    public function setFirstMarker($marker)
    {
        $this->first_marker = $marker;
    }

    /**
     * @param array $rows
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;
        $row = current($rows);
        $keys = array_keys($row);
        foreach ($keys as $header) {
            $this->headers[$header] = ucwords(str_replace('_', ' ', $header));
        }
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getTemplateArray()
    {

    }

    /**
     * Sorts the rows currently set in the Pager. If the rows displayed by the
     * pager are a partial set, you may not want to use this method.
     */
    public function sortCurrentRows()
    {
        $args = func_get_args();
        usort($this->rows,
                call_user_func_array(array('self', 'make_comparer'), $args));
    }

    /**
     * This is a callable component of usort.
     *
     * For simple ascending sorts (multiple column included):
     * usort($row, make_comparer('column_name'[, 'other_column_name']);
     *
     * For setting a descending sort
     * usort($rows, make_comparer(array('column_name', SORT_DESC)));
     *
     * To include a function result on a column
     * usort($rows, make_comparer(array('column_name', SORT_ASC, 'function_name')));
     *
     * From stackoverflow.com : user - jon
     * http://stackoverflow.com/questions/96759/how-do-i-sort-a-multidimensional-array-in-php
     * http://stackoverflow.com/users/50079/jon
     * @return type
     */
    public static function make_comparer()
    {
        // Normalize criteria up front so that the comparer finds everything tidy
        $criteria = func_get_args();
        foreach ($criteria as $index => $criterion) {
            $criteria[$index] = is_array($criterion) ? array_pad($criterion, 3,
                            null) : array($criterion, SORT_ASC, null);
        }

        return function($first, $second) use ($criteria) {
                    foreach ($criteria as $criterion) {
                        // How will we compare this round?
                        list($column, $sortOrder, $projection) = $criterion;
                        $sortOrder = $sortOrder === SORT_DESC ? -1 : 1;

                        // If a projection was defined project the values now
                        if ($projection) {
                            $lhs = call_user_func($projection, $first[$column]);
                            $rhs = call_user_func($projection, $second[$column]);
                        } else {
                            $lhs = $first[$column];
                            $rhs = $second[$column];
                        }

                        // Do the actual comparison; do not return if equal
                        if ($lhs < $rhs) {
                            return -1 * $sortOrder;
                        } else if ($lhs > $rhs) {
                            return 1 * $sortOrder;
                        }
                    }

                    return 0; // tiebreakers exhausted, so $first == $second
                };
    }

    public function createSortHeaders()
    {
        $icon = '<i class="icon-arrow-down"></i>';
        foreach ($this->headers as $column_name => $print_name)
        {
            $rows[] = "<a href='#' data-column_name='$column_name'>$print_name $icon</a>";
        }
        return $rows;
    }

    public function get()
    {
        $this->template->add('headers', $this->createSortHeaders());
        $this->template->add('rows', $this->rows);
        return $this->template->__toString();
    }

    public function __toString()
    {
        return $this->get();
    }

}

?>