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
                if (Current_User::allow('filecabinet', 'edit_folders')) {
                    $links[] = $folder->editLink();
                    $pagetags['ADMIN_LINKS'] = implode(' | ', $links);
                }
            }
        }

        if ($folder->ftype == IMAGE_FOLDER) {
            $pagetags['MODULE_CREATED_LABEL'] = dgettext('filecabinet', 'Created in');
        }

        $pagetags['TITLE_LABEL'] = dgettext('filecabinet', 'Title');
        $pagetags['ITEM_LABEL']  = dgettext('filecabinet', 'Items');
        $pagetags['PUBLIC_LABEL'] = dgettext('filecabinet', 'Public');

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

        $allowed_file_types = $this->cabinet->getAllowedTypes();

        $result = PHPWS_File::readDirectory($classify_dir, false, true);

        if (empty($result)) {
            $this->cabinet->content = dgettext('filecabinet', 'The incoming file directory is currently empty.');
            return;
        }

        sort($result);

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

        $tpl['CHECK_ALL'] = javascript('check_all', array('checkbox_name'=>'file_list'));

        foreach ($result as $file) {
            $links = array();
            $rowtpl['CHECK'] = sprintf('<input type="checkbox" name="file_list[]" value="%s" />', $file);
            $rowtpl['FILE_NAME'] = $file;
            $rowtpl['FILE_TYPE'] = PHPWS_File::getVbType($file);

            $vars['file'] = urlencode($file);

            if (!$this->cabinet->fileTypeAllowed($file)) {
                $rowtpl['ERROR'] = ' class="error"';
                $rowtpl['MESSAGE'] = dgettext('filecabinet', 'File type not allowed');
            } elseif (!PHPWS_File::checkMimeType($classify_dir . $file)) {
                $rowtpl['ERROR'] = ' class="error"';
                $rowtpl['MESSAGE'] = dgettext('filecabinet', 'Unknown or mismatched mime type');
            } else {
                $rowtpl['ERROR'] = $rowtpl['MESSAGE'] = null;
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

    function editFolder($folder, $select_module=true)
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

        if ($select_module) {
            $modules = PHPWS_Core::getModuleNames();
            $modlist[0] = dgettext('filecabinet', '-- General --');
            foreach ($modules as $key=>$mod) {
                $modlist[$key] = $mod;
            }
            $form->addSelect('module_created', $modlist);
            if (!empty($folder->module_created)) {
                $form->setMatch('module_created', $folder->module_created);                
            }
            $form->setLabel('module_created', dgettext('filecabinet', 'Module reservation'));
        } else {
            $form->addHidden('module_created', $folder->module_created);
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

        if ($folder->ftype == IMAGE_FOLDER) {
            $resizes = Cabinet::getResizes(0, true);
            $form->addSelect('max_image_dimension', $resizes);
            $form->setLabel('max_image_dimension', dgettext('filecabinet', 'Maximum image upload dimension'));
            $form->setMatch('max_image_dimension', $folder->max_image_dimension);
        }

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

        if (Current_User::allow('filecabinet', 'edit_folders', $folder->id, 'folder')) {
            $links[] = $folder->uploadLink(false);
            if ($folder->ftype == MULTIMEDIA_FOLDER) {
                $links[] = $folder->embedLink();
            }
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

        $form = new PHPWS_Form('settings');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'save_settings');

        $form->addTplTag('DOCUMENT_SETTINGS', dgettext('filecabinet', 'Document settings'));
        $form->addTplTag('IMAGE_SETTINGS', dgettext('filecabinet', 'Image settings'));
        $form->addTplTag('MULTIMEDIA_SETTINGS', dgettext('filecabinet', 'Multimedia settings'));

        $form->addText('base_doc_directory', PHPWS_Settings::get('filecabinet', 'base_doc_directory'));
        $form->setSize('base_doc_directory', '50');
        $form->setLabel('base_doc_directory', dgettext('filecabinet', 'Base document directory'));

        $form->addText('max_image_dimension', PHPWS_Settings::get('filecabinet', 'max_image_dimension'));
        $form->setLabel('max_image_dimension', dgettext('filecabinet', 'Maximum image pixel dimension'));
        $form->setSize('max_image_dimension', 4, 4);

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

        $form->addText('crop_threshold', PHPWS_Settings::get('filecabinet', 'crop_threshold'));
        $form->setLabel('crop_threshold', dgettext('filecabinet', 'Crop pixel threshold'));
        $form->setSize('crop_threshold', 4, 4);

        $form->addCheck('use_ffmpeg', 1);
        $form->setMatch('use_ffmpeg', PHPWS_Settings::get('filecabinet', 'use_ffmpeg'));

        $form->addCheck('caption_images', 1);
        $form->setMatch('caption_images', PHPWS_Settings::get('filecabinet', 'caption_images'));
        $form->setLabel('caption_images', dgettext('filecabinet', 'Caption images'));

        $form->addCheck('popup_image_navigation', 1);
        $form->setMatch('popup_image_navigation', PHPWS_Settings::get('filecabinet', 'popup_image_navigation'));
        $form->setLabel('popup_image_navigation', dgettext('filecabinet', 'Popup images allow folder navigation'));

        $form->addText('max_thumbnail_size', PHPWS_Settings::get('filecabinet', 'max_thumbnail_size'));
        $form->setLabel('max_thumbnail_size', dgettext('filecabinet', 'Maximum thumbnail pixel dimension'));
        $form->setSize('max_thumbnail_size', 3, 3);

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

        $form->addRadioAssoc('jcaro_type', array(0=>dgettext('filecabinet', 'Horizontal'),
                                                 1=>dgettext('filecabinet', 'Vertical')));
        $form->setMatch('jcaro_type', (int)PHPWS_Settings::get('filecabinet', 'vertical_folder'));

        $num = array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8);
        $form->addSelect('number_visible', $num);
        $form->setMatch('number_visible', PHPWS_Settings::get('filecabinet', 'number_visible'));
        $form->setLabel('number_visible', dgettext('filecabinet', 'Number of thumbnails visible'));

        $form->addSubmit(dgettext('filecabinet', 'Save settings'));
        $tpl = $form->getTemplate();

        $tpl['CAROUSEL'] = dgettext('filecabinet', 'Carousel defaults');
        $tpl['SYSTEM_SIZE'] = dgettext('filecabinet', 'System upload limits');
        $tpl['SYSTEM_LABEL'] = dgettext('filecabinet', 'Server upload limit');
        $tpl['FORM_LABEL'] = dgettext('filecabinet', 'Form upload limit');
        $tpl['ABSOLUTE_LABEL'] = dgettext('filecabinet', 'Absolute upload limit');

        $tpl['MAX_SYSTEM_SIZE'] = sprintf(dgettext('filecabinet', '%s bytes'), $sizes['system']);
        $tpl['MAX_FORM_SIZE']   = sprintf(dgettext('filecabinet', '%s bytes'), $sizes['form']);
        $tpl['ABSOLUTE_SIZE']   = sprintf(dgettext('filecabinet', '%s bytes'), $sizes['absolute']);

        return PHPWS_Template::process($tpl, 'filecabinet', 'settings.tpl');
    }

    function classifyFile($files) 
    {
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

        $count = 0;

        $image_types    = $this->cabinet->getAllowedTypes('image');
        $document_types = $this->cabinet->getAllowedTypes('document');
        $media_types    = $this->cabinet->getAllowedTypes('media');

        foreach ($files as $file) {
            if (!is_file($classify_dir . $file) || !PHPWS_File::checkMimeType($classify_dir . $file)
                || !$this->cabinet->fileTypeAllowed($file)) {
                continue;
            }

            $form = new PHPWS_Form('file_form_' . $count);
            $ext = PHPWS_File::getFileExtension($file);
            if (in_array($ext, $image_types)) {
                $folders = & $image_folders;
            } elseif (in_array($ext, $document_types)) {
                $folders = & $document_folders;
            } elseif (in_array($ext, $media_types)) {
                $folders = & $multimedia_folders;
            } else {
                
            }

            $form->addSelect("folder[$count]", $folders);
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

        if (isset($tpl)) {
            $form_template = $form->getTemplate(true,true,$tpl);
            $this->cabinet->content = PHPWS_Template::process($form_template, 'filecabinet', 'classify_file.tpl');
        } else {
            $this->cabinet->content = dgettext('filecabinet', 'Unable to classify files.');
        }
    }

    function fileTypes()
    {
        include PHPWS_SOURCE_DIR . 'mod/filecabinet/inc/known_types.php';

        $image_types = explode(',', PHPWS_Settings::get('filecabinet', 'image_files'));
        $media_types = explode(',', PHPWS_Settings::get('filecabinet', 'media_files'));
        $doc_types   = explode(',', PHPWS_Settings::get('filecabinet', 'document_files'));

        $all_file_types = PHPWS_File::getAllFileTypes();

        $form = new PHPWS_Form('allowed-file-types');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'post_allowed_files');

        $img_checks = $this->sortType($known_images, $all_file_types);
        if (!empty($img_checks)) {
            $form->addCheckAssoc('allowed_images', $img_checks);
            $form->setMatch('allowed_images', $image_types);
        }

        $media_checks = $this->sortType($known_media, $all_file_types);
        if (isset($media_checks)) {
            $form->addCheckAssoc('allowed_media', $media_checks);
            $form->setMatch('allowed_media', $media_types);
        }

        $doc_checks = $this->sortType($known_documents, $all_file_types);
        if (isset($doc_checks)) {
            $form->addCheckAssoc('allowed_documents', $doc_checks);
            $form->setMatch('allowed_documents', $doc_types);
        }

        $form->useRowRepeat();
        $form->addSubmit(dgettext('filecabinet', 'Save allowed files'));
        $tpl = $form->getTemplate();

        $tpl['CHECK_IMAGES'] = javascript('check_all', array('checkbox_name' => 'allowed_images'));
        $tpl['CHECK_MEDIA'] = javascript('check_all', array('checkbox_name' => 'allowed_media'));
        $tpl['CHECK_DOCUMENTS'] = javascript('check_all', array('checkbox_name' => 'allowed_documents'));

        return PHPWS_Template::process($tpl, 'filecabinet', 'allowed_types.tpl');
    }

    function sortType($known, &$all_file_types) {
        foreach ($known as $type) {
            @$file_info = $all_file_types[$type];

            if (empty($file_info) || (isset($file_info['base']) && @$file_info['base'] != $type)) {
                continue;
            }
            $checks[$type] = $file_info['vb'];
        }
        asort($checks);
        return $checks;
    }
    
    function saveSettings()
    {
        if (empty($_POST['base_doc_directory'])) {
            $errors[] = dgettext('filecabinet', 'Default document directory may not be blank');
        } elseif (!is_dir($_POST['base_doc_directory'])) {
            $errors[] = dgettext('filecabinet', 'Document directory does not exist.');
        } elseif (!is_writable($_POST['base_doc_directory'])) {
            $errors[] = dgettext('filecabinet', 'Unable to write to document directory.');
        } elseif (!is_readable($_POST['base_doc_directory'])) {
            $errors[] = dgettext('filecabinet', 'Unable to read document directory.');
        } else {
            $dir = $_POST['base_doc_directory'];
            if (!preg_match('@/$@', $dir)) {
                $dir .= '/';
            }
            PHPWS_Settings::set('filecabinet', 'base_doc_directory', $dir);
        }

        if (empty($_POST['max_image_dimension']) || $_POST['max_image_dimension'] < 50) {
            $errors[] = dgettext('filecabinet', 'The max image dimension must be greater than 50 pixels.');
        } else {
            PHPWS_Settings::set('filecabinet', 'max_image_dimension', $_POST['max_image_dimension']);
        }

        if (isset($_POST['classify_file_type'])) {
            PHPWS_Settings::set('filecabinet', 'classify_file_type', 1);
        } else {
            PHPWS_Settings::set('filecabinet', 'classify_file_type', 0);
        }

        $max_file_upload = preg_replace('/\D/', '', ini_get('upload_max_filesize'));

        if (empty($_POST['max_image_size'])) {
            $errors[] = dgettext('filecabinet', 'You must set a maximum image file size.');
        } else {
            $max_image_size = (int)$_POST['max_image_size'];
            if ( ($max_image_size / 1000000) > ((int)$max_file_upload) ) {
                $errors[] = sprintf(dgettext('filecabinet', 'Your maximum image size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_image_size', $max_image_size);
            }
        }

        if (empty($_POST['max_document_size'])) {
            $errors[] = dgettext('filecabinet', 'You must set a maximum document file size.');
        } else {
            $max_document_size = (int)$_POST['max_document_size'];
            if ( ($max_document_size / 1000000) > (int)$max_file_upload ) {
                $errors[] = sprintf(dgettext('filecabinet', 'Your maximum document size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_document_size', $max_document_size);
            }
        }

        if (empty($_POST['max_multimedia_size'])) {
            $errors[] = dgettext('filecabinet', 'You must set a maximum multimedia file size.');
        } else {
            $max_multimedia_size = (int)$_POST['max_multimedia_size'];
            if ( ($max_multimedia_size / 1000000) > (int)$max_file_upload ) {
                $errors[] = sprintf(dgettext('filecabinet', 'Your maximum multimedia size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_multimedia_size', $max_multimedia_size);
            }
        }

        if (empty($_POST['max_pinned_images'])) {
            PHPWS_Settings::set('filecabinet', 'max_pinned_images', 0);
        } else {
            PHPWS_Settings::set('filecabinet', 'max_pinned_images', (int)$_POST['max_pinned_images']);
        }

        $threshold = (int)$_POST['crop_threshold'];
        if ($threshold < 0) {
            PHPWS_Settings::set('filecabinet', 'crop_threshold', 0);
        } else {
            PHPWS_Settings::set('filecabinet', 'crop_threshold', $threshold);
        }

        if (empty($_POST['max_pinned_documents'])) {
            PHPWS_Settings::set('filecabinet', 'max_pinned_documents', 0);
        } else {
            PHPWS_Settings::set('filecabinet', 'max_pinned_documents', (int)$_POST['max_pinned_documents']);
        }

        if (isset($_POST['use_ffmpeg'])) {
            PHPWS_Settings::set('filecabinet', 'use_ffmpeg', 1);
        } else {
            PHPWS_Settings::set('filecabinet', 'use_ffmpeg', 0);
        }

        if (isset($_POST['auto_link_parent'])) {
            PHPWS_Settings::set('filecabinet', 'auto_link_parent', 1);
        } else {
            PHPWS_Settings::set('filecabinet', 'auto_link_parent', 0);
        }

        if (isset($_POST['caption_images'])) {
            PHPWS_Settings::set('filecabinet', 'caption_images', 1);
        } else {
            PHPWS_Settings::set('filecabinet', 'caption_images', 0);
        }

        if (isset($_POST['popup_image_navigation'])) {
            PHPWS_Settings::set('filecabinet', 'popup_image_navigation', 1);
        } else {
            PHPWS_Settings::set('filecabinet', 'popup_image_navigation', 0);
        }

        if (empty($_POST['max_thumbnail_size'])) {
            PHPWS_Settings::set('filecabinet', 'max_thumbnail_size', 100);
        } else {
            $tn_size = (int)$_POST['max_thumbnail_size'];
            if ($tn_size < 30) {
                $errors[] = dgettext('filecabinet', 'Thumbnails must be over 30px in size.');
            } elseif ($tn_size > 999) {
                $errors[] = dgettext('filecabinet', 'Thumbnail size is too large.');
            } else {
                PHPWS_Settings::set('filecabinet', 'max_thumbnail_size', $tn_size);
            }
        }

        $ffmpeg_dir = strip_tags($_POST['ffmpeg_directory']);
        if (empty($ffmpeg_dir)) {
            PHPWS_Settings::set('filecabinet', 'ffmpeg_directory', null);
            PHPWS_Settings::set('filecabinet', 'use_ffmpeg', 0);
        } else {
            if (!preg_match('@/$@', $ffmpeg_dir)) {
                $ffmpeg_dir .= '/';
            }
            PHPWS_Settings::set('filecabinet', 'ffmpeg_directory', $ffmpeg_dir);
            if (!is_file($ffmpeg_dir . 'ffmpeg')) {
                $errors[] = dgettext('filecabinet', 'Could not find ffmpeg executable.');
                PHPWS_Settings::set('filecabinet', 'use_ffmpeg', 0);
            }
        }

        if (FC_ALLOW_CLASSIFY_DIR_SETTING) {
            if (!empty($_POST['classify_directory'])) {
                $classify_dir = $_POST['classify_directory'];
                if (!preg_match('@/$@', $classify_dir)) {
                    $classify_dir .= '/';
                }
                if (!is_dir($classify_dir)) {
                    $errors[] = dgettext('filecabinet', 'Classify directory could not be found.');
                } elseif(!is_writable($classify_dir)) {
                    $errors[] = dgettext('filecabinet', 'The web server does not have permissions for the classify directory.');
                } else {
                    PHPWS_Settings::set('filecabinet', 'classify_directory', $classify_dir);
                }
            }
        }

        PHPWS_Settings::set('filecabinet', 'vertical_folder', (int) $_POST['jcaro_type']);
        PHPWS_Settings::set('filecabinet', 'number_visible', (int) $_POST['number_visible']);

        PHPWS_Settings::save('filecabinet');
        if (isset($errors)) {
            return $errors;
        } else {
            return true;
        }
    }

    function postAllowedFiles()
    {
        if (empty($_POST['allowed_images'])) {
            PHPWS_Settings::set('filecabinet', 'image_files', '');
        } else {
            PHPWS_Settings::set('filecabinet', 'image_files', implode(',', $_POST['allowed_images']));
        }

        if (empty($_POST['allowed_media'])) {
            PHPWS_Settings::set('filecabinet', 'media_files', '');
        } else {
            PHPWS_Settings::set('filecabinet', 'media_files', implode(',', $_POST['allowed_media']));
        }

        if (empty($_POST['allowed_documents'])) {
            PHPWS_Settings::set('filecabinet', 'document_files', '');
        } else {
            PHPWS_Settings::set('filecabinet', 'document_files', implode(',', $_POST['allowed_documents']));
        }

        PHPWS_Settings::save('filecabinet');
    }
}

?>
