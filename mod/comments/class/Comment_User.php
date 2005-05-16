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
  var $locked        = NULL;
  var $_error        = NULL;

  function Comment_User($user_id=NULL)
  {
    if (empty($user_id)) {
      return;
    }

    $this->user_id = (int)$user_id;
    $result = $this->init();
    if (PEAR::isError($result)) {
      $this->_error = $result;
    }
  }

  function init()
  {
    $db = & new PHPWS_DB('comments_users');
    $db->addWhere('user_id', $this->user_id);
    return $db->loadObject($this);
  }

  function getUserId()
  {
    return $this->user_id;
  }

  function loadDisplayName()
  {
    $this->display_name = Current_User::getDisplayName();
  }

  function setSignature($sig)
  {
    $this->signature = PHPWS_Text::parseInput(strip_tags($sig));
  }

  function getSignature($format=FALSE)
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
      $this->joined_date = gmmktime();
    } else {
      $this->joined_date = $date;
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

  function save()
  {
    $db = & new PHPWS_DB('comments_users');
    return $db->saveObject($this);
  }

  function kill()
  {
    $db = & new PHPWS_DB('comments_users');
    $db->addWhere('user_id', $this->user_id);
    return $db->delete();
  }
}

?>