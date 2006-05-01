<?php

/**
 * Setup class controls the first-time installation of phpwebsite
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Setup{

    function configExists()
    {
        return is_file('config/core/config.php');
    }

    function initConfigSet()
    {
        if (!isset($_SESSION['configSettings'])) {
            $_SESSION['configSettings']['general']  = FALSE;
            $_SESSION['configSettings']['database'] = FALSE;
            Setup::setConfigSet('source_dir', Setup::getSourceDir());
            Setup::setConfigSet('home_dir', Setup::getSourceDir());
            Setup::setConfigSet('site_hash', md5(rand()));
            Setup::setConfigSet('dbname', DEFAULT_DBNAME);
            Setup::setConfigSet('dbuser', DEFAULT_DBUSER);
            Setup::setConfigSet('dbport', DEFAULT_DBPORT);
            Setup::setConfigSet('dbhost', DEFAULT_DBHOST);
            Setup::setConfigSet('dbtype', DEFAULT_DBTYPE);
        }
    }


    function createConfig(&$content)
    {
        Setup::initConfigSet();

        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'postGeneralConfig') {
                if (Setup::postGeneralConfig($content)) {
                    $_SESSION['configSettings']['general'] = TRUE;
                }
            } elseif ($_POST['action'] == 'postDatabaseConfig') {
                if (Setup::postDatabaseConfig($content)) {
                    $_SESSION['configSettings']['database'] = TRUE;
                }
            }
        }

        if ($_SESSION['configSettings']['general'] == FALSE) {
            Setup::generalConfig($content);
        }
        elseif ($_SESSION['configSettings']['database'] == FALSE) {
            Setup::databaseConfig($content);
        }
        else {
            $configDir = Setup::getConfigSet('source_dir') . 'config/core/';
            if (is_file($configDir . 'config.php')) {
                $content[] = _('Your configuration file already exists.');
                $content[] = _('Remove the following file and refresh to continue:');
                $content[] = '<pre>' . $configDir . 'config.php</pre>';
            }
            elseif (Setup::writeConfigFile()) {
                PHPWS_Core::killSession('configSettings');
                $content[] = _('Your configuration file was written successfully!') . '<br />';
                $content[] = '<a href="index.php?step=2">' . _('Move on to Step 2') . '</a>';
            } else {
                $content[] = _('Your configuration file could not be written into the following directory:');
                $content[] = "<pre>$configDir</pre>";
                $content[] = _('Please check your directory permissions and try again.');
                $content[] = '<a href="help/permissions.' . DEFAULT_LANGUAGE . '.txt">' . _('Permission Help') . '</a>';
            }
        }
    }

    function writeConfigFile()
    {
        require_once 'File.php';

        $location = Setup::getConfigSet('source_dir') . 'config/core/';
        if (!is_writable($location)) {
            return FALSE;
        }

        $tpl = & new PHPWS_Template;
        $tpl->setFile('setup/templates/config.tpl', TRUE);
        $tpl->setData($_SESSION['configSettings']);
        $configFile = $tpl->get();

        return File::write($location . 'config.php', $configFile, FILE_MODE_WRITE);
    }

    function postGeneralConfig(&$content)
    {
        $check = TRUE;
        $source_dir = $_POST['source_dir'];

        if (!preg_match('/\/$/', $source_dir)) {
            $source_dir = $source_dir . '/';
        }

        if (!is_dir($source_dir)) {
            $content[] = _('Unable to locate the source directory:') . ' ' . $source_dir;
            $check = FALSE;
        }
        else {
            Setup::setConfigSet('source_dir', $source_dir);
        }


        Setup::setConfigSet('LINUX_PEAR', '//');
        Setup::setConfigSet('WINDOWS_PEAR', '//');

        if ($_POST['pear'] == 'local') {
            if (PHPWS_Core::isWindows()) {
                Setup::setConfigSet('WINDOWS_PEAR', NULL);
            } else {
                Setup::setConfigSet('LINUX_PEAR', NULL);
            }
        }

        Setup::setConfigSet('site_hash', $_POST['site_hash']);
        return $check;
    }

    function postDatabaseConfig(&$content)
    {
        $check = TRUE;
        $currentPW = Setup::getConfigSet('dbpass');

        if (!empty($_POST['dbuser'])) {
            Setup::setConfigSet('dbuser', $_POST['dbuser']);
        } else {
            $content[] = _('Missing a database user name.');
            $check = FALSE;
        }

        if (!empty($_POST['dbpass'])) {
            Setup::setConfigSet('dbpass', $_POST['dbpass']);
        } elseif (empty($currentPW)) {
            $content[] = _('Missing a database password.');
            $check = FALSE;
        }

        Setup::setConfigSet('dbhost', $_POST['dbhost']);

        if (!empty($_POST['dbname'])) {
            Setup::setConfigSet('dbname', $_POST['dbname']);
        } else {
            $content[] = _('Missing a database name.');
            $check = FALSE;
        }

        if (!empty($_POST['dbprefix'])) {
            if (preg_match('/^([a-z])+([a-z0-9_]*)$/i', $_POST['dbprefix'])) {
                Setup::setConfigSet('dbprefix', $_POST['dbprefix']);
            } else {
                $content[] = _('The Table Prefix may only consist of letters, numbers, and the underscore character.');
                $content[] = _('It also may not begin with a number.');
                $check = FALSE;
            }
        }

        Setup::setConfigSet('dbtype', $_POST['dbtype']);
        Setup::setConfigSet('dbport', $_POST['dbport']);


        if (!$check) {
            return FALSE;
        }

        if (CHECK_DB_CONNECTION == FALSE) {
            return TRUE;
        }

        $checkConnection = Setup::testDBConnect();

        if ($checkConnection == 1) {
            return TRUE;
        }
        elseif ($checkConnection == -1) {
            $content[] = _('PhpWebSite was able to connect but the database itself does not exist.');
            $content[] = '<a href="index.php?step=1a">' . _('Do you want phpWebSite to create the database?') . '</a>';
            $content[] = _('If not, you will need to create the database yourself and return to the setup.');
            return FALSE;
        }
        else {
            $content[] = _('Unable to connect to the database with the information provided.');
            $content[] = '<a href="help/database.' . DEFAULT_LANGUAGE . '.txt" target="index">' . _('Database Help') . '</a>';
            return FALSE;
        }

    }


    function createDatabase(&$content)
    {
        $dsn = Setup::getDSN(1);
        $db = & DB::connect($dsn);

        if (PEAR::isError($db)) {
            PHPWS_Error::log($db);
            $content[] = _('Unable to connect.');
            $content[] = _('Check your configuration settings.');
            return FALSE;
        }

        $result = $db->query('CREATE DATABASE ' . Setup::getConfigSet('dbname'));
        if (PEAR::isError($result)) {
            PHPWS_Error::log($db);
            $content[] = _('Unable to create the database.');
            $content[] = _('You will need to create it manually and rerun the setup.');
            return FALSE;
        }

        $dsn = Setup::getDSN(2);
        Setup::setConfigSet('dsn', $dsn);
        $_SESSION['configSettings']['database'] = TRUE;

        $content[] = _('The database creation succeeded!');
        $content[] = '<a href="index.php?step=1">' . _('You can now finish the creation of your config file.') . '</a>';

    }

    function getDSN($mode)
    {
        $dbtype = Setup::getConfigSet('dbtype');
        $dbuser = Setup::getConfigSet('dbuser');
        $dbpass = Setup::getConfigSet('dbpass');
        $dbhost = Setup::getConfigSet('dbhost');
        $dbport = Setup::getConfigSet('dbport');
        $dbname = Setup::getConfigSet('dbname');

        $dsn =  $dbtype . '://' . $dbuser . ':' . $dbpass . '@' . $dbhost;
        if (!empty($dbport)) {
            $dsn .= ':' . $dbport;
        }

        switch ($mode){
        case 1:
            return $dsn;
            break;

        case 2:
            $dsn .= '/' . $dbname;
            return $dsn;
            break;
        }
    }

    function testDBConnect()
    {
        $dsn = Setup::getDSN(1);
        $connection = DB::connect($dsn);

        if (PEAR::isError($connection)) {
            PHPWS_Error::log($connection);
            return 0;
        }
        else {
            $connection->disconnect();

            $dsn = Setup::getDSN(2);
            $result = DB::connect($dsn);

            if (PEAR::isError($result)) {
                // mysql delivers the first error, postgres the second
                if ($result->getCode() == DB_ERROR_NOSUCHDB ||
                    $result->getCode() == DB_ERROR_CONNECT_FAILED) {
                    return -1;
                } else {
                    PHPWS_Error::log($connection);
                    return 0;
                }
            }

            Setup::setConfigSet('dsn', $dsn);
            return 1;
        }

    }

    function setConfigSet($setting, $value)
    {
        $_SESSION['configSettings'][$setting] = $value;
    }

    function getConfigSet($setting)
    {
        if (!isset($_SESSION['configSettings']) || !isset($_SESSION['configSettings'][$setting])) {
            return NULL;
        }

        return $_SESSION['configSettings'][$setting];
    }

    function generalConfig(&$content)
    {

        $form = & new PHPWS_Form('generalConfig');
        $site_hash  = Setup::getConfigSet('site_hash');

        $source_dir = Setup::getConfigSet('source_dir');
        $pear_select = array('local' =>_('Use Pear files included with phpWebSite (recommended).'),
                             'system'=>_('Use server\'s Pear library files (not recommended).')
                             );

        $formTpl['SOURCE_DIR_DEF'] = _('This is the directory where phpWebSite is installed.');

        $formTpl['SITE_HASH_DEF'] = _('The character string below differentiates your site from others on the same server.') . '<br />'
            . _('The example is randomly generated.') . ' '
            . _('You may change it if you wish.');

        $formTpl['PEAR_DEF'] = _('phpWebSite uses the Pear library extensively.') . '<br />'
            . _('We suggest you use the library files included with phpWebSite.');

        $form->add('source_dir', 'textfield', $source_dir);
        $form->setSize('source_dir', 50);
        $form->add('step',   'hidden', '1');
        $form->add('action', 'hidden', 'postGeneralConfig');
        $form->setLabel('source_dir', _('Source Directory')); 

        $form->add('site_hash', 'textfield', $site_hash);
        $form->setSize('site_hash', 40);
        $form->setLabel('site_hash', _('Site Hash'));

        $form->add('pear', 'select', $pear_select);
        $form->setMatch('pear', 'local');
        $form->setLabel('pear', _('Pear Configuration'));

        $form->addSubmit('submit', _('Continue'));

        $form->mergeTemplate($formTpl);

        $content[] = Setup::createForm($form, 'generalConfig.tpl');
    }


    function databaseConfig(&$content)
    {
        $form = & new PHPWS_Form('databaseConfig');
        $form->add('step',   'hidden', '1');
        $form->add('action', 'hidden', 'postDatabaseConfig');

        $databases = array ('mysql' =>'MySQL',
                            'ibase' =>'InterBase',
                            'mssql' =>'Microsoft SQL Server',
                            'msql'  =>'Mini SQL',
                            'oci8'  =>'Oracle 7/8/8i',
                            'odbc'  =>'ODBC',
                            'pgsql' =>'PostgreSQL',
                            'sybase'=>'SyBase',
                            'fbsql' =>'FrontBase',
                            'ifx'   =>'Informix');

        $formTpl['DBTYPE_LBL'] = _('Database Type');
        $formTpl['DBTYPE_DEF'] = _('phpWebSite supports several databases. Choose the type your server currently is running.');

        $formTpl['DBUSER_LBL'] = _('Database User');
        $formTpl['DBUSER_DEF'] = _('This is the user name that phpWebSite will use to access its database.')
            . ' <br /><i>' . _('Note: it is a good idea to give each phpWebSite installation its own user.') . '</i>';

        $formTpl['DBPASS_LBL'] = _('Database Password');
        $formTpl['DBPASS_DEF'] = _('Enter the database\'s user password here.');

        $formTpl['DBHOST_LBL'] = _('Host Specification');
        $formTpl['DBHOST_DEF'] = _('If your database is on the same server as your phpWebSite installation, leave this as &#x22;localhost&#x22;.')
            . '<br />' . _('Otherwise, enter the ip or dns to the database server.');

        $formTpl['DBPORT_LBL'] = _('Host Specification Port');
        $formTpl['DBPORT_DEF'] = _('If your host specification requires access via a specific port, enter it here.');

        $formTpl['DBNAME_LBL'] = _('Database Name');
        $formTpl['DBNAME_DEF'] = _('The database\'s name into which you are installing phpWebSite.')
            . '<br /><i>' . _('Note: if you have not made this database yet, you should do so before continuing.') . '</i>';

        $formTpl['DBPREFIX_LBL'] = _('Table Prefix');
        $formTpl['DBPREFIX_DEF'] = _('If phpWebSite is sharing a database with another application, we suggest you give the tables a prefix.');

        $form->addSelect('dbtype', $databases);
        $form->setMatch('dbtype', Setup::getConfigSet('dbtype'));

        $form->addText('dbuser', Setup::getConfigSet('dbuser'));
        $form->setSize('dbuser', 20);

        $form->addPassword('dbpass', Setup::getConfigSet('dbpass'));
        $form->allowValue('dbpass');
        $form->setSize('dbpass', 20);

        $form->addText('dbhost', Setup::getConfigSet('dbhost'));
        $form->setSize('dbhost', 20);

        $form->addText('dbport', Setup::getConfigSet('dbport'));
        $form->setSize('dbport', 6);

        $form->addText('dbname', Setup::getConfigSet('dbname'));
        $form->setSize('dbname', 20);

        $form->addText('dbprefix', Setup::getConfigSet('dbprefix'));
        $form->setSize('dbprefix', 20);

        $form->mergeTemplate($formTpl);
        $form->addSubmit('default_submit', _('Continue'));
        $content[] = Setup::createForm($form, 'databaseConfig.tpl');
    }

    function createForm($form, $tplFile)
    {
        $template = $form->getTemplate();
        $tpl = & new PHPWS_Template;
        $tpl->setFile("setup/templates/$tplFile", TRUE);
        $tpl->setData($template);

        return $tpl->get();
    }

    function getSourceDir()
    {
        $dir = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);
        for ($i=0; $i < 2; $i++) {
            array_pop($dir);
        }

        return implode(DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;
    }

    function checkDirectories(&$content)
    {
        $errorDir = TRUE;
        $directory[] = Setup::getSourceDir() . 'config/';
        $directory[] = Setup::getSourceDir() . 'images/';
        $directory[] = Setup::getSourceDir() . 'images/mod/';
        $directory[] = Setup::getSourceDir() . 'templates/';
        $directory[] = Setup::getSourceDir() . 'files/';
        $directory[] = Setup::getSourceDir() . 'logs/';
        $directory[] = Setup::getSourceDir() . 'javascript/modules/';

        foreach ($directory as $id=>$check) {
            if (!is_dir($check)) {
                $dirExist[] = $check;
            } elseif (!is_writable($check)) {
                $writableDir[] = $check;
            }
        }

        if (isset($dirExist)) {
            $content[] = _('The following directories need to be created:');
            $content[] = '<pre>' . implode("\n", $dirExist) . '</pre>';
            $errorDir = FALSE;
        }
      
        if (isset($writableDir)) {
            $content[] = _('The following directories are not writable:');
            $content[] = '<pre>' . implode("\n", $writableDir) . '</pre>';
            $content[] = _('You will need to change the permissions.') . '<br />';
            $content[] = '<a href="help/permissions.' . DEFAULT_LANGUAGE . '.txt">' . _('Permission Help') . '</a>';
            $errorDir = FALSE;
        }

        return $errorDir;
    }

    function show($content, $title=NULL)
    {
        $tpl = & new PHPWS_Template;
        $tpl->setFile('setup/templates/setup.tpl', TRUE);
        if (!isset($title)) {
            $title = _('phpWebSite 1.0.0 Beta Setup');
        }

        $setupData['TITLE'] = $title;
        $setupData['MAIN_CONTENT'] = implode('<br />', $content);
        $tpl->setData($setupData);
        return $tpl->get();
    }

    function checkSession(&$content)
    {
        if (!isset($_SESSION['sessionCheck'])) {
            $_SESSION['sessionCheck'] = TRUE;

            if (isset($_REQUEST['check'])) {
                $content[] = _('There is a problem with your sessions.') . '<br />';
                $content[] = _('phpWebSite depends on sessions to move data between pages.') . '<br />';
                $content[] = PHPWS_Text::link('help/sessions.' . DEFAULT_LANGUAGE . '.txt', _('Sessions Help'), NULL, 'index');
                return;
            }
            return FALSE;
        }
        return TRUE;
    }

    function welcome(&$content)
    {
        unset($_SESSION['Boost']);
        $step = 1;
        if (Setup::configExists()) {
            $step = 2;
        }

        include 'setup/welcome.php';

        $content[] = "<a href=\"index.php?step=$step\">" . _('Begin Installation') . '</a>';
        return;
    }

    function createCore(&$content)
    {
        require_once('File.php');
        $content[] = _('Importing core database file.') . '<br />';
        $installSQL = File::readAll('core/boost/install.sql');
        $db = & new PHPWS_DB;
        $result = $db->import($installSQL);

        if (is_array($result)) {
            foreach ($result as $error)
                PHPWS_Error::log($error);
            $content[] = _('Some errors occurred while creating the core database tables.') . '<br />';
            $content[] = _('Please check your error log file.') . '<br />';
            return;
        }

        if ($result == TRUE) {
            $content[] = _('Core installation successful.') . '<br /><br />';
            $content[] = '<a href="index.php?step=3">' . _('Continue to Module Installation') . '</a>';
        }
    }

    function installModules(&$content)
    {
        $modules = PHPWS_Core::coreModList();

        if (!isset($_SESSION['Boost'])) {
            $_SESSION['Boost'] = new PHPWS_Boost;
            $_SESSION['Boost']->loadModules($modules);
        }
        $result = $_SESSION['Boost']->install(FALSE);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('An error occurred while trying to install your modules.') 
                . ' ' . _('Please check your error logs and try again.');
            return TRUE;
        } else {
            $content[] =  $result;
        }

        if ($_SESSION['Boost']->isFinished()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function finish(&$content)
    {
        $content[] = '<hr />';
        $content[] = _('Installation of phpWebSite is complete.') . '<br />';
        $content[] = _('If you experienced any error messages, check your error.log file.') . '<br />';
        if (CHECK_DIRECTORY_PERMISSIONS) {
            $content[] = _('Check Directory Permissions is enabled so be sure to secure your config and templates directories!');
            $content[] = _('If you do not change it now, your next page will be an error screen.');
        } else {
            $content[] = _('After you finish installing your modules in Boost, you should make your config and template directories non-writable.');
        }
        $content[] = '<a href="../index.php">' . _('Go to my new website!') . '</a>' . '<br />';

    }

}

?>