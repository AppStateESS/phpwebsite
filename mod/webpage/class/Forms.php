<?php
/**
 * Holds the administrative forms for Web Page module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Webpage_Forms {

    function edit(&$volume)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'webpage');
        $form->addHidden('wp_admin', 'post_volume');

        if ($volume->id) {
            $form->addHidden('volume_id', $volume->id);
            $form->addSubmit(_('Update webpage'));
        } else {
            $form->addSubmit(_('Create webpage'));
        }

        $form->addText('title', $volume->title);
        $form->setLabel('title', _('Volume title'));
        $form->setSize('title', 40);

        $form->addTextArea('summary', $volume->getSummary());
        $form->useEditor('summary');
        $form->setLabel('summary', _('Summary'));

        $form->addSelect('template', $volume->getTemplateList());
        $form->setMatch ('template', $volume->template);
        $form->setLabel('template', _('Volume template'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'webpage', 'forms/edit.tpl');
    }

    function edit_pages(&$volume, $current_page=1)
    {

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