<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @modified Eloi George
   */

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'Image.php');

if (!defined('RESIZE_IMAGE_USE_DUPLICATE')) {
    define('RESIZE_IMAGE_USE_DUPLICATE', true);
}

class FC_Image_Manager {
    var $folder      = null;
    var $image       = null;
    var $cabinet     = null;
    var $current     = 0;
    var $max_width   = 0;
    var $max_height  = 0;
    var $max_size    = 0;
    var $content     = null;
    /**
     * If true, manager will only show image folders for the current module
     */

    var $_noimage_max_width = 300;
    var $_noimage_max_height = 300;

    function FC_Image_Manager($image_id=0)
    {
        $this->loadImage($image_id);
        $this->loadSettings();
        $this->loadFolder();
    }

    /*
     * Expects 'dop' command to direct action.
     */
    function admin()
    {
        switch ($_REQUEST['iop']) {
        case 'delete_image':
            if (!Current_User::authorized('filecabinet', 'edit_folders', $this->image->folder_id, 'folder')) {
                Current_User::disallow();
            }
            $this->image->delete();
            PHPWS_Core::goBack();
            break;

        case 'post_image_upload':
            if (!Current_User::authorized('filecabinet', 'edit_folders', $this->image->folder_id, 'folder')) {
                Current_User::disallow();
            }

            $this->postImageUpload();
            break;

        case 'upload_image_form':
            $this->edit();
            break;

        case 'clip_image':
            if ($this->image->id) {
                Clipboard::copy($this->image->title, '[filecabinet:image:' . $this->image->id . ']');
            }
            PHPWS_Core::goBack();
            break;
        }
        return $this->content;
    }

    function setMaxSize($size)
    {
        $this->max_size = (int)$size;
    }

    function setMaxWidth($width)
    {
        $this->max_width = (int)$width;
    }

    function setNoimageMaxWidth($width)
    {
        $this->_noimage_max_width = (int)$width;
    }

    function setNoimageMaxHeight($height)
    {
        $this->_noimage_max_height = (int)$height;
    }

    function setMaxHeight($height)
    {
        $this->max_height = (int)$height;
    }

    /**
     * Upload image form
     */
    function edit()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        $form->addHidden('iop',      'post_image_upload');
        $form->addHidden('ms',        $this->max_size);
        $form->addHidden('mh',        $this->max_height);
        $form->addHidden('mw',        $this->max_width);
        $form->addHidden('folder_id', $this->folder->id);

        if ($this->image->id && Current_User::allow('filecabinet', 'edit_folders', $this->folder->id, 'folder', true)) {
            Cabinet::moveToForm($form, $this->folder);
        }

        // if 'im' is set, then we are inside the image manage interface
        // the post needs to be aware of that to respond correctly
        if (isset($_GET['im'])) {
            $form->addHidden('im', 1);
        }

        if ($this->image->id) {
            $form->addHidden('image_id', $this->image->id);
            $form->addTplTag('FORM_TITLE', dgettext('filecabinet', 'Update image'));
        } else {
            $form->addTplTag('FORM_TITLE', dgettext('filecabinet', 'Upload image'));
        }

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setMaxFileSize($this->max_size);

        $form->setLabel('file_name', dgettext('filecabinet', 'Image location'));

