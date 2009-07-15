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
    * @version $Id$
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/

function podcaster_uninstall(&$content) {

    if (isset($_REQUEST['process_uninstall'])) {
        
        if ($_REQUEST['rm_media']) {
            PHPWS_Core::initModClass('podcaster', 'PCR_Episode.php');
            $db = new PHPWS_DB('podcaster_episode');
            $db->addWhere('media_id', 0, '>');
            $episodes = $db->getObjects('Podcaster_Episode');
            if (PEAR::isError($episodes)) {
                return $episodes;
            } elseif (empty($episodes)) {
                /* go ahead and drop the tables */
                PHPWS_DB::dropTable('podcaster_channel');
                PHPWS_DB::dropTable('podcaster_episode');
                PHPWS_DB::dropTable('podcaster_category');
                $content[] = dgettext('podcaster', 'Podcaster tables dropped, no media to delete.');
                return true;
            }
            $error = false;
            $num = count($episodes);
            foreach ($episodes as $episode) {
                $media = $episode->getMedia();
                if ($media) {
                    $result = $media->delete();
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                        $error = true;
                    }
                }
            }
            /* go ahead and drop the tables */
            PHPWS_DB::dropTable('podcaster_channel');
            PHPWS_DB::dropTable('podcaster_episode');
            PHPWS_DB::dropTable('podcaster_category');
            $content[] = sprintf(dgettext('podcaster', 'Podcaster tables dropped, %s media file(s) deleted.'), $num);
            return true;
        } else {
            /* go ahead and drop the tables */
            PHPWS_DB::dropTable('podcaster_channel');
            PHPWS_DB::dropTable('podcaster_episode');
            PHPWS_DB::dropTable('podcaster_category');
            $content[] = dgettext('podcaster', 'Podcaster tables dropped.');
            return true;
        }

        return false;
    } 


    $form = & new PHPWS_Form('rm_media_confirm');

    $form->addHidden('module', 'boost');
    $form->addHidden('opmod', 'podcaster');
    $form->addHidden('action', 'uninstall');
    $form->addHidden('authkey', $_REQUEST['authkey']);
    $form->addHidden('confirm', 'podcaster');
    $form->setMethod('get');

    $form->addHidden('process_uninstall', 1);
    
    if (PHPWS_Settings::get('podcaster', 'rm_media')) {
        $match = 1;
    } else {
        $match = 0;
    }

    $form->addRadio('rm_media', array(0, 1));
    $form->setLabel('rm_media', array(dgettext('podcaster', 'No'), dgettext('podcaster', 'Yes')));
    $form->setMatch('rm_media', $match);

    $form->addSubmit(dgettext('layout', 'Continue'));
  
    $template = $form->getTemplate();
    
    $template['RM_MEDIA'] = dgettext('podcaster', 'Delete related media files from filecabinet?');
    $content[] = PHPWS_Template::process($template, 'podcaster', 'uninstall.tpl');
    
    return false;

}
?>