<?php

namespace filecabinet\FC_Forms;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class FC_Images extends FC_Folder_Factory
{
    protected $ftype = IMAGE_FOLDER;

    public function getForm()
    {
        \Layout::addStyle('filecabinet', 'FC_Forms/form_style.css');
        $this->loadJavascript();
        $this->loadTemplate();

        //$tpl['folder_list'] = $this->printFolderList();
        //$this->template->addVariables($tpl);

        return $this->template->get();
    }

    public function printFolderFiles()
    {
        $files = $this->getFolderFileList('images');
        foreach ($files as $k=>$f) {
            $filepath = $f['file_directory'] . 'tn/' . $f['file_name'];
            $files[$k]['filepath'] = './' . $filepath;
        }
        $template = new \Template;
        $template->setModuleTemplate('filecabinet', 'FC_Forms/image_files.html');
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
        $t = $db->addTable('images');
        $t->addFieldConditional('id', (int) $id);
        $row = $db->selectOneRow();
        if (empty($row)) {
            return null;
        }
        $template = new \Template;
        $template->setModuleTemplate('filecabinet', 'FC_Forms/image_view.html');
        $template->add('title', $row['title']);
        $template->add('alt', $row['alt']);
        $template->add('filepath', $row['file_directory'] . $row['file_name']);
        return $template->get();
    }

}
