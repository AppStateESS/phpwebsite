<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function filecabinet_document($file_type=null, $file_id=null)
{
    if (empty($file_type) || empty($file_id)) {
        return null;
    }

    if ($file_type == 'doc') {
        Core\Core::initModClass('filecabinet', 'Document.php');
        $document = new PHPWS_Document((int)$file_id);
        if (empty($document->id)) {
            $document->logErrors();
            return NULL;
        }
        return $document->getViewLink(TRUE);
    } elseif ($file_type == 'image') {
        Core\Core::initModClass('filecabinet', 'Image.php');
        $image = new PHPWS_Image((int)$file_id);

        if (empty($image->id)) {
            $image->logErrors();
            return NULL;
        }
        return $image->getTag();
    } elseif ($file_type == 'mm' || $file_type == 'media' ) {
        Core\Core::initModClass('filecabinet', 'Multimedia.php');
        $multimedia = new PHPWS_Multimedia((int)$file_id);

        if (empty($multimedia->id)) {
            $multimedia->logErrors();
            return NULL;
        }

        return $multimedia->getTag();
    }
}

?>