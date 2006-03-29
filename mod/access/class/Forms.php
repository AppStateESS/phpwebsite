<?php

/**
 * Administrative forms for the Access module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


class Access_Forms {

    function shortcuts()
    {
        if (!Current_User::allow('access')) {
            Current_User::disallow();
            return;
        }

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
            $options['active'] = _('Activate');
            $options['deactive'] = _('Deactivate');
        }

        $options['delete'] = _('Delete');
        $form->addSelect('list_action', $options);

        $page_tags = $form->getTemplate();

        $page_tags['KEYWORD_LABEL']  = _('Keywords');
        $page_tags['URL_LABEL']      = _('Url');
        $page_tags['ACTIVE_LABEL'] = _('Active?');
        $page_tags['ACTION_LABEL']   = _('Action');
        $page_tags['CHECK_ALL_SHORTCUTS'] = javascript('check_all', array('checkbox_name' => 'shortcut[]'));

        $js_vars['value']        = _('Go');
        $js_vars['select_id']    = $form->getId('list_action');
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
        if (!Current_User::allow('access', 'admin_options')) {
            Current_User::disallow();
            return;
        }
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
        if (!Current_User::allow('access', 'admin_options')) {
            Current_User::disallow();
            return;
        }

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_update_file');
        $form->addSubmit(_('Write .htaccess file'));
        $template = $form->getTemplate();

        $template['INFO'] = _('Your .htaccess file will contain the below:');

        $allow_deny = Access::getAllowDenyList();
        $template['HTACCESS'] = $allow_deny;
        $template['HTACCESS'] .= Access::getRewrite();

        return PHPWS_Template::process($template, 'access', 'forms/update_file.tpl');
        
    }

    function denyAllowForm()
    {
        if (!Current_User::allow('access', 'admin_options')) {
            Current_User::disallow();
            return;
        }

        PHPWS_Core::initModClass('access', 'Allow_Deny.php');

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_deny_allow');

        $result = Access::getAllowDeny();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }

        $form->addText('allow_address');
        $form->addText('deny_address');
        $form->addSubmit('add_allow_address', _('Add allowed IP'));
        $form->addSubmit('add_deny_address', _('Add denied IP'));

        $db = & new PHPWS_DB('access_allow_deny');
        $result = $db->getObjects('Access_Allow_Deny');

        $options['none']      = '';
        $options['active']    = _('Activate');
        $options['deactive']  = _('Deactivate');
        $options['delete']    = _('Delete');

        if (PHPWS_Settings::get('access', 'allow_all')) {
            $allow_all = TRUE;
            $options['allow_all'] = _('Do not allow all');
        } else {
            $allow_all = FALSE;
            $options['allow_all'] = _('Allow all');
        }

        $form->addSelect('allow_action', $options);

        unset($options['allow_all']);

        if (PHPWS_Settings::get('access', 'deny_all')) {
            $deny_all = TRUE;
            $options['deny_all'] = _('Do not deny all');
        } else {
            $deny_all = FALSE;
            $options['deny_all'] = _('Deny all');
        }
        $form->addSelect('deny_action', $options);

        $template = $form->getTemplate();

        if ($allow_all) {
            $template['ALLOW_ALL_MESSAGE'] = _('You have "Allow all" enabled. All rows below will be ignored.');
        }

        if ($deny_all) {
            $template['DENY_ALL_MESSAGE'] = _('You have "Deny all" enabled. All rows below will be ignored.');
        }

        $js_vars['value']        = _('Go');
        $js_vars['action_match'] = 'delete';
        $js_vars['message']      = _('Are you sure you want to delete the checked ips?');

        $js_vars['select_id']    = 'allow_action';
        $template['ALLOW_ACTION_SUBMIT'] = javascript('select_confirm', $js_vars);

        $js_vars['select_id']    = 'deny_action';
        $template['DENY_ACTION_SUBMIT'] = javascript('select_confirm', $js_vars);


        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return _('An error occurred when trying to access the allowed and denied ip records. Please check your logs.');
        } elseif (empty($result)) {
            $template['DENY_MESSAGE']  = _('No denied ip addresses found.');
            $template['ALLOW_MESSAGE'] = _('No allowed ip addresses found.');
        } else {
            foreach ($result as $allow_deny) {
                $action = 'Edit';
                if ($allow_deny->active) {
                    $active = _('Yes');
                } else {
                    $active = _('No');
                }

                if ($allow_deny->allow_or_deny) {
                    $check = sprintf('<input type="checkbox" name="allows[]" value="%s" />', $allow_deny->id);
                    $template['allow_rows'][] = array('ALLOW_CHECK'      => $check,
                                                      'ALLOW_IP_ADDRESS' => $allow_deny->ip_address,
                                                      'ALLOW_ACTIVE'     => $active,
                                                      'ALLOW_ACTION'     => $action);
                } else {
                    $check = sprintf('<input type="checkbox" name="denys[]" value="%s" />', $allow_deny->id);
                    $template['deny_rows'][] = array('DENY_CHECK'      => $check,
                                                     'DENY_IP_ADDRESS' => $allow_deny->ip_address,
                                                     'DENY_ACTIVE'     => $active,
                                                     'DENY_ACTION'     => $action);
                }
            }

            if (empty($template['allow_rows'])) {
                $template['ALLOW_MESSAGE'] = _('No allowed ip addresses found.');
            }

            if (empty($template['deny_rows'])) {
                $template['DENY_MESSAGE'] = _('No denied ip addresses found.');
            }
        }

        $template['CHECK_ALL_ALLOW'] = javascript('check_all', array('checkbox_name' => 'allows[]'));
        $template['CHECK_ALL_DENY'] = javascript('check_all', array('checkbox_name' => 'denys[]'));
        $template['ACTIVE_LABEL']     = _('Active?');
        $template['ALLOW_TITLE']      = _('Allowed IPs');
        $template['DENY_TITLE']       = _('Denied IPs');
        $template['ACTION_LABEL']     = _('Action');
        $template['IP_ADDRESS_LABEL'] = _('IP Address');

        return PHPWS_Template::process($template, 'access', 'forms/allow_deny.tpl');

    }

    function shortcut_menu()
    {
        if (!@$key_id = $_REQUEST['key_id']) {
            javascript('close_window');
        }

        $form = & new PHPWS_Form('shortcut_menu');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_shortcut');
        $form->addHidden('key_id', $key_id);
        $instruction = _('Type keyword here');
        $form->addText('keyword', $instruction);
        $form->setExtra('keyword', sprintf('onclick="if (this.value == \'%s\') {this.value=\'\'}"', $instruction));
        $form->addSubmit('go', _('Go'));
        $tpl = $form->getTemplate();

        $tpl['TITLE'] = _('Shortcuts');
        $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="window.close();" />', _('Cancel'));
        $content = PHPWS_Template::process($tpl, 'access', 'shortcut_menu.tpl');
        return $content;
    }


}

?>