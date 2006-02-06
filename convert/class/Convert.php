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

        case 'convert':
            $this->convertPackage($_REQUEST['package']);
            break;

        case 'make_connection':
            if ($this->checkConnection()) {
                $this->main();
            } else {
                $this->establishConnection(_('Unable to log in to the database. Please check your settings.'));
            }

            break;

        case 'default':
            $this->main();
            break;
        }

    }



    function checkConnection()
    {
        $dbtype = $_POST['type'];
        $dbuser = $_POST['username'];
        $dbpass = $_POST['password'];
        if (!isset($_POST['host'])) {
            $dbhost = 'localhost';
        } else {
            $dbhost = $_POST['host'];
        }

        $dbname = $_POST['db_name'];
        $dbport = $_POST['port'];

        $dsn =  $dbtype . '://' . $dbuser . ':' . $dbpass . '@' . $dbhost . '/' . $dbname;
        if (!empty($dbport)) {
            $dsn .= ':' . $dbport;
        }

        $db = & DB::connect($dsn);

        if (PEAR::isError($db)) {
            return FALSE;
        } else {
            $_SESSION['OTHER_DATABASE'] = $dsn;
            return TRUE;
        }

    }

    function &getSourceDB($table)
    {
        $dsn = $_SESSION['OTHER_DATABASE'];

        $db = & new PHPWS_DB($table);
        $db->loadDB($dsn);
        $db->setTable($table);
        return $db;
    }

    function showPackages()
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
                $template['convert_mods'][] = $this->convertLinkTpl($info_file, $mod_dir);
            }
        }

        $template['TITLE_LABEL'] = _('Title');
        $template['DESCRIPTION_LABEL'] = _('Description');

        $content = PHPWS_Template::process($template, '', 'convert/templates/list.tpl', TRUE);
        $this->show($content);

    }


    function main()
    {
        if (!isset($_SESSION['OTHER_DATABASE'])) {
            $this->establishConnection();
        } else {
            $this->showPackages();
        }
    }

    function establishConnection($message=NULL)
    {

        $username = $type = $port = $password = $name = NULL;

        if (isset($_POST['name'])) {
            $name = $_POST['name'];
        }

        if (isset($_POST['username'])) {
            $name = $_POST['username'];
        }

        if (isset($_POST['password'])) {
            $password = $_POST['password'];
        }

        if (isset($_POST['type'])) {
            $type = $_POST['type'];
        }

        if (isset($_POST['host'])) {
            $host = $_POST['host'];
        } else {
            $host = 'localhost';
        }

        if (isset($_POST['port'])) {
            $port = $_POST['port'];
        }

        $db_list = array ('mysql' =>'MySQL',
                          'ibase' =>'InterBase',
                          'mssql' =>'Microsoft SQL Server',
                          'msql'  =>'Mini SQL',
                          'oci8'  =>'Oracle 7/8/8i',
                          'odbc'  =>'ODBC',
                          'pgsql' =>'PostgreSQL',
                          'sybase'=>'SyBase',
                          'fbsql' =>'FrontBase',
                          'ifx'   =>'Informix');

        $form = & new PHPWS_Form;
        $form->addHidden('command', 'make_connection');
        
        $form->addSelect('type', $db_list);
        $form->setMatch('type', $type);
        $form->setLabel('type', _('Type'));

        $form->addText('db_name', $name);
        $form->setLabel('db_name', _('Database name'));

        $form->addText('username', $username);
        $form->setLabel('username', _('User name'));

        $form->addPassword('password', $password);
        $form->setLabel('password', _('Password'));

        $form->addText('host', $host);
        $form->setLabel('host', _('Host'));

        $form->addText('port', $port);
        $form->setLabel('port', _('Port'));
        $form->addSubmit(_('Connect'));
        $template = $form->getTemplate();

        $template['DIRECTIONS'] = _('Please enter the connect information for the database you wish to convert from.');
        if (isset($message)) {
            $template['MESSAGE'] = $message;
        }

        $content = PHPWS_Template::process($template, '', 'convert/templates/database.tpl', TRUE);

        $this->show($content);
    }

    function convertLinkTpl($info_file, $mod_dir)
    {
        $convert_info = parse_ini_file($info_file);
        if (isset($convert_info['convert']) && Convert::isConverted($convert_info['convert'])) {
            $link = _('Converted');
        } else {
            $link = sprintf('<a href="index.php?command=convert&amp;package=%s">%s</a>', $mod_dir, _('Convert!'));
        }

        $tpl['TITLE'] = $convert_info['title'];
        $tpl['DESCRIPTION'] = $convert_info['description'];
        $tpl['LINK']    = $link;
        return $tpl;
    }

    function show($content, $title=NULL){
        if (!isset($title)) {
            $title = _('phpWebSite 1.0.0 Convert');
        }

        $setupData['MAIN_LINK'] = sprintf('<a href="index.php?command=default">%s</a>', _('Main page'));

        $setupData['TITLE']     = $title;
        $setupData['CONTENT']   = $content;
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

    function loginForm($message=NULL)
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
        if (isset($message)) {
            $template['MESSAGE'] = $message;
        }

        $content = PHPWS_Template::process($template, '', 'convert/templates/login.tpl', TRUE);

        $this->show($content);
    }

    function convertPackage($package)
    {
        $filename = sprintf('convert/modules/%s/convert.php', $package);
        if (!is_file($filename)) {
            $this->show(_('Not a convert file.'));
            return;
        }
            
        include $filename;
        $result = convert();

        $this->show($result);
    }

    function removeConvert($name)
    {
        $db = & new PHPWS_DB('converted');
        $db->addWhere('convert_name', $name);
        return $db->delete();
    }

    function addConvert($name)
    {
        $db = & new PHPWS_DB('converted');
        $db->addValue('convert_name', $name);
        return $db->insert();
    }

    function isConverted($name) {
        $db = & new PHPWS_DB('converted');
        $db->addWhere('convert_name', $name);
        $result = $db->select();
        if (PEAR::isError($result)) {
            return $result;
        } else  {
            return !empty($result);
        }
    }

}

?>
