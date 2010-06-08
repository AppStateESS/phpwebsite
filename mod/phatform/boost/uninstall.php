<?php
/**
 * Uninstall file for PhatForm v2
 *
 * Rewritten to work with phpwebsite 1.0
 * @version $Id$
 */

function phatform_uninstall(&$content) {

    $db = new \core\DB('mod_phatform_forms');
    $db->addColumn('id');
    $db->addColumn('archiveTableName');
    $db->addWhere('saved', 1);
    $result = $db->select();

    if (!empty($result)) {
        foreach ($result as $form) {
            if (empty($form['archiveTableName'])) {
                $table = 'mod_phatform_form_' . $form['id'];
                if (core\DB::isTable($table)) {
                    \core\DB::dropTable($table);
                }
            } else {
                $table = $form['archiveTableName'];
                \core\DB::dropTable($table);
            }
        }
        $content[] = dgettext('phatform', 'Removed all dynamic Form Generator tables.');
    }

    \core\DB::dropTable('mod_phatform_forms');
    \core\DB::dropTable('mod_phatform_options');
    \core\DB::dropTable('mod_phatform_textfield');
    \core\DB::dropTable('mod_phatform_textarea');
    \core\DB::dropTable('mod_phatform_dropbox');
    \core\DB::dropTable('mod_phatform_multiselect');
    \core\DB::dropTable('mod_phatform_radiobutton');
    \core\DB::dropTable('mod_phatform_checkbox');
    $content[] = dgettext('phatform', 'All Form Generator static tables removed.');

    return TRUE;
}

?>