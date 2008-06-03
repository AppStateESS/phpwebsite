<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function notes_update(&$content, $version) {

    switch ($version) {
    case version_compare($version, '0.1.2', '<'):
        $content[] = 'This package does not update versions under 0.1.2.';
        return false;

    case version_compare($version, '0.1.3', '<'):
        $content[] = '<pre>
0.1.3 changes
--------------
+ Added translate functions.
</pre>
';

    case version_compare($version, '0.2.0', '<'):
        $content[] = '<pre>
0.2.0 changes
--------------
+ Updated to new translation functions.
+ Added German files.
</pre>
';

    case version_compare($version, '1.0.0', '<'):
        $content[] = '<pre>';
        $files = array('javascript/search_user/head.js', 'templates/note.tpl',
                       'templates/note_style.css', 'templates/send_note.tpl', 
                       'templates/style.css');
        if (PHPWS_Boost::updateFiles($files, $content)) {
            $content[] = '--- Files updated:';
        } else {
            $content[] = '--- Unable to update files:';
        }
        $content[] = "    " . implode("    \n", $files);
        $content[] = '
1.0.0 changes
--------------
+ Added TinyMCE in limited mode for creating note
+ Cleaned up note display
+ Name search properly searches display names now
+ User name passed by id
+ Unread notes italicized
</pre>
';


    }

    return true;
}

?>