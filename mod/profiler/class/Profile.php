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

PHPWS_Core::configRequireOnce('profiler', 'config.php');

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
            $this->_db = & new PHPWS_DB('profiles');
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

    function setProfileType($profile_type)
    {
        $this->profile_type = (int)$profile_type;
    }

    function setSubmitDate()
    {
        $this->submit_date = mktime();
    }

    function getFullstory($formatted=TRUE)
    {
        if ($formatted) {
            return PHPWS_Text::parseOutput($this->fullstory);
        } else {
            return $this->fullstory;
        }
    }

    function getProfileType()
    {
        switch ($this->profile_type) {
        case PFL_STUDENT:
            return _('Student');
            break;
        case PFL_FACULTY:
            return _('Faculty');
            break;
        case PFL_STAFF:
            return _('Staff');
            break;

        default:
            return _('Profile not set.');
            break;
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
        PHPWS_Core::initCoreClass('Image.php');
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
        $this->setProfileType($_POST['profile_type']);

        if (!empty($_FILES)) {

            // Save full photo
            $full_photo = & new PHPWS_Image;
            $full_photo->setModule('profiler');
            $result = $full_photo->importPost('full_photo', TRUE);

            if ($result) {
                if (is_array($result)) {
                    foreach ($result as $img_error) {
                        $error[] = sprintf(_('Photo image error - %s'), $img_error->getMessage());
                    }
                } else {
                    $result = $full_photo->save();
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        $error[] = _('There was a problem saving photo.');
                    } else {
                        $this->full_photo = $full_photo->getId();
                    }
                }
            }

            // Save thumbnail
            $thumbnail = & new PHPWS_Image;
            $thumbnail->setModule('profiler');
            $result = $thumbnail->importPost('thumbnail', TRUE);

            if ($result) {
                if (is_array($result)) {
                    foreach ($result as $img_error) {
                        $error[] = sprintf(_('Thumbnail image error - %s'), $img_error->getMessage());
                    }
                } else {
                    $result = $thumbnail->save();
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        $error[] = _('There was a problem saving the thumbnail.');
                    } else {
                        $this->thumbnail = $thumbnail->getId();
                    }
                }
            }
        } elseif (isset($_POST['full_photo_id'])) {
            $this->full_photo = (int)$_POST['full_photo_id'];
        }

        if (empty($this->submitted_date)) {
            $this->setSubmitDate();
        }

        if (empty($this->contributor)) {
            $this->contributor = Current_User::getUsername();
        }

        if (isset($_REQUEST['version_id'])) {
            $version = & new Version('profiles', $_REQUEST['version_id']);
        } else {
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

    function getProfileTags()
    {
        $tpl['PROFILE_TYPE'] = $this->getProfileType();

        $vars['profile_id'] = $this->id;
        $vars['command'] = 'edit';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'profiler', $vars);

        $tpl['SUBMIT_DATE'] = strftime(PRF_SUBMIT_DATE_FORMAT, $this->submit_date);

        if (Current_User::allow('profiler', 'delete_profiles')){
            $vars['command'] = 'delete';
            $confirm_vars['QUESTION'] = _('Are you sure you want to permanently delete this profile?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('profiler', $vars, TRUE);
            $confirm_vars['LINK'] = _('Delete');
            $links[] = Layout::getJavascript('confirm', $confirm_vars);
        }

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }
    
}


?>