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

        $this->modules[$module->title] = $module;
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

    function getRegisteredModules($module)
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('registered.module', $module->title);
        $db->addWhere('title', 'registered.registered');
        return $db->getObjects('PHPWS_Module');
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
        if (!isset($this->status[$title])) {
            return NULL;
        }

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

    function install($inBoost=TRUE, $inBranch=FALSE, $home_dir=NULL)
    {
        $continue = FALSE;
        $content = array();
        if (!$this->isModules()) {
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'install');
        }

        $last_mod = end($this->modules);

        foreach ($this->modules as $title => $mod){
            $title = trim($title);
            if ($this->getStatus($title) == BOOST_DONE) {
                continue;
            }

            if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW) {
                $this->setCurrent($title);
                $this->setStatus($title, BOOST_START);
            }

            $content[] = _('Installing') . ' - ' . $mod->getProperName();

            if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()) {
                $content[] = _('Importing SQL install file.');
                $db = & new PHPWS_DB;
                $result = $db->importFile($mod->getDirectory() . 'boost/install.sql');

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->addLog($title, _('Database import failed.'));
                    $content[] = _('An import error occurred.');
                    $content[] = _('Check your logs for more information.');
                    return implode('<br />', $content);
                } else {
                    $content[] = _('Import successful.');
                }
            }

            $result = $this->onInstall($mod, $content);

            if ($result === TRUE) {
                $this->setStatus($title, BOOST_DONE);
                $this->createDirectories($mod, $content, $home_dir);
                $this->registerModule($mod, $content);
                $continue = TRUE;
                break;
            } elseif ($result === -1) {
                // No installation file (install.php) was found.
                $this->setStatus($title, BOOST_DONE);
                $this->createDirectories($mod, $content, $home_dir);
                $this->registerModule($mod, $content);
                $continue = TRUE;
                break;
            }
            elseif ($result === FALSE) {
                $this->setStatus($title, BOOST_PENDING);
                $continue = FALSE;
                break;
            }
            elseif (PEAR::isError($result)) {
                $content[] = _('There was a problem in the installation file:');
                $content[] = '<b>' . $result->getMessage() .'</b>';
                $content[] = '<br />';
                PHPWS_Error::log($result);
                $continue = TRUE;
                break;
            }

        }

        if ($continue && $last_mod->title != $title) {
            // inBoost checks to see if we are in the Setup program

            if ($inBranch) {
                $branchvars['command'] = 'core_module_installation';
                $branchvars['branch_id'] = $_REQUEST['branch_id'];
                $content[] = PHPWS_Text::secureLink(_('Continue installation...'), 'branch', $branchvars);
            } elseif ($inBoost == FALSE) {
                $content[] = '<a href="index.php?step=3">' . _('Continue installation...') . '</a>';
            } else {
                $content[] = _('Installation complete!');
            }
        } elseif($continue) {
            $content[] = _('Installation complete!');
        }
    
        return implode('<br />', $content);    
    }


    function onInstall($mod, &$installCnt)
    {
        $onInstallFile = $mod->getDirectory() . 'boost/install.php';
        $installFunction = $mod->title . '_install';
        if (!is_file($onInstallFile)) {
            $this->addLog($mod->title, _('No installation file found.'));
            return -1;
        }

        if ($this->getStatus($mod->title) == BOOST_START) {
            $this->setStatus($mod->title, BOOST_PENDING);
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
        $updateFunction  = $mod->title . '_update';
        $currentVersion  = $mod->getVersion();
        if (!is_file($onUpdateFile)) {
            $this->addLog($mod->title, _('No update file found.'));
            return -1;
        }

        if ($this->getStatus($mod->title) == BOOST_START) {
            $this->setStatus($mod->title, BOOST_PENDING);
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
        PHPWS_Cache::clearCache();
        $content = array();
        if (!$this->isModules()) {
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'install');
        }

        foreach ($this->modules as $title => $mod){
            unset($GLOBALS['Modules'][$title]);
            $title = trim($title);
            if ($this->getStatus($title) == BOOST_DONE) {
                continue;
            }

            if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW) {
                $this->setCurrent($title);
                $this->setStatus($title, BOOST_START);
            }

            $content[] = '<b>' . _('Uninstalling') . ' - ' . $mod->getProperName() .'</b>';

            if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()) {
                $uninstall_file = $mod->getDirectory() . 'boost/uninstall.sql';
                if (!is_file($uninstall_file)) {
                    $content[] = _('Uninstall SQL not found.');
                } else {
                    $content[] = _('Importing SQL uninstall file.');
                    $result = PHPWS_Boost::importSQL($uninstall_file);
                    
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        
                        $content[] = _('An import error occurred.');
                        $content[] = _('Check your logs for more information.');
                        return implode('<br />', $content);
                    } else {
                        $content[] = _('Import successful.');
                    }
                }
            }

            $result = (bool)$this->onUninstall($mod, $content);

            if ($result === TRUE) {
                $this->setStatus($title, BOOST_DONE);
                $this->removeDirectories($mod, $content);
                $this->unregisterModule($mod, $content);
                $this->removeDependencies($mod);
                $this->removeKeys($mod);
                $content[] = '<hr />';
                $content[] = _('Finished uninstalling module!');
                break;
            }
            elseif ($result == -1) {
                $this->setStatus($title, BOOST_DONE);
                $this->removeDirectories($mod, $content);
                $this->unregisterModule($mod, $content);
                $this->removeDependencies($mod);
                $this->removeKeys($mod);
            }
            elseif ($result === FALSE) {
                $this->setStatus($title, BOOST_PENDING);
                break;
            }
            elseif (PEAR::isError($result)) {
                $content[] = _('There was a problem in the installation file:');
                $content[] = '<b>' . $result->getMessage() .'</b>';
                $content[] = '<br />';
                PHPWS_Error::log($result);
            }

        }
        return implode('<br />', $content);    
    }

    function removeDependencies($mod)
    {
        $db = & new PHPWS_DB('dependencies');
        $db->addWhere('source_mod', $mod->title);
        $db->delete();
    }

    function removeKeys($mod)
    {
        $db = & new PHPWS_DB('phpws_key_edit');
        $db->addWhere('key_id', 'phpws_key.id');
        $db->addWhere('phpws_key.module', $mod->title);
        $db->delete();

        $db->setTable('phpws_key_view');
        $db->delete();

        $db->reset();
        $db->setTable('phpws_key');
        $db->addWhere('module', $mod->title);
        return $db->delete();
    }

    function onUninstall($mod, &$uninstallCnt)
    {
        $onUninstallFile = $mod->getDirectory() . 'boost/uninstall.php';
        $uninstallFunction = $mod->title . '_uninstall';
        if (!is_file($onUninstallFile)) {
            $uninstallCnt[] = _('Uninstall file not found.');
            $this->addLog($mod->title, _('No uninstall file found.'));
            return -1;
        }

        if ($this->getStatus($mod->title) == BOOST_START) {
            $this->setStatus($mod->title, BOOST_PENDING);
        }

        include_once($onUninstallFile);

        if (function_exists($uninstallFunction)) {
            $uninstallCnt[] = _('Processing uninstall file.');
            return $uninstallFunction($uninstallCnt);
        } else {
            $this->addLog($mod->title, 
                          sprintf(_('Uninstall function "%s" was not found.'), 
                                  $uninstallFunction));
            return TRUE;
        }
    }

    function update(&$content)
    {
        if (!$this->isModules()) {
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'update');
        }
        
        foreach ($this->modules as $title => $mod) {
            if (isset($mod->_error)) {
                if ($mod->_error->code == PHPWS_NO_MOD_FOUND) {
                    $content[] = _('Module is not installed.');
                    $result = true;
                    continue;
                }
            }
            $updateMod = & new PHPWS_Module($mod->title);
            if (version_compare($updateMod->getVersion(), $mod->getVersion(), '=')) {
                $content[] =  _('Module does not require updating.');
                $result = FALSE;
                continue;
            }

            $title = trim($title);

            if ($this->getStatus($title) == BOOST_DONE) {
                continue;
            }
      
            if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW) {
                $this->setCurrent($title);
                $this->setStatus($title, BOOST_START);
            }

            $content[] = _('Updating') . ' - ' . $mod->getProperName();
            $result = $this->onUpdate($mod, $content);

            if ($result === TRUE) {
                $this->setStatus($title, BOOST_DONE);
                $newMod = & new PHPWS_Module($mod->title);
                $newMod->save();
                break;
            }
            elseif ($result === -1) {
                $newMod = & new PHPWS_Module($mod->title);
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

        if ( isset($result) && ($result === TRUE || $result == -1) ) {
            $content[] = _('Update complete!');
            return true;
        } else {
            $content[] = _('Update not completed.');
            return false;
        }
    }


    function createDirectories($mod, &$content, $homeDir = NULL, $overwrite=FALSE)
    {
        PHPWS_Core::initCoreClass('File.php');
        if (!isset($homeDir)) {
            $homeDir = getcwd();
        }

        $configSource = $mod->getDirectory() . 'conf/';
        if (is_dir($configSource)) {
            $configDest   = $homeDir . '/config/' . $mod->title . '/';
            if ($overwrite == TRUE || !is_dir($configDest)) {
                $content[] = _('Copying configuration files.');
                $this->addLog($mod->title, sprintf(_("Copying directory %1\$s to %2\$s"), $configSource, $configDest));
                PHPWS_File::recursiveFileCopy($configSource, $configDest);
            }
        }

        $javascriptSource = $mod->getDirectory() . 'javascript/';
        if (is_dir($javascriptSource)) {
            $javascriptDest   = $homeDir . '/javascript/modules/' . $mod->title . '/';
            if ($overwrite == TRUE || !is_dir($javascriptDest)) {
                $content[] = _('Copying javascript directories.');
                $this->addLog($mod->title, sprintf(_("Copying directory %1\$s to %2\$s"), $javascriptSource, $javascriptDest));
                PHPWS_File::recursiveFileCopy($javascriptSource, $javascriptDest);
            }
        }

        $templateSource = $mod->getDirectory() . 'templates/';
        if (is_dir($templateSource)) {
            $templateDest   = $homeDir . '/templates/' . $mod->title . '/';
            if ($overwrite == TRUE || !is_dir($templateDest)) {
                $content[] = _('Copying template files.');
                $this->addLog($mod->title, sprintf(_("Copying directory %1\$s to %2\$s"), $templateSource, $templateDest));
                PHPWS_File::recursiveFileCopy($templateSource, $templateDest);
            }
        }

        if (!is_dir($homeDir . '/images/mod/')) {
            $content[] = _('Creating module image directory.');
            $this->addLog($mod->title, _('Created directory') . ' $homeDir/images/mod/');
            mkdir($homeDir . '/images/mod');
        }

        if ($mod->isFileDir()) {
            $filesDir = $homeDir . '/files/' . $mod->title;
            if (!is_dir($filesDir)){
                $content[] = _('Creating files directory for module.');
                $this->addLog($mod->title, _('Created directory') . ' ' . $filesDir);
                mkdir($filesDir);
            }
        }

        if ($mod->isImageDir()) {
            $imageDir = $homeDir . '/images/' . $mod->title;
            if (!is_dir($imageDir)){
                $this->addLog($mod->title, _('Created directory') . ' ' . $imageDir);
                $content[] = _('Creating image directory for module.');
                mkdir($imageDir);
            }
        }

        $modSource = $mod->getDirectory() . 'img/';
        if (is_dir($modSource)){
            $modImage = $homeDir . '/images/mod/' . $mod->title . '/';
            $this->addLog($mod->title, sprintf(_("Copying directory %1\$s to %2\$s"), $modSource, $modImage));

            $content[] = _('Copying source image directory for module.');
     
            $result = PHPWS_File::recursiveFileCopy($modSource, $modImage);
            if ($result) {
                $content[] = _('Source image directory copied successfully.');
            } else {
                $content[] = _('Source image directory failed to copy.');
            }
        }
    }

    function removeDirectories($mod, &$content, $homeDir = NULL)
    {
        PHPWS_Core::initCoreClass('File.php');
        if (!isset($homeDir))
            $homeDir = getcwd();

        $configDir = $homeDir. '/config/' . $mod->title . '/';
        if (is_dir($configDir)) {
            $this->addLog($mod->title, sprintf(_('Removing directory %s'), $configDir));
            if(!PHPWS_File::rmdir($configDir)) {
                $this->addLog($mod->title, sprintf(_('Unable to remove directory %s'), $configDir));
            }
        }

        $javascriptDir = $homeDir. '/javascript/' . $mod->title . '/';
        if (is_dir($javascriptDir)) {
            $this->addLog($mod->title, sprintf(_('Removing directory %s'), $javascriptDir));
            if(!PHPWS_File::rmdir($javascriptDir)) {
                $this->addLog($mod->title, sprintf(_('Unable to remove directory %s'), $javascriptDir));
            }
        }

        $templateDir = $homeDir . '/templates/' . $mod->title . '/';
        if (is_dir($templateDir)) {
            $this->addLog($mod->title, sprintf(_('Removing directory %s'), $templateDir));
            if(!PHPWS_File::rmdir($templateDir)) {
                $this->addLog($mod->title, sprintf(_('Unable to remove directory %s'), $templateDir));
            }
        }

        $imageDir = $homeDir . '/images/' . $mod->title . '/';
        if (is_dir($imageDir)) {
            $this->addLog($mod->title, sprintf(_('Removing directory %s'), $imageDir));
            if(!PHPWS_File::rmdir($imageDir)) {
                $this->addLog($mod->title, sprintf(_('Unable to remove directory %s'), $imageDir));
            }
        }

        $fileDir = $homeDir . '/files/' . $mod->title . '/';
        if (is_dir($fileDir)) {
            $this->addLog($mod->title, sprintf(_('Removing directory %s'), $fileDir));
            if(!PHPWS_File::rmdir($fileDir)) {
                $this->addLog($mod->title, sprintf(_('Unable to remove directory %s'), $fileDir));
            }
        }

        $imageModDir = $homeDir . '/images/mod/' . $mod->title . '/';
        if (is_dir($imageModDir)) {
            $this->addLog($mod->title, sprintf(_('Removing directory %s'), $imageModDir));
            if(!PHPWS_File::rmdir($imageModDir)) {
                $this->addLog($mod->title, sprintf(_('Unable to remove directory %s'), $imageModDir));
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
        $db->addWhere('title', $module->title);
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
        $filename = sprintf('%smod/%s/inc/key.php', PHPWS_SOURCE_DIR, $module->title);
        if (is_file($filename)) {
            $content[] = _('Registered to Key.');
            Key::registerModule($module->title);
        }

        $content[] = '<br />';
        return $result;
    }

    function unregisterModule($module, &$content)
    {
        $content[] = _('Unregistering module from core.');

        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $module->title);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('An error occurred while unregistering.');
            $content[] = _('Check your logs for more information.');
        } else {
            $content[] = _('Unregistering module from Boost was successful.');

            $result = PHPWS_Settings::unregister($module->title);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('Module\'s settings could not be removed. See your error log.');
            } else {
                $content[] = _('Module\'s settings removed successfully.');
            }

            if (Key::unregisterModule($module->title)) {
                $content[] = _('Key unregistration successful.');
            } else {
                $content[] = _('Some key unregistrations were unsuccessful. Check your logs.');
            }

            if ($module->isUnregister()) {
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
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }

    function unsetRegistered($module, $registered)
    {
        $db = & new PHPWS_DB('registered');
        $db->addWhere('registered', $registered);
        $db->addWhere('module', $module);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }


    function isRegistered($module, $registered)
    {
        $db = & new PHPWS_DB('registered');
        $db->addWhere('registered', $registered);
        $db->addWhere('module', $module);
        $result = $db->select('one');
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }

    /**
     * Registers a module ($register_mod) TO another module ($register_to_mod)
     * In other words, the first parameter is going to perform 
     * an action on the second parameter
     */
    function registerModToMod($register_to_mod, $register_mod, &$content)
    {
        $registerFile = $register_to_mod->getDirectory() . 'boost/register.php';
        if (!is_file($registerFile)) {
            return PHPWS_Error::get(BOOST_NO_REGISTER_FILE, 'boost', 'registerModToMod', $registerFile);
        }

        if (PHPWS_Boost::isRegistered($register_to_mod->title, $register_mod->title)) {
            return NULL;
        }

        include_once $registerFile;

        $registerFunc = $register_to_mod->title . '_register';

        if (!function_exists($registerFunc)) {
            return PHPWS_Error::get(BOOST_NO_REGISTER_FUNCTION, 'boost', 'registerModToMod', $registerFile);
        }

        $result = $registerFunc($register_mod->title, $content);    

        if (PEAR::isError($result)) {
            $content[] = sprintf(_('An error occurred while registering the %s module.'), $register_mod->getProperName());
            $content[] = PHPWS_Boost::addLog($register_mod->title, $result->getMessage());
            $content[] = PHPWS_Error::log($result);
        } elseif ($result == TRUE) {
            PHPWS_Boost::setRegistered($register_to_mod->title, $register_mod->title);
            $content[] = sprintf(_("%1\$s successfully registered to %2\$s."), $register_mod->getProperName(TRUE), $register_to_mod->getProperName(TRUE));
        }
        return TRUE;
    }

    function unregisterModToMod($unregister_from_mod, $register_mod, &$content)
    {
        $unregisterFile = $unregister_from_mod->getDirectory() . 'boost/unregister.php';

        if (!is_file($unregisterFile)) {
            return NULL;
        }

        include_once $unregisterFile;

        $unregisterFunc = $unregister_from_mod->title . '_unregister';

        if (!function_exists($unregisterFunc)) {
            return NULL;
        }

        $result = $unregisterFunc($register_mod->title, $content);    

        if (PEAR::isError($result)) {
            $content[] = sprintf(_('An error occurred while unregistering the %s module.'),$register_mod->getProperName());
            PHPWS_Error::log($result);
            PHPWS_Boost::addLog($register_mod->title, $result->getMessage());
        } elseif ($result == TRUE) {
            PHPWS_Boost::unsetRegistered($unregister_from_mod->title, $register_mod->title);
            $content[] = sprintf(_("%1\$s successfully unregistered from %2\$s."), $register_mod->getProperName(TRUE), $unregister_from_mod->getProperName(TRUE));
        }
    }

    /**
     * Registered the installed module to other modules already present
     *
     */
    function registerSelfToOthers($module, &$content)
    {
        $content[] = _('Registering this module to other modules.');
    
        $modules = PHPWS_Boost::getRegMods();

        if (!is_array($modules)) {
            return;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();
            if ($register_mod->isRegister()) {
                $result = $this->registerModToMod($register_mod, $module, $content);
            }
        }
    }

    function unregisterSelfToOthers($module, &$content)
    {
        $content[] = _('Unregistering this module from other modules.');
    
        $modules = PHPWS_Boost::getUnregMods();

        if (!is_array($modules)) {
            return;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();

            if ($register_mod->isUnregister()) {
                $result = $this->unregisterModToMod($register_mod, $module, $content);
            }
        }
    }

    /**
     * Registers other modules to the module currently getting installed.
     */
    function registerOthersToSelf($module, &$content)
    {
        $content[] = _('Registering other modules to this module.');

        $modules = PHPWS_Boost::getInstalledModules();

        if (!is_array($modules)) {
            return;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();
            $result = $this->registerModToMod($module, $register_mod, $content);
        }
    }

    function unregisterOthersToSelf($module, &$content)
    {
        $content[] = _('Unregistering other modules from this module.');

        $modules = PHPWS_Boost::getRegisteredModules($module);

        if (PEAR::isError($modules)) {
            return $modules;
        } elseif (empty($modules) || !is_array($modules)) {
            return TRUE;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();
            $result = $this->unregisterModToMod($module, $register_mod, $content);
        }
    }

    function unregisterAll($module)
    {
        $db = & new PHPWS_DB('registered');
        $db->addWhere('registered', $module->title);
        $db->addWhere('module', $module->title, '=', 'or');
        return $db->delete();
    }

    function importSQL($file)
    {
        require_once 'File.php';

        if (!is_file($file)) {
            return PHPWS_Error::get(BOOST_ERR_NO_INSTALLSQL, 'boost', 'importSQL', 'File: ' . $file);
        }

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

    /**
     * Copy of the setup function of the same name
     * This one also checks the write and read capabilities of
     * the log files.
     */
    function checkDirectories(&$content)
    {
        $errorDir = TRUE;
        $directory[] = 'config/';
        $directory[] = 'images/';
        $directory[] = 'templates/';
        $directory[] = 'files/';
        $directory[] = 'logs/';
        $directory[] = 'javascript/modules/';

        foreach ($directory as $id=>$check){
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
            $content[] = '<a href="setup/help/permissions.' . DEFAULT_LANGUAGE . '.txt">' . _('Permission Help') . '</a><br />';
            $errorDir = FALSE;
        }

        $files = array('boost.log', 'error.log', 'security.log');
        foreach ($files as $log_name) {
            if (is_file('logs/' . $log_name) && (!is_readable('logs/' . $log_name) || !is_writable('logs/' . $log_name))) {
                $content[] = sprintf(_('Your logs/%s file must be readable and writable.'), $log_name);
                $errorDir = FALSE;
            }
        }

        return $errorDir;
    }

    function getHomeDir()
    {
        if (isset($GLOBALS['boost_branch_dir'])) {
            return $GLOBALS['boost_branch_dir'];
        } else {
            return './';
        }
    }

    /**
     * Receives an array of file locations. Updates
     * the local files and backs up the older version.
     */
    function updateFiles($file_array, $module)
    {
        if (!is_array($file_array)) {
            return FALSE;
        }

        $home_dir = PHPWS_Boost::getHomeDir();

        foreach ($file_array as $filename) {
            $filename = preg_replace('/^\/{1}/', '', $filename);
            $aFiles = explode('/', $filename);
            $source_root = array_shift($aFiles);
            $source_filename = implode('/', $aFiles);

            switch ($source_root) {
            case 'templates':
                $local_root = sprintf('%stemplates/%s/', $home_dir, $module);
                break;

            case 'conf':
                $local_root = sprintf('%sconfig/%s/', $home_dir, $module);
                break;

            case 'img':
                $local_root = sprintf('%simages/mod/%s/', $home_dir, $module);
                break;

            case 'javascript':
                $local_root = sprintf('%sjavascript/modules/%s/', $home_dir, $module);
                break;

            default:
                continue;
                break;
            }

            if (!PHPWS_Boost::checkLocalRoot($local_root)) {
                return false;
            }

            $source_file = sprintf('%smod/%s/%s', PHPWS_SOURCE_DIR, $module, $filename);
            $local_file = sprintf('%s%s', $local_root, $source_filename);

            if (!is_file($source_file)) {
                continue;
            }

            if (is_file($local_file)) {
                if (md5_file($local_file) == md5_file($source_file)) {
                    continue;
                }
                if (!PHPWS_Boost::backupFile($local_file)) {
                    return PHPWS_Error::get(BOOST_FAILED_BACKUP, 'boost', 'PHPWS_Boost::updateFiles', $local_file);
                }
            }

            $result = @copy($source_file, $local_file);
            if (!$result) {
                return PHPWS_Error::get(BOOST_FAILED_LOCAL_COPY, 'boost', 'PHPWS_Boost::updateFiles', $local_file);
            }
        }
        return TRUE;
    }

    function checkLocalRoot($local_root)
    {
        if (is_dir($local_root)) {
            if (!is_writable($local_root)) {
                return false;
            } else {
                return true;
            }
        }

        if (@mkdir($local_root)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Backs up a file by removing the extension, padding the word
     * 'backup' then putting the ext back.
     */
    function backupFile($filename)
    {
        $aFile = explode('/', $filename);
        $file_alone = array_pop($aFile);

        $aFile_alone = explode('.', $file_alone);
        $file_ext = array_pop($aFile_alone);
        $new_file_ext = 'backup.' . $file_ext;

        $aFile_alone[] = $new_file_ext;

        $new_filename = implode('/', $aFile) . '/' . implode('.', $aFile_alone);
        return @copy($filename, $new_filename);
    }

    function updateBranches(&$content)
    {
        if (!PHPWS_Core::moduleExists('branch')) {
            return true;
        }

        PHPWS_Core::initModClass('branch', 'Branch_Admin.php');
        $branches = Branch_Admin::getBranches();
        if (empty($branches)) {
            return true;
        }

        $keys = array_keys($this->status);


        foreach ($branches as $branch) {
            // used as the "local" directory in updateFiles
            $GLOBALS['boost_branch_dir'] = $branch->directory;

            $branch->loadDSN();
            PHPWS_DB::loadDB($branch->dsn);

            // create a new boost based on the branch database
            $branch_boost = & new PHPWS_Boost;
            $branch_boost->loadModules($keys, false);

            $content[] = '<hr />';
            $content[] = sprintf(_('Updating branch %s'), $branch->branch_name);
            
            $result = $branch_boost->update($content);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('Unable to update branch.');
            }
        }

        PHPWS_DB::disconnect();
        Branch::getHubDB();
    }

}

?>