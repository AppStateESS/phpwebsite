<?php

  /**
   * @version $Id: Photo.php 20 2006-10-18 18:36:05Z matt $
   * @author  Steven Levin <steven at NOSPAM tux[dot]appstate[dot]edu>
   * @modified Verdon Vaillancourt
   */

define('PHOTOALBUM_DUPLICATE_IMAGE', 2);

require_once(PHPWS_SOURCE_DIR . 'core/class/Item.php');
require_once(PHPWS_SOURCE_DIR . 'core/class/Text.php');
require_once(PHPWS_SOURCE_DIR . 'core/class/File.php');

class PHPWS_Photo extends PHPWS_Item {

    var $_album = NULL;
    var $_name = NULL;
    var $_width = NULL;
    var $_height = NULL;
    var $_type = NULL;
    var $_tnname = NULL;
    var $_tnwidth = NULL;
    var $_tnheight = NULL;
    var $_blurb = NULL;

    function PHPWS_Photo($id=NULL) {
        $this->setTable('mod_photoalbum_photos');

        if(isset($id)) {
            $this->_id = (int)$id;
            $this->init();
        } else {
            if(!isset($this->_album)) {
                $this->_album = $_SESSION['PHPWS_AlbumManager']->album->getId();
            }
        }
    }

    function _view($showLinks=TRUE) {
        $tags = array();

        $tags['PHOTO_ALBUM'] = $this->_album;
        $tags['PHOTO_NAME'] = $this->_name;
        $tags['PHOTO_TYPE'] = $this->_type;

        $tags['PHOTO_WIDTH'] = $this->_width;
        $tags['PHOTO_HEIGHT'] = $this->_height;
    
        $tags['SRC_WIDTH'] = $this->_width;
        $tags['SRC_HEIGHT'] = $this->_height;
    
        if ((($this->_width >= PHOTOALBUM_MAX_WIDTH || $this->_height >= PHOTOALBUM_MAX_HEIGHT)) && ($_REQUEST['PHPWS_Photo_op'] != 'print')) {
            $ratio = $this->_width / $this->_height;
            if ($ratio >= 1) {
                $tags['SRC_WIDTH'] = PHOTOALBUM_MAX_WIDTH;
                $tags['SRC_HEIGHT'] = PHOTOALBUM_MAX_WIDTH / $ratio;
            } else {
                $tags['SRC_WIDTH'] = PHOTOALBUM_MAX_HEIGHT * $ratio;
                $tags['SRC_HEIGHT'] = PHOTOALBUM_MAX_HEIGHT;
            }
        }

        $tags['WIDTH_TEXT'] = dgettext('photoalbum', 'Width');
        $tags['HEIGHT_TEXT'] = dgettext('photoalbum', 'Height');
        $tags['TYPE_TEXT'] = dgettext('photoalbum', 'Type');
    
        $tags['PHOTO_TEXT'] = dgettext('photoalbum', 'Upload Image');
        $tags['SHORT_TEXT'] = dgettext('photoalbum', 'Short');

        $tags['SHORT'] = PHPWS_Text::parseOutput($this->getLabel());

        if($showLinks) {
            if($this->isHidden()) {
                $tags['HIDDEN_INFO'] = dgettext('photoalbum', 'This photo is currently hidden from the public.');
            }

            if(isset($_SESSION['PHPWS_AlbumManager']->album->pager->limit)) {
                $getArray = array(
                                  'PHPWS_Album_op'=>'view',
                                  'PAGER_limit'   => $_SESSION['PHPWS_AlbumManager']->album->pager->limit,
                                  'PAGER_start'   => $_SESSION['PHPWS_AlbumManager']->album->pager->start,
                                  'PAGER_section'=>$_SESSION['PHPWS_AlbumManager']->album->pager->section);
            } else {
                $getArray = array('PHPWS_Album_op'=>'view');  //bookmarked pic
            }

            $tags['BACK_LINK'] = PHPWS_Text::moduleLink(dgettext('photoalbum', 'Back to album'), 'photoalbum', $getArray);

            $links = array();

            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Photo_op=print" target="_blank">' . dgettext('photoalbum', 'Print') . '</a>';

            if(Current_User::allow('photoalbum', 'edit_photo')) {
                $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Photo_op=edit">' . dgettext('photoalbum', 'Edit') . '</a>';
            }
            if(Current_User::allow('photoalbum', 'delete_photo')) {
                $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Photo_op=delete">' . dgettext('photoalbum', 'Delete') . '</a>';
            }

            $tags['PRINT_EDIT_DELETE_LINKS'] = implode('&#160;|&#160;', $links);

            if(is_array($_SESSION['PHPWS_AlbumManager']->album->photos)) {
                $key = array_search($this->getId(), $_SESSION['PHPWS_AlbumManager']->album->photos);
                if($key > 0) {
                    $tags['PREV_LINK'][] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_id=' . $this->_album . '&amp;PHPWS_Photo_op=view&amp;PHPWS_Photo_id=' . $_SESSION['PHPWS_AlbumManager']->album->photos[$key - 1] . '">&#60;&#60;</a>';
                    $tags['PREV_LINK'][] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_id=' . $this->_album . '&amp;PHPWS_Photo_op=view&amp;PHPWS_Photo_id=' . $_SESSION['PHPWS_AlbumManager']->album->photos[$key - 1] . '">' . dgettext('photoalbum', 'Prev') . '</a>';
                    $tags['PREV_LINK'] = implode('&#160;&#160;', $tags['PREV_LINK']);
                }

                if($key != (sizeof($_SESSION['PHPWS_AlbumManager']->album->photos) - 1)) {
                    $tags['NEXT_LINK'][] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_id=' . $this->_album . '&amp;PHPWS_Photo_op=view&amp;PHPWS_Photo_id=' . $_SESSION['PHPWS_AlbumManager']->album->photos[$key + 1] . '">' . dgettext('photoalbum', 'Next') . '</a>';
                    $tags['NEXT_LINK'][] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_id=' . $this->_album . '&amp;PHPWS_Photo_op=view&amp;PHPWS_Photo_id=' . $_SESSION['PHPWS_AlbumManager']->album->photos[$key + 1] . '">&#62;&#62;</a>';
                    $tags['NEXT_LINK'] = implode('&#160;&#160;', $tags['NEXT_LINK']);
                }
            }
        }

        if(isset($this->_blurb) && (strlen($this->_blurb) > 0)) {
            $tags['EXT_TEXT'] = dgettext('photoalbum', 'Extended');
            $tags['EXT'] = PHPWS_Text::parseOutput($this->_blurb);
        }

        $tags['UPDATED_TEXT'] = dgettext('photoalbum', 'Updated');
        $tags['UPDATED'] = $this->getUpdated();

        return PHPWS_Template::processTemplate($tags, 'photoalbum', 'viewPhoto.tpl');
    }

