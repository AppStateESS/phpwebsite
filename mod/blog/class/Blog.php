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

  function getFormatedDate($type=BLOG_VIEW_DATE_FORMAT)
  {
    return strftime($type, $this->date);
  }

  function save()
  {
    $db = & new PHPWS_DB("blog_entries");
    if (!empty($this->id)) {
      $db->addWhere("id", $this->id);
    } else {
      $this->date = mktime();
    }

    $result = $db->saveObject($this);
    return $result;
  }

  function view($edit=TRUE, $limited=TRUE)
  {
    PHPWS_Core::initModClass("categories", "Categories.php");
    $template['TITLE'] = $this->getTitle(TRUE);
    $template['DATE']  = $this->getFormatedDate();
    $template['ENTRY'] = $this->getEntry(TRUE);

    if ($edit && Current_User::allow("blog", "edit_blog", $this->getId())){
      $vars['blog_id'] = $this->getId();
      $vars['action']  = "admin";
      $vars['command'] = "edit";
      $links[] = PHPWS_Text::secureLink(_("Edit"), "blog", $vars);
    }

    if ($limited) {
      $links[] = PHPWS_Text::rerouteLink(_("View"), "blog", "view", $this->getId());
    }

    $result = Categories::getSimpleLinks("blog", $this->id);
    if (!empty($result)) {
      $template['CATEGORIES'] = implode(", ", $result);
    }

    if (isset($links)) {
      $template['LINKS'] = implode(" | " , $links);
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