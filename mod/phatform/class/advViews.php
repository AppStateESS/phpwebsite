<?php

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Form.php');

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Report.php');

/**
 *
 * Archive and Export View Class
 *
 * @version $Id$
 * @author  Darren Greene <dg49379@NOSPAM.tux.appstate.edu>
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */
class advViews {
    var $pageStart;
    var $pageSection;
    var $pageLimit;
    var $filename;

    function advViews() {
        $this->intAdvViews();
    }

    function intAdvViews() {
        $this->pageStart = 0;
        $this->pageSection = 1;
        $this->pageLimit = PHAT_ENTRY_LIST_LIMIT;
    }

    function deleteExport() {
        if(isset($_POST['yes'])){
            if(!isset($_REQUEST['EXPORT_filename'])) {
                $content = dgettext('phatform', 'There was a problem deleting the export.') . '<br /><br />';     
                $content .= $this->viewExports();
                return $content;
            }

            $filename = PHPWS_HOME_DIR . 'files/phatform/export/' . $_REQUEST['EXPORT_filename'];      
            if(is_file($filename) && unlink($filename)) {
                $content = dgettext('phatform', 'The phatform export was successfully <b>deleted</b>.') . '<br /><br />'; 
            } else {
                $content = dgettext('phatform', 'There was a problem deleting the export.') . '<br /><br />';     
            }

            $content .= $_SESSION['PHAT_advViews']->viewExports();
        } elseif (isset($_POST['no'])) {
            $content = sprintf(dgettext('phatform', 'You have chosen <b>not</b> to delete the export with the filename "%s".'), $_REQUEST["EXPORT_filename"]) . '<br /><br />';
            $content .= $_SESSION['PHAT_advViews']->viewExports();
        } else {
            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('EXPORT_OP', 'deleteExport');
            $elements[0] .= PHPWS_Form::formHidden('EXPORT_filename', $_REQUEST['EXPORT_filename']);
            $elements[0] .= PHPWS_Form::formSubmit(dgettext('phatform', 'Yes'), 'yes');
            $elements[0] .= PHPWS_Form::formSubmit(dgettext('phatform', 'No'), 'no');

            $content = sprintf(dgettext('phatform', 'Are you sure you wish to delete the export with filename "<b>%s</b>"?'), $_REQUEST['EXPORT_filename']) . '<br /><br />';
            $content .= PHPWS_Form::makeForm('export_delete', 'index.php', $elements);
        }

        return $content;
    }

    function readyViewArchive($formId, $archiveTableName) {
        $_SESSION['PHAT_FormManager']->form = new PHAT_Form($formId);
        $_SESSION['PHAT_FormManager']->form->report = new PHAT_Report($archiveTableName);

        return $_SESSION['PHAT_FormManager']->form->report->report();
    }

    function archiveBack() {
        return '<a href="./index.php?module=phatform&amp;PHAT_MAN_OP=viewArchives&amp;PDA_Limit='.$this->pageLimit.'&amp;PDA_start='.$this->pageStart.'&amp;PDA_section='.$this->pageSection.'">Archive Listing</a>';
    }

