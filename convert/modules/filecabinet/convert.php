<?php

  /**
   * Copies documents files into file cabinet module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('filecabinet', 'Document.php');

function convert()
{
    if (Convert::isConverted('filecabinet')) {
        return _('File Cabinet has already converted Documents files.');
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('filecabinet', $mod_list)) {
        return _('The File Cabinet module is not installed.');
    }


    $db = Convert::getSourceDB('mod_documents_files');
    if (empty($db)) {
        return _('Documents module is not installed on the current database.');
    }

    $all_files = $db->select();
    $db->disconnect();
    Convert::siteDB();

    if (empty($all_files)) {
        return _('No files found.');
    } elseif (PEAR::isError($all_files)) {
        PHPWS_Error::log($all_files);
        return _('An error occurred while accessing your mod_documents_files table.');
    }
   

    foreach ($all_files as $old_file) {
        $new_doc = & new PHPWS_Document;
        $new_doc->setTitle($old_file['name']);
        $new_doc->file_name      = $old_file['name'];
        $new_doc->file_directory = PHPWS_HOME_DIR . 'files/filecabinet/';
        $new_doc->file_type      = $old_file['type'];
        $new_doc->size           = $old_file['size'];

        $result = $new_doc->save(true, false);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return _('An error occurred while converting your old documents.');
        }
    }
    Convert::addConvert('filecabinet');
    return _('Documents converted.');
}
?>