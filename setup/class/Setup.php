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

    public $phpws_version = null;

    public function __construct()
    {
        include './core/conf/version.php';
        $this->phpws_version = $version;
    }


    public function configExists()
    {
        return is_file(PHPWS_SOURCE_DIR . 'core/conf/config.php');
    }

    public function initConfigSet()
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


    public function createConfig(&$content)
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
            $configDir = Setup::getConfigSet('source_dir') . PHPWS_SOURCE_DIR . 'core/conf/';
            if (is_file($configDir . 'config.php')) {
                $content[] = dgettext('core','Your configuration file already exists.');
                $content[] = dgettext('core','Remove the following file and refresh to continue:');
                $content[] = '<pre>' . $configDir . 'config.php</pre>';
            }
            elseif (Setup::writeConfigFile()) {
                PHPWS_Core::killSession('configSettings');
                $content[] = dgettext('core','Your configuration file was written successfully!') . '<br />';
                $content[] = '<a href="index.php?step=2">' . dgettext('core','Move on to Step 2') . '</a>';
            } else {
                $content[] = dgettext('core','Your configuration file could not be written into the following directory:');
                $content[] = "<pre>$configDir</pre>";
                $content[] = dgettext('core','Please check your directory permissions and try again.');
                $content[] = '<a href="help/permissions.' . DEFAULT_LANGUAGE . '.txt">' . dgettext('core','Permission Help') . '</a>';
            }
        }
    }

    public function writeConfigFile()
    {
        require_once 'File.php';

        $location = PHPWS_SOURCE_DIR . 'core/conf/';
        if (!is_writable($location)) {
            return FALSE;
        }

        $tpl = new PHPWS_Template;
        $tpl->setFile('core/inc/config.tpl', TRUE);
        $tpl->setData($_SESSION['configSettings']);
        $configFile = $tpl->get();
        return File::write($location . 'config.php', $configFile, FILE_MODE_WRITE);
    }

    public function postGeneralConfig(&$content, &$messages)
    {
        $check = TRUE;
        $source_dir = addslashes($_POST['source_dir']);
        $match = sprintf('/%s$/', preg_quote(DIRECTORY_SLASH, '/'));
        if (!preg_match($match, $source_dir)) {
            $source_dir = $source_dir . DIRECTORY_SLASH;
        }

        if (!is_dir($source_dir)) {
            $messages['source_dir'] = dgettext('core','Unable to locate the source directory:') . ' ' . $source_dir;
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
            $messages['site_hash'] = dgettext('core','Site hash may not be empty.');
            $check = false;
        } else {
            Setup::setConfigSet('site_hash', $_POST['site_hash']);
        }
        return $check;
    }

    public function postDatabaseConfig(&$content, &$messages)
    {
        $check = TRUE;
        $currentPW = Setup::getConfigSet('dbpass');

        if (!empty($_POST['dbuser'])) {
            Setup::setConfigSet('dbuser', $_POST['dbuser']);
        } else {
            $messages['dbuser'] = dgettext('core','Missing a database user name.');
            $check = FALSE;
        }

        if (!empty($_POST['dbpass'])) {
            if (preg_match('/[^\w\s\.!\?]/', $_POST['dbpass'])) {
                $messages['dbpass'] = dgettext('core','Database password may contain alphanumeric characters, punctuation, spaces and underscores only.');
                $check = false;
            } else {
                Setup::setConfigSet('dbpass', $_POST['dbpass']);
            }
        } elseif (empty($currentPW)) {
            $messages['dbpass'] = dgettext('core','Missing a database password.');
            $check = FALSE;
        }

        Setup::setConfigSet('dbhost', $_POST['dbhost']);

        if (!empty($_POST['dbname'])) {
            Setup::setConfigSet('dbname', $_POST['dbname']);
        } else {
            $messages['dbname'] = dgettext('core','Missing a database name.');
            $check = FALSE;
        }

        if (!empty($_POST['dbprefix'])) {
            if (preg_match('/\W/', $_POST['dbprefix'])) {
                $messages['dbpref'] = dgettext('core','Table prefix must be alphanumeric characters or underscores only');
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
            $sub['main'] = dgettext('core','PhpWebSite was able to connect, but the database already contained tables.');
            if (Setup::getConfigSet('dbprefix')) {
                $sub[] = dgettext('core','Since you set a table prefix, you may force an installation into this database.');
                $sub[] = dgettext('core','Click the link below to continue or change your connection settings.');
                $sub[] = sprintf('<a href="index.php?step=1b">%s</a>',dgettext('core','I want to install phpWebSite in this database.'));
            } else {
                $_SESSION['configSettings']['database'] = FALSE;
            }
            $messages['main'] = implode('<br />', $sub);
            return FALSE;
        }
        elseif ($checkConnection == -1) {
            $sub[] = dgettext('core','PhpWebSite was able to connect but the database itself does not exist.');
            $sub[] = '<a href="index.php?step=1a">' . dgettext('core','Do you want phpWebSite to create the database?') . '</a>';
            $sub[] = dgettext('core','If not, you will need to create the database yourself and return to the setup.');
            $messages['main'] = implode('<br />', $sub);
            return FALSE;
        }
        else {
            $sub[] = dgettext('core','Unable to connect to the database with the information provided.');
            $sub[] = '<a href="help/database.' . DEFAULT_LANGUAGE . '.txt" target="index">' . dgettext('core','Database Help') . '</a>';
            $messages['main'] = implode('<br />', $sub);
            return FALSE;
        }
    }


    public function createDatabase(&$content)
    {
        $dsn = Setup::getDSN(1);
        $db = & DB::connect($dsn);

        if (PEAR::isError($db)) {
            PHPWS_Error::log($db);
            $content[] = dgettext('core','Unable to connect.');
            $content[] = dgettext('core','Check your configuration settings.');
            return FALSE;
        }

        $result = $db->query('CREATE DATABASE ' . Setup::getConfigSet('dbname'));
        if (PEAR::isError($result)) {
            PHPWS_Error::log($db);
            $content[] = dgettext('core','Unable to create the database.');
            $content[] = dgettext('core','You will need to create it manually and rerun the setup.');
            return FALSE;
        }

        $dsn = Setup::getDSN(2);
        Setup::setConfigSet('dsn', $dsn);
        $_SESSION['configSettings']['database'] = TRUE;

        $content[] = dgettext('core','The database creation succeeded!');
        return true;
        $content[] = '<a href="index.php?step=1">' . dgettext('core','You can now finish the creation of your config file.') . '</a>';

    }

    public function getDSN($mode)
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

    public function testDBConnect($dsn=null)
    {
        return 2;
        
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

    public function setConfigSet($setting, $value)
    {
        $_SESSION['configSettings'][$setting] = $value;
    }

    public function getConfigSet($setting)
    {
        if (!isset($_SESSION['configSettings']) || !isset($_SESSION['configSettings'][$setting])) {
            return NULL;
        }

        return $_SESSION['configSettings'][$setting];
    }

    public function generalConfig(&$content, $messages)
    {
        $form = new Form2;

        /*
         $form = new PHPWS_Form('generalConfig');
         $site_hash  = Setup::getConfigSet('site_hash');

         $source_dir = Setup::getConfigSet('source_dir');
         $pear_select = array('local' =>dgettext('core','Use Pear files included with phpWebSite (recommended).'),
         'system'=>dgettext('core','Use server\'s Pear library files (not recommended).')
         );

         $formTpl['SOURCE_DIR_DEF'] = dgettext('core','This is the directory where phpWebSite is installed.');

         $formTpl['SITE_HASH_DEF'] = dgettext('core','The character string below differentiates your site from others on the same server.') . '<br />'
         . dgettext('core','The example is randomly generated.') . ' '
         . dgettext('core','You may change it if you wish.');

         $formTpl['PEAR_DEF'] = dgettext('core','phpWebSite uses the Pear library extensively.') . '<br />'
         . dgettext('core','We suggest you use the library files included with phpWebSite.');

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
         $form->setLabel('source_dir', dgettext('core','Source Directory'));

         $form->add('site_hash', 'textfield', $site_hash);
         $form->setSize('site_hash', 40);
         $form->setLabel('site_hash', dgettext('core','Site Hash'));

         $form->add('pear', 'select', $pear_select);
         $form->setMatch('pear', 'local');
         $form->setLabel('pear', dgettext('core','Pear Configuration'));

         $form->addSubmit('submit', dgettext('core','Continue'));

         $form->mergeTemplate($formTpl);

         $content[] = Setup::createForm($form, 'generalConfig.tpl');
         */
    }


    public function databaseConfig(&$content, $messages)
    {
        if (isset($messages['main'])) {
            $formTpl['MAIN'] = $messages['main'];
        }
        $form = new PHPWS_Form('databaseConfig');
        $form->add('step',   'hidden', '1');
        $form->add('action', 'hidden', 'postDatabaseConfig');

        $databases = array ('mysql' =>'MySQL',
         'pgsql' =>'PostgreSQL');

        $formTpl['DBTYPE_DEF'] = dgettext('core','phpWebSite supports MySQL and PostgreSQL. Choose the type your server currently is running.');

        $formTpl['DBUSER_DEF'] = dgettext('core','This is the user name that phpWebSite will use to access its database.')
        . ' <br /><i>' . dgettext('core','Note: it is a good idea to give each phpWebSite installation its own user.') . '</i>';
        if (isset($messages['dbuser'])) {
            $formTpl['DBUSER_ERR'] = $messages['dbuser'];
        }

        $formTpl['DBPASS_DEF'] = dgettext('core','Enter the database\'s user password here.');
        if (isset($messages['dbpass'])) {
            $formTpl['DBPASS_ERR'] = $messages['dbpass'];
        }


        $formTpl['DBPREF_DEF'] = dgettext('core','If you are installing phpWebSite in a shared environment, you may assign a prefix to tables.<br />We recommend you run without one.');
        if (isset($messages['dbpref'])) {
            $formTpl['DBPREF_ERR'] = $messages['dbpref'];
        }

        $formTpl['DBHOST_DEF'] = dgettext('core','If your database is on the same server as your phpWebSite installation, leave this as &#x22;localhost&#x22;.')
        . '<br />' . dgettext('core','Otherwise, enter the ip or dns to the database server.');

        $formTpl['DBPORT_DEF'] = dgettext('core','If your host specification requires access via a specific port, enter it here.');

        $formTpl['DBNAME_DEF'] = dgettext('core','The database\'s name into which you are installing phpWebSite.')
        . '<br /><i>' . dgettext('core','Note: if you have not made this database yet, you should do so before continuing.') . '</i>';
        if (isset($messages['dbname'])) {
            $formTpl['DBNAME_ERR'] = $messages['dbname'];
        }
        $formTpl['TITLE'] = dgettext('core', 'Database configuration');

        $form->addSelect('dbtype', $databases);
        $form->setMatch('dbtype', Setup::getConfigSet('dbtype'));
        $form->setLabel('dbtype', dgettext('core','Database Type'));

        $form->addText('dbuser', Setup::getConfigSet('dbuser'));
        $form->setSize('dbuser', 20);
        $form->setLabel('dbuser', dgettext('core','Database User'));

        $form->addPassword('dbpass', Setup::getConfigSet('dbpass'));
        $form->allowValue('dbpass');
        $form->setSize('dbpass', 20);
        $form->setLabel('dbpass', dgettext('core','Database Password'));

        $form->addText('dbprefix', Setup::getConfigSet('dbprefix'));
        $form->setSize('dbprefix', 5, 5);
        $form->setLabel('dbprefix', dgettext('core','Table prefix'));

        $form->addText('dbhost', Setup::getConfigSet('dbhost'));
        $form->setSize('dbhost', 20);
        $form->setLabel('dbhost', dgettext('core','Host Specification'));

        $form->addText('dbport', Setup::getConfigSet('dbport'));
        $form->setSize('dbport', 6);
        $form->setLabel('dbport', dgettext('core','Host Specification Port'));

        $form->addText('dbname', Setup::getConfigSet('dbname'));
        $form->setSize('dbname', 20);
        $form->setLabel('dbname', dgettext('core','Database Name'));

        $form->mergeTemplate($formTpl);

        $form->addSubmit('default_submit', dgettext('core','Continue'));
        $content[] = Setup::createForm($form, 'databaseConfig.tpl');
    }

    public function createForm($form, $tplFile)
    {
        $template = $form->getTemplate();
        $tpl = new PHPWS_Template;
        $tpl->setFile("setup/templates/$tplFile", TRUE);
        $tpl->setData($template);

        return $tpl->get();
    }

    public function getSourceDir()
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


    public function show($content, $title=NULL, $forward=false)
    {
        include 'core/conf/version.php';
        $tpl = new PHPWS_Template;
        $tpl->setFile('setup/templates/setup.tpl', TRUE);
        if (!isset($title)) {
            $title = sprintf(dgettext('core','phpWebSite %s Setup'), $version);
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

    /**
     * Checks to see if the server is using sessions OR if the user has cookies enabled.
     * The session is established when the step is not set or 1 (the beginning). Also, starts the
     * session.
     * The next visit (step > 1) will determine whether they may continue.
     * @return void
     */
    public function checkSession()
    {
        session_start();
        if (!isset($_REQUEST['step']) || $_REQUEST['step'] == 1) {
            $_SESSION['session_check'] = true;
            return;
        }

        // step > 2; check for session
        if (!isset($_SESSION['session_check'])) {
            $content[] = dgettext('core','phpWebSite depends on sessions to move data between pages.') . '<br />';
            $content[] = sprintf('<a href="help/sessions.%s.txt">%s</a>', DEFAULT_LANGUAGE, dgettext('core','Sessions Help'));
            $this->display(dgettext('core','There is a problem with your sessions.'),
            implode('<br />', $content));
        }
    }

    public function welcome(&$content)
    {
        define('PHPWS_DSN','asd');
        unset($_SESSION['Boost']);
        $step = 1;
        if (CONFIG_CREATED) {
            switch (Setup::testDBConnect(PHPWS_DSN)) {
                case '2':
                    $content[] = dgettext('core','phpWebSite configuration file and database have been found. We are assuming your installation is complete.');
                    $content[] = dgettext('core', 'You should move or delete the setup directory.');
                    $content[] = dgettext('core','If you are returning here from a previous incomplete installation, you will need to clear the database of all tables and try again.');
                    $this->display(dgettext('core', 'There is a problem with your database'), PHPWS_Text::tag_implode('p', $content));
                    exit();

                case '-1':
                    $content[] = dgettext('core','The phpWebSite configuration file exists but it\'s specified database does not.');
                    $content[] = dgettext('core', 'Create the database set in the config file or delete the config file.');
                    $this->display(dgettext('core', 'There is a problem with your database'), PHPWS_Text::tag_implode('p', $content));
                    exit();

                case '0':
                    $content[] = dgettext('core','The phpWebSite configuration file exists but we could not connect to it\'s specified database.');
                    $content[] = dgettext('core', 'Check your dsn settings or delete the config file.');
                    $this->display(dgettext('core', 'There is a problem with your database'), PHPWS_Text::tag_implode('p', $content));
                    exit();

                case '1':
                    $step = 2;
                    break;
            }
        }
exit('stop');
        include './setup/welcome.php';

        $content[] = "<a href=\"index.php?step=$step\">" . dgettext('core','Begin Installation') . '</a>';
        return;
    }

    public function createCore(&$content)
    {
        require_once('File.php');
        $content[] = dgettext('core','Importing core database file.') . '<br />';

        $db = new PHPWS_DB;
        $result = $db->importFile('core/boost/install.sql');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = dgettext('core','Some errors occurred while creating the core database tables.') . '<br />';
            $content[] = dgettext('core','Please check your error log file.') . '<br />';
            return;
        }

        if ($result == TRUE) {
            $db = new PHPWS_DB('core_version');
            include(PHPWS_SOURCE_DIR . 'core/boost/boost.php');
            $db->addValue('version', $version);
            $result = $db->insert();

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = dgettext('core','Some errors occurred while creating the core database tables.') . '<br />';
                $content[] = dgettext('core','Please check your error log file.') . '<br />';
            } else {
                $content[] = dgettext('core','Core installation successful.') . '<br /><br />';
                $content[] = '<a href="index.php?step=3">' . dgettext('core','Continue to Module Installation') . '</a>';
            }
        }
    }

    public function installModules(&$content)
    {
        $modules = PHPWS_Core::coreModList();

        if (!isset($_SESSION['Boost'])) {
            $_SESSION['Boost'] = new PHPWS_Boost;
            $_SESSION['Boost']->loadModules($modules);
        }
        $result = $_SESSION['Boost']->install(FALSE);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = dgettext('core','An error occurred while trying to install your modules.')
            . ' ' . dgettext('core','Please check your error logs and try again.');
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

    public function finish(&$content)
    {
        $content[] = '<hr />';
        $content[] = dgettext('core','Installation of phpWebSite is complete.') . '<br />';
        $content[] = dgettext('core','If you experienced any error messages, check your error.log file.') . '<br />';
        if (CHECK_DIRECTORY_PERMISSIONS) {
            $content[] = dgettext('core','Check Directory Permissions is enabled so be sure to secure your config and templates directories!');
            $content[] = dgettext('core','If you do not change it now, your next page will be an error screen.');
        } else {
            $content[] = dgettext('core','After you finish installing your modules in Boost, you should make your config and template directories non-writable.');
        }
        $content[] = '<a href="../index.php">' . dgettext('core','Go to my new website!') . '</a>' . '<br />';

    }

    public function display($title, $content)
    {
        $tpl = new PHPWS_Template;
        $tpl->setFile("setup/templates/setup.tpl", TRUE);
        $tpl->setData(array('TITLE'=>$title, 'CONTENT'=>$content));

        echo $tpl->get();
        exit();
    }


    /**
     * Checks various server settings prior to starting installation. Some end the installation; others
     * just return notices.
     * @return void
     */
    public function checkServerSettings()
    {
        $allow_install = true;

        // Settings were checked, return without issue.
        if (isset($_SESSION['server_passed'])) {
            return;
        }

        $test['session_auto_start']['pass'] = !(bool)ini_get('session.auto_start'); // need 0
        $test['session_auto_start']['fail'] = dgettext('core','session.auto_start must be set to 0 for phpWebSite to work. Please review your php.ini file.');
        $test['session_auto_start']['name'] = dgettext('core','Session auto start disabled');
        $test['session_auto_start']['crit'] = true;

        $test['pear_files']['pass'] = is_file('lib/pear/DB.php');
        $test['pear_files']['fail'] = sprintf(dgettext('core','Could not find Pear library files. You will need to %sdownload the pear package from our site%s and unzip it in your installation directory.'),
         '<a href="http://phpwebsite.appstate.edu/downloads/pear.zip">', '</a>');
        $test['pear_files']['name'] = dgettext('core','Pear library installed');
        $test['pear_files']['crit'] = true;

        $test['gd']['pass'] = extension_loaded('gd');
        $test['gd']['fail'] = sprintf(dgettext('core','You need to compile the %sGD image library%s into PHP.'), '<a href="http://www.libgd.org/Main_Page">', '</a>');
        $test['gd']['name'] = dgettext('core','GD graphic libraries installed');
        $test['gd']['crit'] = true;

        $test['image_dir']['pass'] = is_dir('images/') && is_writable('images/');
        $test['image_dir']['fail'] = sprintf(dgettext('core','%s directory does not exist or is not writable.'), PHPWS_SOURCE_DIR. 'images/');
        $test['image_dir']['name'] = dgettext('core','Image directory ready');
        $test['image_dir']['crit'] = true;

        $test['file_dir']['pass'] = is_dir('files/') && is_writable('files/');
        $test['file_dir']['fail'] = sprintf(dgettext('core','%s directory does not exist or is not writable.'), PHPWS_SOURCE_DIR . 'files/');
        $test['file_dir']['name'] = dgettext('core','File directory ready');
        $test['file_dir']['crit'] = true;

        $test['log_dir']['pass'] = is_dir('logs/') && is_writable('logs/');
        $test['log_dir']['fail'] = sprintf(dgettext('core','%s directory does not exist or is not writable.'), PHPWS_SOURCE_DIR . 'logs/');
        $test['log_dir']['name'] = dgettext('core','Log directory ready');
        $test['log_dir']['crit'] = true;

        $test['ffmpeg']['pass'] = is_file('/usr/bin/ffmpeg');
        $test['ffmpeg']['fail'] = dgettext('core','You do not appear to have ffmpeg installed. File Cabinet will not be able to create thumbnail images from uploaded videos');
        $test['ffmpeg']['name'] = dgettext('core','FFMPEG installed');
        $test['ffmpeg']['crit'] = false;

        $test['mime_type']['pass'] = function_exists('finfo_open') || function_exists('mime_content_type') || !ini_get('safe_mode');
        $test['mime_type']['fail'] = dgettext('core','Unable to detect MIME file type. You will need to compile finfo_open into PHP.');
        $test['mime_type']['name'] = dgettext('core','MIME file type detection');
        $test['mime_type']['crit'] = true;

        if (preg_match('/-/', PHP_VERSION)) {
            $phpversion = substr(PHP_VERSION,0,strpos(PHP_VERSION, '-'));
        } else {
            $phpversion = PHP_VERSION;
        }

        $test['php_version']['pass'] = version_compare($phpversion, '5.1.0', '>=');
        $test['php_version']['fail'] = sprintf(dgettext('core','Your server must run PHP version 5.1.0 or higher. You are running version %s.'), $phpversion);
        $test['php_version']['name'] = dgettext('core','PHP 5 version check');
        $test['php_version']['crit'] = true;

        $memory_limit = (int)ini_get('memory_limit');

        $test['memory']['pass'] = ($memory_limit > 8);
        $test['memory']['fail'] = dgettext('core','Your PHP memory limit is less than 8MB. You may encounter problems with the script at this level. We suggest raising the limit in your php.ini file or uncommenting the "ini_set(\'memory_limit\', \'10M\');" line in your config/core/config.php file after installation.');
        $test['memory']['name'] = dgettext('core','Memory limit exceeded');
        $test['memory']['crit'] = false;

        $test['globals']['pass'] = !(bool)ini_get('register_globals');
        $test['globals']['fail'] = dgettext('core','You have register_globals enabled. You should disable it.');
        $test['globals']['name'] = dgettext('core','Register globals disabled');
        $test['globals']['crit'] = false;

        $test['magic_quotes']['pass'] = !get_magic_quotes_gpc() && !get_magic_quotes_runtime();
        $test['magic_quotes']['fail'] = dgettext('core','Magic quotes is enabled. Please disable it in your php.ini file.');
        $test['magic_quotes']['name'] = dgettext('core','Magic quotes disabled');
        $test['magic_quotes']['crit'] = true;

        foreach  ($test as $test_section=>$val) {
            if (!$val['pass']) {
                if ($val['crit']) {
                    $crit[] = $val['fail'];
                    $allow_install = false;
                } else {
                    $warn[] = $val['fail'];
                }
            }
        }

        $content = array();
         
        if (!$allow_install) {
            $this->display(dgettext('core', 'Cannot install phpWebSite because of the following reasons:'), '<ul><li>' . implode('</li><li>', $crit) . '</li></ul>');
        } else {
            $_SESSION['server_passed'] = true;
        }

    }

}

?>