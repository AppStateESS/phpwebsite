<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class FC_File_Assoc {
    var $id        = 0;
    var $file_type = 0;
    var $file_id   = 0;
    var $tag       = null;

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
    }

    /*
    function getSource()
    {
        if (!$this->id) {
            return false;
        }

        switch ($this->file_type) {
        case FC_IMAGE:
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $image = new PHPWS_Image($this->file_id);
            return $image;
            break;
        }
    }
    */

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

    function setTag($tag)
    {
        $this->tag = htmlentities($tag, ENT_QUOTES, 'UTF-8');
    }
    

    function getTag()
    {
        return PHPWS_Text::decodeText($this->tag);
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
}

?>