<?php
/**
 * Uninstall file for PhatForm v2
 *
 * Rewritten to work with phpwebsite 1.0
 * @version $Id: uninstall.php 2 2006-04-27 20:24:18Z matt $
 */

function phatform_uninstall(&$content) {
    $db = & new PHPWS_DB('mod_phatform_forms');
    $db->addColumn('id');
    $db->addColumn('archiveTableName');
    $db->addWhere('saved', 1);
    $result = $db->select();
    
    if (!empty($result)) {
        foreach ($result as $form) {
            if (empty($form['archiveTableName'])) {
                $table = 'mod_phatform_form_' . $form['id'];
                if (PHPWS_DB::isTable($table)) {
                    PHPWS_DB::dropTable($table);
                }
            } else {
                $table = $form['archiveTableName'];
                PHPWS_DB::dropTable($table);
            }
        }
        $content[] = _('Removed all dynamic Form Generator tables.');
    }

    PHPWS_DB::dropTable('mod_phatform_forms');
    PHPWS_DB::dropTable('mod_phatform_options');
    PHPWS_DB::dropTable('mod_phatform_textfield');
    PHPWS_DB::dropTable('mod_phatform_textarea');
    PHPWS_DB::dropTable('mod_phatform_dropbox');
    PHPWS_DB::dropTable('mod_phatform_multiselect');
    PHPWS_DB::dropTable('mod_phatform_radiobutton');
    PHPWS_DB::dropTable('mod_phatform_checkbox');
    $content[] = _('All Form Generator static tables removed.');

    return TRUE;
}

?>