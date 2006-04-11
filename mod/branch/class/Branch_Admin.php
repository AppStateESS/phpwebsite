<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('branch', 'Branch.php');

define('BRANCH_NO_CONNECTION',       1);
define('BRANCH_CONNECT_NO_DB',       2);
define('BRANCH_CONNECT_WITH_TABLES', 3);
define('BRANCH_CONNECT_SUCCESS',     4);
define('BRANCH_CONNECT_BAD_DB',      5);

class Branch_Admin {
    // Contains the panel object
    var $panel   = NULL;

    var $title   = NULL;

    var $message = NULL;

    // contains the current content. piped into panel
    var $content = NULL;

    // currently 
    var $branch  = NULL;

    var $error   = NULL;

    // database creation form variables
    var $createdb    = 0;
    var $dbname      = NULL;
    var $dbuser      = NULL;
    var $dbpass      = NULL;
    var $dbhost      = 'localhost';
    var $dbport      = NULL;
    var $dbtype      = NULL;

    // working dsn
    var $dsn         = NULL;

    var $create_step = 1;

    var $db_list    = NULL;

    function Branch_Admin()
    {
        $this->db_list = array ('mysql' =>'MySQL',
                                'ibase' =>'InterBase',
                                'mssql' =>'Microsoft SQL Server',
                                'msql'  =>'Mini SQL',
                                'oci8'  =>'Oracle 7/8/8i',
                                'odbc'  =>'ODBC',
                                'pgsql' =>'PostgreSQL',
                                'sybase'=>'SyBase',
                                'fbsql' =>'FrontBase',
                                'ifx'   =>'Informix');

        if (isset($_SESSION['branch_create_step'])) {
            $this->create_step = $_SESSION['branch_create_step'];
        }

        if (isset($_SESSION['branch_dsn'])) {
            $this->dsn = $_SESSION['branch_dsn'];
        }

        if (isset($_REQUEST['branch_id'])) {
            $this->branch = & new Branch($_REQUEST['branch_id']);
        } else {
            $this->branch = & new Branch;
        }
    }


