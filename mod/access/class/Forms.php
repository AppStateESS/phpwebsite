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


        if (isset($_SESSION['Access_Shortcut_Enabled'])) {
            $sc_vars['command'] = 'disable_shortcut';
            $page_tags['SHORTCUT_LINK']  = PHPWS_Text::secureLink(_('Turn off Shortcuts'), 'access', $sc_vars);
        } else {
            $sc_vars['command'] = 'enable_shortcut';
            $page_tags['SHORTCUT_LINK']  = PHPWS_Text::secureLink(_('Enable Shortcuts'), 'access', $sc_vars);
        }
        $page_tags['KEYWORD_LABEL'] = _('Keywords');
        $page_tags['URL_LABEL']     = _('Url');
        $page_tags['ACTION_LABEL']        = _('Action');

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
        PHPWS_Core::initModClass('access', 'Allow_Deny.php');
        PHPWS_Core::initModClass('access', 'Shortcut.php');

        $allow_denys = Access::getAllowDeny();
        
        if (PEAR::isError($allow_denys)) {
            PHPWS_Error::log($allow_denys);
            $template['DENY_MESSAGE'] = $template['ALLOW_MESSAGE'] = _('An error occurred when accessing allow and deny records.');
        } elseif (empty($allow_denys)) {
            $template['DENY_MESSAGE'] = $template['ALLOW_MESSAGE'] = _('No allows or denys found.');
        } else {
            foreach ($allow_denys as $oAllowDeny) {
                if ($oAllowDeny->allow) {
                    $template['allow_rows'][]  = array('ALLOW_IP' => $oAllowDeny->ip_address);
                } else {
                    $template['deny_rows'][]  = array('DENY_IP' => $oAllowDeny->ip_address);
                }
            }
        }

        $template['ALLOW_LABEL'] = _('Allowed IPs');
        $template['DENY_LABEL'] = _('Denied IPs');

        $shortcuts = Access::getShortcuts();

        if (PEAR::isError($shortcuts)) {
            PHPWS_Error::log($shortcuts);
            $template['SHORTCUT_MESSAGE'] = _('An error occurred when accessing shortcut records.');
        } elseif (empty($shortcuts)) {
            $template['SHORTCUT_MESSAGE'] = _('No shortcuts found.');
        } else {
            foreach ($shortcuts as $s_cut) {
                $template['shortcuts'][]  = array('KEYWORD' => $s_cut->keyword, 'URL' => $s_cut->getRewrite());
            }
        }

        $template['SHORTCUT_LABEL'] = _('Shortcuts');

        return PHPWS_Template::process($template, 'access', 'forms/update_file.tpl');
        
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