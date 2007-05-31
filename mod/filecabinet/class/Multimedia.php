<?php

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'File_Common.php');

class PHPWS_Multimedia extends File_Common {
    var $width  = 0;
    var $height = 0;

    var $_classtype       = 'multimedia';

    function PHPWS_Multimedia($id=0)
    {
        $this->loadAllowedTypes();
        $this->loadDefaultDimensions();
        $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_multimedia_size'));

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
            $this->_errors[] = PHPWS_Error::get(FC_MULTIMEDIA_NOT_FOUND, 'filecabinet', 'PHPWS_Multimedia');
        }
    }

    function init()
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new PHPWS_DB('multimedia');
        return $db->loadObject($this);
    }


    function loadAllowedTypes()
    {
        $this->_allowed_types = unserialize(ALLOWED_MULTIMEDIA_TYPES);
    }

    function loadDefaultDimensions()
    {
        $this->width = PHPWS_Settings::get('filecabinet', 'default_mm_width');
        $this->height = PHPWS_Settings::get('filecabinet', 'default_mm_height');
    }


    function allowMultimediaType($type)
    {
        $mm = new PHPWS_Multimedia;
        return $mm->allowType($type);
    }

    function thumbnailDirectory()
    {
        return $this->file_directory . 'tn/';
    }

    function thumbnailPath()
    {
        $last_dot = strrpos($this->file_name, '.');
        $thumbnail_file = substr($this->file_name, 0, $last_dot) . '.jpg';

        $directory = $this->thumbnailDirectory() . $thumbnail_file;
        if (is_file($directory)) {
            return $directory;
        } else {
            return 'images/mod/filecabinet/video_generic.png';
        }
    }


    function rowTags()
    {
        $links[] = PHPWS_Text::secureLink(dgettext('filecabinet', 'Clip'), 'filecabinet',
                                          array('aop'=>'clip_multimedia',
                                                'multimedia_id' => $this->id));
        
        if (Current_User::allow('filecabinet', 'edit_folder', $this->folder_id)) {
            $links[] = $this->editLink();
            $links[] = $this->deleteLink();
        }

        $tpl['ACTION'] = implode(' | ', $links);
        $tpl['SIZE'] = $this->getSize(TRUE);
        $tpl['FILE_NAME'] = $this->file_name;
        $tpl['THUMBNAIL'] = $this->getThumbnail();
        $tpl['TITLE']     = $this->title;
        $tpl['DIMENSIONS'] = sprintf('%s x %s', $this->width, $this->height);

        return $tpl;
    }

    function editLink()
    {
        return 'Edit';
    }

    function deleteLink()
    {
        return 'Delete';
    }
    
    function getTag()
    {
        $filter_tpl = $this->getFilter();

        $tpl['WIDTH']  = $this->width;
        $tpl['HEIGHT'] = $this->height;
        $tpl['VIDEO_PATH'] = PHPWS_Core::getHomeHttp() . $this->getPath();

        // check for filter file
        $filter = 'templates/filecabinet/' . str_replace('.tpl', '', $filter_tpl) . '/filter.php';

        if (is_file($filter)) {
            include $filter;
        }
        return PHPWS_Template::process($tpl, 'filecabinet', $filter_tpl);
    }

    function getFilter()
    {
        switch ($this->file_type) {
        case 'application/x-extension-flv':
        case 'video/x-flv':
            return 'filters/flash.tpl';
            break;
            
        }
    }


    function getThumbnail($css_id=null)
    {
        if (empty($css_id)) {
            $css_id = $this->id;
        }
        return sprintf('<img src="%s" title="%s" id="multimedia-thumbnail-%s" />',
                       $this->thumbnailPath(),
                       $this->title, $css_id);
    }

    function makeThumbnail()
    {
        if (!PHPWS_Settings::get('filecabinet', 'use_ffmpeg')) {
            return;
        }

        $ffmpeg_directory = PHPWS_Settings::get('filecabinet', 'ffmpeg_directory');
        if (!is_file($ffmpeg_directory . 'ffmpeg')) {
            return;
        }

        $tmp_name = mt_rand();

        /**
         * -i        filename
         * -an       disable audio
         * -ss       seek to position
         * -r        frame rate
         * -vframes  number of video frames to record
         * -y        overwrite output files
         * -f        force format
         */

        $thumbnail_directory = $this->file_directory . 'tn/';

        if (!is_writable($thumbnail_directory)) {
            return;
        }

        $last_dot = strrpos($this->file_name, '.');
        $thumbnail_file = substr($this->file_name, 0, $last_dot) . '.jpg';


        $command = sprintf('%sffmpeg -i %s -an -s 160x120 -ss 00:00:05 -r 1 -vframes 1 -y -f mjpeg %s%s',
                           $ffmpeg_directory, $this->getPath(), $thumbnail_directory, $thumbnail_file);

        $result = system($command);
        return true;
    }
    
    function delete()
    {
        $db = new PHPWS_DB('multimedia');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        $path = $this->getPath();

        if (!@unlink($path)) {
            PHPWS_Error::log(FC_COULD_NOT_DELETE, 'filecabinet', 'PHPWS_Multimedia::delete', $path);
        }

        $tn = $this->thumbnailPath();
        if (!@unlink($tn)) {
            PHPWS_Error::log(FC_COULD_NOT_DELETE, 'filecabinet', 'PHPWS_Multimedia::delete', $path);
        }

        return true;
    }

    function save($write=true, $thumbnail=true)
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

        if (!is_writable($this->file_directory)) {
            return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'PHPWS_Multimedia::save', $this->file_directory);
        }

        if ($write) {
            $result = $this->write();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if ($thumbnail) {
            $this->makeThumbnail();
        }

        $db = new PHPWS_DB('multimedia');
        return $db->saveObject($this);
    }


}
?>