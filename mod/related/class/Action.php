<?php

class Related_Action {

  function create(&$related){
    $template['TITLE_LBL'] = _("Title");
    $template['MODULE_LBL'] = _("Module");

    $instructions[] = _("Currently, nothing is associated with this item.");
    $instructions[] = _("If you want to add related information to this item, click the 'Build Related' link.");

    $template['INSTRUCTIONS'] = implode("<br />", $instructions);

    Related_Action::newBank($related);

    $template['LINK'] = "<a href=\"index.php?module=related&amp;action=start\">" . 
      _("Build Related") . "</a>";

    $template['TITLE'] = $related->getUrl(TRUE);

    $module = & new PHPWS_Module($related->getModule());
    $template['MODULE'] = $module->getProperName(TRUE);

    return PHPWS_Template::process($template, "related", "create.tpl");
  }

  function edit(&$current){
    $tpl = new PHPWS_Template("related");
    $result = $tpl->setFile("edit.tpl");

    $related = & Related_Action::getBank();
    $template['TITLE_LBL'] = _("Title");
    $template['MODULE_LBL'] = _("Module");
    $template['TITLE'] = $related->getUrl(TRUE);

    if (!$related->isSame($current) && !$related->isFriend($current))
      $template['ADD_LINK'] = "<a href=\"index.php?module=related&amp;action=add\">"
      . _("Add Item") . "</a>";



    $template['QUIT_LINK'] = "<a href=\"index.php?module=related&amp;action=quit\">"
      . _("Quit") . "</a>";


    Related_Action::setCurrent($current);

    $module = & new PHPWS_Module($related->getModule());
    $template['MODULE'] = $module->getProperName(TRUE);

    if ($related->hasFriends()){
      $template['SAVE_LINK'] = "<a href=\"index.php?module=related&amp;action=save\">"
	. _("Save") . "</a>";

      $friends = Related_Action::listFriends();

      if (is_array($friends)){
	foreach ($friends as $key=>$friend_item){
	  $up = "<a href=\"index.php?module=related&amp;action=up&amp;pos=$key\"><img src=\"images/mod/related/up.png\"/></a>";
	  $down = "<a href=\"index.php?module=related&amp;action=down&amp;pos=$key\"><img src=\"images/mod/related/down.png\"/></a>";
	  $remove = "<a href=\"index.php?module=related&amp;action=remove&amp;pos=$key\"><img src=\"images/mod/related/remove.png\"/></a>";

	  $tpl->setCurrentBlock("friend_list");
	  $tpl->setData(array("FRIEND_NAME"=>$friend_item,
			      "UP"=>$up,
			      "DOWN"=>$down,
			      "REMOVE"=>$remove
			      ));
	  $tpl->parseCurrentBlock();
	}
      }
    } else
      $template['FRIEND_NAME'] = _("View other items to add them to the list.");

    $tpl->setData($template);

    return $tpl->get();
  }


  function view(&$related){
    $tpl = new PHPWS_Template("related");
    $result = $tpl->setFile("view.tpl");
    Related_Action::newBank($related);

    if (Current_User::allow("related"))
      $template['EDIT_LINK'] = "<a href=\"index.php?module=related&amp;action=start\">"
	. _("Edit") . "</a>";

    $tpl->setData($template);

    return $tpl->get();
    
  }

  function newBank(&$related){
    unset($_SESSION['Related__Bank']);
    $_SESSION['Related_Bank'] = $related;
  }

  function setCurrent(&$friend){
    unset($_SESSION['Current__Friend']);
    $_SESSION['Current_Friend'] = $friend;
  }


  function &getBank(){
    return $_SESSION['Related_Bank'];
  }

  function isBanked(){
    if (isset($_SESSION['Related_Bank']) && $_SESSION['Related_Bank']->isBanked())
      return TRUE;
    else
      return FALSE;
  }

  function listFriends(){
    if (!Related_Action::isBanked())
      return _("Bank not created.");

    $related = & Related_Action::getBank();

    $friends = $related->getFriends();

    if (empty($friends))
      return NULL;

    foreach ($friends as $friend)
      $list[] = $friend->getURL(TRUE);

    return $list;
  }

  function start(){
    if (!isset($_SESSION['Related_Bank']))
      return _("Bank not created.");

    $related = & Related_Action::getBank();
    $related->setBanked(TRUE);

    header("location:" . $related->getUrl());
    exit();
  }

  function quit(){
    $location = $_SESSION['Related_Bank']->getUrl();
    unset($_SESSION['Related_Bank']);
    header("location:$location");
    exit();
  }

  function add(){
    if (!isset($_SESSION['Related_Bank']))
      return _("Bank not created.");

    if (!isset($_SESSION['Current_Friend']))
      return _("Friend not created.");

    $related = & $_SESSION['Related_Bank'];
    $friend = & $_SESSION['Current_Friend'];

    $related->addFriend($friend);

    header("location:" . $friend->getUrl());
    exit();
  }

  function up(){
    if (!isset($_SESSION['Related_Bank']))
      return _("Bank not created.");

    if (!isset($_REQUEST['pos']))
      return _("Missing position.");

    $_SESSION['Related_Bank']->moveFriendUp($_REQUEST['pos']);
    header("location:" . $_SESSION['Current_Friend']->getUrl());
    exit();
  }

  function down(){
    if (!isset($_SESSION['Related_Bank']))
      return _("Bank not created.");

    if (!isset($_REQUEST['pos']))
      return _("Missing position.");

    $_SESSION['Related_Bank']->moveFriendDown($_REQUEST['pos']);
    header("location:" . $_SESSION['Current_Friend']->getUrl());
    exit();
  }

  function remove(){
    if (!isset($_SESSION['Related_Bank']))
      return _("Bank not created.");

    if (!isset($_REQUEST['pos']))
      return _("Missing position.");

    $_SESSION['Related_Bank']->removeFriend($_REQUEST['pos']);
    header("location:" . $_SESSION['Current_Friend']->getUrl());
    exit();
  }

  function save(){
    if (!isset($_SESSION['Related_Bank']))
      return _("Bank not created.");

    $result = $_SESSION['Related_Bank']->save();

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      Layout::add(_("The Related module encountered a database error."));
      return;
    }
    
    Related_Action::quit();
  }

}

?>