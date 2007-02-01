<?php

/**
 * Factory file for files.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

require_once PHPWS_SOURCE_DIR . 'mod/filecabinet/inc/errorDefines.php';

class File_Common {
    var $id              = 0;
    var $key_id          = 0;
    var $file_name       = NULL;
    var $file_directory  = NULL;
    var $ext             = NULL;
    var $file_type       = NULL;
    var $title           = NULL;
    var $description     = NULL;
    var $size            = NULL;

    /**
     * PEAR upload object
     */
    var $_upload         = NULL;
    var $_errors         = array();
    var $_allowed_types  = NULL;
    var $_move_directory = NULL;


    function setId($id)
    {
        $this->id = (int)$id;
    }

    function logErrors()
    {
        if ( !empty($this->_errors) && is_array($this->_errors) ) {
            foreach ($this->_errors as $error) {
                PHPWS_Error::log($error);
            }
        }
    }

    /**
     * Tests file upload to determine if it may be saved to the server.
     * Returns true if so, false otherwise.
     * Called from Image_Manager's postImage function and Cabinet_Action's
     * postDocument function.
     */
    function importPost($var_name)
    {
        require 'HTTP/Upload.php';

        // Store current directory in case they are moving the document
        // or image
        $current_directory = $this->file_directory;

        if (isset($_POST['title'])) {
            $this->setTitle($_POST['title']);
        } else {
            $this->title = NULL;
        }

        if (isset($_POST['alt'])) {
            $this->setAlt($_POST['alt']);
        }

        if (isset($_POST['description'])) {
            $this->setDescription($_POST['description']);
        } else {
            $this->description = NULL;
        }

        $default_dir = $this->getDefaultDirectory();
        if (isset($_POST['directory']) && !empty($_POST['directory'])) {
            $directory =  urldecode($_POST['directory']);

            if (preg_match('@^' . $default_dir . '@', $directory)) {
                $this->setDirectory($directory);
            } else {
                $this->setDirectory($default_dir . $directory);
            }
        } else {
            if (empty($this->file_directory)) {
                $this->setDirectory($default_dir);
            }
        }

        // UPLOAD defines come from PEAR lib/pear/Compat/Constant/UPLOAD_ERR.php
        if (isset($_FILES[$var_name]['error']) && 
            ( $_FILES[$var_name]['error'] == UPLOAD_ERR_INI_SIZE ||
              $_FILES[$var_name]['error'] == UPLOAD_ERR_FORM_SIZE)
            ) {
            $this->_errors[] =  PHPWS_Error::get(PHPWS_FILE_SIZE, 'core', 'File_Common::getFiles');
            return false;
        }

        // need to get language
        $oUpload = new HTTP_Upload('en');
        $this->_upload = $oUpload->getFiles($var_name);

        if (PEAR::isError($this->_upload)) {
            $this->_errors[] = $this->_upload();
            return false;
        }

        if ($this->_upload->isValid()) {
            $file_vars = $this->_upload->getProp();

            $this->setFilename($file_vars['real']);
            $this->_upload->setName($this->file_name);

            $this->setSize($file_vars['size']);

            $this->file_type = $file_vars['type'];
            $this->ext  = $file_vars['ext'];

            if (!$this->allowSize()) {
                if ($this->_classtype == 'document') {
                    $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_SIZE, 'filecabinet', 'PHPWS_Document::importPost', array($this->size, $this->_max_size));
                } else {
                    $this->_errors[] = PHPWS_Error::get(FC_IMG_SIZE, 'filecabinet', 'PHPWS_Document::importPost', array($this->size, $this->_max_size));
                }
                return false;
            }

            if (!$this->allowType()) {
                if ($this->_classtype == 'document') {
                    $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_WRONG_TYPE, 'filecabinet', 'PHPWS_Document::importPost');
                } else {
                    $this->_errors[] = PHPWS_Error::get(FC_IMG_WRONG_TYPE, 'filecabinet', 'PHPWS_Document::importPost');
                }
                return false;
            }

            if ($this->_classtype == 'image') {
                list($this->width, $this->height, $image_type, $image_attr) = getimagesize($this->_upload->upload['tmp_name']);

                if(!$this->allowDimensions()) {
                    $this->_errors[] = PHPWS_Error::get(FC_IMAGE_DIMENSION, 'filecabinet', 'PHPWS_Document::importPost', array($this->width, $this->height, $this->_max_width, $this->_max_height));                
                    return false;
                }
            }

        } elseif ($this->_upload->isMissing()) {
            if ($this->id) {
                if ($current_directory != $this->file_directory) {
                    $this->_move_directory = & $current_directory;
                }

                // if the document id is set, we assume they are just updating other information
                return true;
            }

            // If there wasn't a file uploaded, we return a false without an error.
            // This will allow to check for a false and continue on if the error array is empty
            return false;
        } elseif ($this->_upload->isError()) {
            $this->_errors[] = $this->_upload->getMessage();
            return false;
        }

        return true;
    }

    function getErrors()
    {
        return $this->_errors;
    }

    function setDirectory($directory)
    {
        if (!preg_match('/\/$/', $directory)) {
            $directory .= '/';
        }

        $this->file_directory = $directory;
    }


    function setFilename($filename)
    {
        $this->file_name = preg_replace('/[^\w\s\.]/', '_', $filename);
    }

    function setSize($size)
    {
        $this->size = (int)$size;
    }

    function getSize($format=false)
    {
        if ($format) {
            return $this->_formatSize($this->size);
        } else {
            return $this->size;
        }
    }


    function setMaxSize($max_size)
    {
        $this->_max_size = (int)$max_size;
    }

    function getMaxSize($format=false)
    {
        if ($format) {
            return $this->_formatSize($this->_max_size);
        } else {
            return $this->_max_size;
        }
    }

    function _formatSize($size)
    {
        if ($size >= 1000000) {
            return round($size / 1000000, 2) . 'MB';
        } else {
            return round($size / 1000, 2) . 'K';
        }
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    function getDescription($format=false)
    {
        if ($format) {
            return PHPWS_Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }

    function allowSize($size=NULL)
    {
        if (!isset($size)) {
            $size = $this->getSize();
        }

        return ($size <= $this->_max_size && $size <= ABSOLUTE_UPLOAD_LIMIT) ? true : false;
    }


    /**
     * Writes the file to the server
     */
    function write()
    {
        $this->_upload->setName($this->file_name);
        $directory = preg_replace('@[/\\\]$@', '', $this->file_directory);
        $moved = $this->_upload->moveTo($directory);
        if (!PEAR::isError($moved)) {
            return $moved;
        }

        return true;
    }

    /**
     * Returns the directory path of the file
     * If relative is true, a path relative to the installation
     * directory is returned.
     */
    function getPath($relative=true)
    {
        if (empty($this->file_name)) {
            return PHPWS_Error::get(FC_FILENAME_NOT_SET, 'filecabinet', 'File_Common::getPath');
        }

        if (empty($this->file_directory)) {
            return PHPWS_Error::get(FC_DIRECTORY_NOT_SET, 'filecabinet', 'File_Common::getPath');
        }

        if ($relative) {
            $directory = str_replace(PHPWS_HOME_DIR, './', $this->file_directory);
            return sprintf('%s%s', $directory, $this->file_name);
        } else {
            return $this->file_directory . $this->file_name;
        }
    }

    function allowType($type=NULL)
    {
        if (!isset($type)) {
            $type = $this->file_type;
        }

        return in_array($type, $this->_allowed_types);
    }

    /**
     * Substitute for rename
     * from php.net
     * @author ddoyle [at] canadalawbook [dot] ca
     */
    function move_file($oldfile, $newfile)
    {
        if (!@rename($oldfile,$newfile)) {
            if (@copy ($oldfile,$newfile)) {
                unlink($oldfile);
                return true;
            }
            return false;
        }
        return true;
    }
}

?>