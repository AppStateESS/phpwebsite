<?php

PHPWS_Core::initModClass("users", "Group.php");

class Group_Manager extends PHPWS_Group {
  function listAction($group){
    $id = $group->id;

    $linkVar['action'] = "admin";
    $linkVar['group_id'] = $id;

    $linkVar['command'] = "edit_group";
    $links[] = PHPWS_Text::secureLink(_("Edit"), "users", $linkVar, NULL, _("Edit Group"));

    $linkVar['command'] = "setGroupPermissions";
    $links[] = PHPWS_Text::secureLink(_("Permissions"), "users", $linkVar);

    $linkVar['command'] = "manageMembers";
    $links[] = PHPWS_Text::secureLink(_("Members"), "users", $linkVar);

    /*
    if ($group->active){
      $linkVar['command'] = "deactivateGroup";
      $links[] = PHPWS_Text::moduleLink(_("Deactivate"), "groups", $linkVar);
    } else {
      $linkVar['command'] = "activateGroup";
      $links[] = PHPWS_Text::moduleLink(_("Activate"), "groups", $linkVar);
    }
    */

    return implode(" | ", $links);
  }

  function listMembers(&$group){
    $members = $group->getMembers();
    if (isset($members))
      return count($members);
    else
      return 0;
  }


}

?>