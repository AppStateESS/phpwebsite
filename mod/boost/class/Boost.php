<?php

/**
 * Controls the installation, update, and uninstallation
 * of modules in phpwebsite
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initCoreClass('Module.php');
PHPWS_Core::configRequireOnce('boost', 'config.php');

define('BOOST_NEW',      0);
define('BOOST_START',    1);
define('BOOST_PENDING',  2);
define('BOOST_DONE',     3);
define('PRE094_SUCCESS', 4);

class PHPWS_Boost {
    var $modules       = NULL;
    var $status        = NULL;
    var $current       = NULL;
    var $installedMods = NULL;

    function addModule($module)
    {
        if (!is_object($module) || strtolower(get_class($module)) != 'phpws_module') {
            return PHPWS_Error::get(BOOST_ERR_NOT_MODULE, 'boost', 'setModule');
        }

        $this->modules[$module->getTitle()] = $module;
    }

    function loadModules($modules, $file=TRUE)
    {
        foreach ($modules as $title){
            $mod = & new PHPWS_Module(trim($title), $file);
            $this->addModule($mod);
            $this->setStatus($title, BOOST_NEW);
        }
    }

    function isFinished()
    {
        if (in_array(BOOST_NEW, $this->status)
            || in_array(BOOST_START, $this->status)
            || in_array(BOOST_PENDING, $this->status)) {
            return FALSE;
        }

        return TRUE;
    }

    function getInstalledModules()
    {
        $db = & new PHPWS_DB('modules');
        $db->addColumn('title');
        $modules = $db->getObjects('PHPWS_Module');
        return $modules;
    }


    function setStatus($title, $status)
    {
        $this->status[trim($title)] = $status;
    }

    function getStatus($title)
    {
        if (!isset($this->status[$title]))
            return NULL;

        return $this->status[$title];
    }

    function setCurrent($title)
    {
        $this->current = $title;
    }

    function getCurrent()
    {
        return $this->current;
    }

    function isModules()
    {
        return isset($this->modules);
    }

    function install($inBoost=TRUE)
    {
        $content = array();
        if (!$this->isModules())
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'install');

        $last_mod = end($this->modules);

        foreach ($this->modules as $title => $mod){
            $title = trim($title);
            if ($this->getStatus($title) == BOOST_DONE)
                continue;

            if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW){
                $this->setCurrent($title);
                $this->setStatus($title, BOOST_START);
            }

            $content[] = _('Installing') . ' - ' . $mod->getProperName();

            if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()){
                $content[] = _('Importing SQL install file.');
                $db = & new PHPWS_DB;
                $result = $db->importFile($mod->getDirectory() . 'boost/install.sql');

                if (is_array($result)){
                    foreach ($result as $error)
                        PHPWS_Error::log($error);

                    $content[] = _('An import error occurred.');
                    $content[] = _('Check your logs for more information.');
                    return implode('<br />', $content);
                    return;
                } else
                    $content[] = _('Import successful.');
            }

            $result = $this->onInstall($mod, $content);

            if ($result === TRUE){
                $this->setStatus($title, BOOST_DONE);
                $this->createDirectories($mod, $content);
                $this->registerModule($mod, $content);
                $continue = TRUE;
                break;
            }
            elseif ($result === -1){
                // No installation file (install.php) was found.
                $this->setStatus($title, BOOST_DONE);
                $this->createDirectories($mod, $content);
                $this->registerModule($mod, $content);
                $continue = TRUE;
                break;
            }
            elseif ($result === FALSE){
                $this->setStatus($title, BOOST_PENDING);
                $continue = FALSE;
                break;
            }
            elseif (PEAR::isError($result)){
                $content[] = _('There was a problem in the installation file:');
                $content[] = '<b>' . $result->getMessage() .'</b>';
                $content[] = '<br />';
                PHPWS_Error::log($result);
                $continue = TRUE;
                break;
            }

        }

        if ($continue && $last_mod->title != $title){
            // inBoost checks to see if we are in the Setup program
            if ($inBoost == FALSE) {
                $content[] = '<a href="index.php?step=3">' . _('Continue installation...') . '</a>';
            }
            else {
                $content[] = _('Installation complete!');
            }
        }
    
        return implode('<br />', $content);    
    }


    function onInstall($mod, &$installCnt)
    {
        $onInstallFile = $mod->getDirectory() . 'boost/install.php';
        $installFunction = $mod->getTitle() . '_install';
        if (!is_file($onInstallFile)){
            $this->addLog($mod->getTitle(), _('No installation file found.'));
            return -1;
        }

        if ($this->getStatus($mod->getTitle()) == BOOST_START)
            $this->setStatus($mod->getTitle(), BOOST_PENDING);

        /**
         * If module was before 094, install differently
         */
        if ($mod->isPre94()){
            PHPWS_Core::initCoreClass('Crutch.php');
            PHPWS_Crutch::startSessions();
            $content = NULL;
            include_once($onInstallFile);
            $installCnt[] = $content;
            if ($status)
                return TRUE;
            else
                return PHPWS_Error::get(BOOST_FAILED_PRE94_INSTALL, 'boost', 'install');
        }

        include_once($onInstallFile);

        if (function_exists($installFunction)) {
            $installCnt[] = _('Processing installation file.');
            return $installFunction($installCnt);
        }
        else {
            return TRUE;
        }
    }

    function onUpdate($mod, &$updateCnt)
    {
        $onUpdateFile    = $mod->getDirectory() . 'boost/update.php';
        $updateFunction  = $mod->getTitle() . '_update';
        $currentVersion  = $mod->getVersion();
        if (!is_file($onUpdateFile)) {
            $this->addLog($mod->getTitle(), _('No update file found.'));
            return -1;
        }

        if ($this->getStatus($mod->getTitle()) == BOOST_START) {
            $this->setStatus($mod->getTitle(), BOOST_PENDING);
        }

        /**
         * If module was before 094, update differently
         */
        if ($mod->isPre94()){
            PHPWS_Core::initCoreClass('Crutch.php');
            PHPWS_Crutch::startSessions();
            $content = NULL;
            include_once($onUpdateFile);
            $updateCnt[] = $content;
            if ($status) {
                return TRUE;
            } else {
                return PHPWS_Error::get(BOOST_FAILED_PRE94_UPDATE, 'boost', 'update');
            }
        }

        include_once($onUpdateFile);

        if (function_exists($updateFunction)){
            $updateCnt[] = _('Processing update file.');
            return $updateFunction($updateCnt, $currentVersion);
        } else {
            return TRUE;
        }
    }


    function uninstall()
    {
        $content = array();
        if (!$this->isModules())
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'install');

        foreach ($this->modules as $title => $mod){
            $title = trim($title);
            if ($this->getStatus($title) == BOOST_DONE)
                continue;

            if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW){
                $this->setCurrent($title);
                $this->setStatus($title, BOOST_START);
            }

            $content[] = '<b>' . _('Uninstalling') . ' - ' . $mod->getProperName() .'</b>';

            if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()){
                $content[] = _('Importing SQL uninstall file.');
                $result = PHPWS_Boost::importSQL($mod->getDirectory() . 'boost/uninstall.sql');

                if (PEAR::isError($result)){
                    PHPWS_Error::log($result);

                    $content[] = _('An import error occurred.');
                    $content[] = _('Check your logs for more information.');
                    return implode('<br />', $content);
                } else
                    $content[] = _('Import successful.');
            }

            $result = (bool)$this->onUninstall($mod, $content);

            if ($result === TRUE){
                $this->setStatus($title, BOOST_DONE);
                $this->removeDirectories($mod, $content);
                $this->unregisterModule($mod, $content);
                $content[] = '<hr />';
                $content[] = _('Finished uninstalling module!');
                break;
            }
            elseif ($result == -1){
                $this->setStatus($title, BOOST_DONE);
                $this->removeDirectories($mod, $content);
                $this->unregisterModule($mod, $content);
            }
            elseif ($result === FALSE){
                $this->setStatus($title, BOOST_PENDING);
                break;
            }
            elseif (PEAR::isError($result)){
                $content[] = _('There was a problem in the installation file:');
                $content[] = '<b>' . $result->getMessage() .'</b>';
                $content[] = '<br />';
                PHPWS_Error::log($result);
            }

        }
        return implode('<br />', $content);    
    }

    function onUninstall($mod, &$uninstallCnt)
    {
        $onUninstallFile = $mod->getDirectory() . 'boost/uninstall.php';
        $installFunction = $mod->getTitle() . '_uninstall';
        if (!is_file($onUninstallFile)){
            $uninstallCnt[] = _('Uninstall file not found.');
            $this->addLog($mod->getTitle(), _('No uninstall file found.'));
            return -1;
        }

        if ($this->getStatus($mod->getTitle()) == BOOST_START)
            $this->setStatus($mod->getTitle(), BOOST_PENDING);

        /**
         * If module was before 094, install differently
         */
        if ($mod->isPre94()){
            PHPWS_Core::initCoreClass('Crutch.php');
            PHPWS_Crutch::startSessions();
            $content = NULL;
            include_once($onUninstallFile);
            $uninstallCnt[] = $content;
            return $status;
        }

        include_once($onUninstallFile);

        if (function_exists($installFunction)){
            $uninstallCnt[] = _('Processing uninstall file.');
            return $installFunction($uninstallCnt);
        }
        else
            return TRUE;
    }

    function update()
    {
        $content = array();
        if (!$this->isModules()) {
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'install');
        }

        foreach ($this->modules as $title => $mod){
            $updateMod = & new PHPWS_Module($mod->getTitle());
            if (version_compare($updateMod->getVersion(), $mod->getVersion(), '=')) {
                $content[] =  _('Module does not require updating.');
                $result = FALSE;
                continue;
            }

            $title = trim($title);
            if ($this->getStatus($title) == BOOST_DONE) {
                continue;
            }
      
            if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW){
                $this->setCurrent($title);
                $this->setStatus($title, BOOST_START);
            }

            $content[] = _('Updating') . ' - ' . $mod->getProperName();
    
            $result = $this->onUpdate($mod, $content);

            if ($result === TRUE) {
                $this->setStatus($title, BOOST_DONE);
                $newMod = & new PHPWS_Module($mod->getTitle());
                $newMod->save();
                break;
            }
            elseif ($result === -1) {
                $newMod = & new PHPWS_Module($mod->getTitle());
                $newMod->save();
                $this->setStatus($title, BOOST_DONE);
            }
            elseif ($result === FALSE) {
                $this->setStatus($title, BOOST_PENDING);
                break;
            }
            elseif (PEAR::isError($result)) {
                $content[] = _('There was a problem in the update file:');
                $content[] = $result->getMessage();
                $content[] = '<br />';
                PHPWS_Error::log($result);
            }
        }
  
        if ($result === TRUE || $result == -1){
            $content[] = _('Update complete!');
        } else {
            $content[] = _('Update not completed.');
        }
  
        return implode('<br />', $content);    
    }


    function createDirectories($mod, &$content, $homeDir = NULL, $overwrite=FALSE)
    {
        PHPWS_Core::initCoreClass('File.php');
        if (!isset($homeDir))
            $homeDir = getcwd();

        $configSource = $mod->getDirectory() . 'conf/';
        if (is_dir($configSource)) {
            $configDest   = $homeDir . '/config/' . $mod->getTitle() . '/';
            if ($overwrite == TRUE || !is_dir($configDest)){
                $content[] = _('Copying configuration files.');
                $this->addLog($mod->getTitle(), sprintf(_("Copying directory %1\$s to %2\$s"), $configSource, $configDest));
                PHPWS_File::recursiveFileCopy($configSource, $configDest);
                chdir($homeDir);
            }
        }

        $javascriptSource = $mod->getDirectory() . 'javascript/';
        if (is_dir($javascriptSource)) {
            $javascriptDest   = $homeDir . '/javascript/modules/' . $mod->getTitle() . '/';
            if ($overwrite == TRUE || !is_dir($javascriptDest)){
                $content[] = _('Copying javascript directories.');
                $this->addLog($mod->getTitle(), sprintf(_("Copying directory %1\$s to %2\$s"), $javascriptSource, $javascriptDest));
                PHPWS_File::recursiveFileCopy($javascriptSource, $javascriptDest);
                chdir($homeDir);
            }
        }

        $templateSource = $mod->getDirectory() . 'templates/';
        if (is_dir($templateSource)){
            $templateDest   = $homeDir . '/templates/' . $mod->getTitle() . '/';
            if ($overwrite == TRUE || !is_dir($templateDest)){
                $content[] = _('Copying template files.');
                $this->addLog($mod->getTitle(), sprintf(_("Copying directory %1\$s to %2\$s"), $templateSource, $templateDest));
                PHPWS_File::recursiveFileCopy($templateSource, $templateDest);
                chdir($homeDir);
            }
        }

        if (!is_dir($homeDir . '/images/mod/')){
            $content[] = _('Creating module image directory.');
            $this->addLog($mod->getTitle(), _('Created directory') . ' $homeDir/images/mod/');
            mkdir($homeDir . '/images/mod');
        }

        if ($mod->isFileDir()){
            $filesDir = $homeDir . '/files/' . $mod->getTitle();
            if (!is_dir($filesDir)){
                $content[] = _('Creating files directory for module.');
                $this->addLog($mod->getTitle(), _('Created directory') . ' ' . $filesDir);
                mkdir($filesDir);
            }
        }

        if ($mod->isImageDir()){
            $imageDir = $homeDir . '/images/' . $mod->getTitle();
            if (!is_dir($imageDir)){
                $this->addLog($mod->getTitle(), _('Created directory') . ' ' . $imageDir);
                $content[] = _('Creating image directory for module.');
                mkdir($imageDir);
            }
        }

        $modSource = $mod->getDirectory() . 'img/';
        if (is_dir($modSource)){
            $modImage = $homeDir . '/images/mod/' . $mod->getTitle() . '/';
            $this->addLog($mod->getTitle(), sprintf(_("Copying directory %1\$s to %2\$s"), $modSource, $modImage));

            $content[] = _('Copying source image directory for module.');
     
            $result = PHPWS_File::recursiveFileCopy($modSource, $modImage);
            if ($result) {
                $content[] = _('Source image directory copied successfully.');
            } else {
                $content[] = _('Source image directory failed to copy.');
            }
            chdir($homeDir);
        }
    }

    function removeDirectories($mod, &$content, $homeDir = NULL)
    {
        PHPWS_Core::initCoreClass('File.php');
        if (!isset($homeDir))
            $homeDir = getcwd();

        $configDir = $homeDir. '/config/' . $mod->getTitle() . '/';
        if (is_dir($configDir)) {
            $this->addLog($mod->getTitle(), sprintf(_('Removing directory %s'), $configDir));
            if(!PHPWS_File::rmdir($configDir)) {
                $this->addLog($mod->getTitle(), sprintf(_('Unable to remove directory %s'), $configDir));
            }
        }

        $javascriptDir = $homeDir. '/javascript/' . $mod->getTitle() . '/';
        if (is_dir($javascriptDir)) {
            $this->addLog($mod->getTitle(), sprintf(_('Removing directory %s'), $javascriptDir));
            if(!PHPWS_File::rmdir($javascriptDir)) {
                $this->addLog($mod->getTitle(), sprintf(_('Unable to remove directory %s'), $javascriptDir));
            }
        }

        $templateDir = $homeDir . '/templates/' . $mod->getTitle() . '/';
        if (is_dir($templateDir)) {
            $this->addLog($mod->getTitle(), sprintf(_('Removing directory %s'), $templateDir));
            if(!PHPWS_File::rmdir($templateDir)) {
                $this->addLog($mod->getTitle(), sprintf(_('Unable to remove directory %s'), $templateDir));
            }
        }

        $imageDir = $homeDir . '/images/' . $mod->getTitle() . '/';
        if (is_dir($imageDir)) {
            $this->addLog($mod->getTitle(), sprintf(_('Removing directory %s'), $imageDir));
            if(!PHPWS_File::rmdir($imageDir)) {
                $this->addLog($mod->getTitle(), sprintf(_('Unable to remove directory %s'), $imageDir));
            }
        }

        $fileDir = $homeDir . '/files/' . $mod->getTitle() . '/';
        if (is_dir($fileDir)) {
            $this->addLog($mod->getTitle(), sprintf(_('Removing directory %s'), $fileDir));
            if(!PHPWS_File::rmdir($fileDir)) {
                $this->addLog($mod->getTitle(), sprintf(_('Unable to remove directory %s'), $fileDir));
            }
        }

        $imageModDir = $homeDir . '/images/mod/' . $mod->getTitle() . '/';
        if (is_dir($imageModDir)) {
            $this->addLog($mod->getTitle(), sprintf(_('Removing directory %s'), $imageModDir));
            if(!PHPWS_File::rmdir($imageModDir)) {
                $this->addLog($mod->getTitle(), sprintf(_('Unable to remove directory %s'), $imageModDir));
            }
        }
    
    }

    function registerMyModule($mod_to_register, $mod_to_register_to, &$content)
    {
        $register_mod = & new PHPWS_Module($mod_to_register);
        $register_to_mod = & new PHPWS_Module($mod_to_register_to);
        $result = PHPWS_Boost::unregisterModToMod($register_to_mod, $register_mod, $content);
        $result = PHPWS_Boost::registerModToMod($register_to_mod, $register_mod, $content);
        return $result;
    }


    function registerModule($module, &$content)
    {
        $content[] = _('Registering module to core.');

        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $module->getTitle());
        $db->delete();
        $db->resetWhere();
        if (!$module->getProperName())
            $module->setProperName($module->getProperName(TRUE));

        $result = $module->save();

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            $content[] = _('An error occurred during registration.');
            $content[] = _('Check your logs for more information.');
        } else {
            $content[] = _('Registration successful.');

            if ($module->isRegister()){
                $selfselfResult = $this->registerModToMod($module, $module, $content);
                $otherResult = $this->registerOthersToSelf($module, $content);
            }

            $selfResult = $this->registerSelfToOthers($module, $content);
        }

        $content[] = '<br />';
        return $result;
    }

    function unregisterModule($module, &$content)
    {
        $content[] = _('Unregistering module from core.');

        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $module->getTitle());
        $result = $db->delete();

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            $content[] = _('An error occurred while unregistering.');
            $content[] = _('Check your logs for more information.');
        } else {
            $content[] = _('Unregistering was successful.');

            if ($module->isUnregister()){
                $selfselfResult = $this->unregisterModToMod($module, $module, $content);
                $otherResult = $this->unregisterOthersToSelf($module, $content);
            }

            $selfResult = $this->unregisterSelfToOthers($module, $content);
            $result = $this->unregisterAll($module);
        }
    
        return $result;
    }

    function getRegMods()
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('register', 1);
        return $db->getObjects('PHPWS_Module');
    }

    function getUnregMods()
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('unregister', 1);
        return $db->getObjects('PHPWS_Module');
    }

    function setRegistered($module, $registered)
    {
        $db = & new PHPWS_DB('registered');
        $db->addValue('registered', $registered);
        $db->addValue('module', $module);
        $result = $db->insert();
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }

    function unsetRegistered($module, $registered)
    {
        $db = & new PHPWS_DB('registered');
        $db->addWhere('registered', $registered);
        $db->addWhere('module', $module);
        $result = $db->delete();
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }

    function isRegistered($module, $registered)
    {
        $db = & new PHPWS_DB('registered');
        $db->addWhere('registered', $registered);
        $db->addWhere('module', $module);
        $result = $db->select('one');
        if (PEAR::isError($result))
            return $result;
        else
            return (bool)$result;
    }

    function registerModToMod($register_to_mod, $register_mod, &$content)
    {
        $registerFile = $register_to_mod->getDirectory() . 'boost/register.php';

        if (!is_file($registerFile)) {
            return PHPWS_Error::get(BOOST_NO_REGISTER_FILE, 'boost', 'registerModToMod', $registerFile);
        }

        if (PHPWS_Boost::isRegistered($register_to_mod->getTitle(), $register_mod->getTitle())) {
            return NULL;
        }

        include_once($registerFile);

        $registerFunc = $register_to_mod->getTitle() . '_register';

        if (!function_exists($registerFunc)) {
            return PHPWS_Error::get(BOOST_NO_REGISTER_FUNCTION, 'boost', 'registerModToMod', $registerFile);
        }

        $result = $registerFunc($register_mod->getTitle(), $content);    

        if (PEAR::isError($result)){
            $content[] = sprintf(_('An error occurred while registering the %s module.'), $register_mod->getProperName());
            $content[] = PHPWS_Boost::addLog($register_mod->getTitle(), $result->getMessage());
            $content[] = PHPWS_Error::log($result);
        } elseif ($result == TRUE){
            PHPWS_Boost::setRegistered($register_to_mod->getTitle(), $register_mod->getTitle());
            $content[] = sprintf(_("%1\$s successfully registered to %2\$s."), $register_mod->getProperName(TRUE), $register_to_mod->getProperName(TRUE));
        }
        return TRUE;
    }

    function unregisterModToMod($unregister_from_mod, $register_mod, &$content)
    {
        $unregisterFile = $unregister_from_mod->getDirectory() . 'boost/unregister.php';

        if (!is_file($unregisterFile))
            return NULL;

        include_once($unregisterFile);

        $unregisterFunc = $unregister_from_mod->getTitle() . '_unregister';

        if (!function_exists($unregisterFunc))
            return NULL;

        $result = $unregisterFunc($register_mod->getTitle(), $content);    

        if (PEAR::isError($result)){
            $content[] = sprintf(_('An error occurred while unregistering the %s module.'),$register_mod->getProperName());
            PHPWS_Error::log($result);
            PHPWS_Boost::addLog($register_mod->getTitle(), $result->getMessage());
        } elseif ($result == TRUE){
            PHPWS_Boost::unsetRegistered($unregister_from_mod->getTitle(), $register_mod->getTitle());
            $content[] = sprintf(_("%1\$s successfully unregistered from %2\$s."), $register_mod->getProperName(TRUE), $unregister_from_mod->getProperName(TRUE));
        }
    }


    function registerSelfToOthers($module, &$content)
    {
        $content[] = _('Registering this module to other modules.');
    
        $modules = PHPWS_Boost::getRegMods();

        if (!is_array($modules))
            return;

        foreach ($modules as $register_mod){
            $register_mod->init();
            if ($register_mod->isRegister())
                $result = $this->registerModToMod($register_mod, $module, $content);
        }
    }

    function unregisterSelfToOthers($module, &$content)
    {
        $content[] = _('Unregistering this module from other modules.');
    
        $modules = PHPWS_Boost::getUnregMods();

        if (!is_array($modules))
            return;

        foreach ($modules as $register_mod){
            $register_mod->init();

            if ($register_mod->isUnregister())
                $result = $this->unregisterModToMod($register_mod, $module, $content);
        }
    }


    function registerOthersToSelf($module, &$content)
    {
        $content[] = _('Registering other modules to this module.');

        $modules = PHPWS_Boost::getInstalledModules();

        if (!is_array($modules))
            return;

        foreach ($modules as $register_mod){
            $register_mod->init();
            $result = $this->registerModToMod($module, $register_mod, $content);
        }
    }

    function unregisterOthersToSelf($module, &$content)
    {
        $content[] = _('Unregistering other modules from this module.');

        $modules = PHPWS_Boost::getInstalledModules();

        if (!is_array($modules))
            return;

        foreach ($modules as $register_mod){
            $register_mod->init();
            $result = $this->unregisterModToMod($module, $register_mod, $content);
        }
    }

    function unregisterAll($module)
    {
        $db = & new PHPWS_DB('registered');
        $db->addWhere('registered', $module->getTitle());
        return $db->delete();
    }

    function importSQL($file)
    {
        require_once 'File.php';

        if (!is_file($file))
            return PHPWS_Error::get(BOOST_ERR_NO_INSTALLSQL, 'boost', 'importSQL', 'File: ' . $file);

        $sql = File::readAll($file);
        $db = & new PHPWS_DB;
        $result = $db->import($sql);
        return $result;
    }

    function addLog($module, $message)
    {
        $message = _('Module') . ' - ' . $module . ' : ' . $message;
        PHPWS_Core::log($message, 'boost.log');
    }

    function aboutView($module)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $mod = & new PHPWS_Module($module);
        $file = $mod->getDirectory() . 'boost/about.html';

        if (is_file($file)) {
            include $file;
        } else {
            echo _('The About file is missing for this module.');
        }
        exit();
    }
}

?>