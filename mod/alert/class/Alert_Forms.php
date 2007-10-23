<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Forms {
    var $alert = null;

    function editItem()
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $item = & $this->alert->item;
        $manager = Cabinet::imageManager($this->image_id, 'image_id', 500, 500, 1);

        $form = new PHPWS_Form('alert-item');

        if ($item->id) {
            $this->alert->title = dgettext('alert', 'Update Alert');
            $form->addHidden('id', $item->id);
        } else {
            $this->alert->title = dgettext('alert', 'Create Alert');
        }

        $form->addHidden('module', 'alert');
        $form->addHidden('aop', 'post_item');
        $form->addHidden('image_id', $this->image_id);

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
        $manager->setNoimageMaxWidth(200);
        $manager->setNoimageMaxHeight(200);
        $tpl['IMAGE'] = $manager->get();
        $tpl['IMAGE_LABEL'] = dgettext('alert', 'Image');

        $this->alert->content = PHPWS_Template::process($tpl, 'alert', 'edit_item.tpl');
    }
    

    function editType()
    {
        $type = & $this->alert->type;

        $form = new PHPWS_Form('edit-type');

        if ($type->id) {
            $this->alert->title = dgettext('alert', 'Update alert type');
            $form->addHidden('type_id', $type->id);
        } else {
            $this->alert->title = dgettext('alert', 'Create alert type');
        }

        $form->addHidden('module', 'alert');
        $form->addHidden('aop', 'post_type');
        $form->addText('title', $type->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('alert', 'Title'));

        $form->addCheckBox('email', 1);
        $form->setMatch('email', $type->email);
        $form->setLabel('email', dgettext('alert', 'Email participants'));

        $form->addCheckBox('rssfeed', 1);
        $form->setMatch('rssfeed', $type->rssfeed);
        $form->setLabel('rssfeed', dgettext('alert', 'Create RSS feed'));

        $post_types[APST_NONE]   = dgettext('alert', 'Do not post on front page');
        $post_types[APST_WEEKLY] = dgettext('alert', 'Weekly listing');
        $post_types[APST_DAILY]  = dgettext('alert', 'Daily listing');
        $post_types[APST_PERM]   = dgettext('alert', 'High alert!');

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

        $pagetags['CONTACT_ALERT'] = $this->contactAlert();

        PHPWS_Core::initModClass('alert', 'Alert_Item.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pagetags['TITLE_LABEL'] = dgettext('alert', 'Title');
        $pagetags['ADD_ITEM'] = PHPWS_Text::secureLink(dgettext('alert', 'Add alert'),
                                                       'alert', array('aop'=>'edit_item'));
        $pagetags['ACTION_LABEL'] = dgettext('alert', 'Action');
        $pagetags['ACTIVE_LABEL'] = dgettext('alert', 'Active');
        $pagetags['CREATE_DATE_LABEL'] = dgettext('alert', 'Created');
        $pagetags['UPDATE_DATE_LABEL'] = dgettext('alert', 'Updated');

        $pagetags['NAME_LABEL'] = dgettext('alert', 'by');

        $pager = new DBPager('alert_item', 'Alert_Item');
        $pager->setModule('alert');
        $pager->addPageTags($pagetags);
        $pager->addRowTags('rowTags');

        $pager->setTemplate('manage_items.tpl');
        $content = $pager->get();

        $this->alert->title = dgettext('alert', 'Manage Alerts');
        $this->alert->content = & $content;
    }

    function contactAlert()
    {
        if (!Current_User::allow('alert', 'allow_contact')) {
            return null;
        }

        $contact_needed = $this->alert->contactNeeded();

        if (empty($contact_needed)) {
            return null;
        }

        if (PHPWS_Error::logIfError($contact_needed)) {
            return dgettext('alert', 'An error occurred while checking contact status.');
        } else {
            $tpl['TITLE'] = dgettext('alert', 'The following alerts need to send email notices.');
            foreach ($contact_needed as $item) {
                $subtpl['ITEM_TITLE'] = $item->title;
                if ($item->contact_complete == 1) {
                    $label = dgettext('alert', 'Finish incomplete mailing');
                } else {
                    $label = dgettext('alert', 'Start mailing');
                }
                $subtpl['STATUS'] = PHPWS_Text::secureLink($label, 'alert', array('aop'=>'send_email', 'id'=> $item->id));
                $tpl['rows'][] = $subtpl;
            }

        }
        Layout::addStyle('alert');
        return PHPWS_Template::process($tpl, 'alert', 'contact_links.tpl');

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

    function settings()
    {
        $settings = PHPWS_Settings::get('alert');

        $form = new PHPWS_Form('alert-settings');
        $form->addHidden('module', 'alert');
        $form->addHidden('aop', 'post_settings');

        $form->addText('date_format', $settings['date_format']);
        $form->setTitle('date_format', 'Format uses PHP strftime standard');
        $form->setLabel('date_format', dgettext('alert', 'Date format'));
        $form->addSubmit('submit', dgettext('alert', 'Save settings'));

        $tpl = $form->getTemplate();
        $this->alert->title = dgettext('alert', 'Alert Settings');
        $this->alert->content = PHPWS_Template::process($tpl, 'alert', 'settings.tpl');
    }

}

?>