<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'Document.php');


class Cabinet_Form {

    function getFolders($type)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $folder = new Folder;
        $folder->ftype = $type;
        $folder->loadDirectory();

        if (Current_User::allow('filecabinet')) {
            if (!is_writable($folder->_base_directory)) {
                switch ($folder->ftype) {
                case IMAGE_FOLDER:
                $this->cabinet->message = dgettext('filecabinet', 'Your images directory is not writable.');
                    break;

                case DOCUMENT_FOLDER:
                $this->cabinet->message = dgettext('filecabinet', 'Your documents directory is not writable.');
                    break;

                case MULTIMEDIA_FOLDER:
                    $this->cabinet->message = dgettext('filecabinet', 'Your multimedia directory is not writable.');
                    break;
                }

            } else {
                if (Current_User::allow('filecabinet', 'edit_folders', null, null, true)) {
                    $links[] = $folder->editLink();
                    $pagetags['ADMIN_LINKS'] = implode(' | ', $links);
                }
            }
        }

        $pagetags['TITLE_LABEL'] = dgettext('filecabinet', 'Title');
        $pagetags['ITEM_LABEL']  = dgettext('filecabinet', 'Items');
        $pagetags['PUBLIC_LABEL'] = dgettext('filecabinet', 'Public/Private');

        $pager = new DBPager('folders', 'Folder');
        $pager->setModule('filecabinet');
        $pager->setTemplate('folder_list.tpl');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');
        $pager->setEmptyMessage(dgettext('filecabinet', 'No folders found.'));
        $pager->addWhere('ftype', $type);

