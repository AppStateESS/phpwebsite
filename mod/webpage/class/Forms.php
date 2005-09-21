<?php
/**
 * Holds the administrative forms for Web Page module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Webpage_Forms {

    function pagePanel($volume)
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link['link'] = 'index.php?module=webpage&wp_admin=edit_webpage&volume_id=' . $volume->id;
        $link['title'] = _('Header');
        $tabs['header'] = $link;

        if (!empty($volume->_pages)) {
            foreach ($volume->_pages as $page_id => $page) {
                $link['title'] = sprintf(_('Page %s'), $page->page_number);
                $link['link'] = 'index.php?module=webpage&volume_id=' . $volume->id . '&wp_admin=edit_webpage&page_id=' . $page_id . '&volume_id=' . $volume->id;
                $tabs['page_' . $page->page_number] = $link;
            }
        }

        if ($volume->id) {
            $link['link'] = 'index.php?module=webpage&volume_id=' . $volume->id;
            $link['title'] = _('Add Page');
            $tabs['add_page'] = $link;
        }

        $panel = & new PHPWS_Panel('wp_edit_page');
        $panel->quickSetTabs($tabs);

        $panel->setModule('webpage');
        return $panel;
    }

    function editHeader(&$volume)
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

        $form->addText('title', $volume->title);
        $form->setLabel('title', _('Webpage title'));
        $form->setSize('title', 50);

        $form->addTextArea('summary', $volume->getSummary());
        $form->useEditor('summary');
        $form->setLabel('summary', _('Summary'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'webpage', 'forms/edit.tpl');
    }


    function editPage(&$page)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_page');
        $form->addHidden('volume_id', $page->volume_id);

        if ($page->id) {
            $form->addHidden('page_id', $page->id);
            $form->addSubmit(_('Add new page'));
        } else {
            $form->addSubmit(_('Update page'));
        }

        $form->addText('title', $page->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 50);

        $form->addTextArea('content', $page->getContent());
        $form->useEditor('content');
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
        $tags['TITLE_LABEL']        = _('Title');
        $tags['DATE_CREATED_LABEL'] = _('Created on');
        $tags['DATE_UPDATED_LABEL'] = _('Updated on');
        $tags['CREATED_USER_LABEL'] = _('Created by');
        $tags['UPDATED_USER_LABEL'] = _('Updated by');
        $tags['ACTION_LABEL']       = _('Action');

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('webpage_volume', 'Webpage_Volume');
        $pager->setModule('webpage');
        $pager->setTemplate('forms/list.tpl');
        $pager->setLink('index.php?module=webpage&amp;tab=list');
        $pager->addPageTags($tags);
        $pager->addRowTags('rowTags');
        $pager->setSearch('title');

        $content = $pager->get();

        return $content;
    }
}

?>