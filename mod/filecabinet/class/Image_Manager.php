<?php

// Move this
define('MAX_TN_IMAGE_WIDTH', 80);
define('MAX_TN_IMAGE_HEIGHT', 80);

define('FC_VIEW_MARGIN_WIDTH', 20);
define('FC_VIEW_MARGIN_HEIGHT', 100);

define('FC_UPLOAD_WIDTH', 450);
define('FC_UPLOAD_HEIGHT', 350);

define('FC_MANAGER_WIDTH', 640);
define('FC_MANAGER_HEIGHT', 480);

define('FC_NONE_IMAGE_SRC', 'images/mod/filecabinet/none.png');

PHPWS_Core::initCoreClass('Image.php');

class FC_Image_Manager {
    var $itemname       = NULL;
    var $image          = NULL;
    var $thumbnail      = NULL;
    var $module         = NULL;
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

    function setModule($module)
    {
        $this->module = $module;
        $this->image->module = $module;
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
        $link_vars['mod_title'] = $this->module;
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

   
    function getUploadLink($use_image=TRUE)
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

        $js_vars['itemname'] = $this->itemname;
        $js_vars['src'] = $this->thumbnail->getPath();
        $js_vars['width'] = $this->thumbnail->width;
        $js_vars['height'] = $this->thumbnail->height;
        $js_vars['title'] = $this->thumbnail->getTitle();
        $js_vars['alt']   = $this->thumbnail->getAlt();
        $js_vars['image_id'] = $this->image->id;

        echo javascript('modules/filecabinet/post_file', $js_vars);
        exit();
    }

    function postUpload()
    {
        $link_vars['action']    = 'edit_image';
        $link_vars['current']   = $this->image->id;
        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', $link_vars);
        echo javascript('modules/filecabinet/post_upload', $vars);
        exit();
    }


    function postImage()
    {
        $errors = $this->image->importPost('file_name');
        $this->image->setTitle($_POST['title']);
        $this->image->setDescription($_POST['description']);

        if (is_array($errors) || PEAR::isError($errors)) {
            return $errors;
        } else {
            $result = $this->image->save();
            return $result;
        }
    }


    function edit()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        $session_index = md5($this->module . $this->itemname);

        if ($this->image->directory) {
            $form->addHidden('directory', urlencode($this->image->directory));
        }
        
        $form->addHidden('action',    'post_image_close');
        $form->addHidden('mod_title', $this->module);
        $form->addHidden('itemname',  $this->itemname);
        $form->addHidden('ms',        $this->image->_max_size);
        $form->addHidden('mh',        $this->image->_max_height);
        $form->addHidden('mw',        $this->image->_max_width);
        $form->addHidden('tnw',       $this->tn_width);
        $form->addHidden('tnh',       $this->tn_height);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setMaxFileSize($this->image->_max_size);
        
        $form->setLabel('file_name', _('Image location'));

        $form->addText('title', $this->image->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('description', $this->image->description);
        //        $form->useEditor('description', FALSE);
        $form->setLabel('description', _('Description'));

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

        $template['MAX_SIZE_LABEL']   = _('Maximum file size');
        $template['MAX_WIDTH_LABEL']  = _('Maximum width');
        $template['MAX_HEIGHT_LABEL'] = _('Maximum height');
        $template['MAX_SIZE']         = $this->image->_max_size;
        $template['MAX_WIDTH']        = $this->image->_max_width;
        $template['MAX_HEIGHT']       = $this->image->_max_height;

        return PHPWS_Template::process($template, 'filecabinet', 'edit.tpl');
    }

