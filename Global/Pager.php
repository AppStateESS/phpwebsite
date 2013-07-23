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
    protected $rows_per_page = 10;

    /**
     * Number of page links to show.
     * @var integer
     */
    protected $max_page_links = 8;

    /**
     * Currently page viewed.
     * @var integer
     */
    protected $current_page = 1;

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
     * Template object
     * @var \Template
     */
    protected $template;

    /**
     * What column and direction we are sorting in
     * $this->sort_by['column']
     * $this->sort_by['direction']
     * @var array
     */
    protected $sort_by;

    /**
     * Array of sorting headers for top of pager
     * @var array
     */
    protected $headers;

    /**
     * Id of pager table. This can either be set by the programmer or
     * it will get defaulted and set to the $pager_id variable. If missing
     * from the pager template, the pager will not work.
     * @var string
     */
    protected $id;
    protected $data_url;

    public function __construct()
    {
        javascript('jquery');

        $this->first_marker = t('First');
        $this->last_marker = t('Last');

        $request = \Server::getCurrentRequest();
        if ($request->isVar('sort_by') && $request->isVar('direction')) {
            $column = $request->getVar('sort_by');
            $direction = $request->getVar('direction');
            if (!empty($column)) {
                $this->setSortBy($column, $direction);
            }
        }

        if ($request->isVar('rpp')) {
            $this->setRowsPerPage((int) $request->getVar('rpp'));
        }
        if ($request->isVar('current_page')) {
            $this->setCurrentPage((int) $request->getVar('current_page'));
        }

        $this->next_page_marker = "<i class='icon-forward'></i>";
        $this->prev_page_marker = "<i class='icon-backward'></i>";
        $this->first_marker = "<i class='icon-fast-backward'></i>";
        $this->last_marker = "<i class='icon-fast-forward'></i>";
    }

    public function setTemplate(\Template $template)
    {
        if (!is_file($template->getFile())) {
            throw new \Exception(t('Could not find template file: %t',
                    $template->getFile()));
        }
        $this->template = $template;
    }

    public function setId($id)
    {
        $attr = new \Variable\Attribute($id);
        $this->id = $attr->get();
    }

    public function setLastMarker($marker)
    {
        $this->last_marker = $marker;
    }

    public function getId()
    {
        if (empty($this->id)) {
            $this->id = randomString(12);
        }
        return $this->id;
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
        $this->rows_per_page = (int) $rows;
    }

    public function setSortBy($column_name, $direction = null)
    {
        if (!isset($direction)) {
            $direction = SORT_ASC;
        }
        $this->sort_by['column'] = $column_name;
        $this->sort_by['direction'] = $direction;
    }

    /**
     * Sets the total amount of rows in use. By default, pager uses a count of
     * the rows sent to setRows. This can be overwritten or prevented by
     * setting the total_rows value here.
     *
     * @param integer $total_rows
     */
    public function setTotalRows($total_rows)
    {
        $this->total_rows = (int) $total_rows;
    }

    /**
     *
     * @param integer $page_no
     * @throws Exception Thrown if integer not sent.
     */
    public function setMaxPageLinks($page_no)
    {
        if (!is_integer($page_no)) {
            throw new Exception(t('setMaxPageLinks expects an integer'));
        }
        $this->max_page_links = $page_no;
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
        $this->current_page = $page;
    }

    /**
     * @param array $rows
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;
        if (!isset($this->total_rows)) {
            $this->total_rows = count($rows);
        }
    }

    public function setHeaders(array $headers)
    {
        if (is_assoc($headers)) {
            $this->headers = $headers;
        } else {
            foreach ($headers as $header) {
                $this->headers[$header] = ucwords(str_replace('_', ' ', $header));
            }
        }
    }

    public function getAllRows()
    {
        if (!empty($this->sort_by)) {
            $this->sortCurrentRows($this->sort_by);
        }
        return $this->rows;
    }

    /**
     * Sorts the rows currently set in the Pager. If the rows displayed by the
     * pager are a partial set, you may not want to use this method.
     */
    public function sortCurrentRows($column_name, $direction = null, $function_call = null)
    {
        if (empty($direction)) {
            $direction = SORT_ASC;
        }
        if (empty($this->rows)) {
            throw new \Exception(t('No rows to set'));
        }
        if (!isset($this->headers[$column_name])) {
            throw new \Exception(t('Column name "%s" is not known', $column_name));
        }

        if (isset($function_call) && !function_exists($function_call)) {
            throw new \Exception(t('Function "%s" does not exist',
                    $function_call));
        }
        usort($this->rows,
                call_user_func_array(array('self', 'make_comparer'),
                        array(array($column_name, $direction, $function_call))));
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
                        $sortOrder = $sortOrder == SORT_DESC ? -1 : 1;

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

    public function getHeaders()
    {
        $icon_down = '<i class="icon-chevron-down"></i>';
        $icon_up = '<i class="icon-chevron-up"></i>';
        $icon_stay = '<i class="icon-stop"></i>';
        foreach ($this->headers as $column_name => $print_name) {
            if (isset($this->sort_by[$column_name])) {
                if ($this->sort_by['direction'] == SORT_DESC) {
                    $sort = SORT_REGULAR;
                    $icon = $icon_up;
                } elseif ($this->sort_by['direction'] == SORT_ASC) {
                    $sort = SORT_DESC;
                    $icon = $icon_down;
                } else {
                    $icon = $icon_stay;
                    $sort = SORT_ASC;
                }
            } else {
                $sort = SORT_ASC;
                $icon = $icon_stay;
            }
            $rows[$column_name] = "<a href='javascript:void(0)' data-direction='$sort' data-column-name='$column_name' class='sort-header'>$print_name $icon</a>";
        }
        return $rows;
    }

    public function getHeaderValues()
    {
        $icon = '<i class="icon-arrow-down"></i>';
        foreach ($this->headers as $column_name => $print_name) {
            $rows[] = array('column_name' => $column_name, 'print_name' => $print_name, 'icon' => $icon);
        }
        return $rows;
    }

    public function populateTemplate()
    {
        if (empty($this->template)) {
            throw new \Exception(t('Template not set'));
        }
        if (empty($this->headers)) {
            throw new \Exception(t('Headers not set, cannot populate template'));
        }
        $this->template->add('header_values', $this->getHeaderValues());
        $this->template->add('header', $this->getHeaders());
        $this->template->add('pager_id', $this->getId());
        $this->template->add('pager_javascript', $this->getJavascript());
    }

    protected function getJavascript()
    {
        javascript('jquery');
        $source_http = PHPWS_SOURCE_HTTP;
        \Layout::addJSHeader("<script type='text/javascript' src='{$source_http}Global/Templates/Pager/pager.js'></script>");
    }

    public function getJson()
    {
        $data = null;
        if (empty($this->rows)) {
            return array('rows' => null, 'error' => t('No rows found'));
        }
        if ($this->sort_by && $this->sort_by['direction'] != 0) {
            $this->sortCurrentRows($this->sort_by['column'],
                    $this->sort_by['direction']);
        }

        $data['total_rows'] = $this->total_rows;
        $data['current_page'] = $this->current_page;
        $data['rows_per_page'] = $this->rows_per_page;
        $data['page_listing'] = $this->getPageListing();
        $data['pager_search'] = $this->getPageSearch();
        $start_count = ($this->current_page - 1) * $this->rows_per_page;
        $data['rows'] = array_slice($this->rows, $start_count,
                $this->rows_per_page);
        return $data;
    }

    /**
     * Sets marker used for next button
     * @param string $marker
     */
    public function setNextPageMarker($marker)
    {
        $this->next_page_marker = $marker;
    }

    public function getPageSearch()
    {
        $search = t('Search');
        $content = <<<EOF
<div class="btn-group">
  <input type="text" name="search_box" class="input-medium search-query" />
  <button class="btn btn-small pager-search-submit">$search</button>
  <button class="btn dropdown-toggle btn-small" data-toggle="dropdown">
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    <li>test 1</li>
    <li>test 2</li>
  </ul>
</div>
EOF;
        return $content;
    }

    /**
     * Sets marker used for previous button
     * @param string $marker
     */
    public function setPreviousPageMarker($marker)
    {
        $this->prev_page_marker = $marker;
    }

    public function getPageListing()
    {
        if ($this->total_rows > 0) {
            $number_of_pages = ceil($this->total_rows / $this->rows_per_page);
        }
        $penultimate = $number_of_pages - 1;
        $content[] = '<ul>';

        if ($number_of_pages > $this->max_page_links) {
            $halfway = floor($this->max_page_links / 2);
            $left = $this->current_page - $halfway;
            $right = $this->current_page + $halfway;

            if ($left < 3) {
                $right += ($left * -1);
                $left = 3;
            }

            if ($right >= $penultimate) {
                $left -= $right - $penultimate;
                $right = $penultimate - 1;
            }

            $left_select = ($this->current_page - $halfway) > 3;
            $right_select = ($this->current_page + $halfway) < $penultimate - 1;
        } else {
            $left_select = $right_select = false;
            $left = 3;
            $right = $number_of_pages;
        }

        if ($this->current_page > 1) {
            $count = $this->current_page - 1;
            $content[] = "<li><button data-page-no='$count' class='pager-page-no btn-mini'>$this->prev_page_marker</button></li>";
        }

        $current_page = $this->current_page == 1 ? ' btn-primary' : null;
        $content[] = "<li><button data-page-no='1' class='pager-page-no btn-mini$current_page'>1</button></li>";
        $current_page = $this->current_page == 2 ? ' btn-primary' : null;
        $content[] = "<li><button data-page-no='2' class='pager-page-no btn-mini$current_page'>2</button></li>";
        if ($left_select) {
            $content[] = '<li><button class="btn-disabled btn-mini">&hellip;</button></li>';
        }
        for ($i = $left; $i <= $right; $i++) {
            if ($i == $this->current_page) {
                $content[] = "<li><button data-page-no='$i' class='pager-page-no btn-primary btn-mini'>$i</button></li>";
            } else {
                $content[] = "<li><button data-page-no='$i' class='pager-page-no btn-mini'>$i</button></li>";
            }
        }
        if ($right_select) {
            $content[] = '<li><button class="btn-disabled btn-mini">&hellip;</button></li>';
        }
        $current_page = $this->current_page == $penultimate ? ' btn-primary' : null;
        $content[] = "<li><button data-page-no='$penultimate' class='pager-page-no btn-mini$current_page'>$penultimate</button></li>";
        $current_page = $this->current_page == $number_of_pages ? ' btn-primary' : null;
        $content[] = "<li><button data-page-no='$number_of_pages' class='pager-page-no btn-mini$current_page'>$number_of_pages</button></li>";
        if ($this->current_page != $number_of_pages) {
            $forward = $this->current_page + 1;
            $content[] = "<li><button data-page-no='{$forward}' class='pager-page-no btn-mini'>$this->next_page_marker</button></li>";
        }
        $content[] = '</ul>';
        return implode('', $content);
    }

    public function get()
    {
        $this->populateTemplate();
        return $this->template->__toString();
    }

    public function __toString()
    {
        return $this->get();
    }

    public function setDataUrl($url)
    {
        $this->data_url = $url;
    }

}

?>