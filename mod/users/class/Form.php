<?php

/**
 * Contains forms for users and demographics
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */
PHPWS_Core::initCoreClass("Form.php");

class User_Form {

  function logBox($logged=TRUE){
    translate("users");
    if ($logged){
      $username = $_SESSION['User']->getUsername();
      $form['TITLE']   = _print(_("Hello [var1]"), array($username));
      $form['CONTENT'] = User_Form::loggedIn();
    }
    else {
      $form['TITLE']   = _("Please Login");
      $form['CONTENT'] = User_Form::loggedOut();
    }

    return $form;
  }


  function loggedIn(){
    translate("users");
    PHPWS_Core::initCoreClass("Text.php");
    $template["MODULES"] = PHPWS_Text::moduleLink(_("Control Panel"), "controlpanel");
    $template["LOGOUT"] = PHPWS_Text::moduleLink(_("Log Out"), "users", array("action[user]"=>"logout"));
    $template["HOME"] = PHPWS_Text::moduleLink(_("Home"));

    return PHPWS_Template::process($template, "users", "usermenus/Default.tpl");
  }

  function loggedOut(){
    translate("users");
    if (isset($_REQUEST["block_username"]))
      $username = $_REQUEST["block_username"];
    else
      $username = NULL;

    $template["USERNAME"] = _("Username");
    $template["PASSWORD"] = _("Password");
    
    $form = & new PHPWS_Form("User_Login");
    $form->add("module", "hidden", "users");
    $form->add("action[user]", "hidden", "loginBox");
    $form->add("block_username", "text", $username);
    $form->setTag("block_username", "USERNAME_FORM");
    $form->add("block_password", "password");
    $form->setTag("block_password", "PASSWORD_FORM");
    $form->add("submit", "submit", _("Log In"));
    
    $template = $form->getTemplate(TRUE, TRUE, $template);

    return PHPWS_Template::process($template, "users", "forms/loginBox.tpl");
  }

  function setPermissions($id, $type){
    $group = new PHPWS_Group($id, FALSE);

    $modules = PHPWS_Core::getModules();

    $tpl = & new PHPWS_Template("users");
    $tpl->setFile("forms/permissions.tpl");

    $form = & new PHPWS_Form();
    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "postPermission");
    $form->add("group", "hidden", $id);
    $form->add("type", "hidden", $type);

    foreach ($modules as $mod){
      $row = User_Form::modulePermission($mod, $group);
      $template = array("ROW" => $row);
      $tpl->setCurrentBlock("module");
      $tpl->setData($template);
      $tpl->parseCurrentBlock("module");
    }

    $form->add("update_all", "submit", _("Update All"));
    $template = $form->getTemplate();
    $tpl->setData($template);

