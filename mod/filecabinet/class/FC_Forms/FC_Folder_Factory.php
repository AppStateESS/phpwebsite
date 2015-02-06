<?php

namespace filecabinet\FC_Forms;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
abstract class FC_Folder_Factory
{
    protected $template;
    protected $folders;
    protected $title;
    protected $ftype;
    protected $current_folder_id;

    abstract protected function loadTemplate();

    abstract public function getForm();

    public function __construct($folder_id)
    {
        $this->setCurrentFolderId($folder_id);
    }
    
    protected function getFolderFileList($table)
    {
        $db = \Database::newDB();
        $file_table = $db->addTable($table);
        $file_table->addFieldConditional('folder_id', $this->current_folder_id);
        $file_table->addOrderBy('title', 'asc');
        $folder_files = $db->select();
        return $folder_files;
    }

    protected function setCurrentFolderId($id)
    {
        $this->current_folder_id = (int)$id;
    }
    
    protected function getCurrentFolderId()
    {
        return $this->current_folder_id;
    }
    
    /**
     * Pulls folders from the database according to folder type
     * @param integer $ftype Folder type (image, document, multimedia)
     * @return array
     */
    protected function pullFolderRows()
    {
        if (!$this->ftype) {
            throw new \Exception('Missing folder type');
        }
        $db = \Database::newDB();
        $f = $db->addTable('folders');
        $f->addFieldConditional('ftype', $this->ftype);
        $f->addOrderBy('title');
        $result = $db->select();
        return $result;
    }

    protected function loadJavascript()
    {
        javascript('jquery');
        javascript('dropzone');
        javascript('authkey', null, null, true);
        $accepted_files = $this->getAllowedFileTypes();
        $included_script = "<script type='text/javascript'>Dropzone.autoDiscover = false;</script>"
                . "<script type='text/javascript'>var accepted_files='$accepted_files';</script>";
        \Layout::addJSHeader($included_script, 'fc_accepted_files');
                
        $source = PHPWS_SOURCE_HTTP . 'mod/filecabinet/javascript/fc_folders/folder.js';
        $script = "<script type='text/javascript' src='$source'></script>";
        \Layout::addJSHeader($script, 'fc_folder');
    }

    protected function getAllowedFileTypes()
    {
        switch ($this->ftype) {
            case DOCUMENT_FOLDER:
                $type_list = \PHPWS_Settings::get('filecabinet', 'document_files');
                break;
            case IMAGE_FOLDER:
                $type_list = \PHPWS_Settings::get('filecabinet', 'image_files');
                break;
            case MULTIMEDIA_FOLDER:
                $type_list = \PHPWS_Settings::get('filecabinet', 'media_files');
                break;
        }
        $allowed  = explode(',',$type_list);
        array_walk($allowed, function(&$value,$key){
            $value = '.' . $value;
        });
        $allowed_string = implode(',', $allowed);
        return $allowed_string;
    }
    
    /**
     * 
     * @return type
     */
    protected function printFolderList()
    {
        $this->folders = $this->pullFolderRows($this->ftype);
        if (empty($this->folders)) {
            return null;
        }
        $id = $title = null;

        $active_set = false;
        foreach ($this->folders as $folder) {
            extract($folder);
            if (!$active_set) {
                $active_class = 'active';
                $active_set = true;
            } else {
                $active_class = null;
            }
            $lines[] = "<li class='folder $active_class' data-ftype='$this->ftype' data-folder-id='$id'><i class='pull-right fa fa-edit fa-lg admin'></i> $title</li>";
        }
        return implode("\n", $lines);
    }
    
    public function getTitle()
    {
        return $this->title;
    }

}
