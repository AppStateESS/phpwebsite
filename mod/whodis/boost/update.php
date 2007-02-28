<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function whodis_update(&$content, $version)
{
    switch (1) {
    case version_compare($version, '0.0.2', '<'):
        if (PHPWS_Boost::updateFiles(array('templates/admin.tpl'), 'whodis')) {
            $content[] = 'Template upgraded successfully.';
        } else {
            $content[] = 'Template failed to copy to local directory.';
        }
        $content[] = 'Added purge functionality.';


    case version_compare($version, '0.0.3', '<'):
        if (PHPWS_Boost::updateFiles(array('templates/admin.tpl'), 'whodis')) {
            $content[] = 'Template upgraded successfully.';
        } else {
            $content[] = 'Template failed to copy to local directory.';
        }
        $content[] = '+ Added search option to listing.';
        $content[] = '+ Added checkboxes on referrers for search deletions.';

    case version_compare($version, '0.0.4', '<'):
        $content[] = '<pre>
0.0.4 changes
---------------
+ Added translate functions
</pre>';

    case version_compare($version, '0.0.5', '<'):
        $sql = "
CREATE TABLE whodis_filters (
  id int NOT NULL default 0,
  filter varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
)";
        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'An error occurred when trying to create the whodis_filters table.';
            return false;
        } else {
            $content[] = 'whodis_filters table created successfully.';
        }

        PHPWS_Boost::registerMyModule('whodis', 'users', $content);
        PHPWS_Boost::registerMyModule('whodis', 'controlpanel', $content);

        $files = array('templates/admin.tpl', 'templates/filter.tpl');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'whodis')) {
            $content[] = 'The following files were updated successfully:';
        } else {
            $content[] = 'The following files were NOT updated successfully:';
        }

        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '0.0.5 changes
---------------
+ Can now add filters which will ignore matching referrers.
+ Remove Whodis from general user panel.
+ Can now set whodis permissions.
</pre>';

    case version_compare($version, '0.0.6', '<'):
        $content[] = '<pre>0.0.6 changes
---------------
+ Added validity check of referrer url.
+ Fixed bug preventing duplicate recording.</pre>';

    }
    return true;
}

?>