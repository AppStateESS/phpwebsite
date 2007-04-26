<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * $Id: update.php 31 2006-11-17 17:09:13Z matt $
 */

function photoalbum_update(&$content, $version) {
    switch ($version) {
    case version_compare($version, '1.2.9', '<'):
        $content[] = '- Fixed bug #1571221 improper call to core object';

    case version_compare($version, '1.3.0', '<'):
        $content[] = '- More compatiblity fixes.';

    case version_compare($version, '1.3.1', '<'):
        $content[] = '- Fixed 0.10.x core function call.';

    case version_compare($version, '1.3.2', '<'):
        $content[] = '- Fixed pager listing all photos in all albums.';
        $content[] = '- Fixed incorrect table prefixing usage in delete album.';

    case version_compare($version, '1.3.3', '<'):
        $content[] = '<pre>
1.3.3 Changes
-------------
+ Fixed bug prevented pictures from appearing the albums.
</pre>';

    case version_compare($version, '1.3.4', '<'):
        $content[] = '<pre>
1.3.4 Changes
-------------
+ Added translate function
+ Linked the Album image
</pre>';

    case version_compare($version, '1.4.0', '<'):
        PHPWS_Boost::updateFiles(array('img/photo.png'), 'photoalbum');
        $content[] = '<pre>
1.4.0 changes
-------------
+ Changed functional call to conform with new File Cabinet
+ Added German translation
+ Updated language functions
+ Changed control panel icon
</pre>';

    }

    return true;
}


?>