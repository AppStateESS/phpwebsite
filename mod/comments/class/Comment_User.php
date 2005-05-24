<?php

/**
 * Stores the user information specific to comments
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

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
      $this->user_id = NULL;
    }
  }

  function init()
  {
    $db = & new PHPWS_DB('comments_users');
    $db->addWhere('user_id', $this->user_id);
    return $db->loadObject($this);
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
    $this->signature = PHPWS_Text::parseInput(strip_tags($sig));
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

  function getPicture()
  {
    return $this->picture;
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
	PHPWS_Text::htmlEncodeText($this->email_address) . 
	'" />' . $this->display_name . '</a>';
    } else {
      return $this->email_address;
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
      $template['PICTURE'] = $this->picture;
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

}

?>