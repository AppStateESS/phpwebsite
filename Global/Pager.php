<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Pager {

    /**
     * @var \Database\DB
     */
    protected $db;

    /**
     * Total rows found from the database select
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

    public function __construct()
    {
        $this->first_marker = t('First');
        $this->last_marker = t('Last');
    }

    /**
     * Database object used to pull rows from
     * @param \Database $db
     */
    public function setDB(\Database $db)
    {
        $this->db = $db;
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
}

?>