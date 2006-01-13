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

PHPWS_Core::initCoreClass('File_Common.php');

class PHPWS_Document extends File_Common {
    var $_max_size        = MAX_DOCUMENT_SIZE;
    var $_classtype       = 'document';

    function PHPWS_Document($id=NULL)
    {
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
            $this->_errors[] = PHPWS_Error::get(PHPWS_DOCUMENT_NOT_FOUND, 'core', 'PHPWS_Document');
        }
    }

    function setType($type){
        $this->type = $type;
    }

    function getType(){
        return $this->type;
    }

    function getTitle(){
        return $this->title;
    }

    function allowType($type=NULL)
    {
        $typeList = unserialize(ALLOWED_DOCUMENT_TYPES);

        if (!isset($type)) {
            $type = $this->type;
        }

        return in_array($type, $typeList);
    }


    function loadUpload($varName){
        $result = $this->getFILES($varName);

        if (PEAR::isError($result))
            return $result;

        $result = $this->checkBounds();
        return $result;
    }

    function checkBounds(){
        if (!$this->allowSize()) {
            $errors[] = PHPWS_Error::get(PHPWS_DOCUMENT_SIZE, 'core', 'PHPWS_Document::checkBounds', array($this->getSize(), MAX_DOCUMENT_SIZE));
        }

        if (!$this->allowType()) {
            $errors[] = PHPWS_Error::get(PHPWS_DOCUMENT_WRONG_TYPE, 'core', 'PHPWS_Document::checkBounds');
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
            return PHPWS_Error::get(PHPWS_BOUND_FAILED, 'core', 'PHPWS_Document::setBounds', $path);
        }

        $this->setSize($size);

        $type = mime_content_type($path);

        $this->setType($type);
    }

    function getIconView()
    {
        return 'icon!';
    }

    function getDownloadLink()
    {
        return sprintf('<a href="%s" title="%s">%s</a>', 
                       $this->getPath(), $this->getDescription(),
                       $this->getTitle());
    }

    function save($no_dupes=TRUE, $write=TRUE)
    {
        if (empty($this->directory)) {
            $this->directory = $this->module . '/';
        }

        if (empty($this->alt)) {
            if (empty($this->title)) {
                $this->title = $this->filename;
            }
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
            $db->addWhere('module',    $this->module);
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

}

?>