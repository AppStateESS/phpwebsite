<?php
/**
 * Demographics user is a class that you extend to store user
 * information.
 * If you set a _table variable, demographics will load information
 * from that table as well. Not having a _table saves all the information
 * in demographics
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


class Demographics_User {
    public $user_id        = 0;
    public $display_name   = null;
    public $last_logged    = 0;
    public $created        = 0;
    public $email          = null;
    public $active_user    = 1;
    public $_error         = null;
    public $_base_id       = 0;
    public $_extend_id     = 0;
    // indicates a new demographics user
    public $_new_user      = true;
    public $_table         = null;

    public function load()
    {
        if (!$this->user_id) {
            return;
        }

        if (isset($this->_table)) {
            $db = new PHPWS_DB('users');
            $db->addJoin('left', 'demographics', 'users', 'user_id', 'id');
            $db->addJoin('left', $this->_table, 'users', 'user_id', 'id');
            $db->addWhere('users.id', $this->user_id);
            $db->addColumn($this->_table . '.*');
            $db->addColumn('demographics.*');
            $db->addColumn('users.display_name');
            $db->addColumn('users.last_logged');
            $db->addColumn('users.created');
            $db->addColumn('users.email');
            $db->addColumn('users.active', null, 'active_user');
            $db->addColumn($this->_table . '.user_id', null, '_extend_id');
            $db->addColumn('demographics.user_id', null, '_base_id');
        } else {
            $db = new PHPWS_DB('demographics');
            $db->addWhere('demographics.user_id', (int)$this->user_id);
        }

        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            $this->_error = $result;
            return false;
        } elseif ($result) {
            $this->_new_user = !(bool)$this->_extend_id;
        } else {
            $this->_new_user = true;
        }
        return true;
    }

    /**
     * Returns whether this is a new EXTENDED demographics user. If
     * the extended is removed, the original remains but is not considered
     * "new"
     */
    public function isNew()
    {
        return $this->_new_user;
    }

    /**
     * Updates a current user demographic
     */
    public function save()
    {
        if (!$this->user_id) {
            return false;
        }

        $db = new PHPWS_DB('demographics');
        if ($this->_base_id) {
            $db->addWhere('user_id', $this->_base_id);
        }

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return $result;
        }
        $this->_base_id = $this->user_id;

        if (isset($this->_table)) {
            $db = new PHPWS_DB($this->_table);
            if ($this->_extend_id) {
                $db->addWhere('user_id', $this->_extend_id);
            }

            $result = $db->saveObject($this);
            if (PEAR::isError($result)) {
                $this->_error = $result;
                return $result;
            }
            $this->_extend_id = $this->user_id;

        }
        return true;
    }

    /**
     * Delete a demographics user and, if the table is set,
     * the information that extends it.
     */
    public function delete($all_user_info=false)
    {
        if (!$this->user_id) {
            return false;
        }

        if  ($all_user_info) {
            $db = new PHPWS_DB('demographics');
            $db->addWhere('user_id', $this->user_id);
            $result = $db->delete();
            if (PEAR::isError($result)) {
                $this->_error = $result;
                return $result;
            }
        }

        if (isset($this->_table)) {
            $db = new PHPWS_DB($this->_table);
            $db->addWhere('user_id', $this->user_id);

            $result = $db->delete();
            if (PEAR::isError($result)) {
                $this->_error = $result;
                return $result;
            }
        }

        return true;
    }
}

?>