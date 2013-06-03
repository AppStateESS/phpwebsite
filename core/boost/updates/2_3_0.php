<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
function update_core_2_3_0()
{
    $db = Database::newDB();
    $dtable = $db->addTable('modules');
    if (!$dtable->columnExists('deprecated')) {
        $deprecated = $dtable->addDataType('deprecated', 'smallint');
        $deprecated->setDefault(1);
        $deprecated->add();
    }

    $db2 = Database::newDB();
    $db2->addTable('mod_settings');
    $result = $db2->select();

    if (!empty($result)) {
        Settings::createSettingsTable();
        $db3 = \Database::newDB();
        $settings = $db3->addTable('settings');
        $db3->delete();
        foreach ($result as $v) {
            $module = $setting_name = $setting_type = $small_num = $large_num =
                    $small_char = $large_char = null;
            extract($v);
            $settings->addValue('module_name', $module);
            $settings->addValue('variable_name', $setting_name);

            switch ($setting_type) {
                case 1:
                    $settings->addValue('setting', $small_num);
                    break;

                case 2:
                    $settings->addValue('setting', $large_num);
                    break;

                case 3:
                    $settings->addValue('setting', $small_char);
                    break;

                case 4:
                    $settings->addValue('setting', $large_char);
                    break;
            }
            $settings->insert();
            $settings->resetValues();
        }
    }
}

?>
