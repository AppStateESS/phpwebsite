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

    function loadModules($modules, $file=true)
    {
        foreach ($modules as $title) {
            $mod = new PHPWS_Module(trim($title), $file);
            $this->addModule($mod);
            $this->setStatus($title, BOOST_NEW);
        }
    }

    function isFinished()
    {
        if (in_array(BOOST_NEW, $this->status)
            || in_array(BOOST_START, $this->status)
            || in_array(BOOST_PENDING, $this->status)) {
            return false;
        }

        return true;
    }

    function currentDone()
    {
        return ($this->status[$this->current] == BOOST_DONE) ? true : false;
    }

    function getRegisteredModules($module)
    {
        $db = new PHPWS_DB('modules');
        $db->addWhere('registered.module', $module->title);
        $db->addWhere('title', 'registered.registered');
        return $db->getObjects('PHPWS_Module');
    }

    function getInstalledModules()
    {
        $db = new PHPWS_DB('modules');
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

    function install($inBoost=true, $inBranch=false, $home_dir=NULL)
    {
        $continue = false;
        $content = array();
        $dir_content = array();

        if ($inBranch && !empty($home_dir)) {
            $GLOBALS['boost_branch_dir'] = $home_dir;
        }

        if (!$this->checkDirectories($dir_content, null, false)) {
            return implode('<br />', $dir_content);
        }

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

            $content[] = dgettext('boost', 'Installing') . ' - ' . $mod->getProperName();

            if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()) {
                $content[] = dgettext('boost', 'Importing SQL install file.');
                $db = new PHPWS_DB;
                $result = $db->importFile($mod->getDirectory() . 'boost/install.sql');

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->addLog($title, dgettext('boost', 'Database import failed.'));
                    $content[] = dgettext('boost', 'An import error occurred.');
                    $content[] = dgettext('boost', 'Check your logs for more information.');
                    return implode('<br />', $content);
                } else {
                    $content[] = dgettext('boost', 'Import successful.');
                }
            }

            $result = $this->onInstall($mod, $content);
            // in case install changes translate directory
            
            if ($result === true) {
                $this->setStatus($title, BOOST_DONE);
                $this->createDirectories($mod, $content, $home_dir);
                $this->registerModule($mod, $content);
                $continue = true;
                break;
            } elseif ($result === -1) {
                // No installation file (install.php) was found.
                $this->setStatus($title, BOOST_DONE);
                $this->createDirectories($mod, $content, $home_dir);
                $this->registerModule($mod, $content);
                $continue = true;
                break;
            }
            elseif ($result === false) {
                $this->setStatus($title, BOOST_PENDING);
                $continue = false;
                break;
            }
            elseif (PEAR::isError($result)) {
                $content[] = dgettext('boost', 'There was a problem in the installation file:');
                $content[] = '<b>' . $result->getMessage() .'</b>';
                $content[] = '<br />';
                PHPWS_Error::log($result);
                $continue = true;
                break;
            }
        }

        if ($continue && $last_mod->title != $title) {
            // inBoost checks to see if we are in the Setup program

            if ($inBranch) {
                $branchvars['command'] = 'core_module_installation';
                $branchvars['branch_id'] = $_REQUEST['branch_id'];
                $content[] = PHPWS_Text::secureLink(dgettext('boost', 'Continue installation...'), 'branch', $branchvars);
            } elseif ($inBoost == false) {
                $content[] = '<a href="index.php?step=3">' . dgettext('boost', 'Continue installation...') . '</a>';
            } else {
                $content[] = dgettext('boost', 'Installation complete!');
            }
        } elseif($continue) {
            $content[] = dgettext('boost', 'Installation complete!');
        }
        
        return implode('<br />', $content);    
    }


    function onInstall($mod, &$installCnt)
    {
        $onInstallFile = $mod->getDirectory() . 'boost/install.php';
        $installFunction = $mod->title . '_install';
        if (!is_file($onInstallFile)) {
            $this->addLog($mod->title, dgettext('boost', 'Installation file not implemented.'));
            return -1;
        }

        if ($this->getStatus($mod->title) == BOOST_START) {
            $this->setStatus($mod->title, BOOST_PENDING);
        }

        include_once($onInstallFile);

        if (function_exists($installFunction)) {
            $installCnt[] = dgettext('boost', 'Processing installation file.');
            return $installFunction($installCnt);
        }
        else {
            return true;
        }
    }

    function onUpdate($mod, &$updateCnt)
    {
        $onUpdateFile    = $mod->getDirectory() . 'boost/update.php';
        $updateFunction  = $mod->title . '_update';
        $currentVersion  = $mod->getVersion();
        if (!is_file($onUpdateFile)) {
            $this->addLog($mod->title, dgettext('boost', 'No update file found.'));
            return -1;
        }

        if ($this->getStatus($mod->title) == BOOST_START) {
            $this->setStatus($mod->title, BOOST_PENDING);
        }

        include_once($onUpdateFile);

        if (function_exists($updateFunction)){
            $updateCnt[] = dgettext('boost', 'Processing update file.');
            return $updateFunction($updateCnt, $currentVersion);
        } else {
            return true;
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

            $content[] = '<b>' . dgettext('boost', 'Uninstalling') . ' - ' . $mod->getProperName() .'</b>';

            if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()) {
                $uninstall_file = $mod->getDirectory() . 'boost/uninstall.sql';
                if (!is_file($uninstall_file)) {
                    $content[] = dgettext('boost', 'Uninstall SQL not found.');
                } else {
                    $content[] = dgettext('boost', 'Importing SQL uninstall file.');
                    $result = PHPWS_Boost::importSQL($uninstall_file);
                    
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        
                        $content[] = dgettext('boost', 'An import error occurred.');
                        $content[] = dgettext('boost', 'Check your logs for more information.');
                        return implode('<br />', $content);
                    } else {
                        $content[] = dgettext('boost', 'Import successful.');
                    }
                }
            }

            $result = (bool)$this->onUninstall($mod, $content);

            // ensure translate path
            
            if ($result === true) {
                $this->setStatus($title, BOOST_DONE);
                $this->removeDirectories($mod, $content);
                $this->unregisterModule($mod, $content);
                $this->removeDependencies($mod);
                $this->removeKeys($mod);
                $content[] = '<hr />';
                $content[] = dgettext('boost', 'Finished uninstalling module!');
                break;
            }
            elseif ($result == -1) {
                $this->setStatus($title, BOOST_DONE);
                $this->removeDirectories($mod, $content);
                $this->unregisterModule($mod, $content);
                $this->removeDependencies($mod);
                $this->removeKeys($mod);
            }
            elseif ($result === false) {
                $this->setStatus($title, BOOST_PENDING);
                break;
            }
            elseif (PEAR::isError($result)) {
                $content[] = dgettext('boost', 'There was a problem in the installation file:');
                $content[] = '<b>' . $result->getMessage() .'</b>';
                $content[] = '<br />';
                PHPWS_Error::log($result);
            }

        }
        return implode('<br />', $content);    
    }

    function removeDependencies($mod)
    {
        $db = new PHPWS_DB('dependencies');
        $db->addWhere('source_mod', $mod->title);
        $db->delete();
    }

    function removeKeys($mod)
    {
        $db = new PHPWS_DB('phpws_key_edit');
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
            $uninstallCnt[] = dgettext('boost', 'Uninstall file not found.');
            $this->addLog($mod->title, dgettext('boost', 'No uninstall file found.'));
            return -1;
        }

        if ($this->getStatus($mod->title) == BOOST_START) {
            $this->setStatus($mod->title, BOOST_PENDING);
        }

        include_once($onUninstallFile);

        if (function_exists($uninstallFunction)) {
            $uninstallCnt[] = dgettext('boost', 'Processing uninstall file.');
            return $uninstallFunction($uninstallCnt);
        } else {
            $this->addLog($mod->title, 
                          sprintf(dgettext('boost', 'Uninstall function "%s" was not found.'), 
                                  $uninstallFunction));
            return true;
        }
    }

    function update(&$content)
    {
        if (!$this->isModules()) {
            return PHPWS_Error::get(BOOST_NO_MODULES_SET, 'boost', 'update');
        }

        if (!$this->checkDirectories($content, null, false)) {
            return false;
        }
        
        foreach ($this->modules as $title => $mod) {
            if (isset($mod->_error)) {
                if ($mod->_error->code == PHPWS_NO_MOD_FOUND) {
                    $content[] = dgettext('boost', 'Module is not installed.');
                    $result = true;
                    continue;
                }
            }
            $updateMod = new PHPWS_Module($mod->title);
            if (version_compare($updateMod->getVersion(), $mod->getVersion(), '=')) {
                $content[] =  dgettext('boost', 'Module does not require updating.');
                $result = false;
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

            $content[] = dgettext('boost', 'Updating') . ' - ' . $mod->getProperName();
            $result = $this->onUpdate($mod, $content);

            // assure boost translation path
            

            if ($result === true) {
                $this->setStatus($title, BOOST_DONE);
                $newMod = new PHPWS_Module($mod->title);
                $newMod->save();
                break;
            }
            elseif ($result === -1) {
                $newMod = new PHPWS_Module($mod->title);
                $newMod->save();
                $this->setStatus($title, BOOST_DONE);
            }
            elseif ($result === false) {
                $this->setStatus($title, BOOST_PENDING);
                break;
            }
            elseif (PEAR::isError($result)) {
                $content[] = dgettext('boost', 'There was a problem in the update file:');
                $content[] = $result->getMessage();
                $content[] = '<br />';
                PHPWS_Error::log($result);
            }
        }

        if ( isset($result) && ($result === true || $result == -1) ) {
            $content[] = dgettext('boost', 'Update complete!');
            return true;
        } else {
            $content[] = dgettext('boost', 'Update not completed.');
            return false;
        }
    }


    function createDirectories($mod, &$content, $homeDir = NULL, $overwrite=false)
    {
        PHPWS_Core::initCoreClass('File.php');
        if (!isset($homeDir)) {
            $homeDir = $this->getHomeDir();
        }

        $configSource = $mod->getDirectory() . 'conf/';
        if (is_dir($configSource)) {
            $configDest   = $homeDir . 'config/' . $mod->title . '/';
            if ($overwrite == true || !is_dir($configDest)) {
                $content[] = dgettext('boost', 'Copying configuration files.');
                $this->addLog($mod->title, sprintf(dgettext('boost', "Copying directory %1\$s to %2\$s"), $configSource, $configDest));
                PHPWS_File::recursiveFileCopy($configSource, $configDest);
            }
        }

        $javascriptSource = $mod->getDirectory() . 'javascript/';
        if (is_dir($javascriptSource)) {
            $javascriptDest   = $homeDir . 'javascript/modules/' . $mod->title . '/';
            if ($overwrite == true || !is_dir($javascriptDest)) {
                $content[] = dgettext('boost', 'Copying javascript directories.');
                $this->addLog($mod->title, sprintf(dgettext('boost', "Copying directory %1\$s to %2\$s"), $javascriptSource, $javascriptDest));
                PHPWS_File::recursiveFileCopy($javascriptSource, $javascriptDest);
            }
        }

        $templateSource = $mod->getDirectory() . 'templates/';
        if (is_dir($templateSource)) {
            $templateDest   = $homeDir . 'templates/' . $mod->title . '/';
            if ($overwrite == true || !is_dir($templateDest)) {
                $content[] = dgettext('boost', 'Copying template files.');
                $this->addLog($mod->title, sprintf(dgettext('boost', "Copying directory %1\$s to %2\$s"), $templateSource, $templateDest));
                PHPWS_File::recursiveFileCopy($templateSource, $templateDest);
            }
        }

        if (!is_dir($homeDir . 'images/mod/')) {
            $content[] = dgettext('boost', 'Creating module image directory.');
            $this->addLog($mod->title, dgettext('boost', 'Created directory') . ' ' . $homeDir . 'images/mod/');
            mkdir($homeDir . 'images/mod');
        }

        if ($mod->isFileDir()) {
            $filesDir = $homeDir . 'files/' . $mod->title;
            if (!is_dir($filesDir)){
                $content[] = dgettext('boost', 'Creating files directory for module.');
                $this->addLog($mod->title, dgettext('boost', 'Created directory') . ' ' . $filesDir);
                mkdir($filesDir);
            }
        }

        if ($mod->isImageDir()) {
            $imageDir = $homeDir . 'images/' . $mod->title;
            if (!is_dir($imageDir)){
                $this->addLog($mod->title, dgettext('boost', 'Created directory') . ' ' . $imageDir);
                $content[] = dgettext('boost', 'Creating image directory for module.');
                mkdir($imageDir);
            }
        }

        $modSource = $mod->getDirectory() . 'img/';
        if (is_dir($modSource)){
            $modImage = $homeDir . 'images/mod/' . $mod->title . '/';
            $this->addLog($mod->title, sprintf(dgettext('boost', "Copying directory %1\$s to %2\$s"), $modSource, $modImage));

            $content[] = dgettext('boost', 'Copying source image directory for module.');
     
            $result = PHPWS_File::recursiveFileCopy($modSource, $modImage);
            if ($result) {
                $content[] = dgettext('boost', 'Source image directory copied successfully.');
            } else {
                $content[] = dgettext('boost', 'Source image directory failed to copy.');
            }
        }
    }

    function removeDirectories($mod, &$content, $homeDir = NULL)
    {
        PHPWS_Core::initCoreClass('File.php');
        if (!isset($homeDir)) {
            $this->getHomeDir();
        }

        $configDir = $homeDir. 'config/' . $mod->title . '/';
        if (is_dir($configDir)) {
            $content[] = sprintf(dgettext('boost', 'Removing directory %s'), $configDir);
            $this->addLog($mod->title, sprintf(dgettext('boost', 'Removing directory %s'), $configDir));
            if(!PHPWS_File::rmdir($configDir)) {
                $content[] = dgettext('boost', 'Failure to remove directory.');
                $this->addLog($mod->title, sprintf(dgettext('boost', 'Unable to remove directory %s'), $configDir));
            }
        }

        $javascriptDir = $homeDir. 'javascript/modules/' . $mod->title . '/';
        if (is_dir($javascriptDir)) {
            $content[] = sprintf(dgettext('boost', 'Removing directory %s'), $javascriptDir);
            $this->addLog($mod->title, sprintf(dgettext('boost', 'Removing directory %s'), $javascriptDir));
            if(!PHPWS_File::rmdir($javascriptDir)) {
                $content[] = dgettext('boost', 'Failure to remove directory.');
                $this->addLog($mod->title, sprintf(dgettext('boost', 'Unable to remove directory %s'), $javascriptDir));
            }
        }

        $templateDir = $homeDir . 'templates/' . $mod->title . '/';
        if (is_dir($templateDir)) {
            $content[] = sprintf(dgettext('boost', 'Removing directory %s'), $templateDir);
            $this->addLog($mod->title, sprintf(dgettext('boost', 'Removing directory %s'), $templateDir));
            if(!PHPWS_File::rmdir($templateDir)) {
                $content[] = dgettext('boost', 'Failure to remove directory.');
                $this->addLog($mod->title, sprintf(dgettext('boost', 'Unable to remove directory %s'), $templateDir));
            }
        }

        $imageDir = $homeDir . 'images/' . $mod->title . '/';
        if ($mod->isImageDir() && is_dir($imageDir)) {
            $content[] = sprintf(dgettext('boost', 'Removing directory %s'), $imageDir);
            $this->addLog($mod->title, sprintf(dgettext('boost', 'Removing directory %s'), $imageDir));
            if(!PHPWS_File::rmdir($imageDir)) {
                $content[] = dgettext('boost', 'Failure to remove directory.');
                $this->addLog($mod->title, sprintf(dgettext('boost', 'Unable to remove directory %s'), $imageDir));
            }
        }

        $fileDir = $homeDir . 'files/' . $mod->title . '/';
        if ($mod->isFileDir() && is_dir($fileDir)) {
            $content[] = sprintf(dgettext('boost', 'Removing directory %s'), $fileDir);
            $this->addLog($mod->title, sprintf(dgettext('boost', 'Removing directory %s'), $fileDir));
            if(!PHPWS_File::rmdir($fileDir)) {
                $content[] = dgettext('boost', 'Failure to remove directory.');
                $this->addLog($mod->title, sprintf(dgettext('boost', 'Unable to remove directory %s'), $fileDir));
            }
        }

        $imageModDir = $homeDir . 'images/mod/' . $mod->title . '/';
        if (is_dir($imageModDir)) {
            $content[] = sprintf(dgettext('boost', 'Removing directory %s'), $imageModDir);
            $this->addLog($mod->title, sprintf(dgettext('boost', 'Removing directory %s'), $imageModDir));
            if(!PHPWS_File::rmdir($imageModDir)) {
                $content[] = dgettext('boost', 'Failure to remove directory.');
                $this->addLog($mod->title, sprintf(dgettext('boost', 'Unable to remove directory %s'), $imageModDir));
            }
        }
    
    }

    function registerMyModule($mod_to_register, $mod_to_register_to, &$content)
    {
        $register_mod = new PHPWS_Module($mod_to_register);
        $register_to_mod = new PHPWS_Module($mod_to_register_to);
        $result = PHPWS_Boost::unregisterModToMod($register_to_mod, $register_mod, $content);
        $result = PHPWS_Boost::registerModToMod($register_to_mod, $register_mod, $content);
        return $result;
    }


    function registerModule($module, &$content)
    {
        $content[] = dgettext('boost', 'Registering module to core.');

        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $module->title);
        $db->delete();
        $db->resetWhere();
        if (!$module->getProperName()) {
            $module->setProperName($module->getProperName(true));
        }

        $result = $module->save();

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            $content[] = dgettext('boost', 'An error occurred during registration.');
            $content[] = dgettext('boost', 'Check your logs for more information.');
        } else {
            $content[] = dgettext('boost', 'Registration successful.');

            if ($module->isRegister()){
                $selfselfResult = $this->registerModToMod($module, $module, $content);
                $otherResult = $this->registerOthersToSelf($module, $content);
            }

            $selfResult = $this->registerSelfToOthers($module, $content);
        }
        $filename = sprintf('%smod/%s/inc/key.php', PHPWS_SOURCE_DIR, $module->title);
        if (is_file($filename)) {
            $content[] = dgettext('boost', 'Registered to Key.');
            Key::registerModule($module->title);
        }

        $content[] = '<br />';
        return $result;
    }

    function unregisterModule($module, &$content)
    {
        $content[] = dgettext('boost', 'Unregistering module from core.');

        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $module->title);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = dgettext('boost', 'An error occurred while unregistering.');
            $content[] = dgettext('boost', 'Check your logs for more information.');
        } else {
            $content[] = dgettext('boost', 'Unregistering module from Boost was successful.');

            $result = PHPWS_Settings::unregister($module->title);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = dgettext('boost', 'Module\'s settings could not be removed. See your error log.');
            } else {
                $content[] = dgettext('boost', 'Module\'s settings removed successfully.');
            }

            if (Key::unregisterModule($module->title)) {
                $content[] = dgettext('boost', 'Key unregistration successful.');
            } else {
                $content[] = dgettext('boost', 'Some key unregistrations were unsuccessful. Check your logs.');
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
        $db = new PHPWS_DB('modules');
        $db->addWhere('register', 1);
        return $db->getObjects('PHPWS_Module');
    }

    function getUnregMods()
    {
        $db = new PHPWS_DB('modules');
        $db->addWhere('unregister', 1);
        return $db->getObjects('PHPWS_Module');
    }

    function setRegistered($module, $registered)
    {
        $db = new PHPWS_DB('registered');
        $db->addValue('registered_to', $registered);
        $db->addValue('module', $module);
        $result = $db->insert();
        if (PHPWS_Error::logIfError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }

    function unsetRegistered($module, $registered)
    {
        $db = new PHPWS_DB('registered');
        $db->addWhere('registered_to', $registered);
        $db->addWhere('module', $module);
        $result = $db->delete();

        if (PHPWS_Error::logIfError($result)) {
            return $result;
        } else {
            return (bool)$result;
        }
    }


    function isRegistered($module, $registered)
    {
        $db = new PHPWS_DB('registered');
        $db->addWhere('registered_to', $registered);
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
            $content[] = sprintf(dgettext('boost', 'An error occurred while registering the %s module.'), $register_mod->getProperName());
            $content[] = PHPWS_Boost::addLog($register_mod->title, $result->getMessage());
            $content[] = PHPWS_Error::log($result);
        } elseif ($result == true) {
            PHPWS_Boost::setRegistered($register_to_mod->title, $register_mod->title);
            $content[] = sprintf(dgettext('boost', "%1\$s successfully registered to %2\$s."), $register_mod->getProperName(true), $register_to_mod->getProperName(true));
        }
        return true;
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
            $content[] = sprintf(dgettext('boost', 'An error occurred while unregistering the %s module.'),$register_mod->getProperName());
            PHPWS_Error::log($result);
            PHPWS_Boost::addLog($register_mod->title, $result->getMessage());
        } elseif ($result == true) {
            PHPWS_Boost::unsetRegistered($unregister_from_mod->title, $register_mod->title);
            $content[] = sprintf(dgettext('boost', "%1\$s successfully unregistered from %2\$s."), $register_mod->getProperName(true), $unregister_from_mod->getProperName(true));
        }
    }

    /**
     * Registered the installed module to other modules already present
     *
     */
    function registerSelfToOthers($module, &$content)
    {
        $content[] = dgettext('boost', 'Registering this module to other modules.');
    
        $modules = PHPWS_Boost::getRegMods();

        if (!is_array($modules)) {
            return;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();
            if ($register_mod->isRegister()) {
                PHPWS_Error::logIfError($this->registerModToMod($register_mod, $module, $content));
            }
        }
    }

    function unregisterSelfToOthers($module, &$content)
    {
        $content[] = dgettext('boost', 'Unregistering this module from other modules.');
    
        $modules = PHPWS_Boost::getUnregMods();

        if (!is_array($modules)) {
            return;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();

            if ($register_mod->isUnregister()) {
                PHPWS_Error::logIfError($this->unregisterModToMod($register_mod, $module, $content));
            }
        }
    }

    /**
     * Registers other modules to the module currently getting installed.
     */
    function registerOthersToSelf($module, &$content)
    {
        $content[] = dgettext('boost', 'Registering other modules to this module.');

        $modules = PHPWS_Boost::getInstalledModules();
        if (!is_array($modules)) {
            return;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();
            PHPWS_Error::logIfError($this->registerModToMod($module, $register_mod, $content));
        }
    }

    function unregisterOthersToSelf($module, &$content)
    {
        $content[] = dgettext('boost', 'Unregistering other modules from this module.');

        $modules = PHPWS_Boost::getRegisteredModules($module);

        if (PEAR::isError($modules)) {
            return $modules;
        } elseif (empty($modules) || !is_array($modules)) {
            return true;
        }

        foreach ($modules as $register_mod){
            $register_mod->init();
            PHPWS_Error::logIfError($this->unregisterModToMod($module, $register_mod, $content));
        }
    }

    function unregisterAll($module)
    {
        $db = new PHPWS_DB('registered');
        $db->addWhere('registered_to', $module->title);
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
        $db = new PHPWS_DB;
        $result = $db->import($sql);
        return $result;
    }

    function addLog($module, $message)
    {
        $message = dgettext('boost', 'Module') . ' - ' . $module . ' : ' . $message;
        PHPWS_Core::log($message, 'boost.log');
    }

    function aboutView($module)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $mod = new PHPWS_Module($module);
        $file = $mod->getDirectory() . 'boost/about.html';

        if (is_file($file)) {
            include $file;
        } else {
            echo dgettext('boost', 'The About file is missing for this module.');
        }
        exit();
    }

    /**
     * Copy of the setup function of the same name
     * This one also checks the write and read capabilities of
     * the log files.
     */
    function checkDirectories(&$content, $home_dir=null, $check_branch=true)
    {
        $errorDir = true;
        if (empty($home_dir)) {
            $home_dir = PHPWS_Boost::getHomeDir();
        }

        $directory[] = $home_dir . 'config/';
        $directory[] = $home_dir . 'images/';
        $directory[] = $home_dir . 'templates/';
        $directory[] = $home_dir . 'files/';
        $directory[] = $home_dir . 'logs/';
        $directory[] = $home_dir . 'javascript/';
        $directory[] = $home_dir . 'javascript/modules/';

        foreach ($directory as $id=>$check){
            if (!is_dir($check)) {
                $dirExist[] = $check;
            } elseif (!is_writable($check)) {
                $writableDir[] = $check;
            }
        }

        if (isset($dirExist)) {
            $content[] = dgettext('boost', 'The following directories need to be created:');
            $content[] = implode("\n", $dirExist);
            $errorDir = false;
        }

        if (isset($writableDir)) {
            $content[] = dgettext('boost', 'The following directories are not writable:');
            $content[] = implode(chr(10), $writableDir);
            $errorDir = false;
        }

        $files = array('boost.log', 'error.log');
        foreach ($files as $log_name) {
            if (is_file('logs/' . $log_name) && (!is_readable('logs/' . $log_name) || !is_writable('logs/' . $log_name))) {
                $content[] = sprintf(dgettext('boost', 'Your logs/%s file must be readable and writable.'), $log_name);
                $errorDir = false;
            }
        }

        if (!isset($GLOBALS['Boost_Ready'])) {
            $GLOBALS['Boost_Ready'] = $errorDir;
        }
        
        if (!$errorDir) {
            $GLOBALS['Boost_Current_Directory'] = false;
        }
        if ($check_branch && !PHPWS_Core::isBranch() && PHPWS_Core::moduleExists('branch')) {
            $db = new PHPWS_DB('branch_sites');
            $db->addColumn('branch_name');
            $db->addColumn('directory');
            $result = $db->select();
            if (!empty($result)) {
                if (PHPWS_Error::logIfError($result)) {
                    $content[] = dgettext('boost', 'An error occurred when tryingt to access your branch site listing.');
                    $content[] = dgettext('boost', 'Branches could not be checked.');
                    return $errorDir;
                }
                foreach ($result as $branch) {
                    $content[] = '';
                    $content[] = sprintf(dgettext('boost', 'Checking branch "%s"'), $branch['branch_name']);
                    if (!PHPWS_Boost::checkDirectories($content, $branch['directory'], false)) {
                        $errorDir = false;
                    } else {
                        $content[] = dgettext('boost', 'Branch directories are ready.');
                    }
                }
            }
        }

        return $errorDir;
    }

    function getHomeDir()
    {
        if (isset($GLOBALS['boost_branch_dir'])) {
            return $GLOBALS['boost_branch_dir'];
        } else {
            return getcwd() . '/';
        }
    }

    /**
     * Receives an array of file locations. Updates
     * the local files and backs up the older version.
     */
    function updateFiles($file_array, $module, $return_failures=false)
    {
        if (!is_array($file_array)) {
            return false;
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
                if ($module == 'core') {
                    $local_root = sprintf('%simages/core/', $home_dir);
                } else {
                    $local_root = sprintf('%simages/mod/%s/', $home_dir, $module);
                }
                break;

            case 'javascript':
                if ($module == 'core') {
                    $local_root = sprintf('%sjavascript/', $home_dir);
                } else {
                    $local_root = sprintf('%sjavascript/modules/%s/', $home_dir, $module);
                }
                break;

            default:
                continue;
                break;
            }

            if (!isset($local_root) || !PHPWS_Boost::checkLocalRoot($local_root)) {
                PHPWS_Error::log(BOOST_FAILED_LOCAL_COPY, 'boost', 'PHPWS_Boost::updateFiles', $local_root);
                $failures[] = sprintf(dgettext('boost', 'Inaccessible: %s'), $local_root);
            }

            if ($module == 'core') {
                if ($source_root == 'javascript') {
                    /**
                     * If not in a branch, don't need to copy javascript files
                     */
                    if (!PHPWS_Boost::inBranch()) {
                        continue;
                    }
                    $source_file = sprintf('%s%s', PHPWS_SOURCE_DIR, $filename);
                } else {
                    $source_file = sprintf('%score/%s', PHPWS_SOURCE_DIR, $filename);
                }
            } else {
                $source_file = sprintf('%smod/%s/%s', PHPWS_SOURCE_DIR, $module, $filename);
            }

            $local_file = sprintf('%s%s', $local_root, $source_filename);

            // if file is a directory, back up the whole directory
            if (is_dir($source_file)) {

                // if directory exists, make a backup
                if (is_dir($local_file)) {
                    $local_array = explode('/', $local_file);

                    $last_dir = array_pop($local_array);
                    $local_array[] = sprintf('%s_%s', mktime(), $last_dir);
                    $new_dir_name = implode('/', $local_array);

                    if (!@rename($local_file, $new_dir_name)) {
                        $failures[] = sprintf(dgettext('filecabinet', 'Failed directory backup: %s to %s'), $local_file, $new_dir_name);
                        PHPWS_Error::log(BOOST_FAILED_BACKUP, 'boost', 'PHPWS_Boost::updateFiles', $local_file);
                    }
                }

                if (!PHPWS_File::copy_directory($source_file, $local_file)) {
                    PHPWS_Error::log(BOOST_FAILED_LOCAL_COPY, 'boost', 'PHPWS_Boost::updateFiles', $local_file);
                    $failures[] = sprintf(dgettext('boost', 'Failed directory copy: %s to %s'), $source_file, $local_file);
                }

                continue;
            } elseif (!is_file($source_file)) {
                continue;
            }

            if (preg_match('@/@', $source_filename)) {
                $extra_dir = explode('/', $source_filename);
                $filename = array_pop($extra_dir);
                $sofar = null;
                foreach ($extra_dir as $subdir) {
                    $subdir .= '/';
                    $make_dir = $local_root . $sofar . $subdir;
                    if (!is_dir($make_dir)) {
                        if (!@mkdir($make_dir)) {
                            return false;
                        }
                    }
                    $sofar .= $subdir;
                }
            }

            if (is_file($local_file)) {
                if (md5_file($local_file) == md5_file($source_file)) {
                    continue;
                }
                if (!PHPWS_Boost::backupFile($local_file)) {
                    PHPWS_Error::log(BOOST_FAILED_BACKUP, 'boost', 'PHPWS_Boost::updateFiles', $local_file);
                    $failures[] = sprintf(dgettext('boost', 'No backup: %s'), $local_file);
                }
            }

            $result = @copy($source_file, $local_file);
            if (!$result) {
                PHPWS_Error::log(BOOST_FAILED_LOCAL_COPY, 'boost', 'PHPWS_Boost::updateFiles', $local_file);
                $failures[] = sprintf(dgettext('boost', 'Copy file failure: %s to %s'), $source_file, $local_file);
            }
        }
        if (isset($failures)) {
            if ($return_failures) {
                return $failures;
            } else {
                return false;
            }
        } else {
            return true;
        }
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

        $file_alone = mktime() . '_' . $file_alone;
        $new_filename = implode('/', $aFile) . '/' . $file_alone;
        return @copy($filename, $new_filename);
    }

    function updateBranches(&$content)
    {
        if (!PHPWS_Core::moduleExists('branch')) {
            return true;
        }

        PHPWS_Core::initModClass('branch', 'Branch_Admin.php');
        $branches = Branch_Admin::getBranches(true);
        if (empty($branches)) {
            return true;
        }

        $keys = array_keys($this->status);
        $GLOBALS['Boost_In_Branch'] = true;
        foreach ($branches as $branch) {
            // used as the "local" directory in updateFiles
            $GLOBALS['boost_branch_dir'] = $branch->directory;

            $branch->loadBranchDB();

            // create a new boost based on the branch database
            $branch_boost = new PHPWS_Boost;
            $branch_boost->loadModules($keys, false);

            $content[] = '<hr />';
            $content[] = sprintf(dgettext('boost', 'Updating branch %s'), $branch->branch_name);
            
            $result = $branch_boost->update($content);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = dgettext('boost', 'Unable to update branch.');
            }
        }
        $GLOBALS['Boost_In_Branch'] = false;
        PHPWS_DB::disconnect();
    }

    function getAllMods()
    {
        $all_mods = PHPWS_File::readDirectory(PHPWS_SOURCE_DIR . 'mod/', TRUE);
        foreach ($all_mods as $key=> $module) {
            if (is_file(PHPWS_SOURCE_DIR . 'mod/' . $module . '/boost/boost.php')) {
                $dir_mods[] = $module;
            } elseif (is_file(PHPWS_SOURCE_DIR . 'mod/' . $module . '/conf/boost.php')) {
                $GLOBALS['Boost_Old_Mods'][] = $module;
            }

        }
        return $dir_mods;
    }

    /**
     * Returns true if Boost is installing/updating/uninstalling a branch site from the hub.
     * If a module needs to check if it is running from a branch, PHPWS_Core::isBranch should
     * be used.
     */
    function inBranch()
    {
        if (isset($GLOBALS['Boost_In_Branch'])) {
            return $GLOBALS['Boost_In_Branch'];
        } else {
            return false;
        }
    }

}

?>
