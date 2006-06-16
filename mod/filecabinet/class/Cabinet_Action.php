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

define('DEFAULT_CABINET_LIST', 'image');

PHPWS_Core::initModClass('filecabinet', 'Forms.php');
PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'Document.php');

class Cabinet_Action {

    function admin()
    {
        if (!Current_User::allow('filecabinet')){
            Current_User::disallow();
            return;
        }

        $content = $message = $title = NULL;
        $panel = & Cabinet_Action::cpanel();

        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
        } else {
            $action = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['image_id'])) {
            $image = & new PHPWS_Image($_REQUEST['image_id']);
        } elseif (isset($_REQUEST['document_id'])) {
            $document = & new PHPWS_Document($_REQUEST['document_id']);
        }

        switch ($action) {
        case 'new_document':
            PHPWS_Core::initModClass('filecabinet', 'Forms.php');
            $document = & new PHPWS_Document;
            $title = _('Upload new document');
            $content = Cabinet_Form::editDocument($document);
            break;

        case 'clip_image':
            Clipboard::copy($image->title, '[filecabinet:image:' . $image->id . ']');
            PHPWS_Core::reroute(PHPWS_Text::linkAddress('filecabinet', array('tab' => 'image')));
            break;

        case 'main':
        case 'image':
            $title = _('Manage Images');
            $content = Cabinet_Form::imageManager('image');
            break;

        case 'clip_document':
            if ($document->id) {
                Clipboard::copy($document->title, '[filecabinet:doc:' . $document->id . ']');
            }
        case 'document':
            $title = _('Manage Documents');
            $content = Cabinet_Form::documentManager();
            break;

        case 'delete_document':
            if (!$document->id || !Current_User::authorized('filecabinet', 'delete', $document->id)) {
                Current_User::disallow();
            }
            $result = $document->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $message = _('An error occurred when trying to delete a document.');
            } else {
                $message = _('Document deleted.');
            }

            $title = _('Manage Documents');
            $content = Cabinet_Form::documentManager();
            break;

        case 'copyImage':
            if (!isset($_REQUEST['image_id'])){
                $title = _('Manage Images');
                $content = Cabinet_Form::imageManager('image');
                break;
            }

            $image = & new PHPWS_Image((int)$_REQUEST['image_id']);
            Clipboard::copy($image->getTitle(), $image->getTag());
            $title = _('Manage Images');
            $content = Cabinet_Form::imageManager('image');
            break;

        case 'delete_pick':
            $result = $image->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            $manager = & new FC_Image_Manager;
            $manager->loadReqValues();
            Layout::nakedDisplay($manager->editImage(TRUE));
            break;

        case 'post_pick':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');

            if (isset($_REQUEST['image_id'])) {
                $manager = & new FC_Image_Manager($_REQUEST['image_id']);
            }
            $manager->loadReqValues();
            $manager->postPick();
            break;


        case 'post_image_close':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            
            if (isset($_REQUEST['image_id'])) {
                $manager = & new FC_Image_Manager($_REQUEST['image_id']);
            } else {
                $manager = & new FC_Image_Manager;
            }
            $manager->loadReqValues();
            
            $result = $manager->postImage();

            if (PEAR::isError($result)) {
                if ($result->code == PHPWS_FILE_SIZE) {
                    $manager->image->_errors = array($result);
                    Layout::nakedDisplay($manager->edit());
                } else {
                    PHPWS_Error::log($result);
                    $manager->errorPost();
                }
            } elseif (!$result) {
                Layout::nakedDisplay($manager->edit());
            } else {
                // if the upload is not missing, create a thumbnail
                if (!$manager->image->_upload->isMissing()) {
                    $result = $manager->createThumbnail();
                }
                $manager->postUpload($result);
            }
            break;

        case 'document_edit':
            if (!Current_User::authorized('filecabinet')) {
                Current_User::disallow();
            }
            if (!isset($document)) {
                $document = & new PHPWS_Document;
            }
            $content = Cabinet_Action::documentUpload($document);
            Layout::nakedDisplay($content);
            break;

        case 'get_image_xml':
            Cabinet_Action::getImageXML($_REQUEST['id']);
            break;

        case 'uploadImage':
            if (!PHPWS_Core::isPosted()) {
                $result = Cabinet_Action::uploadImage();
            }
            $message = _('Image uploaded!');
            $content = Cabinet_Form::imageManager();
            break;

        case 'upload_form':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            $manager = & new FC_Image_Manager;
            $manager->loadReqValues();
            Layout::nakedDisplay($manager->edit());
            break;

        case 'admin_edit_image':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            $title = _('Edit Image');
            $manager = & new FC_Image_Manager($_REQUEST['image_id']);
            Layout::nakedDisplay($manager->edit());
            break;

        case 'admin_delete_image':
            $image->delete();
            $title = _('Manage Images');
            $content = Cabinet_Form::imageManager('image');
            break;

        case 'js_doc_edit':
            if (!Current_User::authorized('filecabinet')) {
                Current_User::disallow();
            }
            $content = Cabinet_Action::documentUpload();
            Layout::nakedDisplay($content);
            break;

        case 'admin_edit_document':
            if (!Current_User::allow('filecabinet', 'edit_document', $document->id)) {
                Current_User::disallow();
            }
            $content = Cabinet_Action::editDocument($document);
            Layout::nakedDisplay($content);
            break;

        case 'admin_post_document':
            if (!PHPWS_Core::isPosted()) {
                if (!isset($document)) {
                    $document = & new PHPWS_Document;
                }
                $result = Cabinet_Action::postDocument($document);
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $title = _('Sorry');
                    $content = _('An error occurred while saving your document.') . '<br />'
                        . _('Please check your logs.');
                    break;
                } elseif (is_array($result)) {
                    $document->_errors = $result;
                    $title = _('Upload new document');
                    $content = Cabinet_Form::editDocument($document);
                    break;
                } else {
                    $message = _('Document saved successfully.');
                }
            }
            $title = _('Manage Documents');
            $content = Cabinet_Form::documentManager();
            break;

        case 'js_post_document':
            if (!PHPWS_Core::isPosted()) {
                if (!isset($document)) {
                    $document = & new PHPWS_Document;
                }

                if (!Cabinet_Action::postDocument($document)) {
                    $tpl['CONTENT'] = Cabinet_Form::editDocument($document, TRUE);
                    $tpl['TITLE']   = _('Document');
                    Layout::nakedDisplay(PHPWS_Template::process($tpl, 'filecabinet', 'main.tpl'));
                } else {
                    javascript('close_refresh', array('location'=>'index.php?module=filecabinet&tab=document'));
                    Layout::nakedDisplay();
                }
            } else {
                exit('repeat post?');
            }
            break;

        case 'edit_image':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            if (isset($_REQUEST['current'])) {
                $manager = & new FC_Image_Manager((int)$_REQUEST['current']);
            } else {
                $manager = & new FC_Image_Manager;
            }
            $manager->loadReqValues();
            Layout::nakedDisplay($manager->editImage());
            break;

        case 'view_image':
            Layout::nakedDisplay(Cabinet_Action::viewImage($image));
            break;

        case 'setting':
            $title   = _('Settings');
            $content = Cabinet_Form::settings();
            break;

        case 'save_settings':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
            }
            $result = Cabinet_Action::saveSettings();
            if (is_array($result)) {
                $message = implode('<br />', $result);
            } else {
                $message = _('Settings saved.');
            }

            $title = _('Settings');
            $content = Cabinet_Form::settings();

            break;


        default:
            exit($action);
        }
    
        $template['TITLE']   = $title;
        $template['MESSAGE'] = $message;
        $template['CONTENT'] = $content;

        $main = PHPWS_Template::process($template, 'filecabinet', 'main.tpl');

        $panel->setContent($main);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
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
        }

        if (empty($_POST['base_img_directory'])) {
            $errors[] = _('Default image directory may not be blank');
        } elseif (!is_dir($_POST['base_img_directory'])) {
            $errors[] = _('Image directory does not exist.');
        } elseif (!is_writable($_POST['base_img_directory'])) {
            $errors[] = _('Unable to write to image directory.');
        } elseif (!is_readable($_POST['base_img_directory'])) {
            $errors[] = _('Unable to read image directory.');
        }

        if (isset($errors)) {
            return $errors;
        } else {
            PHPWS_Settings::set('filecabinet', 'base_doc_directory', $_POST['base_doc_directory']);
            PHPWS_Settings::set('filecabinet', 'base_img_directory', $_POST['base_img_directory']);
            PHPWS_Settings::save('filecabinet');
            return TRUE;
        }
    }

    function getImageXML($image_id)
    {
        $image = & new PHPWS_Image($image_id);
        $src = $image->getFullDirectory();

        header("Content-type: text/xml");
        echo '<?xml version="1.0" ?>' . $image->getXML();
        exit(); 
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=filecabinet';

        $image_command    = array('title'=>_('Images'), 'link'=> $link);
        $document_command = array('title'=>_('Documents'), 'link'=> $link);

        $tabs['image']    = $image_command;
        $tabs['document'] = $document_command;
        if (Current_User::isDeity()) {
            $tabs['setting']  = array('title'=> _('Settings'), 'link' => $link);
        }

        $panel = & new PHPWS_Panel('filecabinet');
        $panel->quickSetTabs($tabs);

        $panel->setModule('filecabinet');
        return $panel;
    }


    function postDocument(&$document)
    {
        if (!$document->importPost('file_name')) {
            return FALSE;
        }

        return $document->save();
    }

    function viewImage($image)
    {
        $template['TITLE'] = $image->title;
        $template['DESCRIPTION']  = $image->description;
        $template['IMAGE'] = $image->getTag();
        $template['CLOSE'] = _('Close window');

        $content = PHPWS_Template::process($template, 'filecabinet', 'view.tpl');
        Layout::nakedDisplay($content);
    }

    function getDocDirectories()
    {
        $doc_dir = PHPWS_Settings::get('filecabinet', 'base_doc_directory');

        $directories = PHPWS_File::listDirectories($doc_dir,TRUE,TRUE);

        if (empty($directories)) {
            return array('default' =>  _('[default]'));
        } else {
            $search = preg_quote($doc_dir, '/');
            $new_list[$doc_dir] = '/';
            foreach ($directories as $dir) {
                if (is_writable($dir)) {
                    $edit_dir = preg_replace('/^' . $search . '/', '', $dir);
                    $new_list[$dir] = $edit_dir;
                }
            }

        }

        return $new_list;
    }

    function getImgDirectories()
    {
        PHPWS_Core::initCoreClass('File.php');
        $img_dir = PHPWS_Settings::get('filecabinet', 'base_img_directory');

        $directories = PHPWS_File::listDirectories($img_dir,TRUE,TRUE);

        if (empty($directories)) {
            return array('default' =>  _('[default]'));
        } else {
            $search = preg_quote($img_dir, '/');
            $new_list[$img_dir] = _('[default]');
            foreach ($directories as $dir) {
                if (is_writable($dir)) {
                    $edit_dir = preg_replace('/^' . $search . '/', '', $dir);
                    $new_list[$dir . '/'] = $edit_dir;
                }
            }

        }

        return $new_list;
    }

    function documentUpload(&$document)
    {
        return Cabinet_Form::editDocument($document, TRUE);
    }

    function download($id)
    {
        require_once 'HTTP/Download.php';

        $document = & new PHPWS_Document($id);
        if (!empty($document->_errors)) {
            foreach ($this->_errors as $err) {
                PHPWS_Error::log($err);
            }
            Layout::add(_('Sorry but this file is inaccessible at this time.'));
            return;
        }

        $key = & new Key($document->key_id);
        if (!$key->allowView()) {
            Layout::add(_('Sorry, you are not allowed access to this file.'));
            return;
        }

        $dl = & new HTTP_Download;
        $dl->setFile($document->getPath());
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->filename);
        $dl->setContentType($document->file_type);
        $dl->send();
        exit();
    }

}


?>