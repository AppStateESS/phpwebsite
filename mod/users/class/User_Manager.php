<?php

PHPWS_Core::initCoreClass("List.php");

class User_Manager extends PHPWS_List{

  function User_Manager(){
    Layout::addStyle("users");

    $form = & new PHPWS_Form;
    $form->add("search_users", "textfield");
    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "manage_users");
    $search = $form->getTemplate("search_users");
    unset($search['DEFAULT_SUBMIT']);

    $listTags = array("USERNAME_LABEL"    => _("Username"),
		      "LAST_LOGGED_LABEL" => _("Last Online"),
		      "ACTIVE_LABEL"      => _("Status"),
		      "ACTIONS_LABEL"     => _("Actions"),
		      "SEARCH_LABEL"      => _("Search for User"),
		      "SEARCH"            => implode("", $search)
		      );


    $this->setDualTemplate(FALSE);
    $this->setModule("users");
    $this->setIdColumn("id");
    $this->setClass("User_List");
    $this->setTable("users");

    $dbcolumns = array("username", "last_logged", "active", "deity");
    $listcolumns = array("username", "last_logged", "active", "deity", "actions");

    if (!$_SESSION['User']->isDeity())
      $this->setWhere("deity = 0");

    $this->setDbColumns($dbcolumns);
    $this->setListColumns($listcolumns);

    $this->setName("user_manager");
    $this->setTemplate("manager/users.tpl");
    $this->setOp("action[admin]=main&amp;tab=manage_users");
    $this->setPaging(array("limit"=>10,
			   "section"=>TRUE,
			   "limits"=>array(5, 10 , 25),
			   "forward"    => "&#062;&#062;",
			   "back"       => "&#060;&#060;" ));
    $this->setExtraListTags($listTags);

  }

}

class User_List{
  var $userList = NULL;

  function User_List($userList){
    $this->userList = $userList;
  }

  function getlistusername(){
    return $this->userList['username'];
  }

  function getlistlast_logged(){
    if (isset($this->userList['last_logged']))
      return strftime("%c", $this->userList['last_logged']);
    else
      return NULL;
  }

  function getlistactive(){
    $id = $this->userList['id'];
    if ($this->userList['active'])
      return "<a href=\"index.php?module=users&amp;action[admin]=deactivate&amp;user=$id\">" . _("Active") . "</a>";
    else
      return "<a href=\"index.php?module=users&amp;action[admin]=activate&amp;user=$id\">" . _("Disabled") . "</a>";
  }

  function getlistactions(){
    $startLink = "<a href=\"index.php?module=users&amp;user=" . $this->userList['id'] . "&amp;action[admin]=";

    if ($_SESSION['User']->allow("users", "edit_users"))
      $links[] = $startLink . "editUser\">" . _("Edit") . "</a>";
    
    if ($_SESSION['User']->allow("users", "edit_permissions"))
      $links[] = $startLink . "setUserPermissions\">" . _("Permissions") . "</a>";

    if ($_SESSION['User']->allow("users", "delete_users"))
      $links[] = $startLink . "deleteUser\">" . _("Delete") . "</a>";

    if ($_SESSION['User']->isDeity()){
      if ($this->userList['deity'])
	$links[] = $startLink . "mortalize\">" . _("Deity") . "</a>";
      else
	$links[] = $startLink . "deify\">" . _("Mortal") . "</a>";
    }

    return implode("&nbsp;|&nbsp;", $links);
  }

}

?>