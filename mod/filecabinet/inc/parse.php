<?php

function filecabinet_document($document_id)
{
    PHPWS_Core::initCoreClass('Document.php');
    $document = & new PHPWS_Document((int)$document_id);
    if (empty($document->id)) {
        $document->logErrors();
        return NULL;
    }

    return $document->getDownloadLink();
}

?>