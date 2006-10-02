<?php

PHPWS_Core::requireConfig('filecabinet');
PHPWS_Core::initModClass('filecabinet', 'Image.php');

class FC_Image_Manager {
    var $image          = NULL;
    var $mod_title      = NULL;
    var $itemname       = NULL;
    var $thumbnail      = NULL;
    var $tn_width       = MAX_TN_IMAGE_WIDTH;
    var $tn_height      = MAX_TN_IMAGE_HEIGHT;
    var $_error         = NULL;

    function FC_Image_Manager($image_id=NULL)
    {
        if (empty($image_id)) {
            $this->image = & new PHPWS_Image;
            $this->thumbnail = & new PHPWS_Image;
            return;
        }
     
        $this->loadImage($image_id);
    }

    function loadImage($image_id)
    {
        if (!$image_id) {
            $this->image = & new PHPWS_Image;
            $this->thumbnail = & new PHPWS_Image;
            return;
        }

        $this->image = & new PHPWS_Image((int)$image_id);
        $this->loadThumbnail();
    }

    function setModule($mod_title)
    {
        $this->mod_title = $mod_title;
    }

    function loadThumbnail()
    {
        $this->thumbnail = & new PHPWS_Image;
        $db = & new PHPWS_DB('images');
        $db->addWhere('thumbnail_source', $this->image->id);
        $result = $db->loadObject($this->thumbnail);
    }

    function setDirectory($directory)
    {
        $this->image->directory = $directory;
    }

    function setImageId($image_id)
    {
        $this->image = & new PHPWS_Image($image_id);
    }

    function setItemName($itemname)
    {
        $this->itemname = $itemname;
    }

    function setMaxWidth($width)
    {
        $this->image->setMaxWidth($width);
    }


    function setMaxHeight($height)
    {
        $this->image->setMaxHeight($height);
    }

    function setMaxSize($size)
    {
        $this->image->setMaxSize($size);
    }

    function setTNWidth($width)
    {
        $this->tn_width = (int)$width;
    }

    function setTNHeight($height)
    {
        $this->tn_height = (int)$height;
    }

    function get()
    {
        if (javascriptEnabled()) {
            return $this->javascript();
        } else {
            return $this->normal();
        }
    }

    function normal()
    {
        return NULL;
    }

    function noImage()
    {
        return sprintf('<img src="%s" width="%s" height="%s" title="%s" alt="%s" />',
                             FC_NONE_IMAGE_SRC, MAX_TN_IMAGE_WIDTH, 
                             MAX_TN_IMAGE_HEIGHT, _('No image'), _('No image')
                             );
    }

    function javascript()
    {
        if ($this->image->id) {
            $label = $this->thumbnail->getTag();
        } else {
            $label = $this->noImage();
        }

        $link_vars = $this->getSettings();
        $link_vars['action']    = 'edit_image';
        $link_vars['current']   = $this->image->id;
   
        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', $link_vars);
        $vars['width']   = FC_MANAGER_WIDTH;
        $vars['height']  = FC_MANAGER_HEIGHT;
        $vars['label']   = $label;

        $tpl['IMAGE'] = javascript('open_window', $vars);
        $tpl['HIDDEN'] = sprintf('<input type="hidden" name="%s" value="%s" />', $this->itemname, $this->image->id);
        $tpl['ITEMNAME'] = $this->itemname;
        $tpl['CLEAR_IMAGE'] = $this->getClearLink();
        return PHPWS_Template::process($tpl, 'filecabinet', 'manager/javascript.tpl');
    }

    function getRowTags()
    {
        $vars['image_id'] = $this->image->id;
        $vars['action'] = 'editImage';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'filecabinet', $vars);
        $vars['action'] = 'deleteImage';
        $links[] = PHPWS_Text::secureLink(_('Delete'), 'filecabinet', $vars);
        $vars['action'] = 'copyImage';
        $links[] = PHPWS_Text::moduleLink(_('Copy'), 'filecabinet', $vars);

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function getClearLink()
    {
        $js_vars['src']      = FC_NONE_IMAGE_SRC;
        $js_vars['width']    = MAX_TN_IMAGE_WIDTH;
        $js_vars['height']   = MAX_TN_IMAGE_HEIGHT;
        $js_vars['title']    = $js_vars['alt'] = _('No image');
        $js_vars['itemname'] = $this->itemname;
        $js_vars['label']    = _('Clear image');

        return javascript('modules/filecabinet/clear_image', $js_vars);
    }



