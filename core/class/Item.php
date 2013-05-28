<?php

/**
 * Base class for items used in phpWebSite's 0.x  modules.
 *
 * This class exists to supply backward compatibility with 0.x
 * modules.
 *
 * @version $Id$
 * @author  Steven Levin <steven at tux dot appstate dot edu>
 * @author  Adam Morton
 * @author  Matthew McNaney <mcnaney at gmail dot com>
 * @package Core
 */

if (!defined('PHPWS_DATE_FORMAT')) {
    define('PHPWS_DATE_FORMAT', 'm-d-Y');
}

if (!defined('PHPWS_TIME_FORMAT')) {
    define('PHPWS_TIME_FORMAT', 'H:i');
}

class PHPWS_Item {

    /**
     * Database id of this item.
     *
     * @var     integer
     * @example $this->_id = 2;
     * @access  private
     */
    var $_id = NULL;

    var $_label = NULL;

    /**
     * The username of the user who currently owns this item.
     *
     * @var     integer
     * @example $this->_owner = 'steven';
     * @access  private
     */
    var $_owner = NULL;

    var $_hidden = 0;

    /**
     * The username of the user who last updated this item.
     *
     * @var integer
     * @example $this->_editor = 'admin';
     * @access private
     */
    var $_editor = NULL;

    /**
     * The IP address of the last person to create or update this item.
     *
     * Must be a valid IPv4 or IPv6 address.
     *
     * @var     string
     * @example $this->_ip = '127.0.0.1';
     * @access  private
     */
    var $_ip = NULL;

    /**
     * The date and time this item was created.
     *
     * Must be in unix date/time format.
     *
     * @var     string
     * @example $this->_created = time();
     * @access  private
     */
    var $_created = NULL;

    /**
     * The date and time this item was last updated.
     *
     * Must be in unix date/time format.
     *
     * @var     string
     * @example $this->_updated = time();
     * @access  private
     */
    var $_updated = NULL;

    /**
     * A boolean designating whether this item is active in the system.
     *
     * @var     integer
     * @example $this->_active = 1;
     * @access  private
     */
    var $_active = 1;

    /**
     * A boolean designating whether this item has been approved or not.
     *
     * @var     integer
     * @example $this->_approved = 1;
     * @access  private
     */
    var $_approved = 1;

    /**
     * The table name this item should store and access data from.
     *
     * Must be a valid table in the database and be sql 'friendly'.
     *
     * @var     string
     * @example $this->_table = 'myitem_table';
     * @access  private
     */
    var $_table = NULL;

    /**
     * List of variables which to exclude from commit
     *
     * An object which extends this class must add excludes via
     * <code>$this->add_exclude();</code>
     *
     * @var     array
     * @example $this->_exclude = array('_exclude', '_table', '_id');
     * @access  private
     */
    var $_exclude = array('_exclude', '_table', '_id');

    /**
     * Loads all data for this item and the object which called this function.
     *
     * This function will use the id number and table name to retrieve data on an item
     * from the database.  If no id or table name is supplied, then this new item and the new
     * object which called this function have all variables initialized to NULL. If an id and
     * table name ARE supplied, the data is retrieved, processed, and used to initialize this
     * item as well as the object that called this function.  Column names in the table in the
     * database must coorespond to the variable names in the object which called this function.
     * Any values from the database with no cooresponding variable are ignored and passed to the
     * child object. Underscores preceding an objects member variables are ignored.
     *
     * NOTE: setId() and  setTable() must be called before init or else it
     * will result in failure.
     *
     * @return  TRUE if successful, pear error otherwise
     * @access public
     */
    function init() {
        if((isset($this->_id) && isset($this->_table))) {
            $DB = new PHPWS_DB($this->_table);
            $DB->addWhere('id', (int)$this->_id);

            $className = get_class($this);
            $itemResult = $DB->select('row');

            if (PHPWS_Error::isError($itemResult)) {
                return $itemResult;
            }

            if (!isset($itemResult)) {
                return PHPWS_Error::get(PHPWS_ITEM_NO_RESULT, 'core', 'PHPWS_Item::init');
            } else {
                foreach ($itemResult as $key => $value) {
                    if (preg_match('/^[oa]:\d/i', $value)) {
                        $new_val = unserialize($value);
                        if (is_array($new_val) || is_object($new_val)) {
                            $value = $new_val;
                        }
                    }
                    $this->{'_' . $key} = $value;
                }
            }
        } else {
            return PHPWS_Error::get(PHPWS_ITEM_ID_TABLE, 'core', 'PHPWS_Item::init');
        }

        return TRUE;
    } // END FUNC init

