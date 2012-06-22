<?php

/**
 * Factory file for files.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::initCoreClass('File.php');

define('FILE_TITLE_CUTOFF', 24);

class File_Common {

    public $id = 0;
    public $file_name = null;
    public $file_directory = null;
    public $folder_id = 0;
    public $file_type = null;
    public $title = null;
    public $description = null;
    public $size = 0;

    /**
     * PEAR upload object
     */
    public $_upload = null;
    public $_errors = array();
    public $_allowed_types = null;
    public $_max_size = 0;
    public $_ext = null;

    public function allowSize($size = null)
    {
        if (!isset($size)) {
            $size = $this->getSize();
        }

        return ($size <= $this->_max_size && $size <= ABSOLUTE_UPLOAD_LIMIT) ? true : false;
    }

    /**
     * Compares against a set of allowable extensions. This should not be
     * used alone as it is specific to File Cabinet. It is assumed you
     * have run PHPWS_File::checkMimeType first.
     */
    public function allowType($ext = null)
    {
        if (!isset($ext)) {
            $ext = $this->_ext;
        }
        $ext = strtolower($ext);
        return in_array($ext, $this->_allowed_types);
    }

    public function formatSize($size)
    {
        if ($size >= 1000000) {
            return round($size / 1000000, 2) . 'MB';
        } else {
            return round($size / 1000, 2) . 'K';
        }
    }

    /**
     * Returns the file type. If shorten == true, then the
     * type will be shortened for display purposes. This is on account
     * of certain mime types getting around 40 characters
     * @param $shorten
     */
    public function getFileType($shorten = false)
    {
        $file_length = strlen($this->file_type);
        if ($shorten && $file_length > 20) {
            $short_name = substr($this->file_type, 0, 8) . '...' . substr($this->file_type, $file_length - 8);
            return '<abbr title="' . $this->file_type . '">' . $short_name . '</abbr>';
        } else {
            return $this->file_type;
        }
    }

    public function setMaxSize($max_size)
    {
        static $sizes = null;
        if (empty($sizes)) {
            $sizes = Cabinet::getMaxSizes();
        }

        $max_size = $sizes[$this->_classtype];
        foreach ($sizes as $k => $v) {
            if (in_array($k, array('document', 'image', 'multimedia'))) {
                continue;
            }
            if ($max_size > $v) {
                $max_size = $v;
            }
        }
        $this->_max_size = (int) $max_size;
    }

    public function getSize($format = false)
    {
        if ($format) {
            return $this->formatSize($this->size);
        } else {
            return $this->size;
        }
    }

    /**
     * Tests file upload to determine if it may be saved to the server.
     * Returns true if so, false otherwise.
     * Called from Image_Manager's postImageUpload function and Cabinet_Action's
     * postDocument function.
     */
    public function importPost($var_name, $use_folder = true, $ignore_missing_file = false, $file_prefix = null)
    {
        require 'HTTP/Upload.php';

        if (!empty($_POST['folder_id'])) {
            $this->folder_id = (int) $_POST['folder_id'];
        } elseif (!$this->folder_id && $use_folder) {
            $this->_errors[] = PHPWS_Error::get(FC_MISSING_FOLDER, 'filecabinet', 'File_Common::importPost');
        }

        if (isset($_POST['title'])) {
            $this->setTitle($_POST['title']);
        } else {
            $this->title = null;
        }

        if (isset($_POST['alt'])) {
            $this->setAlt($_POST['alt']);
        }

        if (isset($_POST['description'])) {
            $this->setDescription($_POST['description']);
        } else {
            $this->description = null;
        }

        if ($this->id && $this->isVideo()) {
            if (isset($_POST['width'])) {
                $width = (int) $_POST['width'];
                if ($width > 20) {
                    $this->width = & $width;
                }
            }

            if (isset($_POST['height'])) {
                $height = (int) $_POST['height'];
                if ($height > 20) {
                    $this->height = & $height;
                }
            }
        }

        if (!empty($_FILES[$var_name]['error'])) {
            switch ($_FILES[$var_name]['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->_errors[] = PHPWS_Error::get(PHPWS_FILE_SIZE, 'core', 'File_Common::getFiles');
                    break;

                case UPLOAD_ERR_FORM_SIZE:
                    $this->_errors[] = PHPWS_Error::get(FC_MAX_FORM_UPLOAD, 'filecabinet', 'PHPWS_Document::importPost', array($this->_max_size));
                    return false;
                    break;

                case UPLOAD_ERR_NO_FILE:
                    // Missing file is not important for an update or if they specify to ignore it.
                    if ($this->id || $ignore_missing_file) {
                        return true;
                    } else {
                        $this->_errors[] = PHPWS_Error::get(FC_NO_UPLOAD, 'filecabinet', 'PHPWS_Document::importPost');
                        return false;
                    }
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->_errors[] = PHPWS_Error::get(FC_MISSING_TMP, 'filecabinet', 'PHPWS_Document::importPost', array($this->_max_size));
                    return false;
            }
        }

        // need to get language
        $oUpload = new HTTP_Upload('en');
        $this->_upload = $oUpload->getFiles($var_name);

        if (PHPWS_Error::isError($this->_upload)) {
            $this->_errors[] = $this->_upload();
            return false;
        }

        if ($this->_upload->isValid()) {
            $file_vars = $this->_upload->getProp();

            if (!empty($file_prefix) && !preg_match('/\W/', $file_prefix)) {
                $filename = $file_prefix . $file_vars['real'];
            } else {
                $filename = $file_vars['real'];
            }

            $this->setFilename($filename);
            $this->_upload->setName($this->file_name);

            $this->setSize($file_vars['size']);

            $this->file_type = $file_vars['type'];
            if ($this->file_type == 'application/octet-stream') {
                $mime = PHPWS_File::getMimeType($file_vars['tmp_name']);
                if ($mime != $this->file_type) {
                    $this->file_type = & $mime;
                }
            }

            if (!PHPWS_File::checkMimeType($file_vars['tmp_name'], $file_vars['ext'])) {
                $this->_errors[] = PHPWS_Error::get(FC_FILE_TYPE_MISMATCH, 'filecabinet', 'File_Common::importPost', $file_vars['ext'] . ':' . PHPWS_File::getMimeType($file_vars['tmp_name']));
                return false;
            }

            if (!$this->allowType($file_vars['ext'])) {
                if ($this->_classtype == 'document') {
                    $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_WRONG_TYPE, 'filecabinet', 'File_Common::importPost');
                } elseif ($this->_classtype == 'image') {
                    $this->_errors[] = PHPWS_Error::get(FC_IMG_WRONG_TYPE, 'filecabinet', 'File_Common::importPost');
                } else {
                    $this->_errors[] = PHPWS_Error::get(FC_MULTIMEDIA_WRONG_TYPE, 'filecabinet', 'File_Common::importPost');
                }
                return false;
            }

            if ($this->size && !$this->allowSize()) {
                if ($this->_classtype == 'document') {
                    $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_SIZE, 'filecabinet', 'File_Common::importPost', array($this->size, $this->_max_size));
                } elseif ($this->_classtype == 'image') {
                    $this->_errors[] = PHPWS_Error::get(FC_IMG_SIZE, 'filecabinet', 'File_Common::importPost', array($this->size, $this->_max_size));
                } else {
                    $this->_errors[] = PHPWS_Error::get(FC_MULTIMEDIA_SIZE, 'filecabinet', 'File_Common::importPost', array($this->size, $this->_max_size));
                }
                return false;
            }

            if ($this->_classtype == 'image') {
                list($this->width, $this->height, $image_type, $image_attr) = getimagesize($this->_upload->upload['tmp_name']);

                $result = $this->prewriteResize();
                if (PHPWS_Error::isError($result)) {
                    $this->_errors[] = $result;
                    return false;
                }

                $result = $this->prewriteRotate();
                if (PHPWS_Error::isError($result)) {
                    $this->_errors[] = $result;
                    return false;
                }
            }
        } elseif ($this->_upload->isError()) {
            $this->_errors[] = $this->_upload->getMessage();
            return false;
        } elseif ($this->_upload->isMissing()) {
            $this->_errors[] = PHPWS_Error::get(FC_NO_UPLOAD, 'filecabinet', 'File_Common::importPost');
            return false;
        }

        return true;
    }

    public function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput(strip_tags($description, '<em><strong><b><i><u>'));
    }

    public function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    public function setDirectory($directory)
    {
        if (!preg_match('@/$@', $directory)) {
            $directory .= '/';
        }

        $this->file_directory = $directory;
    }

    public function setFilename($filename)
    {
        $this->file_name = preg_replace('/[^\w\.]/', '_', $filename);
    }

    public function setSize($size)
    {
        $this->size = (int) $size;
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    /**
     * Writes the file to the server
     */
    public function write($public = true)
    {
        if (!is_writable($this->file_directory)) {
            return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'File_Common::write', $this->file_directory);
        }

        if (!$this->id && is_file($this->getPath())) {
            $this->file_name = time() . $this->file_name;
            PHPWS_Error::log(FC_DUPLICATE_FILE, 'filecabinet', 'File_Common::write', $this->getPath());
        }

        if ($this->_upload) {
            $this->_upload->setName($this->file_name);
            $directory = preg_replace('@[/\\\]$@', '', $this->file_directory);

            if (!is_dir($directory)) {
                return PHPWS_Error::get(FC_BAD_DIRECTORY, 'filecabinet', 'File_Common::write', $directory);
            }

            $moved = $this->_upload->moveTo($directory);
            if (!PHPWS_Error::isError($moved)) {
                chmod($directory . '/' . $moved, 0644);
                return $moved;
            }
        }

        return true;
    }

    public function getPath()
    {
        return $this->file_directory . $this->file_name;
    }

    public function logErrors()
    {
        if (!empty($this->_errors) && is_array($this->_errors)) {
            foreach ($this->_errors as $error) {
                PHPWS_Error::log($error);
            }
        }
    }

    public function getErrors()
    {
        $foo = array();
        if (!empty($this->_errors) && is_array($this->_errors)) {
            foreach ($this->_errors as $error) {
                $foo[] = $error->getMessage();
            }
        }
        return $foo;
    }

    public function printErrors()
    {
        $foo = $this->getErrors();
        return implode('<br />', $foo);
    }

    public function loadFileSize()
    {
        if (empty($this->file_directory) ||
                empty($this->file_name) ||
                !is_file($this->getPath())) {
            return false;
        }

        $this->size = filesize($this->getPath());
    }

    public function getVideoTypes()
    {
        static $video_types = null;

        if (empty($video_types)) {
            $video_types = explode(',', FC_VIDEO_TYPES);
        }

        return $video_types;
    }

    /**
     * Checks if a file is a known video file type
     */
    public function isVideo()
    {
        if ($this->_classtype != 'multimedia') {
            return false;
        }

        $videos = $this->getVideoTypes();
        $ext = $this->getExtension();

        return in_array($ext, $videos);
    }

    public function dropExtension()
    {
        $last_dot = strrpos($this->file_name, '.');
        return substr($this->file_name, 0, $last_dot);
    }

    public function getExtension()
    {
        if (!$this->_ext) {
            $this->loadExtension();
        }

        return $this->_ext;
    }

    public function loadExtension()
    {
        if (!$this->_ext && $this->file_name) {
            $this->_ext = PHPWS_File::getFileExtension($this->file_name);
        }
    }

    /**
     * Deletes a file database entry, its directory, and its file association
     * Requires each to have a deleteAssoc function.
     */
    public function commonDelete()
    {
        if (!$this->id) {
            return false;
        }

        switch ($this->_classtype) {
            case 'image':
                $db = new PHPWS_DB('images');
                break;

            case 'document':
                $db = new PHPWS_DB('documents');
                break;

            case 'multimedia':
                $db = new PHPWS_DB('multimedia');
                break;
        }

        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $path = $this->getPath();

        if (!@unlink($path)) {
            PHPWS_Error::log(FC_COULD_NOT_DELETE, 'filecabinet', 'File_Common::commonDelete', $path);
        }

        PHPWS_Error::logIfError($this->deleteAssoc());
        return true;
    }

    public function moveToFolder()
    {
        if (empty($_POST['move_to_folder']) || $_POST['move_to_folder'] == $this->folder_id) {
            return false;
        }

        $new_folder = new Folder($_POST['move_to_folder']);
        $old_folder = new Folder($this->folder_id);

        if ($new_folder->ftype != $old_folder->ftype) {
            return false;
        }

        $dest_dir = $new_folder->getFullDirectory();

        $source = $this->getPath();
        $dest = $dest_dir . $this->file_name;

        if ($this->_classtype != 'document') {
            $stn = $this->thumbnailPath();
            $dtn = $dest_dir . 'tn/' . $this->tnFileName();
        }

        // A embedded file just needs thumbnails moved
        if ($this->_classtype == 'multimedia' && $this->embedded) {
            $this->folder_id = $new_folder->id;
            $this->file_directory = $dest_dir;
            if (@copy($stn, $dtn)) {
                if (!PHPWS_Error::logIfError($this->save(false, false))) {
                    // no error occurs, unlink the source file and thumbnail
                    unlink($stn);
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        // If file already exists in folder, don't copy.
        if (is_file($dest)) {
            return false;
        }

        // copy the source file to the new destination
        if (@copy($source, $dest)) {
            $this->folder_id = $new_folder->id;
            $this->file_directory = $dest_dir;
            switch ($this->_classtype) {
                case 'image':
                    // copy the thumbnail
                    if (@copy($stn, $dtn)) {
                        if (!PHPWS_Error::logIfError($this->save(false, false, false))) {
                            // no error occurs, unlink the source file and thumbnail
                            unlink($source);
                            unlink($stn);
                            return true;
                        } else {
                            // error occurred, delete the copy file
                            unlink($dest);
                            return false;
                        }
                    } else {
                        // thumbnail copy failed, remove copy
                        @unlink($dest);
                        return false;
                    }
                    break;

                case 'document':
                    if (!PHPWS_Error::logIfError($this->save(false, false))) {
                        // no error occurs, unlink the source file
                        unlink($source);
                        return true;
                    } else {
                        // error occurred, delete the copy file
                        unlink($dest);
                        return false;
                    }
                    break;

                case 'multimedia':
                    // copy the thumbnail
                    if (@copy($stn, $dtn)) {
                        if (!PHPWS_Error::logIfError($this->save(false, false))) {
                            // no error occurs, unlink the source file and thumbnail
                            unlink($source);
                            unlink($stn);
                            return true;
                        } else {
                            // error occurred, delete the copy file
                            unlink($dest);
                            return false;
                        }
                    } else {
                        // thumbnail copy failed, remove copy
                        @unlink($dest);
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

    public function getTitle($shorten = false)
    {
        if ($shorten && (strlen($this->title) > FILE_TITLE_CUTOFF)) {
            return sprintf('<abbr title="%s">%s</abbr>', $this->title, PHPWS_Text::shortenUrl($this->title, FILE_TITLE_CUTOFF));
        } else {
            return $this->title;
        }
    }

    public function loadTitleFromFilename()
    {
        $ext = PHPWS_File::getFileExtension($this->file_name);
        $this->title = str_replace('.' . $ext, '', $this->file_name);
        if (preg_match('/_/', $this->title) && !preg_match('/\s/', $this->title)) {
            $this->title = str_replace('_', ' ', $this->title);
        }
    }

    public function ckButtons()
    {
        return <<<EOF
<input type="button" name="edit" value="Edit" rel="{$this->id}"> <input type="button" name="delete" value="Delete" rel="{$this->id}">
EOF;
    }

}

?>