<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Branch {
    var $id          = NULL;
    var $branch_name = NULL;
    var $directory   = NULL; // saved WITHOUT final forward slash (/)
    var $url         = NULL;
    var $site_hash   = NULL;
    var $dsn         = null;

    function Branch($id=0)
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
        $config = file('/var/www/html/branch/config/core/config.php');
        foreach ($config as $row) {
            $row = str_replace(' ', '', trim($row));
            if (preg_match('/^define\(\'phpws_dsn\'/i', $row)) {
                $this->dsn = preg_replace('/define\(\'phpws_dsn\',\'([\w\/:@]+)\'\);/iU', '\\1', $row);
                return true;
            }
        }
        return false;
    }

    function init()
    {
        $db = & new PHPWS_DB('branch_sites');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return $result;
        }
    }

    function setBranchName($branch_name)
    {
        $this->branch_name = $branch_name;
        $db = & new PHPWS_DB('branch_sites');
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

        $db = & new PHPWS_DB('branch_sites');
        return $db->saveObject($this);
    }

    function getUrl()
    {
        if (!preg_match('/^(http(s){0,1}:\/\/)/', $this->url)) {
            $http = 'http://' . $this->url;
        } else {
            $http = &$this->url;
        }
        return sprintf('<a href="%s">%s</a>', $http, $http);
    }

    function createDirectories()
    {
        if (!mkdir($this->directory . '/config/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/config/core/')) {
            return FALSE;
        }
        
        if (!mkdir($this->directory . '/files/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/images/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/images/core/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/javascript/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/javascript/modules')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/templates/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/themes/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/logs/')) {
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

        $links[] = PHPWS_Text::secureLink(_('Edit'), 'branch', 
                                          array('command'=>'edit_branch', 'branch_id'=>$this->id));

        $js['question'] = _('Removing this branch will make it inaccessible.\nThe database and files will remain behind.\nIf you are sure you want to remove the branch, type the branch name:');
        $js['address'] = sprintf('index.php?module=branch&command=remove_branch&branch_id=%s&authkey=%s', $this->id, Current_User::getAuthKey());
        $js['value_name'] = 'branch_name';
        $js['link'] = _('Remove');

        $links[] = javascript('prompt', $js);

        $links[] = PHPWS_Text::secureLink(_('Modules'), 'branch',
                                          array('command'=>'branch_modules', 'branch_id'=>$this->id));

        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
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
            return NULL;
        } else {
            return NULL;
        }

    }

    function checkCurrentBranch()
    {
        if (isset($_SESSION['Approved_Branch'])) {
            return (bool)$_SESSION['Approved_Branch'];
        }

        PHPWS_DB::disconnect();
        $connection = Branch::getHubDB();

        if (!$connection) {
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        }

        $sql = sprintf('SELECT branch_sites.id FROM branch_sites WHERE site_hash=\'%s\'',
                       SITE_HASH);

        $result = $connection->getOne($sql, NULL, DB_FETCHMODE_ASSOC);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($connection);
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        } elseif (empty($result)) {
            $_SESSION['Approved_Branch'] = FALSE;
            $connection->disconnect();
            return FALSE;
        } else {
            $_SESSION['Approved_Branch'] = $result;
            $connection->disconnect();
            return TRUE;
        }
    }

    function getHubDB()
    {
        $dsn = Branch::getHubDSN();

        if (empty($dsn)) {
            return FALSE;
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
            return NULL;
        }

        $db = Branch::getHubDB();

        if (!$db) {
            return NULL;
        }

        $sql = sprintf('SELECT module_name FROM branch_mod_limit WHERE branch_id=\'%s\'', $branch_id);

        $result = $db->getCol($sql);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        } else {
            return $result;
        }
        $db->disconnect();
    }

    /**
     * Deletes a branch from the hub's database
     */
    function delete()
    {
        $db = & new PHPWS_DB('branch_sites');
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