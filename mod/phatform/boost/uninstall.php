<?php
/**
 * Uninstall file for PhatForm v2
 *
 * Rewritten to work with phpwebsite 1.0
 * @version $Id$
 */

function phatform_uninstall(&$content) {

    $db = new Core\DB('mod_phatform_forms');
    $db->addColumn('id');
    $db->addColumn('archiveTableName');
    $db->addWhere('saved', 1);
    $result = $db->select();

    if (!empty($result)) {
        foreach ($result as $form) {
            if (empty($form['archiveTableName'])) {
                $table = 'mod_phatform_form_' . $form['id'];
                if (Core\DB::isTable($table)) {
                    Core\DB::dropTable($table);
                }
            } else {
                $table = $form['archiveTableName'];
                Core\DB::dropTable($table);
            }
        }
        $content[] = dgettext('phatform', 'Removed all dynamic Form Generator tables.');
    }

    Core\DB::dropTable('mod_phatform_forms');
    Core\DB::dropTable('mod_phatform_options');
    Core\DB::dropTable('mod_phatform_textfield');
    Core\DB::dropTable('mod_phatform_textarea');
    Core\DB::dropTable('mod_phatform_dropbox');
    Core\DB::dropTable('mod_phatform_multiselect');
    Core\DB::dropTable('mod_phatform_radiobutton');
    Core\DB::dropTable('mod_phatform_checkbox');
    $content[] = dgettext('phatform', 'All Form Generator static tables removed.');

    return TRUE;
}

?>