<?php

class Cabinet_Form {
    function imageManager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('images', 'FC_Image');
        $pager->setModule('filecabinet');
        $pager->setTemplate('imageList.tpl');
        //        $pager->setLink('index.php?module=filecabinet&amp;tab=image&amp;authkey=' . Current_User::getAuthKey());
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
        $form->addSelect('mod_title', array('all'=> _('All'), 'profiler'=>'profiler'));

        if (Current_User::javascriptEnabled()) {
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
        $pager = & new DBPager('documents', 'PHPWS_Document');
        $pager->setModule('filecabinet');
        $pager->setTemplate('documentList.tpl');
        //        $pager->setLink('index.php?module=filecabinet&amp;tab=document&amp;authkey=' . Current_User::getAuthKey());
        $pager->addRowTags('getRowTags');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');

        $tags['TITLE']      = _('Title');
        $tags['FILENAME']   = _('Filename');
        $tags['DOC_TYPE']   = _('Document Type');
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

}

PHPWS_Core::initCoreClass('Image.php');

class FC_Image extends PHPWS_Image {
    function getRowTags()
    {
        $vars['action'] = 'edit_image';
        $vars['image_id'] = $this->id;
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'filecabinet', $vars);
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

}

?>
