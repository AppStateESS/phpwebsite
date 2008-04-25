<?php

/**
 * Main class for the File Cabinet
 *
 * File Cabinet is meant (for those devs that utilize it)
 * as a central place to administrate all the files uploaded to the site.
 *
 * @version $Id$
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('filecabinet', 'Folder.php');
PHPWS_Core::requireConfig('filecabinet');

class Cabinet {
    var $title          = null;
    var $message        = null;
    var $content        = null;
    var $forms          = null;
    var $panel          = null;
    var $folder         = null;
    var $image_mgr      = null;
    var $document_mgr   = null;
    var $multimedia_mgr = null;
    var $file_manager   = null;


    /**
     * File manager administrative options.
     */
    function fmAdmin()
    {
        if (!$this->authenticate()) {
            Current_User::disallow();
        }

        Layout::cacheOff();
        if ($this->loadFileManager()) {
            Layout::nakedDisplay($this->file_manager->admin(), null, false);
        } else {
            Layout::nakedDisplay(javascript('close_refresh'), null, false);
        }
    }

    /**
     * Document manager administrative options.
     */
    function dmAdmin()
    {
        if (!$this->authenticate()) {
            Current_User::disallow();
        }
        Layout::cacheOff();
        $this->loadDocumentManager();
        Layout::nakedDisplay($this->document_mgr->admin());
    }

    /**
     * Image manager administrative options.
     */
    function imAdmin()
    {
        if (!$this->authenticate()) {
            Current_User::disallow();
        }
        Layout::cacheOff();
        $this->loadImageManager();
        Layout::nakedDisplay($this->image_mgr->admin());
    }

    /**
     * Multimedia manager administrative options.
     */
    function mmAdmin()
    {
        if (!$this->authenticate()) {
            Current_User::disallow();
        }
        Layout::cacheOff();
        $this->loadMultimediaManager();
        Layout::nakedDisplay($this->multimedia_mgr->admin());
    }


    /**
     * Loads the file manager object into the cabinet variable.
     * Attempts to pull a current sessioned object if available
     */
    function loadFileManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'File_Manager.php');

        if (!@$module = $_GET['cm']) {
            return false;
        }

        if (!@$itemname = $_GET['itn']) {
            return false;
        }

        $this->file_manager = new FC_File_Manager($module, $itemname, $_GET['fid']);
        if (isset($_GET['mw'])) {
            $this->file_manager->max_width = (int)$_GET['mw'];
        }

        if (isset($_GET['mh'])) {
            $this->file_manager->max_height = (int)$_GET['mh'];
        }

        if (isset($_GET['ftype'])) {
            $this->file_manager->folder_type = (int)$_GET['ftype'];
        }

        if (isset($_GET['fr'])) {
            $this->file_manager->force_resize = (bool)$_GET['fr'];
        }

        if (isset($_GET['ml'])) {
            $this->file_manager->mod_limit = (bool)$_GET['ml'];
        }

        return true;
    }

    /**
     * Handles admin functions outside of file manager.
     * Expects an 'aop' command.
     */
    function admin()
    {
        $javascript = false; // if true, sends to nakedDisplay
        
        $this->loadPanel();

        if (isset($_REQUEST['aop'])) {
            $aop = $_REQUEST['aop'];
        } else {
            $aop = $this->panel->getCurrentTab();
        }

        if (!Current_User::isLogged()) {
            Current_User::disallow();
            return;
        }

        if ( ($aop != 'edit_image' && $aop != 'get_images') && !Current_User::allow('filecabinet') ){
            Current_User::disallow();
            return;
        }

        // Requires an unrestricted user
        switch ($aop) {
        case 'pin_folder':
        case 'delete_folder':
        case 'unpin':
            if (Current_User::isRestricted('filecabinet')) {
                Current_User::disallow();
            }
        }

        switch ($aop) {
            /** File manager functions **/
            /** end file manager functions **/

        case 'image':
            $this->panel->setCurrentTab('image');
            $this->title = dgettext('filecabinet', 'Image folders');
            $this->loadForms();
            $this->forms->getFolders(IMAGE_FOLDER);
            break;

        case 'multimedia':
            $this->panel->setCurrentTab('multimedia');
            $this->title = dgettext('filecabinet', 'Multimedia folders');
            $this->loadForms();
            $this->forms->getFolders(MULTIMEDIA_FOLDER);
            break;

        case 'add_folder':
            if (!Current_User::allow('filecabinet', 'edit_folders',null, null, true)) {
                Current_User::disallow();
            }
            $javascript = true;
            $this->loadFolder();
            $this->addFolder();
            break;

        case 'pin_folder':
            if (!Current_User::authorized('filecabinet', 'edit_folders')) {
                Current_User::disallow();
            }

            $javascript = true;
            $this->pinFolder();
            javascript('close_refresh');
            break;

        case 'classify':
            if (!Current_User::isDeity()) {
                Current_User::errorPage();
            }
            $this->loadForms();
            $this->forms->classifyFileList();
            break;

        case 'classify_file':
            if (!Current_User::isDeity()) {
                Current_User::errorPage();
            }

            $this->loadForms();
            if (!empty($_POST['file_list'])) {
                $this->forms->classifyFile($_POST['file_list']);
            } elseif (isset($_GET['file'])) {
                $this->forms->classifyFile($_GET['file']);
            } else {
                $this->forms->classifyFileList();
            }
            break;

        case 'post_classifications':
            if (!Current_User::isDeity()) {
                Current_User::errorPage();
            }

            $result = $this->classifyFiles();
            if (is_array($result)) {
                $this->message = implode('<br />', $result);
            }
            $this->loadForms();
            $this->forms->classifyFileList();
            break;

        case 'unpin':
            if (!Current_User::authorized('filecabinet')) {
                Current_User::disallow();
            }

            Cabinet::unpinFolder();
            PHPWS_Core::goBack();
            break;

        case 'pin_form':
            $javascript = true;
            @$key_id = (int)$_GET['key_id'];
            if (!$key_id) {
                javascript('close_refresh', array('refresh'=>0));
                break;
            }

            $this->loadForms();
            $this->forms->pinFolder($key_id);
            break;

        case 'delete_folder':
            if (!Current_User::authorized('filecabinet', 'delete_folders', null, null, true)) {
                Current_User::disallow();
            }
            $this->loadFolder();
            $this->folder->delete();
            PHPWS_Core::goBack();
            break;

        case 'delete_incoming':
            if (!Current_User::allow('filecabinet', 'classify', null, null, true)) {
                Current_User::disallow();
            }
            $this->deleteIncoming();
            $this->loadForms();
            $this->forms->classifyFileList();
            break;

        case 'document':
            $this->panel->setCurrentTab('document');
            $this->title = dgettext('filecabinet', 'Document folders');
            $this->loadForms();
            $this->forms->getFolders(DOCUMENT_FOLDER);
            break;

        case 'edit_folder':
            $javascript = true;
            $this->loadFolder();
            // permission check in function below
            $this->editFolder();
            break;

        case 'change_tn':
            $javascript = true;
            $this->changeTN();
            break;

        case 'post_thumbnail':
            $javascript = true;
            if ($this->postTN()) {
                javascript('close_refresh');
            } else {
                $this->message = dgettext('filecabinet', 'Could not save thumbnail image.');
                $this->changeTN();
            }
            break;

        case 'post_folder':
            $this->loadFolder();

            if (!Current_User::authorized('filecabinet', 'edit_folders')) {
                Current_User::disallow();
            }

            if ($this->folder->post()) {
                if (!$this->folder->save()) {
                    Layout::nakedDisplay(dgettext('filecabinet', 'Failed to create folder. Please check your logs.'));
                } else {
                    Layout::nakedDisplay(javascript('close_refresh'));
                }
            } else {
                $this->message = $this->folder->_error;
                $this->addFolder();
            }
            break;

        case 'post_allowed_files':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
            }

            $this->loadForms();
            $this->forms->postAllowedFiles();

            $this->message = dgettext('filecabinet', 'File types saved.');
            $this->title = dgettext('filecabinet', 'Allowed file types');
            $this->content = $this->forms->fileTypes();
            break;
            
        case 'save_settings':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
            }
            $this->loadForms();
            $result = $this->forms->saveSettings();
            if (is_array($result)) {
                $this->message = implode('<br />', $result);
            } else {
                $this->message = dgettext('filecabinet', 'Settings saved.');
            }

        case 'settings':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
            }
            $this->loadForms();
            $this->title = dgettext('filecabinet', 'Settings');
            $this->content = $this->forms->settings();
            break;

        case 'view_folder':
            $this->viewFolder();
            break;

        case 'file_types':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
            }
            $this->loadForms();
            $this->title = dgettext('filecabinet', 'Allowed file types');
            $this->content = $this->forms->fileTypes();
            break;

        }

        $template['TITLE']   = &$this->title;
        $template['MESSAGE'] = &$this->message;
        $template['CONTENT'] = &$this->content;

        if ($javascript) {
            $main = PHPWS_Template::process($template, 'filecabinet', 'javascript.tpl');
            Layout::nakedDisplay($main);
        } else {
            $main = PHPWS_Template::process($template, 'filecabinet', 'main.tpl');
            $this->panel->setContent($main);
            $finalPanel = $this->panel->display();
            Layout::add(PHPWS_ControlPanel::display($finalPanel));
        }
    }

    function download($document_id)
    {
        require_once 'HTTP/Download.php';
        PHPWS_Core::initModClass('filecabinet', 'Document.php');

        $document = new PHPWS_Document($document_id);

        if (!empty($document->_errors)) {
            foreach ($this->_errors as $err) {
                PHPWS_Error::log($err);
            }
            Layout::add(dgettext('filecabinet', 'Sorry but this file is inaccessible at this time.'));
            return;
        }

        $folder = new Folder($document->folder_id);
        if (!$folder->allow()) {
            $content = dgettext('filecabinet', 'Sorry, the file you requested is off limits.');
            Layout::add($content);
            return;
        }

        $file_path = $document->getPath();

        if (!is_file($file_path)) {
            PHPWS_Error::log(FC_DOCUMENT_NOT_FOUND, 'filecabinet', 'Cabinet_Action::download', $file_path);
            Layout::add(dgettext('filecabinet', 'Sorry but this file is inaccessible at this time.'));
            return;
        }

        $dl = new HTTP_Download;
        $dl->setFile($file_path);
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->filename);
        $dl->setContentType($document->file_type);
        $dl->send();
        exit();
    }

    function user($op=null)
    {
        if (empty($op)) {
            $op = & $_REQUEST['uop'];
        }

        switch($op) {
        case 'view_folder':
            $this->userViewFolder();
            break;
        }

        $template['TITLE']   = $this->title;
        $template['MESSAGE'] = $this->message;
        $template['CONTENT'] = $this->content;

        $main = PHPWS_Template::process($template, 'filecabinet', 'plain.tpl');
        Layout::add($main);
    }

    function fileManager($itemname, $file_id=0)
    {
        Layout::addStyle('filecabinet');
        PHPWS_Core::initModClass('filecabinet', 'File_Manager.php');
        $module = $_REQUEST['module'];
        if (!is_numeric($file_id)) {
            return false;
        }
        $manager = new FC_File_Manager($module, $itemname, $file_id);
        $manager->allFiles();
        return $manager;
    }

    function viewImage($id)
    {
        Layout::addStyle('filecabinet');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $image = new PHPWS_Image($id);
        $folder = new Folder($image->folder_id);

        if (!$folder->allow()) {
            $content = dgettext('filecabinet', 'Sorry, the file you requested is off limits.');
            Layout::add($content);
            return;
        }

        $tpl['TITLE'] = $image->title;

        if ($image->width > FC_MAX_IMAGE_POPUP_WIDTH || $image->height > FC_MAX_IMAGE_POPUP_HEIGHT) {
            if (FC_MAX_IMAGE_POPUP_WIDTH < FC_MAX_IMAGE_POPUP_HEIGHT) {
                $ratio = FC_MAX_IMAGE_POPUP_WIDTH / $image->width;
                $image->width = FC_MAX_IMAGE_POPUP_WIDTH;
                $image->height = $image->height * $ratio;
            } else {
                $ratio = FC_MAX_IMAGE_POPUP_HEIGHT / $image->height;
                $image->height = FC_MAX_IMAGE_POPUP_HEIGHT;
                $image->width = $image->width * $ratio;
            }
            $tpl['IMAGE'] = sprintf('<a href="%s">%s</a>', $image->getPath(), $image->getTag());
        } else {
            $tpl['IMAGE'] = $image->getTag();
        }

        $tpl['DESCRIPTION'] = $image->getDescription();
        $tpl['CLOSE'] = javascript('close_window');
        if ($folder->public_folder) {
            $db = new PHPWS_DB('images');
            $db->setLimit(1);
            $db->addWhere('folder_id', $image->folder_id);
            $db->addWhere('title', $image->title, '>');
            $db->addOrder('title');
            $next_img = $db->getObjects('PHPWS_Image');

            if (!empty($next_img)) {
                $tpl['NEXT'] = sprintf('<a id="next-link" href="%s%s">%s</a>', PHPWS_Core::getHomeHttp(),
                                       $next_img[0]->popupAddress(),
                                       dgettext('filecabinet', 'Next image'));
            }
            
            $db->resetWhere();
            $db->resetOrder();
            $db->addWhere('folder_id', $image->folder_id);
            $db->addWhere('title', $image->title, '<');
            $db->addOrder('title desc');
            $prev_img = $db->getObjects('PHPWS_Image');

            if (!empty($prev_img)) {
                $tpl['PREV'] = sprintf('<a id="prev-link" href="%s%s">%s</a>', PHPWS_Core::getHomeHttp(),
                                       $prev_img[0]->popupAddress(),
                                       dgettext('filecabinet', 'Previous image'));
            }
        }
        $content = PHPWS_Template::process($tpl, 'filecabinet', 'image_view.tpl');
        Layout::nakedDisplay($content);
    }

    function viewMultimedia($id)
    {
        Layout::addStyle('filecabinet');
        PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
        $multimedia = new PHPWS_Multimedia($id);

        $folder = new Folder($multimedia->folder_id);
        if (!$folder->allow()) {
            $content = dgettext('filecabinet', 'Sorry, the file you requested is off limits.');
            Layout::add($content);
            return;
        }


        $tpl['TITLE'] = $multimedia->title;
        $tpl['MULTIMEDIA'] = $multimedia->getTag();
        $tpl['DESCRIPTION'] = $multimedia->getDescription();
        $tpl['CLOSE'] = javascript('close_window');
        $content = PHPWS_Template::process($tpl, 'filecabinet', 'multimedia_view.tpl');
        Layout::nakedDisplay($content);
    }

    function addFolder()
    {
        $this->loadForms();
        if ($this->folder->ftype == IMAGE_FOLDER) {
            $this->title   = dgettext('filecabinet', 'Create image folder');
        } elseif ($this->folder->ftype == DOCUMENT_FOLDER) {
            $this->title   = dgettext('filecabinet', 'Create document folder');
        } else {
            $this->title   = dgettext('filecabinet', 'Create multimedia folder');
        }

        if (isset($_GET['module_created'])) {
            $this->content = $this->forms->editFolder($this->folder, false);
        } else {
            $this->content = $this->forms->editFolder($this->folder, true);
        }
    }

    function editFolder()
    {
        if (!Current_User::allow('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
            Current_User::disallow();
        }

        $this->loadForms();
        if ($this->folder->ftype == IMAGE_FOLDER) {
            $this->title   = dgettext('filecabinet', 'Update image folder');
        } elseif ($this->folder->ftype == DOCUMENT_FOLDER) {
            $this->title   = dgettext('filecabinet', 'Update document folder');
        } else {
            $this->title   = dgettext('filecabinet', 'Update multimedia folder');
        }
        if (isset($_GET['module_created'])) {
            $this->content = $this->forms->editFolder($this->folder, false);
        } else {
            $this->content = $this->forms->editFolder($this->folder, true);
        }
    }
    

    function loadImageManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
        $this->image_mgr = new FC_Image_Manager;
    }

    function loadDocumentManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'Document_Manager.php');
        $this->document_mgr = new FC_Document_Manager;
    }

    function loadMultimediaManager()
    {
        PHPWS_Core::initModClass('filecabinet', 'Multimedia_Manager.php');
        $this->loadFolder(MULTIMEDIA_FOLDER);
        $this->multimedia_mgr = new FC_Multimedia_Manager;
    }


    function loadForms()
    {
        if (empty($this->forms)) {
            PHPWS_Core::initModClass('filecabinet', 'Forms.php');
            $this->forms = new Cabinet_Form;
            $this->forms->cabinet = & $this;
        }
    }

    function unpinFolder()
    {
        if (!isset($_REQUEST['folder_id']) || !isset($_REQUEST['key_id'])) {
            return;
        }

        $folder_id = (int)$_REQUEST['folder_id'];
        $key_id    = (int)$_REQUEST['key_id'];

        $db = new PHPWS_DB('filecabinet_pins');
        $db->addWhere('folder_id', $folder_id);
        $db->addWhere('key_id', $key_id);
        $db->delete();
    }

    function pinFolder()
    {
        if (!isset($_POST['folder_id']) || !isset($_POST['key_id'])) {
            return;
        }

        $folder_id = (int)$_POST['folder_id'];
        $key_id = (int)$_POST['key_id'];

        $db = new PHPWS_DB('filecabinet_pins');
        $db->addWhere('folder_id', $folder_id);
        $db->addWhere('key_id', $key_id);
        $db->delete();

        $db->addValue('folder_id', $folder_id);
        $db->addValue('key_id', $key_id);
        $result = $db->insert();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    function passImages()
    {
        header("Content-type: text/plain");
        $this->loadFolder();
        $this->loadImageManager();
        echo $this->image_mgr->showImages($this->folder);
        exit();
    }


    function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=filecabinet';

        $image_command      = array('title'=>dgettext('filecabinet', 'Image folders'), 'link'=> $link);
        $document_command   = array('title'=>dgettext('filecabinet', 'Document folders'), 'link'=> $link);
        $multimedia_command = array('title'=>dgettext('filecabinet', 'Multimedia folders'), 'link'=> $link);

        $tabs['image']      = $image_command;
        $tabs['document']   = $document_command;
        $tabs['multimedia'] = $multimedia_command;

        if (Current_User::isDeity()) {
            $tabs['classify']   = array('title'=>dgettext('filecabinet', 'Classify'), 'link'=> $link);
            $tabs['settings']  = array('title'=> dgettext('filecabinet', 'Settings'), 'link' => $link);
            $tabs['file_types'] = array('title'=> dgettext('filecabinet', 'File types'), 'link' => $link);
        }

        $this->panel = new PHPWS_Panel('filecabinet');
        $this->panel->quickSetTabs($tabs);
        $this->panel->setModule('filecabinet');
    }


    function userViewFolder()
    {
        $this->loadFolder();
        if (!$this->folder->id || !$this->folder->public_folder) {
            $this->title = dgettext('filecabinet', 'Sorry');
            $this->content = dgettext('filecabinet', 'This is a private folder.');
            return;
        }
        if (!$this->folder->allow()) {
            if (Current_User::isLogged()) {
                $this->title = dgettext('filecabinet', 'Sorry');
                $this->content = dgettext('filecabinet', 'You do not have permission to view this folder.');
            } else {
                Current_User::requireLogin();
            }
            return;
        }
        $this->title = $this->folder->title;
        $this->loadForms();
        $kids = PHPWS_Settings::get('filecabinet', 'no_kids');
        $this->forms->folderContents($this->folder, false, $kids);
    }

    function viewFolder()
    {
        $this->loadFolder();
        if (!$this->folder->id) {
            PHPWS_Core::errorPage('404');
        }

        $this->title = sprintf('%s - %s', $this->folder->title, $this->folder->getPublic());
        $this->loadForms();
        $this->forms->folderContents($this->folder);
    }

    /**
     * Saves files posted in the forms classifyFileList function
     */
    function classifyFiles()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        PHPWS_Core::initModClass('filecabinet', 'Document.php');
        PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');

        if (empty($_POST['file_count'])) {
            return false;
        }

        foreach ($_POST['file_count'] as $key=>$filename) {
            $folder_id = $_POST['folder'][$key];
            $folder = new Folder($folder_id);

            if (empty($_POST['file_title'][$key])) {
                $error[$filename] = dgettext('filecabinet', 'Missing title.');
            }

            // initialize a new file object
            switch ($folder->ftype) {
            case IMAGE_FOLDER:
                $file_obj = new PHPWS_Image;
                break;

            case DOCUMENT_FOLDER:
                $file_obj = new PHPWS_Document;
                break;

            case MULTIMEDIA_FOLDER:
                $file_obj = new PHPWS_Multimedia;
                break;
            }

            // save the folder id and basic information

            $file_obj->folder_id = $folder->id;
            $file_obj->file_name = $filename;

            $file_obj->setTitle($_POST['file_title'][$key]);
            $file_obj->setDirectory($folder->getFullDirectory());

            if (!empty($_POST['file_description'][$key])) {
                $file_obj->setDescription($_POST['file_description'][$key]);
            }

            // move the file from the incoming directory
            $classify_dir = $this->getClassifyDir();
            if (empty($classify_dir)) {
                return array(dgettext('filecabinet', 'The web server does not have permission to access files in the classify directory.'));
            }
            $incoming_file = $classify_dir . $filename;
            $folder_directory = $file_obj->getPath();


            if (!@rename($incoming_file, $folder_directory)) {
                $errors[$filename] = sprintf(dgettext('filecabinet', 'Could not move file "%s" to "%s" folder directory.'), $filename, $folder->title);
                PHPWS_Error::log(FC_FILE_MOVE, 'filecabinet', 'Cabinet::classifyFiles', $folder_directory);
                continue;
            }

            $file_obj->file_type = PHPWS_File::getMimeType($file_obj->getPath());
            $file_obj->loadFileSize();

            // if image is getting saved, need to process
            if ($folder->ftype == IMAGE_FOLDER) {
                $file_obj->loadDimensions();
                $file_obj->save(true, false);
            } else {
                $file_obj->save(false);
            }
        }

        if (isset($errors)) {
            return $errors;
        } else {
            return true;
        }
    }

    function deleteIncoming()
    {
        if (empty($_POST['file_list'])) {
            if (isset($_GET['file'])) {
                $file_list[] = $_GET['file'];
            } else {
                return;
            }
        } else {
            $file_list = & $_POST['file_list'];
        }

        $classify_dir = $this->getClassifyDir();

        if (empty($classify_dir)) {
            $this->message = dgettext('filecabinet', 'The web server does not have permission to delete files from the classify directory.');
        }

        if (!is_array($file_list)) {
            return;
        }

        foreach ($file_list as $filename) {
            $file = $classify_dir . $filename;
            @unlink($classify_dir . $filename);
        }
    }

    function getMaxSizes()
    {
        $sys_size = str_replace('M', '', ini_get('upload_max_filesize'));
        $sys_size = $sys_size * 1000000;
        $form = new PHPWS_Form;

        $sizes['system']     = & $sys_size;
        $sizes['form']       = & $form->max_file_size;
        $sizes['document']   = PHPWS_Settings::get('filecabinet', 'max_document_size');
        $sizes['image']      = PHPWS_Settings::get('filecabinet', 'max_image_size');
        $sizes['multimedia'] = PHPWS_Settings::get('filecabinet', 'max_multimedia_size');
        $sizes['absolute']   = ABSOLUTE_UPLOAD_LIMIT;

        return $sizes;
    }

    function getClassifyDir()
    {
        if (FC_ALLOW_CLASSIFY_DIR_SETTING) {
            $directory = PHPWS_Settings::get('filecabinet', 'classify_directory');
        } else {
            $directory = FC_CLASSIFY_DIRECTORY;
        }

        if (is_writable($directory)) {
            return $directory;
        } else {
            return null;
        }
    }

    function changeTN()
    {
        $form = new PHPWS_Form('thumbnail');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('aop', 'post_thumbnail');
        $form->addHidden('type', $_REQUEST['type']);
        $form->addHidden('id', $_REQUEST['id']);
        $form->addFile('thumbnail');
        $form->setLabel('thumbnail', dgettext('filecabinet', 'Upload thumbnail'));
        $form->addSubmit(dgettext('filecabinet', 'Upload'));

        if ($_REQUEST['type'] == 'mm') {
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $mm = new PHPWS_Multimedia($_REQUEST['id']);
            if (!$mm->id) {
                return false;
            }
        }

        $tpl = $form->getTemplate();

        $tpl['CLOSE'] = javascript('close_window');

        $warnings[] = sprintf(dgettext('filecabinet', 'Max thumbnail size : %sx%s.'), FC_THUMBNAIL_WIDTH, FC_THUMBNAIL_HEIGHT);
        if ($mm->isVideo()) {
            $warnings[] = dgettext('filecabinet', 'Image must be a jpeg file.');
        }

        $tpl['WARNINGS'] = implode('<br />', $warnings);
        $this->title = dgettext('filecabinet', 'Upload new thumbnail');

        $this->content = PHPWS_Template::process($tpl, 'filecabinet', 'thumbnail.tpl');
    }

    function postTN()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');

        if ($_POST['type'] == 'mm') {
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $obj = new PHPWS_Multimedia($_POST['id']);
            if (!$obj->id) {
                return false;
            }
        }

        $image = new PHPWS_Image;
        $image->setMaxWidth(FC_THUMBNAIL_WIDTH);
        $image->setMaxHeight(FC_THUMBNAIL_HEIGHT);
        if (!$image->importPost('thumbnail')) {
            return false;
        }

        if ($obj->isVideo() && $image->file_type != 'image/jpeg' && $image->file_type != 'image/jpg') {
            return false;
        }

        $image->file_directory = $obj->thumbnailDirectory();
        $image->file_name = $obj->dropExtension() . '.' . $image->getExtension();
        $image->write();

        if ($obj->_classtype == 'multimedia') {
            $obj->thumbnail = & $image->file_name;
            $obj->save(false, false);
        }
        return true;
    }

    function listFolders($type=null, $simple=false)
    {
        $db = new PHPWS_DB('folders');
        if ($type) {
            $db->addWhere('ftype', (int)$type);
        }
        if ($simple) {
            $db->select();
        }
    }

    function getFile($id)
    {
        PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
        $file_assoc = new FC_File_Assoc($id);
        return $file_assoc;
    }

    function fileStyle()
    {
        Layout::addStyle('filecabinet', 'file_view.css');
    }

    function getTag($id)
    {
        PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
        $file_assoc = new FC_File_Assoc($id);
        return $file_assoc->getTag();
    }

    function loadFolder($folder_id=0)
    {
        if (!$folder_id && isset($_REQUEST['folder_id'])) {
            $folder_id = &$_REQUEST['folder_id'];
        }

        $this->folder = new Folder($folder_id);
        if (!$this->folder->id) {
            $this->folder->ftype = $_REQUEST['ftype'];
            if (isset($_REQUEST['module_created'])) {
                $this->folder->module_created = $_REQUEST['module_created'];
            }
        }
    }

    function authenticate()
    {
        if (!Current_User::isLogged()) {
            javascript('close_refresh');
            Layout::nakedDisplay();
            exit();
        }

        return Current_User::allow('filecabinet');
    }

    function convertToFileAssoc($table, $column, $type)
    {
        $db = new PHPWS_DB('fc_convert');
        $db->addWhere('table_name', $table);
        $db->addWhere('column_name', $column);
        $result = $db->select();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } elseif ($result) {
            return true;
        }

        PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
        $db = new PHPWS_DB($table);
        $db->addColumn('id');
        $db->addColumn($column);
        $db->setIndexBy('id');
        $item = $db->select('col');
        if (empty($item)) {
            return true;
        }

        foreach ($item as $id=>$item_id) {
            $db->reset();

            if (@$file_assoc_id = $item_converted[$item_id]) {
                $db->addValue($column, $file_assoc_id);
                $db->addWhere('id', $id);
                PHPWS_Error::logIfError($db->update());
            } else {
                $file_assoc = new FC_File_Assoc;
                $file_assoc->file_type = $type;
                $file_assoc->file_id = $item_id;
                if (!PHPWS_Error::logIfError($file_assoc->save())) {
                    $db->addValue($column, $file_assoc->id);
                    $db->addWhere('id', $id);
                    if (PHPWS_Error::logIfError($db->update())) {
                        continue;
                    }
                }
                $item_converted[$item_id] = $file_assoc->id;
            }
        }

        $db->reset();
        $db->addValue('table_name', $table);
        $db->addValue('column_name', $column);
        PHPWS_Error::logIfError($db->insert());
        return true;
    }

    function convertImagesToFileAssoc($table, $column)
    {
        return Cabinet::convertToFileAssoc($table, $column, FC_IMAGE);
    }

    function convertMediaToFileAssoc($table, $column)
    {
        return Cabinet::convertToFileAssoc($table, $column, FC_MEDIA);
    }


    function fileTypeAllowed($ext, $mode='all')
    {
        if (strpos($ext, '.')) {
            $ext = PHPWS_File::getFileExtension($ext);
        }

        $types = Cabinet::getAllowedTypes($mode);
        return in_array($ext, $types);
    }

    function getAllowedTypes($mode='all')
    {
        static $all = array();

        if ($mode == 'all' && !empty($all)) {
            return $all;
        }

        if ($mode=='all' || $mode=='image') {
            $image = PHPWS_Settings::get('filecabinet', 'image_files');
            if ($image) {
                $image = explode(',', $image);
            }
            if ($mode == 'image') {
                return $image;
            }
        }

        if ($mode=='all' || $mode=='document') {
            $docs  = PHPWS_Settings::get('filecabinet', 'document_files');
            if ($docs) {
                $docs = explode(',', $docs);
            }
            if ($mode == 'document') {
                return $docs;
            }
        }

        if ($mode=='all' || $mode=='media') {
            $media = PHPWS_Settings::get('filecabinet', 'media_files');
            if ($media) {
                $media = explode(',', $media);
            }
            if ($mode == 'media') {
                return $media;
            }
        }


        if ($image) {
            $all = array_merge($image, $all);
        }

        if ($docs) {
            $all = array_merge($docs, $all);
        }

        if ($media) {
            $all = array_merge($media, $all);
        }

        return $all;
    }

    function getResizes($max_width=0, $add_default=false)
    {
        if (!$max_width) {
            $max_width = PHPWS_Settings::get('filecabinet', 'max_image_dimension');
        }

        if ($add_default) {
            $resizes[0] = sprintf(dgettext('filecabinet', 'Default (%spx)'), 
                                  PHPWS_Settings::get('filecabinet', 'max_image_dimension'));
        }

        switch (1) {
        case $max_width >= 2000:
            $resizes[2000] = '2000px';

        case $max_width >= 1750:
            $resizes[1750] = '1750px';

        case $max_width >= 1500:
            $resizes[1500] = '1500px';

        case $max_width >= 1250:
            $resizes[1250] = '1250px';

        case $max_width >= 1000:
            $resizes[1000] = '1000px';

        case $max_width >= 800:
            $resizes[800] = '800px';

        case $max_width >= 600:
            $resizes[600] = '600px';

        case $max_width >= 300:
            $resizes[300] = '300px';

        case $max_width >= 100:
            $resizes[100] = '100px';

        case $max_width >= 50:
            $resizes[50] = '50px';
        }

        return $resizes;
    }

    /**
     * Called from the three file type managers. Adds a file listing
     * to move files from one folder to another
     */
    function moveToForm(&$form, $folder)
    {
        $db = new PHPWS_DB('folders');
        $db->addWhere('id', $folder->id, '!=');
        $db->addWhere('ftype', $folder->ftype);
        $db->addColumn('id');
        $db->addColumn('title');
        $db->setIndexBy('id');
        $folders = $db->select('col');
        
        if (!empty($folders)) {
            $folders = array(0=>'') + $folders;
            $form->addSelect('move_to_folder', $folders);
            $form->setLabel('move_to_folder', dgettext('filecabinet', 'Move to folder'));
        }
    }
}

?>