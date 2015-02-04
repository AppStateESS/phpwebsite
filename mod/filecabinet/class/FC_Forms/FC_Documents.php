<?php

namespace filecabinet\FC_Forms;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class FC_Documents extends FC_Folder_Factory
{
    protected $ftype = DOCUMENT_FOLDER;

    protected function loadTemplate()
    {
        $this->template = new \Template();
        $this->template->setModuleTemplate('filecabinet', 'FC_Forms/document.html');
    }

    public function getForm()
    {
        \Layout::addStyle('filecabinet', 'FC_Forms/form_style.css');
        $this->loadJavascript();
        $this->loadTemplate();

        $tpl['folder_list'] = $this->printFolderList();

        $this->template->addVariables($tpl);
        return $this->template->get();
    }

    public function printFolderFiles()
    {
        $files = $this->getFolderFileList('documents');
        $template = new \Template;
        $template->setModuleTemplate('filecabinet', 'FC_Forms/document_files.html');
        if (empty($files)) {
            $template->add('empty', 'No files found');
            $template->add('files', null);
        } else {
            $template->addVariables(array('files' => $files, 'empty'=>null));
        }
        return $template->get();
    }

}
