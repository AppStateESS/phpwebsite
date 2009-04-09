<?php

/**
 * Setup class controls the first-time installation of phpwebsite
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (strstr($_SERVER['SCRIPT_FILENAME'], '\\')) {
    define('DIRECTORY_SLASH', '\\');
} else {
    define('DIRECTORY_SLASH', '/');
}

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
            // Could use some windows checking here
            Setup::setConfigSet('cache_directory', '/tmp/');
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

        $messages = array();

        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'postGeneralConfig' && !SKIP_STEP_1) {
                if (Setup::postGeneralConfig($content, $messages)) {
                    $_SESSION['configSettings']['general'] = TRUE;
                }
            } elseif ($_POST['action'] == 'postDatabaseConfig') {
                if (Setup::postDatabaseConfig($content, $messages)) {
                    $_SESSION['configSettings']['database'] = TRUE;
                }
            }
        }

        if (SKIP_STEP_1) {
            $dir = getcwd() . '/';
            Setup::setConfigSet('source_dir', $dir);
            Setup::setConfigSet('home_dir', $dir);
            Setup::setConfigSet('LINUX_PEAR', '//');
            Setup::setConfigSet('WINDOWS_PEAR', '//');
            if (PHPWS_Core::isWindows()) {
                Setup::setConfigSet('WINDOWS_PEAR', NULL);
            } else {
                Setup::setConfigSet('LINUX_PEAR', NULL);
            }
            Setup::setConfigSet('site_hash', md5(rand()));
            $_SESSION['configSettings']['general'] = true;
        }

        if ($_SESSION['configSettings']['general'] == FALSE) {
            Setup::generalConfig($content, $messages);
        }
        elseif ($_SESSION['configSettings']['database'] == FALSE) {
            Setup::databaseConfig($content, $messages);
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

        $tpl = new PHPWS_Template;
        $tpl->setFile('core/inc/config.tpl', TRUE);
        $tpl->setData($_SESSION['configSettings']);
        $configFile = $tpl->get();
        return File::write($location . 'config.php', $configFile, FILE_MODE_WRITE);
    }

    function postGeneralConfig(&$content, &$messages)
    {
        $check = TRUE;
        $source_dir = addslashes($_POST['source_dir']);
        $match = sprintf('/%s$/', preg_quote(DIRECTORY_SLASH, '/'));
        if (!preg_match($match, $source_dir)) {
            $source_dir = $source_dir . DIRECTORY_SLASH;
        }

        if (!is_dir($source_dir)) {
            $messages['source_dir'] = _('Unable to locate the source directory:') . ' ' . $source_dir;
            $check = FALSE;
        }
        else {
            Setup::setConfigSet('source_dir', $source_dir);
	    Setup::setConfigSet('home_dir', $source_dir);
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

        if (empty($_POST['site_hash'])) {
            $messages['site_hash'] = _('Site hash may not be empty.');
            $check = false;
        } else {
            Setup::setConfigSet('site_hash', $_POST['site_hash']);
        }
        return $check;
    }

    function postDatabaseConfig(&$content, &$messages)
    {
        $check = TRUE;
        $currentPW = Setup::getConfigSet('dbpass');

        if (!empty($_POST['dbuser'])) {
            Setup::setConfigSet('dbuser', $_POST['dbuser']);
        } else {
            $messages['dbuser'] = _('Missing a database user name.');
            $check = FALSE;
        }

        if (!empty($_POST['dbpass'])) {
            if (preg_match('/[^\w\s\.!\?]/', $_POST['dbpass'])) {
                $messages['dbpass'] = _('Database password may contain alphanumeric characters, punctuation, spaces and underscores only.');
                $check = false;
            } else {
                Setup::setConfigSet('dbpass', $_POST['dbpass']);
            }
        } elseif (empty($currentPW)) {
            $messages['dbpass'] = _('Missing a database password.');
            $check = FALSE;
        }

        Setup::setConfigSet('dbhost', $_POST['dbhost']);

        if (!empty($_POST['dbname'])) {
            Setup::setConfigSet('dbname', $_POST['dbname']);
        } else {
            $messages['dbname'] = _('Missing a database name.');
            $check = FALSE;
        }

        if (!empty($_POST['dbprefix'])) {
            if (preg_match('/\W/', $_POST['dbprefix'])) {
                $messages['dbpref'] = _('Table prefix must be alphanumeric characters or underscores only');
                $check = FALSE;
            } else {
                Setup::setConfigSet('dbprefix', $_POST['dbprefix']);
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
        } elseif ($checkConnection == 2) {
            $sub['main'] = _('PhpWebSite was able to connect, but the database already contained tables.');
            if (Setup::getConfigSet('dbprefix')) {
                $sub[] = _('Since you set a table prefix, you may force an installation into this database.');
                $sub[] = _('Click the link below to continue or change your connection settings.');
                $sub[] = sprintf('<a href="index.php?step=1b">%s</a>',_('I want to install phpWebSite in this database.'));
            } else {
                $_SESSION['configSettings']['database'] = FALSE;
            }
            $messages['main'] = implode('<br />', $sub);
            return FALSE;
        }
        elseif ($checkConnection == -1) {
            $sub[] = _('PhpWebSite was able to connect but the database itself does not exist.');
            $sub[] = '<a href="index.php?step=1a">' . _('Do you want phpWebSite to create the database?') . '</a>';
            $sub[] = _('If not, you will need to create the database yourself and return to the setup.');
            $messages['main'] = implode('<br />', $sub);
            return FALSE;
        }
        else {
            $sub[] = _('Unable to connect to the database with the information provided.');
            $sub[] = '<a href="help/database.' . DEFAULT_LANGUAGE . '.txt" target="index">' . _('Database Help') . '</a>';
            $messages['main'] = implode('<br />', $sub);
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
            if ($dbtype == 'pgsql') {
                return $dsn . '/' . $dbname;
            } else {
                return $dsn;
            }
            break;

        case 2:
            $dsn .= '/' . $dbname;
            return $dsn;
            break;
        }
    }

    function testDBConnect($dsn=null)
    {
        if (empty($dsn)) {
            $dsn = Setup::getDSN(1);
            $connection = DB::connect($dsn);

            if (PEAR::isError($connection)) {
                PHPWS_Error::log($connection);
                return 0;
            }
            $connection->disconnect();
            $dsn = Setup::getDSN(2);
        }

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
        
        $tables = $result->getlistOf('tables');
        if (count($tables)) {
            return 2;
        }
        
        Setup::setConfigSet('dsn', $dsn);
        return 1;
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

    function generalConfig(&$content, $messages)
    {

        $form = new PHPWS_Form('generalConfig');
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

        if (isset($messages['source_dir'])) {
            $formTpl['SOURCE_DIR_ERR'] = $messages['source_dir'];
        }


        if (isset($messages['site_hash'])) {
            $formTpl['SITE_HASH_ERR'] = $messages['site_hash'];
        }


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


    function databaseConfig(&$content, $messages)
    {
        if (isset($messages['main'])) {
            $formTpl['MAIN'] = $messages['main'];
        }
        $form = new PHPWS_Form('databaseConfig');
        $form->add('step',   'hidden', '1');
        $form->add('action', 'hidden', 'postDatabaseConfig');

        $databases = array ('mysql' =>'MySQL',
                            'pgsql' =>'PostgreSQL');

        $formTpl['DBTYPE_LBL'] = _('Database Type');
        $formTpl['DBTYPE_DEF'] = _('phpWebSite supports MySQL and PostgreSQL. Choose the type your server currently is running.');

        $formTpl['DBUSER_LBL'] = _('Database User');
        $formTpl['DBUSER_DEF'] = _('This is the user name that phpWebSite will use to access its database.')
            . ' <br /><i>' . _('Note: it is a good idea to give each phpWebSite installation its own user.') . '</i>';
        if (isset($messages['dbuser'])) {
            $formTpl['DBUSER_ERR'] = $messages['dbuser'];
        }

        $formTpl['DBPASS_LBL'] = _('Database Password');
        $formTpl['DBPASS_DEF'] = _('Enter the database\'s user password here.');
        if (isset($messages['dbpass'])) {
            $formTpl['DBPASS_ERR'] = $messages['dbpass'];
        }


        $formTpl['DBPREF_LBL'] = _('Table prefix');
        $formTpl['DBPREF_DEF'] = _('If you are installing phpWebSite in a shared environment, you may assign a prefix to tables.<br />We recommend you run without one.');
        if (isset($messages['dbpref'])) {
            $formTpl['DBPREF_ERR'] = $messages['dbpref'];
        }


        $formTpl['DBHOST_LBL'] = _('Host Specification');
        $formTpl['DBHOST_DEF'] = _('If your database is on the same server as your phpWebSite installation, leave this as &#x22;localhost&#x22;.')
            . '<br />' . _('Otherwise, enter the ip or dns to the database server.');

        $formTpl['DBPORT_LBL'] = _('Host Specification Port');
        $formTpl['DBPORT_DEF'] = _('If your host specification requires access via a specific port, enter it here.');

        $formTpl['DBNAME_LBL'] = _('Database Name');
        $formTpl['DBNAME_DEF'] = _('The database\'s name into which you are installing phpWebSite.')
            . '<br /><i>' . _('Note: if you have not made this database yet, you should do so before continuing.') . '</i>';
        if (isset($messages['dbname'])) {
            $formTpl['DBNAME_ERR'] = $messages['dbname'];
        }


        $form->addSelect('dbtype', $databases);
        $form->setMatch('dbtype', Setup::getConfigSet('dbtype'));

        $form->addText('dbuser', Setup::getConfigSet('dbuser'));
        $form->setSize('dbuser', 20);

        $form->addPassword('dbpass', Setup::getConfigSet('dbpass'));
        $form->allowValue('dbpass');
        $form->setSize('dbpass', 20);

        $form->addText('dbprefix', Setup::getConfigSet('dbprefix'));
        $form->setSize('dbprefix', 5, 5);

        $form->addText('dbhost', Setup::getConfigSet('dbhost'));
        $form->setSize('dbhost', 20);

        $form->addText('dbport', Setup::getConfigSet('dbport'));
        $form->setSize('dbport', 6);

        $form->addText('dbname', Setup::getConfigSet('dbname'));
        $form->setSize('dbname', 20);

        $form->mergeTemplate($formTpl);
        $form->addSubmit('default_submit', _('Continue'));
        $content[] = Setup::createForm($form, 'databaseConfig.tpl');
    }

    function createForm($form, $tplFile)
    {
        $template = $form->getTemplate();
        $tpl = new PHPWS_Template;
        $tpl->setFile("setup/templates/$tplFile", TRUE);
        $tpl->setData($template);

        return $tpl->get();
    }

    function getSourceDir()
    {
        static $directory;

        if (empty($directory)) {
            $dir = explode(DIRECTORY_SLASH, $_SERVER['SCRIPT_FILENAME']);

            for ($i=0; $i < 2; $i++) {
                array_pop($dir);
            }

            $directory = implode(DIRECTORY_SLASH, $dir) . DIRECTORY_SLASH;
        }
        return $directory;
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

    function show($content, $title=NULL, $forward=false)
    {
        include 'core/conf/version.php';
        $tpl = new PHPWS_Template;
        $tpl->setFile('setup/templates/setup.tpl', TRUE);
        if (!isset($title)) {
            $title = sprintf(_('phpWebSite %s Setup'), $version);
        }

        if ($forward && AUTO_FORWARD) {
            $time = 2;
            $address = 'index.php?step=3';
            $setupData['META'] =  sprintf('<meta http-equiv="refresh" content="%s; url=%s" />', $time, $address);
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
        if (CONFIG_CREATED) {
            switch (Setup::testDBConnect(PHPWS_DSN)) {
            case '2':
                echo _('phpWebSite configuration file and database have been found. Assuming installation is complete. You should move or delete the setup directory.');
                echo '<br />';
                echo _('If you are returning here from a previous incomplete installation, you will need to clear the database of all tables and try again.');
                exit();

            case '-1':
                echo _('phpWebSite configuration file exists but database does not. Create the database set in the config file or delete the config file.');
                exit();

            case '0':
                echo _('phpWebSite configuration file exists but could not connect to database. Check your dsn settings or delete the config file.');
                exit();

            case '1':
                $step = 2;
                break;
            }
        }

        include './setup/welcome.php';

        $content[] = "<a href=\"index.php?step=$step\">" . _('Begin Installation') . '</a>';
        return;
    }

    function createCore(&$content)
    {
        require_once('File.php');
        $content[] = _('Importing core database file.') . '<br />';

        $db = new PHPWS_DB;
        $result = $db->importFile('core/boost/install.sql');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('Some errors occurred while creating the core database tables.') . '<br />';
            $content[] = _('Please check your error log file.') . '<br />';
            return;
        }

        if ($result == TRUE) {
            $db = new PHPWS_DB('core_version');
            include(PHPWS_SOURCE_DIR . 'core/boost/boost.php');
            $db->addValue('version', $version);
            $result = $db->insert();

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('Some errors occurred while creating the core database tables.') . '<br />';
                $content[] = _('Please check your error log file.') . '<br />';
            } else {
                $content[] = _('Core installation successful.') . '<br /><br />';
                $content[] = '<a href="index.php?step=3">' . _('Continue to Module Installation') . '</a>';
            }
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
            $content[] = $result;
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