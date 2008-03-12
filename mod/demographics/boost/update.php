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
        $db = new PHPWS_DB('demographics');
        if (PHPWS_Error::logIfError($db->createTableIndex('user_id', null, true))) {
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

    }
    return true;
}

?>