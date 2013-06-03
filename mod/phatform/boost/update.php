<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function phatform_update(&$content, $version)
{
    switch ($version) {
        case version_compare($version, '3.0.2', '<'):
            $content[] = '- Fixed compatibility issues.';

        case version_compare($version, '3.0.3', '<'):
            $content[] = '- Fixed element move bug.';

        case version_compare($version, '3.0.4', '<'):
            $content[] = '<pre>
3.0.4 changes
-------------
+ Simplified install.sql
+ Fixed some incompatible errorMessage function calls
</pre>';

        case version_compare($version, '3.0.5', '<'):
            $content[] = '<pre>
3.0.5 changes
-------------
+ Fixed typo in Form_Manager class causing crashes.
</pre>';

        case version_compare($version, '3.0.6', '<'):
            $content[] = '<pre>
3.0.6 changes
-------------
+ Added translate call.
+ Added missing "export" directory creation.
+ Removed all global core calls
+ Fixed email bug.
</pre>';

        case version_compare($version, '3.1.0', '<'):
            PHPWS_Boost::updateFiles(array('img/phatform.png'), 'phatform');
            $content[] = '<pre>
3.1.0 changes
-------------
+ Added German translations
+ Update language functions.
+ Changed control panel icon
</pre>';

        case version_compare($version, '3.1.1', '<'):
            $content[] = '<pre>
3.1.1 changes
-------------
+ Fixed bug #1785639. Unable to move elements up and down.
+ Fixed bug #1785626. Unable to delete option set.
+ Fixed bug #1785585. List archive would not function.
+ Reduced control panel info.
</pre>';

        case version_compare($version, '3.1.2', '<'):
            $content[] = '<pre>
3.1.2 changes
-------------
+ Fixed error construction in some element save functions.
+ Element now prohibits element names matching preexisting column names.
</pre>';

        case version_compare($version, '3.1.3', '<'):
            if (!PHPWS_Boost::updateFiles(array('templates/form/form.tpl'),
                            'phatform')) {
                $content[] = 'Failed copying templates/form/form.tpl';
            }
            $content[] = '<pre>
3.1.3 changes
-------------
+ added Captcha to anonymous user forms
+ Checking elements against database restricted names.
</pre>';

        case version_compare($version, '3.1.4', '<'):
            PHPWS_Boost::updateFiles(array('conf/phatform.php'), 'phatform');
            $content[] = '<pre>
3.1.4 changes
-------------
+ RFE # 2609260 - PHATFORM_CAPTCHA now definable in configuration file.
+ Preventing numbers from being element names.
</pre>';

        case version_compare($version, '3.1.5', '<'):
            $content[] = '<pre>
3.1.5 changes
-------------
+ mod_rewrite works with form.
+ Page selection fix.
+ Fixed element deletion.
+ Some PHP 5 strict fixes.</pre>';

        case version_compare($version, '3.1.6', '<'):
            $content[] = '<pre>
3.1.6 changes
-------------
+ Introduction passed through text parser
+ Timed out session give warning instead of forcing login</pre>';

        case version_compare($version, '3.1.7', '<'):
            $content[] = '<pre>
3.1.7 changes
-------------
+ Fixed isset check that should have used empty
</pre>';

        case version_compare($version, '3.1.8', '<'):
            $content[] = '<pre>
3.1.8 changes
-------------
+ Fixed newline bug.
+ Fixed notice error message display.
</pre>';

        case version_compare($version, '3.1.9', '<'):
            if (!is_file(PHPWS_HOME_DIR . 'files/phatform/.htaccess')) {
                copy(PHPWS_SOURCE_DIR . 'mod/phatform/boost/htaccess',
                        PHPWS_HOME_DIR . 'files/phatform/.htaccess');
            }
            $content[] = '<pre>
3.1.9 changes
-------------
+ Added .htaccess to files/phatform to prevent direct accecss
</pre>';
    }
    return true;
}

?>