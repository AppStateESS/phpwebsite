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


class RDX_Runtime
{

    public static function showBlock() {
        if (core\Settings::get('rolodex', 'show_block')) {
            if (core\Settings::get('rolodex', 'block_on_home_only')) {
                $key = \core\Key::getCurrent();
                if (!empty($key) && $key->isHomeKey()) {
                    RDX_Runtime::showRolodexBlock();
                }
            } else {
                RDX_Runtime::showRolodexBlock();
            }
        }
    }

    public static function showRolodexBlock() {

        $db = new \core\DB('rolodex_member');
        $db->addColumn('user_id');
        $db->addWhere('active', 1);
        if (!Current_User::isLogged()) {
            $db->addWhere('privacy', 0);
        } else {
            $db->addWhere('privacy', 0);
            $db->addWhere('privacy', 1, '=', 'or');
        }
        if (core\Settings::get('rolodex', 'block_order_by_rand')) {
            $db->addOrder('rand');
        } else {
            $db->addOrder('date_created desc');
        }
        $db->setLimit(1);
        $result = $db->select();
        if (!core\Error::logIfError($result) && !empty($result)) {
            $tpl['TITLE'] = \core\Settings::get('rolodex', 'module_title');
            if (core\Settings::get('rolodex', 'block_order_by_rand')) {
                $tpl['MEMBER_LABEL'] = dgettext('rolodex', 'Featured Member');
            } else {
                $tpl['MEMBER_LABEL'] = dgettext('rolodex', 'Most Recent Member');
            }
            \core\Core::initModClass('rolodex', 'RDX_Member.php');
            $member = new Rolodex_Member($result[0]['user_id']);
            $tpl['MEMBER_TITLE'] = $member->viewLink();
            if ($member->getThumbnail()) {
                $tpl['MEMBER_THUMBNAIL'] = $member->getThumbnail(true, true);
            } else {
                $tpl['MEMBER_THUMBNAIL'] = null;
            }
            $tpl['BROWSE_LINK'] = \core\Text::moduleLink(dgettext('rolodex', 'Browse all members'), 'rolodex', array('uop'=>'list'));
            \core\Core::initModClass('layout', 'Layout.php');
            Layout::add(core\Template::process($tpl, 'rolodex', 'block.tpl'), 'rolodex', 'rdx_sidebox');
        }

    }

}

?>