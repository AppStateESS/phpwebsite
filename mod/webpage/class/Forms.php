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

        $link['title'] = _('Header');
        $tabs['header'] = $link;

        if (!empty($volume->_pages)) {
            foreach ($volume->_pages as $page_id => $page) {
                $link['title'] = sprintf(_('Page %s'), $page->page_number);
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

            $link['title'] = _('Add Page');
            $tabs['add_page'] = $link;
        }

        $panel = & new PHPWS_Panel('wp_edit_page');
        $panel->quickSetTabs($tabs);

        $panel->setModule('webpage');
        return $panel;
    }

    function editHeader(&$volume, &$version)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_header');

        if ($volume->id) {
            $form->addHidden('volume_id', $volume->id);
            $form->addSubmit(_('Update header'));
        } else {
            $form->addSubmit(_('Create header'));
        }

        if (!empty($version)) {
            $form->addHidden('version_id', $version->id);
        }

        $form->addText('title', $volume->title);
        $form->setLabel('title', _('Webpage title'));
        $form->setSize('title', 50);

        $form->addTextArea('summary', $volume->summary);
        $form->useEditor('summary');
        $form->setLabel('summary', _('Summary'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'webpage', 'forms/edit.tpl');
    }


    function editPage(&$page, &$version)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_page');
        $form->addHidden('volume_id', $page->volume_id);

        if ($page->id) {
            $form->addHidden('page_id', $page->id);
            $form->addSubmit(_('Update page'));
        } else {
            $form->addSubmit(_('Add new page'));
        }

        if (!empty($version)) {
            $form->addHidden('version_id', $version->id);
        }

        $form->addText('title', $page->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 50);

        $form->addTextArea('content', $page->content);
        $form->useEditor('content');
        $form->setRows('content', 20);
        $form->setCols('content', 90);
        $form->setLabel('content', _('Content'));


        $form->addSelect('template', $page->getTemplateList());
        $form->setMatch ('template', $page->template);
        $form->setLabel('template', _('Page template'));

        $form->addCheck('force_template', 1);
        $form->setLabel('force_template', _('Force all pages to use this template'));
        
        $template = $form->getTemplate();

        return PHPWS_Template::process($template, 'webpage', 'forms/edit_page.tpl');

    }

    function wp_list()
    {
        $select_op[] = '';
        $select_op['delete_wp']          = _('Delete');
        $select_op['move_to_frontpage']  = _('Move to frontpage');
        $select_op['move_off_frontpage'] = _('Move off frontpage');
        $select_op['activate']           = _('Activate');
        $select_op['deactivate']         = _('Deactivate');

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addSelect('wp_admin', $select_op);
        $tags = $form->getTemplate();

        $tags['TITLE_LABEL']        = _('Title');
        $tags['DATE_CREATED_LABEL'] = _('Created on');
        $tags['DATE_UPDATED_LABEL'] = _('Updated on');
        $tags['CREATED_USER_LABEL'] = _('Created by');
        $tags['UPDATED_USER_LABEL'] = _('Updated by');
        $tags['FRONTPAGE_LABEL']    = _('Front page');
        $tags['ACTION_LABEL']       = _('Action');
        $tags['CHECK_ALL'] = javascript('check_all', array('checkbox_name' => 'webpage'));

        $js['value']        = _('Go');
        $js['select_id']    = $form->getId('wp_admin');
        $js['action_match'] = 'delete_wp';
        $js['message']      = _('Are you sure you want to delete the checked web pages?');

        $tags['SUBMIT'] = javascript('select_confirm', $js);

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('webpage_volume', 'Webpage_Volume');
        $pager->setModule('webpage');
        $pager->setTemplate('forms/list.tpl');
        $pager->setLink('index.php?module=webpage&amp;tab=list');
        $pager->addPageTags($tags);
        $pager->addRowTags('rowTags');
        $pager->addToggle(' ');
        $pager->setSearch('title');
        $pager->addWhere('approved', 1);

        $content = $pager->get();

        return $content;
    }

    function approval()
    {
        PHPWS_Core::initModClass('version', 'Version.php');

        $approval = & new Version_Approval('webpage', 'webpage_volume', 'Webpage_Volume', 'approval_view');
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