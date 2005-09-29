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


        $options['none'] = '';
        if (Current_User::allow('access', 'admin_options')) {
            $options['accept'] = _('Accept');
            $options['unaccept'] = _('');
        }

        $options['delete'] = _('Delete');
        $form->addSelect('list_action', $options);

        $page_tags = $form->getTemplate();


        if (isset($_SESSION['Access_Shortcut_Enabled'])) {
            $sc_vars['command'] = 'disable_shortcut';
            $page_tags['SHORTCUT_LINK']  = PHPWS_Text::secureLink(_('Turn off Shortcuts'), 'access', $sc_vars);
        } else {
            $sc_vars['command'] = 'enable_shortcut';
            $page_tags['SHORTCUT_LINK']  = PHPWS_Text::secureLink(_('Enable Shortcuts'), 'access', $sc_vars);
        }

        $page_tags['KEYWORD_LABEL']  = _('Keywords');
        $page_tags['URL_LABEL']      = _('Url');
        $page_tags['ACCEPTED_LABEL'] = _('Accepted?');
        $page_tags['ACTION_LABEL']   = _('Action');
        $page_tags['CHECK_ALL_SHORTCUTS'] = javascript('check_all', array('checkbox_name' => 'shortcut[]'));
        $js_vars['value']        = _('Go');
        $js_vars['select_id']    = 'list_action';
        $js_vars['action_match'] = 'delete';
        $js_vars['message']      = _('Are you sure you want to delete the checked shortcuts?');
        $page_tags['SUBMIT'] = javascript('select_confirm', $js_vars);

        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');

        $content = $pager->get();
        return $content;
    }

    function administrator()
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
        $form->addHidden('command', 'post_admin');


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

        $form->addCheckBox('allow_file_update', 1);
        $form->setLabel('allow_file_update', _('Allow file update'));
        if (PHPWS_Settings::get('access', 'allow_file_update')) {
            $form->setMatch('allow_file_update', 1);
        }


        $form->addSubmit(_('Save settings'));
        $template = $form->getTemplate();

        $template['MOD_REWRITE_LABEL'] = _('Mod Rewrite Options');
        $template['HTACCESS_LABEL'] = _('.htaccess File Options');

        return PHPWS_Template::process($template, 'access', 'forms/administrator.tpl');
    }


    function updateFile()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_update_file');
        $form->addSubmit(_('Write .htaccess file'));
        $template = $form->getTemplate();

        $template['INFO'] = _('Your .htaccess file will contain the below:');
        $template['HTACCESS'] = Access::getRewrite();

        return PHPWS_Template::process($template, 'access', 'forms/update_file.tpl');
        
    }

    function denyAllowForm()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_deny_allow');

        $result = Access::getAllowDeny();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
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
        $form->addSubmit('go', _('Go'));
        $form->addSubmit('off', _('Turn off Shortcuts'));
        $tpl = $form->getTemplate();

        $tpl['TITLE'] = _('Shortcuts');
        $content = PHPWS_Template::process($tpl, 'access', 'shortcut_menu.tpl');
        Layout::add($content, 'access', 'shortcut_menu');
    }


}

?>