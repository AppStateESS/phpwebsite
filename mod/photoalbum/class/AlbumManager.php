<?php

/**
 * @version $Id$
 * @author  Steven Levin <steven at NOSPAM tux[dot]appstate[dot]edu>
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 */

require_once(PHPWS_SOURCE_DIR . 'mod/photoalbum/class/Album.php');
PHPWS_Core::initModClass('photoalbum', 'Message.php');
class PHPWS_AlbumManager {

    /**
     * Stores the ids of the photos for the current album being viewed
     *
     * @var    PHPWS_Album
     * @access public
     */
    public $album = NULL;

    /**
     * Stores the current error that has occured in the photoalbum
     *
     * @var    Old_Error
     * @access public
     */
    public $error = NULL;

    /**
     * Stores the current message to display for the photoalbum
     *
     * @var    PHPWS_Message
     * @access public
     */
    public $message = NULL;

    public function _list() {
        if(!function_exists('imagecreate')) {
            $content = "<div style=\"color:#ff0000;\">Error!</div>The photoalbum module requires the GD library functions.
                    If you are getting this error then your GD libs are missing.
                    Please contact your systems administrator to resolve this issue.
                    With versions of php prior to 4.3.0 you must compile the GD libs into your build of php (--with-gd[=DIR], where DIR is the GD base install directory)
                    Php 4.3.0 and greater have the GD libs already built in.
                    For more information please refer to the
                    <a href=\"http://www.php.net/manual/en/ref.image.php\" target=\"_blank\">PHP Image Function Manual</a><br /><br />";
            Layout::add($content);
            return;
        }

        PHPWS_Core::initCoreClass('DBPager.php');
        $template['TITLE'] = dgettext('photoalbum', 'Photo Albums');
        $template['CONTENT'] = NULL;

        $pager = new DBPager('mod_photoalbum_albums');
        $pager->setModule('photoalbum');
        $pager->setTemplate('albums/list.tpl');

        if(Current_User::allow('photoalbum', 'add_album')) {
            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_AlbumManager_op=new">' . dgettext('photoalbum', 'New Album') . '</a>';
        }

        $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_AlbumManager_op=list">' . dgettext('photoalbum', 'List Albums') . '</a>';

        $pageTags['LINKS']         = implode(' | ', $links);
        $pageTags['IMAGE_LABEL']   = dgettext('photoalbum', 'Last image');
        $pageTags['LABEL_LABEL']   = dgettext('photoalbum', 'Album name');
        $pageTags['BLURB_LABEL']   = dgettext('photoalbum', 'Information');
        $pageTags['UPDATED_LABEL'] = dgettext('photoalbum', 'Last updated');
        $pageTags['ACTION_LABEL']  = dgettext('photoalbum', 'Action');

        $pager->addPageTags($pageTags);
        $pager->addRowFunction(array('PHPWS_Album', 'AlbumRow'));

        if(!Current_User::allow('photoalbum', 'edit_album')) {
            $pager->addWhere('hidden', 0);
        }

        $pager->setOrder('updated', 'DESC');
        $pager->setEmptyMessage(dgettext('photoalbum', 'No albums found.'));
        $content = $pager->get();

        Layout::add($content);
    }

    public function _new() {
        $this->album = new PHPWS_Album;
        $_REQUEST['PHPWS_Album_op'] = 'edit';
    }

    public function _accessDenied() {
        if(PHPWS_Error::isError($this->error)) {
            $this->error->message('CNT_photoalbum', dgettext('photoalbum', 'Access Denied!'));
            $this->error = NULL;
        } else {
            $message = dgettext('photoalbum', 'Access denied function was called without a proper error initialized.');
            $error = new Old_Error('photoalbum', 'PHPWS_AlbumManager::_accessDenied()', $message, 'exit', 1);
            $error->message();
        }
    }

    public function updateAlbumList($albumId) {
        $sql = "SELECT label, tnname, tnwidth, tnheight FROM mod_photoalbum_photos WHERE album='$albumId' ORDER BY updated DESC LIMIT 1";
        $result = PHPWS_DB::getAll($sql);

        if(isset($result[0])) {
            $image[] = '<img src="images/photoalbum/';
            $image[] = $albumId . '/';
            $image[] = $result[0]['tnname'] . '" ';
            $image[] = 'width="' . $result[0]['tnwidth'] . '" ';
            $image[] = 'height="' . $result[0]['tnheight'] . '" ';
            $image[] = 'alt="' . $result[0]['label'] . '" ';
            $image[] = 'title="' . $result[0]['label'] . '" ';
            $image[] = 'border="0" />';
            $image = implode('', $image);

            $time = time();
            $sql = "UPDATE mod_photoalbum_albums SET image='$image', updated='$time' WHERE id='$albumId'";
            PHPWS_DB::query($sql);
        }
    }

    public function action() {
        if (isset($this->message)) {
            javascipt('alert', array('content' => $this->message));
            unset($this->message);
        }


        if(isset($_REQUEST['PHPWS_Album_id']) && is_numeric($_REQUEST['PHPWS_Album_id'])) {
            if(!isset($this->album) || ($this->album->getId() != $_REQUEST['PHPWS_Album_id'])) {
                $this->album = new PHPWS_Album($_REQUEST['PHPWS_Album_id']);
            }
        }

        if(isset($_REQUEST['PHPWS_AlbumManager_op'])) {
            switch($_REQUEST['PHPWS_AlbumManager_op']) {
                case 'list':
                    $this->_list();
                    break;

                case 'new':
                    if (!Current_User::allow('photoalbum')) {
                        Current_User::disallow();
                    }
                    $this->_new();
                    break;

                case 'accessDenied':
                    $this->_accessDenied();
                    break;
            }
        }
    }

}


?>