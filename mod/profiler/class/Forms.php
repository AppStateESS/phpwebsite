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

    public function default_form()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'profiler');

        return $form;
    }

    public function edit($profile)
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $div = new PHPWS_DB('profiler_division');
        $div->addWhere('show_homepage', 1);
        $div->addOrder('title');
        $div->addColumn('id');
        $div->addColumn('title');
        $div->setIndexBy('id');
        $profile_types = $div->select('col');

        if (empty($profile_types)) {
            $vars['tab'] = 'division';
            $msg = dgettext('profiler', 'Please make a profile division type first.');
            return PHPWS_Text::secureLink($msg, 'profiler', $vars);
        }

        $form = Profile_Forms::default_form();
        $form->addHidden('command', 'post_profile');

        $form->addText('firstname', $profile->firstname);
        $form->setLabel('firstname', dgettext('profiler', 'First name'));

        $form->addText('lastname', $profile->lastname);
        $form->setLabel('lastname', dgettext('profiler', 'Last name'));

        $form->addTextArea('fullstory', $profile->getFullstory());
        $form->setLabel('fullstory', dgettext('profiler', 'Full story'));
        $form->useEditor('fullstory');
        $form->setCols('fullstory', 50);
        $form->setRows('fullstory', 10);

        $form->addTextArea('caption', $profile->getCaption());
        $form->setLabel('caption', dgettext('profiler', 'Caption'));

        $form->addSelect('profile_type', $profile_types);
        $form->setMatch('profile_type', $profile->profile_type);
        $form->setLabel('profile_type', dgettext('profiler', 'Profile type'));

        $form->addText('email', $profile->email);
        $form->setLabel('email', dgettext('profiler', 'Email address'));
        $form->setSize('email', 30);

        $form->addText('website', $profile->website);
        $form->setLabel('website', dgettext('profiler', 'Web site address'));
        $form->setSize('website', 50);

        if ($profile->id) {
            $form->addHidden('profile_id', $profile->id);
            $form->addSubmit('submit', dgettext('profiler', 'Update profile'));
        } else {
            $form->addSubmit('submit', dgettext('profiler', 'Create profile'));
        }

        $template = $form->getTemplate();

        $lmanager = Cabinet::fileManager('photo_large',$profile->photo_large);
        $lmanager->setMaxWidth(720);
        $lmanager->setMaxHeight(300);
        $lmanager->imageOnly();

        $mmanager = Cabinet::fileManager('photo_medium', $profile->photo_medium);
        $mmanager->setMaxWidth(200);
        $mmanager->setMaxHeight(200);
        $mmanager->imageOnly();

        $smanager = Cabinet::fileManager('photo_small', $profile->photo_small);
        $smanager->setMaxWidth(150);
        $smanager->setMaxHeight(150);
        $smanager->imageOnly();

        $template['PHOTO_LARGE']  = $lmanager->get();
        $template['PHOTO_MEDIUM'] = $mmanager->get();
        $template['PHOTO_SMALL']  = $smanager->get();

        $template['PHOTO_LARGE_LABEL'] = dgettext('profiler', 'Large photo');
        $template['PHOTO_MEDIUM_LABEL'] = dgettext('profiler', 'Medium photo');
        $template['PHOTO_SMALL_LABEL'] = dgettext('profiler', 'Small photo');

        return PHPWS_Template::process($template, 'profiler', 'forms/edit.tpl');
    }

    public function profileList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pageTags['LASTNAME']     = dgettext('profiler', 'Last Name');
        $pageTags['FIRSTNAME']    = dgettext('profiler', 'First Name');
        $pageTags['PROFILE_TYPE'] = dgettext('profiler', 'Type');
        $pageTags['SUBMIT_DATE']  = dgettext('profiler', 'Submission Date');
        $pageTags['ACTION']       = dgettext('profiler', 'Action');

        $pager = new DBPager('profiles', 'Profile');
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
        return $pager->get();
    }

    public function settings()
    {
        $form = Profile_Forms::default_form();
        $form->addHidden('command', 'save_settings');

        $form->addSelect('profile_number', array(1, 2, 3, 4));
        $form->reindexValue('profile_number');
        $form->setMatch('profile_number', PHPWS_Settings::get('profiler', 'profile_number'));
        $form->setLabel('profile_number', dgettext('profiler', 'Number of profiles'));

        $form->addCheckbox('profile_homepage', 1);
        $form->setMatch('profile_homepage', PHPWS_Settings::get('profiler', 'profile_homepage'));
        $form->setLabel('profile_homepage', dgettext('profiler', 'Enable profile homepage'));

        $form->addSubmit(dgettext('profiler', 'Save settings'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'profiler', 'forms/settings.tpl');
    }

    public function divisionList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('profiler', 'Division.php');
        $js_vars['height']  = '200';
        $js_vars['address'] = 'index.php?module=profiler&amp;command=edit_division&authkey=' . Current_User::getAuthKey();
        $js_vars['label']   = dgettext('profiler', 'Add division');
        $pageTags['ADD_LINK'] = javascript('open_window', $js_vars);

        $pageTags['TITLE_LABEL']  = dgettext('profiler', 'Title');
        $pageTags['ACTION_LABEL'] = dgettext('profiler', 'Action');
        $pageTags['ID_LABEL']     = dgettext('profiler', 'Division ID');

        $pager = new DBPager('profiler_division', 'Profiler_Division');
        $pager->setModule('profiler');
        $pager->setTemplate('forms/division_list.tpl');
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getTags');
        $pager->addPageTags($pageTags);
        return $pager->get();
    }

    public function editDivision(Profiler_Division $division, $error=FALSE)
    {
        $form = new PHPWS_Form('division');
        $form->addHidden('module', 'profiler');
        $form->addHidden('command', 'update_division');
        if ($division->id) {
            $form->addHidden('division_id', $division->id);
            $form->addTplTag('PAGE_TITLE', dgettext('profiler', 'Update Division'));
        } else {
            $form->addTplTag('PAGE_TITLE', dgettext('profiler', 'Create Division'));
        }

        $form->addSubmit(dgettext('profiler', 'Save'));
        $form->addText('title', $division->title);
        $form->setLabel('title', dgettext('profiler', 'Division title'));

        $form->addButton('close', dgettext('profiler', 'Cancel'));
        $form->setExtra('close', 'onclick="window.close()"');

        $template = $form->getTemplate();
        if ($error) {
            $template['ERROR'] = dgettext('profiler', 'Your title is empty or already in use. Enter another.');
        }
        return PHPWS_Template::process($template, 'profiler', 'forms/division_edit.tpl');
    }

}


?>