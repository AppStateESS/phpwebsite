<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('branch', 'Branch.php');

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
        $template['MESSAGE'] = $this->message;
        $template['CONTENT'] = $this->content;
        $content = PHPWS_Template::process($template, 'branch', 'main.tpl');
        
        $this->panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
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
            // Creating new branch
            $this->branch = & new Branch;
            $this->edit();
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

        case 'post_branch':
            // post a new or updated branch to the system
            if (isset($_POST['plug'])) {
                $this->plugHubValues();
                $this->edit();
            } else {
                if (!$this->branch->id && isset($_POST['postdb'])) {
                    if (!$this->postDB()) {
                        $this->edit();
                        break;
                    }

                    $connection = $this->checkConnection();
                    switch ($connection) {
                    case -1:
                        // connection made, but database does not exist
                        if (isset($_POST['createdb'])) {
                            $result = $this->createDB();
                        } else {
                            $this->message = _('Connected successfully, but the database does not exist.');
                            $this->edit();
                        }
                        break;

                    case 0:
                        // Failed connection
                        $this->message = _('Could not connect to the database.');
                        $this->edit();
                        break;

                    case 1:
                        // connection successful
                        break;
                    }
                }
            }
            break;
        }

    }

    /**
     * Form to create or edit a branch
     */
    function edit()
    {
        $branch = & $this->branch;

        $form = & new PHPWS_Form('branch-form');
        $form->addHidden('module', 'branch');
        $form->addHidden('command', 'post_branch');

        if ($branch->id) {
            $this->title = _('Update Branch');
            $form->addHidden('branch_id', $this->branch->id);
            $form->addSubmit('submit', _('Update'));
        } else {
            $form->addHidden('postdb', 1);
            // new branches need the database information created

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

            $form->addText('dbhost', $this->dbhost);http://spong.com/
            $form->setLabel('dbhost', _('Database Host'));
            $form->setSize('dbhost', 40);

            $form->addText('dbport', $this->dbport);
            $form->setLabel('dbport', _('Connection Port'));

            $form->addTplTag('DB_LEGEND', _('Database information'));

            $form->addSubmit('plug', _('Use hub values'));
            $this->title = _('Create Branch');
            $form->addSubmit('submit', _('Create'));
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

        $this->content = PHPWS_Template::process($template, 'branch', 'edit.tpl');

    }

    function checkConnection()
    {
        $dsn1 =  sprintf('%s://%s:%s@%s'
                        $this->dbtype,
                        $this->dbuser,
                        $this->dbpass,
                        $this->dbhost);

        if ($this->dbport) {
            $dsn1 .= ':' . $this->dbport;
        }

        $dsn2 = $dsn1 . '/' . $this->dsname;

        $connection = DB::connect($dsn1);
        if (PEAR::isError($connection)){
            PHPWS_Error::log($connection);
            return 0;
        } else {
            $connection->disconnect();

            $dsn2 = '/' . $dbname;
            $result = DB::connect($dsn2);

            if (PEAR::isError($result)) {
                // check to see if the database does not exist
                // mysql delivers the first error, postgres the second
                if ($result->getCode() == DB_ERROR_NOSUCHDB ||
                    $result->getCode() == DB_ERROR_CONNECT_FAILED) {
                    return -1;
                } else {
                    PHPWS_Error::log($connection);
                    return 0;
                }
            }
            $connection->disconnect();
            return 1;
        }
    }

    function postDB()
    {
        $this->dbname = $_POST['dbname'];
        if (!PHPWS_DB::allowed($this->dbname)) {
            $this->message = _('This database name is not allowed.');
            return FALSE;
        }

        $this->dbuser = $_POST['dbuser'];
        $this->dbpass = $_POST['dbpass'];
        $this->dbhost = $_POST['dbhost'];
        $this->dbtype = $_POST['dbtype'];
        $this->dbport = $_POST['dbport'];

        if (empty($this->dbname)) {
            $this->message = _('You must type a database name.');
            return FALSE;
        }

        if (empty($this->dbuser)) {
            $this->message = _('You must type a database user.');
            return FALSE;
        }

        return TRUE;
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
     * Lists the branches on the system
     */
    function listBranches()
    {

    }

}

?>