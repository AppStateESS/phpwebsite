<?php

/**
 * Main class for the File Cabinet
 *
 * File Cabinet is meant (for those devs that utilize it)
 * as a central place to administrate all the files uploaded to the site.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('filecabinet', 'Folder.php');

class Cabinet {
    var $title        = null;
    var $message      = null;
    var $content      = null;
    var $forms        = null;
    var $panel        = null;
    var $folder       = null;
    var $image_mgr    = null;
    var $document_mgr = null;

    function admin()
    {
        $javascript = false; // if true, sends to nakedDisplay
        
        if (!Current_User::allow('filecabinet')){
            Current_User::disallow();
            return;
        }

        $this->loadPanel();

        if (isset($_REQUEST['aop'])) {
            $aop = $_REQUEST['aop'];
        } else {
            $aop = $this->panel->getCurrentTab();
        }

        switch ($aop) {
        case 'image':
            $this->panel->setCurrentTab('image');
            $this->title = _('Image folders');
            $this->loadForms();
            $this->forms->getFolders(IMAGE_FOLDER);
            break;

        case 'add_folder':
            $javascript = true;
            $this->loadFolder();
            $this->addFolder();
            break;

        case 'clip_document':
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $document = new PHPWS_Document($_GET['document_id']);
            if ($document->id) {
                Clipboard::copy($document->title, '[filecabinet:doc:' . $document->id . ']');
            }
            PHPWS_Core::goBack();
            break;

        case 'delete_folder':
            $this->loadFolder();
            $this->folder->delete();
            PHPWS_Core::goBack();
            break;

        case 'delete_document':
            $this->loadDocumentManager();
            $this->document_mgr->document->delete();
            PHPWS_Core::goBack();
            break;

        case 'delete_image':
            $this->loadImageManager();
            $this->image_mgr->image->delete();
            PHPWS_Core::goBack();
            break;

        case 'document':
            $this->panel->setCurrentTab('document');
            $this->title = _('Document folders');
            $this->loadForms();
            $this->forms->getFolders(DOCUMENT_FOLDER);
            break;

        case 'edit_folder':
            $javascript = true;
            $this->loadFolder(IMAGE_FOLDER);
            $this->editFolder();
            break;

        case 'edit_image':
            $javascript = true;
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            $this->loadImageManager();
            $this->image_mgr->editImage();
            break;

        case 'post_document_upload':
            $javascript = true;
            $this->loadDocumentManager();
            $this->document_mgr->postDocumentUpload();
            break;

        case 'post_image_upload':
            $javascript = true;
            $this->loadImageManager();
            $this->image_mgr->postImageUpload();
            break;

        case 'post_folder':
            $this->loadFolder();
            if ($this->folder->post()) {
                if (!$this->folder->save()) {
                    Layout::nakedDisplay(_('Failed to create folder. Please check your logs.'));
                } else {
                    Layout::nakedDisplay(javascript('close_refresh'));
                }
            } else {
                $this->message = $this->folder->_error;
                $this->addFolder();
            }
            break;

        case 'get_images':
            $this->passImages();
            break;
            
        case 'save_settings':
            $result = $this->saveSettings();
            if (is_array($result)) {
                $this->message = implode('<br />', $result);
            } else {
                $this->message = _('Settings saved.');
            }
        case 'settings':
            $this->loadForms();
            $this->title = _('Settings');
            $this->content = $this->forms->settings();
            break;


        case 'upload_document_form':
            $javascript = true;
            $this->loadDocumentManager();
            $this->document_mgr->edit();
            break;

        case 'upload_image_form':
            $javascript = true;
            $this->loadImageManager();
            $this->image_mgr->edit();
            break;

        case 'view_folder':
            $this->viewFolder();
            break;

        case 'resize_image':
            $this->loadImageManager();
            echo $this->image_mgr->resizeImage();
            break;

        }

        $template['TITLE']   = $this->title;
        $template['MESSAGE'] = $this->message;
        $template['CONTENT'] = $this->content;

        if ($javascript) {
            $main = PHPWS_Template::process($template, 'filecabinet', 'javascript.tpl');
            Layout::nakedDisplay($main);
        } else {
            $main = PHPWS_Template::process($template, 'filecabinet', 'main.tpl');
            $this->panel->setContent($main);
            $finalPanel = $this->panel->display();
            Layout::add(PHPWS_ControlPanel::display($finalPanel));
        }
    }

    function download($document_id)
    {
        require_once 'HTTP/Download.php';
        PHPWS_Core::initModClass('filecabinet', 'Document.php');

        $document = new PHPWS_Document($document_id);
        if (!empty($document->_errors)) {
            foreach ($this->_errors as $err) {
                PHPWS_Error::log($err);
            }
            Layout::add(_('Sorry but this file is inaccessible at this time.'));
            return;
        }

        $folder = new Folder($document->folder_id);

        if (!$folder->allow()) {
            Layout::add(_('Sorry, you are not allowed access to this file.'));
            return;
        }

        $file_path = $document->getPath();

        if (!is_file($file_path)) {
            PHPWS_Error::log(FC_DOCUMENT_NOT_FOUND, 'filecabinet', 'Cabinet_Action::download', $file_path);
            Layout::add(_('Sorry but this file is inaccessible at this time.'));
            return;
        }

        $dl = new HTTP_Download;
        $dl->setFile($file_path);
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->filename);
        $dl->setContentType($document->file_type);
        $dl->send();
        exit();
    }

    function user($op=null)
    {
        if (empty($op)) {
            $op = & $_REQUEST['uop'];
        }

        switch($op) {
        case 'view_image':

            break;

        case 'view_folder':
            $this->userViewFolder();
            break;
        }

        $template['TITLE']   = $this->title;
        $template['MESSAGE'] = $this->message;
        $template['CONTENT'] = $this->content;

        $main = PHPWS_Template::process($template, 'filecabinet', 'plain.tpl');
        Layout::add($main);
    }

    function imageManager($image_id, $itemname, $width, $height)
    {
        PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
        $manager = new FC_Image_Manager($image_id);
        $manager->setItemname($itemname);
        $manager->setMaxWidth($width);
        $manager->setMaxHeight($height);
        return $manager;
    }

    function viewImage($id)
    {
        Layout::addStyle('filecabinet');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $image = new PHPWS_Image($id);
        $tpl['TITLE'] = $image->title;
        $tpl['IMAGE'] = $image->getTag();
        $tpl['DESCRIPTION'] = $image->getDescription();
        $tpl['CLOSE'] = javascript('close_window');
        $content = PHPWS_Template::process($tpl, 'filecabinet', 'view.tpl');

        Layout::nakedDisplay($content);
    }

    function addFolder()
    {
        $this->loadForms();
        if ($this->folder->ftype == IMAGE_FOLDER) {
            $this->title   = _('Create image folder');
        } else {
            $this->title   = _('Create document folder');
        }
        $this->content = $this->forms->editFolder($this->folder);
    }

    function editFolder()
    {
        $this->loadForms();
        if ($this->folder->ftype == IMAGE_FOLDER) {
            $this->title   = _('Update image folder');
        } else {
            $this->title   = _('Update document folder');
        }
        $this->content = $this->forms->editFolder($this->folder);
    }
    

    function loadImageManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
        $this->loadFolder(IMAGE_FOLDER);
        $this->image_mgr = new FC_Image_Manager;
        $this->image_mgr->cabinet = $this;
    }

    function loadDocumentManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'Document_Manager.php');
        $this->loadFolder(DOCUMENT_FOLDER);
        $this->document_mgr = new FC_Document_Manager;
        $this->document_mgr->cabinet = $this;
    }

    function loadFolder($ftype=IMAGE_FOLDER, $folder_id=0)
    {
        if (!$folder_id && isset($_REQUEST['folder_id'])) {
            $folder_id = &$_REQUEST['folder_id'];
        } else {
        }
        $this->folder = new Folder($folder_id);
        if (!$this->folder->id) {
            $this->folder->ftype = $ftype;
            if (isset($_REQUEST['ftype'])) {
                $this->folder->ftype = (int)$_REQUEST['ftype'];
            }
        }
    }

    function loadForms()
    {
        PHPWS_Core::initModClass('filecabinet', 'Forms.php');
        $this->forms = new Cabinet_Form;
        $this->forms->cabinet = $this;
    }

    function passImages()
    {
        header("Content-type: text/plain");
        $this->loadFolder();
        $this->loadImageManager();
        echo $this->image_mgr->showImages($this->folder);
        exit();
    }


    function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=filecabinet';

        $image_command    = array('title'=>_('Image folders'), 'link'=> $link);
        $document_command = array('title'=>_('Document folders'), 'link'=> $link);

        $tabs['image']    = $image_command;
        $tabs['document'] = $document_command;
        if (Current_User::isDeity()) {
            $tabs['settings']  = array('title'=> _('Settings'), 'link' => $link);
        }

        $this->panel = new PHPWS_Panel('filecabinet');
        $this->panel->quickSetTabs($tabs);
        $this->panel->setModule('filecabinet');
    }

    function saveSettings()
    {
        if (empty($_POST['base_doc_directory'])) {
            $errors[] = _('Default document directory may not be blank');
        } elseif (!is_dir($_POST['base_doc_directory'])) {
            $errors[] = _('Document directory does not exist.');
        } elseif (!is_writable($_POST['base_doc_directory'])) {
            $errors[] = _('Unable to write to document directory.');
        } elseif (!is_readable($_POST['base_doc_directory'])) {
            $errors[] = _('Unable to read document directory.');
        } else {
            $dir = $_POST['base_doc_directory'];
            if (!preg_match('@/$@', $dir)) {
                $dir .= '/';
            }
            PHPWS_Settings::set('filecabinet', 'base_doc_directory', $dir);
        }

        if (empty($_POST['max_image_width']) || $_POST['max_image_width'] < 50) {
            $errors[] = _('The max image width must be greater than 50 pixels.');
        } else {
            PHPWS_Settings::set('filecabinet', 'max_image_width', $_POST['max_image_width']);
        }

        if (empty($_POST['max_image_height']) || $_POST['max_image_height'] < 50) {
            $errors[] = _('The max image height must be greater than 50 pixels.');
        } else {
            PHPWS_Settings::set('filecabinet', 'max_image_height', $_POST['max_image_height']);
        }

        $max_file_upload = preg_replace('/\D/', '', ini_get('upload_max_filesize'));

        if (empty($_POST['max_image_size'])) {
            $errors[] = _('You must set a maximum image file size.');
        } else {
            $max_image_size = (int)$_POST['max_image_size'];
            if ( ($max_image_size / 1000000) > ((int)$max_file_upload) ) {
                $errors[] = sprintf(_('Your maximum image size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_image_size', $max_image_size);
            }
        }

        if (empty($_POST['max_document_size'])) {
            $errors[] = _('You must set a maximum document file size.');
        } else {
            $max_document_size = (int)$_POST['max_document_size'];
            if ( ($max_document_size / 1000000) > (int)$max_file_upload ) {
                $errors[] = sprintf(_('Your maximum document size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_document_size', $max_document_size);
            }
        }

        PHPWS_Settings::save('filecabinet');
        if (isset($errors)) {
            return $errors;
        } else {
            return true;
        }
    }

    function userViewFolder()
    {
        $this->loadFolder();
        if (!$this->folder->id) {
            PHPWS_Core::errorPage('404');
        }
        $this->title = $this->folder->title;
        $this->loadForms();
        $this->forms->folderContents($this->folder);
        
    }

    function viewFolder()
    {
        $this->loadFolder();
        if (!$this->folder->id) {
            PHPWS_Core::errorPage('404');
        }
        $this->title = $this->folder->title;
        $this->loadForms();
        $this->forms->folderContents($this->folder);
    }

}

?>