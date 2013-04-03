<?php

namespace Backward;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Package extends \Package {

    protected $register = false;
    protected $unregister = false;
    protected $import_sql = false;
    protected $priority = 50;
    protected $image_dir = null;
    protected $file_dir = null;
    protected $module_path;
    protected $boost_path;

    /**
     * Returns a resource object to be used with permissioning
     */
    abstract protected function getDefaultResource();

    public function __construct($title, $version)
    {
        //\Backward::defineBackwardVariables();
        \Backward::requireBackwardClasses();
        parent::__construct($title, $version);
        $this->boost_path = 'mod/' . $this->title . '/boost/';
    }

    public function install()
    {
        $db = \Database::newDB();
        $db->begin();
        $this->importBoostFile();
        try {
            if ($this->create_tables && $this->import_sql) {
                $sql_file = $this->boost_path . 'install.sql';
                if (!is_file($sql_file)) {
                    throw new \Exception(t('Backward module "%s" is missing a install.sql file', $this->title));
                }
                $this->importSQL($sql_file);
            }
            $this->processInstallFile();
            $this->installPermissions();
            parent::install();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    protected function processInstallFile()
    {
        $null = null;
        $install_file = $this->boost_path . 'install.php';
        if (!is_file($install_file)) {
            return;
        }
        include $install_file;
        $install_function = $this->title . '_install';
        $install_function($null);
    }

    protected function installPermissions()
    {
        /**
         * Defined (or not) in the permission_file
         * @var boolean $use_permissions
         */
        $use_permissions = false;

        /**
         * If permissions are used, this will be an array containing the
         * permissions used by the module. From the permissions file.
         * @var array $permissions
         */
        $permissions = null;

        $permission_file = $this->boost_path . 'permission.php';
        if (!is_file($permission_file)) {
            return;
        }
        include $permission_file;
        if ($use_permissions) {
            if (isset($permissions)) {
                $role_name = $this->title . ' Legacy User';
                try {
                    $module_role = \User\PermissionFactory::createRole($role_name);
                } catch (\Exception $e) {
                    // if the role is already created (code 23000), we don't pass it up.
                    if ($e->getCode() != 23000) {
                        throw $e;
                    }
                    $module_role = \User\PermissionFactory::getRoleByTitle($role_name);
                }
                foreach ($permissions as $permit_title => $permit_name) {
                    $command = \User\PermissionFactory::createCommand($permit_title, $permit_name);
                    $permission = \User\PermissionFactory::createPermission($this->getDefaultResource(), $command);
                    $module_role->allow($permission);
                }
            }
        }
    }

    protected function uninstallPermissions()
    {

    }

    protected function processUninstallFile()
    {
        $null = null;
        $uninstall_file = $this->boost_path . 'uninstall.php';
        if (!is_file($uninstall_file)) {
            return;
        }
        include $uninstall_file;
        $uninstall_function = $this->title . '_uninstall';
        $uninstall_function($null);
    }

    public function uninstall()
    {
        $this->importBoostFile();

        if ($this->remove_tables && $this->import_sql) {
            $sql_file = $this->boost_path . 'uninstall.sql';
            if (is_file($sql_file)) {
                $this->importSQL($sql_file);
            }
        }
        $this->processUninstallFile();
        parent::uninstall();
    }

    public function importBoostFile()
    {
        $boost_file_path = $this->boost_path . 'boost.php';
        if (!is_file($boost_file_path)) {
            throw new \Exception(t('Backward module "%s" is missing a boost.php file', $this->title));
        }
        include $boost_file_path;
        $this->import_sql = isset($import_sql) ? $import_sql : false;
        $this->register = isset($register) ? $register : false;
        $this->unregister = isset($unregister) ? $unregister : false;
        $this->priority = isset($priority) ? $priority : 50;
        $this->dependency = isset($dependency) ? $dependency : false;
        $this->image_dir = isset($image_dir) ? $image_dir : false;
        $this->file_dir = isset($file_dir) ? file_dir : false;
    }

    public function importSQL($file_path)
    {
        $sql_rows = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($sql_rows)) {
            throw new \Exception(t('Could not parse any content from SQL file'));
        }

        foreach ($sql_rows as $query_line) {
            if (preg_match('/^--/', $query_line)) {
                continue;
            }
            $query_stack[] = $query_line;
            if (preg_match('/\);$/', $query_line)) {
                $final_query_stack[] = implode(' ', $query_stack);
                $query_stack = array();
            }
        }
        if (empty($final_query_stack)) {
            throw new \Exception(t('Could not parse any content from SQL file'));
        }
        $db = \Database::newDB();
        foreach ($final_query_stack as $query) {
            try {
                $db->query($query);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

?>
