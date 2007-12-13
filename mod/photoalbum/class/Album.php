<?php

  /**
   * @version $Id$
   * @author  Steven Levin
   * @modified Matthew McNaney <mcnaney at gmail dot com>
   * @modified Verdon Vaillancourt
   */

require_once(PHPWS_SOURCE_DIR . 'core/class/Item.php');
require_once(PHPWS_SOURCE_DIR . 'core/class/File.php');
require_once(PHPWS_SOURCE_DIR . 'mod/photoalbum/class/SlideShow.php');
require_once(PHPWS_SOURCE_DIR . 'mod/photoalbum/class/NoJSSlideShow.php');
require_once(PHPWS_SOURCE_DIR . 'mod/photoalbum/class/Photo.php');

define('PHOTOALBUM_NO_DIRECTORY', 1);

class PHPWS_Album extends PHPWS_Item {
    var $_key_id = 0;

    /**
     * The short description of the photo album
     *
     * @var    string
     * @access private
     */
    var $_blurb0 = NULL;

    /**
     * The extended description of the photo album
     *
     * @var    string
     * @access private
     */
    var $_blurb1 = NULL;

    /**
     * The image to be shown in the album list
     *
     * @var    string
     * @access private
     */
    var $image = NULL;

    /**
     * The ids of all the photos for the current album
     *
     * @var    array
     * @access public
     */
    var $photos = array();

    /**
     * The current photo being edited or viewed
     *
     * @var    PHPWS_Photo
     * @access public
     */
    var $photo = NULL;

    var $page_limit = 9;

    /**
     * An array of the file information for files being batch added
     *
     * @var    array
     * @access private
     */
    var $_batch = array();  

    /**
     * Stores the order in which the photos in the album are being shown
     *
     * @var    integer
     * @access public
     */
    var $order = NULL;

    function PHPWS_Album($id=NULL) {
        $this->setTable('mod_photoalbum_albums');
        $this->addExclude(array('photos', 'photo', 'pager', '_batch', 'order'));


        if(!empty($id)) {
            $this->setId($id);
            $this->init();

            $this->order = PHOTOALBUM_DEFAULT_SORT;

            $this->_orderIds();
        }
    }

    function _orderIds() {
        $sql = array();
        $sql[] = 'SELECT id FROM ';
        $sql[] = 'mod_photoalbum_photos WHERE album=\'';
        $sql[] = $this->getId();
        $sql[] = '\'';
    
        if(!Current_User::allow('photoalbum', 'edit_photo')) {
            $sql[] = ' AND hidden=\'0\'';
        }

        if($this->order == 0) {
            $order = ' ORDER BY created DESC';
        } else {
            $order = ' ORDER BY created ASC';
        }

        $sql = implode('', $sql) . $order;
        $this->photos = PHPWS_DB::getCol($sql);
    }


