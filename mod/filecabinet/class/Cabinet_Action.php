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

class Cabinet_Action {

    function admin()
    {
        if (!Current_User::allow('filecabinet')) {
            Current_User::disallow();
        }
        PHPWS_Core::initCoreClass('Image.php');
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
            $document = & new PHPWS_Document;
            $title = _('Create New Image or Document');
            $content = Cabinet_action::editDocument($document);
            break;

        case 'new_image':
            $image = & new PHPWS_Image;
            $title = _('Create New Image');
            $content = Cabinet_Form::editImage($image);

        case 'main':
        case 'image':
            $title = _('Manage Images');
            $content = Cabinet_Form::imageManager('image');
            break;

        case 'document':
            $title = _('Manage Documents');
            $content = Cabinet_Form::documentManager('document');
            break;

        case 'editImage':
            if (!isset($_REQUEST['image_id'])){
                $title = _('Manage Images');
                $content = Cabinet_Form::imageManager();
                break;
            }
            $image = & new PHPWS_Image((int)$_REQUEST['image_id']);
            $title = _('Edit Image');
            $content = Cabinet_Action::editImage($image);
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

            $result = $manager->postImage($manager->image);
            if (PEAR::isError($result)) {
                if ($result->code == PHPWS_FILE_SIZE) {
                    $manager->image->_errors = array($result);
                    Layout::nakedDisplay($manager->edit());
                } else {
                    PHPWS_Error::log($result);
                    $manager->errorPost();
                }
            } elseif (is_array($result)) {
                $manager->image->_errors = $result;
                Layout::nakedDisplay($manager->edit());
            } else {
                $result = $manager->createThumbnail();
                $manager->postUpload($result);
            }
            break;

        case 'get_image_xml':
            Cabinet_Action::getImageXML($_REQUEST['id']);
            break;

        case 'uploadImage':
            if (!PHPWS_Core::isPosted())
                $result = Cabinet_Action::uploadImage();
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
            $title = _('Edit Image');
            $content = Cabinet_Form::edit_image($image);
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

        $panel = & new PHPWS_Panel('filecabinet');
        $panel->quickSetTabs($tabs);

        $panel->setModule('filecabinet');
        return $panel;
    }

    function postImage(&$image)
    {
        $errors = $image->importPost('file_name');
        $image->setTitle($_POST['title']);
        $image->setDescription($_POST['description']);
        $image->setModule($_POST['mod_title']);

        if (is_array($errors) || PEAR::isError($errors)) {
            return $errors;
        } else {
            $result = $image->save();
            return $result;
        }
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

}


?>