        $this->cabinet->content = $pager->get();
    }

    /**
     * This forms lets admins pick files uploaded to the server for sorting on the site.
     */
    function classifyFileList()
    {
        $this->cabinet->title = dgettext('filecabinet', 'Classify files');

        $classify_dir = $this->cabinet->getClassifyDir();
        
        if (empty($classify_dir) || !is_dir($classify_dir)) {
            $this->cabinet->content = dgettext('filecabinet', 
                                               'Unable to locate the classify directory. Please check your File Cabinet settings, configuration file and directory permissions.');
            return;
        }

        $allowed_file_types = unserialize(ALLOWED_DOCUMENT_TYPES);

        $result = PHPWS_File::readDirectory($classify_dir, false, true);

        if (empty($result)) {
            $this->cabinet->content = dgettext('filecabinet', 'The incoming file directory is currently empty.');
            return;
        }

        if (PHPWS_Error::logIfError($result)) {
            $this->cabinet->content = dgettext('filecabinet', 'An error occurred when trying to read your incoming file directory.');
            return;
        }

        $form = new PHPWS_Form('classify_file_list');
        $form->addHidden('module', 'filecabinet');

        $options['classify']        = dgettext('filecabinet', '-- Pick option --');
        $options['classify_file']   = dgettext('filecabinet', 'Classify checked');
        $options['delete_incoming'] = dgettext('filecabinet', 'Delete checked');

        $form->addSelect('aop', $options);
        $tpl = $form->getTemplate();

        $js_vars['value']        = dgettext('filecabinet', 'Go');
        $js_vars['select_id']    = 'classify_file_list_aop';
        $js_vars['action_match'] = 'delete_incoming';
        $js_vars['message']      = dgettext('filecabinet', 'Are you sure you wish to delete these files?');
        $tpl['SUBMIT'] = javascript('select_confirm', $js_vars);

        $tpl['CHECK_ALL'] = javascript('check_all', array('checkbox_name'=>'file_list[]'));

        foreach ($result as $file) {
            $links = array();
            $rowtpl['CHECK'] = sprintf('<input type="checkbox" name="file_list[]" value="%s" />', $file);
            $rowtpl['FILE_NAME'] = $file;
            $file_type = mime_content_type($classify_dir . $file);
            $rowtpl['FILE_TYPE'] = $file_type;

            $vars['file'] = urlencode($file);

            if (!in_array($file_type, $allowed_file_types)) {
                $rowtpl['ERROR'] = ' class="error"';
            } else {
                $rowtpl['ERROR'] = null;
            $vars['aop'] = 'classify_file';
                $links[] = PHPWS_Text::secureLink(dgettext('filecabinet', 'Classify'), 'filecabinet', $vars);
            }

            $vars['aop'] = 'delete_incoming';
            $cnf_js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this file?');
            $cnf_js['ADDRESS'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);
            $cnf_js['LINK'] = dgettext('filecabinet', 'Delete');
            $links[] = javascript('confirm', $cnf_js);

            $rowtpl['ACTION'] = implode(' | ', $links);

            $tpl['file-list'][] = $rowtpl;
        }

        $tpl['FILENAME_LABEL'] = dgettext('filecabinet', 'File name');
        $tpl['FILETYPE_LABEL'] = dgettext('filecabinet', 'File type');
        $tpl['ACTION_LABEL']   = dgettext('filecabinet', 'Action');

        $this->cabinet->content = PHPWS_Template::process($tpl, 'filecabinet', 'classify_list.tpl');

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
        $form->setCols('description', 40);
        $form->setRows('description', 8);
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
        PHPWS_Core::bookmark();
        Layout::addStyle('filecabinet');
        PHPWS_Core::initCoreClass('DBPager.php');

        if ($folder->ftype == IMAGE_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $pager = new DBPager('images', 'PHPWS_Image');
            $pager->setTemplate('image_grid.tpl');
            $limits[9]  = 9;
            $limits[16] = 16;
            $limits[25] = 25;
        } elseif ($folder->ftype == DOCUMENT_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $pager = new DBPager('documents', 'PHPWS_Document');
            $pager->setTemplate('file_list.tpl');
            $pager->addToggle('class="bgcolor1"');
            $limits[10]  = 10;
            $limits[25] =  25;
            $limits[50] =  50;
        } elseif ($folder->ftype = MULTIMEDIA_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $pager = new DBPager('multimedia', 'PHPWS_Multimedia');
            $pager->setTemplate('multimedia_grid.tpl');
            $pager->addToggle('class="bgcolor1"');
            $limits[9]  = 9;
            $limits[16] = 16;
            $limits[25] = 25;
        }

        if (Current_User::allow('filecabinet', 'edit_folders', $folder->id)) {
            $links[] = $folder->uploadLink();
            $links[] = $folder->editLink();
        }

        if ($this->cabinet->panel) {
            $links[] = PHPWS_Text::moduleLink(dgettext('filecabinet', 'Back to folder list'),
                                                       'filecabinet', array('tab'=>$this->cabinet->panel->getCurrentTab()));
        }

        if (@$links) {
            $pagetags['ADMIN_LINKS'] = implode(' | ', $links);
        }

        $pagetags['ACTION_LABEL']    = dgettext('filecabinet', 'Action');
        $pagetags['SIZE_LABEL']      = dgettext('filecabinet', 'Size');
        $pagetags['FILE_NAME_LABEL'] = dgettext('filecabinet', 'File name');
        $pagetags['FILE_TYPE_LABEL'] = dgettext('filecabinet', 'File type');
        $pagetags['TITLE_LABEL']     = dgettext('filecabinet', 'Title');

        //        $pager->setLink($folder->viewLink(false));
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

    function pinFolder($key_id)
    {
        $key = new Key($key_id);

        $this->cabinet->title = sprintf(dgettext('filecabinet', 'Pin folder to "%s"'), $key->title);

        $db = new PHPWS_DB('folders');
        $db->addWhere('public_folder', 1);
        $db->addColumn('title');
        $db->addColumn('id');
        $db->setIndexBy('id');
        $result = $db->select('col');
        if (empty($result)) {
            $this->cabinet->title = dgettext('filecabinet', 'Sorry..');
            $this->cabinet->content = dgettext('filecabinet', 'No public folders are available.');
            return;
        }

        $form = new PHPWS_Form('pinfolders');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'pin_folder');
        $form->addHidden('key_id', $key_id);
        $form->addSelect('folder_id', $result);
        $form->setLabel('folder_id', dgettext('filecabinet', 'Folder'));
        $form->addSubmit('submit', dgettext('filecabinet', 'Pin folder'));
        $tpl = $form->getTemplate();

        $tpl['CANCEL'] = javascript('close_window', array('value'=>dgettext('filecabinet', 'Cancel')));

        $this->cabinet->content = PHPWS_Template::process($tpl, 'filecabinet', 'pin_folder.tpl');
    }

    function settings()
    {
        $sizes = Cabinet::getMaxSizes();

        $form = new PHPWS_FORM;
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'save_settings');

        $form->addTplTag('DOCUMENT_SETTINGS', dgettext('filecabinet', 'Document settings'));
        $form->addTplTag('IMAGE_SETTINGS', dgettext('filecabinet', 'Image settings'));
        $form->addTplTag('MULTIMEDIA_SETTINGS', dgettext('filecabinet', 'Multimedia settings'));

        $form->addText('base_doc_directory', PHPWS_Settings::get('filecabinet', 'base_doc_directory'));
        $form->setSize('base_doc_directory', '50');
        $form->setLabel('base_doc_directory', dgettext('filecabinet', 'Base document directory'));

        $form->addCheck('auto_link_parent', 1);
        $form->setMatch('auto_link_parent', PHPWS_Settings::get('filecabinet', 'auto_link_parent'));
        $form->setLabel('auto_link_parent', dgettext('filecabinet', 'Automatically link child images to parent'));

        $form->addText('max_image_width', PHPWS_Settings::get('filecabinet', 'max_image_width'));
        $form->setLabel('max_image_width', dgettext('filecabinet', 'Maximum image pixel width'));
        $form->setSize('max_image_width', 4, 4);

        $form->addText('max_image_height', PHPWS_Settings::get('filecabinet', 'max_image_height'));
        $form->setLabel('max_image_height', dgettext('filecabinet', 'Maximum image pixel height'));
        $form->setSize('max_image_height', 4, 4);

        $form->addText('max_image_size', $sizes['image']);
        $form->setLabel('max_image_size', dgettext('filecabinet', 'Maximum image file size (in bytes)'));
        $form->setSize('max_image_size', 10, 10);

        $form->addText('max_document_size', $sizes['document']);
        $form->setLabel('max_document_size', dgettext('filecabinet', 'Maximum document file size (in bytes)'));
        $form->setSize('max_document_size', 10, 10);

        $form->addText('max_multimedia_size', $sizes['multimedia']);
        $form->setLabel('max_multimedia_size', dgettext('filecabinet', 'Maximum multimedia file size (in bytes)'));
        $form->setSize('max_multimedia_size', 10, 10);

        $form->addText('max_pinned_images', PHPWS_Settings::get('filecabinet', 'max_pinned_images'));
        $form->setLabel('max_pinned_images', dgettext('filecabinet', 'Maximum pinned images shown (0 for all)'));
        $form->setSize('max_pinned_images', 3, 3);

        $form->addText('max_pinned_documents', PHPWS_Settings::get('filecabinet', 'max_pinned_documents'));
        $form->setLabel('max_pinned_documents', dgettext('filecabinet', 'Maximum pinned documents shown (0 for all)'));
        $form->setSize('max_pinned_documents', 3, 3);


        $form->addCheck('use_ffmpeg', 1);
        $form->setMatch('use_ffmpeg', PHPWS_Settings::get('filecabinet', 'use_ffmpeg'));


        $ffmpeg_directory = PHPWS_Settings::get('filecabinet', 'ffmpeg_directory');
        if (empty($ffmpeg_directory) || !is_file($ffmpeg_directory . 'ffmpeg')) {
            $form->setDisabled('use_ffmpeg');
            $form->setLabel('use_ffmpeg', dgettext('filecabinet', 'Enable FFMpeg thumbnails (enabled on ffmpeg confirmation)'));
        } else {
            $form->setLabel('use_ffmpeg', dgettext('filecabinet', 'Enable FFMpeg thumbnails'));
        }


        $form->addTplTag('CLASSIFY_SETTINGS', dgettext('filecabinet', 'Classify settings'));
        $form->addText('ffmpeg_directory', $ffmpeg_directory);
        $form->setLabel('ffmpeg_directory', dgettext('filecabinet', 'FFMpeg directory'));
        $form->setSize('ffmpeg_directory', 40);
        
        if (FC_ALLOW_CLASSIFY_DIR_SETTING) {            
            $form->addText('classify_directory', PHPWS_Settings::get('filecabinet', 'classify_directory'));
            $form->setLabel('classify_directory', dgettext('filecabinet', 'Incoming classify directory'));
            $form->setSize('classify_directory', 50, 255);
        }

        $form->addSubmit(dgettext('filecabinet', 'Save settings'));
        $tpl = $form->getTemplate();

        $tpl['SYSTEM_SIZE'] = dgettext('filecabinet', 'System upload limits');
        $tpl['SYSTEM_LABEL'] = dgettext('filecabinet', 'Server upload limit');
        $tpl['FORM_LABEL'] = dgettext('filecabinet', 'Form upload limit');
        $tpl['ABSOLUTE_LABEL'] = dgettext('filecabinet', 'Absolute upload limit');

        $tpl['MAX_SYSTEM_SIZE'] = sprintf(dgettext('filecabinet', '%s bytes'), $sizes['system']);
        $tpl['MAX_FORM_SIZE']   = sprintf(dgettext('filecabinet', '%s bytes'), $sizes['form']);
        $tpl['ABSOLUTE_SIZE']   = sprintf(dgettext('filecabinet', '%s bytes'), $sizes['absolute']);

        return PHPWS_Template::process($tpl, 'filecabinet', 'settings.tpl');
    }

    function classifyFile($files) {
        $this->cabinet->title = dgettext('filecabinet', 'Classify Files');
        $classify_dir = $this->cabinet->getClassifyDir();

        if (empty($classify_dir) || !is_dir($classify_dir)) {
            $this->cabinet->content = dgettext('filecabinet',
                                               'Unable to locate the classify directory. Please check your File Cabinet settings, configuration file and directory permissions.');
            return;
        }

        if (!is_array($files)) {
            $files = array($files);
        }

        $db = new PHPWS_DB('folders');
        // image folders
        $db->addWhere('ftype', IMAGE_FOLDER);
        $db->addColumn('id');
        $db->addColumn('title');
        $db->setIndexBy('id');
        $image_folders = $db->select('col');

        // document folders
        $db->resetWhere();
        $db->addWhere('ftype', DOCUMENT_FOLDER);
        $document_folders = $db->select('col');

        // multimedia folders
        $db->resetWhere();
        $db->addWhere('ftype', MULTIMEDIA_FOLDER);
        $multimedia_folders = $db->select('col');


        if (empty($document_folders) && empty($multimedia_folders)) {
            $both_folders = null;
        } elseif (empty($document_folders)) {
            foreach($multimedia_folders as $first_mm=>$foo);
            $both_folders = & $multimedia_folders;
        } elseif (empty($multimedia_folders)) {
            foreach($document_folders as $first_doc=>$foo);
            $both_folders = & $document_folders;
        } else {
            foreach($multimedia_folders as $first_mm=>$foo);
            foreach($document_folders as $first_doc=>$foo);
            $both_folders = $document_folders + $multimedia_folders;
        }

        $count = 0;
        foreach ($files as $file) {
            if (!is_file($classify_dir . $file)) {
                continue;
            }

            $form = new PHPWS_Form('file_form_' . $count);

            $file_info = $this->cabinet->fileInfo($classify_dir . $file);

            if ($file_info['image']) {
                if (!empty($image_folders)) {
                    $form->addSelect("folder[$count]", $image_folders);
                } else {
                    $form->addTplTag("FOLDER[$COUNT]" , dgettext('filecabinet', 'You must create an image folder.'));
                }
            } elseif ($file_info['document'] && $file_info['multimedia']) {
                if (!empty($both_folders)) {
                    $form->addSelect("folder[$count]", $both_folders);
                    $form->setOptgroup("folder[$count]", $first_doc, dgettext('filecabinet', 'Document folders'));
                    $form->setOptgroup("folder[$count]", $first_mm, dgettext('filecabinet', 'Multimedia folders'));
                } else {
                    $form->addTplTag("FOLDER", dgettext('filecabinet', 'You must create a document or multimedia folder.'));
                }
            } elseif ($file_info['document']) {
                if (!empty($document_folders)) {
                    $form->addSelect("folder[$count]", $document_folders);
                    $form->setOptgroup("folder[$count]", $first_doc, dgettext('filecabinet', 'Document folders'));
                } else {
                    $form->addTplTag("FOLDER", dgettext('filecabinet', 'You must create a document folder.'));
                }
            } elseif ($file_info['multimedia']) {
                if (!empty($multimedia_folders)) {
                    $form->addSelect("folder[$count]", $multimedia_folders);

                    $form->setOptgroup("folder[$count]", $first_mm, dgettext('filecabinet', 'Multimedia folders'));
                } else {
                    $form->addTplTag("FOLDER", dgettext('filecabinet', 'You must create a multimedia folder.'));
                }
            } else {
                continue;
            }

            $form->setTag("folder[$count]", 'folder');
            $form->setLabel("folder[$count]", dgettext('filecabinet', 'Folder'));

            $form->addText("file_title[$count]", $file);
            $form->setLabel("file_title[$count]", dgettext('filecabinet', 'Title'));
            $form->setTag("file_title[$count]", 'file_title');
            $form->setSize("file_title[$count]", 40);

            $form->addTextarea("file_description[$count]");
            $form->setLabel("file_description[$count]", dgettext('filecabinet', 'Decription'));
            $form->setTag("file_description[$count]", 'file_description');
            

            $form->addSubmit('submit', dgettext('filecabinet', 'Classify files'));
            $subtpl = $form->getTemplate();
            $subtpl['HIDDEN'] = sprintf('<input type="hidden" name="file_count[%s]" value="%s" />',
                                        $count, $file);
            $subtpl['FILE_NAME'] = $file;
            $subtpl['FILE_NAME_LABEL'] = dgettext('filecabinet', 'File name');

            unset($subtpl['START_FORM']);
            unset($subtpl['END_FORM']);

            $tpl['files'][] = $subtpl;
            $count++;
        }

        $form = new PHPWS_Form('classify_files');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'post_classifications');

        $form_template = $form->getTemplate(true,true,$tpl);

        $this->cabinet->content = PHPWS_Template::process($form_template, 'filecabinet', 'classify_file.tpl');
    }
}

?>
