<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class FC_File_Assoc {
    var $id         = 0;
    var $file_type  = 0;
    var $file_id    = 0;
    var $resize     = null;
    var $_use_style = true;
    /**
     * If the file assoc is an image and no_link is true,
     * the image's default link (if any) will be supressed
     */
    var $_source        = null;
    var $_resize_parent = null;
    var $_link_image    = true;
    var $_allow_caption = true;
    var $_file_path     = null;

    function FC_File_Assoc($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $db = new PHPWS_DB('fc_file_assoc');
        $result = $db->loadObject($this);
        if (!PHPWS_Error::logIfError($result)) {
            if (!$result) {
                $this->id = 0;
            }
        }
        $this->loadSource();
        if ($this->_source) {
            $this->_file_path = $this->_source->getPath();
        }
    }

    function getSource()
    {
        return $this->_source;
    }

    function loadSource()
    {
        switch ($this->file_type) {
        case FC_IMAGE:
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $this->_source = new PHPWS_Image($this->file_id);
            break;

        case FC_DOCUMENT:
            PHPWS_Core::initModClass('filecabinet', 'Document.php');
            $this->_source = new PHPWS_Document($this->file_id);
            break;

        case FC_MEDIA:
        case FC_MEDIA_RESIZE:
            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $this->_source = new PHPWS_Multimedia($this->file_id);
            //            test($this,1);
            break;

        case FC_IMAGE_RESIZE:
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $this->_resize_parent = new PHPWS_Image($this->file_id);
            if (!$this->_resize_parent->id) {
                $this->_resize_parent = null;
                return;
            }
            $this->_source = clone($this->_resize_parent);
            $this->_source->file_directory = $this->_resize_parent->getResizePath();
            $this->_source->file_name = $this->resize;
            $this->_source->loadDimensions();
            break;

        case FC_IMAGE_RANDOM:
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $image = new PHPWS_Image;
            $db = new PHPWS_DB('images');
            $db->addWhere('folder_id', $this->file_id);
            $db->addorder('random');
            $db->setLimit(1);
            if ($db->loadObject($image)) {
                $this->_source = $image;
            }
            break;

        default:
            return;
        }
        $this->_file_path = $this->_source->getPath();
    }

    function parentLinked($thumbnail=false)
    {
        if ($this->file_type != FC_IMAGE_RESIZE || !$this->_resize_parent) {
            $this->_link_image = true;
            if ($thumbnail) {
                return $this->_resize_parent->getThumbnail();
            } else {
                return $this->getTag();
            }
        }

        if ($thumbnail) {
            $img = $this->_resize_parent->getThumbnail();
        } elseif (PHPWS_Settings::get('filecabinet', 'caption_images') && $this->_allow_caption) {
            $img = $this->_source->captioned(null, false);
        } else {
            $img = $this->_source->getTag(null, false);
        }

        return $this->_resize_parent->getJSView(false, $img);
    }

    function allowImageLink($link=true)
    {
        $this->_link_image = (bool)$link;
    }

    function isImage($include_resize=true)
    {
        if ($include_resize) {
            return ($this->file_type == FC_IMAGE || $this->file_type == FC_IMAGE_RESIZE);
        } else {
            return ($this->file_type == FC_IMAGE);
        }
    }

    function isDocument()
    {
        return ($this->file_type == FC_DOCUMENT);
    }

    function isMedia()
    {
        return ($this->file_type == FC_MEDIA);
    }

    function isResize()
    {
        return ($this->file_type == FC_IMAGE_RESIZE);
    }

    function allowCaption($allow=true)
    {
        $this->_allow_caption = (bool)$allow;
    }

    function deadAssoc()
    {
        $this->delete();
        $this->id        = 0;
        $this->file_type = 0;
        $this->file_id   = 0;
        $this->resize    = null;
    }

    function getFolderType()
    {
        switch ($this->file_type) {
        case FC_IMAGE:
        case FC_IMAGE_FOLDER:
        case FC_IMAGE_RANDOM:
        case FC_IMAGE_RESIZE:
            return IMAGE_FOLDER;

        case FC_DOCUMENT:
        case FC_DOCUMENT_FOLDER:
            return DOCUMENT_FOLDER;

        case FC_MEDIA:
            return MULTIMEDIA_FOLDER;
        }
    }

    /**
     * Returns an image, media, or document path.
     * Does not work with random images or folder listing
     */
    function getPath()
    {
        return $this->_file_path;
    }

    function getThumbnail()
    {
        PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        PHPWS_Core::initModClass('filecabinet', 'Document.php');

        switch ($this->file_type) {
        case FC_IMAGE:
        case FC_IMAGE_RANDOM:
            return $this->_source->getThumbnail(null, $this->_link_image);

        case FC_IMAGE_RESIZE:
            return $this->_resize_parent->getThumbnail(null, $this->_link_image);

        case FC_DOCUMENT:
            return $this->_source->getIconView();
            break;

        case FC_MEDIA:
        case FC_MEDIA_RESIZE:
            return $this->_source->getThumbnail();
            break;
        }
    }

    function getTag($embed=false)
    {
        PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        PHPWS_Core::initModClass('filecabinet', 'Document.php');

        if ($this->_use_style) {
            Layout::addStyle('filecabinet', 'file_view.css');
        }

        switch ($this->file_type) {
        case FC_IMAGE:
        case FC_IMAGE_RANDOM:
            if ($this->_source->id) {
                if (PHPWS_Settings::get('filecabinet', 'caption_images') && $this->_allow_caption) {
                    return $this->_source->captioned(null, $this->_link_image);
                } else {
                    return $this->_source->getTag(null, $this->_link_image);
                }
            } else {
                $this->deadAssoc();
            }
            break;

        case FC_IMAGE_RESIZE:
            if ($this->_source->id) {
                if (PHPWS_Settings::get('filecabinet', 'caption_images') && $this->_allow_caption) {
                    return $this->_source->captioned(null, $this->_link_image);
                } else {
                    return $this->_source->getTag(null, $this->_link_image);
                }
            } else {
                $this->deadAssoc();
            }
            break;

        case FC_IMAGE_FOLDER:
            return $this->slideshow();

        case FC_MEDIA_RESIZE:
            $this->setMediaDimensions();
        case FC_DOCUMENT:
        case FC_MEDIA:
            if ($this->_source->id) {
                return $this->_source->getTag($embed);
            } else {
                $this->deadAssoc();
            }
            break;

        case FC_DOCUMENT_FOLDER:
            return $this->documentFolder();
        }
        return null;
    }

    function documentFolder()
    {
        $folder = new Folder($this->file_id);
        $folder->loadFiles();
        foreach ($folder->_files as $document) {
            $tpl['files'][] = array('TITLE'=>$document->getViewLink(true), 'SIZE'=>$document->getSize(true));
        }
        $tpl['ICON'] = '<img src="images/mod/filecabinet/file_manager/folder_contents.png" />';
        $tpl['DOWNLOAD'] = sprintf(dgettext('filecabinet', 'Download from %s'), $folder->title);
        return PHPWS_Template::process($tpl, 'filecabinet', 'multi_doc_download.tpl');
    }

    function randomImage()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $image = new PHPWS_Image;
        $db = new PHPWS_DB('images');
        $db->addWhere('folder_id', $this->file_id);
        $db->addorder('random');
        $db->setLimit(1);
        if ($db->loadObject($image)) {
            $this->_file_path = $image->getPath();
            return $image->getTag();
        } else {
            return dgettext('filecabinet', 'Folder missing image files.');
        }
    }

    function slideshow()
    {
        Layout::addStyle('filecabinet', 'style.css');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $db = new PHPWS_DB('images');
        $db->addWhere('folder_id', $this->file_id);

        $result = $db->getObjects('PHPWS_Image');
        if (PHPWS_Error::logIfError($result) || !$result) {
            return dgettext('filecabinet', 'Folder missing image files.');
        } else {
            foreach ($result as $image) {
                $tpl['thumbnails'][] = array('IMAGE'=> $image->getJSView(true));
            }
            return PHPWS_Template::process($tpl, 'filecabinet', 'ss_box.tpl');
        }
    }

    function getTable()
    {
        switch ($this->file_type) {
        case FC_IMAGE:
        case FC_IMAGE_FOLDER:
        case FC_IMAGE_RANDOM:
        case FC_IMAGE_RESIZE:
            return 'images';

        case FC_DOCUMENT:
        case FC_DOCUMENT_FOLDER:
            return 'documents';

        case FC_MEDIA:
            return 'multimedia';
        }

    }

    function getFolder()
    {
        $db = new PHPWS_DB('folders');
        if ($this->file_type == FC_IMAGE_RANDOM || $this->file_type == FC_IMAGE_FOLDER
            || $this->file_type == FC_DOCUMENT_FOLDER) {
            $folder = new Folder($this->file_id);
            if (PHPWS_Error::logIfError($folder) || !$folder->id) {
                return false;
            } else {
                return $folder;
            }
        } else {
            $table = $this->getTable();
            $folder = new Folder;
            $db->addWhere('fc_file_assoc.id', $this->id);
            $db->addWhere('fc_file_assoc.file_id', "$table.id");
            $db->addWhere('folders.id', "$table.folder_id");

            $result = $db->loadObject($folder);
            if (PHPWS_Error::logIfError($result) || !$result) {
                return false;
            } else {
                return $folder;
            }
        }
    }

    function save()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        return $db->saveObject($this);
    }

    function updateTag($file_type, $id, $tag)
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('ftype', (int)$file_type);
        $db->addWhere('file_id', (int)$id);
        $db->addValue('tag',  htmlentities($tag, ENT_QUOTES, 'UTF-8'));
        $db->update();
    }

    function imageFolderView()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $db = new PHPWS_DB('images');
        $db->addWhere('folder_id', $this->file_id);
        $result = $db->getObjects('PHPWS_Image');
    }

    function delete()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    function setMediaDimensions()
    {
        if (empty($this->resize)) {
            $this->file_type = 3;
            return;
        }

        $dim = explode('x', $this->resize);
        $max_width = &$dim[0];
        $max_height = &$dim[1];

        $width = $this->_source->width;
        $height = $this->_source->height;

        if ($max_width >= $width && $max_height >= $height) {
            $this->file_type = 3;
            return;
        }

        if ($max_width < $width || !$max_height) {
            $new_width = $max_width;
            $new_height = floor($height / ($width / $new_width));
        } else {
            $new_height = $max_height;
            $new_width = floor($width / ($height / $new_height));
        }

        $this->_source->width = $new_width;
        $this->_source->height = $new_height;
    }

}

?>