<?php

if (!isset($_REQUEST['action'])) return;

$content = array();
PHPWS_Core::initModClass("boost", "Form.php");

switch ($_REQUEST['action']){
 case "admin":
   $content[] = Boost_Form::admin();
   break;
}// End area switch

$finalContent = implode("", $content);

Layout::add(PHPWS_ControlPanel::display($finalContent));
?>