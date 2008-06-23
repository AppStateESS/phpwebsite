<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
class FC_File_Manager {
    var $module         = null;
    var $file_assoc     = null;
    var $itemname       = null;
    var $folder_type    = 0;
    var $current_folder = 0;
    var $max_width      = 0;
    var $max_height     = 0;
    var $lock_type      = null;
    var $mod_limit      = false;
    /**
     * If true, user must resize to fit image limits
     */
    var $force_resize   = false;
    /**
     * id to session holding manager information
     */
    var $session_id  = null;

    function FC_File_Manager($module, $itemname, $file_id=0)
    {
        $this->max_width = $this->max_height = PHPWS_Settings::get('filecabinet', 'max_image_dimension');
        $this->module     = & $module;
        $this->itemname   = & $itemname;
        $this->session_id = md5($this->module . $this->itemname);
        $this->loadFileAssoc($file_id);
        $this->lock_type = @$_SESSION['FM_Type_Lock'][$this->session_id];
    }

    /*
     * Expects 'fop' command to direct action.
     */
    function admin()
    {
        /**
         * Folder permissions needed
         */
        switch($_REQUEST['fop']) {
        case 'open_file_manager':
            return $this->openFileManager();
            break;

        case 'fm_folders':
            return $this->folderView();
            break;

        case 'fm_fld_contents':
            return $this->folderContentView();
            break;

        case 'pick_file':
            $this->pickFile();
            break;

        case 'resize_pick':
            $this->resizePick();
            break;
        }
    }

    function imageOnly($folder=true, $random=true)
    {
        $locks = array(FC_IMAGE);

        if ($folder) {
            $locks[] = FC_IMAGE_FOLDER;
        }

        if ($random) {
            $locks[] = FC_IMAGE_RANDOM;
        }

        $this->lock_type = $locks;
        $_SESSION['FM_Type_Lock'][$this->session_id] = $this->lock_type;
    }

    function documentOnly($folder=true)
    {
        $locks = array(FC_DOCUMENT);
        if ($folder) {
            $locks[] = FC_DOCUMENT_FOLDER;
        }

        $this->lock_type = $locks;
        $_SESSION['FM_Type_Lock'][$this->session_id] = $this->lock_type;
    }

    function mediaOnly()
    {
        $this->lock_type = array(FC_MEDIA);
        $_SESSION['FM_Type_Lock'][$this->session_id] = $this->lock_type;
    }

    function allFiles()
    {
        $this->lock_type = null;
        $_SESSION['FM_Type_Lock'][$this->session_id] = null;
        unset($_SESSION['FM_Type_Lock'][$this->session_id]);
    }

    function forceResize($force=true)
    {
        $this->force_resize = (bool)$force;
    }

    function clearLock()
    {
        if (!isset($_SESSION['FM_Type_Lock'])) {
            return;
        }
        unset($_SESSION['FM_Type_Lock'][$this->session_id]);
    }

    function loadFileAssoc($file_id)
    {
        PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
        $this->file_assoc = new FC_File_Assoc($file_id);
    }

    function setItemName($itemname)
    {
        $this->itemname = $itemname;
    }

    function setMaxSize($size)
    {
        $this->max_size = (int)$size;
    }

    function setMaxWidth($width)
    {
        $this->max_width = (int)$width;
    }

    function setMaxHeight($height)
    {
        $this->max_height = (int)$height;
    }

