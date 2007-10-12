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
        $form->addHidden('module', 'alert');
        $form->addText('title', $item->title);
        $form->addTextArea('description', $item->getDescription());

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
        $form->addText('title', $type->title);
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
        $form->addSubmit(dgettext('alert', 'Save'));
        $tpl = $form->getTemplate();

        $this->alert->content = PHPWS_Template::process($tpl, 'alert', 'edit_type.tpl');
    }

    function manageTypes()
    {
        PHPWS_Core::initModClass('alert', 'Alert_Type.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pagetags['TITLE_LABEL'] = dgettext('alert', 'Title');
        $pagetags['ADD_TYPE'] = PHPWS_Text::secureLink(dgettext('alert', 'Add alert type'),
                                                       'alert', array('aop'=>'edit_type'));

        $pager = new DBPager('alert_type', 'Alert_Type');
        $pager->setModule('alert');
        $pager->addPageTags($pagetags);

        $pager->setTemplate('manage_types.tpl');
        $content = $pager->get();

        $this->alert->title = dgettext('alert', 'Manage Alert Types');
        $this->alert->content = $content;
    }

}

?>