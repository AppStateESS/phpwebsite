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

    if (Current_User::isLogged()){
      $username = Current_User::getUsername();
      $form['TITLE']   = sprintf(_("Hello %s"), $username);
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
    $template["MODULES"] = PHPWS_Text::secureLink(_("Control Panel"), "controlpanel", array("command"=>"panel_view"));
    $template["LOGOUT"] = PHPWS_Text::moduleLink(_("Log Out"), "users", array("action"=>"user", "command"=>"logout"));
    $template["HOME"] = PHPWS_Text::moduleLink(_("Home"));

    return PHPWS_Template::process($template, "users", "usermenus/Default.tpl");
  }

  function loggedOut(){
    translate("users");

    if (isset($_REQUEST["block_username"]))
      $username = $_REQUEST["block_username"];
    else
      $username = NULL;

    $form = & new PHPWS_Form("User_Login");
    $form->addHidden("module", "users");
    $form->addHidden("action", "user");
    $form->addHidden("command", "loginBox");
    $form->addText("block_username", $username);
    $form->addPassword("block_password");
    $form->addSubmit("submit", _("Log In"));

    $form->setLabel("block_username", _("Username"));
    $form->setLabel("block_password", _("Password"));
    $form->setId("block_username", "username");
    
    $template = $form->getTemplate();

    return PHPWS_Template::process($template, "users", "forms/loginBox.tpl");
  }

  function setPermissions($id){
    Layout::addStyle("users");
    $group = & new PHPWS_Group($id, FALSE);
    test($group);

    $modules = PHPWS_Core::getModules();

    $tpl = & new PHPWS_Template("users");
    $tpl->setFile("forms/permissions.tpl");

    foreach ($modules as $mod){
      $mod_template = User_Form::modulePermission($mod, $group);
      if ($mod_template == false)
	continue;

      $tpl->setCurrentBlock("module");
      $tpl->setData($mod_template);
      $tpl->parseCurrentBlock("module");
    }

    $form = & new PHPWS_Form();
    $form->addHidden("module", "users");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postPermission");
    $form->addHidden("group_id", $id);
    $form->add("update", "submit", _("Update"));
    $template = $form->getTemplate();

    $tpl->setData($template);

    $content = $tpl->get();

    return $content;
  }


  function modulePermission($mod, &$group){
    $group->loadPermissions(FALSE);

    $file = PHPWS_Core::getConfigFile($mod['title'], "permission.php");
    $template = NULL;

    if ($file == FALSE)
      return $file;

    include $file;

    if (!isset($use_permissions) || $use_permissions == FALSE)
      return;

    $permSet[NO_PERMISSION]      = _("None");
    $permSet[FULL_PERMISSION]    = _("Full");

    if (isset($itemPermissions) && $itemPermissions == TRUE)
      $permSet[PARTIAL_PERMISSION] = _("Partial");
    else
      unset($permSet[PARTIAL_PERMISSION]);

    ksort($permSet);

    $permCheck = $group->allow($mod['title'], NULL, NULL, TRUE);

    foreach ($permSet as $key => $value){
      $form = & new PHPWS_Form;
      $name = "module_permission[{$mod['title']}]";
      $result = $form->addRadio($name, $key);
      $form->setMatch($name, $permCheck);
      $template['PERMISSION_' . $key] = $form->get($name);
    }

    if (isset($permissions)){
      foreach ($permissions as $permName => $permProper){
	$form = & new PHPWS_Form;
	$name = "sub_permission[{$mod['title']}][$permName]";
	$form->addCheckBox($name, 1);
	$subpermissions[] = $form->get($name) . " " . $permProper;
      }

      $template['SUBPERMISSIONS'] = implode("<br />", $subpermissions);
    }

    $template['MODULE_NAME'] = $mod['proper_name'];
    return $template;
  }

  function manageUsers(){
    Layout::addStyle("users");
    PHPWS_Core::initCoreClass("DBPager.php");
    PHPWS_Core::initModClass("users", "User_Manager.php");

    $pageTags['USERNAME'] = _("Username");
    $pageTags['LAST_LOGGED'] = _("Last Logged");
    $pageTags['ACTIVE'] = _("Active");
    $pageTags['ACTIONS'] = _("Actions");

    $pager = & new DBPager("users", "User_Manager");
    $pager->setModule("users");
    $pager->setTemplate("manager/users.tpl");
    $pager->setLink("index.php?module=users&amp;action=admin&amp;tab=manage_users&amp;authkey=" . Current_User::getAuthKey());
    $pager->addTags($pageTags);
    $pager->setMethod("active", "listActive");
    $pager->setMethod("last_logged", "listLastLogged");
    $pager->addToggle("class=\"toggle1\"");
    $pager->addToggle("class=\"toggle2\"");
    $pager->addRowTag("actions", "User_Manager", "listAction");

    if (!Current_User::isDeity())
      $pager->addWhere("id", ANONYMOUS_ID, "!=");

    return $pager->get();
  }


  function manageGroups(){
    Layout::addStyle("users");
    PHPWS_Core::initCoreClass("DBPager.php");
    PHPWS_Core::initModClass("users", "Group_Manager.php");

    $pageTags['GROUPNAME'] = _("Group Name");
    //    $pageTags['ACTIVE'] = _("Active");
    $pageTags['MEMBERS_LABEL'] = _("Members");
    $pageTags['ACTIONS_LABEL'] = _("Actions");

    $pager = & new DBPager("users_groups", "Group_Manager");
    $pager->setModule("users");
    $pager->setTemplate("manager/groups.tpl");
    $pager->setLink("index.php?module=users&amp;action=admin&amp;tab=manage_groups&amp;authkey=" . Current_User::getAuthKey());
    $pager->addTags($pageTags);
    $pager->setMethod("active", "listActive");
    $pager->addToggle("class=\"toggle1\"");
    $pager->addToggle("class=\"toggle2\"");
    $pager->addRowTag("actions", "Group_Manager", "listAction");
    $pager->addRowTag("members", "Group_Manager", "listMembers");

    if (!Current_User::isDeity())
      $pager->addWhere("id", ANONYMOUS_ID, "!=");

    $pager->addWhere("user_id", 0);

    return $pager->get();
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

      $vars['action'] = "admin";
      $vars['command'] = "dropMember";
      $vars['group'] = $group->getId();
      foreach ($groupResult as $item){
	$count++;
	$vars['member'] = $item['id'];
	$action = PHPWS_Text::secureLink(_("Drop"), "users", $vars);
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
      $pager->setLinkBack("index.php?module=users&amp;group=" . $group->getId() . "&amp;action=admin&amp;command=manageMembers");
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

  function userForm(&$user, $message=NULL){
    translate("users");

    $form = & new PHPWS_Form;

    if ($user->getId() > 0){
      $form->addHidden("userId", $user->getId());
      $form->addSubmit("submit", _("Update User"));
    } else
      $form->addSubmit("submit", _("Add User"));


    $form->addHidden("action", "admin");
    $form->addHidden("command", "postUser");

    $form->addHidden("module", "users");
    $form->addText("username", $user->getUsername());
    $form->addPassword("password1");
    $form->addPassword("password2");
    $form->addText("email", $user->getEmail());

    $form->setLabel("email", _("Email Address"));
    $form->setLabel("username", _("Username"));
    $form->setLabel("password1", _("Password"));

    if (isset($tpl))
      $form->mergeTemplate($tpl);

    $template = $form->getTemplate();
    if (isset($message)){
      foreach ($message as $tag=>$error)
	$template[strtoupper($tag) . "_ERROR"] = $error;
    }

    return PHPWS_Template::process($template, "users", "forms/userForm.tpl");
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
      $form->addHidden("groupId", $group->getId());
      $form->addSubmit("submit", _("Update Group"));
    } else
      $form->addSubmit("submit", _("Add Group"));

    $form->addHidden("module", "users");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postGroup");

    $form->addText("groupname", $group->getName());
    $form->setLabel("groupname", _("Group Name"));
    $template = $form->getTemplate();

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
    $db->setIndexBy("id");
    $result = $db->getObjects("PHPWS_Group");

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
    $db->setIndexBy("id");
    $result = $db->getObjects("PHPWS_Group");

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

  function authorizationSetup(){
    Layout::addStyle("users");

    $values['DROP_Q'] = _("Are you sure you want to drop this authorization script?");

    Layout::loadModuleJavascript("users", "authorize.js", $values);

    $template = array();
    PHPWS_Core::initCoreClass("File.php");

    $auth_list = User_Action::getAuthorizationList();

    foreach ($auth_list as $auth){
      $file_compare[] = $auth['filename'];
    }

    $form = & new PHPWS_Form;

    $form->addHidden("module", "users");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postAuthorization");

    $file_list = PHPWS_File::readDirectory(PHPWS_SOURCE_DIR . "mod/users/scripts/", FALSE, TRUE, FALSE, array("php"));

    $remaining_files = array_diff($file_list, $file_compare);

    if (empty($remaining_files))
      $template['FILE_LIST'] = _("No new scripts found");
    else {
      $form->addSelect("file_list", $remaining_files);
      $form->reindexValue("file_list");
      $form->addSubmit("add_script", _("Add Script File"));
    }

    $form->mergeTemplate($template);
    $form->addSubmit("submit", _("Update Default"));
    $template = $form->getTemplate();

    $template['AUTH_LIST_LABEL'] = _("Authorization Scripts");
    $template['DEFAULT_LABEL'] = _("Default");
    $template['DISPLAY_LABEL'] = _("Display Name");
    $template['FILENAME_LABEL'] = _("Script Filename");
    $template['ACTION_LABEL'] = _("Action");

    $tpl = new PHPWS_Template("users");
    $tpl->setFile("forms/authorization.tpl");
    $tpl->setData($template);

    $default_authorization = PHPWS_User::getUserSetting("default_authorization");

    foreach ($auth_list as $authorize){
      extract($authorize);
      if ($default_authorization == $id)
	$checked = "checked=\"checked\"";
      else
	$checked = NULL;

      $getVars['module'] = "users";
      $getVars['action'] = "admin";
      $getVars['command'] = "dropScript";

      if ($filename != "local.php" && $filename != "global.php")
	$links[1] = "<a href=\"javascript:void(0)\" onclick=\"drop($id)\">Drop</a>";

      $getVars['command'] = "editScript";
      $links[2] = PHPWS_Text::secureLink(_("Edit"), "users", $getVars);

      $row['CHECK'] = "<input type=\"radio\" name=\"default_authorization\" value=\"$id\" $checked />";
      $row['DISPLAY_NAME'] = $display_name;
      $row['FILENAME'] = $filename;
      $row['ACTION'] = implode(" | ", $links);
      
      $tpl->setCurrentBlock("auth-rows");
      $tpl->setData($row);
      $tpl->parseCurrentBlock();
    }

    $content = $tpl->get();
    return $content;
  }

  function settings(){
    PHPWS_Core::initModClass("help", "Help.php");

    $default_group = PHPWS_User::getUserSetting("default_group");

    if (PEAR::isError($default_group)){
      PHPWS_Error::log($default_group);
      $default_group = 0;
    }

    $content = array();

    $form = new PHPWS_Form("user_settings");
    $form->addHidden("module", "users");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "update_settings");
    $form->addSubmit("submit",_("Update Settings"));

    $groups = User_Action::getGroups("group");

    $groups[0] = "-" . _("No Default") . "-";
    ksort($groups);

    $form->add("default_group", "select", $groups);
    $form->setMatch("default_group", $default_group);
    $form->setLabel("default_group", _("Default User Group"));

    $template = $form->getTemplate();
    $template['DEFAULT_HELP'] = PHPWS_Help::show_link("users", "default_user_group");
    return PHPWS_Template::process($template, "users", "forms/settings.tpl");
  }


}

?>