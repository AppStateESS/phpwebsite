<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');

class FC_Multimedia_Manager {
    var $cabinet    = null;
    var $multimedia = null;
    var $max_size   = 0;

    function FC_Multimedia_Manager($multimedia_id=0)
    {
        $this->loadMultimedia($multimedia_id);
        $this->loadSettings();
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
        $this->cabinet->title = dgettext('filecabinet', 'Upload multimedia');
        if (empty($this->multimedia)) {
            $this->loadMultimedia();
        }

        PHPWS_Core::initCoreClass('File.php');

        $form = new PHPWS_FORM;
        $form->addHidden('module',    'filecabinet');
        $form->addHidden('aop',       'post_multimedia_upload');
        $form->addHidden('ms',        $this->max_size);
        $form->addHidden('folder_id', $this->cabinet->folder->id);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setLabel('file_name', dgettext('filecabinet', 'Multimedia location'));

        $form->addText('title', $this->multimedia->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));

        $form->addTextArea('description', $this->multimedia->description);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        if (!empty($this->multimedia->id)) {
            $form->addHidden('multimedia_id', $this->multimedia->id);
            $form->addSubmit('submit', dgettext('filecabinet', 'Update'));
        } else {
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
        }
        $template['MAX_SIZE_LABEL'] = dgettext('filecabinet', 'Maximum file size');

        $sys_size = str_replace('M', '', ini_get('upload_max_filesize'));

        $sys_size = $sys_size * 1000000;

        if((int)$sys_size < (int)$this->max_size) {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes (system wide)'), $sys_size);
        } else {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes'), $this->max_size);
        }

        $this->cabinet->content = PHPWS_Template::process($template, 'filecabinet', 'multimedia_edit.tpl');
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
            $this->cabinet->content = dgettext('filecabinet', 'An error occurred when trying to save your multimedia file.');
            javascript('close_refresh', $vars);
            return;
        } elseif ($result) {
            $result = $this->multimedia->save();

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            if (!isset($_POST['im'])) {
                javascript('close_refresh');
            } else {
                javascript('modules/filecabinet/refresh_manager', array('multimedia_id'=>$this->multimedia->id));
            }
        } else {
            $this->cabinet->message = $this->multimedia->printErrors();
            $this->edit();
            return;
        }
    }
}
?>