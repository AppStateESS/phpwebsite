<?php

namespace filecabinet\FC_Forms;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class FC_Multimedia extends FC_Folder_Factory
{
    protected $ftype = MULTIMEDIA_FOLDER;

    public function getForm()
    {
        \Layout::addStyle('filecabinet', 'FC_Forms/form_style.css');
        $this->loadJavascript();
        $this->loadTemplate();

        return $this->template->get();
    }

    public function printFolderFiles()
    {
        $files = $this->getFolderFileList('multimedia');
        $template = new \Template;
        $template->setModuleTemplate('filecabinet', 'FC_Forms/multimedia_files.html');
        if (empty($files)) {
            return null;
        } else {
            $template->addVariables(array('files' => $files, 'empty' => null));
        }
        return $template->get();
    }

    public function printFile($id)
    {
        $db = \Database::newDB();
        $t = $db->addTable('multimedia');
        $t->addFieldConditional('id', (int) $id);
        $row = $db->selectOneRow();
        if (empty($row)) {
            return null;
        }
        $ext = \PHPWS_File::getFileExtension($row['file_name']);
        if ($ext == 'mp3') {
            $template = 'filters/audio.tpl';
        } else {
            $template = 'filters/media.tpl';
        }

        return \PHPWS_Template::process(array('FILE_PATH' => $row['file_directory'] . $row['file_name']), 'filecabinet', $template);
    }

}
