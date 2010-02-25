<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Branch {
    public $id          = null;
    public $branch_name = null;
    public $directory   = null;
    public $url         = null;
    public $site_hash   = null;
    public $dsn         = null;
    public $prefix      = null;

    public function __construct($id=0, $load_dsn=false)
    {
        $this->site_hash = md5(rand());
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    public function loadDSN()
    {
        // @todo this needs changing to work with new system
        $config_file = $this->getBranchConfig();

        $config_contents = file_get_contents($config_file);
        $config = explode("\n", $config_contents);

        if (preg_match('/phpws_table_prefix/i', $config_contents)) {
            $prefix_used = true;
        } else {
            $prefix_used = false;
        }

        foreach ($config as $row) {
            if (preg_match('/phpws_dsn/i', $row) && preg_match('/^define/i', $row)) {
                $sub = explode(',', $row);
                $this->dsn = preg_replace("@'|\);$@", '', trim($sub[1]));
            }

            if (preg_match('/phpws_table_prefix/i', $row) && preg_match('/^define/i', $row)) {
                $this->prefix = preg_replace('/phpws_table_prefix|define|[\s\'"(),;]/i', '', trim($row));
            }

            if (!empty($this->dsn) && (!$prefix_used || !empty($this->prefix))) {
                return true;
            }
        }

        if (isset($this->dsn)) {
            return true;
        } else {
            return false;
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('branch_sites');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return $result;
        }
    }

    public function setBranchName($branch_name)
    {
        $this->branch_name = $branch_name;
        $db = new PHPWS_DB('branch_sites');
        $db->addWhere('branch_name', $branch_name);
        $db->addWhere('id', $this->id, '!=');
        $result = $db->select();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        } elseif ($result) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * using this method for config file name
     * @return unknown_type
     */
    public function getBranchConfig()
    {
        $name = preg_replace('/\W/', '-', $this->branch_name);
        return PHPWS_SOURCE_DIR . 'config/branches/' . $name . '.php';
    }

    public function save()
    {
        if (!preg_match('/\/$/', $this->directory)) {
            $this->directory .= '/';
        }

        $db = new PHPWS_DB('branch_sites');
        return $db->saveObject($this);
    }

    public function getUrl()
    {
        if (!preg_match('/^(http(s){0,1}:\/\/)/', $this->url)) {
            $http = 'http://' . $this->url;
        } else {
            $http = &$this->url;
        }
        return sprintf('<a href="%s">%s</a>', $http, PHPWS_Text::shortenUrl($http));
    }

    public function createDirectories()
    {
        if (!mkdir($this->directory . 'files/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'images/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'admin/')) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Returns an associative array for the branch list page
     */
    public function getTpl()
    {

        $tpl['URL'] = $this->getUrl();

        $links[] = PHPWS_Text::secureLink(dgettext('branch', 'Edit'), 'branch',
        array('command'=>'edit_branch', 'branch_id'=>$this->id));

        $js['question'] = dgettext('branch', 'Removing this branch will make it inaccessible.\nThe database and files will remain behind.\nIf you are sure you want to remove the branch, type the branch name:');
        $js['address'] = sprintf('index.php?module=branch&command=remove_branch&branch_id=%s&authkey=%s', $this->id, Current_User::getAuthKey());
        $js['value_name'] = 'branch_name';
        $js['link'] = dgettext('branch', 'Remove');

        $links[] = javascript('prompt', $js);

        $links[] = PHPWS_Text::secureLink(dgettext('branch', 'Modules'), 'branch',
        array('command'=>'branch_modules', 'branch_id'=>$this->id));
        $tpl['DIRECTORY'] = sprintf('<abbr title="%s">%s</abbr>', $this->directory,
        PHPWS_Text::shortenUrl($this->directory));
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    public function getHubPrefix() {
        $handle = @fopen(PHPWS_SOURCE_DIR . 'core/conf/branches/config.php', 'r');
        if ($handle) {
            $search_for = '^define\(\'PHPWS_TABLE_PREFIX\',';
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $buffer = str_replace(' ', '', $buffer);
                if (preg_match('/' . $search_for . '/', $buffer)) {
                    $prefix = preg_replace('/^define\(\'PHPWS_TABLE_PREFIX\',\'(.*)\'\);/Ui', '\\1', $buffer);
                    return trim($prefix);
                    break;
                }
            }
            return null;
        } else {
            return null;
        }
    }

    public function getHubDSN()
    {
        $handle = @fopen(PHPWS_SOURCE_DIR . 'config/config.php', 'r');
        if ($handle) {
            $search_for = '^define\(\'PHPWS_DSN\',';
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $buffer = str_replace(' ', '', $buffer);
                if (preg_match('/' . $search_for . '/', $buffer)) {
                    $dsn = preg_replace('/^define\(\'PHPWS_DSN\',\'(.*)\'\);/Ui', '\\1', $buffer);
                    return trim($dsn);
                    break;
                }
            }
            return null;
        } else {
            return null;
        }
    }

    /**
     * Makes a connection to the hub database. Used when currently using a
     * branch connection.
     */
    public function loadHubDB()
    {
        $dsn = Branch::getHubDSN();
        if (empty($dsn)) {
            return FALSE;
        }

        $GLOBALS['Branch_Temp']['dsn'] = $GLOBALS['PHPWS_DB']['dsn'];
        $GLOBALS['Branch_Temp']['prefix'] = $GLOBALS['PHPWS_DB']['tbl_prefix'];

        $prefix = Branch::getHubPrefix();
        return PHPWS_DB::loadDB($dsn, $prefix);
    }

    /**
     * Connects currently constructed branch to its database
     * Not called statically.
     */
    public function loadBranchDB()
    {
        if (empty($this->dsn)) {
            return false;
        }

        return PHPWS_DB::loadDB($this->dsn, $this->prefix);
    }

    /**
     * Restores the branch connection after calling the loadHubDB
     */
    public function restoreBranchDB()
    {
        $prefix = $dsn = null;
        extract($GLOBALS['Branch_Temp']);
        PHPWS_DB::loadDB($dsn, $prefix);
    }

    public function checkCurrentBranch()
    {
        if (isset($_SESSION['Approved_Branch'])) {
            return (bool)$_SESSION['Approved_Branch'];
        }

        Branch::loadHubDB();

        if (!PHPWS_DB::isConnected()) {
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        }

        $db = new PHPWS_DB('branch_sites');
        $db->addWhere('site_hash', SITE_HASH);
        $db->addColumn('id');
        $result = $db->select('one');

        PHPWS_DB::loadDB();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $_SESSION['Approved_Branch'] = FALSE;
            return false;
        } elseif (empty($result)) {
            $_SESSION['Approved_Branch'] = FALSE;
            return false;
        } else {
            $_SESSION['Approved_Branch'] = $result;
            return true;
        }
    }

    public function getHubDB()
    {
        $dsn = Branch::getHubDSN();
        if (empty($dsn)) {
            return FALSE;
        }

        $prefix = Branch::getHubPrefix();

        if ($prefix) {
            $GLOBALS['PHPWS_TABLE_PREFIX'] = $prefix;
        }

        $connection = DB::connect($dsn);

        if (PEAR::isError($connection)) {
            PHPWS_Error::log($connection);
            return FALSE;
        }
        return $connection;
    }

    public function getCurrent()
    {
        if (!isset($_SESSION['Approved_Branch'])) {
            return FALSE;
        } else {
            return $_SESSION['Approved_Branch'];
        }
    }

    public function getBranchMods()
    {
        $branch_id = Branch::getCurrent();
        if (!$branch_id) {
            return null;
        }

        Branch::loadHubDB();

        $db = new PHPWS_DB('branch_mod_limit');
        $db->addColumn('module_name');
        $db->addWhere('branch_id', $branch_id);
        $result = $db->select('col');

        PHPWS_DB::loadDB();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return null;
        } else {
            return $result;
        }

    }

    /**
     * Deletes a branch from the hub's database
     */
    public function delete()
    {
        $db = new PHPWS_DB('branch_sites');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        }
        $db->reset();
        $db->setTable('branch_mod_limit');
        $db->addWhere('branch_id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }

        return true;
    }
}

?>