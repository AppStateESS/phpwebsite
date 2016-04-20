<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
function phatform_install(&$content)
{
    if (!is_dir('files/phatform/')) {
        mkdir('files/phatform/');
    }
    if (!is_dir('files/phatform/archive')) {
        mkdir('files/phatform/archive/');
    }
    if (!is_dir('files/phatform/report')) {
        mkdir('files/phatform/report/');
    }
    if (!is_dir('files/phatform/export')) {
        mkdir('files/phatform/export/');
    }
    if (!is_file('files/phatform/.htaccess')) {
        copy(PHPWS_SOURCE_DIR . 'mod/phatform/boost/htaccess', PHPWS_HOME_DIR . 'files/phatform/.htaccess');
    }
    return true;
}
