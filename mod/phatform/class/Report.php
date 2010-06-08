<?php

require_once(PHPWS_SOURCE_DIR . 'core/class/Text.php');

/**
 * PHAT_Rport class for reporting on PHAT_Form data
 *
 * @version $Id$
 * @author  Adam Morton
 * @author  Steven Levin
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 */
class PHAT_Report {

    /**
     * Id of the current form to report on
     *
     * @var    integer
     * @access private
     */
    var $_formId = NULL;

    /**
     * Name of the current form to report on
     *
     * @var    string
     * @access private
     */
    var $_formName = NULL;

    /**
     * Entries for this form
     *
     * @var    array
     * @access private
     */
    var $_entries = NULL;

    /**
     * The total number of entries for this form
     *
     * @var    integer
     * @access private
     */
    var $_totalEntries = NULL;

    /**
     * The number of completed entries for this form
     *
     * @var    integer
     * @access private
     */
    var $_completeEntries = NULL;

    /**
     * The number of imcomlete entries for this form
     *
     * @var    integer
     * @access private
     */
    var $_incompleteEntries = NULL;

    /**
     * The current search query for this report
     *
     * This only searches usernames of users who have taken the form
     *
     * @var    string
     * @access private
     */
    var $_searchQuery = NULL;

    /**
     * The current filter being placed on the current entries
     *
     * Controls whether or not to list all, complete, or incomplete entries
     *
     * @var    integer
     * @access private
     */
    var $_listFilter = NULL;

    /**
     * Stores data for paging
     *
     * @var    integer
     * @access public
     */
    var $pageStart = NULL;

    /**
     * Stores data for paging
     *
     * @var    integer
     * @access public
     */
    var $pageSection = NULL;

    /**
     * Stores data for paging
     *
     * @var    integer
     * @access public
     */
    var $pageLimit = NULL;

    var $archive = NULL;

    /**
     *
     *
     *
     *
     */
    function PHAT_Report($archiveTable=NULL) {
        if(isset($archiveTable)) {
            $this->archive = $archiveTable;
        }

        $this->_formId = $_SESSION['PHAT_FormManager']->form->getId();
        $this->_formName = $_SESSION['PHAT_FormManager']->form->getLabel();

        $this->_searchQuery = NULL;
        $this->_listFilter = 1;
        $this->setEntries();
        $this->setComplete();
        $this->setIncomplete();

        $this->_totalEntries = sizeof($this->_entries);

        $this->pageStart = 0;
        $this->pageSection = 1;
        $this->pageLimit = PHAT_ENTRY_LIST_LIMIT;
    }

    function setArchive($tableName) {
        $this->archive = $tableName;
    }

    function getFormTable() {
        if(isset($this->archive)) {
            return $this->archive;
        } else {
            return 'mod_phatform_form_' . $this->_formId;
        }
    }

    /**
     *
     *
     *
     *
     */
    function report() {
        $content = $_SESSION['PHAT_FormManager']->menu();
        $content .= $this->formStats();
        $content .= $this->listEntries();

        return $content;
    }

