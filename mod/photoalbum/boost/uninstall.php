<?php

/**
 * @version $Id$
 */

if (!Current_User::isDeity()){
    header("location:index.php");
    exit();
}

function photoalbum_uninstall(&$content) {
    \core\DB::dropTable('mod_photoalbum_albums');
    \core\DB::dropTable('mod_photoalbum_photos');
    $content[] = 'Table uninstalled.';
    return TRUE;
}

?>