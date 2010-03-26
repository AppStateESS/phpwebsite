<?php
/**
 * Photoalbum conversion file
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function convert()
{
    $mod_list = PHPWS_Core::installModList();

    if (!in_array('photoalbum', $mod_list)) {
        return _('Photo Album is not installed locally.');
    }
     
    if (!Convert::isConverted('photoalbum_albums')) {
        return convertAlbum();
    } elseif (!Convert::isConverted('photoalbum')) {
        return convertPics();
    } else {
        return _('Photo Album has already been converted.');
    }
}

function convertAlbum()
{
    $db = Convert::getSourceDB('mod_photoalbum_albums');
    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    if (empty($result)) {
        return _('No albums to convert.');
    } elseif(PHPWS_Error::isError($result)) {
        PHPWS_Error::log($result);
        return _('An error occurred when accessing the conversion database.');
    }

    $tbl_prefix = Convert::getTblPrefix();
    foreach ($result as $row) {
        $savedb = new PHPWS_DB('mod_photoalbum_albums');

        $key = new Key;
        $link = sprintf('index.php?module=photoalbum&PHPWS_Album_op=view&PHPWS_Album_id=%s', $row['id']);

        $key->setModule('photoalbum');
        $key->setItemName('album');
        $key->setItemId($row['id']);
        $key->setEditPermission('edit_album');
        $key->setUrl($link);
        $key->setTitle(utf8_encode($row['label']));
        $key->setSummary($row['blurb0']);
        $result = $key->save();
        $row['key_id'] = $key->id;
        unset($row['comments'], $row['anonymous']);

        $savedb->addValue($row);
        $result = $savedb->insert(false);
        if (PHPWS_Error::logIfError($result)) {
            return 'An error occurred when trying to convert your Albums.';
        }
        $savedb->reset();
    }

    Convert::addConvert('photoalbum_albums');
    createSeqTable('mod_photoalbum_albums');
    $content[] = _('Albums converted and keyed.');
    $content[] = sprintf('<a href="index.php?command=convert&package=photoalbum">%s</a>',
    _('Continue conversion. . .'));
    return implode('<br />', $content);

}


function convertPics()
{
    $db = Convert::getSourceDB('mod_photoalbum_photos');
    $photos = $db->export(false);
    if (!empty($photos)) {
        if (PHPWS_Error::isError($photos)) {
            PHPWS_Error::log($photos);
            return _('An error occurred when trying to copy your mod_photoalbum_photos table.');
        }

        if (!empty($_SESSION['Convert_Tbl_Prefix'])) {
            $photos = str_replace($_SESSION['Convert_Tbl_Prefix'] . 'mod_photoalbum_photos', 'mod_photoalbum_photos', $photos);
        }
        $db->disconnect();
        Convert::siteDB();

        if (PHPWS_DB::import($photos, false)) {
            createSeqTable('mod_photoalbum_photos');
            $content[] = _('Photos table imported successfully.');
        } else {
            $error = true;
            $content[] = _('Photos table failed to copy successfully.');
            return implode('<br />', $content);
        }
    }
    Convert::addConvert('photoalbum');
    $content[] = _('Finished converting Photo Album.');
    $content[] = sprintf('<a href="index.php">%s</a>', _('Return to the main page.'));
    return implode('<br />', $content);
}


function createSeqTable($table)
{
    $db = new PHPWS_DB($table);
    return $db->updateSequenceTable();
}

?>