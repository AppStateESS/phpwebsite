<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::requireInc('pagesmith', 'error_defines.php');

class PageSmith {
    var $forms   = null;
    var $panel   = null;

    var $title   = null;
    var $message = null;
    var $content = null;

    var $page    = null;


    function admin()
    {
        if (!Current_User::allow('pagesmith')) {
            Current_User::disallow();
        }

        switch ($_REQUEST['aop']) {
        case 'menu':
            $this->loadForms();
            if (!isset($_GET['tab']) || $_GET['tab'] == 'new') {
                $this->loadPage();
                $this->forms->editPage();
            } else {
                $this->forms->pageList();
            }
            break;
        }


        $this->loadPanel();

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        $content = PHPWS_Template::process($tpl, 'pagesmith', 'admin_main.tpl');
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
    }

    function getPageTemplate($tpl_name)
    {
        $tpl_dir = $this->pageTplDir() . $tpl_name . '/structure.xml';

        if (!is_file($tpl_dir)) {
            return null;
        }

        PHPWS_Core::initCoreClass('XMLParser.php');
        $xml = new XMLParser($tpl_dir);
        if (PHPWS_Error::isError($xml->error)) {
            return $xml->error;
        }

        $result = $xml->format();
        test($result);
    }


    function loadForms()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Forms.php');
        $this->forms = new PS_Forms;
        $this->forms->ps = & $this;
    }

    function loadPage()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Page.php');
        if (@$_REQUEST['pid']) {
            $this->page = new PS_Page($_REQUEST['pid']);
        } else {
            $this->page = new PS_Page;
        }
    }

    function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('pagesmith');

        $link = 'index.php?module=pagesmith&amp;aop=menu';
        $tabs['new']  = array('title'=>dgettext('pagesmith', 'New'), 'link'=>$link);
        $tabs['list'] = array('title'=>dgettext('pagesmith', 'List'), 'link'=>$link);

        $this->panel->quickSetTabs($tabs);
        $this->panel->setModule('pagesmith');
    }

    function pageTplDir()
    {
        return PHPWS_Template::getTemplateDirectory('pagesmith')  . 'page_templates/';
    }


    function user()
    {

    }


    function viewPage()
    {

    }
}

?>