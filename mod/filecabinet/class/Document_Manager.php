<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

core\Core::initModClass('filecabinet', 'Document.php');

class FC_Document_Manager {
    public $folder   = null;
    public $document = null;
    public $max_size = 0;

    public function __construct($document_id=0)
    {
        $this->loadDocument($document_id);
        $this->loadSettings();
        $this->loadFolder();
    }

    /*
     * Expects 'dop' command to direct action.
     */
    public function admin()
    {
        switch ($_REQUEST['dop']) {
            case 'delete_document':
                if (!$this->folder->id || !Current_User::secured('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }
                $this->document->delete();
                \core\Core::returnToBookmark();
                break;
            case 'post_document_upload':
                if (!$this->folder->id || !Current_User::authorized('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }
                return $this->postDocumentUpload();
                break;
            case 'upload_document_form':
                if (!$this->folder->id || !Current_User::secured('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }

                return $this->edit();
                break;

            case 'clip_document':
                if ($this->document->id) {
                    Clipboard::copy($this->document->title, $this->document->getViewLink(true, null, true), true,
                    sprintf('[filecabinet:doc:%s]', $this->document->id));
                }
                \core\Core::goBack();
                break;
        }

    }

    public function authenticate()
    {
        if (empty($this->module)) {
            return false;
        }
        return Current_User::allow($this->module);
    }

    public function edit()
    {
        if (empty($this->document)) {
            $this->loadDocument();
        }

        
        $form = new \core\Form;
        $form->addHidden('module',    'filecabinet');
        $form->addHidden('dop',       'post_document_upload');
        $form->addHidden('ms',        $this->max_size);
        $form->addHidden('folder_id', $this->folder->id);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setLabel('file_name', dgettext('filecabinet', 'Document location'));

        $form->addText('title', $this->document->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));

        $form->addTextArea('description', $this->document->description);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        if ($this->document->id) {
            $form->addTplTag('FORM_TITLE', dgettext('filecabinet', 'Update file'));
            $form->addHidden('document_id', $this->document->id);
            $form->addSubmit('submit', dgettext('filecabinet', 'Update'));
        } else {
            $form->addTplTag('FORM_TITLE', dgettext('filecabinet', 'Upload new file'));
            $form->addSubmit('submit', dgettext('filecabinet', 'Upload'));
        }

        $form->addButton('cancel', dgettext('filecabinet', 'Cancel'));
        $form->setExtra('cancel', 'onclick="window.close()"');

        $form->setExtra('submit', 'onclick="this.style.display=\'none\'"');

        if ($this->document->id && Current_User::allow('filecabinet', 'edit_folders', $this->folder->id, 'folder', true)) {
            Cabinet::moveToForm($form, $this->folder);
        }

        $template = $form->getTemplate();

        if ($this->document->id) {
            $template['CURRENT_DOCUMENT_LABEL'] = dgettext('filecabinet', 'Current document');
            $template['CURRENT_DOCUMENT_ICON']  = $this->document->getIconView();
            $template['CURRENT_DOCUMENT_FILE']  = $this->document->file_name;
        }
        $template['MAX_SIZE_LABEL'] = dgettext('filecabinet', 'Maximum file size');

        $sys_size = str_replace('M', '', ini_get('upload_max_filesize'));

        $sys_size = $sys_size * 1000000;

        if((int)$sys_size < (int)$this->max_size) {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes (system wide)'), $sys_size);
        } else {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes'), $this->max_size);
        }

        if ($this->document->_errors) {
            $template['ERROR'] = $this->document->printErrors();
        }

        return \core\Template::process($template, 'filecabinet', 'document_edit.tpl');
    }

    public function loadDocument($document_id=0)
    {
        if (!$document_id && isset($_REQUEST['document_id'])) {
            $document_id = $_REQUEST['document_id'];
        }
        $this->document = new PHPWS_Document($document_id);
    }

    public function loadSettings()
    {
        if (isset($_REQUEST['ms']) && $_REQUEST['ms'] > 1000) {
            $this->setMaxSize($_REQUEST['ms']);
        } else {
            $this->setMaxSize(core\Settings::get('filecabinet', 'max_document_size'));
        }
    }

    public function postDocumentUpload()
    {
        // importPost in File_Common
        $result = $this->document->importPost('file_name');

        if (core\Error::isError($result)) {
            \core\Error::log($result);
            $vars['timeout'] = '3';
            $vars['refresh'] = 0;
            javascript('close_refresh', $vars);
            return dgettext('filecabinet', 'An error occurred when trying to save your document.');
        } elseif ($result) {
            $result = $this->document->save();

            if (core\Error::logIfError($result)) {
                $content = dgettext('filecabinet', '<p>Could not upload file to folder. Please check your directory permissions.</p>');
                $content .= sprintf('<a href="#" onclick="window.close(); return false">%s</a>', dgettext('filecabinet', 'Close this window'));
                Layout::nakedDisplay($content);
                exit();
            }
            \core\Core::initModClass('filecabinet', 'File_Assoc.php');
            FC_File_Assoc::updateTag(FC_DOCUMENT, $this->document->id, $this->document->getTag());

            $this->document->moveToFolder();
            if (!isset($_POST['im'])) {
                javascript('close_refresh');
            } else {
                javascriptMod('filecabinet', 'refresh_manager', array('document_id'=>$this->document->id));
            }

        } else {
            return $this->edit();
        }
    }

    public function setMaxSize($size)
    {
        $this->max_size = (int)$size;
    }

    public function loadFolder($folder_id=0)
    {
        if (!$folder_id && isset($_REQUEST['folder_id'])) {
            $folder_id = &$_REQUEST['folder_id'];
        }

        $this->folder = new Folder($folder_id);
        if (!$this->folder->id) {
            $this->folder->ftype = DOCUMENT_FOLDER;
        }
    }
}

?>