    function _edit() {
        /*
        if (empty($_SESSION['PHPWS_AlbumManager']->album->pager)) {
            return null;
        }
        $PAGER_limit   = $_SESSION['PHPWS_AlbumManager']->album->pager->limit;
        $PAGER_start   = $_SESSION['PHPWS_AlbumManager']->album->pager->start;
        $PAGER_section = $_SESSION['PHPWS_AlbumManager']->album->pager->section;
        */

        $id = $this->getId();
        $authorize = TRUE;
        if(isset($id)) {
            if(!Current_User::allow('photoalbum', 'edit_photo')) {
                Current_User::disallow();
                return;
            }
        } else {
            if(!Current_User::allow('photoalbum', 'add_photo')) {
                Current_User::disallow();
                return;
            }
        }
    
        $options = array(0=>dgettext('photoalbum', 'Visible'),
                         1=>dgettext('photoalbum', 'Hidden'));

        $hidden = 0;
        if($this->isHidden()) {
            $hidden = 1;
        }

        $form = new PHPWS_Form('PHPWS_Photo_edit');

        if(isset($this->_name)) {
            $form->add('Photo_remove', 'checkbox');
            $form->setTab('Photo_remove', 1);
        }

        $form->add('Photo', 'file');
        $form->add('Photo_short', 'text', $this->getLabel());
        $form->setSize('Photo_short', 40);
        $form->setMaxSize('Photo_short', 255);
        $form->setTab('Photo_short', 2);

        $form->add('Photo_hidden', 'select', $options);
        $form->setMatch('Photo_hidden', $hidden);
        $form->setTab('Photo_hidden', 3);

        $form->add('Photo_ext', 'textarea', $this->_blurb);
        $form->setTab('Photo_ext', 4);
        $form->useEditor('Photo_ext');

        $form->add('Photo_save', 'submit', dgettext('photoalbum', 'Save'));
        $form->setTab('Photo_save', 5);

        $form->add('module', 'hidden', 'photoalbum');
        /*
        $form->add('PAGER_limit', 'hidden', $PAGER_limit);
        $form->add('PAGER_start', 'hidden', $PAGER_start);
        $form->add('PAGER_section', 'hidden', $PAGER_section);
        */
        $form->add('PHPWS_Photo_op', 'hidden', 'save');
     
        $tags = array();
        $tags = $form->getTemplate();

        if(isset($this->_name)) {
            $tags['PHOTO_ALBUM'] = $this->_album;
            $tags['PHOTO_NAME'] = $this->_name;
            $tags['PHOTO_WIDTH'] = $this->_width;
            $tags['PHOTO_HEIGHT'] = $this->_height;
            $tags['PHOTO_TYPE'] = $this->_type;

            $tags['WIDTH_TEXT'] = dgettext('photoalbum', 'Width');
            $tags['HEIGHT_TEXT'] = dgettext('photoalbum', 'Height');
            $tags['TYPE_TEXT'] = dgettext('photoalbum', 'Type');
            $tags['REMOVE_TEXT'] = dgettext('photoalbum', 'Remove Image');

            $tags['UPDATED_TEXT'] = dgettext('photoalbum', 'Updated');
            $tags['UPDATED'] = $this->getUpdated();
        }

        /*
        $getArray = array(
                          'PHPWS_Album_op'=>'view',
                          'PAGER_limit'   => $PAGER_limit,
                          'PAGER_start'   => $PAGER_start,
                          'PAGER_section' => $PAGER_section);

        $tags['BACK_LINK'] = PHPWS_Text::moduleLink(dgettext('photoalbum', 'Back to album'), 'photoalbum', $getArray);
        */
        $tags['BACK_LINK'] = PHPWS_Text::backLink();

        $tags['PHOTO_TEXT'] = dgettext('photoalbum', 'Upload Image');
        $tags['SHORT_TEXT'] = dgettext('photoalbum', 'Short');
        $tags['EXT_TEXT'] = dgettext('photoalbum', 'Extended');
        $tags['HIDDEN_TEXT'] = dgettext('photoalbum', 'Activity');

        return PHPWS_Template::processTemplate($tags, 'photoalbum', 'editPhoto.tpl');
    }

