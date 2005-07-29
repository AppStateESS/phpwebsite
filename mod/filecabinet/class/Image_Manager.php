<?php

// Move this
define('MAX_TN_IMAGE_WIDTH', 100);
define('MAX_TN_IMAGE_HEIGHT', 100);

define('FC_VIEW_MARGIN_WIDTH', 20);
define('FC_VIEW_MARGIN_HEIGHT', 100);

define('FC_UPLOAD_WIDTH', 450);
define('FC_UPLOAD_HEIGHT', 300);

define('FC_NONE_IMAGE', 
       sprintf('<img src="images/mod/filecabinet/none.png" width="32" height="32" title="%s" alt="%s" border="0" />',
               _('None'), _('None')));

class FC_Image_Manager {
    var $itemname       = NULL;
    var $image          = NULL;
    var $thumbnail      = NULL;
    var $max_width      = MAX_IMAGE_WIDTH;
    var $max_height     = MAX_IMAGE_HEIGHT;
    var $tn_width       = MAX_TN_IMAGE_WIDTH;
    var $tn_height      = MAX_TN_IMAGE_HEIGHT;
    var $max_size       = MAX_IMAGE_SIZE;
    var $_error         = NULL;

    function FC_Image_Manager($image_id=NULL)
    {
        if (empty($image_id)) {
            $this->image = & new PHPWS_Image;
            $this->thumbnail = & new PHPWS_Image;
            return;
        }

        $this->image = & new PHPWS_Image((int)$image_id);
        $this->loadThumbnail();
        
    }

    function setModule($module)
    {
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
        $this->image->id = (int)$image_id;
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


    function javascript()
    {
        if ($this->image->id) {
            $vars['image_id'] = $this->image->id;
            $vars['action'] = 'change';
            $tpl['CHANGE_CURRENT'] = $this->getChangeLink();
            $tpl['CURRENT']        = $this->getViewLink();
            $tpl['CLEAR_IMAGE']    = $this->getClearLink();
        } else {
            $tpl['CURRENT'] = FC_NONE_IMAGE;
        }

        $tpl['UPLOAD_NEW'] = $this->getUploadLink(FALSE);

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

    function getChangeLink()
    {
        
    }

    function getUploadLink($use_image=TRUE)
    {
        $vars['width']   = FC_UPLOAD_WIDTH;
        $vars['height']  = FC_UPLOAD_HEIGHT;
        if ($use_image) {
            $vars['label']   = FC_NONE_IMAGE;
        } else {
            $vars['label']   = _('Upload Image');
        }
        $link_vars['action']    = 'upload_form';
        $link_vars['mod_title'] = $this->image->module;
        $link_vars['itemname']  = $this->itemname;
        $link_vars['directory'] = urlencode($this->image->directory);
        $vars['address'] = PHPWS_Text::linkAddress('filecabinet', $link_vars);
        return Layout::getJavascript('open_window', $vars);
    }

    function errorPost() {
        exit(_('An error occurred when trying to save your image.'));
    }

    function postJavascript()
    {
        $vars = array();
        $vars['image_link'] = addslashes($this->getViewLink());
        $vars['hidden'] = addslashes(sprintf('<input type="hidden" name="%s_id" value="%s" />', 
                                             $this->itemname,
                                             $this->image->id)
                                     );
        echo Layout::getModuleJavascript('filecabinet', 'post_file', $vars);
        exit();
    }


    function getClearLink()
    {

    }


    function edit()
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'filecabinet');

        if ($this->image->directory) {
            $form->addHidden('directory', urlencode($this->image->directory));
        }
        
        $form->addHidden('action',    'post_image_close');
        $form->addHidden('mod_title', $this->image->module);
        $form->addHidden('itemname',  $this->itemname);
        
        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        
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

        $errors = $this->image->getErrors();
        if (!empty($errors)) {
            foreach ($errors as $err) {
                $message[] = array('ERROR' => $err->getMessage());
            }
            $template['errors'] = $message;
        }

        return PHPWS_Template::process($template, 'filecabinet', 'edit.tpl');
    }

    function loadReqValues()
    {
        if ($_REQUEST['directory']) {
            $this->setDirectory(urldecode($_REQUEST['directory']));
        }
        $this->setModule($_REQUEST['mod_title']);
        $this->setItemName($_REQUEST['itemname']);
    }

    /**
     * This function is an altered copy of PHPWS_File::makeThumbnail
     */ 
    function createThumbnail()
    {
        $src_img   = &$this->image;
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

}


?>