<?php

class Blog {
  var $id    = NULL;
  var $title = NULL;
  var $entry = NULL;
  var $date  = NULL;

  function Blog($id=NULL)
  {
    if (isset($id)){
      $this->id = (int)$id;
      $result = $this->init();
      if (PEAR::isError($result))
	PHPWS_Error::log($result);
    }
  }

  function init()
  {
    if (!isset($this->id))
      return FALSE;

    $db = & new PHPWS_DB("blog_entries");
    $db->addWhere("id", $this->id);
    $result = $db->loadObject($this);
    if (PEAR::isError($result))
      return $result;
  }


  function getEntry($print=FALSE)
  {
    if ($print)
      return PHPWS_Text::parseOutput($this->entry);
    else
      return $this->entry;
  }

  function getId()
  {
    return $this->id;
  }

  function getTitle($print=FALSE)
  {
    if ($print)
      return PHPWS_Text::parseOutput($this->title);
    else
      return $this->title;
  }

  function getFormatedDate($type="%x")
  {
    return strftime($type, $this->date);
  }

  function save($isVersion=FALSE, $approve=FALSE)
  {
    PHPWS_Core::initModClass("version", "Version.php");

    /*
     * if restricted createUnapproved or updateUnapproved
     * if unrestricted
     *   if approving an unapproved  updateApproved
     *   if updating an unapproved   updateUnapproved
     *   if ignoring unapproved or     createApproved
     *   no unapproved
     */

    $db = & new PHPWS_DB("blog_entries");

    if (isset($this->id))
      $db->addWhere("id", $this->id);

    $this->date = mktime();

    if (Current_User::isRestricted("blog")) {
      $result = Version::saveUnapproved("blog", "blog_entries", $this);
    }
    else {
      if ($isVersion == TRUE) {
	if ($approve == TRUE) {
	  $db->saveObject($this);
	  $result = Version::saveApproved("blog", "blog_entries", $this);
	} else {
	  $result = Version::saveUnapproved("blog", "blog_entries", $this);
	}
      } else {
	$result = $db->saveObject($this);
	if (PEAR::isError($result))
	  return $result;
	$result = Version::saveVersion("blog", "blog_entries", $this);
	test($result);
      }
    }

    return $result;
  }

  function view($edit=TRUE)
  {
    $template['TITLE'] = $this->getTitle(TRUE);
    $template['ENTRY'] = $this->getEntry(TRUE);

    if ($edit && Current_User::allow("blog", "edit_blog", $this->getId())){
      $vars['action']  = "admin";
      $vars['blog_id'] = $this->getId();
      $vars['command'] = "edit";
      
      $template['EDIT'] = PHPWS_Text::secureLink(_("Edit"), "blog", $vars);
    }

    return PHPWS_Template::process($template, "blog", "view.tpl");
  }

  function kill()
  {
    PHPWS_Core::initCoreClass("Version.php");

    $db = & new PHPWS_DB("blog_entries");
    $db->addWhere("id", $this->id);
    $db->delete();

    Version::flush($this->id, "blog_entries");
  }
}

?>