        $form->addText('title', $this->image->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));
        $form->setRequired('title');

        $form->addText('alt', $this->image->alt);
        $form->setSize('alt', 40);
        $form->setLabel('alt', dgettext('filecabinet', 'Alternate text'));

        $form->addTextArea('description', $this->image->description);
        $form->setRows('description', 8);
        $form->setCols('description', 45);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        $link_choice['none'] = dgettext('filecabinet', 'Do not link image');
        $link_choice['url']  = dgettext('filecabinet', 'Link image to web site');

        if ($this->image->folder_id) {
            $folder = new Folder($this->image->folder_id);
            if ($folder->public_folder) {
                $link_choice['folder'] = dgettext('filecabinet', 'Link to image folder');
            }
        }
       
        $form->addSelect('link', $link_choice);
        $form->setLabel('link', dgettext('filecabinet', 'Link image'));
        $form->setExtra('link', 'onchange=voila(this)');

        $form->addText('url');
        $form->setSize('url', 40, 255);
        $form->setLabel('url', dgettext('filecabinet', 'Image link url'));

        if ($this->folder->max_image_dimension && 
            ($this->folder->max_image_dimension < $this->max_width) ) {
            $max_width = $this->folder->max_image_dimension;
        } else {
            $max_width = $this->max_width;
        }

        $resizes = Cabinet::getResizes($max_width);

        if (!empty($resizes)) {
            $form->addSelect('resize', $resizes);
            $form->setLabel('resize', dgettext('filecabinet', 'Resize image if over'));
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
            $form->addTplTag('VISIBLE', 'none');
            $form->setValue('url', 'http://');
            break;

        case $this->image->url == 'parent':
            $form->setMatch('link', 'parent');
            $form->addTplTag('VISIBLE', 'none');
            break;

        case $this->image->url == 'folder':
            $form->setMatch('link', 'folder');
            $form->addTplTag('VISIBLE', 'none');
            break;

        default:
            $form->setMatch('link', 'url');
            $form->setValue('url', $this->image->url);
            $form->addTplTag('VISIBLE', 'table-row');
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
        $template['MAX_DIMENSION_LABEL']  = dgettext('filecabinet', 'Maximum image dimension');

        $template['MAX_DIMENSION']        = $this->max_width;

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

        $template['ERRORS'] = $this->image->printErrors();

        $this->content = PHPWS_Template::process($template, 'filecabinet', 'image_edit.tpl');
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
            $this->content = dgettext('filecabinet', 'An error occurred when trying to save your image.');
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

            case 'folder':
                $this->image->url = 'folder';
                break;

            default:
                $this->image->url = null;
            }

            if ($this->image->id) {
                $this->image->rotate(false);
            }

            $result = $this->image->save();
            $this->updateResizes($this->image);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }

            $this->image->moveToFolder();
            javascript('close_refresh');
        } else {
            $this->edit();
            return;
        }
    }


    function getSettings()
    {
        $vars['ms']        = $this->max_size;
        $vars['mw']        = $this->max_width;
        $vars['mh']        = $this->max_height;

        return $vars;
    }

    function loadSettings()
    {
        if (isset($_REQUEST['ms']) && $_REQUEST['ms'] > 1000) {
            $this->setMaxSize($_REQUEST['ms']);
        } else {
            $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_image_size'));
        }

        if (isset($_REQUEST['mh']) && $_REQUEST['mh'] > 50) {
            $this->setMaxHeight($_REQUEST['mh']);
        } else {
            $this->setMaxHeight(PHPWS_Settings::get('filecabinet', 'max_image_dimension'));
        }

        if (isset($_REQUEST['mw']) && $_REQUEST['mw'] > 50) {
            $this->setMaxWidth($_REQUEST['mw']);
        } else {
            $this->setMaxWidth(PHPWS_Settings::get('filecabinet', 'max_image_dimension'));
        }
    }

    function loadFolder($folder_id=0)
    {
        if (!$folder_id && isset($_REQUEST['folder_id'])) {
            $folder_id = &$_REQUEST['folder_id'];
        }

        $this->folder = new Folder($folder_id);
        if (!$this->folder->id) {
            $this->folder->ftype = IMAGE_FOLDER;
        }
    }

    function updateResizes($image)
    {
        $dir = $image->getResizePath();
        if (!is_dir($dir)) {
            return;
        }

        $images = PHPWS_File::readDirectory($dir, false, true);
        if (empty($images)) {
            return;
        }
        
        foreach ($images as $file_name) {
            if (!preg_match('/\d+x\d+\.\w{1,4}$/', $file_name)) {
                continue;
            }
            $last_dot = strrpos($file_name, '.');
            $base = substr($file_name, 0, $last_dot);
            $dimensions = explode('x', $base);
            $image->resize($dir . $file_name, $dimensions[0], $dimensions[1]);
        }
    }

}

?>