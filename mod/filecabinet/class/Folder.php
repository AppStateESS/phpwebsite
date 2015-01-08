<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Folder
{
    public $id = 0;
    public $key_id = 0;
    public $title = null;
    public $description = null;
    public $ftype = IMAGE_FOLDER;
    public $public_folder = 1;
    public $icon = null;
    public $module_created = null;
    public $max_image_dimension = 0;
    // An array of file objects
    public $_files = 0;
    public $_error = 0;
    public $_base_directory = null;

    public function __construct($id = 0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int) $id;
        $this->init();
        if ($this->_error) {
            $this->logError();
            $this->id = 0;
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('folders');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
        }
    }

    public function setFtype($ftype)
    {
        if (!in_array($ftype, array(IMAGE_FOLDER, DOCUMENT_FOLDER, MULTIMEDIA_FOLDER))) {
            return false;
        }
        $this->ftype = $ftype;
    }

    public function getPublic()
    {
        if ($this->public_folder) {
            return dgettext('filecabinet', 'Public');
        } else {
            return dgettext('filecabinet', 'Private');
        }
    }

    public function deleteLink($mode = 'link')
    {
        $vars['QUESTION'] = dgettext('filecabinet', 'Are you certain you want to delete this folder and all its contents?');
        $vars['ADDRESS'] = PHPWS_Text::linkAddress('filecabinet', array('aop' => 'delete_folder', 'folder_id' => $this->id), true);
        $label = dgettext('filecabinet', 'Delete');
        if ($mode == 'image') {
            $vars['LINK'] = Icon::show('delete', dgettext('filecabinet', 'Delete'));
        } else {
            $vars['LINK'] = $label;
        }
        return javascript('confirm', $vars);
    }

    /**
     * Creates javascript pop up for creating a new folder
     */
    public function editLink($mode = null, $module_created = null)
    {
        if ($this->id) {
            $vars['aop'] = 'edit_folder';
            $vars['folder_id'] = $this->id;
            if ($mode == 'title') {
                $label = $this->title;
            } else {
                $label = dgettext('filecabinet', 'Edit');
            }
        } else {
            $label = dgettext('filecabinet', 'Add folder');
            $vars['aop'] = 'add_folder';
        }

        if ($mode == 'image') {
            $js['label'] = '<i class="fa fa-edit" title="' . dgettext('filecabinet', 'Edit') . '"></i>';
        } else {
            $js['label'] = & $label;
        }

        $vars['ftype'] = $this->ftype;
        if ($module_created) {
            $vars['module_created'] = $module_created;
        }

        $js['address'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);

        $js['width'] = 370;
        $js['height'] = 500;
        if ($mode == 'button') {
            $js['type'] = 'button';
        }
        return javascript('open_window', $js);
    }

    public function deleteImageLink()
    {
        $vars['action'] = 'delete_image';
        $vars['image_id'] = $this->id;
        $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this image?');
        $js['ADDRESS'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);
        $js['LINK'] = dgettext('filecabinet', 'Delete');
        $links[] = javascript('confirm', $js);
    }

    public function getFullDirectory()
    {
        if (!$this->id) {
            return null;
        }
        if (empty($this->_base_directory)) {
            $this->loadDirectory();
        }
        return sprintf('%sfolder%s/', $this->_base_directory, $this->id);
    }

    public function loadDirectory()
    {
        if ($this->ftype == DOCUMENT_FOLDER) {
            $this->_base_directory = PHPWS_Settings::get('filecabinet', 'base_doc_directory');
        } elseif ($this->ftype == IMAGE_FOLDER) {
            $this->_base_directory = 'images/filecabinet/';
        } else {
            $this->_base_directory = 'files/multimedia/';
        }
    }

    /**
     * @deprecated
     * @param type $mode
     * @param type $force_width
     * @param type $force_height
     * @return type
     */
    public function uploadLink($mode = null, $force_width = null, $force_height = null)
    {
        if ($this->ftype == DOCUMENT_FOLDER) {
            return $this->addLink('document', $mode);
        } elseif ($this->ftype == IMAGE_FOLDER) {
            return $this->addLink('image', $mode, $force_width, $force_height);
        } else {
            return $this->addLink('media', $mode);
        }
    }

    /**
     * @deprecated
     */
    private function addLink($type, $mode = null, $force_width = 0, $force_height = 0)
    {
        $vars['width'] = 600;
        $vars['height'] = 600;

        $link_var['folder_id'] = $this->id;
        switch ($type) {
            case 'image':
                $link_var['iop'] = 'upload_image_form';
                $link_var['fw'] = $force_width;
                $link_var['fh'] = $force_height;
                $label = dgettext('filecabinet', 'Add image');
                break;

            case 'document':
                $link_var['dop'] = 'upload_document_form';
                $label = dgettext('filecabinet', 'Add document');
                break;

            case 'media':
                $link_var['mop'] = 'upload_multimedia_form';
                $label = dgettext('filecabinet', 'Add media');
                break;
        }

        $link = new PHPWS_Link(null, 'filecabinet', $link_var, true);
        $link->convertAmp(false);
        $link->setSalted();
        $vars['address'] = $link->getAddress();
        $vars['title'] = & $label;

        switch ($mode) {
            case 'button':
                $vars['label'] = $label;
                $vars['type'] = 'button';
                break;

            case 'icon':
                $vars['label'] = '<i class="fa fa-upload" title="' . dgettext('filecabient', 'Upload') . '"></i>';
                break;

            default:
                $vars['label'] = $label;
        }

        return javascript('open_window', $vars);
    }
    
    /**
     * @deprecated
     * @param type $button
     * @return type
     */
    public function embedLink($button = false)
    {
        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', array('mop' => 'edit_embed',
                    'folder_id' => $this->id), true);
        $vars['width'] = 400;
        $vars['height'] = 200;
        $vars['title'] = $vars['label'] = dgettext('filecabinet', 'Add embedded');
        if ($button) {
            $vars['type'] = 'button';
        }
        return javascript('open_window', $vars);
    }

    public function logError()
    {
        PHPWS_Error::log($this->_error);
    }

    public function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    public function viewLink($formatted = true)
    {
        $link = sprintf('index.php?module=filecabinet&amp;uop=view_folder&amp;folder_id=%s', $this->id);

        if (!$formatted) {
            return $link;
        } else {
            return sprintf('<a href="%s" title="%s">%s</a>', $link, dgettext('filecabinet', 'View folder'), $this->title);
        }
    }

    public function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    public function post()
    {
        if (empty($_POST['title'])) {
            $this->_error = dgettext('filecabinet', 'You must entitle your folder.');
            return false;
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setDescription($_POST['description']);

        if (!empty($_POST['module_created'])) {
            $this->module_created = $_POST['module_created'];
        } else {
            $this->module_created = null;
        }

        $this->ftype = $_POST['ftype'];
        if (isset($_POST['max_image_dimension'])) {
            $this->max_image_dimension = (int) $_POST['max_image_dimension'];
        }
        $this->public_folder = $_POST['public_folder'];
        return true;
    }

    public function save()
    {
        if (empty($this->icon)) {
            $this->icon = PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/folder.png';
        }

        if (!$this->id) {
            $new_folder = true;
        } else {
            $new_folder = false;
        }

        $db = new PHPWS_DB('folders');
        $result = $db->saveObject($this);

        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        $full_dir = $this->getFullDirectory();
        if ($new_folder) {
            if (!is_dir($full_dir)) {
                $result = @mkdir($full_dir);
            } else {
                $result = true;
            }

            if ($result) {
                if ($this->ftype == IMAGE_FOLDER || $this->ftype == MULTIMEDIA_FOLDER) {
                    $thumb_dir = $full_dir . '/tn/';
                    if (!is_dir($thumb_dir)) {
                        $result = @mkdir($thumb_dir);
                        if (!$result) {
                            @rmdir($full_dir);
                            return false;
                        } else {
                            file_put_contents($thumb_dir . '.htaccess', 'Allow from all');
                        }
                    }
                }
            } else {
                PHPWS_Error::log(FC_BAD_DIRECTORY, 'filecabinet', 'Folder:save', $full_dir);
                $this->delete();
                return false;
            }
        }

        if ($this->ftype == DOCUMENT_FOLDER) {
            if ($this->public_folder) {
                $path = $full_dir . '.htaccess';
                if (is_file($path)) {
                    unlink($path);
                }
            } else {
                file_put_contents($full_dir . '.htaccess', 'Deny from all');
            }
        }
        return $this->saveKey($new_folder);
    }

    public function saveKey($new_folder = true)
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->getError())) {
                $key = new Key;
            }
        }

        $key->setModule('filecabinet');
        $key->setItemName('folder');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_folders');
        $key->setUrl($this->viewLink(false));
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }
        $this->key_id = $key->id;

        if ($new_folder) {
            $db = new PHPWS_DB('folders');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            $result = $db->update();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }
        return true;
    }

    public function allow()
    {
        if (!$this->public_folder && !Current_User::isLogged()) {
            return false;
        }

        if (!$this->key_id) {
            return true;
        }
        $key = new Key($this->key_id);
        return $key->allowView();
    }

    public function delete()
    {
        if ($this->ftype == IMAGE_FOLDER) {
            $table = 'images';
        } elseif ($this->ftype == DOCUMENT_FOLDER) {
            $table = 'documents';
        } elseif ($this->ftype == MULTIMEDIA_FOLDER) {
            $table = 'multimedia';
        } else {
            return false;
        }

        /**
         * Delete file associations inside folder
         */
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere($table . '.folder_id', $this->id);
        $db->addWhere($table . '.id', 'fc_file_assoc.file_id');
        PHPWS_Error::logIfError($db->delete());


        /**
         * Delete the special folder associations to this folder
         */
        $db->reset();
        $db->addWhere('file_type', FC_IMAGE_FOLDER, '=', 'or', 1);
        $db->addWhere('file_type', FC_IMAGE_LIGHTBOX, '=', 'or', 1);
        $db->addWhere('file_type', FC_IMAGE_RANDOM, '=', 'or', 1);
        $db->addWhere('file_type', FC_DOCUMENT_FOLDER, '=', 'or', 1);
        $db->addWhere('file_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /**
         * Delete the files in the folder from the db
         */
        unset($db);
        $db = new PHPWS_DB($table);
        $db->addWhere('folder_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /**
         * Delete the folder from the database
         */
        $db = new PHPWS_DB('folders');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /**
         * Delete the key
         */
        $key = new Key($this->key_id);
        $key->delete();

        /**
         * Delete the physical directory the folder occupies
         */
        $directory = $this->getFullDirectory();

        if (is_dir($directory)) {
            PHPWS_File::rmdir($directory);
        }

        return true;
    }

    public function rowTags()
    {
        PHPWS_Core::requireConfig('filecabinet', 'config.php');
        if (FC_ICON_PAGER_LINKS) {
            $mode = 'icon';
            $spacer = '';
        } else {
            $mode = null;
            $spacer = ' | ';
        }

        //$icon = sprintf('<img src="%s" />', $this->icon);
        $vars['aop'] = 'view_folder';
        $vars['folder_id'] = $this->id;

        $tpl['TITLE'] = PHPWS_Text::moduleLink($this->title, 'filecabinet', $vars);
        $tpl['ITEMS'] = $this->tallyItems();

        if (Current_User::allow('filecabinet', 'edit_folders', $this->id, 'folder')) {
            $links[] = $this->editLink('image');
            $links[] = $this->uploadLink('icon');
        }

        if (Current_User::allow('filecabinet', 'edit_folders', $this->id, 'folder', true)) {
            if ($this->key_id) {
                $links[] = Current_User::popupPermission($this->key_id, null, $mode);
            }
        }

        if (Current_User::allow('filecabinet', 'delete_folders', null, null, true)) {
            $links[] = $this->deleteLink('image');
        }

        $mods = PHPWS_Core::getModuleNames();
        if ($this->module_created && isset($mods[$this->module_created])) {
            $tpl['MODULE_CREATED'] = $mods[$this->module_created];
        } else {
            $tpl['MODULE_CREATED'] = dgettext('filecabinet', 'General');
        }

        $tpl['PUBLIC'] = $this->getPublic();

        if (@$links) {
            $tpl['LINKS'] = implode($spacer, $links);
        }

        return $tpl;
    }

    /**
     * Loads the files in the current folder into the _files variable
     * $original_only applies to images
     */
    public function loadFiles($original_only = false)
    {
        if ($this->ftype == IMAGE_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $db = new PHPWS_DB('images');
            $obj_name = 'PHPWS_Image';
        } elseif ($this->ftype == DOCUMENT_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $db = new PHPWS_DB('documents');
            $obj_name = 'PHPWS_Document';
        } elseif ($this->ftype == MULTIMEDIA_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $db = new PHPWS_DB('multimedia');
            $obj_name = 'PHPWS_Multimedia';
        }

        $db->addWhere('folder_id', $this->id);
        $db->addOrder('title');
        $result = $db->getObjects($obj_name);

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } elseif ($result) {
            $this->_files = &$result;
            return true;
        } else {
            return false;
        }
    }

    public function tallyItems()
    {
        if ($this->ftype == IMAGE_FOLDER) {
            $db = new PHPWS_DB('images');
        } elseif ($this->ftype == DOCUMENT_FOLDER) {
            $db = new PHPWS_DB('documents');
        } elseif ($this->ftype == MULTIMEDIA_FOLDER) {
            $db = new PHPWS_DB('multimedia');
        }

        $db->addWhere('folder_id', $this->id);
        return $db->count();
    }

}

?>