<?php

class Access_Forms {

    function shortcuts()
    {
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('access_shortcuts', 'Access_Shortcut');
        $pager->setModule('access');
        $pager->setTemplate('forms/shortcut_list.tpl');
        $pager->setLink('index.php?module=access&amp;tab=shortcuts');
        $pager->addToggle('class="bgcolor1"');

        $form = & new PHPWS_Form('shortcut_list');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_shortcut_list');

        $options['delete_shortcut'] = _('Delete');
        $form->addSelect('list_action', $options);
        $form->addSubmit(_('Go'));
        $page_tags = $form->getTemplate();

        $sc_vars['command'] = 'enable_shortcut';
        $page_tags['ADD_SHORTCUT']  = PHPWS_Text::secureLink(_('Add Shortcut'), 'access', $sc_vars);
        $page_tags['KEYWORD_LABEL'] = _('Keywords');
        $page_tags['URL_LABEL']     = _('Url');
        $page_tags['ACTION_LABEL']        = _('Action');

        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');

        $content = $pager->get();
        return $content;
    }

    function rewrite()
    {
        if (!MOD_REWRITE_ENABLED) {
            $content[] = _('You do not have mod rewrite enabled.');
            $content[] = _('Open your config/core/config.php file in a text editor.');
            $content[] = _('Set your "MOD_REWRITE_ENABLED" define equal to TRUE.');
            return implode('<br />', $content);
        } elseif (!Access::check_htaccess()) {
            $content[] = _('Your <b>.htaccess</b> file is not writable.');
            $content[] = _('Look in your installation directory and give Apache write access.');
            return implode('<br />', $content);
        }

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'access');

        $form->addCheckbox('rewrite_engine', 1);
        $form->setLabel('rewrite_engine', _('Rewrite engine on'));
        if (PHPWS_Settings::get('access', 'rewrite_engine')) {
            $form->setMatch('rewrite_engine', 1);
        }

        $form->addCheckbox('shortcuts_enabled', 1);
        $form->setLabel('shortcuts_enabled', _('Shortcuts enabled'));
        if (PHPWS_Settings::get('access', 'shortcuts_enabled')) {
            $form->setMatch('shortcuts_enabled', 1);
        }


        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'access', 'forms/rewrite.tpl');

    }


    function shortcut_menu()
    {
        $url = urlencode(PHPWS_Core::getCurrentUrl());

        $form = & new PHPWS_Form('shortcut_menu');
        $form->addHidden('module', 'access');
        $form->addHidden('url', $url);
        $form->addHidden('command', 'post_shortcut');
        $instruction = _('Type keyword here');
        $form->addText('keyword', $instruction);
        $form->setExtra('keyword', 'onclick="this.value=\'\'"');
        $form->addSubmit(_('Go'));
        $tpl = $form->getTemplate();

        $tpl['TITLE'] = _('Shortcuts');
        $content = PHPWS_Template::process($tpl, 'access', 'shortcut_menu.tpl');
        Layout::add($content, 'access', 'shortcut_menu');
    }


}

?>