    function editImage()
    {

        $db = & new PHPWS_DB('images');
        $db->addWhere('thumbnail_source', 0, '>');
        $db->addWhere('type', 'image/gif', NULL, 'or');
        $db->addWhere('module', $this->module);
        $db->setIndexBy('thumbnail_source');
        $thumbnails = $db->getObjects('PHPWS_Image');

        $db->reset();
        $db->addWhere('module', $this->module);
        $db->addWhere('thumbnail_source', 0);
        $db->setIndexBy('id');
        $source_images = $db->getObjects('PHPWS_Image');

        if (empty($thumbnails)) {
            $tpl['thumbnail-list'][] = array('THUMBNAIL' => _('No images found.'));
        } else {
            foreach ($thumbnails as $tn) {
                if ($tn->type == 'image/gif') {
                    $tpl['thumbnail-list'][] = array('THUMBNAIL' => sprintf('<img src="images/mod/filecabinet/no_tn.png" width="48" height="48" title="%s" alt="%s" />',
                                                                            _('No thumbnails for gifs'),
                                                                            _('No thumbnails for gifs')),
                                                     'TN_ID'     => $tn->id,
                                                     'ID'        => $tn->id,
                                                     'ITEMNAME'  => $this->itemname,
                                                     'MOD_TITLE' => $this->module,
                                                     'WIDTH'     => $tn->width,
                                                     'HEIGHT'    => $tn->height,
                                                     'VIEW'      => sprintf('<img src="images/mod/filecabinet/viewmag+.png" width="16" height="16" title="%s" />', _('View full image'))
                                                     );

                } else {
                    $tpl['thumbnail-list'][] = array('THUMBNAIL' => $tn->getTag(),
                                                     'TN_ID'     => $tn->id,
                                                     'ID'        => $tn->thumbnail_source,
                                                     'ITEMNAME'  => $this->itemname,
                                                     'MOD_TITLE' => $this->module,
                                                     'WIDTH'     => $source_images[$tn->thumbnail_source]->width,
                                                     'HEIGHT'    => $source_images[$tn->thumbnail_source]->height,
                                                     'VIEW'      => sprintf('<img src="images/mod/filecabinet/viewmag+.png" width="16" height="16" title="%s" />', _('View full image'))
                                                     );
                }
            }
        }


        //$js_vars['link_label']    = _('Pick this image');

        if ($this->image->id) {
            $js_vars['current_image'] = $this->image->getTag();
        }

        $js_vars = $this->getSettings();
        $js_vars['title_label']   = _('Title');
        $js_vars['desc_label']    = _('Description');
        $js_vars['upload_width']  = FC_UPLOAD_WIDTH;
        $js_vars['upload_height']  = FC_UPLOAD_HEIGHT;

        $link_vars['image_warning'] = _('Choose a image first.');
        $link_vars['action']    = 'post_pick';
        $link_vars['mod_title'] = $this->module;
        $link_vars['itemname']  = $this->itemname;

        javascript('modules/filecabinet/pick_image', $js_vars);
        $tpl['UPLOAD_LINK'] = $this->getUploadLink();
        $tpl['UPLOAD'] = _('Upload');
        $tpl['OK'] = _('Ok');
        $tpl['CANCEL'] = _('Cancel');
        $tpl['TITLE'] = _('Image Browser');
        $tpl['SUBHEIGHT'] = floor(FC_MANAGER_HEIGHT * .95);

        $content = PHPWS_Template::process($tpl, 'filecabinet', 'manager/pick.tpl');
        return $content;
    }


    function loadReqValues()
    {
        if (isset($_REQUEST['directory'])) {
            $this->setDirectory(urldecode($_REQUEST['directory']));
        }
        $this->setModule($_REQUEST['mod_title']);
        $this->setItemName($_REQUEST['itemname']);
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
        if ($src_img->type == 'image/gif') {
            return FALSE;
        }
        $thumbnail = &$this->thumbnail;

        $thumbnail->directory        = $src_img->directory;
        $thumbnail->module           = $src_img->module;
        $thumbnail->type             = $src_img->type;
        $thumbnail->title            = $src_img->title;
        $thumbnail->description      = $src_img->description;
        $thumbnail->thumbnail_source = $src_img->id;

        if ( ($src_img->width < $this->tn_width) &&
             ($src_img->height < $this->tn_height) ) {
            $src_img->thumbnail_source = $src_img->id;
            $src_img->save(TRUE, FALSE);
            return TRUE;
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
        } else {
            $thumbnailImage = ImageCreate($thumbnail->width, $thumbnail->height);
        }

        if ($src_img->type == 'image/jpeg') {
            $fullImage = ImageCreateFromJPEG($src_img->getFullDirectory());
        } elseif ($src_img->type == 'image/png') {
            $fullImage = ImageCreateFromPNG($src_img->getFullDirectory());
        } else {
            // really?
            return FALSE;
        }

        ImageCopyResized($thumbnailImage, $fullImage, 0, 0, 0, 0,
                         $thumbnail->width, $thumbnail->height, ImageSX($fullImage), ImageSY($fullImage));
        ImageDestroy($fullImage);

        $thumbnailFileName = explode('.', $src_img->filename);

        if ($src_img->type == 'image/jpeg') {
            $thumbnail->setFilename($thumbnailFileName[0] . '_tn.jpg');
            imagejpeg($thumbnailImage, $thumbnail->getFullDirectory());
        } elseif ($src_img->type == 'image/png') {
            $thumbnail->setFilename($thumbnailFileName[0] . '_tn.png');
            imagepng($thumbnailImage, $thumbnail->getFullDirectory());
        }

        $thumbnail->size = filesize($thumbnail->getFullDirectory());

        return $thumbnail->save(TRUE, FALSE);
    }


    function getSettings()
    {
        $vars['itemname']      = $this->itemname;
        $vars['mod_title']     = $this->module;
        $vars['ms']            = $this->image->_max_size;
        $vars['mw']            = $this->image->_max_width;
        $vars['mh']            = $this->image->_max_height;
        $vars['tnw']           = $this->tn_width;
        $vars['tnh']           = $this->tn_height;

        return $vars;
    }
}


?>