    /**
     * Saves this item and the object which calls this function to the database.
     *
     * This function will save the contents of this item and the contents of the
     * object which called this function to the table specified via the $this->_table
     * variable.  The column names in the table must coorespond to the variable
     * names in this object and the object which called this function.  Any variable
     * that appears which does not have a cooresponding column in the database will
     * be ignored. Underscores preceding private variables in the object will be
     * removed before interpreting the column name.
     *
     * @param  boolean Flag whether or not to call set function for the item
     * @param  array   Extra variables to be saved to the database, must be an
     *                 associative array keyed by the table column
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function commit($set=TRUE, $extras=NULL) {
        if($set) {
            if(!empty($this->_id)) {
                $this->setUpdated();
                $this->setEditor();
            } else {
                $this->setCreated();
                $this->setUpdated();
                $this->setOwner();
                $this->setEditor();
            }
            $this->setIp();
        }

        $db = new PHPWS_DB($this->_table);
        if (!empty($this->_id)) {
            $db->addWhere('id', $this->_id);
        }

        return $db->saveObject($this, TRUE);
    } // END FUNC commit

    /**
     * Remove this item from the database
     *
     * Removes the current Item from the database if $this->_id is set properly.
     * Items which extend must provide the approval if necessary.
     *
     * @return mixed  TRUE on success and a Pear Error on false
     * @access public
     */
    function kill() {
        if(isset($this->_id) && isset($this->_table)) {
            $DB = new PHPWS_DB($this->_table);
            $DB->addWhere('id', $this->_id);
            $result = $DB->delete();
            return (PHPWS_Error::isError($result) ? $result : TRUE);
        } else
        return PHPWS_Error::get(PHPWS_ITEM_ID_TABLE, 'core', 'PHPWS_Item::kill');
    } // END FUNC kill

    /**
     * Sets all member variables.
     *
     * This function takes an associative array and uses it to set the member
     * variables for this item with the values found in the array.  This is done via
     * the set functions for this item.  The set functions will do any error checking
     * on the variables and set them accordingly.  If any variable is invalid, a FALSE
     * is returned and this item remains unchanged.
     *
     * @param  array   $vars The associative array of variables.
     * @return boolean TRUE on success and a pear Error on failure.
     * @access public
     */
    function setVars($vars = NULL) {
        $classVars = get_class_vars(get_class($this));

        if(is_array($vars) && is_array($classVars)) {
            foreach($vars as $key => $value) {
                /* checking to see if the key passed exists as a variable (with or without _ ) for this object */
                if(array_key_exists($key, $classVars)) {
                    $this->$key = $value;
                    return TRUE;
                } else {
                    $key = '_' . $key;
                    if(array_key_exists($key, $classVars)) {
                        $this->$key = $value;
                        return TRUE;
                    }
                }
            }
        } else {
            $error = 'Argument passed was not an array in PHPWS_Item::setVars()';
            return PEAR::raiseError($error);
        }
    } // END FUNC setVars

    /**
     * Sets the database id for this item.
     *
     * The $id passed in will be checked to make sure it is an integer.
     *
     * @param  integer $id The integer to set this item's database id to.
     * @return boolean TRUE on success and pear error on failure.
     * @access public
     */
    function setId($id) {
        $this->_id = (int)$id;
    } // END FUNC setId

    /**
     * Sets the owner of this item.
     *
     * Check to see if the user session exists and sets the owner to the username
     * in that session
     *
     * @return boolean TRUE on success and pear error on failure.
     * @access public
     */
    function setOwner() {
        if(isset($_SESSION['PHPWS_User'])) {
            if(isset($_SESSION['PHPWS_User']->username)) {
                $this->_owner = $_SESSION['PHPWS_User']->username;
            } else {
                $this->_owner = NULL;
                $error = 'The user session did not contain a name in PHPWS_Item::setOwner().';
                return PEAR::raiseError($error);
            }
        } else {
            $this->_owner = NULL;
            $error = 'The user session was not available in PHPWS_Item::setOwner().';
            $er = new PEAR;
            return $er->raiseError($error);
        }
    } // END FUNC setOwner

