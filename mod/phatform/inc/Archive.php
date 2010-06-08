<?php
/**
 * @version $Id$
 * @author Adam Morton
 * @author Steven Levin
 */

function archive($formId = NULL) {
    if(!isset($formId)) {
        $message = dgettext('phatform', 'No form ID was passed');
        return new Core\Error('phatform', 'archive()', $message, 'continue', PHAT_DEBUG_MODE);
    }

    $archiveDir = PHPWS_HOME_DIR . 'files/phatform/archive/';
    $path = $archiveDir;

    clearstatcache();
    if(!is_dir($path)) {
        if(is_writeable($archiveDir)) {
            Core\File::makeDir($path);
        } else {
            return Core\Error::get(PHATFORM_ARCHIVE_PATH, 'phatform', 'Archive.php::archive', $path);
        }
    } else if(!is_writeable($path)) {
        return Core\Error::get(PHATFORM_ARCHIVE_PATH, 'phatform', 'Archive.php::archive()');
    }

    $table = array();
    $time = time();

    $table[] = 'mod_phatform_forms';
    $table[] = 'mod_phatform_forms_seq';
    $table[] = 'mod_phatform_form_' . $formId;
    $table[] = 'mod_phatform_form_' . $formId . '_seq';
    $table[] = 'mod_phatform_multiselect';
    $table[] = 'mod_phatform_multiselect_seq';
    $table[] = 'mod_phatform_checkbox';
    $table[] = 'mod_phatform_checkbox_seq';
    $table[] = 'mod_phatform_dropbox';
    $table[] = 'mod_phatform_dropbox_seq';
    $table[] = 'mod_phatform_options';
    $table[] = 'mod_phatform_options_seq';
    $table[] = 'mod_phatform_radiobutton';
    $table[] = 'mod_phatform_radiobutton_seq';
    $table[] = 'mod_phatform_textarea';
    $table[] = 'mod_phatform_textarea_seq';
    $table[] = 'mod_phatform_textfield';
    $table[] = 'mod_phatform_textfield_seq';


    $step1 = explode('//', PHPWS_DSN);

    $step2 = explode('@', $step1[1]);

    $step3 = explode(':', $step2[0]);

    $step4 = explode('/', $step2[1]);

    $dbuser = $step3[0];
    $dbpass = $step3[1];

    $dbhost = $step4[0];
    $dbname = $step4[1];

    for($i=0; $i<sizeof($table); $i++) {
        $pipe = ' >> ';
        if($i == 0) {$pipe = ' > ';}
        $goCode = 'mysqldump -h' . $dbhost . ' -u' . $dbuser . ' -p' . $dbpass . ' ' . $dbname . ' ' . $table[$i]  . $pipe . $path . $formId . '.' . $time . '.phat';
        system($goCode);
    }
}

?>