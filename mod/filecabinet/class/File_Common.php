<?php

/**
 * Factory file for files.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::requireConfig('filecabinet', 'errorDefines.php');

class File_Common {
    var $id             = 0;
    var $key_id         = 0;
    var $file_name      = NULL;
    var $file_directory = NULL;
    var $ext            = NULL;
    var $file_type      = NULL;
    var $title          = NULL;
    var $description    = NULL;
    var $size           = NULL;

    /**
     * PEAR upload object
     */
    var $_upload        = NULL;
    var $_errors        = array();
    var $_allowed_types = NULL;


    function setId($id)
    {
        $this->id = (int)$id;
    }

    function logErrors()
    {
        if (empty($this->_errors) || !is_array($this->_errors)) {
            return;
        }

        foreach ($this->_errors as $error) {
            PHPWS_Error::log($error);
        }
    }

    function importPost($var_name)
    {
        require 'HTTP/Upload.php';

        $this->setTitle($_POST['title']);
        $this->setDescription($_POST['description']);

        if (isset($_POST['directory'])) {
            $this->setDirectory($_POST['directory']);
        } else {
            $this->setDirectory($this->getDefaultDirectory());
        }

        if (isset($_FILES[$var_name]['error']) && 
            ( $_FILES[$var_name]['error'] == UPLOAD_ERR_INI_SIZE ||
              $_FILES[$var_name]['error'] == UPLOAD_ERR_FORM_SIZE)
            ) {
            $this->_errors[] =  PHPWS_Error::get(PHPWS_FILE_SIZE, 'core', 'File_Common::getFiles');
            return FALSE;
        }

        // need to get language
        $oUpload = new HTTP_Upload('en');
        
        $this->_upload = $oUpload->getFiles($var_name);
        if (PEAR::isError($this->_upload)) {
            $this->_errors[] = $this->_upload();
            return FALSE;
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
                    $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_SIZE, 'filecabinet', 'PHPWS_Document::importPost', array($this->getSize(), MAX_DOCUMENT_SIZE));
                } else {
                    $this->_errors[] = PHPWS_Error::get(FC_IMAGE_SIZE, 'filecabinet', 'PHPWS_Document::importPost', array($this->getSize(), MAX_IMAGE_SIZE));
                }
                return FALSE;
            }
            
            if (!$this->allowType()) {
                if ($this->_classtype == 'document') {
                    $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_WRONG_TYPE, 'filecabinet', 'PHPWS_Document::importPost', array($this->getSize(), MAX_DOCUMENT_SIZE));
                } else {
                    $this->_errors[] = PHPWS_Error::get(FC_IMG_WRONG_TYPE, 'filecabinet', 'PHPWS_Document::importPost', array($this->getSize(), MAX_IMAGE_SIZE));
                }
                return FALSE;
            }
        } elseif ($this->_upload->isMissing()) {
            $this->_errors[] = PHPWS_Error::get(FC_NO_UPLOAD, 'filecabinet', 'File_Common::importPost');
            return FALSE;
        } elseif ($this->_upload->isError()) {
            $error[] = $this->_upload->getMessage();
            return FALSE;
        }

        return TRUE;
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
        $this->file_name = preg_replace('/\s/', '_', $filename);
    }

    function setSize($size)
    {
        $this->size = (int)$size;
    }

    function getSize($format=FALSE)
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

    function getMaxSize($format=FALSE)
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

    function getDescription($format=FALSE)
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

        return ($size <= $this->_max_size && $size <= ABSOLUTE_UPLOAD_LIMIT) ? TRUE : FALSE;
    }

    // rewrite
    function write()
    {
        $moved = $this->_upload->moveTo($this->file_directory);
        if (!PEAR::isError($moved)) {
            return $moved;
        }

        return TRUE;
    }

    function getPath($full_path=FALSE, $path_type='http')
    {
        if (empty($this->file_name)) {
            return PHPWS_Error::get(FC_FILENAME_NOT_SET, 'filecabinet', 'File_Common::getPath');
        }

        if (empty($this->file_directory)) {
            return PHPWS_Error::get(FC_DIRECTORY_NOT_SET, 'filecabinet', 'File_Common::getPath');
        }

        if ($full_path) {
            if ($path_type == 'http') {
                $path = PHPWS_Core::getHomeHttp();
            } else {
                $path = PHPWS_Core::getHomeDir();
            }
        } else {
            $path = './';
        }

        return $path . $root . $this->getDirectory() . $this->getFilename();
    }

    function allowType($type=NULL)
    {
        if (!isset($type)) {
            $type = $this->file_type;
        }
        return in_array($type, $this->_allowed_types);
    }

}

?>