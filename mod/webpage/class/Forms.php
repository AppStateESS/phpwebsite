<?php
/**
 * Holds the administrative forms for Web Page module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Webpage_Forms {

    function pagePanel($volume, $version_id=0)
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link['link'] = 'index.php?module=webpage&wp_admin=edit_webpage&volume_id=' . $volume->id;
        if ($version_id) {
            $link['link'] .= '&version_id=' . $version_id;
        }

        $link['title'] = dgettext('webpage', 'Header');
        $tabs['header'] = $link;

        if (!empty($volume->_pages)) {
            foreach ($volume->_pages as $page_id => $page) {
                if (!empty($page->title)) {
                    $link['link_title'] = & $page->title;
                    if (strlen($page->title) > 12) {
                        $title = substr($page->title, 0, 10);
                    } else {
                        $title = & $page->title;
                    }
                    $link['title'] = sprintf('%d. %s', $page->page_number, $title);
                } else {
                    $link['title'] = sprintf(dgettext('webpage', '%d. (Untitled)'), $page->page_number);
                }
                $link['link'] = sprintf('index.php?module=webpage&wp_admin=edit_webpage&page_id=%s&volume_id=%s', $page_id, $volume->id);
                if ($version_id) {
                    $link['link'] .= '&version_id=' . $version_id;
                }
                $tabs['page_' . $page->page_number] = $link;
            }
        }

        if ($volume->id) {
            $link['link'] = 'index.php?module=webpage&volume_id=' . $volume->id;
            if ($version_id) {
                $link['link'] .= '&version_id=' . $version_id;
            }

            $link['title'] = dgettext('webpage', 'Add Page');
            $tabs['add_page'] = $link;
        }

        $panel = new PHPWS_Panel('wp_edit_page');
        $panel->quickSetTabs($tabs);

        $panel->setModule('webpage');
        return $panel;
    }

    function editHeader(Webpage_Volume $volume, Version $version)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_header');

        if ($volume->id) {
            $form->addHidden('volume_id', $volume->id);
            $form->addSubmit(dgettext('webpage', 'Update header'));
        } else {
            $form->addSubmit(dgettext('webpage', 'Create header'));
        }

        if (!empty($version)) {
            $form->addHidden('version_id', $version->id);
        }

        $form->addText('title', $volume->title);
        $form->setLabel('title', dgettext('webpage', 'Webpage title'));
        $form->setSize('title', 50);

        $form->addTextArea('summary', $volume->summary);
        $form->useEditor('summary');
        $form->setLabel('summary', dgettext('webpage', 'Summary'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'webpage', 'forms/edit.tpl');
    }


    function editPage(Webpage_Page $page, Version $version)
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_page');
        $form->addHidden('volume_id', $page->volume_id);

        if ($page->id) {
            $form->addHidden('page_id', $page->id);
            $form->addSubmit(dgettext('webpage', 'Update page'));
        } else {
            $form->addSubmit(dgettext('webpage', 'Add new page'));
        }

        if (!empty($version)) {
            $form->addHidden('version_id', $version->id);
        }

        $form->addText('title', $page->title);
        $form->setLabel('title', dgettext('webpage', 'Title'));
        $form->setSize('title', 50);

        $form->addTextArea('content', $page->content);
        $form->useEditor('content');
        $form->setRows('content', 20);
        $form->setCols('content', 90);
        $form->setLabel('content', dgettext('webpage', 'Content'));
        $page_templates = $page->getTemplateList();

        if (empty($page_templates)) {
            return dgettext('webpage', 'There is a problem with your page templates. Check your error log.');
        }

        $form->addSelect('template', $page_templates);
        $form->setMatch ('template', $page->template);
        $form->setLabel('template', dgettext('webpage', 'Page template'));

        $form->addCheck('force_template', 1);
        $form->setLabel('force_template', dgettext('webpage', 'Force all pages to use this template'));

        if (PHPWS_Settings::get('webpage', 'add_images')) {
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::fileManager('image_id', $page->image_id);
            $manager->maxImageWidth(640);
            $manager->maxImageHeight(480);
            if ($manager) {
                $form->addTplTag('PAGE_IMAGE', $manager->get());
                $form->addTplTag('IMAGE_LABEL', dgettext('webpage', 'Image'));
            }
        }

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'webpage', 'forms/edit_page.tpl');
    }

    function wp_list()
    {
        $select_op['list'] = dgettext('webpage', '- Select option -');

        if (Current_User::allow('webpage', 'delete_page', null, null, true)) {
            $select_op['delete_wp']          = dgettext('webpage', 'Delete');
        }
        if (Current_User::isUnrestricted('webpage')) {
            $select_op['move_to_frontpage']  = dgettext('webpage', 'Move to frontpage');
            $select_op['move_off_frontpage'] = dgettext('webpage', 'Move off frontpage');
            $select_op['activate']           = dgettext('webpage', 'Activate');
            $select_op['deactivate']         = dgettext('webpage', 'Deactivate');
        }

        if (Current_User::allow('webpage', 'featured')) {
            $select_op['feature']            = dgettext('webpage', 'Feature page');
        }

        $form = new PHPWS_Form;
        $form->addHidden('module', 'webpage');

        if (count($select_op) > 1) {
            $form->addSelect('wp_admin', $select_op);
        }

        $tags = $form->getTemplate();

        $tags['TITLE_LABEL']        = dgettext('webpage', 'Title');
        $tags['DATE_CREATED_LABEL'] = dgettext('webpage', 'Created on');
        $tags['DATE_UPDATED_LABEL'] = dgettext('webpage', 'Updated on');
        $tags['CREATED_USER_LABEL'] = dgettext('webpage', 'Created by');
        $tags['UPDATED_USER_LABEL'] = dgettext('webpage', 'Updated by');
        $tags['FRONTPAGE_LABEL']    = dgettext('webpage', 'Front page');
        $tags['ACTIVE_LABEL']       = dgettext('webpage', 'Active');
        $tags['ACTION_LABEL']       = dgettext('webpage', 'Action');

        if (count($select_op) > 1) {
            $tags['CHECK_ALL'] = javascript('check_all', array('checkbox_name' => 'webpage'));
            $js['value']        = dgettext('webpage', 'Go');
            $js['select_id']    = $form->getId('wp_admin');
            $js['action_match'] = 'delete_wp';
            $js['message']      = dgettext('webpage', 'Are you sure you want to delete the checked web pages?');

            $tags['SUBMIT'] = javascript('select_confirm', $js);
        }


        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('webpage_volume', 'Webpage_Volume');
        $pager->setModule('webpage');
        $pager->setTemplate('forms/list.tpl');
        $pager->setLink('index.php?module=webpage&amp;tab=list');
        $pager->addPageTags($tags);
        $pager->addRowTags('rowTags');
        $pager->addToggle(' ');
        $pager->setSearch('title');
        Key::restrictEdit($pager->db, 'webpage', 'edit_page');
        $pager->db->addWhere('approved', 1);
        $pager->db->addWhere('approved', 0, '=', 'or', 'up');
        $pager->db->addWhere('update_user_id', Current_User::getId(), '=', 'and', 'up');
        $pager->db->setGroupConj('up', 'or');

        $content = $pager->get();

        return $content;
    }

    function approval()
    {
        PHPWS_Core::initModClass('version', 'Version.php');

        $approval = new Version_Approval('webpage', 'webpage_volume', 'Webpage_Volume', 'approval_view');
        $vars['wp_admin'] = 'edit_webpage';
        $approval->setEditUrl(PHPWS_Text::linkAddress('webpage', $vars, TRUE));

        $vars['wp_admin'] = 'approval_view';
        $approval->setViewUrl(PHPWS_Text::linkAddress('webpage', $vars, TRUE));

        $vars['wp_admin'] = 'approve_webpage';
        $approval->setApproveUrl(PHPWS_Text::linkAddress('webpage', $vars, TRUE));

        $vars['wp_admin'] = 'disapprove_webpage';
        $approval->setDisapproveUrl(PHPWS_Text::linkAddress('webpage', $vars, TRUE));

        return $approval->getList();
    }

}

?>