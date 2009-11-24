<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::requireInc('filecabinet', 'defines.php');

class FC_File_Assoc {
    public $id         = 0;
    public $file_type  = 0;
    public $file_id    = 0;
    public $resize     = null;
    public $width      = 0;
    public $height     = 0;

    /**
     * Used with carousel, determines direction
     */
    public $vertical   = 0;

    /**
     * Used with carousel, determines number images seen
     */
    public $num_visible = 3;

    public $_use_style = true;
    /**
     * If the file assoc is an image and no_link is true,
     * the image's default link (if any) will be supressed
     */
    public $_source        = null;
    public $_resize_parent = null;
    public $_link_image    = true;
    public $_allow_caption = true;
    public $_file_path     = null;

    public function __construct($id=0)
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

    public function getSource()
    {
        return $this->_source;
    }

    public function loadSource()
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
                break;

            case FC_IMAGE_RESIZE:
            case FC_IMAGE_CROP:
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

    public function parentLinked($thumbnail=false)
    {
        if ( ($this->file_type != FC_IMAGE_RESIZE && $this->file_type != FC_IMAGE_CROP) || !$this->_resize_parent) {
            $this->_link_image = true;
            if ($thumbnail) {
                if ($this->_resize_parent) {
                    $img = $this->_resize_parent->getThumbnail();
                    return $this->_resize_parent->getJSView(false, $img);
                } else {
                    $img = $this->_source->getThumbnail();
                    return $this->_source->getJSView(false, $img);
                }
            } else {
                $img = $this->getTag();
                return $this->_source->getJSView(false, $img);
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

    public function allowImageLink($link=true)
    {
        $this->_link_image = (bool)$link;
    }

    public function isImage($include_resize=true)
    {
        if ($include_resize) {
            return ($this->file_type == FC_IMAGE || $this->file_type == FC_IMAGE_RESIZE ||
            $this->file_type == FC_IMAGE_CROP);
        } else {
            return ($this->file_type == FC_IMAGE);
        }
    }

    public function isDocument()
    {
        return ($this->file_type == FC_DOCUMENT);
    }

    public function isMedia()
    {
        return ($this->file_type == FC_MEDIA);
    }

    public function isResize()
    {
        return ($this->file_type == FC_IMAGE_RESIZE);
    }

    public function isCrop()
    {
        return ($this->file_type == FC_IMAGE_CROP);
    }

    public function allowCaption($allow=true)
    {
        $this->_allow_caption = (bool)$allow;
    }

    public function deadAssoc()
    {
        $this->delete();
        $this->id        = 0;
        $this->file_type = 0;
        $this->file_id   = 0;
        $this->resize    = null;
    }

    public function getFolderType()
    {
        switch ($this->file_type) {
            case FC_IMAGE:
            case FC_IMAGE_FOLDER:
            case FC_IMAGE_LIGHTBOX:
            case FC_IMAGE_RANDOM:
            case FC_IMAGE_RESIZE:
            case FC_IMAGE_CROP:
                return IMAGE_FOLDER;

            case FC_DOCUMENT:
            case FC_DOCUMENT_FOLDER:
                return DOCUMENT_FOLDER;

            case FC_MEDIA:
            case FC_MEDIA_RESIZE:
                return MULTIMEDIA_FOLDER;
        }
    }

    /**
     * Returns an image, media, or document path.
     * Does not work with random images or folder listing
     */
    public function getPath()
    {
        return $this->_file_path;
    }

    public function getThumbnail()
    {
        PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        PHPWS_Core::initModClass('filecabinet', 'Document.php');

        switch ($this->file_type) {
            case FC_IMAGE:
            case FC_IMAGE_RANDOM:
                return $this->_source->getThumbnail(null, $this->_link_image);

            case FC_IMAGE_RESIZE:
            case FC_IMAGE_CROP:
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

    public function getTag($embed=false, $base=false)
    {
        PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        PHPWS_Core::initModClass('filecabinet', 'Document.php');

        if ($this->_use_style) {
            PHPWS_Core::initModClass('layout', 'Layout.php');
            Layout::addStyle('filecabinet', 'file_view.css');
        }

        switch ($this->file_type) {
            case FC_IMAGE:
            case FC_IMAGE_RANDOM:
                if ($this->_source->id) {
                    if (PHPWS_Settings::get('filecabinet', 'caption_images') && $this->_allow_caption) {
                        return $this->_source->captioned(null, $this->_link_image, $base);
                    } else {
                        return $this->_source->getTag(null, $this->_link_image, $base);
                    }
                } else {
                    $this->deadAssoc();
                }
                break;

            case FC_IMAGE_RESIZE:
            case FC_IMAGE_CROP:
                if ($this->_source->id) {
                    if (PHPWS_Settings::get('filecabinet', 'caption_images') && $this->_allow_caption) {
                        return $this->_source->captioned(null, $this->_link_image, $base);
                    } else {
                        return $this->_source->getTag(null, $this->_link_image, $base);
                    }
                } else {
                    $this->deadAssoc();
                }
                break;

            case FC_IMAGE_FOLDER:
                return $this->slideshow();

            case FC_IMAGE_LIGHTBOX:
                return $this->lightbox();

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

    public function documentFolder()
    {
        $folder = new Folder($this->file_id);
        $folder->loadFiles();
        foreach ($folder->_files as $document) {
            $tpl['files'][] = $document->getTag(true, true);
        }

        $tpl['DOWNLOAD'] = sprintf(dgettext('filecabinet', 'Download from %s'), $folder->title);
        return PHPWS_Template::process($tpl, 'filecabinet', 'multi_doc_download.tpl');
    }

    public function randomImage()
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

    public function slideshow()
    {
        static $count = 0;

        $count++;
        Layout::addStyle('filecabinet');
        $message = null;
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $folder = new Folder($this->file_id);
        if (!$folder->public_folder) {
            if (!Current_User::allow('filecabinet')) {
                return null;
            } else {
                $message = dgettext('filecabinet', 'Folder is private. Slideshow not available');
            }
        }
        $db = new PHPWS_DB('images');
        $db->addWhere('folder_id', $this->file_id);

        $result = $db->getObjects('PHPWS_Image');
        if (PHPWS_Error::logIfError($result) || !$result) {
            return dgettext('filecabinet', 'Folder missing image files.');
        } else {
            foreach ($result as $image) {
                $tpl['thumbnails'][] = array('IMAGE'=> sprintf('<a href="%s">%s</a>', $image->getPath(), $image->getThumbnail()));
            }

            $this->loadCarousel($count);

            // Safari skips the final thumbnail
            if ($GLOBALS['browser'] == 'Safari') {
                $tpl['STUB'] = '<li></li>';
            }

            $tpl['CARO_ID'] = "caro-$count";
            $tpl_file = 'carousel.tpl';
            return PHPWS_Template::process($tpl, 'filecabinet', $tpl_file);
        }
    }

    public function loadCarousel($count)
    {
        static $repeats = array();
        javascript('jquery');
        $max_size = PHPWS_Settings::get('filecabinet', 'max_thumbnail_size');
        $total_size = $this->getTotalCarouselSize();

        $svars['TOTAL_SIZE'] = $total_size;
        $svars['CARO_ID'] = "caro-$count";
        $repeats['style-repeat'][] = $svars;

        $vars['TOTAL_SIZE'] = $total_size;
        $vars['CARO_ID'] = "caro-$count";
        $vars['HEIGHT'] = $max_size;
        $vars['WIDTH'] = $max_size;
        $vars['SCROLL'] = $this->num_visible;
        $vars['VERTICAL'] = $this->vertical ? 'true' : 'false';
        $vars['ARROW_POSITION'] = floor($max_size/2) + 5;
        $repeats['js-repeat'][] = $vars;
        javascript('modules/filecabinet/jcarousel/', $repeats);
    }


    public function getTotalCarouselSize()
    {
        $max_size = PHPWS_Settings::get('filecabinet', 'max_thumbnail_size');
        return ($max_size * $this->num_visible) + ($this->num_visible * 10);
    }

    public function lightbox()
    {
        $message = null;
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $folder = new Folder($this->file_id);
        if (!$folder->public_folder) {
            if (!Current_User::allow('filecabinet')) {
                return null;
            } else {
                $message = dgettext('filecabinet', 'Folder is private. Slideshow not available');
            }
        }
        $db = new PHPWS_DB('images');
        $db->addWhere('folder_id', $this->file_id);
        if ($this->num_visible < 99) {
            $db->addOrder('rand');
            $db->setLimit($this->num_visible);
        }

        $result = $db->getObjects('PHPWS_Image');
        if (PHPWS_Error::logIfError($result) || !$result) {
            return dgettext('filecabinet', 'Folder missing image files.');
        } else {
            foreach ($result as $image) {
                $img = sprintf('<a href="%s">%s</a>', $image->getPath(), $image->getThumbnail());
                $tpl['thumbnails'][] = array('IMAGE' => $img);
            }
            $this->loadLightbox();
            if ($this->vertical) {
                $tpl_file = 'lightbox_vert.tpl';
            } else {
                $tpl_file = 'lightbox_horz.tpl';
            }
            if ($message) {
                $tpl['MESSAGE'] = $message;
            }
            return PHPWS_Template::process($tpl, 'filecabinet', $tpl_file);
        }
    }

    public function loadLightbox()
    {
        javascript('jquery');
        $vars = null;
        $vars['txtImage'] = dgettext('filecabinet', 'Image');
        $vars['txtOf']  = dgettext('filecabinet', 'of');
        javascript('modules/filecabinet/lightbox/', $vars);
    }

    public function getTable()
    {
        switch ($this->file_type) {
            case FC_IMAGE:
            case FC_IMAGE_FOLDER:
            case FC_IMAGE_LIGHTBOX:
            case FC_IMAGE_RANDOM:
            case FC_IMAGE_RESIZE:
            case FC_IMAGE_CROP:
                return 'images';

            case FC_DOCUMENT:
            case FC_DOCUMENT_FOLDER:
                return 'documents';

            case FC_MEDIA:
            case FC_MEDIA_RESIZE:
                return 'multimedia';
        }

    }

    public function getFolder()
    {
        $db = new PHPWS_DB('folders');
        if ($this->file_type == FC_IMAGE_RANDOM || $this->file_type == FC_IMAGE_FOLDER || $this->file_type == FC_IMAGE_LIGHTBOX
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

    public function save()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        return $db->saveObject($this);
    }

    public function updateTag($file_type, $id, $tag)
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('ftype', (int)$file_type);
        $db->addWhere('file_id', (int)$id);
        $db->addValue('tag',  htmlentities($tag, ENT_QUOTES, 'UTF-8'));
        $db->update();
    }

    public function imageFolderView()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $db = new PHPWS_DB('images');
        $db->addWhere('folder_id', $this->file_id);
        $result = $db->getObjects('PHPWS_Image');
    }

    public function delete()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    public function setMediaDimensions()
    {
        $max_width = $this->width;
        $max_height = $this->height;

        $width = $this->_source->width;
        $height = $this->_source->height;
        if ($max_width >= $width && $max_height >= $height) {
            $this->file_type = 3;
            return;
        }

        if (($width && $max_width) && ($max_width < $width || !$max_height)) {
            $new_width = $max_width;
            $new_height = floor($height / ($width / $new_width));
        } elseif ($height && $max_height) {
            $new_height = $max_height;
            $new_width = floor($width / ($height / $new_height));
        } else {
            // prevents a possible divide by zero
            return;
        }

        $this->_source->width = $new_width;
        $this->_source->height = $new_height;
    }

}

?>