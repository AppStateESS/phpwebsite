<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('IMAGE_FOLDER', 1);
define('DOCUMENT_FOLDER', 2);

class Folder {
    var $id              = 0;
    var $key_id          = 0;
    var $title           = null;
    var $description     = null;
    var $ftype           = IMAGE_FOLDER;
    var $public_folder   = 1;
    var $icon            = null;
    // An array of file objects
    var $_files          = 0;
    var $_error          = 0;
    var $_base_directory = null;

    function Folder($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
        if ($this->_error) {
            $this->logError();
            $this->id = 0;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('folders');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }
    }


    function deleteLink()
    {
        $vars['QUESTION'] = _('Are you certain you want to delete this folder and all its contents?');
        $vars['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', array('aop'=>'delete_folder', 'folder_id'=>$this->id),
                                                    true);
        $vars['LINK'] = _('Delete');
        return javascript('confirm', $vars);
    }

    /**
     * Creates javascript pop up for creating a new folder
     */
    function editLink()
    {
        if ($this->id) {
            $vars['aop']    = 'edit_folder';
            $vars['folder_id'] = $this->id;
            $js['label'] = _('Edit');
        } else {
            $js['label'] = _('Add folder');
            $vars['aop'] = 'add_folder';
        }

        $vars['ftype'] = $this->ftype;

        $js['address'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);

        $js['width'] = 370;
        $js['height'] = 420;
        return javascript('open_window', $js);
    }

    function deleteImageLink()
    {
        $vars['action'] = 'delete_image';
        $vars['image_id'] = $this->id;
        $js['QUESTION'] = _('Are you sure you want to delete this image?');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', $vars, true);
        $js['LINK']     = _('Delete');
        $links[] = javascript('confirm', $js);
    }


    function getFullDirectory()
    {
        if (!$this->id) {
            return null;
        }
        if (empty($this->_base_directory)) {
            $this->loadDirectory();
        }
        return sprintf('%sfolder%s/', $this->_base_directory, $this->id);
    }

    function loadDirectory()
    {
        if ($this->ftype == DOCUMENT_FOLDER) {
            $this->_base_directory = PHPWS_Settings::get('filecabinet', 'base_doc_directory');
        } else {
            $this->_base_directory = 'images/filecabinet/';
        }
    }

    function imageUploadLink()
    {
        $vars['address'] = 'index.php?module=filecabinet&aop=upload_image_form&folder_id=' . $this->id;
        $vars['width']   = 540;
        $vars['height']  = 460;
        $vars['label']   = _('Add image');
        return javascript('open_window', $vars);
    }


    function documentUploadLink()
    {
        $vars['address'] = 'index.php?module=filecabinet&aop=upload_document_form&folder_id=' . $this->id;
        $vars['width']   = 540;
        $vars['height']  = 400;
        $vars['label']   = _('Add document');
        return javascript('open_window', $vars);
    }

