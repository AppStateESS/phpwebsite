<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

define('BRANCH_NO_CONNECTION',       1);
define('BRANCH_CONNECT_NO_DB',       2);
define('BRANCH_CONNECT_WITH_TABLES', 3);
define('BRANCH_CONNECT_SUCCESS',     4);
define('BRANCH_CONNECT_BAD_DB',      5);

PHPWS_Core::initModClass('branch', 'Branch.php');

class Branch_Admin {
    // Contains the panel object
    public $panel   = null;

    public $title   = null;

    public $message = null;

    // contains the current content. piped into panel
    public $content = null;

    // currently
    public $branch  = null;

    public $error   = null;

    // database creation form variables
    public $createdb    = 0;
    public $dbname      = null;
    public $dbuser      = null;
    public $dbpass      = null;
    public $dbhost      = 'localhost';
    public $dbport      = null;
    public $dbtype      = null;
    public $dbprefix    = null;
    public $dsn         = null; // full dsn

    public $create_step = 1;

    public $db_list    = null;

    public function __construct()
    {
        $this->db_list = array ('mysql' =>'MySQL',
                                'pgsql' =>'PostgreSQL');

        if (isset($_SESSION['branch_create_step'])) {
            $this->create_step = $_SESSION['branch_create_step'];
        }

        if (isset($_SESSION['branch_dsn'])) {
            $dsn = &$_SESSION['branch_dsn'];
            $this->dbname   = $dsn['dbname'];
            $this->dbuser   = $dsn['dbuser'];
            $this->dbpass   = $dsn['dbpass'];
            $this->dbhost   = $dsn['dbhost'];
            $this->dbport   = $dsn['dbport'];
            $this->dbtype   = $dsn['dbtype'];
            $this->dbprefix = $dsn['dbprefix'];
        }

        if (isset($_REQUEST['branch_id'])) {
            $this->branch = new Branch($_REQUEST['branch_id']);
        } else {
            $this->branch = new Branch;
        }
    }


    public function main()
    {
        $content = null;
        // Create the admin panel
        $this->cpanel();

        // Direct the path command
        $this->direct();

        // Display the results
        $this->displayPanel();
    }

    /**
     * Directs the administrative choices
     * Content is displayed in main
     */
    public function direct()
    {
        if (!@$command = $_REQUEST['command']) {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
            case 'new':
                $this->resetAdmin();
                $this->edit_db();
                break;

            case 'edit':
                // editing existing branch
                if (empty($this->branch->id)) {
                    $this->content = dgettext('branch', 'Incorrect or missing branch id.');
                }
                break;

            case 'list':
                // list all branches in the system
                $this->listBranches();
                break;

            case 'post_db':
                // post a new or updated branch to the system
                if (isset($_POST['plug'])) {
                    // user is going to use the hub dsn information
                    $this->plugHubValues();
                    $this->edit_db();
                } else {
                    if (!$this->post_db()) {
                        $this->edit_db();
                    } else {
                        $this->testDB(!empty($_POST['force']));
                    }
                }
                break;

            case 'edit_branch':
                $this->edit_basic();
                break;

            case 'branch_modules':
                $this->edit_modules();
                break;

            case 'save_branch_modules':
                if ($this->saveBranchModules()) {
                    $this->message = dgettext('branch', 'Module list saved successfully.');
                    $this->message .= sprintf('<br /><a href="http://%s">%s</a>', $this->branch->url, dgettext('branch', 'Go to the branch site...'));
                } else {
                    $this->message = dgettext('branch', 'An error occurred when trying to save the module list.');
                }
                $this->edit_modules();
                break;

            case 'post_basic':
                if (!$this->branch->id) {
                    $new_branch = true;
                } else {
                    $new_branch = false;
                }
                if (!$this->post_basic()) {
                    $this->edit_basic();
                } else {
                    $result = $this->branch->save();
                    if (PHPWS_Error::isError($result)) {
                        PHPWS_Error::log($result);
                        $this->title = dgettext('branch', 'An error occurred while saving your branch.');
                        $this->content = $result->getMessage();
                        return;
                    }

                    if ($new_branch) {
                        if ($this->branch->createDirectories()) {
                            $this->setCreateStep(3);
                            $this->title = dgettext('branch', 'Create branch directories');
                            $this->message[] = dgettext('branch', 'Branch created successfully.');
                            $this->install_branch_core();
                        } else {
                            $this->title = dgettext('branch', 'Unable to create branch directories.');
                            $this->content = dgettext('branch', 'Sorry, but Branch failed to make the proper directories.');
                        }
                    } else {
                        $this->listBranches();
                    }
                }
                break;

            case 'install_branch_core':
                $this->install_branch_core();
                break;

            case 'core_module_installation':
                $result =  $this->core_module_installation();
                if ($result) {
                    $this->content[] = dgettext('branch', 'All done!');
                    $this->content[] = PHPWS_Text::secureLink(dgettext('branch', 'Set up allowed modules'),
                                                          'branch', array('command' => 'branch_modules',
                                                                          'branch_id' => $this->branch->id));
                    $this->resetAdmin();
                } elseif ($_SESSION['Boost']->currentDone()) {
                    $meta = sprintf('index.php?module=branch&command=core_module_installation&branch_id=%s&authkey=%s', $this->branch->id, Current_User::getAuthKey());
                    Layout::metaRoute($meta);
                }
                break;

            case 'remove_branch':
                if ( isset($_REQUEST['branch_id']) && isset($_REQUEST['branch_name']) &&
                $this->branch->branch_name === $_REQUEST['branch_name'] ) {
                    $this->branch->delete();
                }

                $this->listBranches();
                break;

            case 'force_install':
                $this->setCreateStep(2);
                $this->saveDSN();
                $this->message[] = dgettext('branch', 'Connection successful. Database available.');
                $this->edit_basic();
                break;
        }// end of the command switch
    }

