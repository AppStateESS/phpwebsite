<?php
/**
    * vlist - phpwebsite module
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
    * @author Verdon Vaillancourt <verdonv at users dot sourceforge dot net>
*/


class vList_Runtime
{

    public function showBlock() {
        if (PHPWS_Settings::get('vlist', 'enable_sidebox')) {
            if (PHPWS_Settings::get('vlist', 'sidebox_homeonly')) {
                $key = Key::getCurrent();
                if (!empty($key) && $key->isHomeKey()) {
                    vList_Runtime::showvListBlock();
                }
            } else {
                vList_Runtime::showvListBlock();
            }
        }
    }

    public function showvListBlock() {

        $db = new PHPWS_DB('vlist_listing');
        $db->addColumn('id');
        $db->addWhere('active', 1);
        if (PHPWS_Settings::get('vlist', 'block_order_by')) {
            $db->addOrder('rand');
            $label = dgettext('vlist', 'Random Listing');
        } else {
            $label = dgettext('vlist', 'Most Recent Listing');
            $db->addOrder('created desc');
        }
        $db->setLimit(1);
        $result = $db->select();
        if (!PHPWS_Error::logIfError($result) && !empty($result)) {
            $tpl['TITLE'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'module_title'));
            $tpl['LABEL'] = $label;
            $tpl['TEXT'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('vlist', 'sidebox_text'));
            PHPWS_Core::initModClass('vlist', 'vList_Listing.php');
            $listing = new vList_Listing($result[0]['id']);
            $tpl['NAME'] = $listing->viewLink();
            if ($listing->image_id) {
                $tpl['THUMBNAIL'] = $listing->getThumbnail(true);
            } else {
                $tpl['THUMBNAIL'] = null;
            }
            $tpl['LINK'] = PHPWS_Text::moduleLink(dgettext('vlist', 'Browse all listings'), 'vlist', array('uop'=>'listings'));

            if (Current_User::allow('vlist', 'edit_listing')) {
                $tpl['SUBMIT_LINK'] = PHPWS_Text::secureLink(dgettext('vlist', 'Add Listing'), 'vlist', array('aop'=>'new_listing'));
            } elseif (PHPWS_Settings::get('vlist', 'anon_files') || (PHPWS_Settings::get('vlist', 'user_files') && $_SESSION['User']->username != '')) {
                $tpl['SUBMIT_LINK'] = PHPWS_Text::moduleLink(dgettext('vlist', 'Submit a listing'), 'vlist', array('uop'=>'submit_listing'));
            }

            PHPWS_Core::initModClass('layout', 'Layout.php');
            Layout::add(PHPWS_Template::process($tpl, 'vlist', 'block.tpl'), 'vlist', 'vlist_sidebox');
        }

    }

}

?>