    /**
     * @modified Verdon Vaillancourt
     */
    function _save() {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $id = $this->getId();
        $authorize = TRUE;
        if(isset($id)) {
            if(!Current_User::allow('photoalbum', 'edit_photo')) {
                Current_User::disallow();
                return FALSE;
            }
        } else {
            if(!Current_User::allow('photoalbum', 'add_photo')) {
                Current_User::disallow();
                return FALSE;
            }
        }

        if(isset($_REQUEST['Photo_short'])) {
            $error = $this->setLabel($_REQUEST['Photo_short']);
        }
    
        if(isset($_REQUEST['Photo_remove']) && ($_REQUEST['Photo_remove'] == 1)) {
            if($this->_unlink()) {
                if(!isset($this->_label))
                    $this->_label = '';

                $this->commit();

                trim($this->_label);

                $message = dgettext('photoalbum', 'The image was successfully removed.') . '<br /><br />';
                $_SESSION['PHPWS_AlbumManager']->message = $message;
            } else {
                $message =  dgettext('photoalbum', 'There was a problem removing the image.');
                $_SESSION['PHPWS_AlbumManager']->message = $message;
            }

            $_REQUEST['PHPWS_Photo_op'] = 'edit';
            $this->action();
            return;
        }

        if($_FILES['Photo']['error'] == 0) { 
            if(isset($this->_name)) {
                PHPWS_Error::log(PHOTOALBUM_DUPLICATE_IMAGE, 'photoalbum', 'PHPWS_Photo::save', $this->_name);
                $_SESSION['PHPWS_AlbumManager']->message = dgettext('photoalbum', 'You must remove the image before uploading a new one.');
                $_REQUEST['PHPWS_Photo_op'] = 'edit';
                $this->action();
                return;
            }

            $name = PHPWS_File::nameToSafe($_FILES['Photo']['name']);
            $name = strtolower($name);
            $file = PHOTOALBUM_DIR . $this->_album . '/' . $name;

            if(is_file($file)) {
                $name = time() . '_' . $name;
                $file = PHOTOALBUM_DIR . $this->_album . '/' . $name;
            }

            @move_uploaded_file($_FILES['Photo']['tmp_name'], $file);
            if(is_file($file)) {
                chmod($file, 0644);
                $info = @getimagesize($file);

                if (PHPWS_Image::allowImageType($_FILES['Photo']['type'])) {
                    $this->_name = $name;
                    $this->_type = $_FILES['Photo']['type'];
                    $this->_width = $info[0];
                    $this->_height = $info[1];      

                    if($info[2] == 1 || $info[2] == 2 || $info[2] == 3) {
                        $dir = 'images/photoalbum/' . $this->_album . '/';

                        $thumbnail = PHPWS_File::makeThumbnail($this->_name, $dir, $dir, PHOTOALBUM_TN_WIDTH, PHOTOALBUM_TN_HEIGHT);

                        if (!is_array($thumbnail)) {
                            exit('Thumbnail error');
                        }

                        if(is_file(PHOTOALBUM_DIR . $this->_album . '/' . $thumbnail[0])) {
                            $this->_tnname = $thumbnail[0];
                            $this->_tnwidth = $thumbnail[1];
                            $this->_tnheight = $thumbnail[2];

                            $_SESSION['PHPWS_AlbumManager']->album->image = '<img src="./images/photoalbum/' . $this->_album . '/' . $this->_tnname . '" width="' . $this->_tnwidth . '" height="' . $this->_tnheight . '" border="0" alt="' . htmlentities($this->getLabel()) . '" />';
                            $_SESSION['PHPWS_AlbumManager']->album->commit();
                        }
                    } else {
                        $thumbnail = new PHPWS_Error('Photo', '_save', 'Submitted image type does not have support built for the GD libraries.');
                    }
                } else {
                    @unlink($file);
                    $_SESSION['PHPWS_AlbumManager']->message = dgettext('photoalbum', 'The image uploaded was not an allowed image type.');
                    $_REQUEST['PHPWS_Photo_op'] = 'edit';
                    $this->action();
                    return;
                }
                if (PHOTOALBUM_RS) {
                    PHPWS_File::scaleImage($file, $file, PHOTOALBUM_RS_WIDTH, PHOTOALBUM_RS_HEIGHT);
                    $new_size = getimagesize($file);
                    $this->_width = $new_size[0];
                    $this->_height = $new_size[1];
                }
            } else {
                $_SESSION['PHPWS_AlbumManager']->message = dgettext('photoalbum', 'There was a problem uploading the specified image.');
                $_REQUEST['PHPWS_Photo_op'] = 'edit';
                $this->action();
                return;
            }
        } else if($_FILES['Photo']['error'] != 4) {
            $_SESSION['PHPWS_AlbumManager']->message = dgettext('photoalbum', 'The file uploaded exceeded the max size allowed.');
            $_REQUEST['PHPWS_Photo_op'] = 'edit';
            $this->action(); 
            return;
        }

        if(isset($_REQUEST['Photo_ext'])) {
            $this->_blurb = PHPWS_Text::parseInput($_REQUEST['Photo_ext']);
        }

        if(isset($_REQUEST['Photo_hidden']) && ($_REQUEST['Photo_hidden'] == 1)) {
            $this->setHidden();
        } else {
            $this->setHidden(FALSE);
        }

        if(isset($error) && PHPWS_Error::isError($error)) {
            $_SESSION['PHPWS_AlbumManager']->message = dgettext('photoalbum', 'You must enter a short description for the photo.');
            $_REQUEST['PHPWS_Photo_op'] = 'edit';
            $this->action();
            return;
        }

        $error = $this->commit();
        if(PHPWS_Error::isError($error)) {
            $_SESSION['PHPWS_AlbumManager']->message = dgettext('photoalbum', 'There was a problem saving the information to the database.');
            $_REQUEST['PHPWS_Photo_op'] = 'edit';
            $this->action(); 
            return;
        }

        $sql = 'UPDATE mod_photoalbum_albums SET image=\'' . $this->getThumbnail() . '\' WHERE id=\'' . $this->_album . '\'';
        PHPWS_DB::query($sql);

        if(isset($thumbnail) && PHPWS_Error::isError($thumbnail)) {
            $message='<span style="color:red">' . sprintf(dgettext('photoalbum', 'The Photo %s was saved but their was a problem creating the thumbnail image.'), 
                                                            '<b><i>' . $this->getLabel() . '</i></b>') . '</span><br /><br />';      
        } else {
            $message = sprintf(dgettext('photoalbum', 'The Photo %s was successfully saved.'), '<b><i>' . $this->getLabel() . '</i></b>') . "<br />\n";
        }

        $_SESSION['PHPWS_AlbumManager']->message = $message;

        $_REQUEST['PHPWS_Album_op'] = 'view';
        $_SESSION['PHPWS_AlbumManager']->album = new PHPWS_Album($this->_album);
        $_SESSION['PHPWS_AlbumManager']->album->action();
    }

