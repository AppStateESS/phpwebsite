<?php

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
    }
}

?>