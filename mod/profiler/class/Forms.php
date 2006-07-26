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

        $div = & new PHPWS_DB('profiler_division');
        $div->addWhere('show_homepage', 1);
        $div->addOrder('title');
        $div->addColumn('id');
        $div->addColumn('title');
        $div->setIndexBy('id');
        $profile_types = $div->select('col');

        if (empty($profile_types)) {
            $vars['tab'] = 'division';
            return PHPWS_Text::secureLink(_('Please make a profile division type first.'), 'profiler', $vars);
        }

        $form = Profile_Forms::default_form();
        $form->addHidden('command', 'post_profile');

        $form->addText('firstname', $profile->firstname);
        $form->setLabel('firstname', _('First name'));

        $form->addText('lastname', $profile->lastname);
        $form->setLabel('lastname', _('Last name'));

        $form->addTextArea('fullstory', $profile->getFullstory());
        $form->setLabel('fullstory', _('Full story'));
        $form->useEditor('fullstory');
        $form->setCols('fullstory', 50);
        $form->setRows('fullstory', 10);

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
        $pager->db->addColumn('profiles.*');
        $pager->db->addColumn('profiler_division.title', NULL, '_division_title');
        $pager->db->addWhere('profile_type', 'profiler_division.id');
        $pager->setModule('profiler');
        $pager->setTemplate('forms/list.tpl');
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
        $form = Profile_Forms::default_form();
        $form->addHidden('command', 'save_settings');

        $form->addSelect('profile_number', array(1, 2, 3, 4));
        $form->reindexValue('profile_number');
        $form->setMatch('profile_number', PHPWS_Settings::get('profiler', 'profile_number'));
        $form->setLabel('profile_number', _('Number of profiles'));

        $form->addCheckbox('profile_homepage', 1);
        $form->setMatch('profile_homepage', PHPWS_Settings::get('profiler', 'profile_homepage'));
        $form->setLabel('profile_homepage', _('Enable profile homepage'));

        $form->addSubmit(_('Save settings'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'profiler', 'forms/settings.tpl');
    }

    function divisionList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('profiler', 'Division.php');

        $js_vars['height']  = '200';
        $js_vars['address'] = 'index.php?module=profiler&amp;command=edit_division&authkey=' . Current_User::getAuthKey();
        $js_vars['label']   = _('Add division');
        $pageTags['ADD_LINK'] = javascript('open_window', $js_vars);

        $pageTags['TITLE_LABEL']  = _('Title');
        $pageTags['ACTION_LABEL'] = _('Action');
        $pageTags['ID_LABEL']     = _('Division ID');

        $pager = & new DBPager('profiler_division', 'Profiler_Division');
        $pager->setModule('profiler');
        $pager->setTemplate('forms/division_list.tpl');
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getTags');
        $pager->addPageTags($pageTags);
        return $pager->get();
    }

    function editDivision(&$division, $error=FALSE)
    {
        $form = & new PHPWS_Form('division');
        $form->addHidden('module', 'profiler');
        $form->addHidden('command', 'update_division');

        if ($division->id) {
            $form->addHidden('division_id', $division->id);
            $form->addTplTag('PAGE_TITLE', _('Update Division'));
        } else {
            $form->addTplTag('PAGE_TITLE', _('Create Division'));
        }

        $form->addSubmit(_('Save'));
        $form->addText('title', $division->title);
        $form->setLabel('title', _('Division title'));

        $form->addButton('close', _('Cancel'));
        $form->setExtra('close', 'onclick="window.close()"');

        $template = $form->getTemplate();
        if ($error) {
            $template['ERROR'] = _('Your title is empty or already in use. Enter another.');
        }
        return PHPWS_Template::process($template, 'profiler', 'forms/division_edit.tpl');
    }

}


?>