    public function install_branch_core()
    {
        PHPWS_Core::initCoreClass('File.php');
        $content = array();

        $this->title = dgettext('branch', 'Install branch core');

        $dsn = $this->getDSN();
        if (empty($dsn)) {
            $this->content[] = dgettext('branch', 'Unable to get database connect information. Please try again.');
            return false;
        }

        if (!PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'admin/', $this->branch->directory . 'admin/')) {
            $this->content[] = dgettext('branch', 'Failed to copy admin file to branch.');
            return false;
        } else {
            $this->content[] = dgettext('branch', 'Copied admin file to branch.');
        }

        if (!PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'javascript/editors/fckeditor/', $this->branch->directory . 'javascript/editors/fckeditor')) {
            $this->content[] = dgettext('branch', 'Failed to copy FCKeditor to branch.');
            return false;
        } else {
            $this->content[] = dgettext('branch', 'Copied FCKeditor to branch.');
        }

        if (is_file(PHPWS_SOURCE_DIR . 'core/inc/htaccess')) {
            $this->content[] = dgettext('branch', '.htaccess detected on hub. Attempting to create default file on branch.');
            if (@copy(PHPWS_SOURCE_DIR . 'core/inc/htaccess', $this->branch->directory . '.htaccess')) {
                $this->content[] = dgettext('branch', 'Copied successfully.');
            } else {
                $this->content[] = dgettext('branch', 'Unable to copy .htaccess file.');
            }
        }

        $stats = sprintf('<?php include \'%sphpws_stats.php\' ?>', PHPWS_SOURCE_DIR);
        $index_file = sprintf('<?php include \'%sindex.php\'; ?>', PHPWS_SOURCE_DIR);
        file_put_contents($this->branch->directory . 'phpws_stats.php', $stats);
        file_put_contents($this->branch->directory . 'index.php', $index_file);

        file_put_contents($this->branch->directory . 'config/.htaccess', 'Deny from all');


        if (!$this->copy_config()) {
            $this->content[] = dgettext('branch', 'Failed to create config.php file in the branch.');
            return false;
        } else {
            $this->content[] = dgettext('branch', 'Config file created successfully.');
        }

        $result = $this->create_core();

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $this->content[] = dgettext('branch', 'Core SQL import failed.');
            return false;
        } else {
            $this->content[] = dgettext('branch', 'Core SQL import successful.');
        }

        $link = dgettext('branch', 'Core installed successfully. Continue to core module installation.');
        $vars['command']   = 'core_module_installation';
        $vars['branch_id'] = $this->branch->id;
        $this->content[] = PHPWS_Text::secureLink($link, 'branch', $vars);
        return true;
    }

    public function create_core()
    {
        $db = new PHPWS_DB;
        $loaddb = $db->loadDB($this->getDSN(), $this->dbprefix);
        if (PHPWS_Error::isError($loaddb)) {
            return $loaddb;
        }

        $result = $db->importFile(PHPWS_SOURCE_DIR . 'core/boost/install.sql');

        if ($result == TRUE) {
            $db->setTable('core_version');
            include(PHPWS_SOURCE_DIR . 'core/boost/boost.php');
            $db->addValue('version', $version);
            $result = $db->insert();
            $db->disconnect();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                return $result;
            }
            return true;
        } else {
            $db->disconnect();
            return $result;
        }
    }

    public function copy_config()
    {
        require_once 'File.php';

        $location = $this->branch->directory . 'config/core/';
        if (!is_writable($location)) {
            return false;
        }

        $filename = $this->branch->getBranchConfig();
        if (is_file($filename)) {
            return false;
        }

        $config_file[] = '<?php';
        $config_file[] = sprintf("define('PHPWS_SOURCE_DIR', '%s');", PHPWS_SOURCE_DIR);
        $config_file[] = sprintf("define('PHPWS_HOME_DIR', '%s');", $this->branch->directory);
        $config_file[] = sprintf("define('SITE_HASH', '%s');", $this->branch->site_hash);
        $config_file[] = sprintf("define('PHPWS_DSN', '%s');", $this->getDSN());
        if (!empty($this->dbprefix)) {
            $config_file[] = sprintf("define('PHPWS_TABLE_PREFIX', '%s');", $this->dbprefix);
        }
        $config_file[] = '?>';
        return file_put_contents($filename, implode("\n", $config_file));
    }

    public function post_basic()
    {
        PHPWS_Core::initCoreClass('File.php');
        $result = true;

        if (empty($this->branch->dbname) && isset($this->dbname)) {
            $this->branch->dbname = $this->dbname;
        }

        $this->branch->directory = $_POST['directory'];
        if (!preg_match('/\/$/', $this->branch->directory)) {
            $this->branch->directory .= '/';
        }

        if (!is_dir($this->branch->directory)) {
            $this->message[] = dgettext('branch', 'Branch directory does not exist.');
            $directory = explode('/', $this->branch->directory);
            // removes item after the /
            array_pop($directory);
            // removes the last directory
            array_pop($directory);
            $write_dir = implode('/', $directory);

            // only writes directory on new branches
            if (!$this->branch->id) {
                if (is_writable($write_dir)) {
                    if(@mkdir($this->branch->directory)) {
                        $this->message[] = dgettext('branch', 'Directory creation successful.');
                    } else {
                        $this->message[] = dgettext('branch', 'Unable to create the directory. You will need to create it manually.');
                        return false;
                    }
                } else {
                    $this->message[] = dgettext('branch', 'Unable to create the directory. You will need to create it manually.');
                    $result = false;
                }
            }
        } elseif (!is_writable($this->branch->directory)) {
            $this->message[] = dgettext('branch', 'Directory exists but is not writable.');
            $result = false;
        } elseif(!$this->branch->id && PHPWS_File::listDirectories($this->branch->directory)) {
            $this->message[] = dgettext('branch', 'Directory exists but already contains files.');
            $result = false;
        }

        if (empty($_POST['branch_name'])) {
            $this->message[] = dgettext('branch', 'You must name your branch.');
            $result = false;
        } elseif (!$this->branch->setBranchName($_POST['branch_name'])) {
            $this->message[] = dgettext('branch', 'You may not use that branch name.');
            $result = false;
        }

        if (empty($_POST['url'])) {
            $this->message[] = dgettext('branch', 'Enter your site\'s url address.');
            $result = false;
        } else {
            $this->branch->url = $_POST['url'];
        }

        if (empty($_POST['site_hash'])) {
            $this->message[] = dgettext('branch', 'Your branch site must have a site_hash.');
            $result = false;
        } else {
            $this->branch->site_hash = $_POST['site_hash'];
        }
        return $result;
    }


    public function core_module_installation()
    {
        if (!isset($_SESSION['Boost'])){
            $modules = PHPWS_Core::coreModList();
            $_SESSION['Boost'] = new PHPWS_Boost;
            $_SESSION['Boost']->loadModules($modules);
        }

        // Load branch database
        PHPWS_DB::loadDB($this->getDSN(), $this->dbprefix);

        $this->title = dgettext('branch', 'Installing core modules');

        $result = $_SESSION['Boost']->install(false, true, $this->branch->directory);

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $this->content[] = dgettext('branch', 'An error occurred while trying to install your modules.')
            . ' ' . dgettext('branch', 'Please check your error logs and try again.');
            return true;
        } else {
            $this->content[] = $result;
        }

        PHPWS_DB::loadDB();
        return $_SESSION['Boost']->isFinished();
    }

    /**
     * sets the current 'step' the user is in for the creation
     * of a new branch
     */
    public function setCreateStep($step)
    {
        $_SESSION['branch_create_step'] = $step;
        $this->create_step = $step;
    }

    /**
     * saves a workable dsn line for use in the creation of the branch
     */
    public function saveDSN()
    {
        $_SESSION['branch_dsn'] = array('dbtype'   => $this->dbtype,
                                        'dbuser'   => $this->dbuser,
                                        'dbpass'   => $this->dbpass,
                                        'dbhost'   => $this->dbhost,
                                        'dbport'   => $this->dbport,
                                        'dbname'   => $this->dbname,
                                        'dbprefix' => $this->dbprefix);
    }

    /**
     * if the dsn variable is set, returns it. Otherwise, it attempts to create
     * the dsn line from variables in the object. If the variables are not
     * set, it returns null
     */
    public function getDSN($dbname=true)
    {
        if (isset($this->dbuser)) {
            $dsn =  sprintf('%s://%s:%s@%s',
            $this->dbtype,
            $this->dbuser,
            $this->dbpass,
            $this->dbhost);

            if ($this->dbport) {
                $dsn .= ':' . $this->dbport;
            }

            if ($dbname) {
                $dsn .= '/' . $this->dbname;
            }
            $GLOBALS['Branch_DSN'] = $dsn;
            return $dsn;
        } else {
            return null;
        }

    }

    public function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $newLink = 'index.php?module=branch&amp;command=new';
        $newCommand = array ('title'=>dgettext('branch', 'New'), 'link'=> $newLink);

        $listLink = 'index.php?module=branch&amp;command=list';
        $listCommand = array ('title'=>dgettext('branch', 'List'), 'link'=> $listLink);

        $tabs['new'] = &$newCommand;
        $tabs['list'] = &$listCommand;

        $this->panel = new PHPWS_Panel('branch');
        $this->panel->quickSetTabs($tabs);
        $this->panel->enableSecure();
        $this->panel->setModule('branch');
    }

    /**
     * Displays the content variable in the control panel
     */
    public function displayPanel()
    {
        $template['TITLE']   = $this->title;
        if ($this->message) {
            if (is_array($this->message)) {
                $template['MESSAGE'] = implode('<br />', $this->message);
            } else {
                $template['MESSAGE'] = $this->message;
            }
        }

        if (is_array($this->content)) {
            $template['CONTENT'] = implode('<br />', $this->content);
        } else {
            $template['CONTENT'] = $this->content;
        }
        $content = PHPWS_Template::process($template, 'branch', 'main.tpl');

        $this->panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
    }

    /**
     * resets the branch creation process
     */
    public function resetAdmin()
    {
        unset($_SESSION['branch_create_step']);
        unset($_SESSION['branch_dsn']);
        unset($_SESSION['Boost']);
        $this->__construct();
    }


    /**
     * Once the database information has been posted successfully,
     * testDB determines if the database connection can be made and,
     * if so, if there a database to which to connect. If not, then
     * it creates the database (if specified)
     */
    public function testDB($force_on_populated=false)
    {
        $connection = $this->checkConnection();
        PHPWS_DB::loadDB();

        switch ($connection) {
            case BRANCH_CONNECT_NO_DB:
                // connection made, but database does not exist
                if (isset($_POST['createdb'])) {
                    $result = $this->createDB();
                    if (PHPWS_Error::isError($result)) {
                        $this->message[] = dgettext('branch', 'An error occurred when trying to connect to the database.');
                        $this->edit_db();
                    } elseif ($result) {
                        $this->message[] = dgettext('branch', 'Database created successfully.');
                        $this->setCreateStep(2);
                        $this->saveDSN();
                        $this->edit_basic();
                    } else {
                        $this->message[] = dgettext('branch', 'Unable to create the database. You will need to create it manually.');
                        $this->edit_db();
                    }
                } else {
                    $this->message[] = dgettext('branch', 'Connected successfully, but the database does not exist.');
                    $this->edit_db();
                }
                break;

            case BRANCH_NO_CONNECTION:
            case BRANCH_CONNECT_BAD_DB:
                // Failed connection
                $this->message[] = dgettext('branch', 'Could not connect to the database.');
                $this->edit_db();
                break;

            case BRANCH_CONNECT_SUCCESS:
                // connection successful
                $this->setCreateStep(2);
                $this->saveDSN();
                $this->message[] = dgettext('branch', 'Connection successful. Database available.');
                $this->edit_basic();
                break;

            case BRANCH_CONNECT_WITH_TABLES:
                if ($force_on_populated && !empty($this->dbprefix)) {
                    $this->setCreateStep(2);
                    $this->saveDSN();
                    $this->message[] = dgettext('branch', 'Connection successful. Database available.');
                    $this->edit_basic();
                } else {
                    $this->message[] = dgettext('branch', 'Connected successfully, but this database already contains tables.');

                    if (!empty($this->dbprefix)) {
                        $this->message[] = dgettext('branch', 'Though not recommended, you can force installation by clicking Continue below.');
                        $force = true;
                    } else {
                        $this->message[] = dgettext('branch', 'Though not recommended, prefixing will allow you to install to this database.');
                        $force = false;
                    }
                    $this->edit_db($force);
                }
                break;
        }
    }

    public function edit_basic()
    {
        $branch = $this->branch;

        $form = new PHPWS_Form('branch-form');
        $form->addHidden('module', 'branch');
        $form->addHidden('command', 'post_basic');

        if ($branch->id) {
            $this->title = dgettext('branch', 'Edit branch');
            $form->addHidden('branch_id', $this->branch->id);
            $form->addSubmit('submit', dgettext('branch', 'Update'));
        } else {
            $this->title = dgettext('branch', 'Create branch information');
            $form->addSubmit('submit', dgettext('branch', 'Continue...'));
        }

        $form->addText('branch_name', $branch->branch_name);
        $form->setLabel('branch_name', dgettext('branch', 'Branch name'));

        $form->addText('directory', $branch->directory);
        $form->setSize('directory', 50);
        $form->setLabel('directory', dgettext('branch', 'Full directory path'));

        $form->addText('url', $branch->url);
        $form->setSize('url', 50);
        $form->setLabel('url', dgettext('branch', 'URL'));

        $form->addText('site_hash', $branch->site_hash);
        $form->setSize('site_hash', 40);
        $form->setLabel('site_hash', dgettext('branch', 'ID hash'));
        $template = $form->getTemplate();
        $template['BRANCH_LEGEND'] = dgettext('branch', 'Branch specifications');
        $this->content = PHPWS_Template::process($template, 'branch', 'edit_basic.tpl');
    }

    /**
     * Form to create or edit a branch
     */
    public function edit_db($force=false)
    {
        $this->title = dgettext('branch', 'Setup branch database');
        $form = new PHPWS_Form('branch-form');
        $form->addHidden('module', 'branch');
        $form->addHidden('command', 'post_db');
        $form->addHidden('force', (int)$force);

        $form->addCheck('createdb', $this->createdb);
        $form->setLabel('createdb', dgettext('branch', 'Create new database'));

        $form->addSelect('dbtype', $this->db_list);
        $form->setMatch('dbtype', $this->dbtype);
        $form->setLabel('dbtype', dgettext('branch', 'Database syntax'));

        $form->addText('dbname', $this->dbname);
        $form->setLabel('dbname', dgettext('branch', 'Database name'));

        $form->addText('dbuser', $this->dbuser);
        $form->setLabel('dbuser', dgettext('branch', 'Permission user'));

        $form->addPassword('dbpass', $this->dbpass);
        $form->allowValue('dbpass');
        $form->setLabel('dbpass', dgettext('branch', 'User password'));

        $form->addText('dbprefix', $this->dbprefix);
        $form->setLabel('dbprefix', dgettext('branch', 'Table prefix'));
        $form->setSize('dbprefix', 5, 5);

        $form->addText('dbhost', $this->dbhost);
        $form->setLabel('dbhost', dgettext('branch', 'Database Host'));
        $form->setSize('dbhost', 40);

        $form->addText('dbport', $this->dbport);
        $form->setLabel('dbport', dgettext('branch', 'Connection Port'));

        $form->addTplTag('DB_LEGEND', dgettext('branch', 'Database information'));

        $form->addSubmit('plug', dgettext('branch', 'Use hub values'));
        $form->addSubmit('submit', dgettext('branch', 'Continue...'));

        $template = $form->getTemplate();

        $this->content = PHPWS_Template::process($template, 'branch', 'edit_db.tpl');
    }

    public function checkConnection()
    {
        $dsn1 =  sprintf('%s://%s:%s@%s',
        $this->dbtype,
        $this->dbuser,
        $this->dbpass,
        $this->dbhost);

        if ($this->dbport) {
            $dsn1 .= ':' . $this->dbport;
        }

        $dsn2 = $dsn1 . '/' . $this->dbname;

        $pear_db = new DB;
        $connection = $pear_db->connect($dsn1);

        if (PHPWS_Error::isError($connection)) {
            // basic connection failed
            PHPWS_Error::log($connection);
            return BRANCH_NO_CONNECTION;
        } else {
            $pear_db2 = new DB;
            $connection2 = $pear_db2->connect($dsn2);

            if (PHPWS_Error::isError($connection2)) {
                // check to see if the database does not exist
                // mysql delivers the first error, postgres the second
                if ($connection2->getCode() == DB_ERROR_NOSUCHDB ||
                $connection2->getCode() == DB_ERROR_CONNECT_FAILED) {
                    return BRANCH_CONNECT_NO_DB;
                } else {
                    // connection failed with db name
                    PHPWS_Error::log($connection2);
                    return BRANCH_CONNECT_BAD_DB;
                }
            } else {
                $tables = $connection2->getlistOf('tables');
                if (!empty($tables)) {
                    // connect was successful but database already contains tables
                    return BRANCH_CONNECT_WITH_TABLES;
                } else {
                    // connection successful, table exists and is empty
                    return BRANCH_CONNECT_SUCCESS;
                }
            }
        }
    }

    /**
     * copies the db form settings into the object
     */
    public function post_db()
    {
        $result = true;
        $this->dbuser   = $_POST['dbuser'];
        $this->dbpass   = $_POST['dbpass'];
        $this->dbhost   = $_POST['dbhost'];
        $this->dbtype   = $_POST['dbtype'];
        $this->dbport   = $_POST['dbport'];
        $this->dbprefix = $_POST['dbprefix'];

        $this->dbname = $_POST['dbname'];
        if (!PHPWS_DB::allowed($this->dbname)) {
            $this->message[] = dgettext('branch', 'This database name is not allowed.');
            $result = false;
        }

        if (empty($this->dbname)) {
            $this->message[] = dgettext('branch', 'You must type a database name.');
            $result = false;
        }

        if (empty($this->dbuser)) {
            $this->message[] = dgettext('branch', 'You must type a database user.');
            $result = false;
        }

        if (preg_match('/\W/', $this->dbprefix)) {
            $content[] = dgettext('branch', 'Table prefix must be alphanumeric characters or underscores only');
            $result = false;
        }

        return $result;
    }

    /**
     * Grabs the current database values from the hub installation
     */
    public function plugHubValues()
    {
        $dsn = & $GLOBALS['PHPWS_DB']['connection']->dsn;

        $this->dbname = $dsn['database'];
        $this->dbuser = $dsn['username'];
        $this->dbpass = $dsn['password'];
        $this->dbhost = $dsn['hostspec'];
        if ($dsn['port']) {
            $this->dbport = $dsn['port'];
        } else {
            $this->dbport = null;
        }

        $this->dbprefix = PHPWS_DB::getPrefix();
        // dsn also contains dbsyntax
        $this->dbtype = $dsn['phptype'];
    }

    /**
     * Creates a new database with the dsn information
     */
    public function createDB()
    {
        $dsn = $this->getDSN(false);
        if (empty($dsn)) {
            return false;
        }

        $tdb = new DB;
        $db = $tdb->connect($dsn);

        if (PHPWS_Error::isError($db)) {
            PHPWS_Error::log($db);
            return $db;
        }

        $result = $db->query('CREATE DATABASE ' . $this->dbname);
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($db);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Form that allows the hub admin determine which modules a
     * branch can install.
     */
    public function edit_modules()
    {
        PHPWS_Core::initCoreClass('File.php');
        $this->title = sprintf(dgettext('branch', 'Module access for "%s"'), $this->branch->branch_name);

        $content = null;

        $core_mods = PHPWS_Core::coreModList();
        $all_mods = PHPWS_File::readDirectory(PHPWS_SOURCE_DIR . 'mod/', true);
        $all_mods = array_diff($all_mods, $core_mods);

        foreach ($all_mods as $key => $module) {
            if (is_file(PHPWS_SOURCE_DIR . 'mod/' . $module . '/boost/boost.php')) {
                $dir_mods[] = $module;
            }
        }

        $db = new PHPWS_DB('branch_mod_limit');
        $db->addWhere('branch_id', $this->branch->id);
        $db->addColumn('module_name');
        $branch_mods = $db->select('col');

        unset($dir_mods[array_search('branch', $dir_mods)]);
        sort($dir_mods);
        $form = new PHPWS_Form('module_list');
        $form->useRowRepeat();

        $form->addHidden('module', 'branch');
        $form->addHidden('command', 'save_branch_modules');
        $form->addHidden('branch_id', $this->branch->id);

        $form->addCheck('module_name', $dir_mods);
        $form->setLabel('module_name', $dir_mods);
        if (!empty($branch_mods)) {
            $form->setMatch('module_name', $branch_mods);
        }

        $form->addSubmit('submit', dgettext('branch', 'Save'));

        $form->addTplTag('CHECK_ALL', javascript('check_all', array('checkbox_name' => 'module_name')));

        $template = $form->getTemplate();

        $template['DIRECTIONS'] = dgettext('branch', 'Unchecked modules cannot be installed on this branch.');

        $content = PHPWS_Template::process($template, 'branch', 'module_list.tpl');
        $this->content = & $content;
    }

    /**
     * Lists the branches on the system
     */
    public function listBranches()
    {
        $page_tags['BRANCH_NAME_LABEL'] = dgettext('branch', 'Branch name');
        $page_tags['DIRECTORY_LABEL']   = dgettext('branch', 'Directory');
        $page_tags['URL_LABEL']         = dgettext('branch', 'Url');
        $page_tags['ACTION_LABEL']      = dgettext('branch', 'Action');

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('branch_sites', 'Branch');
        $pager->setModule('branch');
        $pager->setTemplate('branch_list.tpl');
        $pager->addPageTags($page_tags);
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getTpl');
        $this->title = dgettext('branch', 'Branch List');
        $this->content = $pager->get();
    }

    public function saveBranchModules()
    {
        $db = new PHPWS_DB('branch_mod_limit');
        $db->addWhere('branch_id', (int)$_POST['branch_id']);
        $db->delete();
        $db->reset();

        if (empty($_POST['module_name']) || !is_array($_POST['module_name'])) {
            return;
        }

        foreach ($_POST['module_name'] as $module) {
            $db->addValue('branch_id', (int)$_POST['branch_id']);
            $db->addValue('module_name', $module);
            $result = $db->insert();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                return false;
            }
            $db->reset();
        }
        return true;
    }

    public static function getBranches($load_db_info=false)
    {
        $db = new PHPWS_DB('branch_sites');
        $result = $db->getObjects('Branch');
        if (PHPWS_Error::isError($result) || !$load_db_info || empty($result)) {
            return $result;
        }
        foreach ($result as $branch) {
            if ($branch->loadDSN()) {
                $new_result[] = $branch;
            }
        }

        if (isset($new_result)) {
            return $new_result;
        } else {
            return $result;
        }
    }

}

?>