<?php

namespace filecabinet\FC_Forms;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
abstract class FC_Folder_Factory
{
    /**
     *
     * @var \Template
     */
    protected $template;
    protected $folders;
    protected $title;
    protected $ftype;
    protected $current_folder_id;

    abstract public function getForm();

    public function __construct($folder_id)
    {
        if ($folder_id == 0) {
            $folder_id = $this->getLastActiveFolder();
        }
        $this->setCurrentFolderId($folder_id);
    }

    protected function loadTemplate()
    {
        $modal = new \Modal('edit-file-form');
        $this->template = new \Template();
        $this->template->setModuleTemplate('filecabinet', 'FC_Forms/folders.html');
        $this->template->add('modal', $modal->get());
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
        $this->current_folder_id = (int) $id;
        $this->setLastActiveFolder($this->current_folder_id);
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
                . "<script type='text/javascript'>var accepted_files='$accepted_files';var ftype=$this->ftype;</script>";
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
        $allowed = explode(',', $type_list);
        array_walk($allowed, function(&$value, $key) {
            $value = '.' . $value;
        });
        $allowed_string = implode(',', $allowed);
        return $allowed_string;
    }

    /**
     * 
     * @return type
     */
    public function printFolderList()
    {
        $this->folders = $this->pullFolderRows($this->ftype);
        if (empty($this->folders)) {
            return null;
        }
        $id = $title = null;

        $active_id = $this->getLastActiveFolder();
        foreach ($this->folders as $folder) {
            extract($folder);
            if (!$active_id) {
                $active_class = 'active';
                $active_id = $folder['id'];
            } elseif ($active_id == $folder['id']) {
                $active_class = 'active';
            } else {
                $active_class = null;
            }
            $lines[] = "<li class='folder $active_class' data-ftype='$this->ftype' data-folder-id='$id'><i class='pull-right fa fa-edit fa-lg edit-folder admin'></i> $title</li>";
        }
        return implode("\n", $lines);
    }

    private function setLastActiveFolder($id)
    {
        $_SESSION['last_fc_folder_id'][$this->ftype] = $id;
    }

    private function getLastActiveFolder()
    {
        if (!isset($_SESSION['last_fc_folder_id'][$this->ftype])) {
            return 0;
        } else {
            return $_SESSION['last_fc_folder_id'][$this->ftype];
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

}
