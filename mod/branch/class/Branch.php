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
    var $dbname      = NULL;

    function Branch($id=0)
    {
        $this->site_hash = md5(rand());
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = & new PHPWS_DB('branch_sites');
        $db->loadObject($this);
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

    function getTpl()
    {
        $tpl['URL'] = $this->getUrl();
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
            return $_SESSION['Approved_Branch'];
        }
        
        $dsn = Branch::getHubDSN();

        if (empty($dsn)) {
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        }

        $connection = DB::connect($dsn);

        if (PEAR::isError($connection)) {
            PHPWS_Error::log($connection);
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        }

        $sql = sprintf('SELECT branch_sites.* FROM branch_sites WHERE site_hash=\'%s\' AND directory=\'%s\'',
                       SITE_HASH, PHPWS_HOME_DIR);

        $result = $connection->getRow($sql, NULL, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($connection);
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        } elseif (empty($result)) {
            $_SESSION['Approved_Branch'] = FALSE;
            return FALSE;
        } else {
            $_SESSION['Approved_Branch'] = TRUE;
            return TRUE;

        }
    }
}

?>