    function getThumbnailLink()
    {
        $link_vars['action']    = 'change_thumbnail';
        $link_vars['mod_title'] = $this->mod_title;
        $link_vars['itemname']  = $this->itemname;
        $link_vars['current']   = $this->image->id;
   
        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', $link_vars);
        $vars['width']   = FC_UPLOAD_WIDTH;
        $vars['height']  = FC_UPLOAD_HEIGHT;
        $vars['label']   = _('Change Thumbnail');
        return javascript('open_window', $vars);
    }


    function getViewLink($bare_link=TRUE)
    {
        if ($bare_link) {
            $vars['label']   = $this->thumbnail->getTag();
            $vars['address'] = $this->image->getFullDirectory();
            $vars['width']   = $this->image->width + 20;
            $vars['height']  = $this->image->height + 20;

            return javascript('open_window', $vars);
        } else {
            $vars['address'] = 'index.php?module=filecabinet&amp;action=view_image&amp;image_id='
                . $this->image->id;
        }

        $vars['width']   = $this->image->getWidth() + FC_VIEW_MARGIN_WIDTH;
        $vars['height']  = $this->image->getHeight() + FC_VIEW_MARGIN_HEIGHT;
        $vars['label']   = $this->thumbnail->getTag();
        return javascript('open_window', $vars);
    }

    function noThumbnail()
    {
        return sprintf('<img title="%s" alt="%s" src="images/mod/filecabinet/no_thumbnail.png" />',
                       _('No thumbnail'), _('No thumbnail'));
    }

    /**
     * Bare upload link to pull up image manager
     */
    function getUploadLink()
    {
        $link_vars['action']    = 'edit_image';
        $vars['address'] = $this->getLinkAddress('filecabinet', $link_vars);
        $vars['width']   = FC_UPLOAD_WIDTH;
        $vars['height']  = FC_UPLOAD_HEIGHT;
        $vars['label']   = _('Upload image'); 

        return javascript('open_window', $vars);
    }

   
    function getLinkAddress($use_image=TRUE)
    {
        $link_vars = $this->getSettings();
        $link_vars['action']    = 'upload_form';

        if (isset($this->image->directory)) {
            $link_vars['directory'] = urlencode($this->image->directory);
        }
        return PHPWS_Text::linkAddress('filecabinet', $link_vars);
    }

    function errorPost() {
        exit(_('An error occurred when trying to save your image.'));
    }

    function postPick()
    {
        $js_vars['mod_title'] = $this->mod_title;
        $js_vars['itemname']  = $this->itemname;
        $js_vars['src']       = $this->thumbnail->getPath();
        $js_vars['width']     = $this->thumbnail->width;
        $js_vars['height']    = $this->thumbnail->height;
        $js_vars['title']     = addslashes($this->thumbnail->title);
        $js_vars['alt']       = addslashes($this->thumbnail->getAlt());
        $js_vars['image_id']  = $this->image->id;
        
        echo javascript('modules/filecabinet/post_file', $js_vars);
        exit();
    }

    function postUpload()
    {
        $link_vars['action']    = 'edit_image';
        $link_vars['current']   = $this->image->id;
        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', $link_vars);
        javascript('close_refresh', $vars);
        Layout::nakedDisplay();
    }


    function postImage()
    {
        if (!$this->image->importPost('file_name')) {
            return FALSE;
        } else {
            $result = $this->image->save();
            return $result;
        }
    }


    function edit()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        $session_index = md5($this->mod_title . $this->itemname);

        $img_directories = Cabinet_Action::getImgDirectories();

        $form->addHidden('action',    'post_image_close');
        $form->addHidden('ms',        $this->image->_max_size);
        $form->addHidden('mh',        $this->image->_max_height);
        $form->addHidden('mw',        $this->image->_max_width);
        $form->addHidden('tnw',       $this->tn_width);
        $form->addHidden('tnh',       $this->tn_height);
        if ($this->image->id) {
            $form->addHidden('image_id', $this->image->id);
        }


        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setMaxFileSize($this->image->_max_size);
        
        $form->setLabel('file_name', _('Image location'));

