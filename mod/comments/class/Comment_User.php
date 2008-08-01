<?php

  /**
   * Stores the user information specific to comments
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @version $Id$
   */
PHPWS_Core::requireConfig('comments');
PHPWS_Core::initModClass('demographics', 'Demographics.php');

class Comment_User extends Demographics_User {

    public $display_name  = NULL;
    public $signature     = NULL;
    public $comments_made = 0;
    public $joined_date   = 0;
    public $avatar        = NULL;
    public $contact_email = NULL;
    public $website       = NULL;
    public $location      = NULL;
    public $locked        = 0;

    // using a second table with demographics
    public $_table        = 'comments_users';


    public function __construct($user_id=NULL)
    {
        if ($user_id == 0) {
            $this->loadAnonymous();
            return;
        }
        $this->user_id = (int)$user_id;
        $this->load();
    }


    public function loadAnonymous()
    {
        $this->display_name = DEFAULT_ANONYMOUS_TITLE;
    }


    public function setSignature($sig)
    {
        if (empty($sig)) {
            $this->signature = NULL;
            return true;
        }
        if (PHPWS_Settings::get('comments', 'allow_image_signatures')) {
            $this->signature = trim(strip_tags($sig, '<img>'));
        } else {
            if (preg_match('/<img/', $_POST['signature'])) {
                $this->_error[] = dgettext('comments', 'Image signatures not allowed.');
            }
            $this->signature = trim(strip_tags($sig));
        }
        return true;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function bumpCommentsMade()
    {
        if (!$this->user_id) {
            return;
        }

        $db = new PHPWS_DB($this->_table);
        $result = $db->incrementColumn('comments_made');
    }


    public function loadJoinedDate($date=NULL)
    {
        if (!isset($date)) {
            $this->joined_date = Current_User::getCreatedDate();
        } else {
            $this->joined_date = $date;
        }
    }

    public function getJoinedDate($format=false)
    {
        if ($format) {
            return strftime(COMMENT_DATE_FORMAT, $this->joined_date);
        } else {
            return $this->joined_date;
        }

    }

    public function setAvatar($avatar_url)
    {
        $this->avatar = $avatar_url;
    }

    public function getAvatar($format=true)
    {
        if (empty($this->avatar)) {
            return NULL;
        }
        if ($format) {
            return sprintf('<img src="%s" />', $this->avatar);
        } else {
            return $this->avatar;
        }
    }

    public function setContactEmail($email_address)
    {
        if (PHPWS_Text::isValidInput($email_address, 'email')) {
            $this->contact_email = $email_address;
            return true;
        } else {
            return false;
        }
    }

    public function getContactEmail($format=false)
    {
        if ($format) {
            return '<a href="mailto:' . $this->contact_email . '" />' . $this->display_name . '</a>';
        } else {
            return $this->contact_email;
        }
    }

    public function setWebsite($website)
    {
        $this->website = strip_tags($website);
    }

    public function getWebsite($format=false)
    {
        if ($format && isset($this->website)) {
            return sprintf('<a href="%s" title="%s">%s</a>',
                           $this->website,
                           sprintf(dgettext('comments', '%s\'s Website'), $this->display_name),
                           dgettext('comments', 'Website'));
        } else {
            return $this->website;
        }
    }

    public function setLocation($location)
    {
        $this->location = strip_tags($location);
    }

    public function lock()
    {
        $this->locked = 1;
    }

    public function unlock()
    {
        $this->locked = 0;
    }


    public function kill()
    {
        if (preg_match('/^images\/comments/', $this->avatar)) {
            @unlink($this->avatar);
        }

        $db = new PHPWS_DB('comments_items');
        $db->addWhere('author_id', $this->user_id);
        $db->addValue('author_id', 0);
        PHPWS_Error::logIfError($db->update());

        return $this->delete();
    }

    public function hasError()
    {
        return isset($this->_error);
    }

    public function getError()
    {
        return $this->_error;
    }

    public function logError()
    {
        if (PEAR::isError($this->_error)) {
            PHPWS_Error::log($this->_error);
        }
    }

    public function getTpl()
    {
        $template['AUTHOR_NAME']   = $this->display_name;
        $template['COMMENTS_MADE'] = $this->comments_made;

        $signature = $this->getSignature();

        if (!empty($signature)) {
            $template['SIGNATURE'] = $signature;
        }

        if (!empty($this->joined_date)) {
            $template['JOINED_DATE'] = $this->getJoinedDate(true);
            $template['JOINED_DATE_LABEL'] = dgettext('comments', 'Joined');
        }

        if ($this->locked) {
            $template['AVATAR'] = CM_LOCK_IMAGE;
            $template['AUTHOR_NAME'] .= sprintf(' <span class="smaller">(%s)</span>', dgettext('comments', 'Locked'));
        } else {
            if (isset($this->avatar)) {
                $template['AVATAR'] = $this->getAvatar();
            }
        }

        if (isset($this->contact_email)) {
            $template['CONTACT_EMAIL'] = $this->getContactEmail(true);
        }

        if (isset($this->website)) {
            $template['WEBSITE'] = $this->getWebsite(true);
        }

        if (isset($this->location)) {
            $template['LOCATION'] = $this->location;
            $template['LOCATION_LABEL'] = dgettext('comments', 'Location');
        }
        return $template;
    }

    /**
     * Saves user's options from the My Page Form
     */
    public function saveOptions()
    {
        $errors = array();

        $current_avatar = $this->avatar;
        $local_avatar = PHPWS_Settings::get('comments', 'local_avatars');

        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        if (PHPWS_Settings::get('comments', 'allow_signatures')) {
            $this->setSignature($_POST['signature']);
        } else {
            $this->signature = NULL;
        }

        if (!PHPWS_Settings::get('comments', 'allow_avatars') ||
            (!$local_avatar && empty($_POST['avatar']))) {
            if (!empty($current_avatar)) {
                @unlink($current_avatar);
            }
            $this->avatar = null;
        } else {
            if ($local_avatar) {
                $image = new PHPWS_Image;
                $image->setDirectory('images/comments/');
                $image->setMaxWidth(COMMENT_MAX_AVATAR_WIDTH);
                $image->setMaxHeight(COMMENT_MAX_AVATAR_HEIGHT);

                $prefix = sprintf('%s_%s_', Current_User::getId(), mktime());
                if (!$image->importPost('avatar', false, true, $prefix)) {
                    if (isset($image->_errors)) {
                        foreach ($image->_errors as $oError) {
                            $errors[] = $oError->getMessage();
                        }
                    }
                } elseif ($image->file_name) {
                    // checking file name in case they don't want an avatar
                    $result = $image->write();
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        $errors[] = dgettext('comments', 'There was a problem saving your image.');
                    } else {
                        if ($current_avatar != $image->getPath() && is_file($current_avatar)) {
                            @unlink($current_avatar);
                        }
                        $this->setAvatar($image->getPath());
                    }
                }
            } else {
                if ($this->testAvatar(trim($_POST['avatar']), $errors)) {
                    $this->setAvatar($_POST['avatar']);
                }
            }
        }

        if (isset($_POST['order_pref'])) {
            PHPWS_Cookie::write('cm_order_pref', (int)$_POST['order_pref']);
        }

        // need some error checking here
        if (empty($_POST['contact_email'])) {
            $this->contact_email = NULL;
        } else {
            if (!$this->setContactEmail($_POST['contact_email'])) {
                $errors[] = dgettext('comments', 'Your contact email is formatted improperly.');
            }
        }

        if (!empty($errors)) {
            $this->avatar = null;
            return $errors;
        } else {
            $this->saveUser();
            return true;
        }
    }

