<?php
/**
 * Class contains module information
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PHPWS_Module {
    public $title         = null;
    public $proper_name   = null;
    public $priority      = 50;
    public $directory     = null;
    public $version       = null;
    public $active        = true;
    public $image_dir     = false;
    public $file_dir      = false;
    public $register      = false;
    public $unregister    = false;
    public $import_sql    = false;
    public $version_http  = null;
    public $about         = false;
    public $fullMod       = true;
    private $_dependency   = false;
    private $_dep_list     = null;
    public $_error        = null;

    public function __construct($title=null, $file=true)
    {
        if (isset($title)) {
            $this->setTitle($title);
            $this->init($file);
        }
    }

    public function initByDB()
    {
        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $this->title);
        return $db->loadObject($this);
    }

    public function initByFile()
    {
        $filename = sprintf('%smod/%s/boost/boost.php', PHPWS_SOURCE_DIR, $this->title);

        if (!is_file($filename)) {
            $this->fullMod = false;
            return null;
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

        return true;
    }

    public function init($file=true)
    {
        $title = &$this->title;

        if ($title == 'core') {
            $this->setDirectory(PHPWS_SOURCE_DIR . 'core/');

            // even if use_file is false, we get the version_http from the file
            $filename = PHPWS_SOURCE_DIR . 'core/boost/boost.php';
            if (!is_file($filename)) {
                $this->_error = PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, 'core', 'PHPWS_Module::init', $filename);
            } else {
                include $filename;
            }

            if (!$file) {
                $db = new PHPWS_DB('core_version');
                $db->addColumn('version');
                $version = $db->select('one');
            }

            $this->_dependency = (bool)$dependency;
            $this->setVersion($version);
            $this->setRegister(false);
            $this->setImportSQL(true);
            $this->setProperName('Core');
            $this->setVersionHttp($version_http);
            $this->setAbout(true);
        } else {
            $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");
            if ($file == true) {
                $result = PHPWS_Module::initByFile();
            } else {
                $result = PHPWS_Module::initByDB();
            }

            if (PHPWS_Error::isError($result)) {
                $this->_error = $result;
            } elseif(empty($result)) {
                $this->_error = PHPWS_Error::get(PHPWS_NO_MOD_FOUND, 'core', 'PHPWS_Module::init', $title);
            }
        }
    }


    public function setTitle($title)
    {
        $this->title = trim($title);
    }

    public function setProperName($name)
    {
        $this->proper_name = $name;
    }

    public function getProperName($useTitle=false)
    {
        if (!isset($this->proper_name) && $useTitle == true) {
            return ucwords(str_replace('_', ' ', $this->title));
        }
        else {
            return $this->proper_name;
        }
    }

    public function setPriority($priority)
    {
        $this->priority = (int)$priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setRegister($register)
    {
        $this->register = (bool)$register;
    }

    public function isRegister()
    {
        return $this->register;
    }

    public function setUnregister($unregister)
    {
        $this->unregister = (bool)$unregister;
    }

    public function isUnregister()
    {
        return $this->unregister;
    }

    public function setImportSQL($sql)
    {
        $this->import_sql = (bool)$sql;
    }

    public function isImportSQL()
    {
        return $this->import_sql;
    }

    public function setImageDir($switch)
    {
        $this->image_dir = (bool)$switch;
    }

    public function isImageDir()
    {
        return $this->image_dir;
    }

    public function setFileDir($switch)
    {
        $this->file_dir = (bool)$switch;
    }

    public function isFileDir()
    {
        return $this->file_dir;
    }

    public function setActive($active)
    {
        $this->active = (bool)$active;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setAbout($about)
    {
        $this->about = (bool)$about;
    }

    public function isAbout()
    {
        return $this->about;
    }

    public function setVersionHttp($http)
    {
        $this->version_http = $http;
    }

    public function getVersionHttp()
    {
        return $this->version_http;
    }

    public function save()
    {
        if ($this->title != 'core') {
            $db = new PHPWS_DB('modules');
            $db->addWhere('title', $this->title);
            $db->delete();
            $db->resetWhere();
            if (!$this->getProperName()) {
                $this->setProperName($this->getProperName(true));
            }
            $result = $db->saveObject($this);
            if (PHPWS_Error::isError($result)) {
                return $result;
            }

            return $this->saveDependencies();
        } else {
            $db = new PHPWS_DB('core_version');
            $db->addValue('version', $this->version);
            $result = $db->update();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function saveDependencies()
    {
        if (!$this->_dependency) {
            return true;
        }

        $db = new PHPWS_DB('dependencies');
        $db->addWhere('source_mod', $this->title);
        $db->delete();
        $db->reset();

        $dep_list = $this->getDependencies();

        if (empty($dep_list)) {
            return null;
        }

        foreach ($dep_list['MODULE'] as $stats) {
            $db->addValue('source_mod', $this->title);
            $db->addValue('depended_on', $stats['TITLE']);
            $db->addValue('version', $stats['VERSION']);
            $result = $db->insert();

            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }
    }

    public function isDependedUpon()
    {
        static $depend_list = array();

        if (!empty($depend_list) &&
        isset($depend_list[$this->title])) {
            return $depend_list[$this->title];
        }

        $db = new PHPWS_DB('dependencies');
        $db->addWhere('depended_on', $this->title);
        $db->addColumn('source_mod');
        $result = $db->select('col');

        if (empty($result)) {
            return $depend_list[$this->title] = false;
        } else {
            return $depend_list[$this->title] = $result;
        }

    }

    public function isInstalled($title=null)
    {
        static $module_list = array();

        if (isset($this->_error) && $this->_error->code == PHPWS_NO_MOD_FOUND) {
            return false;
        }

        if (empty($title)) {
            if (isset($this->title)) {
                $title = &$this->title;
            } else {
                return null;
            }
        }

        if ($title == 'core') {
            return true;
        }

        if (!empty($module_list) && isset($module_list[$title])) {
            return $module_list[$title];
        }

        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $title);
        $db->addColumn('title');
        $result = $db->select('one');
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            if (isset($result)) {
                $module_list[$title] = true;
                return true;
            } else {
                $module_list[$title] = false;
                return false;
            }
        }
    }

    public function needsUpdate()
    {
        $db = new PHPWS_DB('modules');
        $db->addWhere('title', $this->title);
        $result = $db->select('row');
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }
        return version_compare($result['version'], $this->getVersion(), '<');
    }

    public function isFullMod()
    {
        return $this->fullMod;
    }

    public function checkDependency()
    {
        // Module doesn't have dependencies therefore no
        // need to check
        if (!$this->_dependency) {
            return true;
        }

        $dep_list = $this->getDependencies();

        if (empty($dep_list)) {
            return false;
        }

        foreach ($dep_list['MODULE'] as $stats) {
            extract($stats);
            $module = new PHPWS_Module($TITLE, false);

            if (!$module->isInstalled()) {
                return false;
            }

            if (version_compare($VERSION, $module->getVersion(), '>')) {
                return false;
            }
        }

        return true;
    }


    public function getDependencies()
    {
        $file = $this->getDirectory() . 'boost/dependency.xml';
        if (!is_file($file)) {
            return null;
        }

        $dep_list = PHPWS_Text::xml2php($file, 1);
        $module_list = PHPWS_Text::tagXML($dep_list);

        if (!isset($module_list['MODULE'])) {
            return null;
        }

        return $module_list;
    }
}

?>