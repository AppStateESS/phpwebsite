<?php
if (!Current_User::isDeity() || !isset($_REQUEST['action'])) return;

$content = array();
PHPWS_Core::initModClass('boost', 'Form.php');
PHPWS_Core::initModClass('controlpanel', 'Panel.php');

$boostPanel = & new PHPWS_Panel('boost');
Boost_Form::setTabs($boostPanel);

switch ($_REQUEST['action']){
 case 'admin':
   $content[] = Boost_Form::listModules(Boost_Form::boostTab($boostPanel));
   break;

 case 'check':
   PHPWS_Core::initModClass('boost', 'Action.php');
   $content[] = Boost_Action::checkupdate($_REQUEST['opmod']);
   break;

 case 'aboutView':
   PHPWS_Core::initModClass('boost', 'Boost.php');
   PHPWS_Boost::aboutView($_REQUEST['aboutmod']);
   break;

 case 'install':
   PHPWS_Core::initModClass('boost', 'Action.php');
   $result = Boost_Action::installModule($_REQUEST['opmod']);
   if (PEAR::isError($result)) {
     PHPWS_Error::log($result);
     $content[] = _('An error occurred while installing this module.') .
       ' ' . _('Please check your error logs.');
   } else {
     $content[] = $result;
   }
   break;

 case 'uninstall':
   PHPWS_Core::initModClass('boost', 'Action.php');
   $content[] = Boost_Action::uninstallModule($_REQUEST['opmod']);
   break;

 case 'update':
   PHPWS_Core::initModClass('boost', 'Action.php');
   $content[] = Boost_Action::updateModule($_REQUEST['opmod']);
   break;
}// End area switch

$boostPanel->setContent(implode('', $content));
$finalContent = $boostPanel->display();
Layout::add(PHPWS_ControlPanel::display($finalContent));
?>