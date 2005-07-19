<?php

/**
 * Contains profile information on subject
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

define('PFL_STUDENT', 1);
define('PFL_FACULTY', 2);
define('PFL_STAFF',   3);


// Error defines
define('PFL_PROFILE_NOT_FOUND', 1);

class Profile {
    var $id           = 0;
    var $firstname    = NULL;
    var $lastname     = NULL;
    var $full_photo   = 0;     // Id to the photo
    var $thumbnail    = 0;     // Id to thumbnail
    var $fullstory    = NULL;  // Complete prose to profile
    var $caption      = NULL;  // Abbreviated intro to the profile
    var $profile_type = 0;     // Profile type number, see defines above
    var $keywords     = NULL;  // Searchable words to find a profile
    var $submit_date  = 0;     // Date of profile creation
    var $contributor  = NULL;  // Name of contributor
    var $_error       = NULL;  // Error object holder
    var $_db          = NULL;  // Database object

    function Profile($id=NULL)
    {
        if (empty($id)) {
            return TRUE;
        }

        $this->setId($id);
        $result = $this->init();
        if (PEAR::isError($result)) {
            test($result);
            $this->_error = $result;
            return FALSE;
        }
        return TRUE;
    }

    function resetdb()
    {
        if (isset($this->_db)) {
            $this->_db->reset();
        } else {
            $this->_db = & new PHPWS_DB('menus');
        }
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function setFirstName($firstname)
    {
        $this->firstname = preg_replace('/\W/', '', trim($firstname));
    }

    function setLastName($lastname)
    {
        $this->lastname = preg_replace('/\W/', '', trim($lastname));
    }

    function setCaption($caption)
    {
        $this->caption = PHPWS_Text::parseInput($caption);
    }

    function getCaption($formatted=TRUE)
    {
        if ($formatted) {
            return PHPWS_Text::parseOutput($this->caption);
        } else {
            return $this->caption;
        }
    }

    function setFullstory($fullstory)
    {
        $this->fullstory = PHPWS_Text::parseInput($fullstory);
    }

    function getFullstory($formatted=TRUE)
    {
        if ($formatted) {
            return PHPWS_Text::parseOutput($this->fullstory);
        } else {
            return $this->fullstory;
        }
    }

    function init()
    {
        $this->resetdb();
        $result = $this->_db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        } elseif (empty($result)) {
            return PHPWS_Error::get(PFL_PROFILE_NOT_FOUND, 'profiler', 
                                    'Profile::init', 'Id:' . $this->id);
        }
        return TRUE;
    }

    function postProfile()
    {
        PHPWS_Core::initModClass('version', 'Version.php');

        if (!Current_User::authorized('profiler')) {
            Current_User::disallow();
            return FALSE;
        }

        if (!isset($_POST['profile_id']) && PHPWS_Core::isPosted()) {
            return TRUE;
        }

        if (empty($_POST['firstname'])) {
            $error[] = _('Please enter a first name.');
        }

        $this->setFirstName($_POST['firstname']);

        if (empty($_POST['lastname'])) {
            $error[] = _('Please enter a last name.');
        }

        $this->setLastName($_POST['lastname']);

        $this->setCaption($_POST['caption']);
        $this->setFullStory($_POST['fullstory']);

        if (empty($this->contributor)) {
            $this->contributor = Current_User::getUsername();
        }

        if (isset($_REQUEST['version_id'])) {
            $version = & new Version('profiles', $_REQUEST['version_id']);
        }
        else {
            $version = & new Version('profiles');
        }

        if (isset($error)) {
            return $error;
        }

        $version->setSource($this);
        // User is restricted, everything is unapproved
        // from them
        if (Current_User::isRestricted('profiler')) {
            $version->setApproved(FALSE);
        } else {
            $version->setApproved(TRUE);
        }

        $result = $version->save();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        }

        $this->id = $version->getSourceId();
        return TRUE;
    }


}


?>