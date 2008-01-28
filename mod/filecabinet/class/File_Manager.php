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
    /**
     * id to session holding manager information
     */
    var $session_id  = null;

    function FC_File_Manager($module, $itemname, $file_id=0)
    {
        $this->module     = & $module;
        $this->itemname   = & $itemname;
        $this->session_id = md5($this->module . $this->itemname);
        $this->loadFileAssoc($file_id);
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
        }
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
        return sprintf('<img src="%s" title="%s" />',
                       FC_PLACEHOLDER,
                       dgettext('filecabinet', 'Add an image, media, or document file.')
                       );
    }

    function get()
    {
        Layout::addStyle('filecabinet', 'file_view.css');
        Layout::addStyle('filecabinet', 'file_manager/style.css');

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
        $tpl['CLEAR_LINK']  = $this->clearLink();
        $tpl['EDIT_LINK']   = $this->editLink();
        $tpl['LINK_ID']     = 'l_' . $this->session_id;
        $tpl['ITEMNAME']    = $this->itemname;

        return PHPWS_Template::process($tpl, 'filecabinet', 'file_manager/placeholder.tpl');
    }

    // Copy of image manager's getClearLink
    function clearLink()
    {
        $js_vars['label']    = dgettext('filecabinet', 'Clear file');
        $js_vars['id']       = $this->session_id;
        $js_vars['img']      = $this->placeHolder();
        return javascript('modules/filecabinet/clear_file', $js_vars);
    }

    function linkInfo($dimensions=true)
    {
        $info['cm']   = $this->module;
        $info['itn']  = $this->itemname;
        $info['fid']  = $this->file_assoc->id;

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
        $js['label']   = dgettext('filecabinet', 'Edit file');
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
     * Verifies the current user has at least minimum rights in the module
     * calling the manager
     */
    function authenticate()
    {
        if (empty($this->module)) {
            return false;
        }
        return Current_User::allow($this->module);
    }

    /**
     * Introduction view with three types of files
     */
    function startView()
    {
        Layout::addStyle('filecabinet', 'file_manager/style.css');
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
        if ($this->folder_type == DOCUMENT_FOLDER) {
            $icon_name = 'document80.png';
        } else {
            $icon_name = 'document80_bw.png';
        }
        $document_img = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/%s" title="%s"/>',
                                $icon_name,
                                dgettext('filecabinet', 'View document folders'));

        if ($this->folder_type == IMAGE_FOLDER) {
            $icon_name = 'image80.png';
        } else {
            $icon_name = 'image80_bw.png';
        }
        $image_img    = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/%s" title="%s"/>',
                                $icon_name,
                                dgettext('filecabinet', 'View image folders'));

        if ($this->folder_type == MULTIMEDIA_FOLDER) {
            $icon_name = 'media80.png';
        } else {
            $icon_name = 'media80_bw.png';
        }
        $media_img    = sprintf('<img src="images/mod/filecabinet/file_manager/file_type/%s" title="%s"/>',
                                $icon_name,
                                dgettext('filecabinet', 'View media folders'));

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
    }

    /**
     * View of folders per media
     */
    function folderView()
    {
        Layout::addStyle('filecabinet', 'file_manager/style.css');

        $tpl = array();

        $this->folderIcons($tpl);

        $tpl['CLOSE'] = javascript('close_window');

        $folder = new Folder;
        $folder->ftype = $this->folder_type;
        $tpl['ADD_FOLDER'] = $folder->editLink(true);

        $db = new PHPWS_DB('folders');
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
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        javascript('confirm'); // needed for deletion

        Layout::addStyle('filecabinet', 'file_manager/style.css');
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
        $tpl['FOLDER_TITLE'] = & $this->current_folder->title;

        switch ($this->folder_type) {
        case IMAGE_FOLDER:
            $js = $this->linkInfo();
            $js['authkey'] = Current_User::getAuthKey();
            $js['failure_message'] = dgettext('filecabinet', 'Unable to resize image.');
            $js['confirmation'] = sprintf(dgettext('filecabinet', 'This image is larger than the %s x %s limit. Do you want to resize the image to fit?'),
                                          $this->max_width,
                                          $this->max_height);
            
            javascript('modules/filecabinet/pick_file', $js);
            $db = new PHPWS_DB('images');
            $class_name = 'PHPWS_Image';
            $file_type = FC_IMAGE;
            $image = new PHPWS_Image;
            $image->file_directory = 'images/mod/filecabinet/file_manager/';
            $image->file_name      = 'folder_random.png';
            $image->title          = dgettext('filecabinet', 'Show a random image from this folder');
            $image->alt            = dgettext('filecabinet', 'Random image icon');
            $image->loadDimensions();
            $altvars = $this->linkInfo();

            if ($this->current_folder->public_folder) {
                $altvars['id']        = $this->current_folder->id;
                $altvars['fop']       = 'pick_file';
                
                $altvars['file_type'] = FC_IMAGE_RANDOM;
                $tpl['ALT1'] = PHPWS_Text::secureLink($image->getTag(), 'filecabinet', $altvars);
                
                if ($this->file_assoc->file_type == FC_IMAGE_RANDOM && $this->current_folder->id == $this->file_assoc->file_id) {
                    $tpl['ALT_HIGH1'] = ' alt-high';
                }
                
                $image->file_name = 'thumbnails.png';
                $image->title     = dgettext('filecabinet', 'Show block of thumbnails');
                $image->alt       = dgettext('filecabinet', 'Thumbnail icon');
                $image->loadDimensions();

                $altvars['file_type'] = FC_IMAGE_FOLDER;
                if ($this->file_assoc->file_type == FC_IMAGE_FOLDER) {
                    $tpl['ALT_HIGH2'] = ' alt-high';
                }

                $tpl['ALT2'] = PHPWS_Text::secureLink($image->getTag(), 'filecabinet', $altvars);
            }
            break;

        case DOCUMENT_FOLDER:
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $db = new PHPWS_DB('documents');
            $class_name = 'PHPWS_Document';
            $file_type = FC_DOCUMENT;
            $image = new PHPWS_Image;
            $image->file_directory = 'images/mod/filecabinet/file_manager/';
            $image->file_name      = 'all_files.png';
            $image->title          = dgettext('filecabinet', 'Show all files in the folder');
            $image->alt            = dgettext('filecabinet', 'All files icon');
            $image->loadDimensions();
            if ($this->current_folder->public_folder) {
                $altvars = $this->linkInfo();
                $altvars['id']        = $this->current_folder->id;
                $altvars['fop']       = 'pick_file';

                $altvars['file_type'] = FC_DOCUMENT_FOLDER;
                $tpl['ALT1'] = PHPWS_Text::secureLink($image->getTag(), 'filecabinet', $altvars);

                if ($this->file_assoc->file_type == FC_DOCUMENT_FOLDER && $this->current_folder->id == $this->file_assoc->file_id) {
                    $tpl['ALT_HIGH1'] = ' alt-high';
                }
            }
            break;

        case MULTIMEDIA_FOLDER:
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $db = new PHPWS_DB('multimedia');
            $class_name = 'PHPWS_Multimedia';
            $file_type = FC_MEDIA;
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
            unset($tpl['ALT1']);
            unset($tpl['ALT_HIGH1']);
            unset($tpl['ALT2']);
            unset($tpl['ALT_HIGH2']);
        }
        
        $tpl['ADD_FILE'] = $this->current_folder->uploadLink();
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
        } else {
            return $this->startView();
        }
    }

    function pickFile()
    {
        $file = $this->getFileAssoc($_REQUEST['file_type'], $_REQUEST['id'], true);
        $vars['id']      = $this->session_id;
        $vars['data']    = $this->jsReady($file->getTag(true));
        $vars['new_id']  = $file->id;
        javascript('modules/filecabinet/update_file', $vars);
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

        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('file_type', (int)$file_type);
        $db->addWhere('file_id', (int)$id);
        $result = $db->loadObject($file_assoc);

        if ($result) {
            if (PHPWS_Error::logIfError($result)) {
                return false;
            } elseif (!$update) {
                return $file_assoc;
            }
        }

        $file_assoc->file_type = &$file_type;
        $file_assoc->file_id   = $id;

        if ($file_assoc->file_type == FC_IMAGE_RESIZE) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $image = new PHPWS_Image($id);
            if (!$dst = $image->resizePath()) {
                return false;
            }

            $resize_file_name = sprintf('%sx%s.%s', $this->max_width, $this->max_height, $image->getExtension());

            if (!$image->resize($dst . $resize_file_name, $this->max_width, $this->max_height)) {
                return false;
            }
            $file_assoc->resize = & $resize_file_name;
        }

        $file_assoc->save();
        return $file_assoc;
    }


}

?>