    function main()
    {
        $content = NULL;
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
    function direct()
    {
        if (!@$command = $_REQUEST['command']) {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'new':
            $this->resetCreate();
            $this->edit_db();
            break;

        case 'edit':
            // editing existing branch
            if (empty($this->branch->id)) {
                $this->content = _('Incorrect or missing branch id.');
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
                    $this->testDB();
                }
            }
            break;

        case 'post_basic':
            if (!$this->post_basic()) {
                $this->edit_basic();
            } else {
                $result = $this->branch->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->title = _('An error occurred while saving your branch.');
                    $this->content = $result->getMessage();
                    return;
                }
                $this->setCreateStep(3);
                $this->message[] = _('Branch created successfully.');
                $this->moduleInstallForm();
            }
            break;
        }// end of the command switch

    }


    function post_basic()
    {
        PHPWS_Core::initCoreClass('File.php');
        $result = TRUE;

        $this->branch->directory = $_POST['directory'];
        $this->branch->directory = preg_replace('/\/$/', '', $this->branch->directory);

        if (!is_dir($this->branch->directory)) {
            $this->message[] = _('Branch directory does not exist.');
            $directory = explode('/', $this->branch->directory);
            array_pop($directory);
            $write_dir = implode('/', $directory);
            if (is_writable($write_dir)) {
                if(@mkdir($this->branch->directory)) {
                    $this->message[] = _('Directory creation successful.');
                } else {
                    $this->message[] = _('Unable to create the directory. You will need to create it manually.');
                    $result = FALSE;
                }
            } else {
                $this->message[] = _('Unable to create the directory. You will need to create it manually.');
                $result = FALSE;
            }
        } elseif(PHPWS_File::listDirectories($this->branch->directory)) {
                $this->message[] = _('Directory exists but already contains files.');
                $result = FALSE;
        }

        if (empty($_POST['branch_name'])) {
            $this->message[] = _('You must name your branch.');
            $result = FALSE;
        } else {
            $this->branch->branch_name = $_POST['branch_name'];
        }

        if (empty($_POST['url'])) {
            $this->message[] = _('Enter your site\'s url address.');
            $result = FALSE;
        } else {
            $this->branch->branch_name = $_POST['url'];
        }


        return $result;
    }


    function moduleInstallForm()
    {
        $this->title = _('Branch module installation.');
        $this->content = _('');
        test($_POST);
    }

    /**
     * sets the current 'step' the user is in for the creation
     * of a new branch
     */
    function setCreateStep($step)
    {
        $_SESSION['branch_create_step'] = $step;
        $this->create_step = $step;
    }

    /**
     * saves a workable dsn line for use in the creation of the branch
     */
    function saveDSN()
    {
        $_SESSION['branch_dsn'] = $this->getDSN();
    }

    /**
     * if the dsn variable is set, returns it. Otherwise, it attempts to create
     * the dsn line from variables in the object. If the variables are not
     * set, it returns NULL
     */
    function &getDSN()
    {
        if (isset($this->dsn)) {
            return $this->dsn;
        } elseif (isset($this->dbuser)) {

            $this->dsn =  sprintf('%s://%s:%s@%s',
                            $this->dbtype,
                            $this->dbuser,
                            $this->dbpass,
                            $this->dbhost);
            
            if ($this->dbport) {
                $this->dsn .= ':' . $this->dbport;
            }
            
            return $this->dsn;
        } else {
            return NULL;
        }

    }

    function &cpanel()
    {
            PHPWS_Core::initModClass('controlpanel', 'Panel.php');
            $newLink = 'index.php?module=branch&amp;command=new';
            $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
        
            $listLink = 'index.php?module=branch&amp;command=list';
            $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

            $tabs['new'] = &$newCommand;
            $tabs['list'] = &$listCommand;

            $panel = & new PHPWS_Panel('branch');
            $panel->quickSetTabs($tabs);

            $panel->setModule('branch');
            $this->panel = &$panel;
    }
    
    /**
     * Displays the content variable in the control panel
     */
    function displayPanel()
    {
        $template['TITLE']   = $this->title;
        if ($this->message) {
            $template['MESSAGE'] = implode('<br />', $this->message);
        }
        $template['CONTENT'] = $this->content;
        $content = PHPWS_Template::process($template, 'branch', 'main.tpl');
        
        $this->panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
    }

    /**
     * resets the branch creation process
     */
    function resetCreate()
    {
        unset($_SESSION['branch_create_step']);
        unset($_SESSION['branch_dsn']);
        $this->create_step = 1;
    }


    /**
     * Once the database information has been posted successfully,
     * testDB determines if the database connection can be made and,
     * if so, if there a database to which to connect. If not, then
     * it creates the database (if specified)
     */
    function testDB()
    {
        $connection = $this->checkConnection();
        switch ($connection) {
        case BRANCH_CONNECT_NO_DB:
            // connection made, but database does not exist
            if (isset($_POST['createdb'])) {
                $result = $this->createDB();
                if (PEAR::isError($result)) {
                    $this->message[] = _('An error occurred when trying to connect to the database.');
                    $this->edit_db();
                } elseif ($result) {
                    $this->message[] = _('Database created successfully.');
                    $this->setCreateStep(2);
                    $this->saveDSN();
                    $this->edit_basic();
                } else {
                    $this->message[] = _('Unable to create the database. You will need to create it manually.');
                    $this->edit_db();
                }
            } else {
                $this->message[] = _('Connected successfully, but the database does not exist.');
                $this->edit_db();
            }
            break;

        case BRANCH_NO_CONNECTION:
            // Failed connection
            $this->message[] = _('Could not connect to the database.');
            $this->edit_db();
            break;

        case BRANCH_CONNECT_SUCCESS:
            // connection successful
            $this->setCreateStep(2);
            $this->saveDSN();
            $this->message[] = _('Connection successful. Database available.');
            $this->edit_basic();
            break;

        case BRANCH_CONNECT_WITH_TABLES:
            $this->message[] = _('Connected successfully, but this database already contains tables.');
            $this->edit_db();
            break;
        }
    }

    function edit_basic()
    {
        $branch = & $this->branch;

        $form = & new PHPWS_Form('branch-form');
        $form->addHidden('module', 'branch');
        $form->addHidden('command', 'post_basic');

        if ($branch->id) {
            $this->title = _('Edit branch');
            $form->addHidden('branch_id', $this->branch->id);
            $form->addSubmit('submit', _('Update'));
        } else {
            $this->title = _('Create branch information');
            $form->addSubmit('submit', _('Continue...'));
        }

        $form->addText('branch_name', $branch->branch_name);
        $form->setLabel('branch_name', _('Branch name'));

        $form->addText('directory', $branch->directory);
        $form->setSize('directory', 50);
        $form->setLabel('directory', _('Directory'));

        $form->addText('url', $branch->url);
        $form->setSize('url', 50);
        $form->setLabel('url', _('URL'));

        $form->addText('hash', $branch->hash);
        $form->setSize('hash', 40);
        $form->setLabel('hash', _('ID hash'));
        $template = $form->getTemplate();
        $template['BRANCH_LEGEND'] = _('Branch specifications');
        $this->content = PHPWS_Template::process($template, 'branch', 'edit_basic.tpl');
    }

    /**
     * Form to create or edit a branch
     */
    function edit_db()
    {
        $this->title = _('Setup branch database');
        $form = & new PHPWS_Form('branch-form');
        $form->addHidden('module', 'branch');
        $form->addHidden('command', 'post_db');

        $form->addCheck('createdb', $this->createdb);
        $form->setLabel('createdb', _('Create new database'));
        
        $form->addSelect('dbtype', $this->db_list);
        $form->setMatch('dbtype', $this->dbtype);
        $form->setLabel('dbtype', _('Database syntax'));
        
        $form->addText('dbname', $this->dbname);
        $form->setLabel('dbname', _('Database name'));
        
        $form->addText('dbuser', $this->dbuser);
        $form->setLabel('dbuser', _('Permission user'));
        
        $form->addPassword('dbpass', $this->dbpass);
        $form->allowValue('dbpass');
        $form->setLabel('dbpass', _('User password'));
        
        $form->addText('dbhost', $this->dbhost);
        $form->setLabel('dbhost', _('Database Host'));
        $form->setSize('dbhost', 40);
        
        $form->addText('dbport', $this->dbport);
        $form->setLabel('dbport', _('Connection Port'));
        
        $form->addTplTag('DB_LEGEND', _('Database information'));
        
        $form->addSubmit('plug', _('Use hub values'));
        $form->addSubmit('submit', _('Continue...'));
        
        $template = $form->getTemplate();

        $this->content = PHPWS_Template::process($template, 'branch', 'edit_db.tpl');

    }

    function checkConnection()
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

        $connection = DB::connect($dsn1);
        if (PEAR::isError($connection)) {
            // basic connection failed
            PHPWS_Error::log($connection);
            return BRANCH_NO_CONNECTION;
        } else {
            $connection->disconnect();
            $connection2 = DB::connect($dsn2);

            if (PEAR::isError($connection2)) {
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
            $connection2->disconnect();
        }
    }

    /**
     * copies the db form settings into the object
     */
    function post_db()
    {
        $result = TRUE;
        $this->dbuser = $_POST['dbuser'];
        $this->dbpass = $_POST['dbpass'];
        $this->dbhost = $_POST['dbhost'];
        $this->dbtype = $_POST['dbtype'];
        $this->dbport = $_POST['dbport'];

        $this->dbname = $_POST['dbname'];
        if (!PHPWS_DB::allowed($this->dbname)) {
            $this->message[] = _('This database name is not allowed.');
            $result = FALSE;
        }

        if (empty($this->dbname)) {
            $this->message[] = _('You must type a database name.');
            $result = FALSE;
        }

        if (empty($this->dbuser)) {
            $this->message[] = _('You must type a database user.');
            $result = FALSE;
        }

        return $result;
    }

    /**
     * Grabs the current database values from the hub installation
     */
    function plugHubValues()
    {
        $dsn = & $GLOBALS['PEAR_DB']->dsn;

        $this->dbuser = $dsn['username'];
        $this->dbpass = $dsn['password'];
        $this->dbhost = $dsn['hostspec'];
        if ($dsn['port']) {
            $this->dbport = $dsn['port'];
        } else {
            $this->dbport = NULL;
        }

        // dsn also contains dbsyntax
        $this->dbtype = $dsn['phptype'];
    }

    /**
     * Creates a new database with the dsn information
     */
    function createDB()
    {
        $dsn = $this->getDSN();
        if (empty($dsn)) {
            return FALSE;
        }
        $db = & DB::connect($dsn);

        if (PEAR::isError($db)) {
            PHPWS_Error::log($db);
            return $db;
        }

        $result = $db->query('CREATE DATABASE ' . $this->dbname);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($db);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Lists the branches on the system
     */
    function listBranches()
    {

    }

    /**
     * Form to decide which modules to install
     */
    function edit_modules()
    {

    }
}

?>