    /**
     * Sets editor.
     *
     * Check to see if the user session exists and sets the owner to the username
     * in that session
     *
     * @return boolean TRUE on success and pear error on failure.
     * @access public
     */
    function setEditor() {
        if(isset($_SESSION['PHPWS_User'])) {
            if(isset($_SESSION['PHPWS_User']->username)) {
                $this->_editor = $_SESSION['PHPWS_User']->username;
            } else {
                $this->_editor = NULL;
                $error = 'The user session did not contain a username in PHPWS_Item::setEditor().';
                return PEAR::raiseError($error);
            }
        } else {
            $this->_editor = NULL;
            $error = 'The user session was not available in PHPWS_Item::setEditor().';
            $er = new PEAR;
            return $er->raiseError($error);
        }
    } // END FUNC set_editor

    /**
     * Sets the IP address for this item.
     *
     * Makes sure the $ip passed in is a valid IPv4 or IPv6 address. Uses the
     * PEAR Net_CheckIP package to validate IPv4 addresses and the Net_IPv6 package
     * to validate IPv6 addresses.
     *
     * @return boolean TRUE on success and pear error on failure.
     * @access public
     */
    function setIp() {
        if(isset($_SERVER['REMOTE_ADDR'])) {
            if(class_exists('Net_CheckIP')) {
                if(Net_CheckIP::check_ip($_SERVER['REMOTE_ADDR'])) {
                    $this->_ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $this->_ip = NULL;
                    $error = 'The remote address provided was not valid in PHPWS_Item::setIp().';
                    return PEAR::raiseError($error);
                }
            } else {
                $this->_ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            $this->_ip = NULL;
            $error = 'No remote address was available to set the ip in PHPWS_Item::setIp.';
            return PEAR::raiseError($error);
        }
    } // END FUNC setIp


    /**
     * Sets the textual label for this item.
     *
     * Makes sure the label is a valid string and does not contain php or
     * unallowed html tags.
     *
     * @param  string  $label The string to set this item's label to.
     * @return boolean TRUE on success and PHPWS_Error on failure.
     * @access public
     */
    function setLabel($label = NULL) {
        if($label) {
            $this->_label = PHPWS_Text::parseInput($label);
        } else {
            $error = "No label was requested.";
            return new PHPWS_Error("core", "PHPWS_Item::setLabel()", $error);
        }
    } // END FUNC set_label


    /**
     * Sets the created date for this item.
     *
     * Uses the unix timestamp for date records.
     *
     * @access public
     */
    function setCreated() {
        $this->_created = time();
    } // END FUNC setCreated

    /**
     * Sets the updated date for this item.
     *
     * Uses the unix timestamp for date records.
     *
     * @access public
     */
    function setUpdated() {
        $this->_updated = time();
    } // END FUNC setUpdated

    /**
     * Sets the hidden flag for this item.
     *
     * Makes sure the $flag passed in is a valid boolean variable.
     *
     * @param  boolean $flag A boolean TRUE or FALSE designating whether or not this item is hidden.
     * @return boolean TRUE
     * @access public
     */
    function setActive($flag = TRUE) {
        if($flag) {
            $this->_active = 1;
        } else {
            $this->_active = 0;
        }

        return TRUE;
    }
    /**
     * Sets the hidden flag for this item.
     *
     * Makes sure the $flag passed in is a valid boolean variable.
     *
     * @param  boolean $flag A boolean TRUE or FALSE designating whether or not this item is hidden.
     * @return boolean TRUE
     * @access public
     */
    function setHidden($flag = TRUE) {
        if($flag) {
            $this->_hidden = 1;
        } else {
            $this->_hidden = 0;
        }

        return TRUE;
    } // END FUNC set_hidden


    function setApproved($flag = TRUE) {
        if($flag) {
            $this->_approved = 1;
        } else {
            $this->_approved = 0;
        }

        return TRUE;
    } // END FUNC setApproved

    /**
     * Sets the table name for this item.
     *
     * Makes sure the name passed in is a valid string and an sql 'friendly' name.
     *
     * @param  string $table The name of the table
     * @return TRUE on success and pear error on failure
     * @access public
     */
    function setTable($table = NULL) {
        if(is_string($table)) {
            $this->_table = $table;
        } else {
            $error = 'Table name passed was not a string in PHPWS_Item::setTable().';
            return PEAR::raiseError($error);
        }
    } // END FUNC setTable

    /**
     * Adds variable names to the exclude list
     *
     * Make sure $list past in is an array
     *
     * @param  array   $list items which to exclude from child object on commit.
     * @return boolean TRUE on success pear error on failure.
     * @access public
     */
    function addExclude($list = NULL) {
        if(is_array($list))
        $this->_exclude = array_merge($this->_exclude, $list);
        else {
            $error = 'Argument passed was not an array in PHPWS_Item::addExclude().';
            return PEAR::raiseError($error);
        }
    } // END FUNC addExclude

    function getExclude(){
        return $this->_exclude;
    }


    /**
     * Returns the current database id of this item.
     *
     * @return integer The database id of this item.
     * @access public
     */
    function getId() {
        if(isset($this->_id))
        return $this->_id;
        else
        return NULL;
    } // END FUNC getId

    /**
     * Returns the user id of the user who owns (created) this item.
     *
     * @return integer The username of the user who owns this item.
     * @access public
     */
    function getOwner() {
        if(isset($this->_owner))
        return $this->_owner;
        else
        return NULL;
    } // END FUNC getOwner

    /**
     * Returns the id of the last user to edit this item.
     *
     * @return integer The username of the last user to edit this item.
     * @access public
     */
    function getEditor() {
        if(isset($this->_editor))
        return $this->_editor;
        else
        return NULL;
    } // END FUNC getEditor

    /**
     * Returns the current ip address of this item.
     *
     * @return string The ip address of the last user who created or updated this item.
     * @access public
     */
    function getIp() {
        if(isset($this->_ip))
        return $this->_ip;
        else
        return NULL;
    } // END FUNC getIp

    /**
     * Returns the current textual label of this item.
     *
     * @return string The textual label of this item in string format.
     * @access public
     */
    function getLabel() {
        if(isset($this->_label))
        return $this->_label;
        else
        return NULL;
    } // END FUNC getLabel

    /**
     * Returns the current created date of this item.
     *
     * Uses PHPWS_DATE_FORMAT and PHPWS_TIME_FORMAT defined in the datesettings.en.php
     *
     * @return string The created date of this item in the sql 'datetime' format.
     * @access public
     */
    function getCreated() {
        if(isset($this->_created))
        return date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $this->_created);
        else
        return NULL;
    } // END FUNC getCreated