    function viewArchive() {
        $content  = $_SESSION['PHAT_FormManager']->menu();

        $filename = PHPWS_HOME_DIR . 'files/phatform/archive/' . $_REQUEST['ARCHIVE_filename'];      
        if(is_file($filename)) {
            $fileContent = file($filename);
        } else {
            $content .= dgettext('phatform', 'Archive file was not found.');
            $content .= $this->viewArchives();
            return $content;
        }

        if(empty($fileContent)) {
            $content .= dgettext('phatform', 'File contained no content.');
            $content .= $this->viewArchives();
            return $content;
        }

        if(isset($_REQUEST['ARCHIVE_filename']))
            $this->filename = $_REQUEST['ARCHIVE_filename'];

        $buildingSQL = FALSE;
        $endCreateSmnt = 0;
        $formNum = NULL;
        $sql = '';

        // extract out table containing report data
        for($i=0; $i < count($fileContent); $i++) {
            $line = $fileContent[$i];
            if(stristr($line, 'CREATE TABLE mod_phatform_form_') && 
               ($line[0] != '#' && ($line[0] != '-' && $line[1] != '-'))) {
                $buildingSQL = TRUE;
        
                ereg('form_([0-9]+)', $line, $formNumArr);
                $formNum = $formNumArr[1];
            }

            if($buildingSQL == TRUE) {
                $sql .= $line;
            }

            if($buildingSQL == TRUE && stristr($line, ';')) {   
                $endCreateSmnt = $i + 6;
                break;
            }
        }

        if(empty($sql)) {
            $content .= dgettext('phatform', 'File contained no archive to view.');
            $content .= $this->viewArchives();
            return $content;
        }

        $orgnTableName = 'mod_phatform_form_' . $formNum;
        $newTableName = time() . $orgnTableName;
        $sql = str_replace($orgnTableName, $newTableName, $sql);

        $db = new PHPWS_DB('mod_phatform_forms');
        $db->addWhere('archiveTableName', '%' . $orgnTableName . '%', 'LIKE');
        $result = $db->select();
        if($result) {
            foreach($result as $form) {
                if($form['archiveFileName'] == $this->filename)
                    return $this->readyViewArchive($form['id'], $form['archiveTableName']);
            }
        } 

      
        if(isset($_REQUEST['yes'])) {
            // create main report table
            PHPWS_DB::query(trim($sql));   

            $inserts = FALSE;
            for($j=$endCreateSmnt; $j < count($fileContent); $j++) {
                $line = $fileContent[$j];

                // check if finished inserting report data
                if(stristr($line, 'CREATE TABLE'))
                    break;

                // check to see if finished with comments and spaces before insert commands
                if(stristr($line, 'INSERT INTO '))
                    $inserts = TRUE;
        
                // line is insertion data so put in database
                if($inserts) {
                    $sql = trim($line);
                    if(!empty($sql) && stristr($sql, $orgnTableName)) {
                        $sql = str_replace($orgnTableName, $newTableName, $sql);
                        PHPWS_DB::query(trim($sql));   
                    } else {
                        break;
                    }
                }
            }

            // create special archive form so keep track of archived forms
            $data['owner'] = $_SESSION['OBJ_user']->username;
            $data['editor'] = $_SESSION['OBJ_user']->username;
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['label'] = dgettext('phatform', 'Archived Form');
            $data['groups'] = NULL;
            $data['created'] = time();
            $data['updated'] = time();
            $data['hidden'] = 1;
            $data['approved'] = 1;
            $data['saved'] = 1;
            $data['archiveTableName'] = $newTableName;
            $data['archiveFileName']  = $_REQUEST['ARCHIVE_filename'];
            $db = new PHPWS_DB('mod_phatforms_forms');
            $db->addValue($data);
            $formId = $db->insert();
            return $this->readyViewArchive($formId, $newTableName);

        } else if(isset($_REQUEST['no'])) {
            $content .= dgettext('phatform', 'Viewing of archive has been canceled.');
            $content .= $this->viewArchives();
            return $content;

        } else {
            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_OP', 'viewArchive');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_filename', $_REQUEST['ARCHIVE_filename']);
            $elements[0] .= PHPWS_Form::formSubmit(dgettext('phatform', 'Yes'), 'yes');
            $elements[0] .= PHPWS_Form::formSubmit(dgettext('phatform', 'No'), 'no');

            $content .= dgettext('phatform', 'In order to view this archive a new table will need to added to your database.') . '<br /><br />';
            $content .= '<b>' . dgettext('phatform', 'Are you sure you wish to view this archive?') .'</b><br /><br />';
            $content .= PHPWS_Form::makeForm('archive_view', 'index.php', $elements);
            return $content;
        }
    }

    function getArchiveViewLink() {
        return '<a href="./index.php?module=phatform&amp;ARCHIVE_OP=viewArchive&amp;ARCHIVE_filename=' . $this->filename . '">Report View</a>';
    }

    function deleteArchive() {
        if(isset($_POST['yes'])){
            if(!isset($_REQUEST['ARCHIVE_filename'])) {
                $content = dgettext('phatform', 'There was a problem deleting the archive.') . '<br /><br />';    
                $content .= $this->viewArchives();
                return $content;
            }

            $this->cleanUpArchive();

            $filename = PHPWS_HOME_DIR . 'files/phatform/archive/' . $_REQUEST['ARCHIVE_filename'];      
            if(is_file($filename) && unlink($filename)) {
                $content = dgettext('phatform', 'The phatform archive was successfully <b>deleted</b>.') . '<br /><br />';        
            } else {
                $content = dgettext('phatform', 'There was a problem deleting the archive.') . '<br /><br />';    
            }

            $content .= $_SESSION['PHAT_advViews']->viewArchives();
        } elseif (isset($_POST['no'])) {
            $content = sprintf(dgettext('phatform', 'You have chosen <b>not</b> to delete the archive with the filename "%s".'), $_REQUEST['ARCHIVE_filename']) . '<br /><br />';
            $content .= $_SESSION['PHAT_advViews']->viewArchives();
        } else {
            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_OP', 'deleteArchive');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_filename', $_REQUEST['ARCHIVE_filename']);
            $elements[0] .= PHPWS_Form::formSubmit(dgettext('phatform', 'Yes'), 'yes');
            $elements[0] .= PHPWS_Form::formSubmit(dgettext('phatform', 'No'), 'no');

            $content = sprintf(dgettext('phatform', 'Are you sure you wish to delete the archive with filename "<b>%s</b>"?'), $_REQUEST['ARCHIVE_filename']) . '<br /><br />';
            $content .= PHPWS_Form::makeForm('archive_delete', 'index.php', $elements);
        }

        return $content;
    }

    function downloadExistingExport() {
        if(isset($_REQUEST['EXPORT_filename'])) {
            $filename = 'files/phatform/export/'.$_REQUEST['EXPORT_filename'];

            $this->addHeaders($filename);
            readfile($filename);

        } else {
            return dgettext('phatform', 'Invalid Filename Given') . '<br />';
        }
    }

    function addHeaders($filename, $type='text/plain') {
        header("Content-Type: $type");
        header('Content-Length: '. filesize($filename));
        header('Content-Description: File Transfer');
    
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $saveasname = basename($filename);
        if((is_integer(strpos($user_agent, 'msie')))
           && (is_integer (strpos($user_agent, 'win')))) {
            header('Content-Disposition: filename="'.$saveasname.'"');
        } else {
            header('Content-Disposition: attachment; filename="'.$saveasname.'"');
        }
    
        header('Pragma: cache');
    }

    function downloadExistingArchive() {
        if(isset($_REQUEST['ARCHIVE_filename'])) {
            $filename = 'files/phatform/archive/'.$_REQUEST['ARCHIVE_filename'];
            $this->addHeaders($filename);
            readfile($filename);

            exit();
        } else {
            return dgettext('phatform', 'Invalid Filename Given') . '<br />';
        }
    }

    function viewExports() {
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

        $listTags = array();
        $listTags['FILENAME_LABEL'] = dgettext('phatform', 'Filename');
        $listTags['DATE_LABEL'] = dgettext('phatform', 'Date Created');
        $listTags['ACTION_LABEL'] = dgettext('phatform', 'Action');
    
        $highlight = ' class="bgcolor1"';
    
        $files = array();
        $total_files = 0;
        $dir = PHPWS_HOME_DIR . 'files/phatform/export/';
        $showFormLabel = FALSE;
        if(is_dir($dir)) {
            if($dh = opendir($dir)) {
                while(($file = readdir($dh)) !== false) {
                    if(ereg('.zip$', $file)) {
                        $files[$total_files]['filename'] = $file;
                        $timeStamp = split('\.', $file);
                        $formId = split('_', $file);
                        $files[$total_files]['date'] = date('m / d / y', $timeStamp[1]);

                        $total_files++;
                    }

                }
                closedir($dh);
            }
        }

        if(sizeof($files) > 0) {
            $data = paginateDataArray($files, 'index.php?module=phatform&amp;PHAT_MAN_OP=viewExports', $this->pageLimit, TRUE, array('<b>[ ', ' ]</b>'), NULL, 10, TRUE);
        }

        if(isset($data) && is_array($data[0]) && (sizeof($data[0]) > 0)) {
            $listTags['LIST_ITEMS'] = NULL;
            $tog = 1;
            foreach($data[0] as $entry) {
                $rowTags = array();
                $rowTags['HIGHLIGHT'] = $highlight;
                $rowTags['FILENAME'] = $entry['filename'];
                $rowTags['DATE'] = $entry['date'];
                if(isset($entry['formId']))
                    $rowTags['FORM_LABEL'] = $entry['formId'];
                $rowTags['DOWNLOAD'] = '<a href="index.php?module=phatform&amp;EXPORT_OP=downloadExport&amp;EXPORT_filename=' . $entry['filename'] . '">' . dgettext('phatform', 'Download') . '</a>';
                $rowTags['DELETE'] = '<a href="index.php?module=phatform&amp;EXPORT_OP=deleteExport&amp;EXPORT_filename=' . $entry['filename'] . '">' . dgettext('phatform', 'Delete') . '</a>';
                if ($tog%2) {
                    $highlight = ' class="bgcolor1"';
                } else {
                    $highlight = null;
                }
                $tog++;
                $listTags['LIST_ITEMS'] .= PHPWS_Template::processTemplate($rowTags, 'phatform', 'report/export/row.tpl');
            }

            if((count($files) > $this->pageLimit)) {
                $listTags['NAVIGATION_LINKS'] = $data[1]; 
            }
      
            $listTags['SECTION_INFO'] = $data[2];
            $listTags['SECTION_INFO_LABEL'] = dgettext('phatform', 'Entries');
            $listTags['LINK_BACK'] = '<a href="./index.php?module=phatform&amp;PHAT_FORM_OP=report">' . dgettext('phatform', 'Report View') . '</a>';
        } else {
            $listTags['LIST_ITEMS'] = '<tr><td colspan="4" class="smalltext">' . dgettext('phatform', 'No entries were found matching your search query.') . '</td></tr>';
        }      

        $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Existing Exports');
        return PHPWS_Template::processTemplate($listTags, 'phatform', 'report/export/list.tpl');
    }

    function getArchiveFormName($filename, $formId) {
        $path = PHPWS_HOME_DIR . 'files/phatform/archive/' . $filename;      
        if(is_file($path)) {
            $fileContent = file($path);
        } else {
            return false;
        }

        if(empty($fileContent)) {
            return false;
        }

        for($i=0; $i < count($fileContent); $i++) {
            $line = str_replace("'", '', $fileContent[$i]);

            if(stristr($line, "INSERT INTO mod_phatform_forms VALUES ($formId")) {
                $insertValues = explode(',', $line);
                if(!empty($insertValues[4]))
                    return $insertValues[4];
                else
                    return false;
            }
        }

        return false;    
    }

    function viewArchives() {
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

        $listTags = array();
        $listTags['FORMNAME_LABEL'] = dgettext('phatform', 'Form Name');
        $listTags['FILENAME_LABEL'] = dgettext('phatform', 'Filename');
        $listTags['DATE_LABEL'] = dgettext('phatform', 'Date Created');
        $listTags['ACTION_LABEL'] = dgettext('phatform', 'Action');
        $highlight = ' class="bgcolor1"';
    
        $files = array();
        $total_files = 0;
        $dir = PHPWS_HOME_DIR . 'files/phatform/archive/';
        if(is_dir($dir)) {
            if($dh = opendir($dir)) {
                while(($file = readdir($dh)) !== false) {
                    if(ereg('phat$', $file)) {
                        $files[$total_files]['filename'] = $file;
                        $timeStamp = split('\.', $file);
                        $files[$total_files]['date'] = date('m / d / y', $timeStamp[1]);
                        $total_files++;
                    }
                }
                closedir($dh);
            }
        }

        if(sizeof($files) > 0) {
            $data = paginateDataArray($files, 'index.php?module=phatform&amp;PHAT_MAN_OP=viewArchives', $this->pageLimit, TRUE, array('<b>[ ', ' ]</b>'), NULL, 10, TRUE);
        }

        if(isset($data) && is_array($data[0]) && (sizeof($data[0]) > 0)) {
            $listTags['LIST_ITEMS'] = NULL;
            $tog = 1;
            foreach($data[0] as $entry) {
                $rowTags = array();
                $formNum = array();
                ereg('^([0-9]+)', $entry['filename'], $formNum);

                if($formname = $this->getArchiveFormName($entry['filename'], $formNum[0]))
                    $rowTags['FORMNAME']  = $formname;
                else
                    $rowTags['FORMNAME'] = dgettext('phatform', 'Unknown');

                $rowTags['HIGHLIGHT'] = $highlight;
                $rowTags['FILENAME'] = $entry['filename'];
                $rowTags['DATE'] = $entry['date'];
                $rowTags['DOWNLOAD'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=downloadArchive&amp;ARCHIVE_filename=' . $entry['filename'] . '">' . dgettext('phatform', 'Download') . '</a>';
                $rowTags['VIEW'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=viewArchive&amp;ARCHIVE_filename=' . $entry['filename'] . '">' . dgettext('phatform', 'View') . '</a>';
                $db = new PHPWS_DB('mod_phatform_forms');
                $db->addWhere('archiveFileName', '%' . $entry['filename'] . '%', 'LIKE');
                $result = $db->select();
                if($result) {
                    $rowTags['CLEANUP'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=cleanUpArchive&amp;ARCHIVE_filename='.$entry['filename'] . '">' . dgettext('phatform', 'Clean-Up') . '</a>';
                }

                $rowTags['DELETE'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=deleteArchive&amp;ARCHIVE_filename=' . $entry['filename'] . '">' . dgettext('phatform', 'Delete') . '</a>';
                
                if ($tog%2) {
                    $highlight = ' class="bgcolor1"';
                } else {
                    $highlight = null;
                }
                $tog++;
                $listTags['LIST_ITEMS'] .= PHPWS_Template::processTemplate($rowTags, 'phatform', 'report/archive/row.tpl');
            }
      
            if((count($files) > $this->pageLimit)) {
                $listTags['NAVIGATION_LINKS'] = $data[1]; 
            }
      
            $listTags['SECTION_INFO'] = $data[2];
            $listTags['SECTION_INFO_LABEL'] = dgettext('phatform', 'Entries');
            $listTags['LINK_BACK'] = '<a href="index.php?module=phatform&amp;PHAT_FORM_OP=report">' . dgettext('phatform', 'Report View') . '</a>';
        } else {

            $listTags['LIST_ITEMS'] = '<tr><td colspan="4" class="smalltext">' . dgettext('phatform', 'No entries were found matching your search query.') . '</td></tr>';
        }      

        $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Existing Archives');
        return PHPWS_Template::processTemplate($listTags, 'phatform', 'report/archive/list.tpl');
    }

    function cleanUpArchive() {
        if(isset($_REQUEST['ARCHIVE_filename'])) {
            $db = new PHPWS_DB('mod_phatform_forms');
            $db->addWhere('archiveFileName', $_REQUEST['ARCHIVE_filename']);
            $result = $db->select();
            if($result) {
                $sql = 'DROP TABLE ' . $result[0]['archiveTableName'];

                if(PHPWS_DB::query($sql)) {
                    $result = $db->delete();
                    if($result) {
                        return dgettext('phatform', 'Successfully deleted table associated with the archive with filename ') . "<b>'". $_REQUEST['ARCHIVE_filename'] . "'</b>.";
                    }
                    else {
                        return dgettext('phatform', 'There was a problem deleting viewing archive table associated for filename ') . "<b>'".$_REQUEST['ARCHIVE_filename'] . "'</b>.";       
                    }

                } else {
                    return dgettext('phatform', 'There was a problem deleting viewing archive table associated for filename ') . "<b>'".$_REQUEST['ARCHIVE_filename'] . "'</b>.";   
                }
            }
        }
    }

    function exportActions() {
        switch($_REQUEST['EXPORT_OP']) {
        case 'downloadExport':
            $content  = $_SESSION['PHAT_FormManager']->menu();
            $content .= $_SESSION['PHAT_advViews']->downloadExistingExport();
            break;

        case 'deleteExport':
            $content  = $_SESSION['PHAT_FormManager']->menu();
            $content .= $_SESSION['PHAT_advViews']->deleteExport();
            break;
        }
    
        $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Existing Exports');
        $GLOBALS['CNT_phatform']['content'] = $content;
    }

    function archiveActions() {
        switch($_REQUEST['ARCHIVE_OP']) {
        case 'downloadArchive':
            $content  = $_SESSION['PHAT_FormManager']->menu();
            $content .= $_SESSION['PHAT_advViews']->downloadExistingArchive();      
            break;

        case 'viewArchive':
            $content = $_SESSION['PHAT_advViews']->viewArchive();            
            break;

        case 'deleteArchive':
            $content  = $_SESSION['PHAT_FormManager']->menu();
            $content .= $_SESSION['PHAT_advViews']->deleteArchive();      
            break;

        case 'cleanUpArchive':
            $content  = $_SESSION['PHAT_FormManager']->menu();
            $content .= $_SESSION['PHAT_advViews']->cleanUpArchive();      
            $content .= '<br /><br />';
            $content .= $_SESSION['PHAT_advViews']->viewArchives();
            break;
        }

        $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Existing Archives');
        $GLOBALS['CNT_phatform']['content'] = $content;
    }

}


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
        $curr_sec_decor = array("<b>[ ", " ]</b>");

    if(isset($_REQUEST['PDA_limit'])){
        $limit = $_REQUEST['PDA_limit'];
    } else {
        $limit = $default_limit;
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
        $string_of_items = "";
        $array_of_items = array();
        $nav_links = "";
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
            $section_info = ($start + 1) . " - " . ($start + $item_count) . ' ' . dgettext('phatform','of') . ' ' . $numrows . "\n";
        } else {
            $nav_links .= "<a href=\"" . $link_back . "&amp;PDA_limit=" . $limit . "&#38;PDA_start=" . ($start + $limit) . "&#38;PDA_section=" . ($current_section + 1) . "\"" . $link_class . "\" title=\"&#62;&#62;\">&#62;&#62;</a>\n";
            $section_info = ($start + 1) . " - " . ($start + $limit) . ' ' . dgettext('phatform','of') . ' ' .$numrows . "\n";
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


?>