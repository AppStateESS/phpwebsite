<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireInc('demographics', 'errorDefines.php');
PHPWS_Core::initModClass('demographics', 'Demographics_User.php');
define('DEMOGRAPHICS_DEFAULT_LIMIT', 255);

class Demographics {
    /**
     * Returns the fields currently used in demographics
     */
    function getFields()
    {
        $db = new PHPWS_DB('demographics');
        $columns = $db->getTableColumns();
        return $columns;
    }

    // returns default demographic fields
    function getDefaultFields()
    {
        static $fields;
        if (empty($fields)) {
            $file = PHPWS_SOURCE_DIR . 'mod/demographics/inc/defaults.php';

            if (!is_file($file)) {
                // default file error
                return NULL;
            }
            include $file;
        }

        return $fields;
    }

    /**
     * Returns TRUE if the field is one of Demographic's default
     * fields.
     */
    function isDefaultField($field_name)
    {
        $fields = Demographics::getDefaultFields();
        return isset($fields[$field_name]);
    }

    /**
     * Returns the stats for Demographics default field
     */
    function getDefaultStats($field_name)
    {
        $fields = Demographics::getDefaultFields();
        return $fields[$field_name];
    }


    /**
     * Registers a module to demographics on install
     * Looks in the modules conf directory for a demographics
     * configuration file.
     */
    function register($module)
    {

        $file = sprintf('%smod/%s/boost/demographics.php', PHPWS_SOURCE_DIR, $module);

        if (!is_file($file)) {
            PHPWS_Boost::addLog($module, dgettext('demographics', 'Demographics file not implemented.'));
            return FALSE;
        }

        include $file;

        if (isset($fields) && is_array($fields)) {
            foreach ($fields as $field_name => $stats) {
                Demographics::registerField($field_name, $stats);
            }
        }

        if (isset($default) && is_array($default)) {
            foreach ($default as $field_name) {
                Demographics::registerDefaultField($field_name);
                PHPWS_Boost::addLog($module, sprintf(dgettext('demographics', '%s demographic field registered.'), $field_name));
            }
        }
        return TRUE;
    }

    /**
     * Registers a new field to the system
     */
    function registerField($field_name, $stats)
    {
        $current_fields = Demographics::getFields();

        // if the registered field is not already created, continue
        if (!in_array($field_name, $current_fields)) {
            // If the field is a default field, override whatever stats were sent
            if (Demographics::isDefaultField($field_name)) {
                $stats = Demographics::getDefaultStats($field_name);
                Demographics::createField($field_name, $stats);
            } elseif (!empty($stats)) {
                Demographics::createField($field_name, $stats);
            }
        }

        return TRUE;
    }

    function unregisterField($field_name)
    {
        $current_fields = Demographics::getFields();

        // if the registered field is not created, return
        if (!in_array($field_name, $current_fields)) {
            return TRUE;
        }

        $db = new PHPWS_DB('demographics');
        return $db->dropTableColumn($field_name);
    }

    /**
     * Registers one of Demographic's default fields
     */
    function registerDefaultField($field_name)
    {
        $current_fields = Demographics::getFields();

        // if the registered field is not already created, continue
        if (!in_array($field_name, $current_fields)) {
            if (Demographics::isDefaultField($field_name)) {
                $stats = Demographics::getDefaultStats($field_name);
                Demographics::createField($field_name, $stats);
            }
        }
        return TRUE;
    }

    /**
     * Creates a new field (column) for demographics
     * @modified Eloi George <adarkling at users dot sourceforge dot net>
     */
    function createField($field_name, $stats)
    {
        $stat_types = array('text', 'smallint', 'integer', 'boolean');

        if (!isset($stats['type']) || !in_array($stats['type'], $stat_types)) {
            $type = 'text';
        } else {
            $type = &$stats['type'];
        }

        if ($type == 'text') {
            if (isset($stats['limit'])) {
                $limit = (int)$stats['limit'];
            } else {
                $limit = DEMOGRAPHICS_DEFAULT_LIMIT;
            }
        }

        switch ($type) {
        case 'text':
            $parameter = sprintf('varchar(%s) default NULL', $limit);
            break;

        case 'boolean':
        case 'smallint':
            $parameter = 'smallint NOT NULL default 0';
            break;

        case 'integer':
            $parameter = 'int NOT NULL default 0';
            break;
        }

        $db = new PHPWS_DB('demographics');
        return $db->addTableColumn($field_name, $parameter);
    }

    /**
     * Removes a module's special fields when
     * uninstalled
     */
    function unregister($module)
    {
        $file = PHPWS_Core::getConfigFile($module, 'demographics.php');

        if (!is_file($file)) {
            PHPWS_Boost::addLog($module, dgettext('demographics', 'No demographics file found.'));
            return FALSE;
        }

        include $file;

        if (isset($fields) && is_array($fields)) {
            foreach ($fields as $field_name => $stats) {
                Demographics::unregisterField($field_name);
            }
        }

        return TRUE;
    }

    function getList($ids, $table=NULL, $class_name=NULL)
    {
        if (!is_array($ids)) {
            return FALSE;
        }

        if (isset($table)) {
            $db = new PHPWS_DB($table);
            $db->setDistinct(true);
            $db->addJoin('left', $table, 'demographics', 'user_id', 'user_id');
            $db->addColumn($table . '.*');
            $db->addColumn('demographics.*');
        } else {
            $db = new PHPWS_DB('demographics');
        }

        $db->addWhere('user_id', $ids);
        $db->setIndexBy('user_id');

        if ($class_name) {
            return $db->getObjects($class_name);
        } else {
            return $db->select();
        }
    }
}

?>
