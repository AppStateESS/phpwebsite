<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
translate('access');
$errors = array(SHORTCUT_BAD_KEYWORD     => _('Bad keyword. Use only alphanumeric characters, dashes and spaces.'),
                SHORTCUT_WORD_IN_USE     => _('Keyword already in use. Choose another.'),
                SHORTCUT_MISSING_KEYWORD => _('Missing keyword.'),
                SHORTCUT_MISSING_URL     => _('Missing url.'),
                ACCESS_FILES_DIR         => _('The files/access/ directory is not writable.'),
                ACCESS_HTACCESS_WRITE    => _('Unable to write .htaccess file.'),
                ACCESS_HTACCESS_MISSING  => _('.htaccess file is missing.')
                );

translate();
?>