    function _unlink() {
        if(isset($this->_name)) {
            @unlink(PHOTOALBUM_DIR . $this->_album . '/' . $this->_name);
        }
        if(!is_file(PHOTOALBUM_DIR . $this->_album . '/' . $this->_name)) {
            $this->_name = NULL;
            $this->_type = NULL;
            $this->_width = NULL;
            $this->_height = NULL;
        } else {
            return FALSE;
        }

        if(isset($this->_tnname)) {
            @unlink(PHOTOALBUM_DIR . $this->_album . '/' . $this->_tnname);
        }
        if(!is_file(PHOTOALBUM_DIR . $this->_album . '/' . $this->_tnname)) {
            $this->_tnname = NULL;
            $this->_tnwidth = NULL;
            $this->_tnheight = NULL;
        } else {
            return FALSE;
        }

        return TRUE;
    }

    function _delete() {
        /*
        $pagerSection= (int)$_SESSION['PHPWS_AlbumManager']->album->pager->section;
        $pagerLimit  = (int)$_SESSION['PHPWS_AlbumManager']->album->pager->limit;
        $pagerStart  = (int)$_SESSION['PHPWS_AlbumManager']->album->pager->start;
        */

        if(!Current_User::allow('photoalbum', 'delete_photo')) {
            Current_User::disallow();
            return;
        }

        if(isset($_REQUEST['Photo_yes'])) {
            $this->_unlink();
            $this->kill();

            $message = sprintf(dgettext('photoalbum', 'The photo %s was successfully deleted from the database.'), '<b><i>' . $this->getLabel() . '</i></b>');
            $_SESSION['PHPWS_AlbumManager']->message = $message;

            $_SESSION['PHPWS_AlbumManager']->updateAlbumList($this->_album);

            $_REQUEST['PHPWS_Album_op'] = 'view';
            $_SESSION['PHPWS_AlbumManager']->album = new PHPWS_Album($this->_album);
            $_SESSION['PHPWS_AlbumManager']->album->action();

        } else if(isset($_REQUEST['Photo_no'])) {
            $message = dgettext('photoalbum', 'No photo was deleted from the database.');
            $_SESSION['PHPWS_AlbumManager']->message = $message;

            /*
            $_REQUEST['PAGER_section'] = $pagerSection;
            $_REQUEST['PAGER_start']   = $pagerStart;
            */

            $_REQUEST['PHPWS_Album_op'] = 'view';
            $_SESSION['PHPWS_AlbumManager']->album->action();

        } else {
            $title = dgettext('photoalbum', 'Delete Photo Confirmation');

            $form = new PHPWS_Form('PHPWS_Photo_delete');
            $form->add('module', 'hidden', 'photoalbum');
            $form->add('PHPWS_Photo_op', 'hidden', 'delete');

            /*
            $form->add('PAGER_limit', 'hidden', $pagerLimit);

            if($_SESSION['PHPWS_AlbumManager']->album->pager->_itemCount == 1 && 
               ($pagerSection != 1 || $pagerSection != 0)) {
                $form->add('PAGER_section', 'hidden', $pagerSection - 1);
                $form->add('PAGER_start', 'hidden', $pagerStart - $pagerLimit);

            } else {
                $form->add('PAGER_section', 'hidden', $pagerSection);
                $form->add('PAGER_start', 'hidden', $_SESSION['PHPWS_AlbumManager']->album->pager->start);
            }
            */

            $form->add('Photo_yes', 'submit', dgettext('photoalbum', 'Yes'));
            $form->add('Photo_no', 'submit', dgettext('photoalbum', 'No'));
      
            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = dgettext('photoalbum', 'Are you sure you want to delete this photo?');
      
            $content = PHPWS_Template::process($tags, 'photoalbum', 'deletePhoto.tpl');
            $template['CONTENT'] = "<h3>$title</h3>$content";
            $template['CONTENT'] .= $this->_view(FALSE);

            $template['TITLE'] = dgettext('photoalbum', 'Photo Album') . ':&#160;' . $_SESSION['PHPWS_AlbumManager']->album->getLabel();
            Layout::add(PHPWS_Template::process($template, 'layout', 'box.tpl'));
        }
    }

