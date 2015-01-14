<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');

class FC_Multimedia_Manager
{
    public $multimedia = null;
    public $max_size = 0;
    public $folder = null;
    public $content = null;
    public $message = null;
    public $title = null;

    public function __construct($multimedia_id = 0)
    {
        $this->loadMultimedia($multimedia_id);
        $this->loadSettings();
        $this->loadFolder();
    }

    public function admin()
    {
        switch ($_REQUEST['mop']) {
            case 'delete_multimedia':
                if (!$this->folder->id || !Current_User::authorized('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }
                $this->loadMultimedia(filter_input(INPUT_GET, 'file_id', FILTER_VALIDATE_INT));
                $this->multimedia->delete();
                PHPWS_Core::goBack();
                break;

            case 'post_multimedia_upload':
                if (!$this->folder->id || !Current_User::authorized('filecabinet', 'edit_folders', $this->folder->id, 'folder')) {
                    Current_User::disallow();
                }
                $this->postMultimediaUpload();
                \PHPWS_Core::goBack();
                break;

            case 'upload_multimedia_form':
                if (!Current_User::secured('filecabinet', 'edit_folders', $this->multimedia->folder_id, 'folder')) {
                    Current_User::disallow();
                }

                $this->loadMultimedia(filter_input(INPUT_GET, 'file_id', FILTER_VALIDATE_INT));
                if (!$this->multimedia->id) {
                    $this->multimedia->folder_id = filter_input(INPUT_GET, 'folder_id', FILTER_VALIDATE_INT);
                }
                $this->edit();
                echo json_encode(array('title' => $this->title, 'content' => $this->content));
                exit();

            case 'edit_rtmp':
                if (!Current_User::secured('filecabinet', 'edit_folders', $this->multimedia->folder_id, 'folder')) {
                    Current_User::disallow();
                }

                $this->loadMultimedia(filter_input(INPUT_GET, 'file_id', FILTER_VALIDATE_INT));
                $this->editRTMP();
                echo json_encode(array('title' => $this->title, 'content' => $this->content));
                exit();

            case 'post_rtmp':
                if (!Current_User::authorized('filecabinet', 'edit_folders', $this->multimedia->folder_id, 'folder')) {
                    Current_User::disallow();
                }

                if (!$this->postRTMP()) {
                    $this->editRTMP();
                }
                \PHPWS_Core::goBack();
                break;
        }
        return $this->content;
    }

    /**
     * Post function for RTMP form
     * @return boolean
     */
    public function postRTMP()
    {
        $this->multimedia->setTitle(filter_input(INPUT_POST, 'title'));
        if (!$this->multimedia->id) {
            $this->multimedia->folder_id = filter_input(INPUT_POST, 'folder_id', FILTER_VALIDATE_INT);
        }
        $this->multimedia->file_name = $_POST['rtmp_file'];
        $this->multimedia->file_directory = filter_input(INPUT_POST, 'rtmp_server');
        $this->multimedia->thumbnail = PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/video_generic.jpg';
        $this->multimedia->description = '';
        $this->multimedia->file_type = 'video/rtmp';

        $width = filter_input(INPUT_POST, 'width', FILTER_VALIDATE_INT);
        $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_INT);
        if (empty($width) || empty($height)) {
            $this->multimedia->width = 320;
            $this->multimedia->height = 240;
        } else {
            $this->multimedia->width = $width;
            $this->multimedia->height = $height;
        }

        return !PHPWS_Error::logIfError($this->multimedia->save(false, false));
    }

    /**
     */
    public function editRTMP()
    {
        $form = new PHPWS_Form();
        $form->setFormId('file-form');
        $form->addHidden('multimedia_id', $this->multimedia->id);
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('mop', 'post_rtmp');
        $form->addHidden('folder_id', $this->multimedia->folder_id);

        $form->addText('title', $this->multimedia->getTitle());
        $form->setLabel('title', 'Stream title');
        $form->setClass('title', 'form-control');

        $form->addText('rtmp_server', $this->multimedia->file_directory);
        $form->setLabel('rtmp_server', 'RTMP server');
        $form->setClass('rtmp_server', 'form-control');

        $form->addText('rtmp_file', $this->multimedia->file_name);
        $form->setLabel('rtmp_file', 'Stream/File name');
        $form->setClass('rtmp_file', 'form-control');

        $form->addText('width', $this->multimedia->width);
        $form->setLabel('width', 'Width');
        $form->setClass('width', 'form-control');

        $form->addText('height', $this->multimedia->height);
        $form->setLabel('height', 'Height');
        $form->setClass('height', 'form-control');

        $tpl = $form->getTemplate();

        $this->title = dgettext('filecabinet', 'Create/Update RTMP Stream');

        $this->content = PHPWS_Template::process($tpl, 'filecabinet', 'Forms/rtmp_edit.tpl');
    }

    public function loadMultimedia($multimedia_id = 0)
    {
        if (!$multimedia_id && isset($_REQUEST['multimedia_id'])) {
            $multimedia_id = $_REQUEST['multimedia_id'];
        }

        $this->multimedia = new PHPWS_Multimedia($multimedia_id);
    }

    public function loadSettings()
    {
        if (isset($_REQUEST['ms']) && $_REQUEST['ms'] > 1000) {
            $this->setMaxSize($_REQUEST['ms']);
        } else {
            $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_multimedia_size'));
        }
    }

    public function edit()
    {
        static $folder;

        if (empty($this->multimedia)) {
            $this->loadMultimedia();
        }

        if (empty($folder)) {
            $folder = new Folder($this->multimedia->folder_id);
        }
        PHPWS_Core::initCoreClass('File.php');

        $form = new PHPWS_FORM;
        $form->setFormId('file-form');
        $form->addHidden('module', 'filecabinet');
        $form->addHidden('mop', 'post_multimedia_upload');
        $form->addHidden('ms', $this->max_size);
        $form->addHidden('folder_id', $this->multimedia->folder_id);

        $form->addFile('file_name');
        $form->setLabel('file_name', dgettext('filecabinet', 'Multimedia location'));

        $form->addText('title', $this->multimedia->title);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));
        $form->setClass('title', 'form-control');

