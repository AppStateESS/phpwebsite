<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}
translate('boost');
PHPWS_Core::requireConfig('boost');

if (DEITY_ACCESS_ONLY && !Current_User::isDeity()) {
    Current_User::disallow();
 }

if (!Current_User::authorized('boost')) {
    Current_User::disallow();
 }

if (!isset($_REQUEST['action'])) {
    PHPWS_Core::errorPage(404);
 }

$content = array();
PHPWS_Core::initModClass('boost', 'Form.php');
PHPWS_Core::initModClass('controlpanel', 'Panel.php');
PHPWS_Core::initModClass('boost', 'Action.php');

$boostPanel = & new PHPWS_Panel('boost');
$boostPanel->enableSecure();
Boost_Form::setTabs($boostPanel);

switch ($_REQUEST['action']){
 case 'admin':
     $content[] = Boost_Form::listModules(Boost_Form::boostTab($boostPanel));
     break;

 case 'check':
     $content[] = PHPWS_Text::backLink(_('Return to Boost')) . '<br />';
     $content[] = Boost_Action::checkupdate($_REQUEST['opmod']);
     break;

 case 'check_all':
     Boost_Action::checkAll();
     $content[] = Boost_Form::listModules(Boost_Form::boostTab($boostPanel));
     break;

 case 'aboutView':
     PHPWS_Core::initModClass('boost', 'Boost.php');
     PHPWS_Boost::aboutView($_REQUEST['aboutmod']);
     break;

 case 'install':
     $content[] = PHPWS_Text::backLink(_('Return to Boost')) . '<br />';

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
     $content[] = PHPWS_Text::backLink(_('Return to Boost')) . '<br />';
     $content[] = Boost_Action::uninstallModule($_REQUEST['opmod']);
     break;

 case 'update_core':
     $content[] = PHPWS_Text::backLink(_('Return to Boost')) . '<br />';
     $content[] = Boost_Action::updateModule('core');
     break;

 case 'update':
     $content[] = PHPWS_Text::backLink(_('Return to Boost')) . '<br />';
     $content[] = Boost_Action::updateModule($_REQUEST['opmod']);
     break;

 case 'show_dependency':
     $content[] = Boost_Action::showDependency($_REQUEST['opmod']);
     break;

 case 'show_depended_upon':
     $content[] = Boost_Action::showDependedUpon($_REQUEST['opmod']);
     break;

}// End area switch

$boostPanel->setContent(implode('', $content));
$finalContent = $boostPanel->display();
Layout::add(PHPWS_ControlPanel::display($finalContent));
translate();
?>