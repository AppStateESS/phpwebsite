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

PHPWS_Core::initModClass('filecabinet', 'File_Common.php');

class PHPWS_Document extends File_Common {
    var $_max_size        = MAX_DOCUMENT_SIZE;
    var $_classtype       = 'document';


    function PHPWS_Document($id=NULL)
    {
        $this->loadAllowedTypes();

        if (empty($id)) {
            return;
        }
        
        $this->setId((int)$id);

        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->id = NULL;
            $this->_errors[] = $result;
        } elseif (empty($result)) {
            $this->id = NULL;
            $this->_errors[] = PHPWS_Error::get(FC_DOCUMENT_NOT_FOUND, 'filecabinet', 'PHPWS_Document');
        }

    }

    function loadAllowedTypes()
    {
        $this->_allowed_types = unserialize(ALLOWED_DOCUMENT_TYPES);
    }

    function getDefaultDirectory()
    {
        return PHPWS_Settings::get('filecabinet', 'base_doc_directory');
    }

    function init()
    {
        if (!isset($this->id)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('documents');
        return $db->loadObject($this);
    }


    /*
    function loadUpload($varName){
        $result = $this->getFILES($varName);

        if (PEAR::isError($result))
            return $result;

        $result = $this->checkBounds();
        return $result;
    }
    */
    /*
    function checkBounds(){
        if (!$this->allowSize()) {
            $errors[] = PHPWS_Error::get(PHPWS_DOCUMENT_SIZE, 'filecabinet', 'PHPWS_Document::checkBounds', array($this->getSize(), MAX_DOCUMENT_SIZE));
        }

        if (!$this->allowType()) {
            $errors[] = PHPWS_Error::get(PHPWS_DOCUMENT_WRONG_TYPE, 'filecabinet', 'PHPWS_Document::checkBounds');
        }

        if (isset($errors)) {
            return $errors;
        } else {
            return TRUE;
        }
    }


    function setBounds($path=NULL){
        if (empty($path)) {
            $path = $this->getPath();
        }

        $size = @filesize($path);
        if (empty($size)) {
            return PHPWS_Error::get(FC_BOUND_FAILED, 'filecabinet', 'PHPWS_Document::setBounds', $path);
        }

        $this->setSize($size);

        $type = mime_content_type($path);

        $this->setType($type);
    }

    */

    function getIconView()
    {
        return 'icon!';
    }

    
    function getDownloadLink()
    {
        return 'fix getDownload Link';
        return sprintf('<a href="%s" title="%s">%s</a>', 
                       $this->getPath(), $this->getDescription(),
                       $this->getTitle());
    }

    function save($no_dupes=TRUE, $write=TRUE)
    {
        if (empty($this->directory)) {
            return FALSE;
        }

        if (empty($this->title)) {
            $this->title = $this->filename;
        }

        if ($write) {
            $result = $this->write();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        $db = & new PHPWS_DB('documents');

        if ((bool)$no_dupes && empty($this->id)) {
            $db->addWhere('filename',  $this->filename);
            $db->addWhere('directory', $this->directory);
            $db->addColumn('id');
            $result = $db->select('one');
            if (PEAR::isError($result)) {
                return $result;
            } elseif (isset($result) && is_numeric($result)) {
                $this->id = $result;
                return TRUE;
            }

            $db->reset();
        }

        return $db->saveObject($this);
    }

    function delete()
    {
        if (!$this->id) {
            return FALSE;
        }

        $file_dir = $this->directory . $this->filename;

        // if the file is not there, we want to continue anyway
        if (is_file($file_dir)) {
            if (!@unlink($file_dir)) {
                return PHPWS_Error::get(FC_COULD_NOT_DELETE, 'filecabinet', 'Document::delete', $file_dir);
            }
        }

        $db = & new PHPWS_DB('documents');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }
}

?>