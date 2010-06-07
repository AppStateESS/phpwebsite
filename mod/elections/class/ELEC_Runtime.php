<?php
/**
 * elections - phpwebsite module
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


class ELEC_Runtime
{

    public static function showBlock() {
        if (PHPWS_Settings::get('elections', 'enable_sidebox')) {
            if (PHPWS_Settings::get('elections', 'sidebox_homeonly')) {
                $key = Key::getCurrent();
                if (!empty($key) && $key->isHomeKey()) {
                    ELEC_Runtime::showElectionsBlock();
                }
            } else {
                ELEC_Runtime::showElectionsBlock();
            }
        }
    }


    public function showElectionsBlock() {

        $tpl['TITLE'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('elections', 'title'));

        if (PHPWS_Settings::get('elections', 'enable_elections') || Current_User::isUnrestricted('elections')) {

            $tpl['TEXT'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('elections', 'sidebox_text'));

            Core\Core::initModClass('elections', 'ELEC_Ballot.php');
            $db = new PHPWS_DB('elections_ballots');
            if (!isset($_SESSION['User']->username)) {
                $db->addWhere('pubview', 1);
            }
            $db->addWhere('opening', time(), '<=');
            $db->addWhere('closing', time(), '>=');
            $db->addOrder('title', 'asc');

            $result = $db->getObjects('Elections_ballot');

            if (!PHPWS_Error::logIfError($result) && !empty($result)) {
                foreach ($result as $ballot) {
                    $tpl['ballot_links'][] = $ballot->viewLink(false, true);
                }
                $tpl['BROWSE_LINK'] = PHPWS_Text::moduleLink(dgettext('elections', 'Browse all ballots'), 'elections', array('uop'=>'list_ballots'));
            }

        } else {
            $tpl['TEXT'] = dgettext('elections', 'Thank you for your interest. However, all elections are currently closed.');
        }

        Core\Core::initModClass('layout', 'Layout.php');
        Layout::add(PHPWS_Template::process($tpl, 'elections', 'block.tpl'), 'elections', 'elections_sidebox');
    }


}


?>