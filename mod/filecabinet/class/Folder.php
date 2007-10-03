<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('IMAGE_FOLDER',      1);
define('DOCUMENT_FOLDER',   2);
define('MULTIMEDIA_FOLDER', 3);

class Folder {
    var $id              = 0;
    var $key_id          = 0;
    var $title           = null;
    var $description     = null;
    var $ftype           = IMAGE_FOLDER;
    var $public_folder   = 0;
    var $icon            = null;
    var $module_created  = null;
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

    function getPublic()
    {
        if ($this->public_folder) {
            return dgettext('filecabinet', 'Public');
        } else {
            return dgettext('filecabinet', 'Private');
        }
    }
    
    function deleteLink()
    {
        $vars['QUESTION'] = dgettext('filecabinet', 'Are you certain you want to delete this folder and all its contents?');
        $vars['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', array('aop'=>'delete_folder', 'folder_id'=>$this->id),
                                                    true);
        $vars['LINK'] = dgettext('filecabinet', 'Delete');
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
            $js['label'] = dgettext('filecabinet', 'Edit');
        } else {
            $js['label'] = dgettext('filecabinet', 'Add folder');
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
        $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this image?');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', $vars, true);
        $js['LINK']     = dgettext('filecabinet', 'Delete');
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
        } elseif ($this->ftype == IMAGE_FOLDER) {
            $this->_base_directory = 'images/filecabinet/';
        } else {
            $this->_base_directory = 'files/multimedia/';
        }
    }

    function unpinLink()
    {
        $img = '<img style="float : right" src="images/mod/filecabinet/remove.png" />';
        $key = Key::getCurrent();
        return PHPWS_Text::secureLink($img, 'filecabinet', array('aop'=>'unpin', 'folder_id'=>$this->id, 'key_id'=>$key->id));
    }

    function uploadLink()
    {
        if ($this->ftype == DOCUMENT_FOLDER) {
            return $this->documentUploadLink();
        } elseif ($this->ftype == IMAGE_FOLDER) {
            return $this->imageUploadLink();
        } else {
            return $this->multimediaUploadLink();
        }
    }

    function imageUploadLink()
    {
        $vars['address'] = 'index.php?module=filecabinet&aop=upload_image_form&folder_id=' . $this->id;
        $vars['width']   = 600;
        $vars['height']  = 600;
        $vars['title'] = $vars['label']   = dgettext('filecabinet', 'Add image');
        return javascript('open_window', $vars);
    }


    function documentUploadLink()
    {
        $vars['address'] = 'index.php?module=filecabinet&aop=upload_document_form&folder_id=' . $this->id;
        $vars['width']   = 600;
        $vars['height']  = 600;
        $vars['title'] = $vars['label']   = dgettext('filecabinet', 'Add document');
        return javascript('open_window', $vars);
    }

    function multimediaUploadLink()
    {
        $vars['address'] = 'index.php?module=filecabinet&aop=upload_multimedia_form&folder_id=' . $this->id;
        $vars['width']   = 600;
        $vars['height']  = 600;
        $vars['title'] = $vars['label']   = dgettext('filecabinet', 'Add file');
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

    function viewLink($formatted=true)
    {
        $link = sprintf('index.php?module=filecabinet&amp;uop=view_folder&amp;folder_id=%s', $this->id);

        if (!$formatted) {
            return $link;
        } else {
            return sprintf('<a href="%s" title="%s">%s</a>', $link, dgettext('filecabinet', 'View folder'),
                           $this->title);
        }
    }

    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    function post()
    {
        if (empty($_POST['title'])) {
            $this->_error = dgettext('filecabinet', 'You must entitle your folder.');
            return false;
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setDescription($_POST['description']);

        if (isset($_POST['module_created'])) {
            $this->module_created = $_POST['module_created'];
        }

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
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if ($new_folder) {
            $full_dir = $this->getFullDirectory();
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
                        }
                    }
                }
            } else {
                $this->delete();
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
        $key->setUrl($this->viewLink(false));
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
        if (!$this->public_folder && !Current_User::isLogged()) {
            return false;
        }

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
        } elseif ($this->ftype == MULTIMEDIA_FOLDER) {
            $db = new PHPWS_DB('multimedia');
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

    /**
     * Image link that pops the image manager menu to the users
     */
    function imageTags($max_width, $max_height)
    {
        $icon = sprintf('<img src="%s" alt="%s" title="%s" />', $this->icon, $this->title, $this->title);
        $tpl['TITLE'] = $this->title;
        $items = $this->tallyItems();
        $tpl['ITEMS'] = sprintf(dngettext('filecabinet', '%s image', '%s images', $items), $items);

        $vars['aop']       = 'get_images';
        $vars['folder_id'] = $this->id;
        $vars['mw']        = $max_width;
        $vars['mh']        = $max_height;

        $jsvars['success'] = sprintf('show_images(requester.responseText, %s)', $this->id);
        $jsvars['failure'] = "alert('A problem occurred')"; 

        $tpl['ICON'] = sprintf('<a href="#"  onclick="loadRequester(\'%s\', \'%s\', \'%s\'); return false">%s</a>',
                               PHPWS_Text::linkAddress('filecabinet', $vars, true, false, false),
                               addslashes($jsvars['success']),
                               addslashes($jsvars['failure']),
                               $icon);

        javascript('ajax', $jsvars);
        javascript('modules/filecabinet/folder_contents', array('error_message'=>dgettext('filecabinet', 'Bad folder id.')));
        return $tpl;
    }

    function rowTags()
    {
        $icon = sprintf('<img src="%s" />', $this->icon);
        $vars['aop'] = 'view_folder';
        $vars['folder_id'] = $this->id;
        $tpl['ICON'] = PHPWS_Text::moduleLink($icon, 'filecabinet', $vars);
        $tpl['TITLE'] = PHPWS_Text::moduleLink($this->title, 'filecabinet', $vars);
        $tpl['ITEMS'] = $this->tallyItems();

        if (Current_User::allow('filecabinet', 'edit_folders', $this->id)) {
            $links[] = $this->editLink();
            if ($this->ftype == IMAGE_FOLDER) {
                $links[] = $this->imageUploadLink();
            } elseif ($this->ftype == DOCUMENT_FOLDER) {
                $links[] = $this->documentUploadLink();
            } else {
                $links[] = $this->multimediaUploadLink();
            }

            if ($this->key_id) {
                $links[] =  Current_User::popupPermission($this->key_id);
            }

            $links[] = $this->deleteLink();
        }

        if ($this->ftype == IMAGE_FOLDER) {
            if ($this->module_created) {
                $tpl['MODULE_CREATED'] = $this->module_created;
            } else {
                $tpl['MODULE_CREATED'] = dgettext('filecabinet', 'General');
            }
        }

        $tpl['PUBLIC'] = $this->getPublic();

        if (@$links) {
            $tpl['LINKS'] = implode(' | ', $links);
        }

        return $tpl;
    }

    /**
     * Loads the files in the current folder into the _files variable
     * $original_only applies to images
     */
    function loadFiles($original_only=false)
    {
        if ($this->ftype == IMAGE_FOLDER) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $db = new PHPWS_DB('images');
            if ($original_only) {
                $db->addWhere('parent_id', 0);
            }
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
        $db->addOrder('file_name');
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
        } elseif ($this->ftype == MULTIMEDIA_FOLDER) {
            $db = new PHPWS_DB('multimedia');
        }

        $db->addWhere('folder_id', $this->id);
        return $db->count();
    }

    function getPinned($key_id)
    {
        $db = new PHPWS_DB('folders');
        $db->addWhere('filecabinet_pins.key_id', $key_id);
        $db->addWhere('id', 'filecabinet_pins.folder_id');
        Key::restrictView($db, 'filecabinet');
        $result = $db->getObjects('Folder');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return;
        } elseif (!$result) {
            return;
        }
        Layout::addStyle('filecabinet');
        foreach ($result as $folder) {
            $folder->showPinned(false);
        }
    }

    function showPinned($single=true)
    {
        $tpl['FOLDER_TITLE'] = $this->viewLink();

        $this->loadFiles();

        if  (empty($this->_files)) {
            $tpl['CONTENT'] = dgettext('filecabinet', 'Folder is empty.');
        }

        if ($this->ftype == IMAGE_FOLDER) {
            $max = PHPWS_Settings::get('filecabinet', 'max_pinned_images');
        } elseif ($this->ftype = DOCUMENT_FOLDER) {
            $max = PHPWS_Settings::get('filecabinet', 'max_pinned_documents');
        } else {
            $max = PHPWS_Settings::get('filecabinet', 'max_pinned_multimedia');
        }

        if (!$max) {
            $max = 999;
        }

        $count = 1;

        foreach ($this->_files as $file) {
            if ($count > $max) {
                break;
            }
            $count++;
            $tpl['files'][] = $file->pinTags();
        }

        $tpl['UNPIN'] = $this->unpinLink();

        if (count($this->_files) > $count) {
            $tpl['MORE'] = sprintf('<a href="%s">%s</a>', $this->viewLink(false),
                                   dgettext('filecabinet', 'More...'));
        }


        $content = PHPWS_Template::process($tpl, 'filecabinet', 'pinned.tpl');
        Layout::add($content, 'filecabinet', 'pinfolder');
    }
}


?>