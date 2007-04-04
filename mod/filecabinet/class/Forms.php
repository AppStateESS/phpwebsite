<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'Document.php');

class Cabinet_Form {
    var $document = null;

    function getFolders($type)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $folder = new Folder;
        $folder->ftype = $type;
        $links[] = $folder->editLink();

        $pagetags['LINKS'] = implode(' | ', $links);
        $pagetags['TITLE_LABEL'] = dgettext('filecabinet', 'Title');
        $pagetags['ITEM_LABEL']  = dgettext('filecabinet', 'Items');

        $pager = new DBPager('folders', 'Folder');
        $pager->setModule('filecabinet');
        $pager->setTemplate('folder_list.tpl');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');
        $pager->setEmptyMessage(dgettext('filecabinet', 'No folders found.'));
        $pager->addWhere('ftype', $type);

        $this->cabinet->content = $pager->get();
    }

    function editFolder($folder)
    {
        $form = new PHPWS_Form('folder');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'post_folder');
        $form->addHidden('ftype', $folder->ftype);

        if ($folder->id) {
            $form->addHidden('folder_id', $folder->id);
            $form->addSubmit('submit', dgettext('filecabinet', 'Update folder'));
        } else {
            $form->addSubmit('submit', dgettext('filecabinet', 'Create folder'));
        }

        $form->addTextField('title', $folder->title);
        $form->setSize('title', 40, 255);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));

        $form->addTextArea('description', $folder->description);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        $form->addRadio('public_folder', array(0, 1));
        $form->setLabel('public_folder', array( dgettext('filecabinet', 'Private'), dgettext('filecabinet', 'Public')));
        $form->setMatch('public_folder', $folder->public_folder);

        /**
         * Need to add icon selection. For now, images will use last uploaded
         * image. Documents and empty image folders will use default icon
         */

        $tpl = $form->getTemplate();

        $tpl['CLOSE'] = javascript('close_window');
        return PHPWS_Template::process($tpl, 'filecabinet', 'edit_folder.tpl');
    }

    function folderContents($folder, $pick_image=false)
    {
        Layout::addStyle('filecabinet');
        PHPWS_Core::initCoreClass('DBPager.php');

        if ($folder->ftype == IMAGE_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $pagetags['ADD'] = $folder->imageUploadLink();
            $pager = new DBPager('images', 'PHPWS_Image');
            $pager->setTemplate('image_grid.tpl');
            $limits[9]  = 9;
            $limits[16] = 16;
            $limits[25] = 25;

        } elseif ($folder->ftype == DOCUMENT_FOLDER) {
            $pagetags['ADD'] = $folder->documentUploadLink();
            $pager = new DBPager('documents', 'PHPWS_Document');
            $pager->setTemplate('file_list.tpl');
            $pager->addToggle('class="bgcolor1"');
            $limits[10]  = 10;
            $limits[25] =  25;
            $limits[50] =  50;
        }

        $pagetags['ACTION_LABEL'] = dgettext('filecabinet', 'Action');
        $pagetags['SIZE_LABEL'] = dgettext('filecabinet', 'Size');
        $pagetags['FILE_NAME_LABEL'] = dgettext('filecabinet', 'File name');
        $pagetags['FILE_TYPE_LABEL'] = dgettext('filecabinet', 'File type');
        $pagetags['TITLE_LABEL'] = dgettext('filecabinet', 'Title');

        $pager->setLimitList($limits);
        $pager->setDefaultLimit(16);

        $pager->setSearch('file_name', 'title');

        $pager->addWhere('folder_id', $folder->id);
        $pager->setOrder('title', 'asc', true);
        $pager->setModule('filecabinet');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags', $pick_image);
        $pager->setEmptyMessage(dgettext('filecabinet', 'Folder is empty.'));
        $this->cabinet->content = $pager->get();
    }

    function settings()
    {
        $form = new PHPWS_FORM;
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'save_settings');

        $form->addText('base_doc_directory', PHPWS_Settings::get('filecabinet', 'base_doc_directory'));
        $form->setSize('base_doc_directory', '50');
        $form->setLabel('base_doc_directory', dgettext('filecabinet', 'Base document directory'));

        $form->addText('max_image_width', PHPWS_Settings::get('filecabinet', 'max_image_width'));
        $form->setLabel('max_image_width', dgettext('filecabinet', 'Maximum image pixel width'));
        $form->setSize('max_image_width', 4, 4);

        $form->addText('max_image_height', PHPWS_Settings::get('filecabinet', 'max_image_height'));
        $form->setLabel('max_image_height', dgettext('filecabinet', 'Maximum image pixel height'));
        $form->setSize('max_image_height', 4, 4);

        $form->addText('max_image_size', PHPWS_Settings::get('filecabinet', 'max_image_size'));
        $form->setLabel('max_image_size', dgettext('filecabinet', 'Maximum image file size (in bytes)'));
        $form->setSize('max_image_size', 10, 10);

        $form->addText('max_document_size', PHPWS_Settings::get('filecabinet', 'max_document_size'));
        $form->setLabel('max_document_size', dgettext('filecabinet', 'Maximum document file size (in bytes)'));
        $form->setSize('max_document_size', 10, 10);

        $form->addSubmit(dgettext('filecabinet', 'Save settings'));
        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'filecabinet', 'settings.tpl');
    }


}

?>
