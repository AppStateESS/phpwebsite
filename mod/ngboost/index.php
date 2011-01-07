<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Hilmar Runge <ngwebsite.net>
*/

    if (!defined('PHPWS_SOURCE_DIR')) {
        include '../../core/conf/404.html';
        exit();
    }

    PHPWS_Core::requireConfig('ngboost');

    if (NG_DEITY_ACCESS_ONLY && !Current_User::isDeity()) {
        Current_User::disallow();
    }
    if (!Current_User::authorized('ngboost')) {
        Current_User::disallow();
    }

    if (!isset($_REQUEST['action'])) {
        PHPWS_Core::errorPage(404);
    }

    define('NGANYHELP', '<div style="text-align:right;">'
                    . '<img class="ngAnyHelp" src="'.PHPWS_SOURCE_HTTP
                    . 'mod/ngboost/img/help.16.gif" alt=" ? " />'
                    . '</div>');
    define('NGJQMCLOSE', '<div style="text-align:right;">'
                    .  '<img id="ngjqmclose" class="jqmClose" src="'.PHPWS_SOURCE_HTTP
                    .  'mod/ngboost/img/cancel.16.gif" alt=" X " />'
                    .  '</div>');
    define('NGSAYOK', '<img id="ngok" src="'.PHPWS_SOURCE_HTTP.'mod/ngboost/img/ok.10.gif" alt=" ok " />');
    define('NGSAYKO', '<img id="ngko" src="'.PHPWS_SOURCE_HTTP.'mod/ngboost/img/ko.10.gif" alt=" fail " />');
    define('NGSP3',	'&nbsp;&nbsp;&nbsp;');

    PHPWS_Core::initModClass('ngboost', 'ngForm.php');
    PHPWS_Core::initModClass('controlpanel', 'Panel.php');
    PHPWS_Core::initModClass('ngboost', 'ngAction.php');

    switch ($_REQUEST['action']){
        case 'admin':
            ngBoost_Action::index();
          break;
        case 'check_all':
            ngBoost_Action::ngCheckAll();
          break;
    }

    $boostPanel = new PHPWS_Panel('ngboost');
    $boostPanel->enableSecure();
    Boost_Form::setTabs($boostPanel);

    javascriptMod('ngboost', 'ng');
    Layout::addStyle('ngboost','style.css');

    $tpl['MOCO'] = NGANYHELP.Boost_Form::listModules('core_mods');
    $tpl['MOOT'] = NGANYHELP.Boost_Form::listModules('other_mods');
    $tpl['MONO'] = NGANYHELP.'Modules not suitable for this version - to do';
    $tpl['MONE'] = NGANYHELP.'New and community modules available for this version - to do';
    $tpl['MORE'] = NGANYHELP.Boost_Form::ngTabRepo();
    $tpl['DBAC'] = NGANYHELP.Boost_Form::ngTabDB();
    $result = PHPWS_Template::process($tpl, 'ngboost', 'cptabs.tpl');

    Layout::add(PHPWS_ControlPanel::display('<h2>Modules</h2>
        <div id="ngmsg" style="font-family: monospace;" class="jqmWindow">&nbsp;</div>
        <div id="ngpar" class="jqmWindow">&nbsp;</div>'
        .$result));

?>