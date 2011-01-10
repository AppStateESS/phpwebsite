<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * $Id$
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

        case version_compare($version, '1.4.1', '<'):
            $content[] = '<pre>
1.4.1 changes
-------------
+ RFE #1757050 - Added resizing code from Verdon
</pre>';

        case version_compare($version, '1.4.2', '<'):
            $content[] = '<pre>
1.4.2 changes
-------------
+ Image resize patch from Verdon Vaillancourt.
</pre>';

        case version_compare($version, '1.4.3', '<'):
            PHPWS_Boost::updateFiles(array('conf/config.php'), 'photoalbum');
            $content[] = '<pre>
1.4.3 changes
-------------
+ Increased resize width and height for photoalbum
+ Fixed resize functionality
</pre>';

        case version_compare($version, '1.4.4', '<'):
            PHPWS_Boost::updateFiles(array('conf/config.php', 'templates/albums/list.tpl'), 'photoalbum');
            $content[] = '<pre>
1.4.4 changes
-------------
+ Added page navigation to album listing.
+ Fixed path to icon in SlideShow.
+ Fixes attempted for batch upload.
+ Removed html tags and newline calls causing javascript errors.
+ Fixed: photoalbum was calling hub config file instead of local version
</pre>';

        case version_compare($version, '1.4.5', '<'):
            PHPWS_Boost::updateFiles(array('conf/config.php'), 'photoalbum');
            $content[] = '<pre>
1.4.5 changes
-------------
+ Fixed: inability to upload photos.
</pre>';

        case version_compare($version, '1.4.6', '<'):
            PHPWS_Boost::updateFiles(array('conf/config.php'), 'photoalbum');
            $content[] = '<pre>
1.4.6 changes
-------------
+ Some edits to try and make sorting work better.
</pre>';

        case version_compare($version, '1.4.7', '<'):
            $content[] = '<pre>
1.4.7 changes
-------------
+ Deleted album removes key as well now.
+ Removed some compatibility functions from Core. Redistributed to
this module.
</pre>';

            case version_compare($version, '1.4.8', '<'):
            $content[] = '<pre>
1.4.8 changes
-------------
+ Icon class implemented.
+ PHP 5 strict fixes.
</pre>';

            case version_compare($version, '1.4.9', '<'):
            $content[] = '<pre>
1.4.9 changes
-------------
+ Removed code that prevented image update
</pre>';

    }

    return true;
}


?>