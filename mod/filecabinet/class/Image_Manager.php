<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'Image.php');

if (!defined('RESIZE_IMAGE_USE_DUPLICATE')) {
    define('RESIZE_IMAGE_USE_DUPLICATE', true);
}

if (!defined('FC_MAX_WIDTH_DISPLAY')) {
    define('FC_MAX_WIDTH_DISPLAY', 300);
}

if (!defined('FC_MAX_HEIGHT_DISPLAY')) {
    define('FC_MAX_HEIGHT_DISPLAY', 300);
}

class FC_Image_Manager {
    var $image      = null;
    var $itemname   = null;
    var $cabinet    = null;
    var $current    = 0;
    var $max_width  = 0;
    var $max_height = 0;
    var $max_size   = 0;

    function FC_Image_Manager($image_id=0)
    {
        $this->loadImage($image_id);
        $this->loadSettings();
    }

    // backward compatibility
    function setModule($foo)
    {
    }

    function setMaxSize($size)
    {
        $this->max_size = (int)$size;
    }

    function setItemName($itemname)
    {
        $this->itemname = $itemname;
    }

    function setMaxWidth($width)
    {
        $this->max_width = (int)$width;
    }


    function setMaxHeight($height)
    {
        $this->max_height = (int)$height;
    }


    /**
     * shows image choices from pop up menu
     */
    function showImages($folder, $image_id=0)
    {
        if (!$folder->id) {
            return null;
        } else {
            if ($folder->loadFiles()) {
                $js_vars['itemname']  = $this->itemname;
                
                foreach ($folder->_files as $image) {
                    if (!$image->parent_id || $image->id == $image_id) {
                        $tpl['DISPLAY'] = 'inline';
                        $tpl['STATUS'] = 'parent-image';
                    } else {
                        $tpl['DISPLAY'] = 'none';
                        $tpl['STATUS'] = 'child-image';
                    }

                    if ($image->id == $image_id) {
                        $tpl['HIGHLIGHT'] = 'background-color : #D4D4D4;';
                    } else {
                        $tpl['HIGHLIGHT'] = null;
                    }

                    $width = & $image->width;
                    $height = & $image->height;
                    $image_url = $image->getPath();

                    if ( ($this->max_width < $image->width) || ($this->max_height < $image->height) ) {
                        $tpl['THUMBNAIL'] = sprintf('<a href="#" onclick="oversized(%s, \'%s\', \'%s\', %s, %s); return false">%s</a>',
                                                    $image->id, $image_url, addslashes($image->title), $image->width, $image->height, $image->getThumbnail());
                    } else {

                        $tpl['THUMBNAIL'] = sprintf('<a href="#" onclick="pick_image(%s, \'%s\', \'%s\', %s, %s); return false">%s</a>',
                                                    $image->id, $image_url, addslashes($image->title), $image->width, $image->height, $image->getThumbnail());
                    }

                    $tpl['EDIT'] = $image->editLink(true);

                    $tpl['TITLE']     = $image->title;
                    $tpl['VIEW']      = $image->getJSView();
                    $tpl['ID']        = $image->id;
                    $tpl['WIDTH']     = $image->width;
                    $tpl['HEIGHT']    = $image->height;
                    $template['thumbnail-list'][] = $tpl;
                }

                $content =  PHPWS_Template::process($template, 'filecabinet', 'manager/pick.tpl');
            } else {
                $content = dgettext('filecabinet', 'Folder empty.');
            }
        }
        
        return $content;
    }

    /**
     * Upload image form
     */
    function edit()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        $form->addHidden('aop',      'post_image_upload');
        $form->addHidden('ms',        $this->max_size);
        $form->addHidden('mh',        $this->max_height);
        $form->addHidden('mw',        $this->max_width);
        $form->addHidden('folder_id', $this->cabinet->folder->id);

        // if 'im' is set, then we are inside the image manage interface
        // the post needs to be aware of that to respond correctly
        if (isset($_GET['im'])) {
            $form->addHidden('im', 1);
        }

        if ($this->image->id) {
            $form->addHidden('image_id', $this->image->id);
            $this->cabinet->title = dgettext('filecabinet', 'Update image');
        } else {
            $this->cabinet->title = dgettext('filecabinet', 'Upload image');
        }

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setMaxFileSize($this->max_size);

