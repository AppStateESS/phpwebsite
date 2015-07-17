<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
PHPWS_Core::initModClass('filecabinet', 'Document.php');

class FC_Document_Manager
{
    public $folder = null;
    public $document = null;
    public $max_size = 0;
    public $content;
    public $title;

    public function __construct($document_id = 0)
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
                PHPWS_Core::returnToBookmark();
                break;
            case 'post_document_upload':
                if (!$this->folder->id || !Current_User::authorized('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }
                $this->postDocumentUpload();
                javascript('close_refresh');
                Layout::nakedDisplay();
                //\PHPWS_Core::goBack();
                break;

            case 'upload_document_form':
                if (!$this->folder->id || !Current_User::secured('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }
                $this->loadDocument(filter_input(INPUT_GET, 'file_id', FILTER_VALIDATE_INT));
                $this->edit();
                echo Layout::wrap($this->content, 'Document Upload', true);
                exit;

            case 'add_access':
                if (!Current_User::authorized('filecabinet')) {
                    Current_User::disallow();
                }
                $keyword = null;

                $this->loadDocument();
                // document exists, try making a shortcut
                if ($this->document->id) {

                    PHPWS_Core::initModClass('access', 'Shortcut.php');
                    $shortcut = new Access_Shortcut;
                    if (isset($_GET['keyword'])) {
                        $keyword = $_GET['keyword'];
                    }

                    if (empty($keyword)) {
                        $keyword = $this->document->title;
                    }

                    $result = $shortcut->setKeyword($keyword);
                    $new_keyword = $shortcut->keyword;
                    // if setKeyword returns a false or error, we have them pick a different name
                    if (!$result || PHPWS_Error::isError($result)) {
                        $message = dgettext('filecabinet', 'Access shortcut name already in use. Please enter another.');
                        $success = false;
                    } else {
                        $shortcut->setUrl('filecabinet', $this->document->getViewLink());
                        $shortcut->save();
                        $success = true;
                        $message = '<p>' . dgettext('filecabinet', 'Access shortcut successful!') . '</p>';
                        $message .= '<a href="' . PHPWS_Core::getHomeHttp() . $shortcut->keyword . '">' . PHPWS_Core::getHomeHttp() . $shortcut->keyword . '</a>';
                    }
                } else {
                    $message = dgettext('filecabinet', 'File not found');
                    // not really a success but prevents a repost prompt
                    $success = true;
                }
                echo json_encode(array('success' => $success, 'message' => $message, 'keyword' => $new_keyword));
                exit();
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

        PHPWS_Core::initCoreClass('File.php');

        $form = new PHPWS_FORM;
        $form->setFormId('file-form');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('dop', 'post_document_upload');
        $form->addHidden('ms', $this->max_size);
        $form->addHidden('folder_id', $this->folder->id);

        $form->addFile('file_name');
        $form->setLabel('file_name', dgettext('filecabinet', 'Document location'));

        $form->addText('title', $this->document->title);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));
        $form->setClass('title', 'form-control');

        $form->addSubmit('upload', 'Upload document');
        $form->setClass('upload', 'btn btn-primary');
        
        if ($this->document->id) {
            $this->title = dgettext('filecabinet', 'Update file');
            $form->addHidden('document_id', $this->document->id);
        } else {
            $this->title = dgettext('filecabinet', 'Upload new file');
        }

        if ($this->document->id && Current_User::allow('filecabinet', 'edit_folders', $this->folder->id, 'folder', true)) {
            Cabinet::moveToForm($form, $this->folder);
        }

        $template = $form->getTemplate();

        if ($this->document->id) {
            $template['CURRENT_DOCUMENT_LABEL'] = dgettext('filecabinet', 'Current document');
            $template['CURRENT_DOCUMENT_ICON'] = $this->document->getIconView();
            $template['CURRENT_DOCUMENT_FILE'] = $this->document->file_name;
        }
        $template['MAX_SIZE_LABEL'] = dgettext('filecabinet', 'Maximum file size');

        $sys_size = str_replace('M', '', ini_get('upload_max_filesize'));

        $sys_size = $sys_size * 1000000;

        if ((int) $sys_size < (int) $this->max_size) {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes (system wide)'), $sys_size);
        } else {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes'), $this->max_size);
        }

        $this->content = PHPWS_Template::process($template, 'filecabinet', 'Forms/document_edit.tpl');
    }

    public function loadDocument($document_id = 0)
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
            $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_document_size'));
        }
    }

    public function postDocumentUpload()
    {
        // importPost in File_Common
        $result = $this->document->importPost('file_name');
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $vars['timeout'] = '3';
            $vars['refresh'] = 0;
            javascript('close_refresh', $vars);
            return dgettext('filecabinet', 'An error occurred when trying to save your document.');
        } elseif ($result) {
            $result = $this->document->save();
            if (PHPWS_Error::logIfError($result)) {
                $content = dgettext('filecabinet', '<p>Could not upload file to folder. Please check your directory permissions.</p>');
                $content .= sprintf('<a href="#" onclick="window.close(); return false">%s</a>', dgettext('filecabinet', 'Close this window'));
                Layout::nakedDisplay($content);
                exit();
            }
            PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
            // This function doesn't do anything
            //FC_File_Assoc::updateTag(FC_DOCUMENT, $this->document->id, $this->document->getTag());

            $this->document->moveToFolder();
            if (!isset($_POST['im'])) {
                javascript('close_refresh');
            } else {
                javascriptMod('filecabinet', 'refresh_manager', array('document_id' => $this->document->id));
            }
        } else {
            foreach ($this->document->_errors as $err) {
                $message[] = $err->getMessage();
            }
            Cabinet::setMessage($this->document->printErrors());
        }
    }

    public function setMaxSize($size)
    {
        $this->max_size = (int) $size;
    }

    public function loadFolder($folder_id = 0)
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