        $form->addText('title', $this->image->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addText('alt', $this->image->alt);
        $form->setSize('alt', 40);
        $form->setLabel('alt', _('Alternate text'));

        $form->addTextArea('description', $this->image->description);
        $form->setLabel('description', _('Description'));

        $form->addSelect('directory', $img_directories);

        if ($this->image->file_directory) {
            $form->setMatch('directory', $this->image->file_directory);
        } elseif (isset($_REQUEST['mod_title'])) {
            $image_directory = PHPWS_HOME_DIR . 'images/' . $_REQUEST['mod_title'] . '/';
            $form->setMatch('directory', $image_directory);
        }
        $form->setLabel('directory', _('Save directory'));


        if (isset($this->image->id)) {
            $form->addSubmit(_('Update'));
        } else {
            $form->addSubmit(_('Upload'));
        }

        $template = $form->getTemplate();

        $template['CANCEL'] = sprintf('<input type="button" value="%s" onclick="javascript:window.close()" />', _('Cancel'));

        $errors = $this->image->getErrors();

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $message[] = array('ERROR' => $err->getMessage());
            }
            $template['errors'] = $message;
        }

        if ($this->image->id) {
            $template['CURRENT_IMAGE_LABEL'] = _('Current image');
            $template['CURRENT_IMAGE']       = $this->image->getJSView(TRUE);
        }
        $template['MAX_SIZE_LABEL']   = _('Maximum file size');
        $template['MAX_WIDTH_LABEL']  = _('Maximum width');
        $template['MAX_HEIGHT_LABEL'] = _('Maximum height');
        $template['MAX_SIZE']         = $this->image->_max_size;
        $template['MAX_WIDTH']        = $this->image->_max_width;
        $template['MAX_HEIGHT']       = $this->image->_max_height;

        return PHPWS_Template::process($template, 'filecabinet', 'image_edit.tpl');
    }

    function editImage($clear_opener=FALSE)
    {
        if ($clear_opener) {
            $this->getClearLink();
            javascript('onload', array('function' => "clear_image('" . $this->itemname . "')"));
        }

        $db = & new PHPWS_DB('images');
        $db->addWhere('thumbnail_source', 0, '>');
        $db->setIndexBy('thumbnail_source');
        $thumbnails = $db->getObjects('PHPWS_Image');

        $db->reset();
        $db->addWhere('thumbnail_source', 0, '=', 'and', 1);
        $db->addWhere('thumbnail_source', 'images.id', '=', 'or', 1);
        $db->setIndexBy('id');
        $source_images = $db->getObjects('PHPWS_Image');

        if (empty($thumbnails)) {
            $tpl['MESSAGE'] = _('No images found.');
        } else {
            foreach ($thumbnails as $tn) {
                $source_img = & $source_images[$tn->thumbnail_source];
                $desc_total = strlen($source_img->description);
                $added_height = floor(($desc_total / 10 ) + 40);

                $tpl['thumbnail-list'][] = array('THUMBNAIL' => $tn->getPath(),
                                                 'TN_ID'     => $tn->id,
                                                 'ID'        => $tn->thumbnail_source,
                                                 'ITEMNAME'  => $this->itemname,
                                                 'MOD_TITLE' => $this->mod_title,
                                                 'WIDTH'     => $source_img->width,
                                                 'HEIGHT'    => $source_img->height,
                                                 'POP_WIDTH'     => $source_img->width + 40,
                                                 'POP_HEIGHT'    => $source_img->height + $added_height,
                                                 'VIEW'      => sprintf('<img src="images/mod/filecabinet/viewmag+.png" width="16" height="16" title="%s" />', _('View full image'))
                                                 );
            }
        }

        if ($this->image->id) {
            $js_vars['current_image'] = $this->image->getTag();
        }

        $js_vars = $this->getSettings();
        $js_vars['title_label']   = _('Title');
        $js_vars['desc_label']    = _('Description');
        $js_vars['upload_width']  = FC_UPLOAD_WIDTH;
        $js_vars['upload_height']  = FC_UPLOAD_HEIGHT;
        $js_vars['authkey'] = Current_User::getAuthKey();
        $js_vars['confirm_delete'] = _('Are you sure you want to permanently delete this image?');

        $js_vars['image_warning'] = _('Choose a image first.');
        $js_vars['action']    = 'post_pick';
        $js_vars['mod_title'] = $this->mod_title;
        $js_vars['itemname']  = $this->itemname;

        javascript('modules/filecabinet/pick_image', $js_vars);
        $tpl['UPLOAD_LINK'] = $this->getLinkAddress();
        $tpl['UPLOAD'] = _('Upload');
        $tpl['OK'] = _('Ok');
        $tpl['CANCEL'] = _('Cancel');
        $tpl['TITLE'] = _('Image Browser');
        $tpl['SUBHEIGHT'] = floor(FC_MANAGER_HEIGHT * .95);
        $tpl['DELETE'] = _('Delete');
        $content = PHPWS_Template::process($tpl, 'filecabinet', 'manager/pick.tpl');
        return $content;
    }


    function loadReqValues()
    {
        if (isset($_REQUEST['directory'])) {
            $this->setDirectory(urldecode($_REQUEST['directory']));
        }

        if (isset($_REQUEST['itemname'])) {
            $this->setItemname($_REQUEST['itemname']);
        }

        if (isset($_REQUEST['mod_title'])) {
            $this->setModule($_REQUEST['mod_title']);
        }
       

        if (isset($_REQUEST['ms'])) {
            $this->setMaxSize($_REQUEST['ms']);
        }

        if (isset($_REQUEST['mh'])) {
            $this->setMaxHeight($_REQUEST['mh']);
        }

        if (isset($_REQUEST['mw'])) {
            $this->setMaxWidth($_REQUEST['mw']);
        }

        if (isset($_REQUEST['tnw'])) {
            $this->setTNWidth($_REQUEST['tnw']);
        }

        if (isset($_REQUEST['tnh'])) {
            $this->setTNHeight($_REQUEST['tnh']);
        }
    }

    /**
     * This function is an altered copy of PHPWS_File::makeThumbnail
     */ 
    function createThumbnail()
    {
        PHPWS_Core::initCoreClass('File.php');
        $src_img   = &$this->image;
        $thumbnail = &$this->thumbnail;

        $thumbnail->file_directory   = $src_img->file_directory;
        $thumbnail->file_type             = $src_img->file_type;
        $thumbnail->title            = $src_img->title;
        $thumbnail->description      = $src_img->description;
        $thumbnail->thumbnail_source = $src_img->id;

        if ( ($src_img->width < $this->tn_width) &&
             ($src_img->height < $this->tn_height) ) {
            $src_img->thumbnail_source = $src_img->id;
            return $src_img->save(TRUE, FALSE);
        } else {
            if($src_img->width > $src_img->height) {
                $scale = $this->tn_width / $src_img->width;
            } else{
                $scale = $this->tn_height / $src_img->height;
            }
        }


        $thumbnail->width  = round($scale * $src_img->width);
        $thumbnail->height = round($scale * $src_img->height);

        if(PHPWS_File::chkgd2()) {
            $thumbnailImage = ImageCreateTrueColor($thumbnail->width, $thumbnail->height);
            imageAlphaBlending($thumbnailImage, false);
            imageSaveAlpha($thumbnailImage, true);
        } else {
            $thumbnailImage = ImageCreate($thumbnail->width, $thumbnail->height);
        }

        if ($src_img->file_type == 'image/gif') {
            $fullImage = imagecreatefromgif($src_img->getFullDirectory());
        } elseif ($src_img->file_type == 'image/jpeg') {
            $fullImage = ImageCreateFromJPEG($src_img->getFullDirectory());
        } elseif ($src_img->file_type == 'image/png') {
            $fullImage = ImageCreateFromPNG($src_img->getFullDirectory());
        } else {
            // really?
            return FALSE;
        }

        ImageCopyResized($thumbnailImage, $fullImage, 0, 0, 0, 0,
                         $thumbnail->width, $thumbnail->height, ImageSX($fullImage), ImageSY($fullImage));
        ImageDestroy($fullImage);

        $thumbnailFileName = explode('.', $src_img->file_name);

        if ($src_img->file_type == 'image/gif') {
            $thumbnail->setFilename($thumbnailFileName[0] . '_tn.gif');
            imagegif($thumbnailImage, $thumbnail->getFullDirectory());
        } elseif ($src_img->file_type == 'image/jpeg') {
            $thumbnail->setFilename($thumbnailFileName[0] . '_tn.jpg');
            imagejpeg($thumbnailImage, $thumbnail->getFullDirectory());
        } elseif ($src_img->file_type == 'image/png') {
            $thumbnail->setFilename($thumbnailFileName[0] . '_tn.png');
            imagepng($thumbnailImage, $thumbnail->getFullDirectory());
        }

        $thumbnail->size = filesize($thumbnail->getFullDirectory());
        return $thumbnail->save(TRUE, FALSE);
    }


    function getSettings()
    {
        $vars['itemname']  = $this->itemname;
        $vars['mod_title'] = $this->mod_title;
        $vars['ms']        = $this->image->_max_size;
        $vars['mw']        = $this->image->_max_width;
        $vars['mh']        = $this->image->_max_height;
        $vars['tnw']       = $this->tn_width;
        $vars['tnh']       = $this->tn_height;

        return $vars;
    }
}

?>