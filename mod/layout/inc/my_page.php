<?php

function my_page()
{
    translate('layout');
    $title = $content =  NULL;

    if (@$message = $_SESSION['Layout_User_Message']) {
        unset($_SESSION['Layout_User_Message']);
    }

    if (isset($_SESSION['Reset_Layout'])) {
        unset($_SESSION['Reset_Layout']);
        Layout::reset();
    }

    if (!(@$lo_command = $_REQUEST['lo_command'])) {
        $lo_command = 'user_form';
    }

    switch ($lo_command) {
    case 'user_form':
        $title = _('Display settings');
        $content = Layout_User_Settings::user_form(); 
        break;

    case 'save_settings':
        Layout_User_Settings::save_settings();
        $_SESSION['Reset_Layout'] = 1;
        $_SESSION['Layout_User_Message'] = _('Settings saved');
        PHPWS_Core::reroute('index.php?module=users&action=user&tab=layout');
        break;
    }

    $tpl['TITLE']   = $title;
    $tpl['CONTENT'] = $content;
    $tpl['MESSAGE'] = $message;
    translate();
    return PHPWS_Template::process($tpl, 'layout', 'main.tpl');
}


class Layout_User_Settings {
    function user_form()
    {
        $form = & new PHPWS_Form;
        My_Page::addHidden($form, 'layout');

        $form->addHidden('lo_command', 'save_settings');

        $css = Layout::getAlternateStyles();
        if ($css) {
            $form->addSelect('alternate', $css);
            $form->setMatch('alternate', PHPWS_Cookie::read('layout_style'));
            $form->setLabel('alternate', _('Available styles'));
            $form->addSubmit(_('Save settings'));
        } else {
            $blank = _('No alternate style sheets available.');
            return $blank;
        }

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'user_form.tpl');
    }

    function save_settings()
    {
        if (isset($_POST['alternate'])) {
            PHPWS_Cookie::write('layout_style', $_POST['alternate']);
            return TRUE;
        }
    }
}

?>