    public function saveUser()
    {
        if ($this->isNew()) {
            $user = new PHPWS_User($this->user_id);
            $this->display_name = $user->getDisplayName();
        }

        return $this->save();
    }

    /**
     * Tests an image's url to see if it is the correct file type,
     * dimensions, etc.
     */
    public function testAvatar($url, &$errors)
    {
        if (!preg_match('@^http:@', $url)) {
            $errors[] = dgettext('comments', 'Avatar graphics must be from offsite.');
            return false;
        }

        $ext = PHPWS_File::getFileExtension($url);
        if (!PHPWS_Image::allowImageType($ext)) {
            $errors[] = dgettext('comments', 'Unacceptable image file.');
            return false;
        }

        if (!PHPWS_File::checkMimeType($url, $ext)) {
            $errors[] = dgettext('comments', 'Unacceptable file type.');
            return false;
        }

        $test = @getimagesize($url);

        if (!$test || !is_array($test)) {
            $errors[] = dgettext('comments', 'Could not verify file dimensions.');
            return false;
        }


        if (COMMENT_MAX_AVATAR_WIDTH < $test[0] || COMMENT_MAX_AVATAR_HEIGHT < $test[1]) {
            $errors[] = sprintf(dgettext('comments', 'Your avatar must be smaller than %sx%spx.'),
                                COMMENT_MAX_AVATAR_WIDTH, COMMENT_MAX_AVATAR_HEIGHT);
            return false;
        }

        return true;
    }

}

?>