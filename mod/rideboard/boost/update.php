<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rideboard_update(&$content, $version)
{
    switch(1) {
    case version_compare($version, '1.0.1', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('templates/settings.tpl'), 'rideboard')) {
            $content[] = '--- Local templates updated.';
        } else {
            $content[] = '--- Failed to update local templates.';
        }
        $content[] = "\n1.0.1 Version
-------------
+ Settings allows menu link creation.</pre>";


    case version_compare($version, '1.1.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/carpool_view.tpl',
                       'templates/carpools.tpl',
                       'templates/settings.tpl',
                       'templates/edit_carpool.tpl');

        if (PHPWS_Boost::updateFiles($files, 'rideboard')) {
            $content[] = '--- Local templates updated.';
        } else {
            $content[] = '--- Failed to update local templates.';
        }
        $content[] = "\n1.1.0 Version
-------------
+ Added a carpooling component.</pre>";

    case version_compare($version, '1.1.1', '<'):
        $content[] = '<pre>';
        if (!PHPWS_DB::isTable('rb_carpool')) {
            $carpool = 'CREATE TABLE rb_carpool (
id INT NOT NULL,
user_id INT NOT NULL default 0,
email VARCHAR( 255 ) NOT NULL,
created INT NOT NULL default 0,
start_address VARCHAR( 255 ) NOT NULL,
dest_address VARCHAR( 255 ) NOT NULL,
comment TEXT,
PRIMARY KEY ( id )
);';
            $result = PHPWS_DB::query($carpool);
            if (PHPWS_Error::logIfError($result)) {
                $content[] = 'Unable to create carpool table.</pre>';
                return false;
            } else {
                $content[] = 'Carpool table created successfully.';
            }
        }
        $content[] = "1.1.1 Changes
------------------------
+ 1.1.0 did not create rb_carpool table on update.</pre>";

    }
    return true;
}

?>