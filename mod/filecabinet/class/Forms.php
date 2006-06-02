<?php

PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'Document.php');

class Cabinet_Form {
    function imageManager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');


        $pager = & new DBPager('images', 'PHPWS_Image');
        $pager->setModule('filecabinet');
        $pager->setTemplate('imageList.tpl');
        $pager->addRowTags('getRowTags');
        $pager->addToggle('class="toggle1"');
        $pager->addWhere('thumbnail_source', 0);
        $pager->addWhere('thumbnail_source', 'images.id', '=', 'or');
        $pager->setDefaultLimit(10);

        $tags['THUMBNAIL'] = _('Thumbnail');
        $tags['TITLE']     = _('Title');
        $tags['FILE_NAME'] = _('File name');
        $tags['SIZE']      = _('Size');
        $tags['ACTION']    = _('Action');
        $manager = & new FC_Image_Manager;
        $tags['UPLOAD']    = $manager->getUploadLink();

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
        $form->setLabel('base_doc_directory', _('Base document directory'));

        $form->addText('base_img_directory', PHPWS_Settings::get('filecabinet', 'base_img_directory'));
        $form->setSize('base_img_directory', '50');
        $form->setLabel('base_img_directory', _('Base image directory'));

        $form->addSubmit(_('Save settings'));
        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'filecabinet', 'settings.tpl');
    }

    function editDocument(&$document, $js_form=FALSE)
    {
        PHPWS_Core::initCoreClass('File.php');

        $form = & new PHPWS_FORM;
        $form->addHidden('module', 'filecabinet');

        $doc_directories = Cabinet_Action::getDocDirectories();

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
            $form->addHidden('document_id', $document->id);
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
}

?>
