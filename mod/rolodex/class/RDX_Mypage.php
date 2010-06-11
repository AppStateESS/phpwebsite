<?php
/**
 * rolodex - phpwebsite module
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


PHPWS_Core::initModClass('rolodex', 'Rolodex.php');
class Rolodex_Mypage {

    public static function main()
    {
        Rolodex_Mypage::action($tpl['TITLE'], $tpl['CONTENT']);
        $tpl['MESSAGE'] = Rolodex_Mypage::getMessage();

        if (empty($tpl['CONTENT']))
        {
            $tpl['TITLE'] = sprintf(dgettext('rolodex', 'Manage my %s profile'), PHPWS_Settings::get('rolodex', 'module_title'));
            $db = new PHPWS_DB('rolodex_member');
            $db->addWhere('user_id', Current_User::getId());
            $result = $db->count();
            if ($result > 0) {
                $vars['user_id'] = Current_User::getId();
                $vars['uop'] = 'edit_member';
                $links[] = PHPWS_Text::secureLink(dgettext('rolodex', 'Edit my profile'), 'rolodex', $vars);
            } else {
                $vars['user_id'] = Current_User::getId();
                if (PHPWS_Settings::get('rolodex', 'req_approval')) {
                    $vars['uop'] = 'submit_member';
                    $links[] = PHPWS_Text::secureLink(dgettext('rolodex', 'Submit my profile'), 'rolodex', $vars);
                } else {
                    $vars['uop'] = 'add_member';
                    $links[] = PHPWS_Text::secureLink(dgettext('rolodex', 'Add my profile'), 'rolodex', $vars);
                }
            }

            $links = array_merge($links, Rolodex::navLinks());

            $tpl['CONTENT'] = implode(' | ', $links);
            $tpl['CONTENT'] .= Rolodex_Mypage::searchForm();
            if ($result > 0) {
                PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
                if (Rolodex_Member::isDataVisible('privacy_export')) {
                    $tpl['CONTENT'] .= '<br />' . PHPWS_Text::moduleLink(dgettext('rolodex', 'Export records to csv'), 'rolodex', array('uop'=>'export'));
                }
            }
        }

        return PHPWS_Template::process($tpl, 'rolodex', 'my_page.tpl');
    }



    /* nothing below here used yet, maybe use in future */

    public static function action(&$title, &$content)
    {
        if (isset($_REQUEST['uop']))
        {
            switch ($_REQUEST['uop'])
            {
                 


            }
        }
    }


    public static function searchForm()
    {
        $form = new PHPWS_Form('rolodex_search');
        $form->setMethod('get');
        $form->addHidden('module', 'rolodex');
        $form->addHidden('uop', 'list');
        $form->addHidden('search', '');
        $form->addHidden('limit', '10');
        $form->addHidden('orderby', 'demographics.last_name');
        $form->addHidden('orderby_dir', 'asc');
        $form->addText('pager_c_search');
        $form->setSize('pager_c_search', 25);
        $form->setLabel('pager_c_search', sprintf(dgettext('rolodex', 'Search %s'), PHPWS_Settings::get('rolodex', 'module_title')));
        $form->addSubmit('go', dgettext('rolodex', 'Search'));
        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'rolodex', 'search.tpl');
    }

    public function sendMessage(&$result, $success_msg, $error_msg)
    {
        $_SESSION['rolodex_message'] = (PHPWS_Error::logIfError($result) ? $error_msg : $success_msg);
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('users', array('action'=>'user', 'tab'=>'rolodex'), false));
    }

    public function sendMessageOnly($msg)
    {
        $_SESSION['rolodex_message'] = $msg;
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('users', array('action'=>'user', 'tab'=>'rolodex'), false));
    }

    public static function getMessage()
    {
        if (isset($_SESSION['rolodex_message']))
        {
            $message = $_SESSION['rolodex_message'];
            unset($_SESSION['rolodex_message']);
            return $message;
        }

        return NULL;
    }


}

?>