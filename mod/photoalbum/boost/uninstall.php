<?php

/**
 * @version $Id: uninstall.php 9 2006-06-05 20:24:53Z matt $
 */

if (!Current_User::isDeity()){
  header("location:index.php");
  exit();
}

function photoalbum_uninstall(&$content) {
    PHPWS_DB::dropTable('mod_photoalbum_albums');
    PHPWS_DB::dropTable('mod_photoalbum_photos');
    $content[] = 'Table uninstalled.';
    return TRUE;
}

?>