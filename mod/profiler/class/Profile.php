<?php

/**
 * Contains profile information on subject
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::configRequireOnce('profiler', 'config.php');

// Error defines
define('PFL_PROFILE_NOT_FOUND', 1);

class Profile {
    var $id              = 0;
    var $firstname       = NULL;
    var $lastname        = NULL;
    var $photo_large     = 0;     // Id to the photo
    var $photo_medium    = 0;     // Id to the photo
    var $photo_small     = 0;     // Id to the photo
    var $fullstory       = NULL;  // Complete prose to profile
    var $caption         = NULL;  // Abbreviated intro to the profile
    var $profile_type    = 0;     // Profile type number, see defines above
    var $keywords        = NULL;  // Searchable words to find a profile
    var $submit_date     = 0;     // Date of profile creation
    var $contributor     = NULL;  // Name of contributor
    var $contributor_id  = 0;
    var $approved        = 0;
    var $_error          = NULL;  // Error object holder
    var $_db             = NULL;  // Database object
    var $_division_title = NULL;

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

    function display($template_name)
    {
        Layout::addStyle('profiler');

        $images = $this->loadImages();
        
        $template_name = preg_replace('/\W/', '', $template_name);

        $template['FIRST_NAME'] = $this->firstname;
        $template['LAST_NAME'] = $this->lastname;
        if (!empty($images['small'])) {
            $image = & $images['small'];
            $template['PHOTO_SMALL'] = $images['small']->getTag(TRUE);
        }

        if (!empty($images['medium'])) {
            $template['PHOTO_MEDIUM'] = $images['medium']->getTag(TRUE);
        }

        if (!empty($images['large'])) {
            $template['PHOTO_LARGE'] = $images['large']->getTag(TRUE);
        }

        $template['FULLSTORY'] = $this->getFullstory();
        $template['CAPTION'] = $this->getCaption();

        $template['READ_MORE'] = sprintf('<a href="%s">%s</a>', 
                                         PHPWS_Core::getHomeHttp() . 'index.php?module=profiler&amp;user_cmd=view_profile&amp;id=' . $this->id,
                                         _('Read more. . .'));

        return PHPWS_Template::process($template, 'profiler', 'views/' . $template_name . '.tpl');
    }

    function loadImages()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $images['small'] = $images['medium'] = $images['large'] = NULL;

        if ($this->photo_small) {
            $images['small'] = & new PHPWS_Image($this->photo_small);
        }

        if ($this->photo_medium) {
            $images['medium'] = & new PHPWS_Image($this->photo_medium);
        }
 
        if ($this->photo_large) {
            $images['large'] = & new PHPWS_Image($this->photo_large);
        }

        return $images;
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
        $this->firstname = preg_replace('/[^\w\s]/', '', trim($firstname));
    }

    function setLastName($lastname)
    {
        $this->lastname = preg_replace('/^\w\s/', '', trim($lastname));
    }

    function setCaption($caption)
    {
        $this->caption = PHPWS_Text::parseInput($caption);
    }

    function getCaption($formatted=TRUE)
    {
        if ($formatted) {
            return PHPWS_Text::parseTag(PHPWS_Text::parseOutput($this->caption));
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
            return PHPWS_Text::parseTag(PHPWS_Text::parseOutput($this->fullstory));
        } else {
            return $this->fullstory;
        }
    }

    function getProfileType()
    {
        static $all_profiles = array();

        if (empty($all_profiles)) {
            $div = & new PHPWS_DB('profiler_division');
            $div->addWhere('show_homepage', 1);
            $div->addOrder('title');
            $div->addColumn('id');
            $div->addColumn('title');
            $div->setIndexBy('id');
            $all_profiles = $div->select('col');
        }

        if (isset($all_profiles[$this->profile_type])) {
            return $all_profiles[$this->profile_type];
        } else {
            return _('Profile not set.');
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
        PHPWS_Core::initModClass('filecabinet', 'Image.php');

        if (!Current_User::authorized('profiler')) {
            Current_User::disallow();
            return FALSE;
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

        $this->photo_large  = (int)$_POST['photo_large'];
        $this->photo_medium = (int)$_POST['photo_medium'];
        $this->photo_small  = (int)$_POST['photo_small'];

        if (empty($this->submitted_date)) {
            $this->setSubmitDate();
        }

        if (empty($this->contributor_id)) {
            $this->contributor_id = Current_User::getId();
            $this->contributor = Current_User::getUsername();
        }

        if (isset($_POST['version_id'])) {
            $this->approved = 0;
        } elseif (Current_User::isRestricted('profiler')) {
            $this->approved = 0;
        } else {
            $this->approved = 1;
        }

        if (isset($error)) {
            return $error;
        } else {
            return TRUE;
        }
    }

    function getProfileTags()
    {
        //        $tpl['PROFILE_TYPE'] = $this->getProfileType();
        $tpl['PROFILE_TYPE'] = $this->_division_title;

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
  
    function delete()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        $this->resetdb();
        $this->_db->addWhere('id', $this->id);
        $result = $this->_db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }

        return Version::flush('profiles', $this->id);
    }

    function save()
    {
        PHPWS_Core::initModClass('version', 'Version.php');

        if ($this->approved || !$this->id) {
            $this->resetdb();
            $result = $this->_db->saveObject($this);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        $version = & new Version('profiles');
        $version->setSource($this);
        $version->setApproved($this->approved);
        return $version->save();
    }
  
}


?>