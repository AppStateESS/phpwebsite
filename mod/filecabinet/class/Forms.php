<?php

PHPWS_Core::initCoreClass('Image.php');
PHPWS_Core::initCoreClass('Document.php');

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

        $form = & new PHPWS_Form;
        $form->setMethod('get');
        $values = $pager->getLinkValues();

        unset($values['authkey']);

        if (isset($values['mod_title']) && $values['mod_title'] != 'all') {
            $current_mod = $values['mod_title'];
            $pager->addWhere('module', $current_mod, '=', 'and', 1);
        } else {
            $current_mod = NULL;
        }

        unset($values['mod_title']);

        $form->addHidden($values);
        $mods = PHPWS_Core::getModules(TRUE, TRUE);
        $module_list['all'] = _('All');
        foreach ($mods as $mod_title) {
            $module_list[$mod_title] = $mod_title;
        }
        $form->addSelect('mod_title', $module_list);

        if (javascriptEnabled()) {
            $form->setExtra('mod_title', 'onchange="javascript:this.form.submit();"');
        } else {
            $form->addSubmit(_('Go'));
        }
        $form->setMatch('mod_title', $current_mod);

        $tags = $form->getTemplate();

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

    function documentManager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('documents', 'FC_Document');
        $pager->setModule('filecabinet');
        $pager->setTemplate('documentList.tpl');
        $pager->addRowTags('getRowTags');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');

        // Pick module
        $form = & new PHPWS_Form;
        $form->setMethod('get');
        $values = $pager->getLinkValues();

        unset($values['authkey']);

        if (isset($values['mod_title']) && $values['mod_title'] != 'all') {
            $current_mod = $values['mod_title'];
            $pager->addWhere('module', $current_mod, '=', 'and', 1);
        } else {
            $current_mod = NULL;
        }

        unset($values['mod_title']);

        $form->addHidden($values);
        $mods = PHPWS_Core::getModules(TRUE, TRUE);
        $module_list['all'] = _('All');
        foreach ($mods as $mod_title) {
            $module_list[$mod_title] = $mod_title;
        }
        
        $form->addSelect('mod_title', $module_list);


        if (javascriptEnabled()) {
            $form->setExtra('mod_title', 'onchange="javascript:this.form.submit();"');
        } else {
            $form->addSubmit(_('Go'));
        }
        $form->setMatch('mod_title', $current_mod);

        $tags = $form->getTemplate();


        $tags['TITLE']    = _('Title');
        $tags['FILENAME'] = _('Filename');
        $tags['TYPE']     = _('Document Type');
        $tags['MODULE']   = _('Module');
        $tags['SIZE']     = _('Size');
        $tags['ACTION']   = _('Action');

        $tags['NEW_DOCUMENT'] = PHPWS_Text::secureLink(_('Upload document'), 'filecabinet',
                                                       array('action'=>'new_document'));
        

        $pager->addPageTags($tags);

        $result = $pager->get();

        if (empty($result)) {
            return _('No documents found.');
        }

        return $result;
    }

    function editDocument(&$document)
    {
        $form = & new PHPWS_FORM;
        $form->addHidden('module', 'filecabinet');

        if ($document->directory) {
            $form->addHidden('directory', urlencode($document->directory));
        }

        $form->addHidden('action', 'admin_post_document');
        $form->setLabel('mod_title', _('Module Directory'));
        $form->setMatch('mod_title', $document->module);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setLabel('file_name', _('Document location'));

        $form->addText('title', $document->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('description', $document->description);
        $form->setLabel('description', _('Description'));

        if (isset($document->id)) {
            $form->addSubmit(_('Update'));
        } else {
            $form->addSubmit(_('Upload'));
        }
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
            $template['CURRENT_DOCUMENT']       = $document->getIconView();
        }
        $template['MAX_SIZE_LABEL'] = _('Maximum file size');
        $template['MAX_SIZE']       = $document->getMaxSize(TRUE);

        return PHPWS_Template::process($template, 'filecabinet', 'document_edit.tpl');

    }

    function editImage(&$image)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        if ($image->directory) {
            $form->addHidden('directory', urlencode($image->directory));
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
        return $tpl;
    }
}


class FC_Document extends PHPWS_Document {
    function getRowTags()
    {
        $vars['document_id'] = $this->id;

        $vars['action'] = 'admin_edit_document';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'filecabinet', $vars);

        $vars['action'] = 'clip_document';
        $links[] = PHPWS_Text::moduleLink(_('Clip'), 'filecabinet', $vars);

        $tpl['ACTION'] = implode(' | ', $links);
        $tpl['SIZE'] = $this->getSize(TRUE);

        return $tpl;
    }
}


?>
