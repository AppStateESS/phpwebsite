<?php
/**
 * skeleton - phpwebsite module
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


class Skeleton_Runtime
{

    public static function showBlock() {
        if (core\Settings::get('skeleton', 'enable_sidebox')) {
            if (core\Settings::get('skeleton', 'sidebox_homeonly')) {
                $key = \core\Key::getCurrent();
                if (!empty($key) && $key->isHomeKey()) {
                    Skeleton_Runtime::showSkeletonBlock();
                }
            } else {
                Skeleton_Runtime::showSkeletonBlock();
            }
        }
    }

    public function showSkeletonBlock() {

        $db = new \core\DB('skeleton_skeletons');
        $db->addColumn('id');
        $db->addOrder('rand');
        $db->setLimit(1);
        $result = $db->select();
        if (!core\Error::logIfError($result) && !empty($result)) {
            $tpl['TITLE'] = dgettext('skeleton', 'Skeletons');
            $tpl['LABEL'] = dgettext('skeleton', 'Random Skeleton');
            $tpl['TEXT'] = \core\Text::parseOutput(core\Settings::get('skeleton', 'sidebox_text'));
            \core\Core::initModClass('skeleton', 'Skeleton_Skeleton.php');
            $skeleton = new Skeleton_Skeleton($result[0]['id']);
            $tpl['NAME'] = $skeleton->viewLink();
            if ($skeleton->file_id) {
                $tpl['THUMBNAIL'] = $skeleton->getThumbnail(true);
            } else {
                $tpl['THUMBNAIL'] = null;
            }
            $tpl['LINK'] = \core\Text::moduleLink(dgettext('skeleton', 'List all skeletons'), 'skeleton', array('uop'=>'list_skeletons'));
            \core\Core::initModClass('layout', 'Layout.php');
            Layout::add(core\Template::process($tpl, 'skeleton', 'block.tpl'), 'skeleton', 'skeleton_sidebox');
        }

    }

}

?>