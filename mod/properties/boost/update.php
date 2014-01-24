<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

function properties_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case (version_compare($currentVersion, '1.1.0', '<')):
            $db = new PHPWS_DB('properties');
            $result = $db->addTableColumn('efficiency',
                    'smallint not null default 0');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'ERROR - could not add efficiency column';
                return false;
            }
            $content[] = '<pre>1.1.0 updates
---------------
+ Added efficiency option</pre>';

        case (version_compare($currentVersion, '1.1.1', '<')):
            $db = new PHPWS_DB('prop_contacts');
            $result = $db->addTableColumn('company_url', 'VARCHAR( 255 ) NULL');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'ERROR - could not add company_url column';
                return false;
            }
            $content[] = '<pre>1.1.1 updates
---------------
+ Added company url
+ Property listing divided into tabs.</pre>';

        case (version_compare($currentVersion, '1.2.0', '<')):
            $db = new PHPWS_DB('properties');
            $db->addWhere('pets_allowed', 1);
            $db->addColumn('id');
            $db->addColumn('pet_type');
            $db->setIndexBy('id');
            $cols = $db->select('col');
            if (!empty($cols)) {
                foreach ($cols as $id => $pets) {
                    if (empty($pets)) {
                        continue;
                    }

                    $db->reset();
                    $pets_array = null;
                    $pets_array = @unserialize($pets);

                    if (!is_array($pets_array)) {
                        continue;
                    } else {
                        $pets = implode(', ', $pets_array);
                    }
                    $db->addWhere('id', $id);
                    $db->addValue('pet_type', $pets);
                    $db->update();
                }
            }
            $db->reset();
            $result = $db->addTableColumn('pet_fee', 'int not null default 0');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'ERROR - could not add pet_fee column';
                return false;
            }

            $db->reset();
            $result = $db->addTableColumn('airconditioning',
                    'smallint not null default 0');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'ERROR - could not add airconditioning column';
                return false;
            }

            $db->reset();
            $result = $db->addTableColumn('heat_type',
                    'varchar(255) default null');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'ERROR - could not add heat_type column';
                return false;
            }

        case (version_compare($currentVersion, '1.2.1', '<')):
            $content[] = '<pre>1.2.1 updates
---------------
- Improved look with Bootstrapping.</pre>';

            case (version_compare($currentVersion, '1.2.2', '<')):
            $content[] = '<pre>1.2.2 updates
---------------
+ Added gas heat and fiber internet/tv.
</pre>';
    }
    return true;
}

?>
