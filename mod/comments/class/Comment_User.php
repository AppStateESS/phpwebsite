<?php

  /**
   * Stores the user information specific to comments
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @version $Id$
   */
PHPWS_Core::requireConfig('comments');

class Comment_User {

    var $user_id       = NULL;
    var $display_name  = NULL;
    var $signature     = NULL;
    var $comments_made = 0;
    var $joined_date   = 0;
    var $picture       = NULL;
    var $contact_email = NULL;
    var $website       = NULL;
    var $location      = NULL;
    var $locked        = 0;
    var $_error        = NULL;

    function Comment_User($user_id=NULL)
    {
        if (is_null($user_id)) {
            return;
        } elseif ((int)$user_id == 0) {
            $this->loadAnonymous();
            return;
        }

        $this->user_id = (int)$user_id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->user_id = NULL;
            $this->_error = $result;
        } elseif (empty($result)) {
            if (!$this->createNewUser()) {
                $this->user_id = NULL;
            }
        }
    }

    function init()
    {
        $db = & new PHPWS_DB('comments_users');
        $db->addWhere('user_id', $this->user_id);
        return $db->loadObject($this);
    }

    function createNewUser()
    {
        $user = & new PHPWS_User($this->user_id);
        if (!$user->id) {
            return FALSE;
        }

        $this->display_name = $user->display_name;
        return $this->save(TRUE);
    }

    function loadAnonymous()
    {
        $this->display_name = DEFAULT_ANONYMOUS_TITLE;
    }

    function getUserId()
    {
        return $this->user_id;
    }

    function loadAll()
    {
        $this->loadUserId();
        $this->loadDisplayName();
        $this->loadJoinedDate();
    }

    function loadUserId()
    {
        $this->user_id = Current_User::getId();
    }

    function loadDisplayName()
    {
        $this->display_name = Current_User::getDisplayName();
    }

    function setSignature($sig)
    {
        if (empty($sig)) {
            $this->signature = NULL;
            return TRUE;
        }

        if (PHPWS_Settings::get('comments', 'allow_image_signatures')) {
            $this->signature = trim(strip_tags($sig, '<img>'));
        } else {
            if (preg_match('/<img/', $_POST['signature'])) {
                $this->_error[] = _('Image signatures not allowed.');
            }
            $this->signature = trim(strip_tags($sig));
        }

        return TRUE;
    }

    function getSignature()
    {
        return $this->signature;
    }

    function increaseCommentsMade()
    {
        $this->comments_made++;
    }

    function decreaseCommentsMade()
    {
        if ($this->comments_made > 0) {
            $this->comments_made--;
        }
    }

    function loadJoinedDate($date=NULL)
    {
        if (!isset($date)) {
            $this->joined_date = Current_User::getCreatedDate();
        } else {
            $this->joined_date = $date;
        }
    }

    function getJoinedDate($format=FALSE)
    {
        if ($format) {
            return strftime(COMMENT_DATE_FORMAT, $this->joined_date);
        } else {
            return $this->joined_date;
        }
    
    }

    function setPicture($picture_url)
    {
        $dimensions = @getimagesize($picture_url);
        if (!$dimensions) {
            return FALSE;
        }
        test($dimensions);
        // test dimension of graphic
        if (1) {
            $this->picture = $picture_url;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getPicture($format=TRUE)
    {
        if (empty($this->picture)) {
            return NULL;
        }
        return sprintf('<img src="%s" />', $this->picture);
    }

    function setContactEmail($email_address)
    {
        if (PHPWS_Text::isValidInput($email_address, 'email')) {
            $this->contact_email = $email_address;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getContactEmail($format=FALSE)
    {
        if ($format) {
            return '<a href="mailto:' . 
                PHPWS_Text::htmlEncodeText($this->contact_email) . 
                '" />' . $this->display_name . '</a>';
        } else {
            return $this->contact_email;
        }
    }

    function setWebsite($website)
    {
        $this->website = strip_tags($website);
    }

    function getWebsite($format=FALSE)
    {
        if ($format && isset($this->website)) {
            return sprintf('<a href="%s" title="%s">%s</a>',
                           $this->website,
                           sprintf(_('%s\'s Website'), $this->display_name),
                           _('Website'));
        } else {
            return $this->website;
        }
    }

    function setLocation($location)
    {
        $this->location = strip_tags($location);
    }

    function lock()
    {
        $this->locked = 1;
    }

    function unlock()
    {
        $this->locked = 0;
    }

    function save($new=FALSE)
    {
        $db = & new PHPWS_DB('comments_users');
        if (!$new) {
            $db->addWhere('user_id', $this->user_id);
        }
        return $db->saveObject($this);
    }

    function add()
    {

    }

    function kill()
    {
        $db = & new PHPWS_DB('comments_users');
        $db->addWhere('user_id', $this->user_id);
        return $db->delete();
    }

    function hasError()
    {
        return isset($this->_error);
    }

    function getError()
    {
        return $this->_error;
    }

    function logError()
    {
        if (PEAR::isError($this->_error)) {
            PHPWS_Error::log($this->_error);
        }
    }

    function getTpl()
    {
        $template['AUTHOR_NAME']   = $this->display_name;
        $template['COMMENTS_MADE'] = $this->comments_made;


        if (isset($this->signature)) {
            $template['SIGNATURE'] = $this->signature;
        }

        if (!empty($this->joined_date)) {
            $template['JOINED_DATE'] = $this->getJoinedDate(TRUE);
            $template['JOINED_DATE_LABEL'] = _('Joined');
        }

        if (isset($this->picture)) {
            $template['PICTURE'] = $this->getPicture();
        }

        if (isset($this->contact_email)) {
            $template['CONTACT_EMAIL'] = $this->getContactEmail(TRUE);
        }
    
        if (isset($this->website)) {
            $template['WEBSITE'] = $this->getWebsite(TRUE);
        }

        if (isset($this->location)) {
            $template['LOCATION'] = $this->location;
            $template['LOCATION_LABEL'] = _('Location');
        }
        return $template;
    }

    function saveOptions()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        if (PHPWS_Settings::get('comments', 'allow_signatures')) {
            $this->setSignature($_POST['signature']);
        }
     
        if (empty($_POST['picture'])) {
            $val['picture'] = NULL;
        } else {
            $image_info = @getimagesize($_POST['picture']);
            if (!$image_info) {
                $errors[] = _('Could not access image url.');
            }
        }

        if (PHPWS_Settings::get('comments', 'allow_avatars')) {
            if (PHPWS_Settings::get('comments', 'local_avatars')) {
                $image = & new PHPWS_Image;
                $image->setDirectory('images/comments/');
                $image->setMaxWidth(COMMENT_MAX_AVATAR_WIDTH);
                $image->setMaxHeight(COMMENT_MAX_AVATAR_HEIGHT);
                
                if (!$image->importPost('picture')) {
                    foreach ($image->_errors as $oError) {
                        $errors[] = $oError->getMessage();
                    }
                } else {
                    $result = $image->write();
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        $errors[] = array(_('There was a problem saving your image.'));
                    } else {
                        $this->picture = $image->getPath();
                    }
                }
            } else {
                $result = $this->testAvatar(trim($_POST['picture']));
            }
        }

        if (isset($errors)) {
            return $errors;
        } else {
            $this->save();
            return TRUE;
        }
    }

    function testAvatar($url)
    {
        $test = @getimagesize($url);
        if (!$test) {
            return FALSE;
        }

        test($test);
    }

}

?>