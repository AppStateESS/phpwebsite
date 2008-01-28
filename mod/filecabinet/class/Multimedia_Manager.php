<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');

class FC_Multimedia_Manager {
    var $multimedia = null;
    var $max_size   = 0;
    var $folder     = null;
    var $content    = null;
    var $message    = null;

    function FC_Multimedia_Manager($multimedia_id=0)
    {
        $this->loadMultimedia($multimedia_id);
        $this->loadSettings();
        $this->loadFolder();
    }

    function admin()
    {
        switch ($_REQUEST['mop']) {
        case 'delete_multimedia':
            $this->multimedia->delete();
            PHPWS_Core::goBack();
            break;

        case 'post_multimedia_upload':
            $this->postMultimediaUpload();
            break;

        case 'upload_multimedia_form':
            $this->edit();
            break;
        }
        return $this->content;
    }


    function loadMultimedia($multimedia_id=0)
    {
        if (!$multimedia_id && isset($_REQUEST['multimedia_id'])) {
            $multimedia_id = $_REQUEST['multimedia_id'];
        }

        $this->multimedia = new PHPWS_Multimedia($multimedia_id);
    }

    function loadSettings()
    {
        if (isset($_REQUEST['ms']) && $_REQUEST['ms'] > 1000) {
            $this->setMaxSize($_REQUEST['ms']);
        } else {
            $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_multimedia_size'));
        }
    }

    function edit()
    {
        if (empty($this->multimedia)) {
            $this->loadMultimedia();
        }

        PHPWS_Core::initCoreClass('File.php');

        $form = new PHPWS_FORM;
        $form->addHidden('module',    'filecabinet');
        $form->addHidden('mop',       'post_multimedia_upload');
        $form->addHidden('ms',        $this->max_size);
        $form->addHidden('folder_id', $this->folder->id);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setLabel('file_name', dgettext('filecabinet', 'Multimedia location'));

        $form->addText('title', $this->multimedia->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));

        $form->addTextArea('description', $this->multimedia->description);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        if ($this->multimedia->id) {
            $form->addTplTag('FORM_TITLE', 'Edit multimedia');
            $form->addHidden('multimedia_id', $this->multimedia->id);
            $form->addSubmit('submit', dgettext('filecabinet', 'Update'));

            $form->addText('width', $this->multimedia->width);
            $form->setSize('width', 5, 5);
            $form->setLabel('width', dgettext('filecabinet', 'Width'));
            
            $form->addText('height', $this->multimedia->height);
            $form->setSize('height', 5, 5);
            $form->setLabel('height', dgettext('filecabinet', 'Height'));
        } else {
            $form->addTplTag('FORM_TITLE', 'Upload multimedia');
            $form->addSubmit('submit', dgettext('filecabinet', 'Upload'));
        }

        $form->addButton('cancel', dgettext('filecabinet', 'Cancel'));
        $form->setExtra('cancel', 'onclick="window.close()"');

        $form->setExtra('submit', 'onclick="this.style.display=\'none\'"');

        $template = $form->getTemplate();

        if ($this->multimedia->id) {
            $template['CURRENT_MULTIMEDIA_LABEL'] = dgettext('filecabinet', 'Current multimedia');
            $template['CURRENT_MULTIMEDIA_ICON']  = $this->multimedia->getThumbnail();
            $template['CURRENT_MULTIMEDIA_FILE']  = $this->multimedia->file_name;
            $ow['address'] = PHPWS_Text::linkAddress('filecabinet', array('aop' =>'change_tn',
                                                                          'type'=>'mm',
                                                                          'id'  =>$this->multimedia->id),
                                                     true);
            $ow['label'] = 'Change thumbnail';
            $ow['width'] = 400;
            $ow['height'] = 250;
            $template['EDIT_THUMBNAIL'] = javascript('open_window', $ow);
        }

        $template['MAX_SIZE_LABEL'] = dgettext('filecabinet', 'Maximum file size');

        $size_max = Cabinet::getMaxSizes();
        $sys_size = & $size_max['system'];
        $form_max = & $size_max['form'];

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

        if ($this->message) {
            $template['ERROR'] = $this->message;
        }
        $this->content = PHPWS_Template::process($template, 'filecabinet', 'multimedia_edit.tpl');
    }

    function setMaxSize($size)
    {
        $this->max_size = (int)$size;
    }

    function postMultimediaUpload()
    {
        $this->loadMultimedia();

        // importPost in File_Common
        $result = $this->multimedia->importPost('file_name');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $vars['timeout'] = '3';
            $vars['refresh'] = 0;
            $this->content = dgettext('filecabinet', 'An error occurred when trying to save your multimedia file.');
            javascript('close_refresh', $vars);
            return;
        } elseif ($result) {
            if (empty($_FILES['file_name']['name'])) {
                $result = $this->multimedia->save(false, false);
            } else {
                $result = $this->multimedia->save();
            }
            
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $this->content = dgettext('filecabinet', 'An error occurred when trying to save your multimedia file.');
                $this->content .= '<br /><strong>' . $result->getMessage() . '</strong>';
                $this->content .= '<br /><br />' . javascript('close_window', array('value'=> dgettext('filecabinet', 'Close this window')));
                return;
            }

            javascript('close_refresh');
        } else {
            $this->message = $this->multimedia->printErrors();
            $this->edit();
            return;
        }
    }

    function loadFolder($folder_id=0)
    {
        if (!$folder_id && isset($_REQUEST['folder_id'])) {
            $folder_id = &$_REQUEST['folder_id'];
        }

        $this->folder = new Folder($folder_id);
        if (!$this->folder->id) {
            $this->folder->ftype = MULTIMEDIA_FOLDER;
        }
    }

}
?>