    /**
     *
     *
     *
     *
     */
    function formStats() {
        $statsTags['FORM_NAME'] = $this->_formName;
        $statsTags['COMPLETED_LABEL'] = dgettext('phatform', 'Completed');
        $statsTags['COMPLETED_NUM'] = $this->_completeEntries;
        $statsTags['INCOMPLETE_LABEL'] = dgettext('phatform', 'Incomplete');
        $statsTags['INCOMPLETE_NUM'] = $this->_incompleteEntries;
        $statsTags['TOTAL_LABEL'] = dgettext('phatform', 'Total');
        $statsTags['TOTAL_NUM'] = $this->_totalEntries;

        $statsTags['LAST_ENTRY_LABEL'] = dgettext('phatform', 'Last Entry');
        $statsTags['LAST_ENTRY'] = $this->getLastEntry();

        $statsTags['LIST_LINK'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=list&amp;PHAT_FullList=1">' . dgettext('phatform', 'Full List') . '</a>';

        $statsTags['PRINT'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=list&amp;lay_quiet=1" target="_blank">' . dgettext('phatform', 'Print') . '</a>';

        if(isset($_REQUEST['ARCHIVE_OP']))
        $statsTags['BACK_LINK'] = $_SESSION['PHAT_advViews']->archiveBack();

        $elements = array();
        $elements[0] = Core\Form::formHidden('PHAT_REPORT_OP', 'export');
        $elements[0] .= Core\Form::formHidden('module', 'phatform');
        $elements[0] .= Core\Form::formSubmit(dgettext('phatform', 'Export'), 'export');

        if(!isset($this->archive))
        $statsTags['EXPORT'] = Core\Form::makeForm('export_button', 'index.php', $elements);

        return Core\Template::processTemplate($statsTags, 'phatform', 'report/stats.tpl');
    }

    /**
     *
     *
     *
     *
     */
    function listEntries() {
        if(isset($_REQUEST['PHAT_EntrySearch'])) {
            $this->_searchQuery = Core\Text::parseInput($_REQUEST['PHAT_EntrySearch']);
            $this->_listFilter = $_REQUEST['PHAT_ListFilter'];
            $this->setEntries();
            $this->pageStart = 0;
            $this->pageSection = 1;
            $this->pageLimit = $_REQUEST['PDA_limit'];
        } elseif(isset($_REQUEST['PHAT_FullList'])) {
            $this->_searchQuery = NULL;
            $this->_listFilter = 1;
            $this->setEntries();
            $this->pageStart = 0;
            $this->pageSection = 1;
        } else {
            if(isset($_REQUEST['PDA_start'])) {
                $this->pageStart = $_REQUEST['PDA_start'];
            } else {
                $_REQUEST['PDA_start'] = $this->pageStart;
            }

            if(isset($_REQUEST['PDA_section'])) {
                $this->pageSection = $_REQUEST['PDA_section'];
            } else {
                $_REQUEST['PDA_section'] = $this->pageSection;
            }

            if(isset($_REQUEST['PDA_limit'])) {
                $this->pageLimit = $_REQUEST['PDA_limit'];
            } else {
                $_REQUEST['PDA_limit'] = $this->pageLimit;
            }
        }

        $listTags = array();
        $listTags['ID_LABEL'] = dgettext('phatform', 'ID');
        $listTags['USER_LABEL'] = dgettext('phatform', 'User');
        $listTags['UPDATED_LABEL'] = dgettext('phatform', 'Updated');
        $listTags['ACTION_LABEL'] = dgettext('phatform', 'Action');

        $highlight = ' class="bgcolor1"';
        if(sizeof($this->_entries) > 0) {
            $data = PHAT_Report::paginateDataArray($this->_entries, 'index.php?module=phatform&amp;PHAT_REPORT_OP=list', $this->pageLimit, TRUE, array('<b>[ ', ' ]</b>'), NULL, 10, TRUE);
        }

        $count = 1;
        if(isset($data) && is_array($data[0]) && (sizeof($data[0]) > 0)) {
            $listTags['LIST_ITEMS'] = NULL;
            foreach($data[0] as $entry) {
                $highlight = null;
                $rowTags = array();
                $rowTags['HIGHLIGHT'] = $highlight;
                $rowTags['ID'] = $entry['id'];
                $rowTags['USER'] = $entry['user'];
                $rowTags['UPDATED'] = date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $entry['updated']);

                $rowTags['VIEW'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=view&amp;PHAT_ENTRY_ID=' . $entry['id'] . '">' . dgettext('phatform', 'View') . '</a>';

                if(!isset($this->archive)) {
                    $rowTags['EDIT'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=edit&amp;PHAT_ENTRY_ID=' . $entry['id'] . '">' . dgettext('phatform', 'Edit') . '</a>';
                    $rowTags['DELETE'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=confirmDelete&amp;PHAT_ENTRY_ID=' . $entry['id'] . '">' . dgettext('phatform', 'Delete') . '</a>';
                }

                if ($count%2) {
                    $highlight = ' class="bgcolor1"';
                }
                $count ++;
                $listTags['LIST_ITEMS'] .= Core\Template::processTemplate($rowTags, 'phatform', 'report/row.tpl');
            }

            if(!isset($_REQUEST['lay_quiet'])) {
                if(($this->_totalEntries > $this->pageLimit)) {
                    $listTags['NAVIGATION_LINKS'] = $data[1];
                }

                $listTags['SECTION_INFO'] = $data[2];
                $listTags['SECTION_INFO_LABEL'] = dgettext('phatform', 'Entries');
            }
        } else {
            $listTags['LIST_ITEMS'] = '<tr><td colspan="4" class="smalltext">' . dgettext('phatform', 'No entries were found matching your search query.') . '</td></tr>';
        }

        if(!isset($_REQUEST['lay_quiet'])) {
            $filterOptions = array(1=>dgettext('phatform', 'All'), 2=>dgettext('phatform', 'Incomplete'), 3=>dgettext('phatform', 'Complete'));
            $limitOptions = array(10=>10, 20=>20, 30=>30, 40=>40, 50=>50);

            $elements[0] = Core\Form::formHidden('module', 'phatform');
            $elements[0] .= Core\Form::formHidden('PHAT_REPORT_OP', 'list');
            $elements[0] .= Core\Form::formSelect('PHAT_ListFilter', $filterOptions, $this->_listFilter, FALSE, TRUE);

            $elements[0] .= Core\Form::formSelect('PDA_limit', $limitOptions, $this->pageLimit, TRUE);

            if(!$_SESSION['PHAT_FormManager']->form->isAnonymous()) {
                $elements[0] .= Core\Form::formTextField('PHAT_EntrySearch', $this->_searchQuery, 20, 255);
            }

            $elements[0] .= Core\Form::formSubmit(dgettext('phatform', 'Search'));
            $listTags['SEARCH_FORM'] = Core\Form::makeForm('PHAT_SearchEntries', 'index.php', $elements);
        }

        $GLOBALS['CNT_phatform']['title'] = $_SESSION['PHAT_FormManager']->form->getLabel();
        return Core\Template::processTemplate($listTags, 'phatform', 'report/list.tpl');
    }

    /**
     *
     *
     *
     *
     */
    function view($showLinks = TRUE) {
        /* Find the key into the entries array for the selected entry */
        foreach($this->_entries as $entryKey=>$entryValue) {
            if($entryValue['id'] == $_REQUEST['PHAT_ENTRY_ID'])
            break;
        }

        /* Get the data for the selected entry from the database */
        $sql = 'SELECT * FROM ' . $this->getFormTable() . " WHERE id='" . $_REQUEST['PHAT_ENTRY_ID'] . "'";
        $entry = Core\DB::getRow($sql);

        $rowClass = NULL;
        $entryTags = array();
        $entryTags['ENTRY_DATA'] = NULL;
        /* Step through the entries values and feed them through the entryRow template */
        $toggle = 1;
        foreach($entry as $key=>$value) {
            $rowTags = array();
            if($key == 'position') {
                continue;
            } elseif($key == 'updated') {
                $value = date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $value);
            }

            $attribute = ' class="bgcolor1" ';

            /* Toggle the row colors for better visability */
            if ($toggle%2) {
                $rowClass = $attribute;
            } else {
                $rowClass = null;
            }
            $toggle++;

            if(isset($rowClass)) {
                $rowTags['ROW_CLASS'] = $rowClass;
            }
            $rowTags['ENTRY_LABEL'] = $key;

            if(preg_match('/a:.:{/', $value)) {
                $rowTags['ENTRY_VALUE'] = implode(', ', unserialize($value));
            } else {
                $rowTags['ENTRY_VALUE'] = Core\Text::parseOutput($value);
            }

            $entryTags['ENTRY_DATA'] .= Core\Template::processTemplate($rowTags, 'phatform', 'report/entryRow.tpl');
        }

        if(isset($this->archive))
        $entryTags['BACK_LINK'] = $_SESSION['PHAT_advViews']->getArchiveViewLink();

        if($showLinks && !isset($_REQUEST['lay_quiet'])) {
            $entryTags['PRINT'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=view&amp;PHAT_ENTRY_ID=' . $_REQUEST['PHAT_ENTRY_ID'] . '&amp;lay_quiet=1" target="_blank">' . dgettext('phatform', 'Print View') . '</a>';

            /* Show the next and/or previous links to step through entries */
            if($entryKey < sizeof($this->_entries) - 1)
            $entryTags['NEXT'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=view&amp;PHAT_ENTRY_ID=' . $this->_entries[$entryKey+1]['id'] . '">' . dgettext('phatform', 'Next Entry') . '</a>';

            if($entryKey > 0)
            $entryTags['PREVIOUS'] = '<a href="index.php?module=phatform&amp;PHAT_REPORT_OP=view&amp;PHAT_ENTRY_ID=' . $this->_entries[$entryKey-1]['id'] . '">' . dgettext('phatform', 'Previous Entry') . '</a>';
        }

        $GLOBALS['CNT_phatform']['title'] = $_SESSION['PHAT_FormManager']->form->getLabel();
        /* Return the entire processed entry */
        if(isset($_REQUEST['lay_quiet']))
        echo Core\Template::processTemplate($entryTags, 'phatform', 'report/entry.tpl');
        else
        return Core\Template::processTemplate($entryTags, 'phatform', 'report/entry.tpl');
    }

    /**
     *
     *
     *
     *
     */
    function edit() {
        $_SESSION['PHAT_FormManager']->form->setEditData(TRUE);
        $_SESSION['PHAT_FormManager']->form->setDataId($_REQUEST['PHAT_ENTRY_ID']);
        $_SESSION['PHAT_FormManager']->form->loadUserData();
        return $_SESSION['PHAT_FormManager']->form->view();
    }

    /**
     *
     *
     *
     *
     */
    function confirmDelete() {
        $hiddens['module'] = 'phatform';
        $hiddens['PHAT_REPORT_OP'] = 'delete';
        $hiddens['PHAT_ENTRY_ID'] = $_REQUEST['PHAT_ENTRY_ID'];
        foreach ($hiddens as $key => $value) {
            $eles[] = Core\Form::formHidden($key, $value);
        }

        $elements[0] = implode("\n", $eles);

        $confirmTags['MESSAGE'] = dgettext('phatform', 'Are you sure you want to delete this entry?');
        $confirmTags['NO_BUTTON'] = Core\Form::formSubmit(dgettext('phatform', 'No'), 'PHAT_DeleteNo');
        $confirmTags['YES_BUTTON'] = Core\Form::formSubmit(dgettext('phatform', 'Yes'), 'PHAT_DeleteYes');

        $elements[0] .= Core\Template::processTemplate($confirmTags, 'phatform', 'report/deleteConfirm.tpl');
        $content = Core\Form::makeForm('PHAT_EntryDeleteConfirm', 'index.php', $elements);
        $content .= '<br /><hr /><br />';
        $content .= $this->view(FALSE);

        return $content;
    }

    /**
     *
     *
     *
     *
     */
    function delete() {
        if(isset($_REQUEST['PHAT_DeleteYes'])) {
            $db = new Core\DB('mod_phatform_form_' . $this->_formId);
            $db->addWhere('id', (int)$_REQUEST['PHAT_ENTRY_ID']);
            $db->delete();

            $_REQUEST['PHAT_REPORT_OP'] = 'list';
            /* Find the key into the entries array for the selected entry */
            foreach($this->_entries as $entryKey=>$entryValue) {
                if($entryValue['id'] == $_REQUEST['PHAT_ENTRY_ID'])
                break;
            }
            unset($this->_entries[$entryKey]);
            $message = dgettext('phatform', 'The form entry was successfully deleted from the database.');
            $this->PHAT_Report();
        } else if(isset($_REQUEST['PHAT_DeleteNo'])) {
            $_REQUEST['PHAT_REPORT_OP'] = 'list';
            $message = dgettext('phatform', 'No form entry was deleted from the database.');
        }

        $GLOBALS['CNT_phatform']['content'] .= $message;
        $this->action();
    }

    /**
     *
     *
     *
     *
     */
    function getLastEntry() {
        $lastEntry = NULL;
        $sql = 'SELECT id, user, MAX(updated) FROM ' . $this->getFormTable() . ' GROUP BY user';
        $result = Core\DB::getAll($sql);

        if(sizeof($result) > 0) {
            $lastEntry = $result[0]['user'] . ' (' . date(PHPWS_DATE_FORMAT, $result[0]['MAX(updated)']) . ')';
        }

        return $lastEntry;
    }

    /**
     *
     *
     *
     *
     */
    function setEntries() {
        $sql = 'SELECT id, user, updated FROM ' . $this->getFormTable();


        if($this->_searchQuery || $this->_listFilter) {
            $sql .= ' WHERE';
        }

        if(isset($this->_searchQuery) && ($this->_searchQuery != '')) {
            $sql .= " user LIKE '%" . $this->_searchQuery . "%' AND";
        }

        if($this->_listFilter) {
            switch($this->_listFilter) {
                case '1':
                    $sql .= " position>='-1'";
                    break;

                case '2':
                    $sql .= " position!='-1'";
                    break;

                case '3':
                    $sql .= " position='-1'";
                    break;
            }
        }

        $result = Core\DB::getAll($sql);

        $this->_entries = $result;
    }

    /**
     *
     *
     *
     *
     */
    function setComplete() {
        $sql = 'SELECT count(id) FROM ' . $this->getFormTable() . " WHERE position='-1'";
        $result = Core\DB::getAll($sql);
        if (Core\Error::isError($result)) {
            return $result;
        }
        $this->_completeEntries = $result[0]['count(id)'];
    }

    /**
     *
     *
     *
     *
     */
    function setIncomplete() {
        $sql = 'SELECT count(id) FROM ' . $this->getFormTable() . " WHERE position!='-1'";
        $result = Core\DB::getAll($sql);
        if (Core\Error::isError($result)) {
            return $result;
        }

        $this->_incompleteEntries = $result[0]['count(id)'];
    }

    function getEntries() {
        return $this->_entries;
    }

    /**
     * Called when a user tries to access functionality he/she has no permission to access
     *
     * @access private
     */
    function _accessDenied() {
        Core\Core::errorPage('400');
    }// END FUNC accessDenied()


    /**
     *
     *
     *
     *
     */
    function action() {
        switch($_REQUEST['PHAT_REPORT_OP']) {
            case 'list':
                if(Current_User::allow('phatform', 'report_view')) {
                    $content = $this->report();
                } else {
                    $this->accessDenied();
                }
                break;

            case 'edit':
                if(Current_User::allow('phatform', 'report_edit')) {
                    $content = $_SESSION['PHAT_FormManager']->menu() . $this->edit();
                } else {
                    $this->accessDenied();
                }
                break;

            case 'view':
                if(Current_User::allow('phatform', 'report_view')) {
                    $content = $_SESSION['PHAT_FormManager']->menu() . $this->view();
                } else {
                    $this->accessDenied();
                }
                break;

            case 'confirmDelete':
                if(Current_User::allow('phatform', 'report_delete')) {
                    $content = $this->confirmDelete();
                } else {
                    $this->accessDenied();
                }
                break;

            case 'delete':
                if(Current_User::allow('phatform', 'report_delete')) {
                    $content = $this->delete();
                } else {
                    $this->accessDenied();
                }
                break;

            case 'export':
                if(Current_User::allow('phatform', 'report_export')) {
                    include(PHPWS_SOURCE_DIR . 'mod/phatform/inc/Export.php');
                    $error = export($this->_formId);
                    if(Core\Error::isError($error)) {
                        javascript('alert', array('content' => Core\Error::printError($error)));
                        $content = $this->report();
                    }
                } else {
                    $this->accessDenied();
                }
                break;
        }

        if($content) {
            if (isset($_REQUEST['lay_quiet'])) {
                Layout::nakedDisplay($content);
            } else {
                $GLOBALS['CNT_phatform']['content'] = $content;
            }
        }
    }// END FUNC action()

    /**
     * paginateDataArray
     *
     * This function will paginate an array of data. While using this function remember to always pass it the same content array
     * and DO NOT alter array during usage unless you are starting back at zero.
     *
     * @author Steven Levin <steven@NOSPAM.tux.appstate.edu>
     * @param  array    $content        Rows of data to be displayed
     * @param  string   $link_back      Link to where data is being displayed (ie. ./index.php?module=search&SEA_search_op=view_results)
     * @param  integer  $default_limit  Number of items to show per page
     * @param  boolean  $make_sections  Flag controls weather section links show up
     * @param  resource $curr_sec_decor HTML to decorate the current section
     * @param  string   $link_class     Style sheet class to use for navigation links
     * @param  integer  $break_point    Number of sections at which the section display will insert ... to show range
     * @return array 0=>string of rows to be displayed, 1=>navigation links for the paginated data, 2=>current section information
     * @access public
     */
    function paginateDataArray($content, $link_back, $default_limit=10, $make_sections=FALSE, $curr_sec_decor=NULL, $link_class=NULL, $break_point=20, $return_array=FALSE){

        if (is_null($curr_sec_decor))
        $curr_sec_decor = array('<b>[ ', ' ]</b>');

        if(isset($_REQUEST['PDA_limit']) && $_REQUEST['PDA_limit'] > 0){
            $limit = $_REQUEST['PDA_limit'];
        } elseif ($default_limit > 0) {
            $limit = $default_limit;
        } else {
            $limit = 10;
        }

        if(isset($_REQUEST['PDA_start'])){
            $start = $_REQUEST['PDA_start'];
        } else {
            $start = 0;
        }

        if(isset($_REQUEST['PDA_section'])){
            $current_section = $_REQUEST['PDA_section'];
        } else {
            $current_section = 1;
        }

        if(is_array($content)){
            $numrows = count($content);
            $sections = ceil($numrows / $limit);
            $content_keys = array_keys($content);
            $string_of_items = '';
            $array_of_items = array();
            $nav_links = '';
            $item_count = 0;
            $pad = 3;

            if (isset($link_class)) {
                $link_class = " class=\"$link_class\"";
            }

            reset($content_keys);
            for($x = 0; $x < $start; $x++){
                next($content_keys);
            }
            while((list($content_key, $content_value) = each($content_keys)) && (($item_count < $limit) && (($start + $item_count) < $numrows ))){
                if($return_array) {
                    $array_of_items[] = $content[$content_keys[$content_key]];
                } else {
                    $string_of_items .= $content[$content_keys[$content_key]] . "\n";
                }

                $item_count++;
            }

            if($start == 0){
                $nav_links = "&#60;&#60;\n";
            } else {
                $nav_links = "<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($start - $limit) . "&#38;PDA_section=" . ($current_section - 1). "\"" . $link_class . "\" title=\"&#60;&#60;\">&#60;&#60;</a>\n";
            }

            if($make_sections && ($sections <= $break_point)){
                for($x = 1; $x <= $sections; $x++){
                    if($x == $current_section){
                        $nav_links .= "&#160;" . $curr_sec_decor[0] . $x . $curr_sec_decor[1] . "&#160;\n";
                    } else {
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($limit * ($x - 1)) . "&#38;PDA_section=" . $x . "\"" . $link_class . "\" title=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    }
                }
            } else if($make_sections && ($sections > $break_point)){
                for($x = 1; $x <= $sections; $x++){
                    if($x == $current_section){
                        $nav_links .= "&#160;" . $curr_sec_decor[0] . $x . $curr_sec_decor[1] . "&#160;\n";
                    } else if($x == 1 || $x == 2){
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($limit * ($x - 1)) . "&#38;PDA_section=" . $x . "\"" . $link_class . "\" title=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    } else if(($x == $sections) || ($x == ($sections - 1))){
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($limit * ($x - 1)) . "&#38;PDA_section=" . $x . "\"" . $link_class . "\" title=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    } else if(($current_section == ($x - $pad)) || ($current_section == ($x + $pad))){
                        $nav_links .= "&#160;<b>. . .</b>&#160;";
                    } else if(($current_section > ($x - $pad)) && ($current_section < ($x + $pad))){
                        $nav_links .= "&#160;<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($limit * ($x - 1)) . "&#38;PDA_section=" . $x . "\"" . $link_class . "\" title=\"" . $x . "\">" . $x . "</a>&#160;\n";
                    }
                }
            } else {
                $nav_links .= "&#160;&#160;\n";
            }

            if(($start + $limit) >= $numrows){
                $nav_links .= "&#62;&#62;\n";
                $section_info = ($start + 1) . " - " . ($start + $item_count) . ' ' . dgettext('phatform', 'of') . ' ' . $numrows . "\n";
            } else {
                $nav_links .= "<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($start + $limit) . "&#38;PDA_section=" . ($current_section + 1) . "\"" . $link_class . "\" title=\"&#62;&#62;\">&#62;&#62;</a>\n";
                $section_info = ($start + 1) . " - " . ($start + $limit) . ' ' . dgettext('phatform', 'of') . ' ' .$numrows . "\n";
            }

        } else {
            exit("Argument 1 to function paginateDataArray not an array.");
        }


        if($return_array) {
            return array(0=>$array_of_items, 1=>$nav_links, 2=>$section_info);
        } else {
            return array("0"=>"$string_of_items", "1"=>"$nav_links", "2"=>"$section_info");
        }
    }// END FUNC paginateDataArray()

}// END CLASS PHAT_Report


?>