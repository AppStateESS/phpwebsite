<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'File_Common.php');

define('GENERIC_VIDEO_ICON', PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/video_generic.jpg');
define('GENERIC_AUDIO_ICON', PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/audio.png');

class PHPWS_Multimedia extends File_Common {
    public $width     = 0;
    public $height    = 0;
    public $thumbnail = null;
    /**
     * In seconds
     */
    public $duration  = 0;
    public $embedded  = 0;
    public $_classtype       = 'multimedia';

    public function __construct($id=0)
    {
        $this->loadAllowedTypes();
        $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_multimedia_size'));

        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();

        if (PHPWS_Error::isError($result)) {
            $this->id = 0;
            $this->_errors[] = $result;
        } elseif (empty($result)) {
            $this->id = 0;
            $this->_errors[] = PHPWS_Error::get(FC_MULTIMEDIA_NOT_FOUND, 'filecabinet', 'PHPWS_Multimedia');
        }
        $this->loadExtension();
    }

    public function init()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new PHPWS_DB('multimedia');
        return $db->loadObject($this);
    }


    public function loadAllowedTypes()
    {
        $this->_allowed_types = explode(',', PHPWS_Settings::get('filecabinet', 'media_files'));
    }

    public function getID3()
    {
        require_once PHPWS_SOURCE_DIR . 'lib/getid3/getid3/getid3.php';
        $getID3 = new getID3;

        // File to get info from
        $file_location = $this->getPath();
        // Get information from the file
        $fileinfo = $getID3->analyze($file_location);
        getid3_lib::CopyTagsToComments($fileinfo);
        return $fileinfo;
    }

    public function loadDimensions()
    {
        $fileinfo = $this->getID3();
        if (isset($fileinfo['video']['resolution_x'])) {
            $this->width = & $fileinfo['video']['resolution_x'];
            $this->height = & $fileinfo['video']['resolution_y'];
        } elseif (isset($fileinfo['video']['streams'][2]['resolution_x'])) {
            $this->width = & $fileinfo['video']['streams'][2]['resolution_x'];
            $this->height = & $fileinfo['video']['streams'][2]['resolution_y'];
        } else {
            $this->width = PHPWS_Settings::get('filecabinet', 'default_mm_width');
            $this->height = PHPWS_Settings::get('filecabinet', 'default_mm_height');
        }

        $this->duration = (int)$fileinfo['playtime_seconds'];
    }


    public function allowMultimediaType($type)
    {
        $mm = new PHPWS_Multimedia;
        return $mm->allowType($type);
    }

    public function thumbnailDirectory()
    {
        return $this->file_directory . 'tn/';
    }

    public function thumbnailPath()
    {
        if (!$this->thumbnail) {
            return null;
        }
        return $this->thumbnailDirectory() . $this->thumbnail;
    }


    public function rowTags()
    {
        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            $clip = Icon::show('clip', dgettext('filecabinet', 'Clip media'));
            $links[] = PHPWS_Text::secureLink($clip, 'filecabinet',
            array('mop'=>'clip_multimedia',
                                                    'multimedia_id' => $this->id));
            $links[] = $this->editLink(true);
            $links[] = $this->deleteLink(true);
        }

        if (isset($links)) {
            $tpl['ACTION'] = implode('', $links);
        }
        $tpl['SIZE'] = $this->getSize(TRUE);
        $tpl['FILE_NAME'] = $this->file_name;
        $tpl['THUMBNAIL'] = $this->getJSView(true);
        $tpl['TITLE']     = $this->getJSView(false, $this->title);

        if ($this->isVideo()) {
            $tpl['DIMENSIONS'] = sprintf('%s x %s', $this->width, $this->height);
        }

        return $tpl;
    }

    public function popupAddress()
    {
        if (MOD_REWRITE_ENABLED) {
            return sprintf('filecabinet/id/%s/mtype/multimedia', $this->id);
        } else {
            return sprintf('index.php?module=filecabinet&amp;mtype=multimedia&amp;id=%s', $this->id);
        }
    }


    public function popupSize()
    {
        static $sizes = null;

        if (!$this->width) {
            $this->width = 100;
        }

        if (!$this->height) {
            $this->height = 100;
        }

        $dimensions = array(FC_MAX_MULTIMEDIA_POPUP_WIDTH, FC_MAX_MULTIMEDIA_POPUP_HEIGHT);
        if (isset($sizes[$this->id])) {
            return $sizes[$this->id];
        }
        $padded_width = $this->width + 40;
        $padded_height = $this->height + 120;

        if (!empty($this->description)) {
            $padded_height += round( (strlen(strip_tags($this->description)) / ($this->width / 12)) * 12);
        }

        if ( $padded_width < FC_MAX_MULTIMEDIA_POPUP_WIDTH && $padded_height < FC_MAX_MULTIMEDIA_POPUP_HEIGHT ) {
            $final_width = $final_height = 0;

            for ($lmt = 250; $lmt += 50; $lmt < 1300) {
                if (!$final_width && ($padded_width + 25) < $lmt) {
                    $final_width = $lmt;
                }

                if (!$final_height && ($padded_height + 25) < $lmt ) {
                    $final_height = $lmt;
                }

                if ($final_width && $final_height) {
                    $dimensions = array($final_width, $final_height);
                    break;
                }
            }
        }
        $sizes[$this->id] = $dimensions;
        return $dimensions;
    }

    public function getJSView($thumbnail=false, $link_override=null)
    {
        if ($link_override) {
            $values['label'] = $link_override;
        } else {
            if ($thumbnail) {
                $values['label'] = $this->getThumbnail();
            } else {
                $values['label'] = sprintf('<img src="%smod/filecabinet/img/viewmag+.png" title="%s" />', PHPWS_SOURCE_HTTP,
                dgettext('filecabinet', 'View full image'));
            }
        }

        $size = $this->popupSize();
        $values['address']     = $this->popupAddress();
        $values['width']       = $size[0];
        $values['height']      = $size[1];
        $values['window_name'] = 'multimedia_view';
        return Layout::getJavascript('open_window', $values);
    }


    public function editLink($icon=false)
    {
        $vars['mop'] = 'upload_multimedia_form';
        $vars['multimedia_id'] = $this->id;
        $vars['folder_id'] = $this->folder_id;

        $jsvars['width'] = 550;
        $jsvars['height'] = 620;

        $link = new PHPWS_Link(null, 'filecabinet', $vars);
        $link->setSecure();
        $link->setSalted();
        $jsvars['address'] = $link->getAddress();
        $jsvars['window_name'] = 'edit_link';

        if ($icon) {
            $jsvars['label'] = Icon::show('edit', dgettext('filecabinet', 'Edit multimedia file'));
        } else {
            $jsvars['label'] = dgettext('filecabinet', 'Edit');
        }
        return javascript('open_window', $jsvars);

    }

    public function deleteLink($icon=false)
    {
        $vars['mop'] = 'delete_multimedia';
        $vars['multimedia_id'] = $this->id;
        $vars['folder_id'] = $this->folder_id;

        $js['QUESTION'] = dgettext('filecabinet', 'Are you sure you want to delete this multimedia file?');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('filecabinet', $vars, true);

        if ($icon) {
            $js['LINK'] = Icon::show('delete');
        } else {
            $js['LINK'] = dgettext('filecabinet', 'Delete');
        }

        return javascript('confirm', $js);
    }

    public function getTag($embed=false)
    {
        $filter = $this->getFilter();
        $is_video = preg_match('/^audio/i', $this->file_type) ? false : true;
        $tpl['WIDTH']  = $this->width;
        $tpl['HEIGHT'] = $this->height;
        $tpl['IMAGE']  = $this->getThumbnail(null, false);

        $thumbnail = $this->thumbnailPath();

        $tpl['FILE_PATH'] = PHPWS_Core::getHomeHttp() . $this->getPath();
        $tpl['FILE_NAME'] = $this->file_name;
        $tpl['ID'] = 'media' . $this->id;
        $tpl['source_http'] = $tpl['SOURCE_HTTP'] = PHPWS_SOURCE_HTTP;

        // check for filter file
        if ($this->embedded) {
            $filter_tpl = sprintf('%smod/filecabinet/inc/embed/%s/embed.tpl', PHPWS_SOURCE_DIR, $filter);
        } else {
            $filter_exe = PHPWS_SOURCE_DIR . "mod/filecabinet/templates/filters/$filter/filter.php";
            $filter_tpl = PHPWS_SOURCE_DIR . "mod/filecabinet/templates/filters/$filter.tpl";
            if ($embed) {
                if ($filter == 'media') {
                    $filter_tpl = PHPWS_SOURCE_DIR . "mod/filecabinet/templates/filters/media_embed.tpl";
                } elseif ($filter == 'shockwave') {
                    $filter_tpl = PHPWS_SOURCE_DIR . "mod/filecabinet/templates/filters/shockwave_embed.tpl";
                }
            }
            if (is_file($filter_exe)) {
                include $filter_exe;
            }

        }
        return PHPWS_Template::process($tpl, 'filecabinet', $filter_tpl, true);
    }

    public function getFilter()
    {
        if ($this->embedded) {
            return $this->file_type;
        }
        $this->getExtension();
        switch ($this->_ext) {
            case 'flv':
                return 'flowplayer';
                break;

            case 'mp3':
            case 'wav':
                return 'media';
                break;

            case 'qt':
            case 'mov':
                return 'quicktime';
                break;

            case 'mpeg':
            case 'mpe':
            case 'mpg':
            case 'wmv':
            case 'avi':
                return 'windows';
                break;

            case 'swf':
                $this->width = 400;
                $this->height = 400;
                return 'shockwave';
                break;
        }
    }


    public function getThumbnail($css_id=null, $force_resize=true)
    {
        if (empty($css_id)) {
            $css_id = $this->id;
        }
        if ($force_resize) {
            $width =  'width="' . FC_THUMBNAIL_WIDTH . '"';
        } else {
            $width = null;
        }
        return sprintf('<img src="%s" title="%s" id="multimedia-thumbnail-%s"%s />',
        $this->thumbnailPath(), $this->title, $css_id, $width);
    }

    public function tnFileName()
    {
        return $this->thumbnail;
    }

    public function genericTN($file_name)
    {
        $this->thumbnail = $file_name . '.jpg';
        if ($this->file_type == 'application/x-shockwave-flash') {
            return @copy(PHPWS_SOURCE_DIR . 'mod/filecabinet/img/shockwave.jpg', $this->thumbnailDirectory() . $this->thumbnail);
        } else {
            return @copy(PHPWS_SOURCE_DIR . 'mod/filecabinet/img/video_generic.jpg', $this->thumbnailDirectory() . $this->thumbnail);
        }
    }

    public function makeVideoThumbnail()
    {
        $thumbnail_directory = $this->thumbnailDirectory();

        if (!is_writable($thumbnail_directory)) {
            PHPWS_Error::log(FC_THUMBNAIL_NOT_WRITABLE, 'filecabinet',
                             'Multimedia::makeVideoThumbnail', $thumbnail_directory);
            return false;
        }

        $raw_file_name = $this->dropExtension();

        if (!PHPWS_Settings::get('filecabinet', 'use_ffmpeg') ||
        $this->file_type == 'application/x-shockwave-flash') {
            $this->genericTN($raw_file_name);
            return;
        } else {
            $ffmpeg_directory = PHPWS_Settings::get('filecabinet', 'ffmpeg_directory');

            if (!is_file($ffmpeg_directory . 'ffmpeg')) {
                PHPWS_Error::log(FC_FFMPREG_NOT_FOUND, 'filecabinet',
                                 'Multimedia::makeVideoThumbnail', $ffmpeg_directory);
                $this->genericTN($raw_file_name);
                return true;
            }

            $tmp_name = mt_rand();

            $jpeg = $raw_file_name . '.jpg';
            $thumb_path = $thumbnail_directory . $jpeg;

            //$max_size = FC_THUMBNAIL_WIDTH;
            $max_size = $this->width;

            if ($this->width > $this->height) {
                $diff = $max_size / $this->width;
                $new_width = $max_size;
                $new_height = round($this->height * $diff);
            } else {
                $diff = $max_size / $this->height;
                $new_height = $max_size;
                $new_width = round($this->width * $diff);
            }

            /**
             * -i        filename
             * -an       disable audio
             * -ss       seek to position
             * -r        frame rate
             * -vframes  number of video frames to record
             * -y        overwrite output files
             * -f        force format
             */

            $command = sprintf('%sffmpeg -i %s -an -s %sx%s -ss 00:00:05 -r 1 -vframes 1 -y -f mjpeg %s',
            $ffmpeg_directory, $this->getPath(), $new_width, $new_height, $thumb_path);
            @system($command);

            if (!is_file($thumb_path) || filesize($thumb_path) < 10) {
                @unlink($thumb_path);
                $this->genericTN($raw_file_name);
                return false;
            } else {
                $this->addPlayButton($thumb_path);
                $this->thumbnail = & $jpeg;
            }
        }
        return true;
    }

    /**
     * @param unknown_type $jpeg
     * @return unknown_type
     */
    private function addPlayButton($jpeg)
    {
        $button = PHPWS_SOURCE_DIR . 'mod/filecabinet/img/playbutton.png';

        $w_offset = floor($this->width / 2) - 30;
        $h_offset = floor($this->height / 2) - 30;

        $background = imagecreatefromjpeg($jpeg);

        // Find base image size
        $swidth = imagesx($background);
        $sheight = imagesy($background);

        // Turn on alpha blending
        imagealphablending($background, true);

        // Create overlay image
        $button = imagecreatefrompng($button);

        // Get the size of overlay
        $owidth = imagesx($button);
        $oheight = imagesy($button);

        // Overlay watermark
        imagecopy($background, $button, $swidth - $owidth - $w_offset, $sheight - $oheight - $h_offset, 0, 0, $owidth, $oheight);
        imagejpeg($background, $jpeg);
        // Destroy the images
        imagedestroy($background);
        imagedestroy($button);
    }


    public function makeAudioThumbnail()
    {
        $thumbnail_directory = $this->thumbnailDirectory();

        if (!is_writable($thumbnail_directory)) {
            PHPWS_Error::log(FC_THUMBNAIL_NOT_WRITABLE, 'filecabinet',
                             'Multimedia::makeAudioThumbnail', $thumbnail_directory);

            return false;
        }

        $file_name = $this->dropExtension();
        $this->thumbnail = $file_name . '.png';
        return @copy(PHPWS_SOURCE_DIR . 'mod/filecabinet/img/audio.png', $thumbnail_directory . $this->thumbnail);
    }

    public function delete()
    {
        $result = $this->commonDelete();

        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if ($this->isVideo()) {
            $tn_path = $this->thumbnailDirectory() . $this->dropExtension() . '.*';
            foreach (glob($tn_path) as $filename) {
                if (!@unlink($filename)) {
                    PHPWS_Error::log(FC_COULD_NOT_DELETE, 'filecabinet', 'PHPWS_Multimedia::delete', $filename);
                }
            }
        }

        if ($this->embedded) {
            $filename = $this->thumbnailDirectory() . $this->file_name . '.jpg';
            if (!@unlink($filename)) {
                PHPWS_Error::log(FC_COULD_NOT_DELETE, 'filecabinet', 'PHPWS_Multimedia::delete', $filename);
            }
        }

        return true;
    }

    public function save($write=true, $thumbnail=true)
    {
        if (empty($this->file_directory)) {
            if ($this->folder_id) {
                $folder = new Folder($_POST['folder_id']);
                if ($folder->id) {
                    $this->setDirectory($folder->getFullDirectory());
                } else {
                    return PHPWS_Error::get(FC_MISSING_FOLDER, 'filecabinet', 'PHPWS_Multimedia::save');
                }
            } else {
                return PHPWS_Error::get(FC_DIRECTORY_NOT_SET, 'filecabinet', 'PHPWS_Multimedia::save');
            }
        }

        if ($write) {
            $result = $this->write();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }

        if (!$this->width || !$this->height) {
            $this->loadDimensions();
        }

        if ($thumbnail) {
            if ($this->isVideo()) {
                $this->makeVideoThumbnail();
            } else {
                $this->makeAudioThumbnail();
            }
        }

        if (empty($this->title)) {
            $this->title = $this->file_name;
        }

        $db = new PHPWS_DB('multimedia');
        return $db->saveObject($this);
    }

    /**
     * Template sent to File Manager for media selection.
     */
    public function managerTpl($fmanager)
    {
        $tpl['ICON'] = $this->getManagerIcon($fmanager);
        $title_len = strlen($this->title);
        if ($title_len > 20) {
            $file_name = sprintf('<abbr title="%s">%s</abbr>', $this->file_name,
            PHPWS_Text::shortenUrl($this->file_name, 20));
        } else {
            $file_name = & $this->file_name;
        }
        $tpl['TITLE'] = $this->getTitle(true);

        $filename_len = strlen($this->file_name);

        if ($filename_len > 20) {
            $file_name = sprintf('<abbr title="%s">%s</abbr>', $this->file_name,
            PHPWS_Text::shortenUrl($this->file_name, 20));
        } else {
            $file_name = & $this->file_name;
        }
        if (!$this->embedded) {
            $tpl['INFO'] = sprintf('%s<br>%s', $file_name, $this->getSize(true));
        }
        if (Current_User::allow('filecabinet', 'edit_folders', $this->folder_id, 'folder')) {
            if (!$this->embedded) {
                $links[] = $this->editLink(true);
            }
            $links[] = $this->deleteLink(true);
            $tpl['LINKS'] = implode(' ', $links);
        }
        return $tpl;
    }

    public function pinTags()
    {
        $tpl['TN'] = $this->getJSView(true);
        $tpl['TITLE'] = $this->title;
        return $tpl;
    }

    public function getManagerIcon($fmanager)
    {
        $force = $fmanager->force_resize ? 'true' : 'false';
        if ( ($fmanager->max_width < $this->width) || ($fmanager->max_height < $this->height) ) {
            return sprintf('<a href="#" onclick="oversized_media(%s, %s); return false">%s</a>', $this->id, $force, $this->getThumbnail());
        } else {
            $vars = $fmanager->linkInfo(false);
            $vars['fop']       = 'pick_file';
            $vars['file_type'] = FC_MEDIA;
            $vars['id']        = $this->id;
            $link = PHPWS_Text::linkAddress('filecabinet', $vars, true);
            return sprintf('<a href="%s">%s</a>', $link, $this->getThumbnail());
        }
    }

    public function deleteAssoc()
    {
        $db = new PHPWS_DB('fc_file_assoc');
        $db->addWhere('file_type', FC_MEDIA);
        $db->addWhere('file_id', $this->id);
        return $db->delete();
    }

    public function importExternalMedia()
    {
        $this->embedded = 1;
        include sprintf('%smod/filecabinet/inc/embed/%s/import.php', PHPWS_SOURCE_DIR, $this->file_type);
        $function_name  = $this->file_type . '_import';
        if (!function_exists($function_name)) {
            return false;
        }

        return call_user_func_array($function_name, array($this));
    }
}
?>