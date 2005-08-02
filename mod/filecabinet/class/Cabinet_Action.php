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
            $file = & new PHPWS_Image($_REQUEST['image_id']);
        } elseif (isset($_REQUEST['document_id'])) {
            $file = & new PHPWS_Document($_REQUEST['document_id']);
        }

        switch ($action) {
        case 'new':
            $file = & new File_Common;
            $title = _('Create New Image or Document');
            $content = Cabinet_action::edit($file);
            break;

        case 'main':
        case 'image':
            $title = _('Manage Images');
            $content = Cabinet_Action::manager('image');
            break;

        case 'document':
            $title = _('Manage Documents');
            $content = Cabinet_Action::manager('document');
            break;

        case 'editImage':
            if (!isset($_REQUEST['image_id'])){
                $title = _('Manage Images');
                $content = Cabinet_Action::manager('image');
                break;
            }
            $image = & new PHPWS_Image((int)$_REQUEST['image_id']);
            $title = _('Edit Image');
            $content = Cabinet_Action::editImage($image);
            break;

        case 'copyImage':
            if (!isset($_REQUEST['image_id'])){
                $title = _('Manage Images');
                $content = Cabinet_Action::manager('image');
                break;
            }

            $image = & new PHPWS_Image((int)$_REQUEST['image_id']);
            Clipboard::copy($image->getTitle(), $image->getTag());
            $title = _('Manage Images');
            $content = Cabinet_Action::manager('image');

            break;

        case 'delete':
            break;

        case 'image_manager':

            break;

        case 'post_image':
            $image = & new PHPWS_Image;
            $file = Cabinet_Action::createFile();
            $result = Cabinet_Action::postImage($file);
            if (PEAR::isError($result)) {
                PEAR::log($result);
                $title = _('Error');
                $content = _('There was a problem saving your image.');
                Layout::metaRoute('index.php?module=filecabinet');
            } elseif (is_array($result)) {
                $file->_errors = $result;
                $content = Cabinet_Action::edit($file);
            } else {
                $title = _('Success!');
                $content = _('File saved successfully.');
                Layout::metaRoute('index.php?module=filecabinet');
            }
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
                PHPWS_Error::log($result);
                $manager->errorPost();
            } elseif (is_array($result)) {
                $manager->image->_errors = $result;
                Layout::nakedDisplay($manager->edit());
            } else {
                $result = $manager->createThumbnail();
                $manager->postJavascript($result);
            }
            break;

        case 'get_image_xml':
            Cabinet_Action::getImageXML($_REQUEST['id']);
            break;

        case 'uploadImage':
            if (!PHPWS_Core::isPosted())
                $result = Cabinet_Action::uploadImage();
            $message = _('Image uploaded!');
            $content = Cabinet_Action::manager($panel->getCurrentTab());
            break;

        case 'upload_form':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            $manager = & new FC_Image_Manager;
            $manager->loadReqValues();
            Layout::nakedDisplay($manager->edit());
            break;

        case 'pick_image':
            PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
            $manager = & new FC_Image_Manager;
            $manager->loadReqValues();
            Layout::nakedDisplay($manager->pick());

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

        $new_command      = array('title' => _('New'), 'link' => $link);
        $image_command    = array('title'=>_('Images'), 'link'=> $link);
        $document_command = array('title'=>_('Documents'), 'link'=> $link);

        $tabs['new']      = $new_command;
        $tabs['image']    = $image_command;
        $tabs['document'] = $document_command;

        $panel = & new PHPWS_Panel('filecabinet');
        $panel->quickSetTabs($tabs);

        $panel->setModule('filecabinet');
        return $panel;
    }


    function imageManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $pager = & new DBPager('images', 'FC_Image');
        $pager->setModule('filecabinet');
        $pager->setTemplate('imageList.tpl');
        $pager->setLink('index.php?module=filecabinet&amp;tab=image&amp;authkey=' . Current_User::getAuthKey());
        $pager->addRowTags('getRowTags');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');

        $tags['TITLE']      = _('Title');
        $tags['FILENAME']   = _('Filename');
        $tags['MODULE']     = _('Module');
        $tags['SIZE']       = _('Size');
        $tags['ACTION']     = _('Action');

        $pager->addPageTags($tags);

        $result = $pager->get();

        if (empty($result)) {
            return _('No items found.');
        }

        return $result;
    }

    function manager($type)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        if ($type == 'image'){
            return Cabinet_Action::imageManager();
        }
    }

    function edit($file=NULL, $set_module=FALSE)
    {
        if (!$set_module) {
            $mod_list = PHPWS_Core::getModules();

            if (empty($mod_list)) {
                return;
            }

            if (empty($file->module)) {
                $file->module = 'filecabinet';
            }
        }

        foreach ($mod_list as $mod_info) {
            extract($mod_info);
            $select_list[$title] = $proper_name;
        }

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        if ($file->directory) {
            $form->addHidden('directory', urlencode($file->directory));
        }

        if (!$set_module) {
            $form->addHidden('action', 'post_image');
            $form->addSelect('mod_title', $select_list);
            $form->setLabel('mod_title', _('Module Directory'));
            $form->setMatch('mod_title', $file->module);
        } else {
            $form->addHidden('action',    'post_image_close');
            $form->addHidden('mod_title', $file->module);
            $form->addHidden('itemname',  $file->itemname);
        }

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        
        if ($file->getClassType() == 'image') {
            $form->setLabel('file_name', _('Image location'));
        } else {
            $form->setLabel('file_name', _('Document location'));
        }

        $form->addText('title', $file->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('description', $file->description);
        //        $form->useEditor('description', FALSE);
        $form->setLabel('description', _('Description'));

        if (isset($file->id)) {
            $form->addSubmit(_('Update'));
        } else {
            $form->addSubmit(_('Upload'));
        }
        $template = $form->getTemplate();

        $errors = $file->getErrors();
        if (!empty($errors)) {
            foreach ($errors as $err) {
                $message[] = array('ERROR' => $err->getMessage());
            }
            $template['errors'] = $message;
        }

        return PHPWS_Template::process($template, 'filecabinet', 'edit.tpl');
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


}


?>