<?php
/**
 * Class contains module information
 *
 * @version $Id$
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
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
    var $pre94         = FALSE;
    var $fullMod       = TRUE;
    var $_dependency   = FALSE;
    var $_dep_list     = NULL;
    var $_error        = NULL;

    function PHPWS_Module($title=NULL, $file=TRUE)
    {
        if (isset($title)){
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
        $result = PHPWS_Core::getConfigFile($this->title, 'boost.php');
        if (empty($result)) {
            $this->fullMod = FALSE;
            return $result;
        }

        include $result;
    
        if (isset($mod_title)){
            $this->pre94 = TRUE;
            $proper_name = $mod_pname;
            if (!isset($active)|| $active == 'on') {
                $active = TRUE;
            } else {
                $active = FALSE;
            }
        }
    
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

        if ($this->isPre94()) {
            $this->setImportSQL(FALSE);
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
        $title = $this->getTitle();

        $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");

        if ($file == TRUE) {
            $result = PHPWS_Module::initByFile();
        } else {
            $result = PHPWS_Module::initByDB();
        }

        if (PEAR::isError($result)) {
            $this->_error = $result;
        }

    }


    function setTitle($title)
    {
        $this->title = trim($title);
    }

    function getTitle()
    {
        return $this->title;
    }

    function setProperName($name)
    {
        $this->proper_name = $name;
    }

    function getProperName($useTitle=FALSE)
    {
        if (!isset($this->proper_name) && $useTitle == TRUE)
            return ucwords(str_replace('_', ' ', $this->getTitle()));
        else
            return $this->proper_name;
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

    function isPre94()
    {
        return $this->pre94;
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
        $db->addWhere('title', $this->getTitle());
        $db->delete();
        $db->resetWhere();
        if (!$this->getProperName())
            $this->setProperName($this->getProperName(TRUE));

        return $db->saveObject($this);
    }

    function isInstalled()
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('title', $this->getTitle());
        $result = $db->select('row');
        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return FALSE;
        } else
            return isset($result);
    }

    function needsUpdate()
    {
        $db = & new PHPWS_DB('modules');
        $db->addWhere('title', $this->getTitle());
        $result = $db->select('row');
        if (PEAR::isError($result)){
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

        foreach ($dep_list as $mod_title => $stats) {
            $module = & new PHPWS_Module($mod_title, FALSE);

            if (!$module->isInstalled()) {
                return FALSE;
            }

            if (version_compare($stats['VERSION'], $module->getVersion(), '<')) {
                return FALSE;
            }
        }

        return TRUE;
    }

    function getDependencies()
    {
        $file = $this->getDirectory() . 'conf/dependency.xml';
        if (!is_file($file)) {
            return NULL;
        }

        $list = PHPWS_Text::xml2php($file);
        $dep_list = $list[0]['value'];
        foreach ($dep_list as $info) {
            foreach ($info as $mod) {
                $title = NULL;
                $module = array();
                foreach ($mod as $mod_info) {
                    extract($mod_info);
                    if ($tag == 'TITLE') {
                        $title = $value;
                        continue;
                    }
                    $module[$tag] = $value;
                }
                if (isset($title)) {
                    $module_list[$title] = $module;
                }
            }
        }
        return $module_list;
    }
}

?>