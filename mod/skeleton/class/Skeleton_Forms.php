<?php
/**
 * skeleton - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

class Skeleton_Forms {
    public $skeleton = null;

    public function get($type)
    {
        switch ($type) {

            case 'new_skeleton':
            case 'edit_skeleton':
                if (empty($this->skeleton->skeleton)) {
                    $this->skeleton->loadSkeleton();
                }
                $this->editSkeleton();
                break;

            case 'list_skeletons':
                $this->skeleton->panel->setCurrentTab('list_skeletons');
                $this->listSkeletons();
                break;

            case 'new_bone':
                $this->selectSkeleton();
                break;

            case 'edit_bone':
                if (empty($this->skeleton->bone)) {
                    $this->skeleton->loadBone();
                }
                $this->editBone();
                break;

            case 'list_bones':
                $this->skeleton->panel->setCurrentTab('list_bones');
                $this->listBones();
                break;

            case 'settings':
                $this->skeleton->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'info':
                $this->skeleton->panel->setCurrentTab('info');
                $this->showInfo();
                break;

        }

    }


    public function listSkeletons()
    {
        if (Current_User::allow('skeleton', 'edit_skeleton') && isset($_REQUEST['uop'])) {
            $link[] = Core\Text::secureLink(dgettext('skeleton', 'Add new skeleton'), 'skeleton', array('aop'=>'new_skeleton'));
            MiniAdmin::add('skeleton', $link);
        }

        $ptags['TITLE_HEADER'] = dgettext('skeleton', 'Title');
        $ptags['DIED_HEADER'] = dgettext('skeleton', 'Died');
        $ptags['BONES_HEADER'] = dgettext('skeleton', 'Bones');

        Core\Core::initModClass('skeleton', 'Skeleton_Skeleton.php');
                $pager = new Core\DBPager('skeleton_skeletons', 'Skeleton_Skeleton');
        $pager->setModule('skeleton');

        /* I am not using the next line in this mod, I just leave it
         * as a reminder of addWhere()
        if (!Current_User::isUnrestricted('skeleton')) {
            $pager->addWhere('active', 1);
        }
        */

        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_skeletons.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'new_skeleton';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('skeleton', 'Check your %s then create a %s to begin'), Core\Text::secureLink(dgettext('skeleton', 'Settings'), 'skeleton', $vars),  Core\Text::secureLink(dgettext('skeleton', 'New Skeleton'), 'skeleton', $vars2));
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');
        $pager->cacheQueries();

        $this->skeleton->content = $pager->get();
        $this->skeleton->title = dgettext('skeleton', 'Skeleton Skeletons');
    }


    public function listBones()
    {
        $ptags['TITLE_HEADER'] = dgettext('skeleton', 'Name');
        $ptags['SKELETON_HEADER'] = dgettext('skeleton', 'Skeleton');

        Core\Core::initModClass('skeleton', 'Skeleton_Bone.php');
                $pager = new Core\DBPager('skeleton_bones', 'Skeleton_Bone');
        $pager->setModule('skeleton');
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_bones.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'menu';
            $vars2['tab']  = 'new_bone';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('skeleton', 'Check your %s then create a %s to begin'), Core\Text::secureLink(dgettext('skeleton', 'Settings'), 'skeleton', $vars),  Core\Text::secureLink(dgettext('skeleton', 'New Bone'), 'skeleton', $vars2));
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');
        $pager->cacheQueries();

        $this->skeleton->content = $pager->get();
        $this->skeleton->title = dgettext('skeleton', 'Skeleton Bones');
    }


    public function editSkeleton()
    {
        $form = new Core\Form('skeleton_skeleton');
        $skeleton = & $this->skeleton->skeleton;

        $form->addHidden('module', 'skeleton');
        $form->addHidden('aop', 'post_skeleton');
        if ($skeleton->id) {
            $form->addHidden('id', $skeleton->id);
            $form->addSubmit(dgettext('skeleton', 'Update'));
            $this->skeleton->title = dgettext('skeleton', 'Update skeleton skeleton');
        } else {
            $form->addSubmit(dgettext('skeleton', 'Create'));
            $this->skeleton->title = dgettext('skeleton', 'Create skeleton skeleton');
        }

        $form->addText('title', $skeleton->getTitle());
        $form->setSize('title', 40);
        $form->setRequired('title');
        $form->setLabel('title', dgettext('skeleton', 'Title'));

        $form->addTextArea('description', $skeleton->getDescription());
        $form->useEditor('description', true, true, 0, 0, 'tinymce');
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setRequired('description');
        $form->setLabel('description', dgettext('skeleton', 'Description'));

        if (Core\Settings::get('skeleton', 'enable_files')) {
            Core\Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::fileManager('file_id', $skeleton->file_id);
            $manager->imageOnly();
            $manager->maxImageWidth(Core\Settings::get('skeleton', 'max_width'));
            $manager->maxImageHeight(Core\Settings::get('skeleton', 'max_height'));
            if ($manager) {
                $form->addTplTag('FILE_MANAGER', $manager->get());
            }
        }

        $form->addText('died', $skeleton->getDied('%Y/%m/%d %H:%M'));
        $form->setLabel('died', dgettext('skeleton', 'Died, date/time'));
        $form->setSize('died', 20);

        $tpl = $form->getTemplate();

        $tpl['DETAILS_LABEL'] = dgettext('skeleton', 'Details');

        $jscal['form_name'] = 'skeleton_skeleton';
        $jscal['type']      = 'text_clock';

        $jscal['date_name'] = 'died';
        $tpl['DIED_CAL'] = javascript('js_calendar', $jscal);

        $tpl['EXAMPLE'] = 'YY/MM/DD HH:MM';

        $this->skeleton->content = Core\Template::process($tpl, 'skeleton', 'edit_skeleton.tpl');
    }


    public function editBone()
    {
        $form = new Core\Form;
        $bone = & $this->skeleton->bone;
        $skeleton = & $this->skeleton->skeleton;

        $form->addHidden('module', 'skeleton');
        $form->addHidden('aop', 'post_bone');
        $form->addHidden('skeleton_id', $skeleton->id);
        if ($bone->id) {
            $this->skeleton->title = sprintf(dgettext('skeleton', 'Update %s bone'), $skeleton->title);
            $form->addHidden('bone_id', $bone->id);
            $form->addSubmit(dgettext('skeleton', 'Update'));
        } else {
            $this->skeleton->title = sprintf(dgettext('skeleton', 'Add bone to %s'), $skeleton->title);
            $form->addSubmit(dgettext('skeleton', 'Add'));
        }

        $form->addText('title', $bone->title);
        $form->setSize('title', 40);
        $form->setRequired('title');
        $form->setLabel('title', dgettext('skeleton', 'Title'));

        $form->addTextArea('description', $bone->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setRequired('description');
        $form->setLabel('description', dgettext('skeleton', 'Description'));

        if (Core\Settings::get('skeleton', 'enable_files')) {
            Core\Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::fileManager('file_id', $bone->file_id);
//            $manager->imageOnly();
            $manager->maxImageWidth(Core\Settings::get('skeleton', 'max_width'));
            $manager->maxImageHeight(Core\Settings::get('skeleton', 'max_height'));
            if ($manager) {
                $form->addTplTag('FILE_MANAGER', $manager->get());
            }
        }

        $tpl = $form->getTemplate();
        $tpl['INFO_LABEL'] = dgettext('skeleton', 'Bone details');

        $this->skeleton->content = Core\Template::process($tpl, 'skeleton', 'edit_bone.tpl');
    }


    public function editSettings()
    {

        $form = new Core\Form('skeleton_settings');
        $form->addHidden('module', 'skeleton');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('enable_sidebox', 1);
        $form->setMatch('enable_sidebox', Core\Settings::get('skeleton', 'enable_sidebox'));
        $form->setLabel('enable_sidebox', dgettext('skeleton', 'Enable skeleton sidebox'));

        $form->addCheckbox('sidebox_homeonly', 1);
        $form->setMatch('sidebox_homeonly', Core\Settings::get('skeleton', 'sidebox_homeonly'));
        $form->setLabel('sidebox_homeonly', dgettext('skeleton', 'Show sidebox on home page only'));

        $form->addTextArea('sidebox_text', Core\Text::parseOutput(Core\Settings::get('skeleton', 'sidebox_text')));
        $form->setRows('sidebox_text', '4');
        $form->setCols('sidebox_text', '40');
        $form->setLabel('sidebox_text', dgettext('skeleton', 'Sidebox text'));

        $form->addCheckbox('enable_files', 1);
        $form->setMatch('enable_files', Core\Settings::get('skeleton', 'enable_files'));
        $form->setLabel('enable_files', dgettext('skeleton', 'Enable images and files on skeleton and bone profiles'));

        $form->addTextField('max_width', Core\Settings::get('skeleton', 'max_width'));
        $form->setLabel('max_width', dgettext('skeleton', 'Maximum image width (50-600)'));
        $form->setSize('max_width', 4,4);

        $form->addTextField('max_height', Core\Settings::get('skeleton', 'max_height'));
        $form->setLabel('max_height', dgettext('skeleton', 'Maximum image height (50-600)'));
        $form->setSize('max_height', 4,4);

        $form->addSubmit('save', dgettext('skeleton', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('skeleton', 'General Settings');

        $this->skeleton->title = dgettext('skeleton', 'Settings');
        $this->skeleton->content = Core\Template::process($tpl, 'skeleton', 'edit_settings.tpl');
    }


    public function showInfo()
    {

        $tpl['TITLE'] = dgettext('skeleton', 'Important Information');
        $tpl['INFO_1_LABEL'] = dgettext('skeleton', 'About this module:');
        $tpl['INFO_1'] = dgettext('skeleton', 'This is the first release of skeleton for the new 1.x series phpwebsite. This module is a simple demonstration module for people wishing to develop their own phpwebsite modules.');
        $tpl['INFO_2_LABEL'] = dgettext('skeleton', 'Features');
        $tpl['INFO_2'] = dgettext('skeleton', 'I have tried to demonstrate simple and basic uses of many of the methods for developing a phpwebsite module. Skeletons and bones are pretty straight forward item classes. Bones relate to skeletons. Skeletons use the core Key class. This enables lots of things like being able to use the Search class as well as Categories and easy Menu tools. The list views show the basics of using the Core\DBPager class. Skeletons and bones demonstrate how to integrate a File Cabinet file manager. Skeletons are restricted to images and Bones are wide open. Be sure to check out the ease of use of the Link, Text, Form, Settings, Permissions and Layout classes for building intelligent and rich user experiences.');
        $tpl['INFO_3_LABEL'] = dgettext('skeleton', 'Final Words');
        $tpl['INFO_3'] = dgettext('skeleton', 'Although the skeleton mod demonstrates a lot of basic core functionality, and a reasonable way to construct a module, the phpwebsite core is really very sophisticated and flexible. If I were to attempt to show everything possible in this one module, it would just get confusing :) Please spend some time reading the stuff in /docs/ as well as look through the skeleton mod code. Also, skim through the code in some of the other mods, such as blog and calendar and so on.');
        $tpl['INFO_4_LABEL'] = null;
        $tpl['INFO_4'] = null;
        $tpl['INFO_5_LABEL'] = null;
        $tpl['INFO_5'] = null;
        $tpl['DONATE'] = sprintf(dgettext('skeleton', 'If you would like to help out with the ongoing development of skeleton, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Skeleton%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->skeleton->title = dgettext('skeleton', 'Read me');
        $this->skeleton->content = Core\Template::process($tpl, 'skeleton', 'info.tpl');
    }


    public function selectSkeleton()
    {

        $form = new Core\Form('skeleton_skeletons');
        $form->addHidden('module', 'skeleton');
        $form->addHidden('aop', 'edit_bone');

        Core\Core::initModClass('skeleton', 'Skeleton_Skeleton.php');
        $db = new Core\DB('skeleton_skeletons');
        $db->addColumn('id');
        $db->addColumn('title');
        $result = $db->getObjects('Skeleton_Skeleton');

        if ($result) {
            foreach ($result as $skeleton) {
                $choices[$skeleton->id] = $skeleton->title;
            }
            $form->addSelect('skeleton_id', $choices);
            $form->setLabel('skeleton_id', dgettext('skeleton', 'Available skeletons'));
            $form->addSubmit('save', dgettext('skeleton', 'Continue'));
        } else {
            $form->addTplTag('NO_SKELETONS_NOTE', dgettext('skeleton', 'Sorry, there are no skeletons available. You will have to create a skeleton first.'));
        }

        $tpl = $form->getTemplate();
        $tpl['SKELETON_ID_GROUP_LABEL'] = dgettext('skeleton', 'Select skeleton');

        $this->skeleton->title = dgettext('skeleton', 'New bone step one');
        $this->skeleton->content = Core\Template::process($tpl, 'skeleton', 'select_skeleton.tpl');
    }



}

?>