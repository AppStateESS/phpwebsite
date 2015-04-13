<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'Document.php');

class Cabinet_Form
{
    public $cabinet = null;

    private function getModal()
    {
        $modal = new \Modal('folder-form');
        $modal->setWidthPercentage(30);
        $modal->addButton('<button class="btn btn-success save-element">' . _('Save') . '</button>');
        return $modal->get();
    }

    public function getFolders($type)
    {
        javascript('jquery');
        $src = PHPWS_SOURCE_HTTP . 'mod/filecabinet/javascript/folder_options/folders.js';
        \Layout::addJSHeader("<script type='text/javascript' src='$src'></script>", 'folder-options');

        PHPWS_Core::initCoreClass('DBPager.php');
        $folder = new Folder;
        $folder->ftype = $type;
        $folder->loadDirectory();

        $pagetags['MODAL'] = $this->getModal();

        if (Current_User::allow('filecabinet')) {
            if (!is_dir($folder->_base_directory)) {
                $this->cabinet->message = dgettext('filecabinet', "Directory {$folder->_base_directory} does not exist.");
            } else {
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
                        $authkey = \Current_User::getAuthKey();
                        $pagetags['ADMIN_LINKS'] = "<button class='btn btn-success show-modal' data-operation='aop' data-command='add_folder' data-folder_id='0' data-ftype='$folder->ftype'><i class='fa fa-plus'></i> Add Folder</button>";
                        //$pagetags['ADMIN_LINKS'] = $folder->editLink();
                    }
                }
            }
        }

        switch ($folder->ftype) {
            case IMAGE_FOLDER:
                $aop = 'image';
                $table = 'images';
                break;

            case DOCUMENT_FOLDER:
                $aop = 'document';
                $table = 'documents';
                break;

            case MULTIMEDIA_FOLDER:
                $table = $aop = 'multimedia';
                break;
        }

        $pagetags['ITEM_LABEL'] = dgettext('filecabinet', 'Items');

        $form = new PHPWS_Form('folder-search');
        $form->setMethod('get');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', $aop);
        $form->addHidden('ftype', $folder->ftype);
        $folder_search = isset($_GET['folder_search']) ? $_GET['folder_search'] : null;
        $form->addText('folder_search', $folder_search);
        $form->addSubmit(dgettext('filecabinet', 'Search folders for file'));
        $search_tpl = $form->getTemplate();

        $pagetags['FILE_SEARCH'] = $search_tpl['START_FORM'] . $search_tpl['FOLDER_SEARCH'] . $search_tpl['SUBMIT'] . $search_tpl['END_FORM'];

        $pager = new DBPager('folders', 'Folder');

        if (!empty($_GET['folder_search'])) {
            $pl = UTF8_MODE ? '\pL' : null;
            $search = preg_replace("/[^\w\s\-$pl]/", '', $_GET['folder_search']);
            if (!empty($search)) {
                $pager->addWhere("$table.file_name", $search, 'REGEXP', 'or', 'g1');
                $pager->addWhere("$table.title", $search, 'REGEXP', 'or', 'g1');
                $pager->addWhere('folders.id', "$table.folder_id");
            }
        }

        $pager->addSortHeader('title', dgettext('filecabinet', 'Title'));
        $pager->addSortHeader('public_folder', dgettext('filecabinet', 'Public'));
        $pager->setModule('filecabinet');
        $pager->setTemplate('Forms/folder_list.tpl');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');
        $pager->setEmptyMessage(dgettext('filecabinet', 'No folders found.'));
        $pager->addWhere('ftype', $type);
        $pager->setDefaultOrder('title');
        $pager->setAutoSort(false);
        $this->cabinet->content = $pager->get();
    }

    /**
     * This forms lets admins pick files uploaded to the server for sorting on the site.
     */
    public function classifyFileList()
    {
        $this->cabinet->title = dgettext('filecabinet', 'Classify files');

        $classify_dir = $this->cabinet->getClassifyDir();

        if (empty($classify_dir) || !is_dir($classify_dir)) {
            $this->cabinet->content = dgettext('filecabinet', 'Unable to locate the classify directory. Please check your File Cabinet settings, configuration file and directory permissions.');
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
        $form->addHidden('aop', 'classify_action');
        $form->addHidden('module', 'filecabinet');

        $image_folders = Cabinet::listFolders(IMAGE_FOLDER, true);
        if (!empty($image_folders)) {
            $form->addSelect('image_folders', $image_folders);
            $form->addSubmit('image_force', dgettext('filecabinet', 'Put all checked images here'));
        }

        $document_folders = Cabinet::listFolders(DOCUMENT_FOLDER, true);
        if (!empty($document_folders)) {
            $form->addSelect('document_folders', $document_folders);
            $form->addSubmit('document_force', dgettext('filecabinet', 'Put all checked documents here'));
        }

        $media_folders = Cabinet::listFolders(MULTIMEDIA_FOLDER, true);
        if (!empty($media_folders)) {
            $form->addSelect('media_folders', $media_folders);
            $form->addSubmit('media_force', dgettext('filecabinet', 'Put all checked media here'));
        }


        $options['classify'] = dgettext('filecabinet', '-- Pick option --');
        $options['classify_file'] = dgettext('filecabinet', 'Classify checked');
        $options['delete_incoming'] = dgettext('filecabinet', 'Delete checked');

        $form->addSelect('process_checked', $options);
        $tpl = $form->getTemplate();

        $js_vars['value'] = dgettext('filecabinet', 'Go');
        $js_vars['select_id'] = 'classify_file_list_aop';
        $js_vars['action_match'] = 'delete_incoming';
        $js_vars['message'] = dgettext('filecabinet', 'Are you sure you wish to delete these files?');
        $tpl['SUBMIT'] = javascript('select_confirm', $js_vars);

        $tpl['CHECK_ALL'] = javascript('check_all', array('checkbox_name' => 'file_list'));

        foreach ($result as $file) {
            $links = array();
            $id = preg_replace('/\W/', '-', $file);

            $rowtpl['FILE_NAME'] = sprintf('<label for="%s">%s</label>', $id, $file);
            $rowtpl['FILE_TYPE'] = PHPWS_File::getVbType($file);

            $vars['file'] = urlencode($file);

            if (!$this->cabinet->fileTypeAllowed($file)) {
                $rowtpl['ERROR'] = ' class="error"';
                $rowtpl['MESSAGE'] = dgettext('filecabinet', 'File type not allowed');
            } elseif (!PHPWS_File::checkMimeType($classify_dir . $file)) {
                if (!is_readable($classify_dir . $file)) {
                    $rowtpl['ERROR'] = ' class="error"';
                    $rowtpl['MESSAGE'] = dgettext('filecabinet', 'File is unreadable');
                } else {
                    $rowtpl['ERROR'] = ' class="error"';
                    $rowtpl['MESSAGE'] = dgettext('filecabinet', 'Unknown or mismatched mime type');
                }
            } else {
                $rowtpl['CHECK'] = sprintf('<input type="checkbox" id="%s" name="file_list[]" value="%s" />', $id, $file);
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
        $tpl['ACTION_LABEL'] = dgettext('filecabinet', 'Action');

        $this->cabinet->content = PHPWS_Template::process($tpl, 'filecabinet', 'Forms/classify_list.tpl');
    }

    public function editFolder($folder)
    {
        $form = new PHPWS_Form('file-form');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'post_folder');
        $form->addHidden('ftype', $folder->ftype);

        if ($folder->id) {
            $form->addHidden('folder_id', $folder->id);
        }

        $form->addTextField('title', $folder->title);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));
        $form->setClass('title', 'form-control');

        if ($folder->ftype == DOCUMENT_FOLDER) {
            $form->addRadio('public_folder', array(0, 1));
            $form->setLabel('public_folder', array(dgettext('filecabinet', 'Indirect links'),
                dgettext('filecabinet', 'Direct links')));
        }
        $form->setMatch('public_folder', $folder->public_folder);
        if ($folder->ftype == IMAGE_FOLDER) {
            $resizes = Cabinet::getResizes(0, true);
            $form->addSelect('max_image_dimension', $resizes);
            $form->setLabel('max_image_dimension', dgettext('filecabinet', 'Maximum image upload dimension'));
            $form->setMatch('max_image_dimension', $folder->max_image_dimension);
            $form->setClass('max_image_dimension', 'form-control');
        }

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'filecabinet', 'Forms/edit_folder.tpl');
    }

    /**
     * Lists the contents of a folder for adminstrative changes.
     * @param object $folder
     * @param boolean $pick_image
     */
    public function folderContents($folder)
    {
        $links = array();
        javascript('jquery');
        $src = PHPWS_SOURCE_HTTP . 'mod/filecabinet/javascript/folder_options/contents.js';
        \Layout::addJSHeader("<script type='text/javascript' src='$src'></script>", 'folder-options');
        Layout::addStyle('filecabinet');
        PHPWS_Core::initCoreClass('DBPager.php');

        $dir_write = true;
        if (!is_writable($folder->getFullDirectory())) {
            $this->cabinet->message .= dgettext('filecabinet', 'Warning: this folder\'s directory is not writable.');
            $dir_write = false;
        }

        if ($folder->ftype == IMAGE_FOLDER) {
            javascript('lightbox');
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $pager = new DBPager('images', 'PHPWS_Image');
            $pager->setTemplate('Forms/image_grid.tpl');
            $limits[9] = 9;
            $limits[16] = 16;
            $limits[25] = 25;
            $operation = 'iop';
            $command = 'upload_image_form';
            $label = _('Add image');
        } elseif ($folder->ftype == DOCUMENT_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $pager = new DBPager('documents', 'PHPWS_Document');
            $pager->setTemplate('Forms/file_list.tpl');
            $limits[10] = 10;
            $limits[25] = 25;
            $limits[50] = 50;
            $operation = 'dop';
            $label = _('Add document');
            $command = 'upload_document_form';
            $pager->addSortHeader('downloaded', sprintf('<abbr title="%s">%s</abbr>', dgettext('filecabinet', 'Downloaded'), dgettext('filecabinet', 'DL')));
        } elseif ($folder->ftype = MULTIMEDIA_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $pager = new DBPager('multimedia', 'PHPWS_Multimedia');
            $pager->setTemplate('Forms/multimedia_grid.tpl');
            $limits[9] = 9;
            $limits[16] = 16;
            $limits[25] = 25;
            $label = _('Add media');
            $command = 'upload_multimedia_form';
            $operation = 'mop';
        }

        if (Current_User::allow('filecabinet', 'edit_folders', $folder->id, 'folder')) {
            if ($dir_write) {
                $links[] = $folder->uploadLink('button');
            }
            if ($folder->ftype == MULTIMEDIA_FOLDER) {
                //$links[] = $folder->rtmpLink();
                $salt = array('mop' => 'edit_rtmp', 'folder_id' => $folder->id);
                $authkey = \Current_User::getAuthKey(PHPWS_Text::saltArray($salt));
                $links[] = <<<EOF
<button class="btn btn-default show-modal" data-authkey="$authkey" data-command="edit_rtmp" data-operation="$operation" data-folder-id="$folder->id"><i class="fa fa-cloud"></i> Add RTMP video</button>
EOF;
            }
            //$links[] = $folder->editLink();
            $salt = array($operation => 'edit_folder', 'folder_id' => $folder->id);
            $authkey = \Current_User::getAuthKey(PHPWS_Text::saltArray($salt));
            $links[] = <<<EOF
<button class="btn btn-default show-modal" data-authkey="$authkey" data-command="edit_folder" data-operation="aop" data-folder-id="$folder->id"><i class="fa fa-edit"></i> Edit</button>
EOF;
        }

        if ($this->cabinet->panel) {
            $pagetags['BACK'] = PHPWS_Text::moduleLink('<i class="fa fa-reply"></i> ' .
                            dgettext('filecabinet', 'Back to folder list'), 'filecabinet', array('tab' => $this->cabinet->panel->getCurrentTab()), null, null, 'btn btn-default');
        }

        if (!empty($links)) {
            $pagetags['ADMIN_LINKS'] = implode(' ', $links);
        }

        $pagetags['MODAL'] = $this->getModal();

        $pagetags['ACTION_LABEL'] = dgettext('filecabinet', 'Action');

        $pager->setLimitList($limits);

        $pager->setSearch('file_name', 'title', 'description');
        $pager->addWhere('folder_id', $folder->id);
        $pager->setOrder('title', 'asc', true);
        $pager->setModule('filecabinet');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');
        $pager->addSortHeader('title', dgettext('filecabinet', 'Title'));
        $pager->addSortHeader('file_name', dgettext('filecabinet', 'File name'));
        $pager->addSortHeader('file_type', dgettext('filecabinet', 'File type'));
        $pager->addSortHeader('size', dgettext('filecabinet', 'Size'));

        $pager->setEmptyMessage(dgettext('filecabinet', 'Folder is empty.'));
        $this->cabinet->content = $pager->get();
    }

    public function pinFolder($key_id)
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

        $tpl['CANCEL'] = javascript('close_window', array('value' => dgettext('filecabinet', 'Cancel')));

        $this->cabinet->content = PHPWS_Template::process($tpl, 'filecabinet', 'Forms/pin_folder.tpl');
    }

    public function settings()
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

        $form->addCheckBox('autofloat', 1);
        $form->setMatch('autofloat', PHPWS_Settings::get('filecabinet', 'autofloat'));
        $form->setLabel('autofloat', dgettext('filecabinet', 'Float new images under 300px to the right of content'));

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

        $form->addCheck('allow_direct_links', 1);
        $form->setMatch('allow_direct_links', PHPWS_Settings::get('filecabinet', 'allow_direct_links'));
        $form->setLabel('allow_direct_links', dgettext('filecabinet', 'Allow direct links to documents'));

        $form->addCheck('force_thumbnail_dimensions', 1);
        $form->setMatch('force_thumbnail_dimensions', PHPWS_Settings::get('filecabinet', 'force_thumbnail_dimensions'));
        $form->setLabel('force_thumbnail_dimensions', dgettext('filecabinet', 'Force thumbnail dimensions on display'));

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

        $form->addRadioAssoc('jcaro_type', array(0 => dgettext('filecabinet', 'Horizontal'),
            1 => dgettext('filecabinet', 'Vertical')));
        $form->setMatch('jcaro_type', (int) PHPWS_Settings::get('filecabinet', 'vertical_folder'));

        $num = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8);
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

        $tpl['MAX_SYSTEM_SIZE'] = File_Common::humanReadable($sizes['system']);
        $tpl['MAX_FORM_SIZE'] = File_Common::humanReadable($sizes['form']);
        $tpl['ABSOLUTE_SIZE'] = File_Common::humanReadable($sizes['absolute']);

        if (Current_User::isDeity()) {
            $link = new PHPWS_Link(null, 'filecabinet', array('aop' => 'fix_document_dir'), true);
            $js = array('question' => dgettext('filecabinet', 'This process will update all of your document files with the current base directory.
Do not run this process unless you are sure it will fix download problems.
If you are sure, type Y-E-S below.'),
                'address' => $link->getAddress(),
                'value_name' => 'confirm',
                'link' => dgettext('filecabinet', 'Reindex document directories')
            );
            $tpl['FIX_DIRECTORIES'] = javascript('prompt', $js);
        }

        return PHPWS_Template::process($tpl, 'filecabinet', 'Forms/settings.tpl');
    }

    public function classifyFile($files)
    {
        $tpl = null;
        $this->cabinet->title = dgettext('filecabinet', 'Classify Files');
        $classify_dir = $this->cabinet->getClassifyDir();

        if (empty($classify_dir) || !is_dir($classify_dir)) {
            $this->cabinet->content = dgettext('filecabinet', 'Unable to locate the classify directory. Please check your File Cabinet settings, configuration file and directory permissions.');
            return;
        }

        if (!is_array($files)) {
            $files = array($files);
        }

        // image folders
        $image_folders = Cabinet::listFolders(IMAGE_FOLDER, true);

        // document folders
        $document_folders = Cabinet::listFolders(DOCUMENT_FOLDER, true);

        // multimedia folders
        $multimedia_folders = Cabinet::listFolders(MULTIMEDIA_FOLDER, true);

        $count = 0;

        $image_types = $this->cabinet->getAllowedTypes('image');
        $document_types = $this->cabinet->getAllowedTypes('document');
        $media_types = $this->cabinet->getAllowedTypes('media');

        foreach ($files as $file) {
            if (!is_file($classify_dir . $file) || !PHPWS_File::checkMimeType($classify_dir . $file) || !$this->cabinet->fileTypeAllowed($file)) {
                continue;
            }

            $form = new PHPWS_Form('file_form_' . $count);
            $ext = PHPWS_File::getFileExtension($file);
            if (in_array($ext, $image_types)) {
                $folders = & $image_folders;
            } elseif (in_array($ext, $media_types)) {
                $folders = & $multimedia_folders;
            } elseif (in_array($ext, $document_types)) {
                $folders = & $document_folders;
            } else {
                continue;
            }

            $form->addSelect("folder[$count]", $folders);
            $form->setTag("folder[$count]", 'folder');
            $form->setLabel("folder[$count]", dgettext('filecabinet', 'Folder'));

            $form->addText("file_title[$count]", $file);
            $form->setLabel("file_title[$count]", dgettext('filecabinet', 'Title'));
            $form->setTag("file_title[$count]", 'file_title');
            $form->setSize("file_title[$count]", 40);

            $form->addTextarea("file_description[$count]");
            $form->setLabel("file_description[$count]", dgettext('filecabinet', 'Description'));
            $form->setTag("file_description[$count]", 'file_description');

            $form->addSubmit('submit', dgettext('filecabinet', 'Classify files'));
            $subtpl = $form->getTemplate();
            $subtpl['HIDDEN'] = sprintf('<input type="hidden" name="file_count[%s]" value="%s" />', $count, $file);
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

        if (!empty($tpl)) {
            $form_template = $form->getTemplate(true, true, $tpl);
            $this->cabinet->content = PHPWS_Template::process($form_template, 'filecabinet', 'Forms/classify_file.tpl');
        } else {
            $this->cabinet->content = dgettext('filecabinet', 'Unable to classify files.');
        }
    }

    public function fileTypes()
    {
        $known_images = null;
        $known_media = null;
        $known_documents = null;
        include PHPWS_SOURCE_DIR . 'mod/filecabinet/inc/known_types.php';

        $image_types = explode(',', PHPWS_Settings::get('filecabinet', 'image_files'));
        $media_types = explode(',', PHPWS_Settings::get('filecabinet', 'media_files'));
        $doc_types = explode(',', PHPWS_Settings::get('filecabinet', 'document_files'));

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

        return PHPWS_Template::process($tpl, 'filecabinet', 'Forms/allowed_types.tpl');
    }

    public function sortType($known, &$all_file_types)
    {
        foreach ($known as $type) {
            if (isset($all_file_types[$type])) {
                $file_info = $all_file_types[$type];
            } else {
                $file_info = null;
            }

            if (empty($file_info) || (isset($file_info['base']) && @$file_info['base'] != $type)) {
                continue;
            }
            $checks[$type] = $file_info['vb'];
        }
        asort($checks);
        return $checks;
    }

    public function saveSettings()
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

        PHPWS_Settings::set('filecabinet', 'classify_file_type', (int) isset($_POST['classify_file_type']));

        $max_file_upload = preg_replace('/\D/', '', ini_get('upload_max_filesize'));

        if (empty($_POST['max_image_size'])) {
            $errors[] = dgettext('filecabinet', 'You must set a maximum image file size.');
        } else {
            $max_image_size = (int) $_POST['max_image_size'];
            if (($max_image_size / 1000000) > ((int) $max_file_upload)) {
                $errors[] = sprintf(dgettext('filecabinet', 'Your maximum image size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_image_size', $max_image_size);
            }
        }

        if (empty($_POST['max_document_size'])) {
            $errors[] = dgettext('filecabinet', 'You must set a maximum document file size.');
        } else {
            $max_document_size = (int) $_POST['max_document_size'];
            if (($max_document_size / 1000000) > (int) $max_file_upload) {
                $errors[] = sprintf(dgettext('filecabinet', 'Your maximum document size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_document_size', $max_document_size);
            }
        }

        if (empty($_POST['max_multimedia_size'])) {
            $errors[] = dgettext('filecabinet', 'You must set a maximum multimedia file size.');
        } else {
            $max_multimedia_size = (int) $_POST['max_multimedia_size'];
            if (($max_multimedia_size / 1000000) > (int) $max_file_upload) {
                $errors[] = sprintf(dgettext('filecabinet', 'Your maximum multimedia size exceeds the server limit of %sMB.'), $max_file_upload);
            } else {
                PHPWS_Settings::set('filecabinet', 'max_multimedia_size', $max_multimedia_size);
            }
        }

        if (empty($_POST['max_pinned_images'])) {
            PHPWS_Settings::set('filecabinet', 'max_pinned_images', 0);
        } else {
            PHPWS_Settings::set('filecabinet', 'max_pinned_images', (int) $_POST['max_pinned_images']);
        }

        $threshold = (int) $_POST['crop_threshold'];
        if ($threshold < 0) {
            PHPWS_Settings::set('filecabinet', 'crop_threshold', 0);
        } else {
            PHPWS_Settings::set('filecabinet', 'crop_threshold', $threshold);
        }

        PHPWS_Settings::set('filecabinet', 'force_thumbnail_dimensions', (int) isset($_POST['force_thumbnail_dimensions']));

        if (empty($_POST['max_pinned_documents'])) {
            PHPWS_Settings::set('filecabinet', 'max_pinned_documents', 0);
        } else {
            PHPWS_Settings::set('filecabinet', 'max_pinned_documents', (int) $_POST['max_pinned_documents']);
        }

        PHPWS_Settings::set('filecabinet', 'autofloat', (int) isset($_POST['autofloat']));
        PHPWS_Settings::set('filecabinet', 'use_ffmpeg', (int) isset($_POST['use_ffmpeg']));
        PHPWS_Settings::set('filecabinet', 'auto_link_parent', (int) isset($_POST['auto_link_parent']));
        PHPWS_Settings::set('filecabinet', 'caption_images', (int) isset($_POST['caption_images']));
        PHPWS_Settings::set('filecabinet', 'popup_image_navigation', (int) isset($_POST['popup_image_navigation']));
        PHPWS_Settings::set('filecabinet', 'allow_direct_links', (int) isset($_POST['allow_direct_links']));

        if (empty($_POST['max_thumbnail_size'])) {
            PHPWS_Settings::set('filecabinet', 'max_thumbnail_size', 100);
        } else {
            $tn_size = (int) $_POST['max_thumbnail_size'];
            if ($tn_size < 30) {
                $errors[] = dgettext('filecabinet', 'Thumbnails must be over 30px in size.');
            } elseif ($tn_size > 999) {
                $errors[] = dgettext('filecabinet', 'Thumbnail size is too large.');
            } else {
                PHPWS_Settings::set('filecabinet', 'max_thumbnail_size', $tn_size);
            }
        }

        $ffmpeg_dir = trim(strip_tags($_POST['ffmpeg_directory']));
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
                } elseif (!is_writable($classify_dir)) {
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

    public function postAllowedFiles()
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
