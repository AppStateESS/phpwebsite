<?php

/**
 * This class is for all files that are not images
 *
 * At some time there may be special circumstances for documents but
 * for now they are just download links.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::requireConfig('filecabinet');
Core\Core::initModClass('filecabinet', 'File_Common.php');

class PHPWS_Document extends File_Common {
    public $downloaded = 0;
    public $_classtype = 'document';

    function __construct($id=NULL)
    {
        $this->loadAllowedTypes();
        $this->setMaxSize(Core\Settings::get('filecabinet', 'max_document_size'));

        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (Core\Error::isError($result)) {
            $this->id = 0;
            $this->_errors[] = $result;
        } elseif (empty($result)) {
            $this->id = 0;
            $this->_errors[] = Core\Error::get(FC_IMG_NOT_FOUND, 'filecabinet', 'PHPWS_Image');
        }
        $this->loadExtension();
    }

    public function init()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new Core\DB('documents');
        return $db->loadObject($this);
    }


    public function getIconView($mode='icon')
    {
        static $icon_list = NULL;

        if (empty($icon_list)) {
            $file = Core\Core::getConfigFile('filecabinet', 'icons.php');
            if (!$file) {
                return sprintf('<img class="fc-mime-icon" src="' . PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/mime_types/text.png" title="%s" alt="%s" />', htmlspecialchars($this->title, ENT_QUOTES), htmlspecialchars($this->title, ENT_QUOTES));
            } else {
                include $file;
            }
        }

        if (!@$graphic = $icon_list[$this->file_type]) {
            if ($mode == 'small_icon') {
                return sprintf('<img class="fc-mime-icon" src="' . PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/mime_types/s_text.png" title="%s" alt="%s" />', htmlspecialchars($this->title, ENT_QUOTES), htmlspecialchars($this->title, ENT_QUOTES));
            } else {
                return sprintf('<img class="fc-mime-icon" src="' . PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/mime_types/text.png" title="%s" alt="%s" />', htmlspecialchars($this->title, ENT_QUOTES), htmlspecialchars($this->title, ENT_QUOTES));
            }
        } else {
            if ($mode == 'small_icon') {
                $graphic = 's_' . $graphic;
            }
            return sprintf('<img class="fc-mime-icon" src="' . PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/mime_types/%s" title="%s" alt="%s" />', $graphic, htmlspecialchars($this->title, ENT_QUOTES), htmlspecialchars($this->title, ENT_QUOTES));
        }
    }

    /**
     * Returns the download path if document is under the install directory.
     * Returns null otherwise.
     */
    public function getDownloadPath()
    {
        $path = $this->getPath();

        $testpath = preg_quote(PHPWS_HOME_DIR);

        if (preg_match("@$testpath@", $path)) {
            return str_replace(PHPWS_HOME_DIR, '', $this->getPath());
        } else {
            return null;
        }
    }

    public function getViewLink($format=false, $type='title', $base=false)
    {
        if (MOD_REWRITE_ENABLED) {
            $link = 'filecabinet/' . $this->id;
        } else {
            $link = sprintf('index.php?module=filecabinet&amp;id=' . $this->id);
        }

        if ($base) {
            $link = PHPWS_HOME_HTTP . $link;
        }

        if ($format) {
            switch ($type) {
                case 'small_icon':
                case 'icon':
                    return sprintf('<a href="%s">%s</a>', $link, $this->getIconView($type));

                case 'download':
                case 'filename':
                    return sprintf('<a href="%s">%s</a>', $link, $this->file_name);

                default:
                case 'title':
                    return sprintf('<a href="%s">%s</a>', $link, htmlspecialchars($this->title, ENT_QUOTES));
            }
        } else {
            return $link;
        }
    }

    public function loadAllowedTypes()
    {
        $this->_allowed_types = explode(',', Core\Settings::get('filecabinet', 'document_files'));
    }

    public function allowDocumentType($type)
    {
        $document = new PHPWS_Document;
        return $document->allowType($type);
    }


    public function pinTags()
    {
        $tpl['TN'] = $this->getViewLink(true, 'icon');
        $tpl['TITLE'] = $this->getViewLink(true);

        return $tpl;
    }

    public function rowTags()
    {
        $links = null;

        $tpl['SIZE'] = $this->getSize(true);

        $tpl['ICON']      = $this->getViewLink(true, 'small_icon');
        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $links[] = $this->editLink(true);

            $vars['document_id'] = $this->id;
            $vars['dop']      = 'clip_document';
            $clip = sprintf('<img src="%smod/filecabinet/img/clip.png" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('filecabinet', 'Clip document'));
            $links[] = Core\Text::moduleLink($clip, 'filecabinet', $vars);
            $links[] = $this->deleteLink(true);
        }

        if ($links) {
            $tpl['ACTION'] = implode('', $links);
        } else {
            $tpl['ACTION'] = null;
        }
        $tpl['FILE_TYPE'] = $this->getFileType(true);
        $tpl['DOWNLOADED'] = $this->downloaded;

        $tpl['TITLE'] = $this->getViewLink(true);
        return $tpl;
    }

    public function save($write=true)
    {
        if (empty($this->file_directory)) {
            if ($this->folder_id) {
                $folder = new Folder($_POST['folder_id']);
                if ($folder->id) {
                    $this->setDirectory($folder->getFullDirectory());
                } else {
                    return Core\Error::get(FC_MISSING_FOLDER, 'filecabinet', 'PHPWS_Document::save');
                }
            } else {
                return Core\Error::get(FC_DIRECTORY_NOT_SET, 'filecabinet', 'PHPWS_Document::save');
            }
        }

        if (!is_writable($this->file_directory)) {
            return Core\Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Document::save', $this->file_directory);
        }

        if (empty($this->title)) {
            $this->title = $this->file_name;
        }

        if ($write) {
            $result = $this->write(false);
            if (Core\Error::isError($result)) {
                return $result;
            }
        }

        $db = new Core\DB('documents');
        return $db->saveObject($this);
    }

    public function delete()
    {
        return $this->commonDelete();
    }

    public function managerTpl($fmanager)
    {
        $tpl['ICON'] = $this->getManagerIcon($fmanager);
        $tpl['TITLE'] = $this->getTitle(true);

        $filename_len = strlen($this->file_name);

        if ($filename_len > 20) {
            $file_name = sprintf('<abbr title="%s">%s</abbr>', $this->file_name,
            Core\Text::shortenUrl($this->file_name, 20));
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

    public function deleteLink($icon=false)
    {
        $vars['document_id'] = $this->id;
        $vars['folder_id']   = $this->folder_id;
        $vars['dop'] = 'delete_document';
        $link = new Core\Link(null, 'filecabinet', $vars, true);
        $link->setSalted(1);
        $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this document?');

        $js['ADDRESS'] = $link->getAddress();

        if ($icon) {
            $js['LINK'] = Core\Icon::show('delete', dgettext('filecabinet', 'Delete document'));
        } else {
            $js['LINK'] = dgettext('filecabinet', 'Delete');
        }

        return javascript('confirm', $js);
    }

    public function editLink($icon=false)
    {
        $vars['document_id'] = $this->id;
        $vars['folder_id']   = $this->folder_id;
        $vars['dop'] = 'upload_document_form';
        $link = new Core\Link(null, 'filecabinet', $vars, true);
        $link->setSalted(1);

        $js['address'] = $link->getAddress();
        $js['width'] = 550;
        $js['height'] = 500;

        if ($icon) {
            $js['label'] = Core\Icon::show('edit', dgettext('filecabinet', 'Edit document'));
        } else {
            $js['label'] = dgettext('filecabinet', 'Edit');
        }
        return javascript('open_window', $js);
    }


    public function getManagerIcon($fmanager)
    {
        $vars = $fmanager->linkInfo(false);
        $vars['fop']       = 'pick_file';
        $vars['file_type'] = FC_DOCUMENT;
        $vars['id']        = $this->id;
        $link = Core\Text::linkAddress('filecabinet', $vars, true);
        return sprintf('<a href="%s">%s</a>', $link, $this->getIconView());
    }

    public function getTag($return_tpl=false, $small_icon=false)
    {
        $tpl['TITLE']    = $this->getViewLink(true);
        $tpl['SIZE']     = $this->getSize(true);
        if ($small_icon) {
            $tpl['ICON']     = $this->getViewLink(true, 'small_icon');
        } else {
            $tpl['ICON']     = $this->getViewLink(true, 'icon');
        }
        $tpl['TYPE']     = $this->getFileType(true);
        $tpl['DESCRIPTION'] = $this->getDescription();
        $tpl['DOWNLOAD'] = dgettext('filecabinet', 'Download file');
        if ($return_tpl) {
            return $tpl;
        } else {
            return Core\Template::process($tpl, 'filecabinet', 'document_download.tpl');
        }
    }

    public function deleteAssoc()
    {
        $db = new Core\DB('fc_file_assoc');
        $db->addWhere('file_type', FC_DOCUMENT);
        $db->addWhere('file_id', $this->id);
        return $db->delete();
    }

}

?>