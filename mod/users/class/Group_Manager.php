<?php

PHPWS_Core::initCoreClass("List.php");
PHPWS_Core::initModClass("users", "Group.php");

class Group_Manager extends PHPWS_List{

  function Group_Manager(){
    Layout::addStyle("users");

    $form = & new PHPWS_Form;
    $form->add("search_groups", "textfield");
    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "manage_groups");
    $search = $form->getTemplate();
    unset($search['DEFAULT_SUBMIT']);

    $listTags = array("NAME_LABEL"    => _("Group Name"),
		      "MEMBERS_LABEL" => _("Members"),
		      "ACTIVE_LABEL"  => _("Status"),
		      "ACTIONS_LABEL" => _("Actions"),
		      "SEARCH_LABEL"  => _("Search for Group"),
		      "SEARCH"        => implode("", $search)
		      );


    $this->setModule("users");
    $this->setIdColumn("id");
    $this->setClass("Group_List");
    $this->setTable("users_groups");

    $columns = array("name"       => TRUE,
		     "active"     => TRUE,
		     "actions"    => FALSE,
		     "members"    => FALSE
		     );

    $this->setWhere("user_id = 0");
    $this->setColumns($columns);
    $this->setName("group_manager");
    $this->setTemplate("manager/groups.tpl");
    $this->setOp("action[admin]=main&amp;tab=manage_groups");
    $this->setPaging(array("limit"=>10,
			   "section"=>TRUE,
			   "limits"=>array(5, 10, 25),
			   "forward"    => "&#062;&#062;",
			   "back"       => "&#060;&#060;" ));
    $this->setExtraListTags($listTags);

  }

}
 
class Group_List{
  var $groupList = NULL;

  function Group_List($groupList){
    $this->groupList = $groupList;
  }

  function getlistname(){
    return $this->groupList['name'];
  }


  function getlistactive(){
    $id = $this->groupList['id'];
    if ($this->groupList['active'])
      return "<a href=\"index.php?module=users&amp;action[admin]=deactivate&amp;group=$id\">" . _("Active") . "</a>";
    else
      return "<a href=\"index.php?module=users&amp;action[admin]=activate&amp;group=$id\">" . _("Disabled") . "</a>";
  }

  function getlistactions(){
    $startLink = "<a href=\"index.php?module=users&amp;group=" . $this->groupList['id'] . "&amp;action[admin]=";
    $links[] = $startLink . "editGroup\">" . _("Edit") . "</a>";
    $links[] = $startLink . "setGroupPermissions\">" . _("Permissions") . "</a>";
    $links[] = $startLink . "deleteGroup\">" . _("Delete") . "</a>";
    return implode("&nbsp;|&nbsp;", $links);
  }

  function getlistmembers(){
    $db = & new PHPWS_DB("users_members");
    $db->addColumn("member_id", FALSE, TRUE);
    $db->addWhere("group_id", $this->groupList['id']);
    $result = $db->select("one");

    $link = "<a href=\"index.php?module=users&amp;group=" . $this->groupList['id'] . "&amp;action[admin]=manageMembers\">$result</a>";
    return $link;
  }

}

?>