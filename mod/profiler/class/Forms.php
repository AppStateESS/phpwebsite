<?php
/**
 * Contains the administration forms for profiler
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Profile_Forms {

    function &default_form()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'profiler');

        return $form;
    }

    function edit($profile)
    {
        $profile_types = array(PFL_STUDENT => 'Student',
                               PFL_FACULTY => 'Faculty',
                               PFL_STAFF   => 'Staff');
        $form = Profile_Forms::default_form();
        $form->addHidden('command', 'post_profile');

        $form->addText('firstname', $profile->firstname);
        $form->setLabel('firstname', _('First name'));

        $form->addText('lastname', $profile->lastname);
        $form->setLabel('lastname', _('Last name'));

        $form->addTextArea('fullstory', $profile->getFullstory());
        $form->setLabel('fullstory', _('Full story'));
        $form->useEditor('fullstory');

        $form->addTextArea('caption', $profile->getCaption());
        $form->setLabel('caption', _('Caption'));
        
        $form->addSelect('profile_type', $profile_types);
        $form->setMatch('profile_type', $profile->profile_type);
        $form->setLabel('profile_type', _('Profile type'));

        if ($profile->id) {
            $form->addHidden('profile_id', $profile->id);
            $form->addSubmit('submit', _('Update profile'));
        } else {
            $form->addSubmit('submit', _('Create profile'));
        }

        $template = $form->getTemplate();

        return PHPWS_Template::process($template, 'profiler', 'forms/edit.tpl');

    }

    function profileList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['LASTNAME']     = _('Last Name');
        $pageTags['FIRSTNAME']    = _('First Name');
        $pageTags['PROFILE_TYPE'] = _('Type');
        $pageTags['SUBMIT_DATE']  = _('Submission Date');
        $pageTags['ACTION']       = _('Action');

        $pager = & new DBPager('profiles', 'Profile');
        $pager->setModule('profiler');
        $pager->setTemplate('forms/list.tpl');
        $pager->setLink('index.php?module=profiler');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getProfileTags');
        $pager->addPageTags($pageTags);
        $pager->setSearch('lastname', 'firstname');
        $content = $pager->get();
        return $content;
    }

}


?>