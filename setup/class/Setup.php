<?php

class Setup{

  function configExists(){
    return is_file("config/core/config.php");
  }

  function initConfigSet(){
    if (!isset($_SESSION['configSettings'])){
      $_SESSION['configSettings']['general']  = FALSE;
      $_SESSION['configSettings']['database'] = FALSE;
      Setup::setConfigSet('source_dir', Setup::getSourceDir());
      Setup::setConfigSet('site_hash', md5(rand()));
      Setup::setConfigSet('dbname', DEFAULT_DBNAME);
      Setup::setConfigSet('dbuser', DEFAULT_DBUSER);
      Setup::setConfigSet('dbport', DEFAULT_DBPORT);
      Setup::setConfigSet('dbhost', DEFAULT_DBHOST);
      Setup::setConfigSet('dbtype', DEFAULT_DBTYPE);
    }
  }


  function createConfig(&$content){
    Setup::initConfigSet();

    if (isset($_POST['action'])){
      if ($_POST['action'] == "postGeneralConfig"){
	if (Setup::postGeneralConfig($content))
	  $_SESSION['configSettings']['general'] = TRUE;
      } elseif ($_POST['action'] == "postDatabaseConfig"){
	if (Setup::postDatabaseConfig($content))
	  $_SESSION['configSettings']['database'] = TRUE;
      }
    }

    if ($_SESSION['configSettings']['general'] == FALSE)
      Setup::generalConfig($content);
    elseif ($_SESSION['configSettings']['database'] == FALSE)
      Setup::databaseConfig($content);
    else {
      $configDir = Setup::getConfigSet("source_dir") . "config/core/";
      if (is_file($configDir . "config.php")){
	$content[] = _("Your configuration file already exists.") . "<br />"
	  . _("Remove the following file and refresh to continue:") . "<br />"
	  . "<pre>" . $configDir . "config.php</pre>";
      }
      elseif (Setup::writeConfigFile()){
	PHPWS_Core::killSession("configSettings");
	$content[] = _("Your configuration file was written successfully!") . "<br /><br />";
	$content[] = PHPWS_Text::link("index.php", _("Move on to Step 2"), array("step"=>2)) . "<br />";
      } else {
	$content[] = _("Your configuration file could not be written into the following directory:") . "<br />";
	$content[] = "<pre>$configDir</pre>";
	$content[] = _("Please check your directory permissions and try again.") . "<br />";
	$content[] = PHPWS_Text::link("help/permissions." . DEFAULT_LANGUAGE . ".txt", _("Permission Help"), NULL, "index");
      }
    }
  }

  function writeConfigFile(){
    require_once "File.php";

    $location = Setup::getConfigSet("source_dir") . "config/core/";
    if (!is_writable($location))
      return FALSE;

    $tpl = & new PHPWS_Template;
    $tpl->setFile("setup/templates/config.tpl", TRUE);
    $tpl->setData($_SESSION['configSettings']);
    $configFile = $tpl->get();

    return File::write($location . "config.php", $configFile, FILE_MODE_WRITE);
  }

  function postGeneralConfig(&$content){
    $check = TRUE;
    $source_dir = $_POST['source_dir'];

    if (!preg_match("/\/$/", $source_dir))
      $source_dir = $source_dir . "/";

    if (!is_dir($source_dir)){
      $content[] = _("Unable to locate the source directory:") . " " . $source_dir;
      $check = FALSE;
    }
    else
      Setup::setConfigSet("source_dir", $source_dir);


    Setup::setConfigSet("LINUX_PEAR", "//");
    Setup::setConfigSet("WINDOWS_PEAR", "//");

    if ($_POST['pear'] == 'local'){
      if (PHPWS_Core::isWindows())
	Setup::setConfigSet("WINDOWS_PEAR", NULL);
      else
	Setup::setConfigSet("LINUX_PEAR", NULL);
    }

    Setup::setConfigSet("site_hash", $_POST['site_hash']);
    return $check;
  }