    function _print() {
        Layout::nakedDisplay($this->_view(FALSE));
    }

    function getThumbnail() {
        $label = htmlentities($this->getLabel());

        $image = array();
        $image[] = '<img src="images/photoalbum/';
        $image[] = $this->_album . '/';
        $image[] = $this->_tnname . '" ';
        $image[] = 'width="' . $this->_tnwidth . '" ';
        $image[] = 'height="' . $this->_tnheight . '" ';
        $image[] = 'alt="' . addslashes($label) . '" ';
        $image[] = 'title="' . addslashes($label) . '" ';
        $image[] = 'border="0" />';
        return implode('', $image);
    }

    function getAlbum() {
        return $this->_album;
    }

    function action() {
        if(isset($_SESSION['PHPWS_AlbumManager']->message)) {
            javascript('alert', array('content'=>$_SESSION['PHPWS_AlbumManager']->message));
            unset($_SESSION['PHPWS_AlbumManager']->message);
        }

        if(isset($_REQUEST['PHPWS_Photo_op'])) {
            switch($_REQUEST['PHPWS_Photo_op']) {
            case 'view':
                $key = new Key($_SESSION['PHPWS_AlbumManager']->album->_key_id);
                if (!$key->allowView()) {
                    PHPWS_Core::errorPage('403');
                }
                $title = dgettext('photoalbum', 'View Photo');
                $content = $this->_view();
                break;
        
            case 'edit':
                if($this->_id == NULL)
                    $title = dgettext('photoalbum', 'New Photo');
                else
                    $title = dgettext('photoalbum', 'Edit Photo');

                $content = $this->_edit();
                break;
        
            case 'save':
                $this->_save();
                break;
        
            case 'delete':
                $this->_delete();
                break;
        
            case 'print':
                $this->_print();
                break;
            }
        }
      
        if(isset($content)) {
            $template['TITLE'] = $title;
            $template['CONTENT'] = $content;
            Layout::add(PHPWS_Template::process($template, 'layout', 'box.tpl'));
        }
    }

