<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

pinFolder();

function pinFolder() {
    $key = Key::getCurrent();
    if ($key && !$key->isDummy()) {
        if ( Current_User::isUnrestricted('filecabinet') &&
             Current_User::allow('filecabinet', 'edit_folders') ) {
            $js['address'] = PHPWS_Text::linkAddress('filecabinet', array('aop'=>'pin_form', 'key_id'=>$key->id), true);
            $js['label'] = dgettext('filecabinet', 'Pin folder');
            $js['width'] = 360;
            $js['height'] = 150;
            $link = javascript('open_window', $js);
            MiniAdmin::add('filecabinet', $link);
        }

        PHPWS_Core::initModClass('filecabinet', 'Folder.php');
        Folder::getPinned($key->id);
    }
}

javascript('modules/filecabinet/fck_video_replace');
?>