  function postDatabaseConfig(&$content){
    $check = TRUE;
    $currentPW = Setup::getConfigSet("dbpass");

    if (!empty($_POST['dbuser']))
      Setup::setConfigSet("dbuser", $_POST['dbuser']);
    else {
      $content[] = _("Missing a database user name.") . "<br />";
      $check = FALSE;
    }

    if (!empty($_POST['dbpass']))
      Setup::setConfigSet("dbpass", $_POST['dbpass']);
    elseif (empty($currentPW)) {
      $content[] = _("Missing a database password.") . "<br />";
      $check = FALSE;
    }

    if (!empty($_POST['dbhost']))
      $content[] = _("Notice: Missing a host reference.") . "<br />";

    Setup::setConfigSet("dbhost", $_POST['dbhost']);

    if (!empty($_POST['dbname']))
      Setup::setConfigSet("dbname", $_POST['dbname']);
    else {
      $content[] = _("Missing a database name.") . "<br />";
      $check = FALSE;
    }

    if (!empty($_POST['dbprefix'])){
      if (preg_match("/^([a-z])+([a-z0-9_]*)$/i", $_POST['dbprefix']))
	Setup::setConfigSet("dbprefix", $_POST['dbprefix']);
      else {
	$content[] = _("The Table Prefix may only consist of letters, numbers, and the underscore character.") . "<br />";
	$content[] = _("It also may not begin with a number.") . "<br />";
	$check = FALSE;
      }
    }

    Setup::setConfigSet("dbtype", $_POST['dbtype']);
    Setup::setConfigSet("dbport", $_POST['dbport']);


    if (!$check)
      return FALSE;

    if (CHECK_DB_CONNECTION == FALSE)
      return TRUE;

    if (Setup::testDBConnect())
      return TRUE;
    else {
      $content[] = _("Unable to connect to the database with the information provided.") . "<br />";
      $content[] = PHPWS_Text::link("help/database." . DEFAULT_LANGUAGE . ".txt", _("Database Help"), NULL, "index");
      return FALSE;
    }

  }

