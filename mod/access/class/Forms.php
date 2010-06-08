<?php

/**
 * Administrative forms for the Access module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


class Access_Forms {

    public static function shortcuts()
    {
        if (!Current_User::allow('access')) {
            Current_User::disallow();
            return;
        }

        Core\Core::initModClass('access', 'Shortcut.php');
                $pager = new Core\DBPager('access_shortcuts', 'Access_Shortcut');
        $pager->setModule('access');
        $pager->setTemplate('forms/shortcut_list.tpl');
        $pager->setLink('index.php?module=access&amp;tab=shortcuts');
        $pager->addToggle('class="bgcolor1"');

        $form = new Core\Form('shortcut_list');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_shortcut_list');

        $options['none'] = '';
        if (Current_User::allow('access', 'admin_options')) {
            $options['active'] = dgettext('access', 'Activate');
            $options['deactive'] = dgettext('access', 'Deactivate');
        }

        $options['delete'] = dgettext('access', 'Delete');
        $form->addSelect('list_action', $options);

        $page_tags = $form->getTemplate();

        $page_tags['URL_LABEL']      = dgettext('access', 'Url');
        $page_tags['ACTIVE_LABEL'] = dgettext('access', 'Active?');
        $page_tags['ACTION_LABEL']   = dgettext('access', 'Action');
        $page_tags['CHECK_ALL_SHORTCUTS'] = javascript('check_all', array('checkbox_name' => 'shortcut[]'));

        $js_vars['value']        = dgettext('access', 'Go');
        $js_vars['select_id']    = $form->getId('list_action');
        $js_vars['action_match'] = 'delete';
        $js_vars['message']      = dgettext('access', 'Are you sure you want to delete the checked shortcuts?');
        $page_tags['SUBMIT'] = javascript('select_confirm', $js_vars);

        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');

        $content = $pager->get();
        return $content;
    }

    public static function denyAllowForm()
    {
        if (!Current_User::allow('access', 'admin_options')) {
            Current_User::disallow();
            return;
        }

        Core\Core::initModClass('access', 'Allow_Deny.php');

        $form = new Core\Form('allow_deny');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_deny_allow');

        $form->addCheck('allow_deny_enabled', 1);
        $form->setMatch('allow_deny_enabled', Core\Settings::get('access', 'allow_deny_enabled'));
        $form->setLabel('allow_deny_enabled', dgettext('access', 'Allow/Deny enabled'));
        $form->addSubmit('go', dgettext('access', 'Go'));

        $result = Access::getAllowDeny();
        if (Core\Error::isError($result)) {
            Core\Error::log($result);
        }

        $form->addText('allow_address');
        $form->addText('deny_address');
        $form->addSubmit('add_allow_address', dgettext('access', 'Add allowed IP'));
        $form->addSubmit('add_deny_address', dgettext('access', 'Add denied IP'));

        $db = new Core\DB('access_allow_deny');
        $result = $db->getObjects('Access_Allow_Deny');

        $options['none']      = dgettext('access', '-- Choose option --');
        $options['active']    = dgettext('access', 'Activate');
        $options['deactive']  = dgettext('access', 'Deactivate');
        $options['delete']    = dgettext('access', 'Delete');

        if (Core\Settings::get('access', 'allow_all')) {
            $allow_all = TRUE;
            $options['allow_all'] = dgettext('access', 'Do not allow all');
        } else {
            $allow_all = FALSE;
            $options['allow_all'] = dgettext('access', 'Allow all');
        }

        $form->addSelect('allow_action', $options);

        unset($options['allow_all']);

        if (Core\Settings::get('access', 'deny_all')) {
            $deny_all = TRUE;
            $options['deny_all'] = dgettext('access', 'Do not deny all');
        } else {
            $deny_all = FALSE;
            $options['deny_all'] = dgettext('access', 'Deny all');
        }
        $form->addSelect('deny_action', $options);

        $template = $form->getTemplate();

        if ($allow_all) {
            $template['ALLOW_ALL_MESSAGE'] = dgettext('access', 'You have "Allow all" enabled. All rows below will be ignored.');
        }

        if ($deny_all) {
            $template['DENY_ALL_MESSAGE'] = dgettext('access', 'You have "Deny all" enabled. All rows below will be ignored.');
        }

        $js_vars['value']        = dgettext('access', 'Go');
        $js_vars['action_match'] = 'delete';
        $js_vars['message']      = dgettext('access', 'Are you sure you want to delete the checked ips?');

        $js_vars['select_id']    = 'allow_deny_allow_action';
        $template['ALLOW_ACTION_SUBMIT'] = javascript('select_confirm', $js_vars);

        $js_vars['select_id']    = 'allow_deny_deny_action';
        $template['DENY_ACTION_SUBMIT'] = javascript('select_confirm', $js_vars);


        if (Core\Error::isError($result)) {
            Core\Error::log($result);
            return dgettext('access', 'An error occurred when trying to access the allowed and denied ip records. Please check your logs.');
        } elseif (empty($result)) {
            $template['DENY_MESSAGE']  = dgettext('access', 'No denied ip addresses found.');
            $template['ALLOW_MESSAGE'] = dgettext('access', 'No allowed ip addresses found.');
        } else {
            foreach ($result as $allow_deny) {
                $action = Core\Text::secureLink(dgettext('access', 'Delete'), 'access', array('ad_id'=>$allow_deny->id, 'command'=>'delete_allow_deny'));
                if ($allow_deny->active) {
                    $active = dgettext('access', 'Yes');
                } else {
                    $active = dgettext('access', 'No');
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
                $template['ALLOW_MESSAGE'] = dgettext('access', 'No allowed ip addresses found.');
            }

            if (empty($template['deny_rows'])) {
                $template['DENY_MESSAGE'] = dgettext('access', 'No denied ip addresses found.');
            }
        }

        $template['CHECK_ALL_ALLOW'] = javascript('check_all', array('checkbox_name' => 'allows'));
        $template['CHECK_ALL_DENY'] = javascript('check_all', array('checkbox_name' => 'denys'));
        $template['ACTIVE_LABEL']     = dgettext('access', 'Active?');
        $template['ALLOW_TITLE']      = dgettext('access', 'Allowed IPs');
        $template['DENY_TITLE']       = dgettext('access', 'Denied IPs');
        $template['ACTION_LABEL']     = dgettext('access', 'Action');
        $template['IP_ADDRESS_LABEL'] = dgettext('access', 'IP Address');
        $template['WARNING']          = dgettext('access', 'Remember to "Update" your access file when finished changing IP rules.');

        return Core\Template::process($template, 'access', 'forms/allow_deny.tpl');
    }

    public function shortcut_menu()
    {
        Core\Core::initModClass('access', 'Shortcut.php');
        @$sc_id = $_REQUEST['sc_id'];

        if (!$sc_id) {
            @$key_id = $_REQUEST['key_id'];
            if (!$key_id) {
                javascript('close_window');
                return;
            } else {
                $shortcut = new Access_Shortcut;
                $key = new Core\Key($key_id);
                if (!$key->id) {
                    javascript('close_window');
                    return;
                }
                $shortcut->keyword = trim(preg_replace('/[^\w\s\-]/', '', $key->title));
            }
        } else {
            $shortcut = new Access_Shortcut($sc_id);
            if (!$shortcut->id) {
                javascript('close_window');
                return;
            }
        }

        $form = new Core\Form('shortcut_menu');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_shortcut');
        if (isset($key_id)) {
            $form->addHidden('key_id', $key_id);
        } else {
            $form->addHidden('sc_id', $shortcut->id);
        }

        $form->addText('keyword', $shortcut->keyword);
        $form->addSubmit('go', dgettext('access', 'Go'));
        $tpl = $form->getTemplate();

        $tpl['TITLE'] = dgettext('access', 'Shortcuts');
        $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="window.close();" />', dgettext('access', 'Cancel'));
        $content = Core\Template::process($tpl, 'access', 'shortcut_menu.tpl');
        return $content;
    }


}

?>