<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function demographics_update(&$content, $version)
{
    switch (1) {
        case version_compare($version, '0.0.2', '<'):
            $content[] = '<pre>0.0.2 changes
--------------
+ Added translate functions</pre>';

        case version_compare($version, '0.1.0', '<'):
            $content[] = '<pre>0.1.0 changes
--------------
+ Updated language functions
+ Added German translation</pre>';

        case version_compare($version, '1.0.0', '<'):
            $content[] = '<pre>1.0.0 changes
--------------
+ Implemented patch 1773655 from Eloi George. Fixes column type identification.</pre>
';

        case version_compare($version, '1.1.0', '<'):
            $db = new Core\DB('demographics');
            if (Core\Error::logIfError($db->createTableIndex('user_id', null, true))) {
                $content[] = 'Warning: A problems occurred when trying to create a unique index on the demographics table.';
            }

            $content[] = '<pre>1.1.0 changes
--------------
+ Made user_id column unique
+ Added _base_id and _extend_id. Allows demographics and extended
  object to work independently of one another.
+ Added parameter to delete function to prevent deletion of all user
  information.
+ Demographics loads itself above the extended class.
</pre>
';

        case version_compare($version, '1.1.1', '<'):
            $content[] = '<pre>1.1.1 changes
--------------
+ Removed deadend getUser function.
+ Removed some passed-by-reference ampersands.
+ Support for remove_users added.</pre>';

        case version_compare($version, '1.2.0', '<'):
            $content[] = '<pre>1.2.0 changes
--------------
+ Applied patch #2028130 from Eloi George. Fixed Demographics_User
  object not updating extend_id and base_id.
+ Added patch #1939132 from Eloi George. Changes new_user variable to
  false on successful getList call.
+ php 5 formatted.
</pre>';

    }
    return true;
}

?>