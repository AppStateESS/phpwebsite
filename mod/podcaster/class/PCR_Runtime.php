<?php
/**
    * podcaster - phpwebsite module
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
    * @version $Id: $
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/


class PCR_Runtime
{

    function showBlock() {
        if (PHPWS_Settings::get('podcaster', 'show_block')) {
            if (PHPWS_Settings::get('podcaster', 'block_on_home_only')) {
                $key = Key::getCurrent();
                if (!empty($key) && $key->isHomeKey()) {
                    PCR_Runtime::showPodcasterBlock();
                }
            } else {
                PCR_Runtime::showPodcasterBlock();
            }
        }
    }

    function showPodcasterBlock() {

        $db = new PHPWS_DB('podcaster_episode');
        $db->addColumn('id');
        $db->addWhere('active', 1);
        $db->addWhere('approved', 1);
        if (PHPWS_Settings::get('podcaster', 'block_order_by_rand')) {
            $db->addOrder('rand');
        } else {
            $db->addOrder('date_created desc');
        }
        $db->setLimit(1);
        $result = $db->select();
        if (!PHPWS_Error::logIfError($result) && !empty($result)) {
            $tpl['TITLE'] = dgettext('podcaster', 'Podcaster');
            if (PHPWS_Settings::get('podcaster', 'block_order_by_rand')) {
                $tpl['EPISODE_LABEL'] = dgettext('podcaster', 'Random Episode');
            } else {
                $tpl['EPISODE_LABEL'] = dgettext('podcaster', 'Most Recent Episode');
            }
            $tpl['CHANNEL_LABEL'] = dgettext('podcaster', 'From the  channel...');
            PHPWS_Core::initModClass('podcaster', 'PCR_Episode.php');
            $episode = new Podcaster_Episode($result[0]['id']);
            $tpl['EPISODE_TITLE'] = $episode->viewLink();
            $tpl['CHANNEL_TITLE'] = $episode->getChannel(true, true);
            PHPWS_Core::initModClass('layout', 'Layout.php');
            Layout::add(PHPWS_Template::process($tpl, 'podcaster', 'block.tpl'), 'podcaster', 'pcr_sidebox');
        }

    }

}

?>