    function rowTpl($value)
    {
        $directory = './images/photoalbum/' . $value['album'];
        $vars['PHPWS_Album_id'] = $value['album'];
        $vars['PHPWS_Photo_id'] = $value['id'];
        $vars['PHPWS_Photo_op'] = 'view';

        $link = PHPWS_Text::linkAddress('photoalbum', $vars);
        $tpl['THUMBNAIL'] = sprintf('<a href="%s"><img src="%s/%s" title="%s" width="%s" height="%s"/></a>',
                                    $link,
                                    $directory, $value['tnname'],
                                    $value['label'], $value['tnwidth'],
                                    $value['tnheight']);

        $tpl['TITLE'] = sprintf('<a href="%s">%s</a>', $link, $value['label']);
        
        if (Current_User::allow('photoalbum', 'edit_photo')) {
            $vars['PHPWS_Photo_op'] = 'edit';
            $links[] = PHPWS_Text::secureLink(dgettext('photoalbum', 'Edit'), 'photoalbum', $vars);
        }

        if (Current_User::allow('photoalbum', 'delete_photo')) {
            $vars['PHPWS_Photo_op'] = 'delete';
            $links[] = PHPWS_Text::secureLink(dgettext('photoalbum', 'Delete'), 'photoalbum', $vars);
        }

        if (isset($links)) {
            $tpl['ADMIN_LINKS'] = implode(' | ', $links);
        }
        return $tpl;
    }
}

?>