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
        $template = new \phpws2\Template;
        $template->setModuleTemplate('filecabinet', 'FC_Forms/multimedia_files.html');
        if (empty($files)) {
            return null;
        } else {
            $template->addVariables(array('files' => $files, 'empty'=>null));
        }
        return $template->get();
    }

    
    public function printFile($id)
    {
    	$db = \phpws2\Database::newDB();
    	$t = $db->addTable('multimedia');
    	$t->addFieldConditional('id', (int)$id);
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
    	
    	$template = new \phpws2\Template;
    	$template->setModuleTemplate('filecabinet', 'FC_Forms/multimedia_view.html');
    	$template->add('title', $row['title']);
    	$template->add('filepath', $row['file_directory'] . $row['file_name']);
    	return $template->get();
    }

}
