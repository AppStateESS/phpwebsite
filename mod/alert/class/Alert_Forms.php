<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

define('APST_NONE',   0);
define('APST_DAILY',  1);
define('APST_WEEKLY', 2);
define('APST_PERM',   3);

class Alert_Forms {
    var $alert = null;

    function editAlert()
    {
        $item = & $this->alert->item;

        $form = new PHPWS_Form('alert-item');

        if ($item->id) {
            $this->alert->title = dgettext('alert', 'Update Alert');
            $form->addHidden('id', $item->id);
        } else {
            $this->alert->title = dgettext('alert', 'Create Alert');
        }

        $form->addHidden('module', 'alert');
        $form->addHidden('aop', 'post_alert');

        $form->addText('title', $item->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('alert', 'Title'));

        $form->addTextArea('description', $item->getDescription());
        $form->setLabel('description', dgettext('alert', 'Description'));
        $form->useEditor('description');

        $types = $this->alert->getTypes();
        if (PHPWS_Error::logIfError($types)) {
            $this->alert->title = dgettext('alert', 'Sorry');
            $this->alert->content = dgettext('alert', 'An error occurred when trying to load alert types. Check your logs.');
            return;
        } elseif (empty($types)) {
            $this->alert->title = dgettext('alert', 'No alert types found');
            $this->alert->content = dgettext('alert', 'Please create a new alert type.');
            return;
        }

        $form->addSelect('type_id', $types);
        $form->setLabel('type_id', dgettext('alert', 'Alert type'));
        
        $form->addSubmit(dgettext('alert', 'Save'));

        $tpl = $form->getTemplate();

        $this->alert->content = PHPWS_Template::process($tpl, 'alert', 'edit_alert.tpl');
    }
    

    function editType()
    {
        $type = & $this->alert->type;

        $form = new PHPWS_Form('edit-type');

        if ($type->id) {
            $this->alert->title = dgettext('alert', 'Update alert type');
        } else {
            $this->alert->title = dgettext('alert', 'Create alert type');
        }

        $form->addHidden('module', 'alert');
        $form->addHidden('aop', 'post_type');
        $form->addText('title', $type->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('alert', 'Title'));

        $form->addCheckBox('email', 1);
        $form->setLabel('email', dgettext('alert', 'Email participants'));

        $form->addCheckBox('rssfeed', 1);
        $form->setLabel('rssfeed', dgettext('alert', 'Create RSS feed'));

        $post_types[APST_NONE] = dgettext('alert', 'Do not post on front page');
        $post_types[APST_DAILY] = dgettext('alert', 'Daily listing');
        $post_types[APST_WEEKLY] = dgettext('alert', 'Weekly listing');
        $post_types[APST_PERM] = dgettext('alert', 'Permanent top');

        $form->addRadioAssoc('post_type', $post_types);
        $form->setMatch('post_type', $type->post_type);

        $form->addTextArea('default_alert', $type->getDefaultAlert());
        $form->useEditor('default_alert');
        $form->setLabel('default_alert', dgettext('alert', 'Default message'));
        $form->addSubmit(dgettext('alert', 'Save'));
        $tpl = $form->getTemplate();

        $this->alert->content = PHPWS_Template::process($tpl, 'alert', 'edit_type.tpl');
    }

    function manageItems()
    {
        PHPWS_Core::initModClass('alert', 'Alert_Item.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pagetags['TITLE_LABEL'] = dgettext('alert', 'Title');
        $pagetags['ADD_ITEM'] = PHPWS_Text::secureLink(dgettext('alert', 'Add alert'),
                                                       'alert', array('aop'=>'edit_item'));
        $pagetags['ACTION_LABEL']    = dgettext('alert', 'Action');

        $pager = new DBPager('alert_item', 'Alert_Item');
        $pager->setModule('alert');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');

        $pager->setTemplate('manage_items.tpl');
        $content = $pager->get();

        $this->alert->title = dgettext('alert', 'Manage Alerts');
        $this->alert->content = & $content;
    }

    function manageTypes()
    {
        PHPWS_Core::initModClass('alert', 'Alert_Type.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pagetags['TITLE_LABEL'] = dgettext('alert', 'Title');
        $pagetags['ADD_TYPE'] = PHPWS_Text::secureLink(dgettext('alert', 'Add alert type'),
                                                       'alert', array('aop'=>'edit_type'));
        $pagetags['POST_TYPE_LABEL'] = dgettext('alert', 'Post type');
        $pagetags['EMAIL_LABEL']     = dgettext('alert', 'Email participants');
        $pagetags['RSSFEED_LABEL']   = dgettext('alert', 'RSS feed');
        $pagetags['ACTION_LABEL']    = dgettext('alert', 'Action');

        $pager = new DBPager('alert_type', 'Alert_Type');
        $pager->setModule('alert');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');

        $pager->setTemplate('manage_types.tpl');
        $content = $pager->get();

        $this->alert->title = dgettext('alert', 'Manage Alert Types');
        $this->alert->content = & $content;
    }

}

?>