    function logError()
    {
        PHPWS_Error::log($this->_error);
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    function post()
    {
        if (empty($_POST['title'])) {
            $this->_error = _('You must entitle your folder.');
            return false;
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setDescription($_POST['description']);

        $this->ftype = $_POST['ftype'];
        $this->public_folder = $_POST['public_folder'];
        return true;
    }

    function save()
    {
        if (empty($this->icon)) {
            $this->icon = 'images/mod/filecabinet/folder.png';
        }

        if (!$this->id) {
            $new_folder = true;
        } else {
            $new_folder = false;
        }

        $db = new PHPWS_DB('folders');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }

        if ($new_folder) {
            $full_dir = $this->getFullDirectory();
            $result = @mkdir($full_dir);
            if ($result) {
                if ($this->ftype == IMAGE_FOLDER) {
                    $thumb_dir = $full_dir . '/tn/';
                    $result = @mkdir($thumb_dir);
                    if (!$result) {
                        @rmdir($full_dir);
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        return $this->saveKey($new_folder);
    }


    function saveKey($new_folder=true)
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }
        
        $key->setModule('filecabinet');
        $key->setItemName('folder');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_folders');
        $key->setUrl('view_folder');
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PEAR::isError($result)) {
            return $result;
        }
        $this->key_id = $key->id;

        if ($new_folder) {
            $db = new PHPWS_DB('folders');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            $result = $db->update();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return true;
    }


    function allow()
    {
        if (!$this->key_id) {
            return true;
        }
        $key = new Key($this->key_id);
        return $key->allowView();
    }

    function delete()
    {
        if ($this->ftype = IMAGE_FOLDER) {
            $db = new PHPWS_DB('images');
        } elseif ($this->ftype == DOCUMENT_FOLDER) {
            $db = new PHPWS_DB('documents');
        } else {
            return false;
        }
        $db->addWhere('folder_id', $this->id);
        $db->delete();

        $db = new PHPWS_DB('folders');
        $db->addWhere('id', $this->id);
        $db->delete();

        $directory = $this->getFullDirectory();

        if (is_dir($directory)) {
            PHPWS_File::rmdir($directory);
        }
    }

    function imageTags($max_width, $max_height)
    {
        $icon = sprintf('<img src="%s" alt="%s" title="%s" />', $this->icon, $this->title, $this->title);
        $tpl['TITLE'] = $this->title;
        $tpl['ITEMS'] = sprintf('%s item(s)', $this->tallyItems());

        $vars['aop'] = 'get_images';
        $vars['folder_id'] = $this->id;
        $vars['mw'] = $max_width;
        $vars['mh'] = $max_height;

        $jsvars['success_function'] = sprintf('show_images(requester.responseText, %s)', $this->id);
        $jsvars['failure_function'] = "alert('A problem occurred')"; 

        $tpl['ICON'] = sprintf('<a href="#" onclick="loadRequester(\'%s\', \'%s\', \'%s\'); return false">%s</a>', 
                               PHPWS_Text::linkAddress('filecabinet', $vars, true, false, false),
                               addslashes($jsvars['success_function']),
                               addslashes($jsvars['failure_function']),
                               $icon);

        javascript('ajax', $jsvars);
        Layout::getModuleJavascript('filecabinet', 'folder_contents', array('error_message'=>_('Bad folder id.')));

        return $tpl;
    }

    function rowTags()
    {
        $icon = sprintf('<img src="%s" />', $this->icon);
        $vars['aop'] = 'view_folder';
        $vars['folder_id'] = $this->id;
        $tpl['ICON'] = PHPWS_Text::moduleLink($icon, 'filecabinet', $vars);
        $tpl['ITEMS'] = $this->tallyItems();

        if (Current_User::allow('filecabinet', 'edit_folders', $this->id)) {
            $links[] = $this->editLink();
            if ($this->ftype == IMAGE_FOLDER) {
                $links[] = $this->imageUploadLink();
            } else {
                $links[] = $this->documentUploadLink();
            }
            
            $links[] =  Current_User::popupPermission($this->key_id);
            $links[] = $this->deleteLink();
        }
        $tpl['LINKS'] = implode(' | ', $links);
        return $tpl;
    }


    function loadFiles()
    {
        if ($this->ftype == IMAGE_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $db = new PHPWS_DB('images');
            $obj_name = 'PHPWS_Image';
        } elseif ($this->ftype == DOCUMENT_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $db = new PHPWS_DB('documents');
            $obj_name = 'PHPWS_Document';
        }

        $db->addWhere('folder_id', $this->id);
        $result = $db->getObjects($obj_name);
        
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } elseif ($result) {
            $this->_files = &$result;
            return true;
        } else {
            return false;
        }
    }

    function tallyItems()
    {
        if ($this->ftype == IMAGE_FOLDER) {
            $db = new PHPWS_DB('images');
        } elseif ($this->ftype == DOCUMENT_FOLDER) {
            $db = new PHPWS_DB('documents');
        }

        $db->addWhere('folder_id', $this->id);
        return $db->count();
    }
}


?>