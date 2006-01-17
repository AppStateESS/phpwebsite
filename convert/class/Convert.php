<?php

class Convert {
    function action()
    {
        if (!isset($_REQUEST['command'])) {
            $command = 'default';
        } else {
            $command = $_REQUEST['command'];
        }

        if (!$this->checkLogged() && $command != 'login') {
            $this->loginForm();
            return;
        }

        switch ($command) {
        case 'login':
            PHPWS_Core::initModClass('users', 'Action.php');
            if ($this->login()) {
                $this->main();
            } else {
                PHPWS_Core::killAllSessions();
                $this->loginForm();
            }
            break;

        case 'default':
            $this->main();
            break;
        }

    }


    function main()
    {
        PHPWS_Core::initCoreClass('File.php');
        $predir = 'convert/modules/';

        $directories = PHPWS_File::listDirectories($predir);

        if (empty($directories)) {
            $this->show(_('No modules to convert.'));
            return;
        }

        foreach ($directories as $mod_dir) {
            $filename = translateFile('info.ini');
            $info_file = $predir . $mod_dir . '/' . $filename;
            if (is_file($info_file)) {
                $template['convert_mods'][] = $this->convertLinkTpl($info_file);
            }
        }

        $template['TITLE_LABEL'] = _('Title');
        $template['DESCRIPTION_LABEL'] = _('Description');


        $content = PHPWS_Template::process($template, '', 'convert/templates/list.tpl', TRUE);
        $this->show($content);
    }

    function convertLinkTpl($info_file)
    {
        $convert_info = parse_ini_file($info_file);
        $link = '<a href="#">Test</a>';
        $tpl['TITLE'] = $convert_info['title'];
        $tpl['DESCRIPTION'] = $convert_info['description'];
        $tpl['LINK']    = $link;
        return $tpl;
    }

    function show($content, $title=NULL){
        if (!isset($title)) {
            $title = _('phpWebSite 1.0.0 Convert');
        }

        $setupData['TITLE']   = $title;
        $setupData['CONTENT'] = $content;
        echo PHPWS_Template::process($setupData, '', 'convert/templates/convert.tpl', TRUE);
    }


    function login()
    {
        if (!User_Action::loginUser($_POST['phpws_username'], $_POST['phpws_password'])) {
            return FALSE;
        } elseif (!Current_User::isDeity()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function checkLogged()
    {
        if (Current_User::isLogged() && Current_User::isDeity()) {
            return TRUE;
        }

        return FALSE;
    }

    function loginForm()
    {
        if (isset($_REQUEST['phpws_username'])) {
            $username = $_REQUEST['phpws_username'];
        } else {
            $username = NULL;
        }

        $form = & new PHPWS_Form('User_Login');
        $form->addHidden('command', 'login');
        $form->addText('phpws_username', $username);
        $form->addPassword('phpws_password');
        $form->addSubmit('submit', _('Log in'));

        $form->setLabel('phpws_username', _('Username'));
        $form->setLabel('phpws_password', _('Password'));
    
        $template = $form->getTemplate();

        $content = PHPWS_Template::process($template, '', 'convert/templates/login.tpl', TRUE);

        $this->show($content);
    }

}

?>
