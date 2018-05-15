<?php

namespace phpws2;


require_once PHPWS_SOURCE_DIR . 'src/Array.php';
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Pager
{
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
     * Number of pages containing the current rows.
     * @var integer
     */
    protected $number_of_pages;

    /**
     * Currently page viewed.
     * @var integer
     */
    protected $current_page = 1;

    /**
     * A function called on the rows set to be displayed.
     * @var string|array
     */
    protected $callback;

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
     * @var \phpws2\Template
     */
    protected $template;
    protected $sort_column;
    protected $sort_direction;

    /**
     * The string requested to search for a specific row
     * @var string
     */
    protected $search_phrase;

    /**
     * User requested column used with search_phrase to find matching rows.
     * @var string
     */
    protected $search_column;

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

    /**
     * Column that identifies the row
     * @var string
     */
    protected $row_id_column;

    /**
     * Data added from addJsonData. Inserted during assignData.
     * @var array
     */
    protected $json_data;

    /**
     * Column user can search on
     * Defaults to headers.
     * @var array
     */
    protected $search_columns = array();

    public function __construct()
    {
        javascript('jquery');

        $request = \Canopy\Server::getCurrentRequest();
        if ($request->isVar('sort_by') && $request->isVar('direction')) {
            $column = $request->getVar('sort_by');
            $direction = $request->getVar('direction');
            if (!empty($column)) {
                $this->setSortBy($column, $direction);
            }
        }

        if ($request->isVar('row_per_page')) {
            $this->setRowsPerPage((int) $request->getVar('row_per_page'));
        }

        if ($request->isVar('current_page')) {
            $this->setCurrentPage((int) $request->getVar('current_page'));
        }

        if ($request->isVar('search_phrase')) {
            $this->setSearchPhrase($request->getVar('search_phrase'));
        }

        if ($request->isVar('search_column')) {
            $this->setSearchColumn($request->getVar('search_column'));
        }

        $this->next_page_marker = "<i class='fa fa-forward'></i>";
        $this->prev_page_marker = "<i class='fa fa-backward'></i>";
    }

    public static function prepare()
    {
        javascript('jquery');
        $source_http = PHPWS_SOURCE_HTTP;
        $header = "<script type='text/javascript' src='{$source_http}src-phpws2/templates/Pager/pager.js'></script>";
        \Layout::addJSHeader($header);
    }

    public function setSearchPhrase($phrase)
    {
        $this->search_phrase = preg_replace('/\s{2,}/', ' ', trim(rawurldecode($phrase)));
    }

    public function setSearchColumn($column)
    {
        $this->search_column = trim(rawurldecode($column));
    }

    public function setTemplate(Template $template)
    {
        if (!is_file($template->getFile())) {
            throw new \Exception(sprintf('Could not find template file: %t', $template->getFile()));
        }
        $this->template = $template;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function setId($id)
    {
        $attr = new \phpws2\Variable\Attribute($id);
        $this->id = $attr->get();
    }

    public function setLastMarker($marker)
    {
        $this->last_marker = $marker;
    }

    public function getId()
    {
        if (empty($this->id)) {
            $this->id = \Canopy\TextString::randomString(12);
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
            throw new \Exception('setTotalItems expected an integer');
        }
        $this->rows_per_page = (int) $rows;
    }

    public function setSortBy($column_name, $direction = null)
    {
        if (!isset($direction)) {
            $direction = SORT_ASC;
        }
        $this->sort_direction = $direction;
        $this->sort_column = $column_name;
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
            throw new \Exception('setMaxPageLinks expects an integer');
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
            throw new \Exception('setCurrentPage integer');
        }
        $this->current_page = $page;
    }

    public function setRowIdColumn($column)
    {
        $this->row_id_column = $column;
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

    /**
     * Sets the headers for the pager. If an associative array is received, the
     * keys will be what is sorted on and the value will be what is displayed.
     *
     * If the array is not associative, the column names will be edited (case changed
     * and spaces added) and displayed.
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        if (\Canopy\is_assoc($headers)) {
            $this->addSearchColumn($headers);
            $this->headers = $headers;
        } else {
            foreach ($headers as $header) {
                $this->headers[$header] = ucwords(str_replace('_', ' ', $header));
                $this->addSearchColumn($header);
            }
        }
    }

    public function removeSearchColumn($column)
    {
        unset($this->search_columns[$column]);
    }

    public function addSearchColumn($column, $full_name = null)
    {
        if (is_array($column)) {
            if (is_array(current($column))) {
                throw new \Exception('Multidimensional array parameter not allowed');
            }
            foreach ($column as $key => $value) {
                $this->addSearchColumn($key, $value);
            }
        } else {
            if (!isset($this->search_columns[$column])) {
                if (empty($full_name)) {
                    $full_name = ucwords(str_replace('_', ' ', $column));
                }
                $this->search_columns[$column] = $full_name;
            }
        }
    }

    public function getAllRows()
    {
        if (!empty($this->sort_column)) {
            $this->sortCurrentRows();
        }
        return $this->rows;
    }

    /**
     * Sorts the rows currently set in the Pager. If the rows displayed by the
     * pager are a partial set, you may not want to use this method.
     */
    public function sortCurrentRows($function_call = null)
    {
        if (empty($this->sort_direction)) {
            $this->sort_direction = SORT_ASC;
        }
        if (empty($this->rows)) {
            throw new \Exception('No rows to set');
        }
        if (!isset($this->headers[$this->sort_column])) {
            throw new \Exception(sprintf('Column name "%s" is not known', $this->sort_column));
        }

        if (isset($function_call) && !function_exists($function_call)) {
            throw new \Exception(sprintf('Function "%s" does not exist', $function_call));
        }
        usort($this->rows, call_user_func_array(array('self', 'make_comparer'), array(array($this->sort_column, $this->sort_direction, $function_call))));
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
            $criteria[$index] = is_array($criterion) ? array_pad($criterion, 3, null) : array($criterion, SORT_ASC, null);
        }

        return function($first, $second) use ($criteria) {
            foreach ($criteria as $criterion) {
                // How will we compare this round?
                list($column, $checkOrder, $projection) = $criterion;
                $sortOrder = $checkOrder == SORT_DESC ? -1 : 1;

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
        $icon_down = '<i class="fa fa-sort-asc"></i>';
        $icon_up = '<i class="fa fa-sort-desc"></i>';
        $icon_stay = '<i class="fa fa-sort"></i>';
        foreach ($this->headers as $column_name => $print_name) {
            if ($this->sort_column == $column_name) {
                switch ($this->sort_direction) {
                    case SORT_DESC:
                        $sort = SORT_REGULAR;
                        $icon = $icon_up;
                        break;
                    case SORT_ASC:
                        $sort = SORT_DESC;
                        $icon = $icon_down;
                        break;
                    default:
                        $icon = $icon_stay;
                        $sort = SORT_ASC;
                        break;
                }
            } else {
                $sort = SORT_ASC;
                $icon = $icon_stay;
            }
            $rows[$column_name] = "<a href='javascript:void(0)' data-direction='$sort' data-column-name='$column_name' class='sort-header'>$print_name $icon</a>";
        }
        return $rows;
    }

    public function getNumberOfPages()
    {
        if (isset($this->number_of_pages)) {
            return $this->number_of_pages;
        }

        $this->number_of_pages = ceil($this->total_rows / $this->rows_per_page);
        return $this->number_of_pages;
    }

    public function populateTemplate()
    {
        if (empty($this->template)) {
            throw new \Exception('Template not set');
        }
        $this->template->add('pager_id', $this->getId());
        $this->template->add('pager_javascript', $this->getJavascript());
    }

    protected function getJavascript()
    {
        $source_http = PHPWS_SOURCE_HTTP;
        \Layout::addJSHeader("<script type='text/javascript' src='{$source_http}src-phpws2/templates/Pager/pager.js'></script>");
    }

    public function getJson()
    {
        if (empty($this->rows)) {
            $this->processRows();
        }

        return $this->assignData();
    }

    public function addJsonData($key, $value)
    {
        $this->json_data[$key] = $value;
    }

    protected function assignData()
    {
        if (!empty($this->json_data)) {
            $data = $this->json_data;
        } else {
            $data = array();
        }
        $data['headers'] = $this->getHeaders();
        $data['total_rows'] = $this->total_rows;
        $data['current_page'] = $this->current_page;
        $data['rows_per_page'] = $this->rows_per_page;
        $data['page_listing'] = $this->getPageListing();
        $data['page_limit'] = $this->getPageLimit();
        $data['search_column'] = $this->search_column;
        $data['pager_search'] = $this->getPageSearch();
        $data['row_id_column'] = $this->row_id_column;
        $data['rows'] = $this->rows;
        $data['page_count'] = $this->getNumberOfPages();
        return $data;
    }

    public function processRows()
    {
        if (!empty($this->search_phrase)) {
            $this->filterRows();
            if ($this->current_page > $this->getNumberofPages()) {
                $this->current_page = 1;
            }
        }
        if (!empty($this->sort_column) && $this->sort_direction != 0) {
            $this->sortCurrentRows();
        }
        $start_count = ($this->current_page - 1) * $this->rows_per_page;
        $this->rows = array_slice($this->rows, $start_count, $this->rows_per_page);

        $this->executeCallback();
    }

    protected function executeCallback()
    {
        if (!empty($this->callback)) {
            foreach ($this->rows as $key => $value) {
                if (is_string($this->callback) || is_array($this->callback)) {
                    $this->rows[$key] = call_user_func($this->callback, $value);
                } else {
                    throw new \Exception('Unknown callback type');
                }
            }
        }
    }

    private function filterRows()
    {
        if (empty($this->search_phrase)) {
            return;
        }
        $new_rows = array();
        $search_array = explode(' ', $this->search_phrase);
        foreach ($this->rows as $row) {
            foreach ($search_array as $find) {
                if (stristr(implode(' ', $row), $find)) {
                    $new_rows[] = $row;
                    break;
                }
            }
        }
        $this->rows = $new_rows;
        $this->total_rows = count($new_rows);
    }

    /**
     * Sets marker used for next button
     * @param string $marker
     */
    public function setNextPageMarker($marker)
    {
        $this->next_page_marker = $marker;
    }

    /**
     * Returns the search input field
     * @return string
     */
    public function getPageSearch()
    {
        $search = 'Search';

        if ($this->search_column) {
            $search .= ' : ' . $this->search_columns[$this->search_column];
        }

        if (!empty($this->search_phrase)) {
            $icon = '<span class="input-group-append"><a href=""><a href="javascript:void(0)" class="search-clear"><i class="fa fa-remove"></i></a></span>';
        } else {
            $icon = null;
        }
        $columns = '';

        //foreach ($this->headers as $key => $value) {
        foreach ($this->search_columns as $key => $value) {
            $columns .= "<li><a data-search-column='$key' class='pager-search-column dropdown-item' href='javascript:void(0)'>$value</a></li>\n";
        }
        $content = <<<EOF
<div class="input-group">
    <div class="input-group-prepend">
        <button class="btn btn-default pager-search-submit">$search</button>
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
        <ul class="dropdown-menu">
            $columns
        </ul>
    </div>
    <input type="text" name="search_box" class="input-sm form-control search-query" value="$this->search_phrase" />$icon
</div>
EOF;
        return $content;
    }

    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Sets marker used for previous button
     * @param string $marker
     */
    public function setPreviousPageMarker($marker)
    {
        $this->prev_page_marker = $marker;
    }

    public function getPageLimit()
    {
        $rows_per_page = $this->rows_per_page;

        $limits[] = 10;
        $limits[] = 25;
        $limits[] = 50;
        $limits[] = 100;

        $content[] = '<div class="form-inline"><div class="form-group"><label>Rows per page: <select class="pager-rpp form-control"></label></div></div>';
        foreach ($limits as $val) {
            $selected = $rows_per_page == $val ? 'selected' : null;
            $content[] = "<option $selected>$val</option>\n";
        }
        $content[] = '</select>';

        return implode("\n", $content);
    }

    public function getPageListing()
    {
        if ($this->total_rows < 1) {
            return 'No result found';
        }
        $penultimate = $this->getNumberOfPages() - 1;
        $content[] = '<ul class="pagination">';

        if ($this->getNumberOfPages() > $this->max_page_links) {
            $halfway = floor($this->max_page_links / 2);
            $left = $this->current_page - $halfway + 2;
            $right = $this->current_page + $halfway - 2;
            if ($left < 2) {
                $right += ($left * -1) + 2;
                $left = 1;
            }

            if ($right >= $penultimate) {
                $left -= $right - $penultimate;
                $right = $penultimate - 1;
            }

            $left_select = ($this->current_page - $halfway) > 1;
            $right_select = ($this->current_page + $halfway) <= $penultimate;
        } else {
            $left_select = $right_select = false;
            $left = 1;
            $right = $this->getNumberOfPages();
        }

        if ($this->current_page > 1) {
            $count = $this->current_page - 1;
            $content[] = "<li><a href='javascript:void(0)' data-page-no='$count' class='pager-page-no'>$this->prev_page_marker</a></li>";
        }

        $current_page = $this->current_page == 1 ? ' class="active"' : null;
        $content[] = "<li$current_page><a href='javascript:void(0)' data-page-no='1' class='pager-page-no'>1</a></li>";

        if ($this->getNumberOfPages() > 1) {
            $current_page = $this->current_page == 2 ? ' class="active"' : null;
            $content[] = "<li$current_page><a href='javascript:void(0)' data-page-no='2' class='pager-page-no'>2</a></li>";
        }

        if ($this->getNumberOfPages() > 2) {
            if ($left_select) {
                $content[] = "<li><a href='javascript:void(0)' class='btn-disabled disabled'>&hellip;</a></li>";
            }
            for ($i = $left; $i <= $right; $i++) {
                if ($i < 3 || $i >= $penultimate) {
                    continue;
                }
                if ($i == $this->current_page) {
                    $content[] = "<li class='active'><a href='javascript:void(0)' data-page-no='$i' class='pager-page-no'>$i</a></li>";
                } else {
                    $content[] = "<li><a href='javascript:void(0)' data-page-no='$i' class='pager-page-no'>$i</a></li>";
                }
            }
            if ($right_select) {
                $content[] = "<li><a href='javascript:void(0)' class='disabled'>&hellip;</a></li>";
            }

            if ($penultimate > 2) {
                $current_page = $this->current_page == $penultimate ? ' class="active"' : null;
                $content[] = "<li$current_page><a href='javascript:void(0)' data-page-no='$penultimate' class='pager-page-no'>$penultimate</a></li>";
            }

            $current_page = $this->current_page == $this->number_of_pages ? ' class="active"' : null;
            $content[] = "<li$current_page><a href='javascript:void(0)' data-page-no='$this->number_of_pages' class='pager-page-no'>$this->number_of_pages</a></li>";
        }
        if ($this->current_page != $this->number_of_pages) {
            $forward = $this->current_page + 1;
            $content[] = "<li><a href='javascript:void(0)' data-page-no='{$forward}' class='pager-page-no'>$this->next_page_marker</a></li>";
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