    function _edit() {
        $id = $this->getId();
        $authorize = TRUE;
        if(isset($id)) {
            if(!Current_User::allow('photoalbum', 'edit_album')) {
                Current_User::disallow();
                return;
            }
        } else {
            if(!Current_User::allow('photoalbum', 'add_album')) {
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

        $form = new PHPWS_Form('PHPWS_Album_edit');
        $form->add('Album_name', 'text', $this->getLabel());
        $form->setSize('Album_name', 33);
        $form->setMaxSize('Album_name', 255);
        $form->setTab('Album_name', 1);

        $form->add('Album_short', 'text', $this->_blurb0);
        $form->setSize('Album_short', 40);
        $form->setMaxSize('Album_short', 255);
        $form->setTab('Album_short', 2);

        $form->add('Album_hidden', 'select', $options);
        $form->setMatch('Album_hidden', $hidden);
        $form->setTab('Album_hidden', 3);

        $form->add('Album_ext', 'textarea', $this->_blurb1);
        $form->setTab('Album_ext', 4);
        $form->useEditor('Album_ext');

        $form->add('Album_save', 'submit', dgettext('photoalbum', 'Save'));
        $form->setTab('Album_save', 5);
 
        $form->add('module', 'hidden', 'photoalbum');
        $form->add('PHPWS_Album_op', 'hidden', 'save');
     
        $tags = array();
        $tags = $form->getTemplate();

        $tags['NAME_TEXT'] = dgettext('photoalbum', 'Name');
        $tags['SHORT_TEXT'] = dgettext('photoalbum', 'Short');
        $tags['HIDDEN_TEXT'] = dgettext('photoalbum', 'Activity');
        $tags['EXT_TEXT'] = dgettext('photoalbum', 'Extended');

        $id = $this->getId();
        if(isset($id)) {
            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=view">' . dgettext('photoalbum', 'Back') . '</a>';

            if(Current_User::allow('photoalbum', 'delete_album')) {
                $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=delete">' . dgettext('photoalbum', 'Delete Album') . '</a>';
            }
        } else {
            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_AlbumManager_op=list">' . dgettext('photoalbum', 'Back') . '</a>';
        }

        $tags['LINKS'] = implode('&#160;|&#160;', $links);

        return PHPWS_Template::processTemplate($tags, 'photoalbum', 'editAlbum.tpl');
    }

    function _save() {
        $id = $this->getId();
        $authorize = TRUE;
        if(isset($id)) {
            if(!Current_User::allow('photoalbum', 'edit_album')) {
                Current_User::disallow();
            }
        } else {
            if(!Current_User::allow('photoalbum', 'add_album')) {
                Current_User::disallow();
            }
        }

        if(!$authorize) {
            $_SESSION['PHPWS_AlbumManager']->error = new PHPWS_Error('photoalbum', 'PHPWS_Album::_save()', $message);
            $_REQUEST['PHPWS_AlbumManager_op'] = 'accessDenied';
            $_SESSION['PHPWS_AlbumManager']->action();
            return;
        }
    
        if(isset($_REQUEST['Album_ext'])) {
            $this->_blurb1 = PHPWS_Text::parseInput($_REQUEST['Album_ext']);
        }

        if(isset($_REQUEST['Album_short'])) {
            $this->_blurb0 = PHPWS_Text::parseInput($_REQUEST['Album_short']);
        }

        if(isset($_REQUEST['Album_name'])) {
            $error = $this->setLabel($_REQUEST['Album_name']);
        }

        if(isset($_REQUEST['Album_hidden']) && ($_REQUEST['Album_hidden'] == 1)) {
            $this->setHidden();
        } else {
            $this->setHidden(FALSE);
        }

        if(PHPWS_Error::isError($error)) {
            $message =  dgettext('photoalbum', 'You must enter a name for the Photo Album.');
            javascript('alert', array('content' => $message));
            $_REQUEST['PHPWS_Album_op'] = 'edit';
            $this->action();
            return;
        }

        $error = $this->commit();
        if(PHPWS_Error::isError($error)) {
            $message = dgettext('photoalbum', 'The Photo Album could not be updated to the database.');
            javascript('alert', array('content' => $message));
     
            $_REQUEST['PHPWS_Album_op'] = 'edit';
            $this->action();
            return;
        } else {
            $id = $this->getId();

            $directory = PHOTOALBUM_DIR . $this->getId() . '/';

            if(!is_dir($directory) && !@mkdir($directory)) {
                $message = dgettext('photoalbum', 'The photo album image directory could not be created.');
                PHPWS_Error::log(PHOTOALBUM_NO_DIRECTORY, 'photoalbum', 'PHPWS_Album::_save', $directory);
                $_REQUEST['PHPWS_Album_op'] = 'edit';
            } else {
                $message = sprintf(dgettext('photoalbum', 'The Photo Album "%s" was successfully saved.'), $this->getLabel());
                $_REQUEST['PHPWS_Album_op'] = 'view';
            }

            $_SESSION['PHPWS_AlbumManager']->message = $message;

            $this->saveKey();

            $this->action();
        }
    }

    function _delete() {
        if(!Current_User::allow('photoalbum', 'delete_album')) {
            Current_User::disallow();
            return;
        }

        if(isset($_REQUEST['Album_yes'])) {
            $this->kill();
            $db = new PHPWS_DB('mod_photoalbum_photos');
            $db->addWhere('album', $this->getId());
            $db->delete();

            PHPWS_File::rmdir('images/photoalbum/' . $this->getId() . '/');

            $message = sprintf(dgettext('photoalbum', 'The album %s and all its photos were successfully deleted from the database.'), $this->getLabel());
            javascript('alert', array('content' => $message));
            Layout::add($message);

            $_REQUEST['PHPWS_AlbumManager_op'] = 'list';
            $_SESSION['PHPWS_AlbumManager']->action();
            unset($this);

        } else if(isset($_REQUEST['Album_no'])) {
            $message = dgettext('photoalbum', 'No album was deleted from the database.');
            $_SESSION['PHPWS_AlbumManager']->message = $message;

            $_REQUEST['PHPWS_Album_op'] = 'view';
            $_SESSION['PHPWS_AlbumManager']->album->action();

        } else {
            $title = dgettext('photoalbum', 'Delete Album Confirmation');

            $form = new PHPWS_Form('PHPWS_Album_delete');
            $form->add('module', 'hidden', 'photoalbum');
            $form->add('PHPWS_Album_op', 'hidden', 'delete');

            $form->add('Album_yes', 'submit', dgettext('photoalbum', 'Yes'));
            $form->add('Album_no', 'submit', dgettext('photoalbum', 'No'));
      
            $tags = array();
            $tags = $form->getTemplate();
            $tags['MESSAGE'] = dgettext('photoalbum', 'Are you sure you want to delete this album and all the photos associated with it?');
      
            $content = PHPWS_Template::processTemplate($tags, 'photoalbum', 'deleteAlbum.tpl');

            $newLayout['TITLE']   = dgettext('photoalbum', 'Photo Album') . ':&#160;' . $_SESSION['PHPWS_AlbumManager']->album->getLabel();
            $newLayout['CONTENT'] = "<h3>$title</h3>$content";
            $newLayout['CONTENT'] .= $this->_view();
      
            $finalContent = PHPWS_Template::process($newLayout, 'layout', 'box.tpl');
            Layout::add($finalContent);
        }
    }
    
    function _view() {
        PHPWS_Core::initCoreClass('DBPager.php');

        $columns = NULL;
        $id = $this->getId();

        $key = new Key($this->_key_id);
        if (!$key->allowView()) {
            PHPWS_Core::errorPage('403');
        }
        $key->flag();

        $pager = new DBPager('mod_photoalbum_photos');
        $pager->setModule('photoalbum');
        $pager->setTemplate('photos/list.tpl');
        $pager->setEmptyMessage(dgettext('photoalbum', 'No photos found.'));
        $pager->setDefaultLimit(9);
        $pager->addWhere('album', $id);
        if (isset($_REQUEST['missing_desc']) && Current_User::allow('photoalbum', 'edit_photo')) {
            $pager->addWhere('blurb', '');
        }

        $pager->setOrder('created', 'desc', true);

        $limits[1]  = 1;
        $limits[4]  = 4;
        $limits[9]  = 9;
        $limits[16] = 16;
        $pager->setLimitList($limits);

        if ($this->isHidden()) {
            $listTags['HIDDEN_INFO'] = dgettext('photoalbum', 'This album is currently hidden from the public.');
        }
        $listTags['ALBUM_LINKS'] = implode('&#160;|&#160;', $this->getAlbumLinks());
        $listTags['ORDER_LABEL'] = dgettext('photoalbum', 'Order By');
        $listTags['FIRST_LABEL'] = dgettext('photoalbum', 'First');
        $listTags['BLURB0'] = PHPWS_Text::parseOutput($this->_blurb0);
        $listTags['BLURB1'] = PHPWS_Text::parseOutput($this->_blurb1);
        $listTags['CREATED'] = $this->getCreated();
        $listTags['UPDATED'] = $this->getUpdated();
        $listTags['OWNER'] = $this->getOwner();
        $listTags['EDITOR'] = $this->getEditor();

        $pagerLimit = '&amp;PAGER_limit=' . $this->page_limit;

        $link_val = $pager->getLinkValues();
        $link_val['orderby'] = 'created';

        if($pager->orderby_dir == 'desc') {
            $link_val['orderby_dir'] = 'asc';
            $listTags['ASC_ORDER_LINK'] = PHPWS_Text::moduleLink(dgettext('photoalbum', 'Oldest'), 'photoalbum', $link_val);
            $listTags['DESC_ORDER_LINK'] = dgettext('photoalbum', 'Newest');
        } else {
            $link_val['orderby_dir'] = 'desc';
            $listTags['DESC_ORDER_LINK'] = PHPWS_Text::moduleLink(dgettext('photoalbum', 'Newest'), 'photoalbum', $link_val);
            $listTags['ASC_ORDER_LINK'] = dgettext('photoalbum', 'Oldest');
        }

        $pager->addPageTags($listTags);
        $pager->initialize();

        $pager->addRowFunction(array('PHPWS_Photo', 'rowTpl'));
        $content = $pager->get();
        return $content;
    }

    function _new() {
        $this->photo = new PHPWS_Photo;
        $_REQUEST['PHPWS_Photo_op'] = 'edit';
    }

    function _batchAdd($numForms=5) {
        $id = $this->getId();
        $authorize = TRUE;
        if(!Current_User::allow('photoalbum', 'add_photo')) {
            $message = dgettext('photoalbum', 'You do not have permission to add photos within an album.');
            $authorize = FALSE;
        }

        if(!$authorize) {
            $_SESSION['PHPWS_AlbumManager']->error = new PHPWS_Error('photoalbum', 'PHPWS_Photo::_edit()', $message, 'continue', PHOTOALBUM_DEBUG_MODE);
            $_REQUEST['PHPWS_AlbumManager_op'] = 'accessDenied';
            $_SESSION['PHPWS_AlbumManager']->action();
            return;
        }

        if(isset($_REQUEST['Num_forms']) && ($_REQUEST['Num_forms'] <= PHOTOALBUM_MAX_UPLOADS)) {
            $numForms = $_REQUEST['Num_forms'];

            $orginSize = sizeof($this->_batch) + 1;
            for($i = $numForms; $i <= $orginSize; $i++) {
                if(isset($this->_batch[$i]))
                    unset($this->_batch[$i]);
            }
        }
   
        $form = new PHPWS_Form('PHPWS_Batch_add');

        for($i = 1; $i <= PHOTOALBUM_MAX_UPLOADS; $i++) {
            $uploads[$i] = $i;
        }

        $form->add('Num_forms', 'select', $uploads);
        $form->setMatch('Num_forms', $numForms);
        $form->add('Photos_update', 'submit', dgettext('photoalbum', 'Update'));
        $form->add('Photos_save', 'submit', dgettext('photoalbum', 'Save'));
        $form->add('module', 'hidden', 'photoalbum');
        $form->add('PHPWS_Album_op', 'hidden', 'batchSave');
        $form->setEncode(TRUE);
     
        $formTags = array();
        $formTags = $form->getTemplate();

        $formTags['BACK_LINK'] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=view">' . dgettext('photoalbum', 'Back to album') . '</a>';
        $formTags['NUM_FORMS_TEXT'] = dgettext('photoalbum', 'Number of photos (select before uploading)');
        $formTags['PHOTO_FORMS'] = '';

        $options = array(0=>dgettext('photoalbum', 'Visible'),
                         1=>dgettext('photoalbum', 'Hidden'));

        $tags = array();
        $tags['PHOTO_TEXT'] = dgettext('photoalbum', 'Upload Image');
        $tags['SHORT_TEXT'] = dgettext('photoalbum', 'Short');
        $tags['HIDDEN_TEXT'] = dgettext('photoalbum', 'Activity');
        $tags['PHOTO_NUMBER_TEXT'] = dgettext('photoalbum', 'Photo Number');

        for($i = 0; $i < $numForms; $i++) {      
            if(isset($this->_batch[$i]['error'])) {
                $tags['ERROR'] = $this->_batch[$i]['error'];
                $this->_batch[$i]['error'] = NULL;
                unset($this->_batch[$i]['error']); 
            } else {
                $tags['ERROR'] = NULL;
            }

            $tags['PHOTO_NUMBER'] = $i+1;
            $tags['PHOTO_UPLOAD'] = NULL;
            if(isset($this->_batch[$i]['name'])) {
                $tags['PHOTO_UPLOAD'] = array();
                $tags['PHOTO_UPLOAD'][] = '<img src="images/photoalbum/' . $this->getId() . '/';
                $tags['PHOTO_UPLOAD'][] = $this->_batch[$i]['tnname'] . '"';
                $tags['PHOTO_UPLOAD'][] = ' width="' . $this->_batch[$i]['tnwidth'] . '"';
                $tags['PHOTO_UPLOAD'][] = ' height="' . $this->_batch[$i]['tnheight'] . '"';
                $tags['PHOTO_UPLOAD'][] = ' border="0" />';
                $tags['PHOTO_UPLOAD'] =  implode('', $tags['PHOTO_UPLOAD']);
            }


            $tags['PHOTO_UPLOAD'] .= '<br /><input type="file" name="Photo[]" />';
            if(isset($this->_batch[$i]['label'])) {
                $label = $this->_batch[$i]['label'];
            } else {
                $label = NULL;
            }
            $tags['SHORT'] = '<input type="text" name="Photo_short[]" size="25" maxsize="255" value="' . $label . '" />';

            $tags['HIDDEN'] = "<select name=\"Photo_hidden[]\">\n";
            foreach($options as $key => $value) {
                if(isset($this->_batch[$i]['hidden']) && ($key == $this->_batch[$i]['hidden'])) {
                    $tags['HIDDEN'] .= "<option value=\"$key\" selected=\"selected\">$value</option>\n";
                } else {
                    $tags['HIDDEN'] .= "<option value=\"$key\">$value</option>\n";
                }
            }
            $tags['HIDDEN'] .= '</select>';
            $formTags['PHOTO_FORMS'] .= PHPWS_Template::processTemplate($tags, 'photoalbum', 'albums/photoForm.tpl');
        }

        return PHPWS_Template::processTemplate($formTags, 'photoalbum', 'albums/batchAdd.tpl');
    }

    function _batchSave() {

        if(isset($_REQUEST['Photos_update'])) {
            $_REQUEST['PHPWS_Album_op'] = 'batch';
            $this->action();
            return;
        }

        if(isset($_REQUEST['Num_forms']) && ($_REQUEST['Num_forms'] <= PHOTOALBUM_MAX_UPLOADS)) {
            $numForms = $_REQUEST['Num_forms'];

            $orginSize = sizeof($this->_batch) + 1;
            for($i = $numForms; $i <= $orginSize; $i++) {
                if(isset($this->_batch[$i]))
                    unset($this->_batch[$i]);
            }
        }

        $error = FALSE;

        $allowedImageTypes = unserialize(ALLOWED_IMAGE_TYPES);

        foreach($_FILES['Photo']['error'] as $key => $value) {
            $this->_batch[$key]['hidden'] = $_REQUEST['Photo_hidden'][$key];

            if(isset($_REQUEST['Photo_short'][$key])) {
                $this->_batch[$key]['label'] = PHPWS_Text::parseInput($_REQUEST['Photo_short'][$key]);
            }

            if($value == 0) {
                if(isset($this->_batch[$key]['name'])) {
                    if(is_file(PHOTOALBUM_DIR . $this->getId() . '/' . $this->_batch[$key]['name'])) {
                        unlink(PHOTOALBUM_DIR . $this->getId() . '/' . $this->_batch[$key]['name']);

                    }
                }

                if(isset($this->_batch[$key]['tnname'])) {
                    if(is_file(PHOTOALBUM_DIR . $this->getId() . '/' . $this->_batch[$key]['tnname'])) {
                        unlink(PHOTOALBUM_DIR . $this->getId() . '/' . $this->_batch[$key]['tnname']);
                    }
                }

                $name = PHPWS_File::nameToSafe($_FILES['Photo']['name'][$key]);
                $name = strtolower($name);
                $file = PHOTOALBUM_DIR . $this->getId() . '/' . $name;
                if(is_file($file)) {
                    $name = time() . '_' . $name;
                    $file = PHOTOALBUM_DIR . $this->getId() . '/' . $name;
                }

                @move_uploaded_file($_FILES['Photo']['tmp_name'][$key], $file);
                if(is_file($file)) {
                    chmod($file, 0644);
                    $info = @getimagesize($file);

                    if(in_array($_FILES['Photo']['type'][$key], $allowedImageTypes)) {
                        $this->_batch[$key]['name'] = $name;
                        $this->_batch[$key]['type'] = $_FILES['Photo']['type'][$key];
                        $this->_batch[$key]['width'] = $info[0];
                        $this->_batch[$key]['height'] = $info[1];
            
                        if($info[2] == 2 || $info[2] == 3) {
                            $dir = PHOTOALBUM_DIR . $this->getId() . '/';
                            $thumbnail = PHPWS_File::makeThumbnail($this->_batch[$key]['name'], $dir, $dir, PHOTOALBUM_TN_WIDTH, PHOTOALBUM_TN_HEIGHT);
                            if(is_file($dir . $thumbnail[0])) {
                                $this->_batch[$key]['tnname'] = $thumbnail[0];
                                $this->_batch[$key]['tnwidth'] = $thumbnail[1];
                                $this->_batch[$key]['tnheight'] = $thumbnail[2];
                            }
                            if(PHOTOALBUM_RS) {
                                $resized = PHPWS_File::makeThumbnail($this->_batch[$key]['name'], $dir,
                                                                     $dir, PHOTOALBUM_RS_WIDTH, PHOTOALBUM_RS_HEIGHT, TRUE);
                                if(is_file($dir . $resized[0])) {
                                    $this->_batch[$key]['width'] = $resized[1];
                                    $this->_batch[$key]['height'] = $resized[2];
                                }
                            }
                        }
                    } else {
                        $error = TRUE;
                        $this->_batch[$key]['error'] = dgettext('photoalbum', 'The image uploaded was not an allowed image type.');
                    }
                } else {
                    $error = TRUE;
                    $this->_batch[$key]['error'] = dgettext('photoalbum', 'There was a problem uploading the specified file.');
                }

                if(!(strlen($this->_batch[$key]['label']) > 0)) {
                    $error = TRUE;
                    $this->_batch[$key]['error'] = dgettext('photoalbum', 'A file was uploaded but no short description was given.');
                }
            } else if($value == 4) {
                if(!(isset($this->_batch[$key]['name']) && (strlen($this->_batch[$key]['label']) > 0))) {
                    $error = TRUE;
                    if(strlen($this->_batch[$key]['label']) > 0) {
                        $this->_batch[$key]['error'] = dgettext('photoalbum', 'A short description was added but no file was uploaded.');
                    } else if(isset($this->_batch[$key]['name'])) {
                        $this->_batch[$key]['error'] = dgettext('photoalbum', 'A file was uploaded but no short description was given.');
                    } else {
                        $this->_batch[$key]['error'] = dgettext('photoalbum', 'No file was uploaded and no short description was given.');
                    }
                }
            } else {
                $error = TRUE;
                if($value != 4) {
                    $this->_batch[$key]['error'] = dgettext('photoalbum', 'The uploaded file exceeded the max file size allowed.');
                }
            }
        }

        if($error) {
            $_REQUEST['PHPWS_Album_op'] = 'batch';
            $this->action();
            return;
        } else {
            $count = 0;

            foreach($this->_batch as $value) {
                if(is_array($value) && (sizeof($value) > 0)) {
                    $value['album'] = $this->getId();
                    $value['created'] = time() + $count;
                    $value['updated'] = time() + $count;
                    $value['ip'] = $_SERVER['REMOTE_ADDR'];
                    $value['owner'] = Current_User::getUsername();
                    $value['editor'] = Current_User::getUsername();
                    $value['approved'] = TRUE;
                    $db = new PHPWS_DB('mod_photoalbum_photos');
                    $db->addValue($value);
                    $db->insert();
                    
                    $count ++;
                }
            }

            $_SESSION['PHPWS_AlbumManager']->updateAlbumList($this->getId());

            $this->_batch = array();
            $_REQUEST['PHPWS_Album_op'] = 'view';
            $this->PHPWS_Album($this->_id);
            $this->action();
        }
    }

    function _slideShow() {
        if(javascriptEnabled() && (!isset($_REQUEST['SS_mode']) ||
                                            (isset($_REQUEST['SS_mode']) &&
                                             $_REQUEST['SS_mode'] != 'nojsmode'))) {
            $this->_orderIds();
            return SlideShow::play($this->photos);
        } else {
            if(!isset($_SESSION['NO_JS_SLIDESHOW']) || $_SESSION['NO_JS_SLIDESHOW']->getCount() != count($this->photos) || 
               $_SESSION['NO_JS_SLIDESHOW']->album_id != $this->_id)
                $_SESSION['NO_JS_SLIDESHOW'] = new NoJSSlideShow($this->photos, $this->_id);
            return $_SESSION['NO_JS_SLIDESHOW']->play();
        }
    }

    function saveKey()
    {
        $update_album = FALSE;

        if (empty($this->_key_id)) {
            $key = new Key;
            $update_album = TRUE;
        } else {
            $key = new Key($this->_key_id);
            if (PEAR::isError($key->_error)) {
                $key = new Key;
                $update_album = TRUE;
            }
        }

        $link = sprintf('index.php?module=photoalbum&PHPWS_Album_op=view&PHPWS_Album_id=%s', $this->_id);

        $key->setModule('photoalbum');
        $key->setItemName('album');
        $key->setItemId($this->_id);
        $key->setEditPermission('edit_album');
        $key->setUrl($link);
        $key->setTitle($this->_label);
        $key->setSummary($this->_blurb0);
        $result = $key->save();

        $this->_key_id = $key->id;

        if ($update_album) {
            $this->commit();
        }
        return $key;
    }


    function action() {
        if (isset($_SESSION['PHPWS_AlbumManager']->message)) {
            javascript('alert', array('content'=>$_SESSION['PHPWS_AlbumManager']->message));
            unset($_SESSION['PHPWS_AlbumManager']->message);
        }

        if(isset($_REQUEST['PHPWS_Photo_id']) && is_numeric($_REQUEST['PHPWS_Photo_id'])) {
            if(!isset($this->photo) || ($this->photo->getId() != $_REQUEST['PHPWS_Photo_id'])) {
                $this->photo = new PHPWS_Photo($_REQUEST['PHPWS_Photo_id']);
            }
        } 

        // all permissions checked in function

        if(isset($_REQUEST['PHPWS_Album_op'])) {
            switch($_REQUEST['PHPWS_Album_op']) {
            case 'new':
                $this->_new();
                break;
        
            case 'edit':
                $title = dgettext('photoalbum', 'Edit Album');
                $content = $this->_edit();
                break;
        
            case 'save':
                $this->_save();
                break;
        
            case 'delete':
                  $this->_delete();
                break;
        
            case 'view':
                $title = dgettext('photoalbum', 'Photo Album') . ': ' . $this->getLabel();
                $content = $this->_view();
                break;
        
            case 'batch':
                if (!Current_User::allow('photoalbum', 'add_photo')) {
                    Current_User::disallow();
                }

                $title = dgettext('photoalbum', 'Batch Add Photos');
                $content = $this->_batchAdd();
                break;
        
            case 'batchSave':
                $this->_batchSave();
                break;
        
            case 'slideShow':
                $title = dgettext('photoalbum', 'Slide Show');
                $content = $this->_slideShow();
                break;
            }
        }

        if(isset($content)) {
            $template['TITLE'] = $title;
            $template['CONTENT'] = $content;
            Layout::add(PHPWS_Template::process($template, 'layout', 'box.tpl'));
        }
    }

    function AlbumRow($value)
    {
        $vars['PHPWS_Album_op'] = 'view';
        $vars['PHPWS_Album_id'] = $value['id'];
        $tpl['IMAGE'] = PHPWS_Text::moduleLink($value['image'], 'photoalbum', $vars);
        $tpl['LABEL'] = PHPWS_Text::moduleLink($value['label'], 'photoalbum', $vars);
        $tpl['UPDATED'] = strftime('%c', $value['updated']);
        return $tpl;
    }

    function getAlbumLinks()
    {
        if(Current_User::allow('photoalbum', 'add_photo')) {
            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=new">' . dgettext('photoalbum', 'New Photo') . '</a>';
            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=batch">' . dgettext('photoalbum', 'Batch Add') . '</a>';
        }
        
        $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_AlbumManager_op=list">' . dgettext('photoalbum', 'List Albums') . '</a>';
        
        $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=slideShow">' . 
            dgettext('photoalbum', 'Slide Show') . '</a>';
        
        if(Current_User::allow('photoalbum', 'edit_album')) {
            if (!isset($_REQUEST['missing_desc'])) {
                $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=view&amp;missing_desc=1">' . dgettext('photoalbum', 'Missing Descriptions') . '</a>';
            } else {
                $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=view">' . dgettext('photoalbum', 'All photos') . '</a>';
            }
            $links[] = '<a href="./index.php?module=photoalbum&amp;PHPWS_Album_op=edit">' . dgettext('photoalbum', 'Settings') . '</a>';
        }
        return $links;
    }
}

?>