    $content  = $tpl->get();
    return $content;
  }


  function modulePermission($mod, &$group){
    Layout::addStyle("users");
    $file = PHPWS_Core::getConfigFile($mod['title'], "permission.php");
    $template = NULL;

    if (PEAR::isError($file))
      return;

    include $file;
    if (!isset($permissions))
      return;

    $permSet[NO_PERMISSION]      = _("None");
    $permSet[FULL_PERMISSION]    = _("Full");

    if ($itemPermissions == TRUE)
      $permSet[PARTIAL_PERMISSION] = _("Partial");
    else
      unset($permSet[PARTIAL_PERMISSION]);

    ksort($permSet);

    $permCheck = $group->allow($mod['title'], NULL, NULL, TRUE);

    foreach ($permSet as $key => $value){
      if ((int)$key == (int)$permCheck)
	$checked = "checked=\"checked\"";
      else
	$checked = NULL;
      $name = "permission[" . $mod['title'] . "]";
      $radio[] = "<input type=\"radio\" name=\"$name\" value=\"$key\" $checked /> $value";
    }

    $form = & new PHPWS_Form;

    foreach ($permissions as $itemname => $permVal){
      foreach ($permVal as $permName => $permProper){
	$formName = "subpermission[$itemname][$permName]"; 
	$form->add($formName, "checkbox", 1);
	if ($group->allow($itemname, $permName))
	  $form->setMatch($formName, 1);
	$subperm[] = $form->get($formName) . " $permProper";
      }
    }

    $template["SUBPERMISSIONS"] = implode("<br />", $subperm);
    $template["CHOICE"] = implode("<br />", $radio);
    $template["MODULE_NAME"] = $mod['proper_name'];
    $form->add("update[" . $mod['title'] . "]", "submit", _print(_("Update [var1]"), $mod['proper_name']));
    $template["UPDATE"] = $form->get("update[" . $mod['title'] . "]");

    $content = PHPWS_Template::process($template, "users", "forms/mod_permission.tpl");
    return $content;
  }

  function adminPanel($content){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");

    if ($_SESSION['User']->allow("users", "add_edit_users")){
      $tabs["new_user"] = array("title"=>"New User", "link"=>"index.php?module=users&amp;action[admin]=main");
      $tabs["manage_users"] = array("title"=>"Manage Users", "link"=>"index.php?module=users&amp;action[admin]=main");
    }

    if ($_SESSION['User']->allow("users", "add_edit_groups")){
      $tabs["new_group"] = array("title"=>"New Group", "link"=>"index.php?module=users&amp;action[admin]=main");
      $tabs["manage_groups"] = array("title"=>"Manage Groups", "link"=>"index.php?module=users&amp;action[admin]=main");
    }

    if ($_SESSION['User']->allow("users", "demographics"))
      $tabs["demographics"] = array("title"=>"Demographics", "link"=>"index.php?module=users&amp;action[admin]=main");


    $panel = & new PHPWS_Panel("users");
    $panel->quickSetTabs($tabs);
    $panel->setContent($content);
    Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }

  function manageUsers(){
    PHPWS_Core::initModClass("users", "User_Manager.php");
    if (!isset($_SESSION['User_Manager']))
      $manager = & new User_Manager;
    else
      $manager = unserialize($_SESSION['User_Manager']);

    if (isset($_POST['search_users']))
      $manager->setWhere("username LIKE '%" . $_POST['search_users'] . "%'");

    $content = $manager->getList("users", "Testing User Title");

    $_SESSION['User_Manager'] = serialize($manager);

    if (PEAR::isError($content)){
      PHPWS_Error::log($content);
      return $content->getMessage();
    }
    return $content;

  }

  function manageGroups(){
    PHPWS_Core::initModClass("users", "Group_Manager.php");
    if (!isset($_SESSION['Group_Manager']))
      $manager = & new Group_Manager;
    else
      $manager = unserialize($_SESSION['Group_Manager']);

    if (isset($_POST['search_groups']))
      $manager->setWhere("name LIKE '%" . $_POST['search_groups'] . "%'");

    $content = $manager->getList("users", "Testing Group Title");

    $_SESSION['Group_Manager'] = serialize($manager);

    if (PEAR::isError($content)){
      PHPWS_Error::log($content);
      return $content->getMessage();
    }
    return $content;

  }

  function manageMembers(&$group){
    $form = & new PHPWS_Form("memberList");
    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "manageMembers");
    $form->add("group", "hidden", $group->getId());
    $form->add("search_member", "textfield");
    $form->add("search", "submit", _("Add"));

    $template['NAME_LABEL'] = _("Group name");
    $template['GROUPNAME'] = $group->getName();
    $template['ADD_MEMBER_LBL'] = _("Add Member");

    if (isset($_POST['search_member'])){
      $_SESSION['Last_Member_Search'] = preg_replace("/[\W]+/", "", $_POST['search_member']);
      $db = & new PHPWS_DB("users_groups");
      $db->addWhere("name", $_SESSION['Last_Member_Search']);
      $db->addColumn("id");
      $result = $db->select("one");

      if (isset($result)){
	if (PEAR::isError($result))
	  PHPWS_Error::log($result);
	else {
	  $group->addMember($result);
	  $group->save();
	  unset($_SESSION['Last_Member_Search']);
	}

      }
    }


    if (isset($_SESSION['Last_Member_Search'])){
      $result = User_Form::getLikeGroups($_SESSION['Last_Member_Search'], $group);
      if (isset($result)) {
	$template['LIKE_GROUPS'] = $result;
	$template['LIKE_INSTRUCTION'] = _("Member not found.") . " " . _("Closest matches below.");
      } else
	$template['LIKE_INSTRUCTION'] = _("Member not found.") . " " . _("No matches found.");
    }

    $template = $form->getTemplate(TRUE, TRUE, $template);

    $template['CURRENT_MEMBERS_LBL'] = _("Current Members");
    $template['CURRENT_MEMBERS'] = User_Form::getMemberList($group);

    $result =  PHPWS_Template::process($template, "users", "forms/memberForm.tpl");
    return $result;

  }


  function getMemberList(&$group){
    $content = NULL;
    PHPWS_Core::initCoreClass("Pager.php");
    Layout::addStyle("users");


    $result = $group->getMembers();
    unset($db);
    if ($result){
      $db = & new PHPWS_DB("users_groups");
      $db->addColumn("name");
      $db->addColumn("id");
      $db->addWhere("id", $result, "=", "or");

      $groupResult = $db->select();

      $count = 0;
      foreach ($groupResult as $item){
	$count++;
	$action = "<a href=\"index.php?module=users&amp;action[admin]=dropMember&amp;member=" . $item['id'] . "&amp;group="
	  . $group->getId() . "\">Drop</a>";
	if ($count % 2)
	  $template['STYLE'] = "class=\"bg-light\"";
	else
	  $template['STYLE'] = NULL;
	$template['NAME'] = $item['name'];
	$template['ACTION'] = $action;

	$data[] = PHPWS_Template::process($template, "users", "forms/memberlist.tpl");
      }

      $pager = & new PHPWS_Pager;
      $pager->setData($data);
      $pager->setLinkBack("index.php?module=users&amp;group=" . $group->getId() . "&amp;action[admin]=manageMembers");
      $pager->pageData();
      $content = $pager->getData();
    }

    if (!isset($content))
      $content = _("No members.");

    if (PEAR::isError($content)){
      PHPWS_Error::log($content);
      return $content->getMessage();
    }
    return $content;
  }


  function demographics($message=NULL){
    PHPWS_Core::initModClass("users", "Demographics.php");
    PHPWS_Core::initModClass("users", "Demo_Manager.php");

    if (isset($message))
      $finalForm['MESSAGE'] = $message;

    $finalForm['SELECTIONS'] = Demo_Manager::getList();
    return PHPWS_Template::process($finalForm, "users", "manager/demoList.tpl");
  }

  function setActiveDemographics(){
    PHPWS_Core::initModClass("users", "Demographics.php");
    $alldemo = $_SESSION['All_Demo'];
    unset($_SESSION['All_Demo']);

    $db = & new PHPWS_DB("users_demographics");

    if (PEAR::isError($alldemo))
      Layout::add($alldemo->getMessage());

    if (!isset($alldemo))
      return;

    foreach ($alldemo as $label){
      $db->addWhere("label", $label);
      if (isset($_POST['demo'][$label]))
	$db->addValue("active", 1);
      else
	$db->addValue("active", 0);
      $result = $db->update();
      $db->reset();
    }
  }

  function userForm(&$user, $message=NULL){
    translate("users");

    PHPWS_Core::initModClass("users", "Demographics.php");
    $form = & new PHPWS_Form;

    if ($user->getId() > 0){
      $form->add("userId", "hidden", $user->getId());
      $form->add("submit", "submit", _("Update User"));
    } else {
      $form->add("submit", "submit", _("Add User"));
    }

    if (isset($message))
      $tpl['MESSAGE'] = $message;

    $tpl['USERNAME_LBL'] = _("Username");
    $tpl['PASSWORD_LBL'] = _("Password");

    $form->add("action[admin]", "hidden", "postUser");
    $form->add("module", "hidden", "users");
    $form->add("username", "text", $user->getUsername());
    $form->add("password1", "password");
    $form->add("password2", "password");

    Demographics::form($form, $user);

    $form->mergeTemplate($tpl);

    $template = $form->getTemplate();

    $result = PHPWS_Template::process($template, "users", "forms/userForm.tpl");
    return $result;
  }

  function deify(&$user){
    if (!$_SESSION['User']->isDeity() || ($user->getId() == $_SESSION['User']->getId())){
      $content[] = _("Only another deity can create a deity.");
    } else {
      $link = "<a href=\"index.php?module=users&amp;user=" . $user->getId() . "&amp;action[admin]=deify";
      $content[] = _("Are you certain you want this user to have complete control of this web site?");
      $content[] = $link . "&amp;authorize=1\">" . _("Yes, make them a deity.") . "</a>";
      $content[] = $link . "&amp;authorize=0\">" . _("No, leave them as a mortal.") . "</a>";
    }

    return implode("<br />", $content);
  }

  function mortalize(&$user){
    if (!$_SESSION['User']->isDeity())
      $content[] = _("Only another deity can create a mortal.");
    elseif($user->getId() == $_SESSION['User']->getId())
      $content[] = _("A deity can not make themselves mortal.");
    else {
      $link = "<a href=\"index.php?module=users&amp;user=" . $user->getId() . "&amp;action[admin]=mortalize";
      $content[] = _("Are you certain you want strip complete control from this user?");
      $content[] = $link . "&amp;authorize=1\">" . _("Yes, make them a mortal.") . "</a>";
      $content[] = $link . "&amp;authorize=0\">" . _("No, leave them as a deity.") . "</a>";
    }
    return implode("<br />", $content);
  }

  function groupForm(&$group){
    translate("users");

    $form = & new PHPWS_Form("groupForm");
    $members = $group->getMembers();

    if ($group->getId() > 0){
      $form->add("groupId", "hidden", $group->getId());
      $form->add("submit", "submit", _("Update Group"));
    } else
      $form->add("submit", "submit", _("Add Group"));

    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "postGroup");

    $form->add("groupname", "textfield", $group->getName());

    $template = $form->getTemplate();
    $template['GROUPNAME_LBL'] = _("Group Name");

    $content = PHPWS_Template::process($template, "users", "forms/groupForm.tpl");
    return $content;
  }

  function memberForm(){
    $form->add("add_member", "textfield");
    $form->add("new_member_submit", "submit", _("Add"));
    
    $template['CURRENT_MEMBERS'] = User_Form::memberListForm($group);
    $template['ADD_MEMBER_LBL'] = _("Add Member");
    $template['CURRENT_MEMBERS_LBL'] = _("Current Members");

    if (isset($_POST['new_member_submit']) && !empty($_POST["add_member"])){
      $result = User_Form::getLikeGroups($_POST['add_member'], $group);
      if (isset($result)) {
	$template['LIKE_GROUPS'] = $result;
	$template['LIKE_INSTRUCTION'] = _("Members found.");
      } else
	$template['LIKE_INSTRUCTION'] = _("No matches found.");
    }

  }

  function memberListForm($group){
    $members = $group->getMembers();
    if (!isset($members))
      return _("None found");

    $db = & new PHPWS_DB("users_groups");
    foreach ($members as $id)
      $db->addWhere("id", $id);
    $db->addOrder("name");

    $result = $db->loadObjects("PHPWS_Group", "id");

    $tpl = & new PHPWS_Template("users");
    $tpl->setFile("forms/memberlist.tpl");
    $count = 0;
    $form = new PHPWS_Form;

    foreach ($result as $group){
      $form->add("member_drop[" . $group->getId() . "]", "submit", _("Drop"));
      $dropbutton = $form->get("member_drop[" . $group->getId() ."]");
      $count++;
      $tpl->setCurrentBlock("row");
      $tpl->setData(array("NAME"=>$group->getName(), "DROP"=>$dropbutton));
      if ($count%2)
	$tpl->setData(array("STYLE" => "class=\"bg-light\""));
      $tpl->parseCurrentBlock();
    }

    return $tpl->get();

  }


  function getLikeGroups($name, &$group){
    $db = & new PHPWS_DB("users_groups");
    $name = preg_replace("/[^\w]/", "", $name);
    $db->addWhere("name", "%$name%", "LIKE");

    if (!is_null($group->getName()))
      $db->addWhere("name", $group->getName(), "!=");

    $members = $group->getMembers();
    if (isset($members)){
      foreach ($members as $id)
	$db->addWhere("id", $id, "!=");
    }

    $result = $db->loadObjects("PHPWS_Group", "id");

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return NULL;
    } elseif (!isset($result))
	return NULL;

    $tpl = & new PHPWS_Template("users");
    $tpl->setFile("forms/likeGroups.tpl");
    $count = 0;

    foreach ($result as $member){
      if (isset($members))
	if (in_array($member->getId(), $members))
	  continue;
      $link = "<a href=\"index.php?module=users&amp;action[admin]=addMember&amp;member=" . $member->getId() . "&amp;group=" . $group->getId() . "\">" . _("Add") . "</a>";
      $count++;
      $tpl->setCurrentBlock("row");
      $tpl->setData(array("NAME"=>$member->getName(), "ADD"=>$link));
      if ($count%2)
	$tpl->setData(array("STYLE" => "class=\"bg-light\""));
      $tpl->parseCurrentBlock();
    }

    $content = $tpl->get();
    return $content;
  }
}

?>