<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Branch_Admin {
    var $panel   = NULL;
    var $content = NULL;

    function main()
    {
        $content = NULL;
        // Create the admin panel
        $this->cpanel();

        // Direct the path command
        $this->direct();

        // Display the results
        $this->displayPanel();
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $newLink = 'index.php?module=branch&amp;command=new';
        $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
        
        $listLink = 'index.php?module=branch&amp;command=list';
        $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

        $tabs['new'] = &$newCommand;
        $tabs['list'] = &$listCommand;

        $panel = & new PHPWS_Panel('branch');
        $panel->quickSetTabs($tabs);

        $panel->setModule('branch');
        $this->panel = &$panel;
    }

    function displayPanel()
    {
        $this->panel->setContent($this->content);
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
    }

    function direct()
    {
        if (!@$command = $_REQUEST['command']) {
            $command = $this->panel->getCurrentTab();
        }

        $this->content = $command;
    }

}

?>