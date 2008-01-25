<?php

/**
 * This class is for all files that are not images
 *
 * At some time there may be special circumstances for documents but
 * for now they are just download links.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::requireConfig('core', 'file_types.php');
PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'File_Common.php');

class PHPWS_Document extends File_Common {
    var $_classtype = 'document';

    function PHPWS_Document($id=NULL)
    {
        $this->loadAllowedTypes();
        $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_document_size'));

        if (empty($id)) {
            return;
        }
    
        $this->id = (int)$id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->id = 0;
            $this->_errors[] = $result;
        } elseif (empty($result)) {
            $this->id = 0;
            $this->_errors[] = PHPWS_Error::get(FC_IMG_NOT_FOUND, 'filecabinet', 'PHPWS_Image');
        }
    }

    function init()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new PHPWS_DB('documents');
        return $db->loadObject($this);
    }


    function getIconView()
    {
        static $icon_list = NULL;

        if (empty($icon_list)) {
            $file = PHPWS_Core::getConfigFile('filecabinet', 'icons.php');
            if (!$file) {
                return sprintf('<img class="fc-mime-icon" src="./images/mod/filecabinet/mime_types/text.png" title="%s" alt="%s" />', $this->title, $this->title);
            } else {
                include $file;
            }
        }

        if (!@$graphic = $icon_list[$this->file_type]) {
            return sprintf('<img class="fc-mime-icon" src="./images/mod/filecabinet/mime_types/text.png" title="%s" alt="%s" />', $this->title, $this->title);
        } else {
            return sprintf('<img class="fc-mime-icon" src="images/mod/filecabinet/mime_types/%s" title="%s" alt="%s" />', $graphic, $this->title, $this->title);
        }
    }

    /**
     * Returns the download path if document is under the install directory.
     * Returns null otherwise.
     */
    function getDownloadPath()
    {
        $path = $this->getPath();

        $testpath = preg_quote(PHPWS_HOME_DIR);

        if (preg_match("@$testpath@", $path)) {
            return str_replace(PHPWS_HOME_DIR, '', $this->getPath());
        } else {
            return null;
        }
    }

    function getViewLink($format=FALSE, $type='title')
    {
        if (MOD_REWRITE_ENABLED) {
            $link = 'filecabinet/' . $this->id;
        } else {
            $link = sprintf('index.php?module=filecabinet&amp;id=' . $this->id);
        }

        if ($format) {
            switch ($type) {
            case 'title':
                return sprintf('<a href="%s">%s</a>', $link, $this->title);

            case 'icon':
                return sprintf('<a href="%s">%s</a>', $link, $this->getIconView());

            case 'smallicon':
                return sprintf('<a href="%s">%s</a>', $link, $this->getIconView(true));

            case 'filename':
                return sprintf('<a href="%s">%s</a>', $link, $this->file_name);
            }
        } else {
            return $link;
        }
    }

    function loadAllowedTypes()
    {
        $this->_allowed_types = unserialize(ALLOWED_DOCUMENT_TYPES);
    }

    function allowDocumentType($type)
    {
        $document = new PHPWS_Document;
        return $document->allowType($type);
    }


    function pinTags()
    {
        $tpl['TN'] = $this->getViewLink(true, 'icon');
        $tpl['TITLE'] = $this->getViewLink(true, 'title');

        return $tpl;
    }

    function rowTags()
    {
        $links = null;

        $tpl['SIZE']      = $this->getSize(true);
        $dpath = $this->getDownloadPath();
        if ($dpath) {
            $tpl['FILE_NAME'] = sprintf('<a href="%s">%s</a>', $dpath, $this->file_name);
        } else {
            $tpl['FILE_NAME'] = $this->file_name;
        }

        $tpl['ICON']      = $this->getViewLink(true, 'smallicon');
        $tpl['TITLE']     = $this->title;

        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $links[] = $this->editLink();

            $vars['document_id'] = $this->id;
            $vars['dop']      = 'clip_document';
            $links[] = PHPWS_Text::moduleLink(dgettext('filecabinet', 'Clip'), 'filecabinet', $vars);
            
            $vars['dop'] = 'delete_document';
            $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this document?');
            $js['LINK'] = dgettext('filecabinet', 'Delete');
            $js['ADDRESS'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);
            $links[] = javascript('confirm', $js);
        }

        if ($links) {
            $tpl['ACTION'] = implode(' | ', $links);
        } else {
            $tpl['ACTION'] = $this->getViewLink(true, 'download');
        }

        $tpl['TITLE'] = $this->getViewLink(true, true);
        return $tpl;
    }

    function save($write=true)
    {
        if (empty($this->file_directory)) {
            if ($this->folder_id) {
                $folder = new Folder($_POST['folder_id']);
                if ($folder->id) {
                    $this->setDirectory($folder->getFullDirectory());
                } else {
                    return PHPWS_Error::get(FC_MISSING_FOLDER, 'filecabinet', 'PHPWS_Document::save');
                }
            } else {
                return PHPWS_Error::get(FC_DIRECTORY_NOT_SET, 'filecabinet', 'PHPWS_Document::save');
            }
        }

        if (!is_writable($this->file_directory)) {
            return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Document::save', $this->file_directory);
        }

        if (empty($this->title)) {
            $this->title = $this->file_name;
        }

        if ($write) {
            if (!is_writable($this->file_directory)) {
                return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Document::save', $this->file_directory);
            }

            if (!$this->id && is_file($this->getPath())) {
                return PHPWS_Error::get(FC_DUPLICATE_FILE, 'filecabinet', 'PHPWS_Document::save', $this->getPath());
            }

            $result = $this->write(false);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        $db = new PHPWS_DB('documents');
        return $db->saveObject($this);
    }

    function delete()
    {
        if (!$this->id) {
            return FALSE;
        }
        
        $file_dir = $this->file_directory . $this->file_name;
        
        // if the file is not there, we want to continue anyway
        if (is_file($file_dir)) {
            if (!@unlink($file_dir)) {
                return PHPWS_Error::get(FC_COULD_NOT_DELETE, 'filecabinet', 'Document::delete', $file_dir);
            }
        }

        $db = new PHPWS_DB('documents');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    function managerTpl($fmanager)
    {
        $tpl['ICON'] = $this->getManagerIcon($fmanager);
        $title_len = strlen($this->title);
        if ($title_len > 20) {
            $file_name = sprintf('<abbr title="%s">%s</abbr>', $this->file_name,
                                 PHPWS_Text::shortenUrl($this->file_name, 20));
        } else {
            $file_name = & $this->file_name;
        } 
        $tpl['TITLE'] = PHPWS_Text::shortenUrl($this->title, 30);

        $filename_len = strlen($this->file_name);

        if ($filename_len > 20) {
            $file_name = sprintf('<abbr title="%s">%s</abbr>', $this->file_name,
                                 PHPWS_Text::shortenUrl($this->file_name, 20));
        } else {
            $file_name = & $this->file_name;
        }

        $tpl['INFO'] = sprintf('%s<br>%s', $file_name, $this->getSize(true));
        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $links[] = $this->editLink(true);
            $links[] = $this->deleteLink(true);
            $tpl['LINKS'] = implode(' ', $links);
        }
        return $tpl;
    }

    function deleteLink($icon=false)
    {
        $vars['dop']         = 'delete_document';
        $vars['document_id'] = $this->id;
        $vars['folder_id']   = $this->folder_id;

        $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this document?');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', $vars, true);

        if ($icon) {
            $js['LINK'] = '<img src="images/mod/filecabinet/delete.png" />';
        } else {
            $js['LINK'] = dgettext('filecabinet', 'Delete');
        }
        return javascript('confirm', $js);
    }
    
    function editLink($icon=false)
    {
        $vars['document_id'] = $this->id;
        $vars['dop'] = 'upload_document_form';
        $js['address'] = PHPWS_Text::linkAddress('filecabinet', $vars, true);

        $js['width'] = 550;
        $js['height'] = 430;

        if ($icon) {
            $js['label'] =sprintf('<img src="images/mod/filecabinet/edit.png" width="16" height="16" title="%s" />', dgettext('filecabinet', 'Edit document'));
        } else {
            $js['label'] = dgettext('filecabinet', 'Edit');
        }
        return javascript('open_window', $js);
    }

    function getManagerIcon($fmanager)
    {
        $vars = $fmanager->linkInfo(false);
        $vars['fop']       = 'pick_file';
        $vars['file_type'] = FC_DOCUMENT;
        $vars['id']        = $this->id;
        $link = PHPWS_Text::linkAddress('filecabinet', $vars, true);
        return sprintf('<a href="%s">%s</a>', $link, $this->getIconView());
    }

    function getTag()
    {
        return $this->downloadLink();
    }
    
    function downloadLink()
    {
        $tpl['files'][] = array('TITLE'=>$this->getViewLink(true), 'SIZE'=>$this->getSize(true));
        $tpl['ICON'] = $this->getViewLink(true, 'icon');
        $tpl['DOWNLOAD'] = dgettext('filecabinet', 'Download file');
        return PHPWS_Template::process($tpl, 'filecabinet', 'document_download.tpl');
    }

}

?>