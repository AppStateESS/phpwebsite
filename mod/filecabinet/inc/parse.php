<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function filecabinet_document($file_type, $document_id)
{
    if ($file_type == 'doc') {
        PHPWS_Core::initModClass('filecabinet', 'Document.php');
        $document = & new PHPWS_Document((int)$document_id);
        if (empty($document->id)) {
            $document->logErrors();
            return NULL;
        }

        return $document->getViewLink(TRUE);
    } elseif ($file_type == 'image') {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $image = & new PHPWS_Image((int)$document_id);

        if (empty($image->id)) {
            $image->logErrors();
            return NULL;
        }
        return $image->getTag();
    }
}

?>