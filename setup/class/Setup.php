<?php

/**
 * Setup class controls the first-time installation of phpwebsite
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
require_once 'core/class/Debug.php';
if (strstr($_SERVER['SCRIPT_FILENAME'], '\\')) {
    define('DIRECTORY_SLASH', '\\');
} else {
    define('DIRECTORY_SLASH', '/');
}

class Setup {

    public $phpws_version = null;

    /**
     * Array of error messages from form submission
     * @var array
     */
    public $messages = null;

    public $title = null;

    public $content = null;

    /**
     * How far along the setup has progressed
     * @var string
     */
    public $step = '0';

    public function __construct()
    {
        include './core/conf/version.php';
        $this->phpws_version = $version;
        if (isset($_REQUEST['step'])) {
            $this->setStep($_REQUEST['step']);
        }
    }


    public function setStep($step)
    {
        $this->step = (int)$step;
    }

    public function configExists()
    {
        return is_file(PHPWS_SOURCE_DIR . 'config/core/config.php');
    }

    public function initConfigSet()
    {
        $this->setConfigSet('cache_directory', '/tmp/');
        if (!isset($_SESSION['configSettings'])) {
            $_SESSION['configSettings']['database'] = false;
            // Could use some windows checking here
            $this->setConfigSet('dbname', DEFAULT_DBNAME);
            $this->setConfigSet('dbuser', DEFAULT_DBUSER);
            $this->setConfigSet('dbport', DEFAULT_DBPORT);
            $this->setConfigSet('dbhost', DEFAULT_DBHOST);
            $this->setConfigSet('dbtype', DEFAULT_DBTYPE);
        }
    }

    public function createConfig()
    {
        $this->initConfigSet();

        if (!isset($_SESSION['configSettings']['database']) || $_SESSION['configSettings']['database'] == false) {
            $this->databaseConfig();
        } else {
            $configDir = PHPWS_SOURCE_DIR . 'config/';
            if (is_file($configDir . 'config.php')) {
                $this->content[] = dgettext('core','Your configuration file already exists.');
                $this->content[] = dgettext('core','Remove the following file and refresh to continue:');
                $this->content[] = '<pre>' . $configDir . 'config.php</pre>';
            } elseif ($this->writeConfigFile()) {
                PHPWS_Core::killSession('configSettings');
                $this->content[] = dgettext('core','Your configuration file was written successfully!') . '<br />';
                $this->content[] = '<a href="index.php?step=3">' . dgettext('core','Move on to Step 3') . '</a>';
            } else {
                $this->content[] = dgettext('core','Your configuration file could not be written into the following directory:');
                $this->content[] = "<pre>$configDir</pre>";
                $this->content[] = dgettext('core','Please check your directory permissions and try again.');
                $this->content[] = '<a href="help/permissions.' . DEFAULT_LANGUAGE . '.txt">' . dgettext('core','Permission Help') . '</a>';
            }
        }
        $this->title = dgettext('core', 'Create Configuration File');
    }

    /**
     * Writes the config/core/config.php file. Assumes one does not already
     * exist
     * @return unknown_type
     */
    public function writeConfigFile()
    {
        require_once 'File.php';

        $location = PHPWS_SOURCE_DIR . 'config/core/';
        if (!is_writable($location)) {
            $this->messages[] = 'Cannot write file to ' . $location;
            return false;
        }

        $filename = $location . 'config.php';
        if (is_file($filename)) {
            $this->messages[] = dgettext('core', 'Configuration file already exists.');
            return false;
        }

        $config_file[] = '<?php';
        $config_file[] = sprintf("define('PHPWS_SOURCE_DIR', '%s');", PHPWS_SOURCE_DIR);
        $config_file[] = sprintf("define('PHPWS_HOME_DIR', '%s');", PHPWS_SOURCE_DIR);

        $config_file[] = sprintf("define('SITE_HASH', '%s');", md5(rand()));
        $config_file[] = sprintf("define('PHPWS_DSN', '%s');", $_SESSION['configSettings']['dsn']);
        $config_file[] = sprintf("define('PHPWS_TABLE_PREFIX', '%s');", $_SESSION['configSettings']['dbprefix']);
        $config_file[] = '?>';
        if(!file_put_contents($filename, implode("\n", $config_file))) {
            return false;
        } else {
            $source_http = sprintf("<?php\ndefine('PHPWS_SOURCE_HTTP', '%s');\n?>", str_replace('setup/', '', PHPWS_Core::getHomeHttp()));
            return file_put_contents($location . 'source.php', $source_http);
        }
    }


    private function postUser()
    {
        $_SESSION['User']->deity = true;
        $_SESSION['User']->authorize = 1;
        $_SESSION['User']->approved = 1;

        // all is well
        $aiw = true;
        if (empty($_POST['username']) || preg_match('/[^\w\.\-]/', $_POST['username'])) {
            $aiw = false;
            $this->messages[] = dgettext('users', 'Username is improperly formatted.');
        } else {
            $_SESSION['User']->username = $_POST['username'];
        }

        if (empty($_POST['pass1']) || $_POST['pass1'] != $_POST['pass2'] || strlen($_POST['pass1']) < 4 ) {
            $aiw = false;
            $this->messages[] = dgettext('core', 'Password is not acceptable.');
        } else {
            $_SESSION['User']->setPassword($_POST['pass1']);
        }

        if (empty($_POST['email'])) {
            $aiw = false;
            $this->messages[] = dgettext('core', 'Please enter an email address.');
        } else {
            $_SESSION['User']->setEmail($_POST['email']);
        }

        return $aiw;
    }

    public function postConfig()
    {
        $check = true;
        $currentPW = $this->getConfigSet('dbpass');

        if (!empty($_POST['dbuser'])) {
            $this->setConfigSet('dbuser', $_POST['dbuser']);
        } else {
            $this->messages['dbuser'] = dgettext('core','Missing a database user name.');
            $check = false;
        }

        if (!empty($_POST['dbpass'])) {
            if (preg_match('/[^\w\s\.!\?]/', $_POST['dbpass'])) {
                $this->messages['dbpass'] = dgettext('core','Database password may contain alphanumeric characters, punctuation, spaces and underscores only.');
                $check = false;
            } else {
                $this->setConfigSet('dbpass', $_POST['dbpass']);
            }
        } elseif (empty($currentPW)) {
            $this->messages['dbpass'] = dgettext('core','Missing a database password.');
            $check = false;
        }

        $this->setConfigSet('dbhost', $_POST['dbhost']);

        if (!empty($_POST['dbname'])) {
            $this->setConfigSet('dbname', $_POST['dbname']);
        } else {
            $this->messages['dbname'] = dgettext('core','Missing a database name.');
            $check = false;
        }

        if (!empty($_POST['dbprefix'])) {
            if (preg_match('/\W/', $_POST['dbprefix'])) {
                $this->messages['dbpref'] = dgettext('core','Table prefix must be alphanumeric characters or underscores only');
                $check = false;
            } else {
                $this->setConfigSet('dbprefix', $_POST['dbprefix']);
            }
        }

        $this->setConfigSet('dbtype', $_POST['dbtype']);
        $this->setConfigSet('dbport', $_POST['dbport']);

        if (!$check) {
            return false;
        }

        if (CHECK_DB_CONNECTION == false) {
            return true;
        }

        $checkConnection = $this->testDBConnect();

        if ($checkConnection == 1) {
            // Database already exists and is empty.
            $this->messages[] = dgettext('core', 'Database found.');
            return true;
        } elseif ($checkConnection == 2) {
            $sub[] = dgettext('core','PhpWebSite was able to connect, but the database already contained tables.');
            if ($this->getConfigSet('dbprefix')) {
                $sub[] = dgettext('core','Since you set a table prefix, you may force an installation into this database.');
                $sub[] = dgettext('core','Click the link below to continue or change your connection settings.');
                $sub[] = sprintf('<a href="index.php?step=7">%s</a>',dgettext('core','I want to install phpWebSite in this database.'));
            } else {
                $sub[] = dgettext('core', 'Create a new database, remove all tables from the database you want to use, or use table prefixing.');
                $_SESSION['configSettings']['database'] = false;
            }
            $this->messages['main'] = implode('<br />', $sub);
            return false;
        }
        elseif ($checkConnection == -1) {
            if ($this->createDatabase()) {
                //Database created successfully, move on to creating core
                $this->messages[] = dgettext('core', 'Database created.');
                return true;
            } else {
                $this->databaseConfig();
            }
        } else {
            $this->messages[] = dgettext('core','Unable to connect to the database with the information provided.');
            $this->messages[] = '<a href="help/database.' . DEFAULT_LANGUAGE . '.txt" target="index">' . dgettext('core','Database Help') . '</a>';
            return false;
        }
    }


    public function createDatabase()
    {
        $dsn = $this->getDSN(1);
        $dbobj = new DB;
        $db = $dbobj->connect($dsn);

        if (PHPWS_Error::isError($db)) {
            PHPWS_Error::log($db);
            $this->messages[] = dgettext('core','Unable to connect.');
            $this->messages[] = dgettext('core','Check your configuration settings.');
            return false;
        }

        $result = $db->query('CREATE DATABASE ' . $this->getConfigSet('dbname'));
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($db);
            $this->messages[] = dgettext('core','Unable to create the database.');
            $this->messages[] = dgettext('core','You will need to create it manually and rerun the setup.');
            return false;
        }

        $dsn = $this->getDSN(2);
        $this->setConfigSet('dsn', $dsn);
        $_SESSION['configSettings']['database'] = true;

        return true;
    }

    public function getDSN($mode)
    {
        $dbtype = $this->getConfigSet('dbtype');
        $dbuser = $this->getConfigSet('dbuser');
        $dbpass = $this->getConfigSet('dbpass');
        $dbhost = $this->getConfigSet('dbhost');
        $dbport = $this->getConfigSet('dbport');
        $dbname = $this->getConfigSet('dbname');

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
        if (empty($dsn)) {
            $dsn = $this->getDSN(1);
            $pear_db = new DB;
            $connection = $pear_db->connect($dsn);

            if (PHPWS_Error::isError($connection)) {
                PHPWS_Error::log($connection);
                return 0;
            }
            $connection->disconnect();
            $dsn = $this->getDSN(2);
        }

        $tdb = new DB;
        $result = $tdb->connect($dsn);

        if (PHPWS_Error::isError($result)) {
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

        $this->setConfigSet('dsn', $dsn);
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


    public function databaseConfig()
    {
        $form = new PHPWS_Form();
        $form->addHidden('step', '2');

        $databases = array ('mysql' =>'MySQL', 'pgsql' =>'PostgreSQL');

        $formTpl['DBTYPE_DEF'] = dgettext('core','phpWebSite supports MySQL and PostgreSQL. Choose the type your server currently is running.');

        $formTpl['DBUSER_DEF'] = dgettext('core','This is the user name that phpWebSite will use to access its database.')
        . ' <br /><i>' . dgettext('core','Note: it is a good idea to give each phpWebSite installation its own user.') . '</i>';
        if (isset($this->messages['dbuser'])) {
            $formTpl['DBUSER_ERR'] = $this->messages['dbuser'];
        }

        $formTpl['DBPASS_DEF'] = dgettext('core','Enter the database\'s user password here.');
        if (isset($this->messages['dbpass'])) {
            $formTpl['DBPASS_ERR'] = $this->messages['dbpass'];
        }


        $formTpl['DBPREF_DEF'] = dgettext('core','If you are installing phpWebSite in a shared environment, you may assign a prefix to tables.<br />We recommend you run without one.');
        if (isset($this->messages['dbpref'])) {
            $formTpl['DBPREF_ERR'] = $this->messages['dbpref'];
        }

        $formTpl['DBHOST_DEF'] = dgettext('core','If your database is on the same server as your phpWebSite installation, leave this as &#x22;localhost&#x22;.')
        . '<br />' . dgettext('core','Otherwise, enter the ip or dns to the database server.');

        $formTpl['DBPORT_DEF'] = dgettext('core','If your host specification requires access via a specific port, enter it here.');

        $formTpl['DBNAME_DEF'] = dgettext('core','The database\'s name into which you are installing phpWebSite.')
        . '<br /><i>' . dgettext('core','Note: if you have not made this database yet, you should do so before continuing.') . '</i>';
        if (isset($this->messages['dbname'])) {
            $formTpl['DBNAME_ERR'] = $this->messages['dbname'];
        }
        $formTpl['TITLE'] = dgettext('core', 'Database configuration');
        $form->addSelect('dbtype', $databases);
        $form->setMatch('dbtype', $this->getConfigSet('dbtype'));
        $form->setLabel('dbtype', dgettext('core','Database Type'));

        $form->addText('dbuser', $this->getConfigSet('dbuser'));
        $form->setSize('dbuser', 20);
        $form->setLabel('dbuser', dgettext('core','Database User'));

        $form->addPassword('dbpass', $this->getConfigSet('dbpass'));
        $form->allowValue('dbpass');
        $form->setSize('dbpass', 20);
        $form->setLabel('dbpass', dgettext('core','Database Password'));

        $form->addText('dbprefix', $this->getConfigSet('dbprefix'));
        $form->setSize('dbprefix', 5, 5);
        $form->setLabel('dbprefix', dgettext('core','Table prefix'));

        $form->addText('dbhost', $this->getConfigSet('dbhost'));
        $form->setSize('dbhost', 20);
        $form->setLabel('dbhost', dgettext('core','Host Specification'));

        $form->addText('dbport', $this->getConfigSet('dbport'));
        $form->setSize('dbport', 6);
        $form->setLabel('dbport', dgettext('core','Host Specification Port'));

        $form->addText('dbname', $this->getConfigSet('dbname'));
        $form->setSize('dbname', 20);
        $form->setLabel('dbname', dgettext('core','Database Name'));

        $form->mergeTemplate($formTpl);

        $form->addSubmit('default_submit', dgettext('core','Continue'));
        $this->content = $this->createForm($form, 'databaseConfig.tpl');
        $this->title = dgettext('core', 'Configure phpWebSite');
        $this->display();
    }

    public function createForm($form, $tplFile)
    {
        $template = $form->getTemplate();
        $tpl = new PHPWS_Template;
        $tpl->setFile("setup/templates/$tplFile", true);
        $tpl->setData($template);

        return $tpl->get();
    }

    public function show($content, $title=NULL, $forward=false)
    {
        include 'core/conf/version.php';
        $tpl = new PHPWS_Template;
        $tpl->setFile('setup/templates/setup.tpl', true);
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
        session_name('never_ever_name_your_session_this');
        session_start();
        if ($this->step == 0) {
            $_SESSION['session_check'] = true;
            return;
        }

        // step > 2; check for session
        if (!isset($_SESSION['session_check'])) {
            $this->content[] = dgettext('core','phpWebSite depends on sessions to move data between pages.');
            $this->content[] = sprintf('<a href="help/sessions.%s.txt">%s</a>', DEFAULT_LANGUAGE, dgettext('core','Sessions Help'));
            $this->content[] = sprintf(dgettext('core', 'If you think your sessions are working properly, %syou can click here return to the beginning%s.'),
                                       '<a href="index.php">', '</a>');
            $this->title = dgettext('core','There is a problem with your sessions.');
            $this->display();
        }
    }

    public function welcome()
    {
        unset($_SESSION['Boost']);
        // If the config file is already created, need to check why
        if (CONFIG_CREATED) {
            switch ($this->testDBConnect(PHPWS_DSN)) {
                case '2':
                    $this->content[] = dgettext('core','phpWebSite configuration file and database have been found. We are assuming your installation is complete.');
                    $this->content[] = dgettext('core', 'You should move or delete the setup directory.');
                    $this->content[] = dgettext('core','If you are returning here from a previous incomplete installation, you will need to clear the database of all tables and try again.');
                    $this->title = dgettext('core', 'There is a problem with your database');
                    $this->display();
                    exit();

                case '-1':
                    $this->content[] = dgettext('core','The phpWebSite configuration file exists but it\'s specified database does not.');
                    $this->content[] = dgettext('core', 'Create the database set in the config file or delete the config file.');
                    $this->title = dgettext('core', 'There is a problem with your database');
                    $this->display();
                    exit();

                case '0':
                    $this->content[] = dgettext('core','The phpWebSite configuration file exists but we could not connect to it\'s specified database.');
                    $this->content[] = dgettext('core', 'Check your dsn settings or delete the config file.');
                    $this->title = dgettext('core', 'There is a problem with your database');
                    $this->display();
                    exit();

                case '1':
                    // install the core as the config exists and the database is empty
                    $this->step = '4';
                    $this->goToStep();
                    break;
            }
        } else {
            // create config file
            $this->step = 1;
        }
        $this->goToStep();
    }

    public function createUser()
    {
        if (!isset($_SESSION['User'])) {
            $_SESSION['User'] = new PHPWS_User;
        }
        $form = new PHPWS_Form;
        $form->addHidden('step', 5);
        $form->addText('username', $_SESSION['User']->username);
        $form->setLabel('username', dgettext('users', 'Username'));

        $form->addText('email', $_SESSION['User']->email);
        $form->setLabel('email', dgettext('users', 'Email'));

        $form->addPassword('pass1');
        $form->setLabel('pass1', dgettext('users', 'Password'));

        $form->addPassword('pass2');
        $form->setLabel('pass2', dgettext('users', 'Confirm'));
        $form->addSubmit(dgettext('core', 'Create my account'));
        $this->title = dgettext('core', 'Please create your new user account');
        $this->content[] = $this->createForm($form, 'new_user.html');
        $this->display();
    }


    public function createCore()
    {
        require_once('File.php');
        $this->content[] = dgettext('core','Importing core database file.');

        $db = new PHPWS_DB;
        $result = $db->importFile('core/boost/install.sql');

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $this->content[] = dgettext('core','Some errors occurred while creating the core database tables.');
            $this->content[] = dgettext('core','Please check your error log file.');
            return false;
        }

        if ($result == true) {
            $db = new PHPWS_DB('core_version');
            include(PHPWS_SOURCE_DIR . 'core/boost/boost.php');
            $db->addValue('version', $version);
            $result = $db->insert();

            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $this->content[] = dgettext('core','Some errors occurred while creating the core database tables.');
                $this->content[] = dgettext('core','Please check your error log file.');
                return false;
            } else {
                $this->content[] = dgettext('core','Core installation successful.');
                return true;
            }
        }
    }


    private function installModules($modules) {
        if (!isset($_SESSION['Boost'])) {
            $_SESSION['Boost'] = new PHPWS_Boost;
            $_SESSION['Boost']->loadModules($modules);
        }
        $result = $_SESSION['Boost']->install(false);

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $this->content[] = dgettext('core','An error occurred while trying to install your modules.')
            . ' ' . dgettext('core','Please check your error logs and try again.');
            return true;
        } else {
            $this->content[] = $result;
        }

        $result = $_SESSION['Boost']->isFinished();
        unset($_SESSION['Boost']);
        return $result;
    }

    private function installContentModules()
    {
        $modules[] = 'block';
        $modules[] = 'menu';
        $modules[] = 'pagesmith';
        return $this->installModules($modules);
    }

    public function installCoreModules()
    {
        $modules = PHPWS_Core::coreModList();
        return $this->installModules($modules);
    }

    public function finish()
    {
        $this->content[] = '<hr />';
        $this->content[] = dgettext('core','Installation of phpWebSite is complete.') . '<br />';
        $this->content[] = dgettext('core','If you experienced any error messages, check your error.log file.') . '<br />';
        if (CHECK_DIRECTORY_PERMISSIONS) {
            $this->content[] = dgettext('core','Check Directory Permissions is enabled so be sure to secure your config and templates directories!');
            $this->content[] = dgettext('core','If you do not change it now, your next page will be an error screen.');
        } else {
            $this->content[] = dgettext('core','After you finish installing your modules in Boost, you should make your config and template directories non-writable.');
        }
        $this->content[] = '<a href="../index.php">' . dgettext('core','Go to my new website!') . '</a>' . '<br />';

    }

    public function display()
    {
        $tpl = new PHPWS_Template;
        $tpl->setFile("setup/templates/setup.tpl", true);
        if (is_array($this->content)) {
            $content = PHPWS_Text::tag_implode('p', $this->content);
        } else {
            $content = & $this->content;
        }

        if (!empty($this->messages)) {
            if (is_array($this->messages)) {
                $message = PHPWS_Text::tag_implode('p', $this->messages);
            } else {
                $message = & $this->messages;
            }
        } else {
            $message = null;
        }

        $tpl->setData(array('TITLE'=>$this->title, 'CONTENT'=>$content, 'MESSAGE'=>$message));

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
        $test['memory']['fail'] = dgettext('core','Your PHP memory limit is less than 8MB. You may encounter problems with the script at this level.');
        $test['memory']['fail'] .= dgettext('core', 'We suggest raising the limit in your php.ini file or uncommenting the "ini_set(\'memory_limit\', \'10M\');" line in your config/core/config.php file after installation.');
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
            $this->title = dgettext('core', 'Cannot install phpWebSite because of the following reasons:');
            $this->content = '<ul>' . PHPWS_Text::tag_implode('li', $crit) . '</ul>';
            $this->display();
        } else {
            $_SESSION['server_passed'] = true;
        }

    }

    function goToStep()
    {
        switch ($this->step) {
            case '0':
                $this->welcome();
                break;

            case '1':
                $this->createConfig();
                break;

            case '2':
                if (!$this->postConfig()) {
                    // create config file and database
                    $this->createConfig();
                } else {
                    if ($this->writeConfigFile()) {
                        // config written, need to reload page for new defines
                        header('location: index.php?step=3');
                        exit();
                    } else {
                        echo implode('<br />', $this->messages);
                    }
                    exit();
                }
                break;

            case '3':
                if ($this->createCore()) {
                    if ($this->installCoreModules()) {
                        $this->content[] = dgettext('core', 'Core modules installed successfully.');
                        $this->content[] = sprintf('<a href="index.php?step=4">%s</a>', dgettext('core', 'Click to continue'));
                    }
                }
                break;

            case '4':
                $this->createUser();
                break;

            case '5':
                if ($this->postUser()) {
                    $db = new PHPWS_DB('users');
                    $result = $db->select();
                    if (empty($result)) {
                        $_SESSION['User']->setDisplayName('Administrator');
                        $_SESSION['User']->save();
                        $this->content[] = dgettext('core', 'New user created successfully.');
                        $this->step = 6;
                        $this->goToStep();
                        break;
                    } elseif (PHPWS_Error::isError($result)) {
                        PHPWS_Error::log($result);
                        $this->content[] = dgettext('core', 'Sorry an error occurred. Please check your logs.');
                    } else {
                        $this->content[] = dgettext('core', 'Cannot create a new user. Initial user already exists.');
                        $this->display();
                    }

                } else {
                    $this->createUser();
                }
                break;

            case '6':
                if ($this->installContentModules()) {
                    $this->content[] = dgettext('core', 'Starting modules installed.');
                    $this->content[] = dgettext('core', 'The site should be ready for you to use.');
                    //                    $this->content[] = sprintf('<a href="%s">%s</a>', PHPWS_SOURCE_HTTP, dgettext('core', 'Continue to your new site...'));
                    $this->content[] = sprintf('<a href="../">%s</a>', dgettext('core', 'Continue to your new site...'));
                    unset($_SESSION['configSettings']);
                    unset($_SESSION['User']);
                    unset($_SESSION['session_check']);
                    $this->display();
                }

            case '7':
                $dsn = $this->getDSN(2);
                $this->setConfigSet('dsn', $dsn);
                $_SESSION['configSettings']['database'] = true;
                if ($this->writeConfigFile()) {
                    // config written, need to reload page for new defines
                    header('location: index.php?step=3');
                    exit();
                } else {
                    $this->step = 2;
                    $this->createConfig();
                }
        }
        $this->display();
    }

}

?>