        $form->addTextArea('description', $this->multimedia->description);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));
        $form->setClass('description', 'form-control');

        if ($this->multimedia->id) {
            $this->title = 'Edit multimedia';
            $form->addHidden('multimedia_id', $this->multimedia->id);

            $form->addText('width', $this->multimedia->width);
            $form->setLabel('width', dgettext('filecabinet', 'Width'));

            $form->addText('height', $this->multimedia->height);
            $form->setLabel('height', dgettext('filecabinet', 'Height'));
        } else {
            $this->title = 'Upload multimedia';
        }

        if ($this->multimedia->id && Current_User::allow('filecabinet', 'edit_folders', $this->multimedia->folder_id, 'folder', true)) {
            Cabinet::moveToForm($form, $folder);
        }

        $template = $form->getTemplate();

        if ($this->multimedia->id) {
            $template['CURRENT_MULTIMEDIA_LABEL'] = dgettext('filecabinet', 'Current multimedia');
            $template['CURRENT_MULTIMEDIA_ICON'] = $this->multimedia->getThumbnail();
            $template['CURRENT_MULTIMEDIA_FILE'] = $this->multimedia->file_name;
            $ow['address'] = PHPWS_Text::linkAddress('filecabinet', array('aop' => 'change_tn',
                        'type' => 'mm',
                        'id' => $this->multimedia->id), true);
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
        $this->content = PHPWS_Template::process($template, 'filecabinet', 'Forms/multimedia_edit.tpl');
    }

    public function setMaxSize($size)
    {
        $this->max_size = (int) $size;
    }

    public function postMultimediaUpload()
    {
        $this->loadMultimedia();

        $result = $this->multimedia->importPost('file_name');

        if (PHPWS_Error::isError($result)) {
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

            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $this->content = dgettext('filecabinet', 'An error occurred when trying to save your multimedia file.');
                $this->content .= '<br /><strong>' . $result->getMessage() . '</strong>';
                $this->content .= '<br /><br />' . javascript('close_window', array('value' => dgettext('filecabinet', 'Close this window')));
                return;
            }
            $this->multimedia->moveToFolder();
            javascript('close_refresh');
        } else {
            Cabinet::setMessage($this->multimedia->printErrors());
            return;
        }
    }

    public function loadFolder($folder_id = 0)
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