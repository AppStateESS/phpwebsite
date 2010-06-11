<?php

/**
 * Administration interface
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Version_Admin {
    public static function main()
    {
        $message = $title = $content = NULL;

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = 'settings';
        }

        switch ($command) {
            case 'settings':
                $title = dgettext('version', 'Version settings');
                $content = Version_Admin::settings();
                break;

            case 'post_setting':
                PHPWS_Settings::set('version', 'saved_versions', $_REQUEST['saved_versions']);
                PHPWS_Settings::save('version');
                $title = dgettext('version', 'Version settings');
                $message = dgettext('version', 'Settings saved.');
                $content = Version_Admin::settings();
                break;

        }

        $template['TITLE']   = $title;
        $template['CONTENT'] = $content;
        $template['MESSAGE'] = $message;

        Layout::add(PHPWS_ControlPanel::display(PHPWS_Template::process($template, 'version', 'main.tpl')));
    }

    public static function settings()
    {
        $versions[0] = dgettext('version', 'Keep all versions');
        $versions[5] = 5;
        $versions[10] = 10;
        $versions[25] = 25;
        $versions[50] = 50;

        $version_number = PHPWS_Settings::get('version', 'saved_versions');

        $form = new PHPWS_Form;
        $form->addHidden('module', 'version');
        $form->addHidden('command', 'post_setting');
        $form->addSelect('saved_versions', $versions);
        $form->setMatch('saved_versions', $version_number);
        $form->setLabel('saved_versions', dgettext('version', 'Versions saved'));

        $form->addSubmit(dgettext('version', 'Save'));
        $template = $form->getTemplate();

        $content = PHPWS_Template::process($template, 'version', 'settings.tpl');
        return $content;
    }
}

?>