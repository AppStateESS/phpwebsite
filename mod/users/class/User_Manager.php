<?php

PHPWS_Core::initCoreClass("List.php");

class User_Manager extends PHPWS_List{

  function User_Manager(){
    $listTags = array("USERNAME_LABEL"  => _("Username"),
		      "CREATED_LABEL"   => _("Created"),
		      "UPDATED_LABEL"   => _("Last Updated"),
		      "ACTIVE_LABEL"    => _("Active"),
		      "APPROVED_LABEL"  => _("Approved"),
		      "DEITY_LABEL"     => _("Deity")
		      );


    $this->setModule("users");
    //    $this->setIdColumn("id");
    $this->setClass("User_Manager");
    $this->setTable("users");
    $this->setColumns(array("username"   => TRUE,
			    "created"    => TRUE,
			    "updated"    => TRUE,
			    "active"     => TRUE,
			    "approved"   => TRUE,
			    "deity"      => TRUE
			    ));
    $this->setName("user_manager");
    $this->setTemplate("manager/users.tpl");
    $this->setOp("action[admin]=main&amp;tab=manage_users");
    $this->setPaging(array("limit"=>10,
			   "section"=>TRUE,
			   "limits"=>array(5, 10 , 25),
			   "forward"    => "&#062;&#062;",
			   "back"       => "&#060;&#060;" ));
    //    $this->setExtraListTags($form->getTemplate());
  }


}

?>