        $form->setLabel('file_name', dgettext('filecabinet', 'Image location'));

        $form->addText('title', $this->image->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));

        $form->addText('alt', $this->image->alt);
        $form->setSize('alt', 40);
        $form->setLabel('alt', dgettext('filecabinet', 'Alternate text'));

        $form->addTextArea('description', $this->image->description);
        $form->setRows('description', 8);
        $form->setCols('description', 45);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        $link_choice['none'] = dgettext('filecabinet', 'Do not link image');
        $link_choice['url']  = dgettext('filecabinet', 'Link image to web site');

        if ($this->image->parent_id) {
            $link_choice['parent'] = dgettext('filecabinet', 'Link image to original, full sized image');
        }
       
        $form->addSelect('link', $link_choice);
        $form->setLabel('link', dgettext('filecabinet', 'Link image'));
        $form->setExtra('link', 'onchange=voila(this)');

        $form->addText('url');
        $form->setSize('url', 50, 255);
        $form->setLabel('url', dgettext('filecabinet', 'Image link url'));


        if ($this->max_width >= 1280 && $this->max_height >= 1024) {
            $resizes['1280x1024'] = '1280x1024';
        }

        if ($this->max_width >= 1280 && $this->max_height >= 960) {
            $resizes['1280x960'] = '1280x960';
        }

        if ($this->max_width >= 1024 && $this->max_height >= 768) {
            $resizes['1024x768'] = '1024x768';
        }

        if ($this->max_width >= 800 && $this->max_height >= 600) {
            $resizes['800x600']  = '800x600';
        }

        if ($this->max_width >= 640 && $this->max_height >= 480) {
            $resizes['640x480']  = '640x480';
        }

        if (isset($resizes)) {
            $temp = array_reverse($resizes, true);
            $id = $this->max_width . 'x' . $this->max_height;
            $temp[$id] = sprintf(dgettext('filecabinet', '%sx%s (limit)'), $this->max_width, $this->max_height);
            $resizes = array_reverse($temp, true);
        }

        if (!empty($resizes)) {
            $form->addSelect('resize', $resizes);
            $form->setLabel('resize', dgettext('filecabinet', 'Scale down'));
        }

        $rotate['none']  = dgettext('filecabinet', 'None');
        $rotate['90cw']  = dgettext('filecabinet', '90 degrees clockwise');
        $rotate['90ccw'] = dgettext('filecabinet', '90 degrees counter clockwise');
        $rotate['180']   = dgettext('filecabinet', '180 degrees');

        $form->addSelect('rotate', $rotate);
        $form->setLabel('rotate', dgettext('filecabinet', 'Rotate image'));


        switch (1) {
        case empty($this->image->url):
            $form->setMatch('link', 'none');
            $form->addTplTag('VISIBLE', 'hidden');
            $form->setValue('url', 'http://');
            break;

        case $this->image->url == 'parent':
            $form->setMatch('link', 'parent');
            $form->addTplTag('VISIBLE', 'hidden');
            break;

        default:
            $form->setMatch('link', 'url');
            $form->setValue('url', $this->image->url);
            $form->addTplTag('VISIBLE', 'visible');
            break;
        }


        if (!empty($this->image->id)) {
            $form->addSubmit(dgettext('filecabinet', 'Update'));
        } else {
            $form->addSubmit(dgettext('filecabinet', 'Upload'));
        }

        $template = $form->getTemplate();

        $template['CANCEL'] = sprintf('<input type="button" value="%s" onclick="javascript:window.close()" />', dgettext('filecabinet', 'Cancel'));

        if ($this->image->id) {
            $template['CURRENT_IMAGE_LABEL'] = dgettext('filecabinet', 'Current image');
            $template['CURRENT_IMAGE']       = $this->image->getJSView(TRUE);
            $template['SIZE']                = sprintf('%s x %s', $this->image->width, $this->image->height);
        }
        $template['MAX_SIZE_LABEL']   = dgettext('filecabinet', 'Maximum file size');
        $template['MAX_WIDTH_LABEL']  = dgettext('filecabinet', 'Maximum width');
        $template['MAX_HEIGHT_LABEL'] = dgettext('filecabinet', 'Maximum height');

        $template['MAX_WIDTH']        = $this->max_width;
        $template['MAX_HEIGHT']       = $this->max_height;

        $sys_size = str_replace('M', '', ini_get('upload_max_filesize'));
        $sys_size = $sys_size * 1000000;
        $form_max = $form->max_file_size;

        if ($form_max < $sys_size && $form_max < $this->max_size) {
            $max_size = & $form_max;
        } elseif ($sys_size < $form_max && $sys_size < $this->max_size) {
            $max_size = & $sys_size;
        } else {
            $max_size = & $this->max_size;
        }

        if ($max_size >= 1000000) {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%dMB (%d bytes)'), floor($max_size / 1000000), $max_size);
        } elseif ($max_size >= 1000) {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%dKB (%d bytes)'), floor($max_size / 1000), $max_size);
        } else {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes'), $max_size);
        }


        $this->cabinet->content = PHPWS_Template::process($template, 'filecabinet', 'image_edit.tpl');
    }


    function loadImage($image_id=0)
    {
        if (!$image_id && isset($_REQUEST['image_id'])) {
            $image_id = $_REQUEST['image_id'];
        }

        $this->image = new PHPWS_Image($image_id);
    }

    /**
     * From Cabinet::admin.
     * Error checks and posts the image upload
     */
    function postImageUpload()
    {
        // importPost in File_Common
        $result = $this->image->importPost('file_name');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $vars['timeout'] = '3';
            $vars['refresh'] = 0;
            $this->cabinet->content = dgettext('filecabinet', 'An error occurred when trying to save your image.');
            javascript('close_refresh', $vars);
            return;
        } elseif ($result) {
            switch ($_POST['link']) {
            case 'url':
                if (empty($_POST['url'])) {
                    $this->image->url = null;
                } else {
                    $this->image->url = $_POST['url'];
                }
                $this->url = $_POST['link'];
                break;

            case 'parent':
                if ($this->image->parent_id) {
                    $this->image->url = 'parent';
                } else {
                    $this->image->url = null;
                }
                break;

            default:
                $this->image->url = null;
            }

            $result = $this->image->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            if (!isset($_POST['im'])) {
                javascript('close_refresh');
            } else {
                javascript('modules/filecabinet/refresh_manager', array('image_id'=>$this->image->id));
            }
        } else {
            $this->cabinet->message = $this->image->printErrors();
            $this->edit();
            return;
        }
    }

    function get()
    {
        if (!Current_User::allow('filecabinet')) {
            return $this->image->getTag();
        }

        if ($this->image->id) {
            $label = $this->image->getTag('image-manager-' . $this->itemname, false);
        } else {
            $label = $this->noImage();
        }

        $link_vars = $this->getSettings();
        $link_vars['aop']    = 'edit_image';
        $link_vars['current']   = $this->image->id;

        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', $link_vars);
        $vars['width']   = 700;
        $vars['height']  = 600;
        $vars['label']   = $label;

        $tpl['IMAGE'] = javascript('open_window', $vars);
        
        $tpl['HIDDEN'] = sprintf('<input type="hidden" id="%s" name="%s" value="%s" />', $this->itemname . '_hidden_value', $this->itemname, $this->image->id);
        $tpl['ITEMNAME'] = $this->itemname;
        $tpl['CLEAR_IMAGE'] = $this->getClearLink();
        
        return PHPWS_Template::process($tpl, 'filecabinet', 'manager/javascript.tpl');
    }

    function getSettings()
    {
        $vars['itemname']  = $this->itemname;
        $vars['ms']        = $this->max_size;
        $vars['mw']        = $this->max_width;
        $vars['mh']        = $this->max_height;

        return $vars;
    }

    function noImage()
    {
        $no_image = dgettext('filecabinet', 'No image');
        if ($this->max_width > FC_MAX_WIDTH_DISPLAY) {
            $width = FC_MAX_WIDTH_DISPLAY;
        } else {
            $width = & $this->max_width;
        }

        if ($this->max_height > FC_MAX_HEIGHT_DISPLAY) {
            $height = FC_MAX_HEIGHT_DISPLAY;
        } else {
            $height = & $this->max_height;
        }

        return sprintf('<img src="%s" width="%s" height="%s" title="%s" alt="%s" id="image-manager-%s" />',
                       FC_NONE_IMAGE_SRC, $width, 
                       $height, $no_image, $no_image, $this->itemname);
    }

    function getClearLink()
    {
        $js_vars['src']      = FC_NONE_IMAGE_SRC;

        if ($this->max_width > FC_MAX_WIDTH_DISPLAY) {
            $js_vars['width'] = FC_MAX_WIDTH_DISPLAY;
        } else {
            $js_vars['width'] = & $this->max_width;
        }

        if ($this->max_height > FC_MAX_HEIGHT_DISPLAY) {
            $js_vars['height'] = FC_MAX_HEIGHT_DISPLAY;
        } else {
            $js_vars['height'] = & $this->max_height;
        }

        $js_vars['alt']      = 
        $js_vars['title']    = $js_vars['alt'] = dgettext('filecabinet', 'No image');
        $js_vars['itemname'] = $this->itemname;
        $js_vars['label']    = dgettext('filecabinet', 'Clear image');
        $js_vars['id'] = $this->itemname . '-clear';
        return javascript('modules/filecabinet/clear_image', $js_vars);
    }

    function loadSettings()
    {
        if (isset($_REQUEST['itemname'])) {
            $this->setItemname($_REQUEST['itemname']);
        }

        if (isset($_REQUEST['ms']) && $_REQUEST['ms'] > 1000) {
            $this->setMaxSize($_REQUEST['ms']);
        } else {
            $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_image_size'));
        }

        if (isset($_REQUEST['mh']) && $_REQUEST['mh'] > 50) {
            $this->setMaxHeight($_REQUEST['mh']);
        } else {
            $this->setMaxHeight(PHPWS_Settings::get('filecabinet', 'max_image_height'));
        }

        if (isset($_REQUEST['mw']) && $_REQUEST['mw'] > 50) {
            $this->setMaxWidth($_REQUEST['mw']);
        } else {
            $this->setMaxWidth(PHPWS_Settings::get('filecabinet', 'max_image_width'));
        }
    }

    /**
     * This is the pop up menu where a user can pick an image.
     */
    function editImage()
    {
        if (isset($_GET['current'])) {
            $image = new PHPWS_Image($_GET['current']);
            $folder = new Folder($image->folder_id);
        }

        Layout::addStyle('filecabinet');
        $this->cabinet->title = dgettext('filecabinet', 'Choose an image folder');

        // Needed for image view popups
        javascript('open_window');
        // don't delete above

        $js['itemname'] = $this->itemname;
        $js['failure_message'] = addslashes(dgettext('filecabinet', 'Unable to resize image.'));
        $js['confirmation'] = sprintf(dgettext('filecabinet', 'This image is larger than the %s x %s limit. Do you want to resize the image to fit?'),
                                      $this->max_width,
                                      $this->max_height);
        $js['authkey'] = Current_User::getAuthKey();

        $js['maxwidth'] = $this->max_width;
        $js['maxheight'] = $this->max_height;
        $js['maxsize'] = $this->max_size;

        javascript('modules/filecabinet/pick_image', $js);

        $db = new PHPWS_DB('folders');
        $db->addWhere('ftype', IMAGE_FOLDER);
        $db->addOrder('title');
        $folders = $db->getObjects('Folder');

        if (!empty($folders)) {
            foreach ($folders as $fldr) {
                $tpl['listrows'][] = $fldr->imageTags($this->max_width, $this->max_height);
            }
        }

        if (Current_User::allow('filecabinet', 'edit_folders')) {
            $address = PHPWS_Text::linkAddress('filecabinet', array('aop'=>'add_folder', 'ftype'=>IMAGE_FOLDER), true);
            $folder_window = sprintf("javascript:open_window('%s', %s, %s, 'new_folder'); return false", $address, 370, 420);
            $tpl['ADD_FOLDER'] = sprintf('<input id="add-folder" type="button" name="add_folder" value="%s" onclick="%s" />', dgettext('filecabinet', 'Add folder'), $folder_window);
        }

        if (Current_User::allow('filecabinet', 'edit_folders', $folder->id)) {
            $address = PHPWS_Text::linkAddress('filecabinet', array('aop'=>'upload_image_form', 'im'=>1, 'folder_id'=>$folder->id), true);
            $image_window = sprintf("javascript:open_window('%s', %s, %s, 'new_image'); return false", $address, 600, 550);
            $image_button = sprintf('<input id="add-image" type="button" name="add_image" value="%s" onclick="%s" />', dgettext('filecabinet', 'Add image'), $image_window);
            $tpl['ADD_IMAGE'] = $image_button;
        }

        $tpl['CLOSE_IMAGE'] = sprintf('<input type="button" onclick="javascript:window.close()" value="%s" id="close-image" />', dgettext('filecabinet', 'Close'));

        if ($folder->id) {
            $show_images = $this->showImages($folder, $image->id);
            if (!empty($show_images)) {
                $tpl['IMAGE_LIST'] = &$show_images;
                $tpl['IMG_DISPLAY'] = 'visible';
            } else {
                $tpl['IMAGE_LIST'] = dgettext('filecabinet', 'Bad folder id.');
            }
        } else {
            $tpl['IMG_DISPLAY'] = 'hidden';
            if (empty($folders)) {
                $tpl['IMAGE_LIST'] = dgettext('filecabinet', 'Please create a new folder.');
            } else {
                $tpl['IMAGE_LIST'] = dgettext('filecabinet', 'Choose a folder.');
            }
        }

        $tpl['ORIGINAL'] = '<input id="original-only" type="checkbox" name="original_only" value="1" checked="checked" onclick="source_trigger(this)" />';
        $tpl['ORIGINAL_LABEL'] = dgettext('pagesmith', 'Show source images only');

        $this->cabinet->content = PHPWS_Template::process($tpl, 'filecabinet', 'image_folders.tpl');
    }


    /**
     * Resizes an image outside a modules defined boundaries.
     *
     * If the resized image already exists, we use the copy.
     * Although the original could have changed, we don't delete the resized and replace it.
     * The resize may be in use somewhere else, so we don't want the image to change.
     * Also, if the image changes the thumbnail would have to change as well.
     */
    function resizeImage()
    {
        $directory = $this->image->file_directory;
        $image_name = $this->image->file_name;

        $a_image = explode('.', $image_name);
        $ext = array_pop($a_image);
        $image_name = sprintf('%s_%sx%s.%s', implode('.', $a_image), $this->max_width, $this->max_height, $ext);

        $copy_dir = $directory . $image_name;

        if (is_file($copy_dir)) {
            if (RESIZE_IMAGE_USE_DUPLICATE) {
                // use duplicate instead
                $image = new PHPWS_Image;
                $db = new PHPWS_DB('images');
                $db->addWhere('folder_id', $this->image->folder_id);
                $db->addWhere('file_name', $image_name);
                if ($db->loadObject($image)) {
                    header('Content-type: text/xml');
                    echo $image->xmlFormat();
                    exit();
                } else {
                    // image not in system, delete it and move on
                    @unlink($copy_dir);
                }
            } else {
                // don't use duplicate, make a new file
                for ($i=2; $i<50; $i++) {
                    $image_name = sprintf('%s_%sx%s_v%s.%s', implode('.', $a_image), $this->max_width, $this->max_height, $i, $ext);
                    $copy_dir = $directory . $image_name;
                    if (!is_file($copy_dir)) {
                        // found a replacement, breaking loop
                        $i = 50;
                    }
                }
            }
        }

        if (!$this->max_width || !$this->max_height) {
            return null;
        }

        if ($this->image->resize($copy_dir, $this->max_width, $this->max_height)) {
            $image = new PHPWS_Image;
            $image->file_name      = $image_name;
            $image->file_directory = $directory;
            $image->folder_id      = $this->image->folder_id;
            $image->file_type      = $this->image->file_type;
            $image->title          = $this->image->title;
            $image->description    = $this->image->description;
            $image->alt            = $this->image->alt;
            $image->parent_id      = $this->image->id;
            if (PHPWS_Settings::get('filecabinet', 'auto_link_parent')) {
                $image->url        = 'parent';
            }
            $image->loadDimensions();
            $result = $image->save();
            $result = null;
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            } else {
                header('Content-type: text/xml');
                echo $image->xmlFormat();
            }
        }

        exit();
    }
}

?>