    function placeHolder()
    {
        if (!$this->lock_type) {
            if (Current_User::allow('filecabinet')) {
                $link = sprintf('<img src="%s" title="%s" />',
                               FC_PLACEHOLDER,
                               dgettext('filecabinet', 'Add an image, media, or document file.')
                               );
            } else {
                return sprintf('<img src="%s" title="%s" />',
                               FC_NO_RIGHTS,
                               dgettext('filecabinet', 'Add an image, media, or document file.')
                               );
            }
        }

        switch (1) {
        case in_array(FC_IMAGE, $this->lock_type):
            $link = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/image200.png" title="%s"/>',
                                dgettext('filecabinet', 'Add a image, an image folder, or a randomly changing image'));
            break;

        case in_array(FC_DOCUMENT, $this->lock_type):
            $link =  sprintf('<img src="images/mod/filecabinet/file_manager/file_type/document200.png" title="%s"/>',
                                    dgettext('filecabinet', 'Add a document or a document folder'));
            break;
        case in_array(FC_MEDIA, $this->lock_type):
            $link =  sprintf('<img src="images/mod/filecabinet/file_manager/file_type/media200.png" title="%s"/>',
                                dgettext('filecabinet', 'Add a video or sound file'));
            break;
        }
        return $this->editLink($link);
    }

    function get()
    {
        Layout::addStyle('filecabinet', 'file_view.css');
        Layout::addStyle('filecabinet');

        /**
         * No label, show default graphic
         */
        if (!$this->file_assoc->id) {
            $tpl['FILE'] = $this->placeHolder();
            $tpl['FILE_ID'] = 0;
        } else {
            $tag = $this->file_assoc->getTag();
            if (empty($tag)) {
                $tpl['FILE'] = $this->placeHolder();
                $tpl['FILE_ID'] = 0;
            } else {
                $tpl['FILE'] = $tag;
                $tpl['FILE_ID'] = $this->file_assoc->id;
            }
        }

        // Copy of image manager's getClearLink
        $tpl['PLACEHOLDER'] = 'pl_' . $this->session_id;
        $tpl['HIDDEN_ID']   = 'h_' . $this->session_id;
        if (Current_User::allow('filecabinet')) {
            $tpl['CLEAR_LINK']  = $this->clearLink();
            $tpl['EDIT_LINK']   = $this->editLink();
            $tpl['LINK_ID']     = 'l_' . $this->session_id;
        }
        $tpl['ITEMNAME']    = $this->itemname;
        $this->file_assoc->loadCarousel();
        return PHPWS_Template::process($tpl, 'filecabinet', 'file_manager/placeholder.tpl');
    }

    // Copy of image manager's getClearLink
    function clearLink()
    {
        $js_vars['label']    = dgettext('filecabinet', 'Clear file');
        $js_vars['id']       = $this->session_id;
        $js_vars['img']      = str_replace("'", "\'", $this->placeHolder());
        return javascript('modules/filecabinet/clear_file', $js_vars);
    }

    function linkInfo($dimensions=true)
    {
        $info['cm']   = $this->module;
        $info['itn']  = $this->itemname;
        $info['fid']  = $this->file_assoc->id;
        $info['fr']   = $this->force_resize ? 1 : 0;
        $info['ml']   = $this->mod_limit ? 1 : 0;

        if ($dimensions) {
            if ($this->max_width) {
                $info['mw'] = & $this->max_width;
            }
            
            if ($this->max_height) {
                $info['mh'] = & $this->max_height;
            }
        }

        if ($this->folder_type) {
            $info['ftype'] = $this->folder_type;
        }
        return $info;
    }

    /**
     * Returns a javascript pop-up link to pick a file
     */
    function editLink($label=null)
    {
        $js['width']   = 800;
        $js['height']  = 600;
        if (empty($label)) {
            $js['label']   = dgettext('filecabinet', 'Edit file');
        } else {
            $js['label'] = $label;
        }
        $js['title']   = dgettext('filecabinet', 'Edit the current file');

        $add_vars = $this->linkInfo();
        $add_vars['fop']  = 'open_file_manager';

        $js['address'] = PHPWS_Text::linkAddress('filecabinet', $add_vars, true);
        $js['window_name'] = $this->session_id;
        return javascript('open_window', $js);
    }

    function maxImageWidth($width)
    {
        $this->max_width = (int)$width;
    }

    function maxImageHeight($height)
    {
        $this->max_height = (int)$height;
    }

    /**
     * Introduction view with three types of files
     */
    function startView()
    {
        Layout::addStyle('filecabinet');
        $document_img = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/document200.png" title="%s"/>',
                                dgettext('filecabinet', 'Add a document or a document folder'));

        $image_img    = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/image200.png" title="%s"/>',
                                dgettext('filecabinet', 'Add a image, an image folder, or a randomly changing image'));

        $media_img    = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/media200.png" title="%s"/>',
                                dgettext('filecabinet', 'Add a video or sound file'));

        $vars = $this->linkInfo();
        $vars['fop']   = 'fm_folders';
        $vars['ftype'] = DOCUMENT_FOLDER;
        $document = PHPWS_Text::secureLink($document_img, 'filecabinet', $vars);

        $vars['ftype'] = IMAGE_FOLDER;
        $image    = PHPWS_Text::secureLink($image_img, 'filecabinet', $vars);

        $vars['ftype'] = MULTIMEDIA_FOLDER;
        $media    = PHPWS_Text::secureLink($media_img, 'filecabinet', $vars);

        $tpl['DOCUMENT_ICON'] = & $document;
        $tpl['MEDIA_ICON']    = & $media;
        $tpl['IMAGE_ICON']    = & $image;

        $tpl['DOC_LABEL'] = dgettext('filecabinet', 'Documents');
        $tpl['IMG_LABEL'] = dgettext('filecabinet', 'Images');
        $tpl['MED_LABEL'] = dgettext('filecabinet', 'Media');

        $tpl['INSTRUCTION'] = dgettext('filecabinet', 'Choose the type of file you wish to add');
        $tpl['CLOSE'] = javascript('close_window');

        return PHPWS_Template::process($tpl, 'filecabinet', 'file_manager/start_view.tpl');
    }


    function folderIcons(&$tpl)
    {
        $vars = $this->linkInfo();
        $vars['fop']   = 'fm_folders';

        if (!$this->lock_type || in_array(FC_DOCUMENT, $this->lock_type)) {
            if ($this->folder_type == DOCUMENT_FOLDER) {
                $icon_name = 'document80.png';
            } else {
                $icon_name = 'document80_bw.png';
            }
            $document_img = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/%s" title="%s"/>',
                                    $icon_name,
                                    dgettext('filecabinet', 'View document folders'));
     
            $vars['ftype'] = DOCUMENT_FOLDER;
            $document = PHPWS_Text::secureLink($document_img, 'filecabinet', $vars);
            $tpl['DOCUMENT_ICON'] = & $document;
        }

        if (!$this->lock_type || in_array(FC_IMAGE, $this->lock_type)) {
            if ($this->folder_type == IMAGE_FOLDER) {
                $icon_name = 'image80.png';
            } else {
                $icon_name = 'image80_bw.png';
            }
            $image_img    = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/%s" title="%s"/>',
                                    $icon_name,
                                    dgettext('filecabinet', 'View image folders'));

            $vars['ftype'] = IMAGE_FOLDER;
            $image    = PHPWS_Text::secureLink($image_img, 'filecabinet', $vars);
            $tpl['IMAGE_ICON']    = & $image;
        }

        if (!$this->lock_type || in_array(FC_MEDIA, $this->lock_type)) {
            if ($this->folder_type == MULTIMEDIA_FOLDER) {
                $icon_name = 'media80.png';
            } else {
                $icon_name = 'media80_bw.png';
            }
            $media_img    = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/%s" title="%s"/>',
                                    $icon_name,
                                    dgettext('filecabinet', 'View media folders'));
            
            $vars['ftype'] = MULTIMEDIA_FOLDER;
            $media    = PHPWS_Text::secureLink($media_img, 'filecabinet', $vars);
            $tpl['MEDIA_ICON']    = & $media;
        }
    }

    /**
     * View of folders per media
     */
    function folderView()
    {
        Layout::addStyle('filecabinet');

        $tpl = array();

        $this->folderIcons($tpl);

        $tpl['CLOSE'] = javascript('close_window');

        if (Current_User::allow('filecabinet', 'edit_folders') && Current_User::isUnrestricted('filecabinet')) {
            $folder = new Folder;
            $folder->ftype = $this->folder_type;

            if ($this->mod_limit) {
                $tpl['ADD_FOLDER'] = $folder->editLink('button', $this->module);
            } else {
                $tpl['ADD_FOLDER'] = $folder->editLink('button');
            }
        }

        $db = new PHPWS_DB('folders');
        $db->addWhere('module_created', $this->module, null, null, 'mod_limit');

        if (!$this->mod_limit) {
            $db->addWhere('module_created', null, null, 'or', 'mod_limit');
        }

        $db->addOrder('title');
        $db->addWhere('ftype', $this->folder_type);
        $folders = $db->getObjects('Folder');
        if (!empty($folders)) {
            $fvars = $this->linkInfo();
            $fvars['fop'] = 'fm_fld_contents';
            foreach ($folders as $folder) {
                $fvars['folder_id'] = $folder->id;
                $row['ADDRESS'] = PHPWS_Text::linkAddress('filecabinet', $fvars, true);
                $row['ICON'] = '<img src="images/mod/filecabinet/file_manager/folder.png" />';
                $row['TITLE'] = &$folder->title;
                $tpl['folder-list'][] = $row;
            }
        }
        $tpl['FOLDER_TITLE'] = 'folder view';
        return PHPWS_Template::process($tpl, 'filecabinet', 'file_manager/folder_view.tpl');
    }

    /**
     * View of files in current folder
     */
    function folderContentView()
    {
        javascript('jquery');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        javascript('confirm'); // needed for deletion

        Layout::addStyle('filecabinet');
        if (isset($_GET['folder_id'])) {
            $this->current_folder = new Folder($_GET['folder_id']);
        }

        if (empty($this->current_folder) || empty($this->folder_type)) {
            javascript('alert', array('content' => dgettext('filecabinet', 'Problem with opening browser page. Closing File Manager window.')));
            javascript('close_refresh', array('timeout'=>3, 'refresh'=>0));
            return;
        }

        $tpl = array();
        $this->folderIcons($tpl);

        if (Current_User::allow('filecabinet', 'edit_folders')) {
            $tpl['FOLDER_TITLE'] = $this->current_folder->editLink('title', $this->current_folder->module_created);
        } else {
            $tpl['FOLDER_TITLE'] = & $this->current_folder->title;
        }

        $img_dir = 'images/mod/filecabinet/file_manager/';
        $image_string = '<img src="%s" title="%s" alt="%s" />';
        $link_info = $this->linkInfo();

        switch ($this->folder_type) {
        case IMAGE_FOLDER:
            $js = $link_info;
            $js['authkey'] = Current_User::getAuthKey();
            $js['failure_message'] = dgettext('filecabinet', 'Unable to resize image.');
            $js['confirmation'] = sprintf(dgettext('filecabinet', 'This image is larger than the %s x %s limit. Do you want to resize the image to fit?'),
                                          $this->max_width,
                                          $this->max_height);
            
            javascript('modules/filecabinet/pick_file', $js);
            $db = new PHPWS_DB('images');
            $class_name = 'PHPWS_Image';
            $file_type  = FC_IMAGE;
            $altvars    = $link_info;

            $img1       = 'folder_random.png';
            $img2       = 'thumbnails.png';
            $img1_alt   = dgettext('filecabinet', 'Random image icon');
            $img2_alt   = dgettext('filecabinet', 'Thumbnail icon');

            if ($this->current_folder->public_folder) {
                $altvars['id']        = $this->current_folder->id;
                $altvars['fop']       = 'pick_file';
                $altvars['file_type'] = FC_IMAGE_RANDOM;
                $not_allowed = dgettext('filecabinet', 'Action not allowed');

                if (!$this->lock_type || in_array(FC_IMAGE_RANDOM, $this->lock_type)) {
                    $img1_title = dgettext('filecabinet', 'Show a random image from this folder');
                    $image1 = sprintf($image_string, $img_dir . $img1, $img1_title, $img1_alt);
                    $tpl['ALT1'] = PHPWS_Text::secureLink($image1, 'filecabinet', $altvars);
                
                    if ($this->file_assoc->file_type == FC_IMAGE_RANDOM && $this->current_folder->id == $this->file_assoc->file_id) {
                        $tpl['ALT_HIGH1'] = ' alt-high';
                    }
                } else {
                    $image1 = sprintf($image_string, $img_dir . $img1, $not_allowed, $img1_alt);
                    $tpl['ALT1'] = $image1;
                    $tpl['ALT_HIGH1'] = ' no-use';
                }

                if (!$this->lock_type || in_array(FC_IMAGE_FOLDER, $this->lock_type)) {
                    $altvars['file_type'] = FC_IMAGE_FOLDER;
                    if ($this->file_assoc->file_type == FC_IMAGE_FOLDER) {
                        $tpl['ALT_HIGH2'] = ' alt-high';
                    }

                    $img2_title = dgettext('filecabinet', 'Show block of thumbnails');
                    $image2 = sprintf($image_string, $img_dir . $img2, $img2_title, $img2_alt);
                    $tpl['ALT2'] = PHPWS_Text::secureLink($image2, 'filecabinet', $altvars);
                } else {
                    $image2 = sprintf($image_string, $img_dir . $img2, $not_allowed, $img2_alt);
                    $tpl['ALT2'] = $image2;
                    $tpl['ALT_HIGH2'] = ' no-use';
                }
            } else {
                $not_allowed = dgettext('filecabinet', 'Action not allowed - private folder');
                $image1 = sprintf($image_string, $img_dir . $img1, $not_allowed, $img1_alt);
                $image2 = sprintf($image_string, $img_dir . $img2, $not_allowed, $img2_alt);
                $tpl['ALT1'] = $image1;
                $tpl['ALT_HIGH1'] = ' no-use';
                $tpl['ALT2'] = $image2;
                $tpl['ALT_HIGH2'] = ' no-use';
            }
            break;

        case DOCUMENT_FOLDER:
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $db = new PHPWS_DB('documents');
            $class_name = 'PHPWS_Document';
            $file_type = FC_DOCUMENT;

            $img1     = 'all_files.png';
            $img1_alt = dgettext('filecabinet', 'All files icon');

            if ($this->current_folder->public_folder) {
                if (!$this->lock_type || in_array(FC_DOCUMENT_FOLDER, $this->lock_type)) {
                    $altvars = $link_info;
                    $altvars['id']        = $this->current_folder->id;
                    $altvars['fop']       = 'pick_file';
                    $altvars['file_type'] = FC_DOCUMENT_FOLDER;
                    
                    $img1_title = dgettext('filecabinet', 'Show all files in the folder');
                    $image1 = sprintf($image_string, $img_dir . $img1, $img1_title, $img1_alt);
                    
                    $tpl['ALT1'] = PHPWS_Text::secureLink($image1, 'filecabinet', $altvars);
                    
                    if ($this->file_assoc->file_type == FC_DOCUMENT_FOLDER && $this->current_folder->id == $this->file_assoc->file_id) {
                        $tpl['ALT_HIGH1'] = ' alt-high';
                    }
                } else {
                    $not_allowed = dgettext('filecabinet', 'Action not allowed');
                    $image1 = sprintf($image_string, $img_dir . $img1, $not_allowed, $img1_alt);
                    $tpl['ALT1'] = $image1;
                    $tpl['ALT_HIGH1'] = ' no-use';
                }
            } else {
                $not_allowed = dgettext('filecabinet', 'Action not allowed - private folder');
                $image1 = sprintf($image_string, $img_dir . $img1, $not_allowed, $img1_alt);
                $tpl['ALT1'] = $image1;
                $tpl['ALT_HIGH1'] = ' no-use';
            }
            break;

        case MULTIMEDIA_FOLDER:
            $js = $link_info;
            $js['authkey'] = Current_User::getAuthKey();
            $js['failure_message'] = dgettext('filecabinet', 'Unable to resize media.');
            $js['confirmation'] = sprintf(dgettext('filecabinet', 'This media is larger than the %s x %s limit. Do you want to resize the media to fit?'),
                                          $this->max_width,
                                          $this->max_height);

            javascript('modules/filecabinet/pick_file', $js);
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $db = new PHPWS_DB('multimedia');
            $class_name = 'PHPWS_Multimedia';
            $file_type = FC_MEDIA;
            $tpl['ADD_EMBED'] = $this->current_folder->embedLink(true);
            break;
        }

        $db->addWhere('folder_id', $this->current_folder->id);
        $db->addOrder('title');
        $items = $db->getObjects($class_name);

        if ($items) {
            foreach ($items as $item) {
                $stpl = $item->managerTpl($this);
                $tpl['items'][] = $stpl;
            }
        } else {
            $not_allowed = dgettext('filecabinet', 'No files in folder');
            if (isset($tpl['ALT1'])) {

                $image1 = sprintf($image_string, $img_dir . $img1, $not_allowed, $img1_alt);
                $tpl['ALT1'] = $image1;
                $tpl['ALT_HIGH1'] = ' no-use';
            }

            if (isset($tpl['ALT2'])) {
                $image2 = sprintf($image_string, $img_dir . $img2, $not_allowed, $img2_alt);
                $tpl['ALT2'] = $image2;
                $tpl['ALT_HIGH2'] = ' no-use';
            }
        }
        if (Current_User::allow('filecabinet', 'edit_folders', $this->current_folder->id, 'folder')) {
            $tpl['ADD_FILE'] = $this->current_folder->uploadLink(true);
        }
        $tpl['CLOSE'] = javascript('close_window');
        return PHPWS_Template::process($tpl, 'filecabinet', 'file_manager/folder_content_view.tpl');
    }

    function openFileManager()
    {
        /**
         * File has an id, show file
         */
        if ($this->file_assoc->id) {
            $this->folder_type = $this->file_assoc->getFolderType();
            $this->current_folder = $this->file_assoc->getFolder();

            if (empty($this->current_folder)) {
                $this->file_assoc = new FC_File_Assoc;
                return $this->startView();
            }
            return $this->folderContentView();
        } elseif ($this->lock_type) {
            switch (1) {
            case in_array(FC_IMAGE, $this->lock_type):
                $this->folder_type = IMAGE_FOLDER;
                return $this->folderView();

            case in_array(FC_MEDIA, $this->lock_type):
                $this->folder_type = MULTIMEDIA_FOLDER;
                return $this->folderView();

            case in_array(FC_DOCUMENT, $this->lock_type):
                $this->folder_type = DOCUMENT_FOLDER;
                return $this->folderView();
            }
        } else {
            return $this->startView();
        }
    }

    function pickFile()
    {
        $file = $this->getFileAssoc($_REQUEST['file_type'], $_REQUEST['id'], true);

        if ($file) {
            $vars['id']      = $this->session_id;
            $vars['data']    = $this->jsReady($file->getTag(true));
            $vars['new_id']  = $file->id;
            javascript('modules/filecabinet/update_file', $vars);
        } else {
            exit(dgettext('filecabinet', 'An error occurred. Please check your logs.'));
        }
    }

    function jsReady($data)
    {
        $data = htmlentities($data, ENT_QUOTES, 'UTF-8');
        $data = preg_replace("/\n/", '\\n', $data);
        return $data;
    }


    function getFileAssoc($file_type, $id, $update=true)
    {
        $file_assoc = new FC_File_Assoc;
        $cropped = (int)$file_type == FC_IMAGE_CROP;

        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('file_type', (int)$file_type);
        if ($file_type == FC_IMAGE_RESIZE || $file_type == FC_IMAGE_CROP) {
            $db->addWhere('width', $this->max_width);
            $db->addWhere('height', $this->max_height);
            $db->addWhere('cropped', $cropped);
        }
        $db->addWhere('file_id', (int)$id);

        $result = $db->loadObject($file_assoc);

        if ($result) {
            if (PHPWS_Error::logIfError($result)) {
                return false;
            } elseif (!$update) {
                return $file_assoc;
            }
        }

        $file_assoc->file_type = & $file_type;
        $file_assoc->file_id   = $id;

        if ($file_assoc->file_type == FC_IMAGE_RESIZE || $file_assoc->file_type == FC_IMAGE_CROP) {
            $file_assoc->width   = $this->max_width;
            $file_assoc->height  = $this->max_height;
            $file_assoc->cropped = $cropped;

            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $image = new PHPWS_Image($id);
            if (!$dst = $image->makeResizePath()) {
                return false;
            }

            if ($cropped) {
                $resize_file_name = sprintf('%sx%s_crop.%s', $this->max_width, $this->max_height, $image->getExtension());
            } else {
                $resize_file_name = sprintf('%sx%s.%s', $this->max_width, $this->max_height, $image->getExtension());
            }

            if (!$image->resize($dst . $resize_file_name, $this->max_width, $this->max_height, $cropped)) {
                return false;
            }

            $file_assoc->resize = & $resize_file_name;
        } elseif ($file_assoc->file_type == FC_MEDIA_RESIZE) {
            $file_assoc->width = $this->max_width;
            $file_assoc->height = $this->max_height;
        }

        $file_assoc->save();

        $file_assoc->loadSource();
        return $file_assoc;
    }

    /**
     * Limits folder selection by module.
     */ 
    function moduleLimit($limit=true)
    {
        $this->mod_limit = (bool)$limit;
    }

}

?>