<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert {
    /**
     * First three variables display the content of the module
     */
    var $title      = null;
    var $content    = null;
    var $message    = null;

    /**
     * An alert item
     */
    var $item  = null;

    /**
     * An alert type object
     */
    var $type  = null;

    /**
     * The forms object. Initialized with loadForms.
     */
    var $forms = null;
    var $panel      = null;

    function admin()
    {
        if (!Current_User::allow('alert')) {
            Current_User::disallow();
            return;
        }

        $this->loadPanel();

        if (isset($_REQUEST['aop'])) {
            $command = $_REQUEST['aop'];
        }

        if ($command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'new':
            $this->loadAlert();
            $this->loadForms();
            $this->forms->editAlert();
            break;


        case 'edit_type':
            $this->loadType();
            $this->loadForms();
            $this->forms->editType();
            break;

        case 'types':
            $this->loadForms();
            $this->forms->manageTypes();
        }

        Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
    }

    function loadAlert()
    {
        PHPWS_Core::initModClass('alert', 'Alert_Item.php');
        if ($_REQUEST['id']) {
            $this->item = new Alert_Item($_REQUEST['id']);
            if (!$this->item->id) {
                $this->message = dgettext('alert', 'Could not locate alert item.');
            }
        } else {
            $this->item = new Alert_Item;
        }
    }

    function loadType()
    {
        PHPWS_Core::initModClass('alert', 'Alert_Type.php');
        if ($_REQUEST['type_id']) {
            $this->type = new Alert_Type($_REQUEST['type_id']);
            if (!$this->type->id) {
                $this->message = dgettext('alert', 'Could not locate alert type.');
            }
        } else {
            $this->type = new Alert_Type;
        }
    }

    function loadForms()
    {
        PHPWS_Core::initModClass('alert', 'Alert_Forms.php');
        $this->forms = new Alert_Forms;
        $this->forms->alert = & $this;
    }

    function loadPanel()
    {
        $this->panel = new PHPWS_Panel('alert');
        $link = 'index.php?module=alert&amp;aop=main';

        $tabs['new']   = array('title'=>dgettext('alert', 'New'), 'link'=>$link, 
                               'link_title'=>dgettext('alert', 'Create a new alert'));
        $tabs['list']  = array('title'=>dgettext('alert', 'List'), 'link'=>$link, 
                               'link_title'=>dgettext('alert', 'List all alerts in the system.'));
        $tabs['types'] = array('title'=>dgettext('alert', 'Alert Types'), 'link'=>$link, 
                               'link_title'=>dgettext('alert', 'Create, update and define alert types.'));

        $this->panel->quickSetTabs($tabs);
    }

    function getTypes($mode='form')
    {
        $db = new PHPWS_DB('alert_type');
        switch ($mode) {
        case 'form':
            $db->addColumn('id');
            $db->addColumn('title');
            $db->setIndexBy('id');
            $types = $db->select('col');
            break;
        }

        if (!empty($types) || PHPWS_Error::isError($types)) {
            return $types;
        } else {
            return null;
        }
    }


}

?>