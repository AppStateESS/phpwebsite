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
        if ($request->isVar('sort_by')) {
            $sort_by = $request->getVar('sort_by');
            if (strstr($sort_by, ':')) {
                list($column, $direction) = explode(':', $sort_by);
            } else {
                $column = $sort_by;
                $direction = null;
            }
            $this->setSortBy($column, $direction);
        }
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
        $this->id = $attr->getValue();
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
        $this->total_items = (int) $rows;
    }

    public function setSortBy($column_name, $direction = null)
    {
        if (empty($direction)) {
            $direction = SORT_ASC;
        }
        $this->sort_by['column'] = $column_name;
        $this->sort_by['direction'] = $direction;
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

    public function getHeaders()
    {
        $icon_down = '<i class="icon-arrow-down"></i>';
        $icon_up = '<i class="icon-arrow-up"></i>';
        foreach ($this->headers as $column_name => $print_name) {
            if (isset($this->sort_by[$column_name])) {
                if ($this->sort_by == 1) {
                    $icon = $icon_up;
                } else {
                    $icon = $icon_down;
                }
            } else {
                $icon = null;
            }
            $rows[$column_name] = "<a href='javascript:void(0)' data-column-name='$column_name' class='sort-header'>$print_name $icon</a>";
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
        $file = PHPWS_SOURCE_DIR . 'Global/Templates/Pager/trigger.html';
        $template = new \Template(null, $file);
        $template->add('data_url', $this->data_url);
        return $template->get();
    }

    public function getJson()
    {
        if ($this->sort_by) {
            $this->sortCurrentRows($this->sort_by['column'],
                    $this->sort_by['direction']);
        }
        if (!empty($this->rows)) {
            $data[$this->id]['rows'] = $this->rows;
        }
        return $data;
    }

    public function get()
    {
        $this->buildTemplate();
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