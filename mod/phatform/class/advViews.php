<?php

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Form.php');

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Report.php');

/**
 *
 * Archive and Export View Class
 *
 * @version $Id: advViews.php 20 2006-10-18 18:23:18Z matt $
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
                $content = _('There was a problem deleting the export.') . '<br /><br />';     
                $content .= $this->viewExports();
                return $content;
            }

            $filename = PHPWS_HOME_DIR . 'files/phatform/export/' . $_REQUEST['EXPORT_filename'];      
            if(is_file($filename) && unlink($filename)) {
                $content = _('The phatform export was successfully <b>deleted</b>.') . '<br /><br />'; 
            } else {
                $content = _('There was a problem deleting the export.') . '<br /><br />';     
            }

            $content .= $_SESSION['PHAT_advViews']->viewExports();
        } elseif (isset($_POST['no'])) {
            $content = sprintf(_('You have chosen <b>not</b> to delete the export with the filename "%s".'), $_REQUEST["EXPORT_filename"]) . '<br /><br />';
            $content .= $_SESSION['PHAT_advViews']->viewExports();
        } else {
            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('EXPORT_OP', 'deleteExport');
            $elements[0] .= PHPWS_Form::formHidden('EXPORT_filename', $_REQUEST['EXPORT_filename']);
            $elements[0] .= PHPWS_Form::formSubmit(_('Yes'), 'yes');
            $elements[0] .= PHPWS_Form::formSubmit(_('No'), 'no');

            $content = sprintf(_('Are you sure you wish to delete the export with filename "<b>%s</b>"?'), $_REQUEST['EXPORT_filename']) . '<br /><br />';
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
            $content .= _('Archive file was not found.');
            $content .= $this->viewArchives();
            return $content;
        }

        if(empty($fileContent)) {
            $content .= _('File contained no content.');
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
            $content .= _('File contained no archive to view.');
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
            $data['label'] = _('Archived Form');
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
            $content .= _('Viewing of archive has been canceled.');
            $content .= $this->viewArchives();
            return $content;

        } else {
            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_OP', 'viewArchive');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_filename', $_REQUEST['ARCHIVE_filename']);
            $elements[0] .= PHPWS_Form::formSubmit(_('Yes'), 'yes');
            $elements[0] .= PHPWS_Form::formSubmit(_('No'), 'no');

            $content .= _('In order to view this archive a new table will need to added to your database.') . '<br /><br />';
            $content .= '<b>' . _('Are you sure you wish to view this archive?') .'</b><br /><br />';
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
                $content = _('There was a problem deleting the archive.') . '<br /><br />';    
                $content .= $this->viewArchives();
                return $content;
            }

            $this->cleanUpArchive();

            $filename = PHPWS_HOME_DIR . 'files/phatform/archive/' . $_REQUEST['ARCHIVE_filename'];      
            if(is_file($filename) && unlink($filename)) {
                $content = _('The phatform archive was successfully <b>deleted</b>.') . '<br /><br />';        
            } else {
                $content = _('There was a problem deleting the archive.') . '<br /><br />';    
            }

            $content .= $_SESSION['PHAT_advViews']->viewArchives();
        } elseif (isset($_POST['no'])) {
            $content = sprintf(_('You have chosen <b>not</b> to delete the archive with the filename "%s".'), $_REQUEST['ARCHIVE_filename']) . '<br /><br />';
            $content .= $_SESSION['PHAT_advViews']->viewArchives();
        } else {
            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_OP', 'deleteArchive');
            $elements[0] .= PHPWS_Form::formHidden('ARCHIVE_filename', $_REQUEST['ARCHIVE_filename']);
            $elements[0] .= PHPWS_Form::formSubmit(_('Yes'), 'yes');
            $elements[0] .= PHPWS_Form::formSubmit(_('No'), 'no');

            $content = sprintf(_('Are you sure you wish to delete the archive with filename "<b>%s</b>"?'), $_REQUEST['ARCHIVE_filename']) . '<br /><br />';
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
            return _('Invalid Filename Given') . '<br />';
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
            return _('Invalid Filename Given') . '<br />';
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
        $listTags['FILENAME_LABEL'] = _('Filename');
        $listTags['DATE_LABEL'] = _('Date Created');
        $listTags['ACTION_LABEL'] = _('Action');
    
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
            $data = PHPWS_Array::paginateDataArray($files, 'index.php?module=phatform&amp;PHAT_MAN_OP=viewExports', $this->pageLimit, TRUE, array('<b>[ ', ' ]</b>'), NULL, 10, TRUE);
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
                $rowTags['DOWNLOAD'] = '<a href="index.php?module=phatform&amp;EXPORT_OP=downloadExport&amp;EXPORT_filename=' . $entry['filename'] . '">' . _('Download') . '</a>';
                $rowTags['DELETE'] = '<a href="index.php?module=phatform&amp;EXPORT_OP=deleteExport&amp;EXPORT_filename=' . $entry['filename'] . '">' . _('Delete') . '</a>';
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
            $listTags['SECTION_INFO_LABEL'] = _('Entries');
            $listTags['LINK_BACK'] = '<a href="./index.php?module=phatform&amp;PHAT_FORM_OP=report">' . _('Report View') . '</a>';
        } else {
            $listTags['LIST_ITEMS'] = '<tr><td colspan="4" class="smalltext">' . _('No entries were found matching your search query.') . '</td></tr>';
        }      

        $GLOBALS['CNT_phatform']['title'] = _('Existing Exports');
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
        $listTags['FORMNAME_LABEL'] = _('Form Name');
        $listTags['FILENAME_LABEL'] = _('Filename');
        $listTags['DATE_LABEL'] = _('Date Created');
        $listTags['ACTION_LABEL'] = _('Action');
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
            $data = PHPWS_Array::paginateDataArray($files, 'index.php?module=phatform&amp;PHAT_MAN_OP=viewArchives', $this->pageLimit, TRUE, array('<b>[ ', ' ]</b>'), NULL, 10, TRUE);
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
                    $rowTags['FORMNAME'] = _('Unknown');

                $rowTags['HIGHLIGHT'] = $highlight;
                $rowTags['FILENAME'] = $entry['filename'];
                $rowTags['DATE'] = $entry['date'];
                $rowTags['DOWNLOAD'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=downloadArchive&amp;ARCHIVE_filename=' . $entry['filename'] . '">' . _('Download') . '</a>';
                $rowTags['VIEW'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=viewArchive&amp;ARCHIVE_filename=' . $entry['filename'] . '">' . _('View') . '</a>';
                $db = new PHPWS_DB('mod_phatform_forms');
                $db->addWhere('archiveFileName', '%' . $entry['filename'] . '%', 'LIKE');
                $result = $db->select();
                if($result) {
                    $rowTags['CLEANUP'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=cleanUpArchive&amp;ARCHIVE_filename='.$entry['filename'] . '">' . _('Clean-Up') . '</a>';
                }

                $rowTags['DELETE'] = '<a href="index.php?module=phatform&amp;ARCHIVE_OP=deleteArchive&amp;ARCHIVE_filename=' . $entry['filename'] . '">' . _('Delete') . '</a>';
                
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
            $listTags['SECTION_INFO_LABEL'] = _('Entries');
            $listTags['LINK_BACK'] = '<a href="index.php?module=phatform&amp;PHAT_FORM_OP=report">' . _('Report View') . '</a>';
        } else {

            $listTags['LIST_ITEMS'] = '<tr><td colspan="4" class="smalltext">' . _('No entries were found matching your search query.') . '</td></tr>';
        }      

        $GLOBALS['CNT_phatform']['title'] = _('Existing Archives');
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
                        return _('Successfully deleted table associated with the archive with filename ') . "<b>'". $_REQUEST['ARCHIVE_filename'] . "'</b>.";
                    }
                    else {
                        return _('There was a problem deleting viewing archive table associated for filename ') . "<b>'".$_REQUEST['ARCHIVE_filename'] . "'</b>.";       
                    }

                } else {
                    return _('There was a problem deleting viewing archive table associated for filename ') . "<b>'".$_REQUEST['ARCHIVE_filename'] . "'</b>.";   
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
    
        $GLOBALS['CNT_phatform']['title'] = _('Existing Exports');
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

        $GLOBALS['CNT_phatform']['title'] = _('Existing Archives');
        $GLOBALS['CNT_phatform']['content'] = $content;
    }

}



?>