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
            if (PHPWS_Error::isError($result)) {
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

        case version_compare($version, '0.1.0', '<'):
            PHPWS_Boost::updateFiles(array('img/whodis.png'), 'whodis');
            $content[] = '<pre>0.1.0 changes
---------------
+ Updated translation functions
+ Added German translation files.
+ Changed control panel icon
</pre>';

        case version_compare($version, '0.1.1', '<'):
            $content[] = '<pre>0.1.1 changes
---------------
+ Added about file.
+ Repackaged because of distro problem with 0.1.0
</pre>';

        case version_compare($version, '0.1.2', '<'):
            if (!PHPWS_DB::isTable('whodis_filters')) {
                $db = new PHPWS_DB('whodis_filters');
                $db->addValue('id', 'int not null default 0');
                $db->addValue('filter', 'varchar(255) not null default \'\'');

                if (PHPWS_Error::logIfError($db->createTable())) {
                    $content[] = 'Could not create whodis_filters table.';
                    return false;
                } else {
                    $db->reset();
                    PHPWS_Error::logIfError($db->createPrimaryKey());
                }
            }

            $content[] = '<pre>0.1.2 changes
---------------
+ Installs made after 0.0.5 were missing the filters table.
</pre>';

        case version_compare($version, '0.2.0', '<'):
            $db = new PHPWS_DB('whodis');
            if (PHPWS_Error::logIfError($db->alterColumnType('url', 'text'))) {
                $content[] = 'Could not change whodis.url to text.';
                return false;
            }

            $content[] = '<pre>0.2.0 changes
---------------
+ Changed url to a text field from a varchar.
</pre>';

    }
    return true;
}


?>