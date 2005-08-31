<?php
/**
 * Contains the administration forms for profiler
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

define('MAX_PHOTO_WIDTH', 640);
define('MAX_PHOTO_HEIGHT', 480);
define('PR_MAX_FILE_SIZE', 60000);

class Profile_Forms {

    function &default_form()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'profiler');

        return $form;
    }

    function edit($profile)
    {
        PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');

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


        $template['PHOTO_LARGE'] = Profile_Forms::getManager($profile->photo_large, 'photo_large');
        $template['PHOTO_MEDIUM'] = Profile_Forms::getManager($profile->photo_medium, 'photo_medium');
        $template['PHOTO_SMALL'] = Profile_Forms::getManager($profile->photo_small, 'photo_small');


        $template['PHOTO_LARGE_LABEL'] = _('Large photo');
        $template['PHOTO_MEDIUM_LABEL'] = _('Medium photo');
        $template['PHOTO_SMALL_LABEL'] = _('Small photo');
        
        
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        return PHPWS_Template::process($template, 'profiler', 'forms/edit.tpl');

    }

    function getManager($image_id, $image_name)
    {
        $manager = & new FC_Image_Manager($image_id);
        $manager->setMaxWidth(MAX_PHOTO_WIDTH);
        $manager->setMaxHeight(MAX_PHOTO_HEIGHT);
        $manager->setMaxSize(PR_MAX_FILE_SIZE);
        $manager->setModule('profiler');
        $manager->setItemname($image_name);

        return $manager->get();
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

    function settings()
    {
        $settings['profile_number'] = 3;
        $settings['profile_sidebar'] = 1;

        $form = & new PHPWS_Form;
        $form->setLegend(_('Profiler Settings'));
        $form->addHidden('module', 'profiler');
        $form->addHidden('command', 'save_settings');

        $form->addSelect('profile_number', array(1, 2, 3, 4));
        $form->reindexValue('profile_number');
        $form->setMatch('profile_number', $settings['profile_number']);
        $form->setLabel('profile_number', _('Number of profiles'));

        $form->addCheckbox('profile_sidebar', 1);
        $form->setMatch('profile_sidebar', $settings['profile_sidebar']);
        $form->setLabel('profile_sidebar', _('Enable profile sidebar'));

        $form->addSubmit(_('Save settings'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'profiler', 'forms/settings.tpl');
    }

}

?>