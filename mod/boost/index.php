<?php

if (!isset($_REQUEST['action'])) return;

$content = array();
PHPWS_Core::initModClass("boost", "Form.php");
PHPWS_Core::initModClass("controlpanel", "Panel.php");

$boostPanel = & new PHPWS_Panel("boost");
Boost_Form::setTabs($boostPanel);

switch ($_REQUEST['action']){
 case "admin":
   $content[] = Boost_Form::listModules(Boost_Form::boostTab($boostPanel));
   break;

 case "check":
   PHPWS_Core::initModClass("boost", "Action.php");
   $content[] = Boost_Action::checkupdate($_REQUEST['opmod']);
   break;

 case "aboutView":
   PHPWS_Core::initModClass("boost", "Boost.php");
   PHPWS_Boost::aboutView($_REQUEST['aboutmod']);
   break;
}// End area switch

$boostPanel->setContent(implode("", $content));
$finalContent = $boostPanel->display();
Layout::add(PHPWS_ControlPanel::display($finalContent));
?>