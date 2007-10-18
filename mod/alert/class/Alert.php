<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

define('APST_NONE',   0);
define('APST_WEEKLY', 1);
define('APST_DAILY',  2);
define('APST_PERM',   3);

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

    var $type_list = null;

    /**
     * The forms object. Initialized with loadForms.
     */
    var $forms = null;
    var $panel      = null;

    function user()
    {
        echo 'in user';
    }

    function viewItems()
    {
        $high_alert = false;

        $this->loadTypes();

        if (empty($this->type_list)) {
            return;
        }

        $db = new PHPWS_DB('alert_item');
        $db->loadClass('alert', 'Alert_Item.php');
        $db->setIndexBy('id');
        $db->addOrder('create_date desc');

        foreach ($this->type_list as $type) {
            $content = null;
            $db->resetWhere();
            $db->addWhere('active', 1);
            $db->addWhere('type_id', $type->id);

            switch ($type->post_type) {
            case APST_NONE:
                continue;

            case APST_DAILY:
                $alert_type = 'daily_alerts';
                $db->addWhere('create_date', mktime() - 86400, '>');
                break;

            case APST_WEEKLY:
                $alert_type = 'weekly_alerts';
                $db->addWhere('create_date', mktime() - (86400 * 7), '>');
                break;

            case APST_PERM:
                $alert_type = 'high_alerts';
                break;
            }

            $result = $db->getObjects('Alert_Item');

            if (PHPWS_Error::logIfError($result)) {
                continue;
            }

            if (empty($result) && !empty($type->default_alert)) {
                $tpl['CONTENT'] = $type->getDefaultAlert();
            } else {
                foreach($result as $item) {
                    $content[] = $item->view();
                }
                $high_alert = true;
                $tpl['CONTENT'] = implode('', $content);
            }

            $tpl['TITLE'] = $type->title;
            $tpl['CLASS'] = sprintf('alert-type-%s', $type->id);

            Layout::add(PHPWS_Template::process($tpl, 'alert', 'view_type.tpl'), 'alert', $alert_type, true);
            if ($high_alert) {
                return;
            }
        }
    }

    function loadTypes()
    {
        $db = new PHPWS_DB('alert_type');
        $db->addOrder('post_type desc');
        $db->loadClass('alert', 'Alert_Type.php');
        $db->setIndexBy('id');
        $result = $db->getObjects('Alert_Type');

        if (PHPWS_Error::logIfError($result)) {
            $this->type_list = null;
            return;
        }
        $this->type_list = & $result;
    }


    function admin()
    {
        if (!Current_User::allow('alert')) {
            Current_User::disallow();
            return;
        }

        $this->loadMessage();

        $this->loadPanel();

        if (isset($_REQUEST['aop'])) {
            $command = $_REQUEST['aop'];
        }

        if ($command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
        case 'new':
        case 'edit_item':
            $this->loadItem();
            $this->loadForms();
            $this->forms->editItem();
            break;

        case 'list':
            $this->panel->setCurrentTab('list');
            $this->loadForms();
            $this->forms->manageItems();
            break;

        case 'post_item':
            $this->loadItem();
            if ($this->postItem()) {
                // need to process after save
                if (PHPWS_Error::logIfError($this->item->save())) {
                    $this->sendMessage(dgettext('alert', 'An error occurred. Could not save alert.'), 'list');
                } else {
                    $this->sendMessage(dgettext('alert', 'Alert saved.'), 'list');
                }
            } else {
                $this->loadForms();
                $this->forms->editItem();
            }
            break;

        case 'delete_item':
            $this->loadItem();
            if ($this->item->delete()) {
                $this->sendMessage(dgettext('alert', 'Deleted alert.'), 'list');
            } else {
                $this->sendMessage(dgettext('alert', 'Could not delete alert.'), 'list');
            }
            break;

        case 'delete_type':
            $this->loadType();
            if ($this->type->delete()) {
                $this->sendMessage(dgettext('alert', 'Deleted alert type.'), 'types');
            } else {
                $this->sendMessage(dgettext('alert', 'Could not delete alert type.'), 'types');
            }
            break;

        case 'types':
            $this->loadForms();
            $this->forms->manageTypes();
            break;

        case 'edit_type':
            $this->loadType();
            $this->loadForms();
            $this->forms->editType();
            break;

        case 'post_type':
            $this->loadType();
            if ($this->postType()) {
                if (PHPWS_Error::logIfError($this->type->save())) {
                    $this->sendMessage(dgettext('alert', 'An error occurred. Could not save alert type.'), 'types');
                } else {
                    $this->sendMessage(dgettext('alert', 'Type saved.'), 'types');
                }
            } else {
                $this->loadForms();
                $this->forms->editType();
            }
            break;
        }

        Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
    }

    function sendMessage($message, $aop)
    {
        $_SESSION['Alert_Message'] = $message;
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('alert', array('aop'=>$aop), true));
    }

    function loadMessage()
    {
        if (isset($_SESSION['Alert_Message'])) {
            $this->message = $_SESSION['Alert_Message'];
            PHPWS_Core::killSession('Alert_Message');
        }
    }

    function loadItem()
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

    function postItem()
    {
        $allgood = true;

        if (!isset($_POST['type_id'])) {
            $this->message = dgettext('alert', 'Error: Missing alert type id.');
            return false;
        }

        $item = & $this->item;

        $item->image_id = (int)$_POST['image_id'];
        $item->type_id = (int)$_POST['type_id'];

        if (empty($_POST['title'])) {
            $this->message = dgettext('alert', 'Please give your alert a title.');
            $allgood = false;
        } else {
            $item->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $this->message = dgettext('alert', 'Please give your alert a description.');
            $allgood = false;
        } else {
            $item->setDescription($_POST['description']);
        }

        return $allgood;
    }

    function postType()
    {
        $allgood = true;

        $type = & $this->type;
        if (empty($_POST['title'])) {
            $this->message = dgettext('alert', 'Please give your alert type a title.');
            $allgood = false;
        } else {
            $type->setTitle($_POST['title']);
        }

        $type->email     = isset($_POST['email']);
        $type->rssfeed   = isset($_POST['rssfeed']);
        $type->post_type = $_POST['post_type'];
        $type->setDefaultAlert($_POST['default_alert']);

        return $allgood;
    }
    
}

?>