    /**
     * Returns the current updated date of this item.
     *
     * Uses PHPWS_DATE_FORMAT and PHPWS_TIME_FORMAT defined in the datesettings.en.php
     *
     * @return string The updated date of this item in the sql 'datetime' format.
     * @access public
     */
    function getUpdated() {
        if(isset($this->_updated))
        return date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $this->_updated);
        else
        return NULL;
    } // END FUNC getUpdated

    /**
     * Returns the current table name for this item.
     *
     * @return string The table name for this item.
     * @access public
     */
    function getTable() {
        if(isset($this->_table))
        return $this->_table;
        else
        return NULL;
    } // END FUNC getTable

    /**
     * Returns TRUE if this item is hidden and FALSE if it is not.
     *
     * @return boolean TRUE if hidden FALSE if not hidden.
     * @access public
     */
    function isHidden() {
        if(isset($this->_hidden)) return $this->_hidden;
        else return NULL;
    } // END FUNC isHidden


    /**
     * Returns TRUE if this item is hidden and FALSE if it is not.
     *
     * @return boolean TRUE if hidden FALSE if not hidden.
     * @access public
     */
    function isActive() {
        if(isset($this->_active) && $this->_active)
        return TRUE;
        else
        return FALSE;
    } // END FUNC isActive

    /**
     * Returns TRUE if this item is approved or FALSE if it is not.
     *
     * @return boolean TRUE if approved, FALSE if not approved.
     * @access public
     */
    function isApproved() {
        if(isset($this->_approved) && $this->_approved)
        return TRUE;
        else
        return FALSE;
    } // END FUNC isApproved

    function set($name, $value) {
        $vars = get_object_vars($this);
        $pri = "_{$name}";
        $pub = "{$name}";
        if(array_key_exists($pri, $vars)) {
            $this->$pri = $value;
        } else if(array_key_exists($pub, $vars)) {
            $this->$pub = $value;
        }
    }

    function get($name) {
        $vars = get_object_vars($this);
        $pri = "_{$name}";
        $pub = "{$name}";
        if(array_key_exists($pri, $vars)) {
            return $this->$pri;
        } else if(array_key_exists($pub, $vars)) {
            return $this->$pub;
        }
    }


    function debug(){
        return phpws_debug::testobject($this);
    }


}// END CLASS PHPWS_Item

?>