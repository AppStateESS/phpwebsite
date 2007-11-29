<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Branch {
    var $id          = null;
    var $branch_name = null;
    var $directory   = null; 
    var $url         = null;
    var $site_hash   = null;
    var $dsn         = null;
    var $prefix      = null;

    function Branch($id=0, $load_dsn=false)
    {
        $this->site_hash = md5(rand());
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function loadDSN()
    {
        $config = file($this->directory . 'config/core/config.php');
        foreach ($config as $row) {
            $row = str_replace(' ', '', trim($row));
            if (preg_match("/^define\('phpws_dsn'/i", $row)) {
                $sub = explode(',', $row);
                $this->dsn = preg_replace("@'|\);$@", '', $sub[1]);
            }

            if (preg_match("/^define\('phpws_table_prefix','\w+'/i", $row)) {
                $this->prefix = preg_replace('/define\(\'phpws_table_prefix\',\'([\w\/:@]+)\'\);/iU', '\\1', $row);
            }

            if (!empty($this->dsn) && !empty($this->prefix)) {
                return true;
            }
        }
        if (isset($this->dsn)) {
            return true;
        } else {
            return false;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('branch_sites');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return $result;
        }
    }

    function setBranchName($branch_name)
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
    
    function save()
    {
        if (!preg_match('/\/$/', $this->directory)) {
            $this->directory .= '/';
        }

        $db = new PHPWS_DB('branch_sites');
        return $db->saveObject($this);
    }

    function getUrl()
    {
        if (!preg_match('/^(http(s){0,1}:\/\/)/', $this->url)) {
            $http = 'http://' . $this->url;
        } else {
            $http = &$this->url;
        }
        return sprintf('<a href="%s">%s</a>', $http, PHPWS_Text::shortenUrl($http));
    }

    function createDirectories()
    {
        if (!mkdir($this->directory . 'config/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'config/core/')) {
            return FALSE;
        }
        
        if (!mkdir($this->directory . 'files/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'images/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'images/core/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'javascript/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'javascript/modules')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'templates/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'templates/cache/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'themes/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . 'logs/')) {
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
    function getTpl()
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

    function getHubPrefix() {
        $handle = @fopen(PHPWS_SOURCE_DIR . 'config/core/config.php', 'r');
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

    function getHubDSN()
    {
        $handle = @fopen(PHPWS_SOURCE_DIR . 'config/core/config.php', 'r');
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
    function loadHubDB()
    {
        $dsn = Branch::getHubDSN();
        if (empty($dsn)) {
            return FALSE;
        }

        $GLOBALS['Branch_Temp']['dsn'] = $GLOBALS['PHPWS_DB']['dsn'];
        $GLOBALS['Branch_Temp']['prefix'] = $GLOBALS['PHPWS_DB']['dsn'];

        $prefix = Branch::getHubPrefix();
        return PHPWS_DB::loadDB($dsn, $prefix);
    }

    /**
     * Connects currently constructed branch to its database
     * Not called statically.
     */
    function loadBranchDB()
    {
        if (empty($this->dsn)) {
            return false;
        }

        return PHPWS_DB::loadDB($this->dsn, $this->prefix);
    }

    /**
     * Restores the branch connection after calling the loadHubDB
     */
    function restoreBranchDB()
    {
        $prefix = $dsn = null;
        extract($GLOBALS['Branch_Temp']);
        PHPWS_DB::loadDB($dsn, $prefix);
    }

    function checkCurrentBranch()
    {
        if (isset($_SESSION['Approved_Branch'])) {
            return (bool)$_SESSION['Approved_Branch'];
        }

        Branch::loadHubDb();

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

    function getHubDB()
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

    function getCurrent()
    {
        if (!isset($_SESSION['Approved_Branch'])) {
            return FALSE;
        } else {
            return $_SESSION['Approved_Branch'];
        }
    }

    function getBranchMods()
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
    function delete()
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