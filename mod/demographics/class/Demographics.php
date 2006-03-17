<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Demographics {

    /**
     * Pulls a user's demographic information
     */
    function &getUser($user_id)
    {
        $demo_user = & new Demographics_User($user_id);
        if ($demo_user->_error) {
            return $demo_user->_error;
        } elseif (!$demo_user->user_id) {
            // return "no user" error here
            return NULL;
        }
    }

    /**
     * Returns the fields currently used in demographics
     */
    function getFields()
    {
        
    }

    /**
     * Creates a new field (column) for demographics
     */
    function createField($name)
    {

    }

    /**
     * Registers a module to demographics on install
     * Looks in the modules conf directory for a demographics
     * configuration file.
     */
    function register($module)
    {
        $file = PHPWS_Core::getConfigFile($module, 'demographics.php');

        if (!$file) {
            return FALSE;
        }

        include $file;

        if (!isset($fields)) {
            return FALSE;
        }
        
        foreach ($fields as $field) {

        }
    }

    /**
     * Removes a module's special fields when
     * uninstalled
     */
    function unregister($module)
    {

    }

}

class Demographics_User {
    var $user_id = 0;
    var $user_info = array();
    var $_demographics = NULL;
    var $_error = NULL;

    function Demographics_User($user_id=0) {
        if (!$user_id) {
            return;
        }

        $db = & new PHPWS_DB('demographics');
        $db->addWhere('user_id', (int)$user_id);
        $result = $db->select();
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }
        if (empty($result)) {
            
        }
        $this->user_id = (int)$user_id;
        $this->user_info = $result;
    }
}


?>