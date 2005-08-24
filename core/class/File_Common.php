<?php

/**
 * Factory file for files.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class File_Common {
    var $id          = NULL;
    var $filename    = NULL;
    var $directory   = NULL;
    var $type        = NULL;
    var $title       = NULL;
    var $description = NULL;
    var $size        = NULL;
    var $module      = NULL;
    var $_max_size   = 0;
    var $_errors     = array();
    var $_tmp_name   = NULL;
    var $_classtype  = NULL;

    function init()
    {
        if (!isset($this->id)) {
            return FALSE;
        }

        if ($this->_classtype == 'image') {
            $table = 'images';
        } elseif ($this->_classtype == 'doc') {
            $table = 'documents';
        } else {
            return FALSE;
        }

        $db = & new PHPWS_DB($table);
        return $db->loadObject($this);
    }


    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
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
        $this->directory = $directory;
    }

    function getDirectory()
    {
        return $this->directory;
    }

    function setFilename($filename)
    {
        $this->filename = preg_replace('/\s/', '_', $filename);
    }

    function getFilename()
    {
        return $this->filename;
    }

    function setSize($size)
    {
        $this->size = (int)$size;
    }

    function getSize()
    {
        return $this->size;
    }

    function setTmpName($name)
    {
        $this->_tmp_name = $name;
    }

    function getTmpName()
    {
        return $this->_tmp_name;
    }

    function setMaxSize($max_size)
    {
        $this->_max_size = (int)$max_size;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }

    function getTitle($format=FALSE)
    {
        if ($format) {
            return str_replace("'", "\'", $this->title);
        } else {
            return $this->title;
        }
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getDescription($format=FALSE)
    {
        if ($format) {
            return PHPWS_Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }

    function setModule($module)
    {
        $this->module = $module;
        if (empty($this->directory)) {
            $this->setDirectory($module);
        }
    }

    function getModule()
    {
        return $this->module;
    }

    function setClassType($type)
    {
        $this->_classtype = $type;
    }

    function getClassType()
    {
        return $this->_classtype;
    }


    function allowType($type=NULL)
    {
        if ($this->_classtype == 'doc') {
            $typeList = unserialize(ALLOWED_DOC_TYPES);
        } else {
            $typeList = unserialize(ALLOWED_IMAGE_TYPES);
        }

        if (!isset($type)) {
            $type = $this->type;
        }

        return in_array($type, $typeList);
    }

    function allowSize($size=NULL)
    {
        if (!isset($size)) {
            $size = $this->getSize();
        }

        return ($size <= $this->_max_size && $size <= ABSOLUTE_UPLOAD_LIMIT) ? TRUE : FALSE;
    }

    function fileIsSet($varName)
    {
        return (empty($_FILES) || empty($_FILES[$varName]['name'])) ? FALSE : TRUE;
    }

    function getFILES($varName)
    {
        if (!$this->fileIsSet($varName)) {
            return FALSE;
        }

        if (isset($_FILES[$varName]['error']) && 
            ( $_FILES[$varName]['error'] == UPLOAD_ERR_INI_SIZE ||
              $_FILES[$varName]['error'] == UPLOAD_ERR_FORM_SIZE)
            ) {
            return PHPWS_Error::get(PHPWS_FILE_SIZE, 'core', 'File_Common::getFiles');
        }

        $this->filename = preg_replace('/[^\w\.]/', '_', $_FILES[$varName]['name']);
        $this->setSize($_FILES[$varName]['size']);
        $this->setTmpName($_FILES[$varName]['tmp_name']);
        $this->setType($_FILES[$varName]['type']);
        return TRUE;
    }

    function write()
    {
        $temp_dir = $this->getTmpName();
        $path = $this->getPath();

        if (empty($temp_dir)) {
            return PHPWS_Error::get(PHPWS_FILE_NO_TMP, 'core', 'PHPWS_image::write', $path);
        }

        if (PEAR::isError($path)) {
            return $path;
        }

        if(!@move_uploaded_file($temp_dir, $path)) {
            return PHPWS_Error::get(PHPWS_FILE_DIR_NONWRITE, 'core', 'PHPWS_image::writeImage', $path);
        }

        return TRUE;
    }

}

?>