<?php

class Demo_Manager extends User_Demographic{

  function Demo_Manager($values){
    if (!is_array($values))
      return NULL;
    extract($values);

    $this->setLabel($label);
    $this->setInputType($input_type);
    $this->setProperName($proper_name);
    $this->setRequired($required);
    $this->setActive($active);
  }

  function getList(){
    
    $listTags = array("ACTIVE_LABEL"      => _("Active"),
		      "PROPER_NAME_LABEL" => _("Proper Name"),
		      "INPUT_TYPE_LABEL"  => _("Input Type"),
		      "ACTIONS_LABEL"     => _("Actions")
		      );

    $form = & new PHPWS_Form("demographic");
    $form->mergeTemplate($listTags);
    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "setActiveDemographics");

    $list = & new PHPWS_List;
    $list->setModule("users");
    $list->setIdColumn("label");
    $list->setClass("Demo_Manager");
    $list->setTable("users_demographics");
    $list->setColumns(array("input_type" => TRUE,
			    "proper_name"=> TRUE,
			    "required"   => TRUE,
			    "active"     => TRUE
			    ));
    $list->setName("demographics");
    $list->setOp("action[admin]=main&amp;tab=demographics");
    $list->setPaging(array("limit"=>5,
			   "section"=>TRUE,
			   "limits"=>array(5, 10 , 25),
			   "forward"    => "&#062;&#062;",
			   "back"       => "&#060;&#060;" ));
    $list->setExtraListTags($form->getTemplate());

    $content = $list->getList();
    $_SESSION['All_Demo'] = $list->getLastIds();
    return $content;
  }


  function getlistlabel(){
    return $this->getLabel();
  }

  function getlistproper_name(){
    return $this->getProperName(TRUE);
  }
  
  function getlistinput_type(){
    return $this->getInputType();
  }

  function getlistactive(){
    $form = & new PHPWS_Form;
    $label = "demo[" . $this->getLabel() . "]";
    $form->add($label, "checkbox", $this->getActive());
    $form->setMatch($label, $this->getActive());
    return $form->get($label);
  }
}


?>