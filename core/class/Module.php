<?php
/**
 * Class contains module information
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PHPWS_Module {
    var $title         = NULL;
    var $proper_name   = NULL;
    var $priority      = 50;
    var $directory     = NULL;
    var $version       = NULL;
    var $active        = TRUE; 
    var $image_dir     = TRUE;
    var $file_dir      = TRUE;
    var $register      = FALSE;
    var $unregister    = FALSE;
    var $import_sql    = FALSE;
    var $version_http  = NULL;
    var $about         = FALSE;
    var $fullMod       = TRUE;
    var $_dependency   = FALSE;
    var $_dep_list     = NULL;
    var $_error        = NULL;

    function PHPWS_Module($title=NULL, $file=TRUE)
    {
        if (isset($title)) {
            $this->setTitle($title);
            $this->init($file);
        }
    }

    function initByDB()
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('title', $this->title);
        return $db->loadObject($this);
    }

    function initByFile()
    {
        $filename = sprintf('%smod/%s/boost/boost.php', PHPWS_SOURCE_DIR, $this->title);

        if (!is_file($filename)) {
            $this->fullMod = FALSE;
            return NULL;
        }

        include $filename;
    
        if (isset($proper_name)) {
            $this->setProperName($proper_name);
        }

        if (isset($priority)) {
            $this->setPriority($priority);
        }

        if (isset($version)) {
            $this->setVersion($version);
        }

        if (isset($active)) {
            $this->setActive($active);
        }

        if (isset($import_sql)) {
            $this->setImportSQL($import_sql);
        }

        if (isset($image_dir)) {
            $this->setImageDir($image_dir);
        }

        if (isset($file_dir)) {
            $this->setFileDir($file_dir);
        }

        if (isset($register)) {
            $this->setRegister($register);
        }

        if (isset($unregister)) {
            $this->setUnregister($unregister);
        }

        if (isset($version_http)) {
            $this->setVersionHttp($version_http);
        }

        if (isset($about)) {
            $this->setAbout($about);
        }

        if (isset($dependency)) {
            $this->_dependency = (bool)$dependency;
        }

        return TRUE;
    }

    function init($file=TRUE)
    {
        $title = $this->title;

        $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");
        if ($file == TRUE) {
            $result = PHPWS_Module::initByFile();
        } else {
            $result = PHPWS_Module::initByDB();
        }

        if (PEAR::isError($result)) {
            $this->_error = $result;
        } elseif(empty($result)) {
            $this->_error = PHPWS_Error::get(PHPWS_NO_MOD_FOUND, 'core', 'PHPWS_Module::init', $title);
        }
    }


    function setTitle($title)
    {
        $this->title = trim($title);
    }

    function setProperName($name)
    {
        $this->proper_name = $name;
    }

    function getProperName($useTitle=FALSE)
    {
        if (!isset($this->proper_name) && $useTitle == TRUE) {
            return ucwords(str_replace('_', ' ', $this->title));
        }
        else {
            return $this->proper_name;
        }
    }

    function setPriority($priority)
    {
        $this->priority = (int)$priority;
    }

    function getPriority()
    {
        return $this->priority;
    }

    function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    function getDirectory()
    {
        return $this->directory;
    }

    function setVersion($version)
    {
        $this->version = $version;
    }

    function getVersion()
    {
        return $this->version;
    }

    function setRegister($register)
    {
        $this->register = (bool)$register;
    }

    function isRegister()
    {
        return $this->register;
    }

    function setUnregister($unregister)
    {
        $this->unregister = (bool)$unregister;
    }

    function isUnregister()
    {
        return $this->unregister;
    }

    function setImportSQL($sql)
    {
        $this->import_sql = (bool)$sql;
    }

    function isImportSQL()
    {
        return $this->import_sql;
    }

    function setImageDir($switch)
    {
        $this->image_dir = (bool)$switch;
    }

    function isImageDir()
    {
        return $this->image_dir;
    }

    function setFileDir($switch)
    {
        $this->file_dir = (bool)$switch;
    }

    function isFileDir()
    {
        return $this->file_dir;
    }

    function setActive($active)
    {
        $this->active = (bool)$active;
    }

    function isActive()
    {
        return $this->active;
    }

    function setAbout($about)
    {
        $this->about = (bool)$about;
    }

    function isAbout()
    {
        return $this->about;
    }

    function setVersionHttp($http)
    {
        $this->version_http = $http;
    }

    function getVersionHttp()
    {
        return $this->version_http;
    }

    function save()
    {
        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $this->title);
        $db->delete();
        $db->resetWhere();
        if (!$this->getProperName()) {
            $this->setProperName($this->getProperName(TRUE));
        }
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        return $this->saveDependencies();
    }

    function saveDependencies()
    {
        if (!$this->_dependency) {
            return TRUE;
        }

        $db = & new PHPWS_DB('dependencies');
        $db->addWhere('source_mod', $this->title);
        $db->delete();
        $db->reset();

        $dep_list = $this->getDependencies();

        if (empty($dep_list)) {
            return NULL;
        }

        foreach ($dep_list['MODULE'] as $stats) {
            $db->addValue('source_mod', $this->title);
            $db->addValue('depended_on', $stats['TITLE']);
            $db->addValue('version', $stats['VERSION']);
            $result = $db->insert();

            if (PEAR::isError($result)) {
                return $result;
            }
        }
    }

    function isDependedUpon()
    {
        static $depend_list = array();

        if (!empty($depend_list) &&
            isset($depend_list[$this->title])) {
            return $depend_list[$this->title];
        }
        
        $db = & new PHPWS_DB('dependencies');
        $db->addWhere('depended_on', $this->title);
        $db->addColumn('source_mod');
        $result = $db->select('col');

        if (empty($result)) {
            return $depend_list[$this->title] = FALSE;
        } else {
            return $depend_list[$this->title] = $result;
        }
            
    }

    function isInstalled($title=NULL)
    {
        static $module_list = array();

        if (empty($title)) {
            if (isset($this->title)) {
                $title = &$this->title;
            } else {
                return NULL;
            }
        }

        if ($title == 'core') {
            return true;
        }

        if (!empty($module_list) && isset($module_list[$title])) {
            return $module_list[$title];
        }

        $db = & new PHPWS_DB('modules');
        $db->addWhere('title', $title);
        $db->addColumn('title');
        $result = $db->select('one');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        } else {
            if (isset($result)) {
                $module_list[$title] = TRUE;
                return TRUE;
            } else {
                $module_list[$title] = FALSE;
                return FALSE;
            }
        }
    }

    function needsUpdate()
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('title', $this->title);
        $result = $db->select('row');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        return version_compare($result['version'], $this->getVersion(), '<');
    }
  
    function isFullMod()
    {
        return $this->fullMod;
    }

    function checkDependency()
    {
        // Module doesn't have dependencies therefore no
        // need to check
        if (!$this->_dependency) {
            return TRUE;
        }

        $dep_list = $this->getDependencies();

        if (empty($dep_list)) {
            return FALSE;
        }

        foreach ($dep_list['MODULE'] as $stats) {
            extract($stats);
            if ($TITLE == 'core') {
                $module = PHPWS_Core::loadAsMod(false);
            } else {
                $module = & new PHPWS_Module($TITLE, FALSE);
            }

            if (!$module->isInstalled()) {
                return FALSE;
            }

            if (version_compare($VERSION, $module->getVersion(), '>')) {
                return FALSE;
            }
        }

        return TRUE;
    }


    function getDependencies()
    {
        $file = $this->getDirectory() . 'boost/dependency.xml';
        if (!is_file($file)) {
            return NULL;
        }

        $dep_list = PHPWS_Text::xml2php($file, 1);
        $module_list = PHPWS_Text::tagXML($dep_list);

        if (!isset($module_list['MODULE'])) {
            return NULL;
        }

        return $module_list;
    }
}

?>