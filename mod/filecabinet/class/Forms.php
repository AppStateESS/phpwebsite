<?php

PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'Document.php');

class Cabinet_Form {
    function imageManager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('images', 'FC_Image');
        $pager->setModule('filecabinet');
        $pager->setTemplate('imageList.tpl');
        $pager->addRowTags('getRowTags');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addWhere('thumbnail_source', 0);
        $pager->addWhere('thumbnail_source', 'images.id', '=', 'or');


        $tags['TITLE']      = _('Title');
        $tags['FILE_NAME']   = _('Filename');
        $tags['SIZE']       = _('Size');
        $tags['ACTION']     = _('Action');

        $pager->addPageTags($tags);

        $result = $pager->get();

        if (empty($result)) {
            return _('No items found.');
        }

        return $result;
    }

    function documentManager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('documents', 'PHPWS_Document');
        $pager->setModule('filecabinet');
        $pager->setTemplate('documentList.tpl');
        $pager->addRowTags('getRowTags');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');

        $tags['TITLE']     = _('Title');
        $tags['FILE_NAME'] = _('File name');
        $tags['FILE_TYPE'] = _('File type');
        $tags['SIZE']      = _('Size');
        $tags['ACTION']    = _('Action');

        if (javascriptEnabled()) {
            $js['address'] = 'index.php?module=filecabinet&action=document_edit&authkey=' . Current_User::getAuthkey();
            $js['label'] = _('Upload document');
            $js['width'] = 550;
            $js['height'] = 350;
            $tags['NEW_DOCUMENT'] = javascript('open_window', $js);
        } else {
            $tags['NEW_DOCUMENT'] = PHPWS_Text::secureLink(_('Upload document'), 'filecabinet',
                                                           array('action'=>'new_document'));
        }
        

        $pager->addPageTags($tags);

        $result = $pager->get();

        if (empty($result)) {
            return _('No documents found.');
        }

        return $result;
    }

    function settings()
    {
        $form = & new PHPWS_FORM;
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('action', 'save_settings');
        $form->addText('base_doc_directory', PHPWS_Settings::get('filecabinet', 'base_doc_directory'));
        $form->setSize('base_doc_directory', '50');
        $form->addSubmit(_('Save settings'));
        $form->setLabel('base_doc_directory', _('Base document directory'));
        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'filecabinet', 'settings.tpl');
    }

    function editDocument(&$document, $js_form=FALSE)
    {
        PHPWS_Core::initCoreClass('File.php');

        $form = & new PHPWS_FORM;
        $form->addHidden('module', 'filecabinet');

        $doc_directories = Cabinet_Action::getDocDirectories();

        if ($document->file_directory) {
            $form->addHidden('directory', urlencode($document->file_directory));
        }


        if ($js_form) {
            $form->addHidden('action', 'js_post_document');
        } else {
            $form->addHidden('action', 'admin_post_document');
        }

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setLabel('file_name', _('Document location'));

        $form->addSelect('directory', $doc_directories);
        $form->setMatch('directory', $document->file_directory);
        $form->setLabel('directory', _('Save directory'));

        $form->addText('title', $document->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));


        $form->addTextArea('description', $document->description);
        $form->setLabel('description', _('Description'));

        if (isset($document->id)) {
            $form->addSubmit('submit', _('Update'));
        } else {
            $form->addSubmit('submit', _('Upload'));
        }

        if ($js_form) {
            $form->addButton('cancel', _('Cancel'));
            $form->setExtra('cancel', 'onclick="window.close()"');
        }

        $form->setExtra('submit', 'onclick="this.style.display=\'none\'"');

        $template = $form->getTemplate();

        $errors = $document->getErrors();
        if (!empty($errors)) {
            foreach ($errors as $err) {
                $message[] = array('ERROR' => $err->getMessage());
            }
            $template['errors'] = $message;
        }

        if ($document->id) {
            $template['CURRENT_DOCUMENT_LABEL'] = _('Current document');
            $template['CURRENT_DOCUMENT_ICON']  = $document->getIconView();
            $template['CURRENT_DOCUMENT_FILE']  = $document->file_name;
        }
        $template['MAX_SIZE_LABEL'] = _('Maximum file size');
        $template['MAX_SIZE']       = $document->getMaxSize(TRUE);

        return PHPWS_Template::process($template, 'filecabinet', 'document_edit.tpl');

    }

    function editImage(&$image)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        if ($image->file_directory) {
            $form->addHidden('directory', urlencode($image->file_directory));
        }

        $form->addHidden('action', 'admin_post_image');
        $form->setLabel('mod_title', _('Module Directory'));
        $form->setMatch('mod_title', $image->module);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        
        $form->setLabel('file_name', _('Image location'));

        $form->addText('title', $image->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addText('alt', $image->alt);
        $form->setSize('alt', 40);
        $form->setLabel('alt', _('Alternate text'));

        $form->addTextArea('description', $image->description);
        $form->setLabel('description', _('Description'));

        if (isset($image->id)) {
            $form->addSubmit(_('Update'));
        } else {
            $form->addSubmit(_('Upload'));
        }
        $template = $form->getTemplate();

        $errors = $image->getErrors();
        if (!empty($errors)) {
            foreach ($errors as $err) {
                $message[] = array('ERROR' => $err->getMessage());
            }
            $template['errors'] = $message;
        }

        $template['CURRENT_IMAGE_LABEL'] = _('Current image');
        $template['CURRENT_IMAGE']       = $image->getJSView(TRUE);
        $template['MAX_SIZE_LABEL']      = _('Maximum file size');
        $template['MAX_SIZE']            = $image->getMaxSize(TRUE);
        $template['MAX_WIDTH_LABEL']     = _('Maximum width');
        $template['MAX_WIDTH']           = $image->_max_width;
        $template['MAX_HEIGHT_LABEL']    = _('Maximum height');
        $template['MAX_HEIGHT']          = $image->_max_height;

        return PHPWS_Template::process($template, 'filecabinet', 'edit_image.tpl');

    }



}

class FC_Image extends PHPWS_Image {
    function getRowTags()
    {
        $vars['action'] = 'admin_edit_image';
        $vars['image_id'] = $this->id;
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'filecabinet', $vars);

        $tpl['ACTION'] = implode(' | ', $links);
        $tpl['SIZE'] = $this->getSize(TRUE);
        $tpl['FILE_NAME'] = $this->getViewLink(TRUE, TRUE);
        return $tpl;
    }
}


?>