  function testDBConnect(){
    $dbtype = Setup::getConfigSet("dbtype");
    $dbuser = Setup::getConfigSet("dbuser");
    $dbpass = Setup::getConfigSet("dbpass");
    $dbhost = Setup::getConfigSet("dbhost");
    $dbport = Setup::getConfigSet("dbport");
    $dbname = Setup::getConfigSet("dbname");

    $dsn = $dbtype . "://" . $dbuser . ":" . $dbpass . "@" . $dbhost;

    if (!empty($dbport))
      $dsn .= ":" . $dbport;

    $dsn .= "/" . $dbname;
    
    $result = DB::connect($dsn);

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    }
    else {
      Setup::setConfigSet("dsn", $dsn);
      return TRUE;
    }
  }

  function setConfigSet($setting, $value){
    $_SESSION['configSettings'][$setting] = $value;
  }

  function getConfigSet($setting){
    if (!isset($_SESSION['configSettings']) || !isset($_SESSION['configSettings'][$setting]))
      return NULL;

    return $_SESSION['configSettings'][$setting];
  }

  function generalConfig(&$content){
    $form = & new PHPWS_Form("generalConfig");
    $site_hash  = Setup::getConfigSet("site_hash");
    $source_dir = Setup::getConfigSet("source_dir");
    $pear_select = array("local" =>_("Use Pear files included with phpWebSite (recommended)."),
			 "system"=>_("Use server's Pear library files (not recommended).")
			 );

    //    $content[] = _("To get started, we need to create your config file.");

    $formTpl['SOURCE_DIR_LBL'] = _("Source Directory");
    $formTpl['SOURCE_DIR_DEF'] = _("This is the directory where phpWebSite is installed.");

    $formTpl['SITE_HASH_LBL'] = _("Site Hash");
    $formTpl['SITE_HASH_DEF'] = _("The character string below differentiates your site from others on the same server.") . "<br />"
      . _("The example is randomly generated.") . " "
      . _("You may change it if you wish.");

    $formTpl['PEAR_LBL'] = _("Pear Configuration");
    $formTpl['PEAR_DEF'] = _("phpWebSite uses the Pear library extensively.") . "<br />"
      . _("We suggest you use the library files included with phpWebSite.");

    $form->add("source_dir", "textfield", $source_dir);
    $form->setSize("source_dir", 50);
    $form->add("step",   "hidden", "1");
    $form->add("action", "hidden", "postGeneralConfig");

    $form->add("site_hash", "textfield", $site_hash);
    $form->setSize("site_hash", 40);

    $form->add("pear", "select", $pear_select);
    $form->setMatch("pear", "local");

    $form->mergeTemplate($formTpl);
    $content[] = Setup::createForm($form, "generalConfig.tpl");
  }


  function databaseConfig(&$content){
    $form = & new PHPWS_Form("databaseConfig");
    $form->add("step",   "hidden", "1");
    $form->add("action", "hidden", "postDatabaseConfig");

    $databases = array ("mysql" =>"MySQL",
			"ibase" =>"InterBase",
			"mssql" =>"Microsoft SQL Server",
			"msql"  =>"Mini SQL",
			"oci8"  =>"Oracle 7/8/8i",
			"odbc"  =>"ODBC",
			"pgsql" =>"PostgreSQL",
			"sybase"=>"SyBase",
			"fbsql" =>"FrontBase",
			"ifx"   =>"Informix");

    $formTpl['DBTYPE_LBL'] = _("Database Type");
    $formTpl['DBTYPE_DEF'] = _("phpWebSite supports several databases. Choose the type your server currently is running.");

    $formTpl['DBUSER_LBL'] = _("Database User");
    $formTpl['DBUSER_DEF'] = _("This is the user name that phpWebSite will use to access its database.")
      . " <br /><i>" . _("Note: it is a good idea to give each phpWebSite installation its own user.") . "</i>";

    $formTpl['DBPASS_LBL'] = _("Database Password");
    $formTpl['DBPASS_DEF'] = _("Enter the database's user password here.");

    $formTpl['DBHOST_LBL'] = _("Host Specification");
    $formTpl['DBHOST_DEF'] = _("If your database is on the same server as your phpWebSite installation, leave this as &#x22;localhost&#x22;.")
      . "<br />" . _("Otherwise, enter the ip or dns to the database server.");

    $formTpl['DBPORT_LBL'] = _("Host Specification Port");
    $formTpl['DBPORT_DEF'] = _("If your host specification requires access via a specific port, enter it here.");

    $formTpl['DBNAME_LBL'] = _("Database Name");
    $formTpl['DBNAME_DEF'] = _("The database's name into which you are installing phpWebSite.")
      . "<br /><i>" . _("Note: if you have not made this database yet, you should do so before continuing.") . "</i>";

    $formTpl['DBPREFIX_LBL'] = _("Table Prefix");
    $formTpl['DBPREFIX_DEF'] = _("If phpWebSite is sharing a database with another application, we suggest you give the tables a prefix.");


    $form->add("dbtype", "select", $databases);
    $form->setMatch("dbtype", Setup::getConfigSet("dbtype"));

    $form->add("dbuser", "textfield", Setup::getConfigSet("dbuser"));
    $form->setSize("dbuser", 20);

    $form->add("dbpass", "password");
    $form->setSize("dbpass", 20);

    $form->add("dbhost", "textfield", Setup::getConfigSet("dbhost"));
    $form->setSize("dbhost", 20);

    $form->add("dbport", "textfield", Setup::getConfigSet("dbport"));
    $form->setSize("dbport", 6);

    $form->add("dbname", "textfield", Setup::getConfigSet("dbname"));
    $form->setSize("dbname", 20);

    $form->add("dbprefix", "textfield", Setup::getConfigSet("dbprefix"));
    $form->setSize("dbprefix", 20);

    $form->mergeTemplate($formTpl);
    $content[] = Setup::createForm($form, "databaseConfig.tpl");
  }

  function createForm($form, $tplFile){
    $template = $form->getTemplate();
    $tpl = & new PHPWS_Template;
    $tpl->setFile("setup/templates/$tplFile", TRUE);
    $tpl->setData($template);
    return $tpl->get();
  }

  function getSourceDir(){
    $dir = explode(DIRECTORY_SEPARATOR, __FILE__);
    for ($i=0; $i < 3; $i++)
      array_pop($dir);

    return implode(DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;
  }

  function checkDirectories(&$content){
    $error = FALSE;
    $directory[] = Setup::getSourceDir() . "config/";
    $directory[] = Setup::getSourceDir() . "images/";
    $directory[] = Setup::getSourceDir() . "templates/";
    $directory[] = Setup::getSourceDir() . "files/";
    $directory[] = Setup::getSourceDir() . "logs/";

    foreach ($directory as $id=>$check){
      if (!is_writable($check))
	$writableDir[] = $check;
    }
      
    if (isset($writableDir)){
      $content[] = _("The following directories are not writable:");
      $content[] = "<pre>" . implode("<br />", $writableDir) . "</pre>";
      $content[] = _("Please make these changes and return.") . "<br />";
      $content[] = PHPWS_Text::link("help/permissions." . DEFAULT_LANGUAGE . ".txt", _("Permission Help"), NULL, "index");
      return FALSE;
    }
    else return TRUE;
  }

  function show($content, $title=NULL){
    $tpl = & new PHPWS_Template;
    $tpl->setFile("setup/templates/setup.tpl", TRUE);
    if (!isset($title))
      $title = _("phpWebSite 0.9.4 Alpha Setup");

    $setupData['TITLE'] = $title;
    $setupData['MAIN_CONTENT'] = implode("", $content);
    $tpl->setData($setupData);
    return $tpl->get();
  }

  function checkSession(&$content){
    if (!isset($_SESSION['sessionCheck'])){
      $_SESSION['sessionCheck'] = TRUE;

      if (isset($_REQUEST['check'])){
	$content[] = _("There is a problem with your sessions.") . "<br />";
	$content[] = _("phpWebSite depends on sessions to move data between pages.") . "<br />";
	$content[] = PHPWS_Text::link("help/sessions." . DEFAULT_LANGUAGE . ".txt", _("Sessions Help"), NULL, "index");
	return;
      }
      return FALSE;
    }
    return TRUE;
  }

  function welcome(&$content){
    unset($_SESSION['Boost']);
    $step = 1;
    if (Setup::configExists())
      $step = 2;

    $content[] = "<b>Welcome to the phpWebSite 0.9.4 Alpha Installation</b><br />";
    $content[] = ""
      . "<p>The word 'Alpha' should clue you in that this software is by no means "
      . "ready for a production environment. Unless you are a developer, installation "
      . "help will be met with derisive laughter and scorn.</p>";

    $content[] = ""
      . "<p>If however you have questions about its functioning and API, please visit "
      . "us at irc: freenode.net #phpwebsite </p>";

    $content[] = PHPWS_Text::link("index.php", _("Begin Installation"), array("step"=>$step));
    return;
  }

  function createCore(&$content){
    require_once("File.php");
    $content[] = _("Importing core database file.") . "<br />";
    $installSQL = File::readAll("core/boost/install.sql");
    $result = PHPWS_DB::import($installSQL);

    if (is_array($result)){
      foreach ($result as $error)
	PHPWS_Error::log($error);
      $content[] = _("Some errors occurred while creating the core database tables.") . "<br />";
      $content[] = _("Please check your error log file.") . "<br />";
      return;
    }

    if ($result == TRUE){
      $content[] = _("Core installation successful.") . "<br /><br />";
      $content[] = PHPWS_Text::link("index.php?step=3", _("Continue to Module Installation"));
    }
  }

  function installModules(&$content){
    $modules = PHPWS_Core::coreModList();

    if (!isset($_SESSION['Boost'])){
      $_SESSION['Boost'] = new PHPWS_Boost;
      $_SESSION['Boost']->loadModules($modules);
    }

    $content[] = $_SESSION['Boost']->install();
    if ($_SESSION['Boost']->isFinished())
      return TRUE;
    else
      return FALSE;
  }

  function finish(&$content){
    $content[] = "<hr />";
    $content[] = _("Installation of phpWebSite is complete.") . "<br />";
    $content[] = _("If you experienced any error messages, check your error.log file.") . "<br />";
    $content[] = "<a href=\"../index.php\">" . _("Go to my new website!") . "</a>" . "<br />";

  }

}

?>