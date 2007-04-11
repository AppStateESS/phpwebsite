<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('filecabinet', 'Document.php');

class FC_Document_Manager {
    var $cabinet  = null;
    var $document = null;
    var $max_size = 0;

    function FC_Document_Manager($document_id=0)
    {
        $this->loadDocument($document_id);
        $this->loadSettings();
    }

    function edit()
    {
        $this->cabinet->title = dgettext('filecabinet', 'Upload document');
        if (empty($this->document)) {
            $this->loadDocument();
        }

        PHPWS_Core::initCoreClass('File.php');

        $form = new PHPWS_FORM;
        $form->addHidden('module',    'filecabinet');
        $form->addHidden('aop',       'post_document_upload');
        $form->addHidden('ms',        $this->max_size);
        $form->addHidden('folder_id', $this->cabinet->folder->id);

        $form->addFile('file_name');
        $form->setSize('file_name', 30);
        $form->setLabel('file_name', dgettext('filecabinet', 'Document location'));

        $form->addText('title', $this->document->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('filecabinet', 'Title'));

        $form->addTextArea('description', $this->document->description);
        $form->setLabel('description', dgettext('filecabinet', 'Description'));

        if (!empty($this->document->id)) {
            $form->addHidden('document_id', $this->document->id);
            $form->addSubmit('submit', dgettext('filecabinet', 'Update'));
        } else {
            $form->addSubmit('submit', dgettext('filecabinet', 'Upload'));
        }

        $form->addButton('cancel', dgettext('filecabinet', 'Cancel'));
        $form->setExtra('cancel', 'onclick="window.close()"');

        $form->setExtra('submit', 'onclick="this.style.display=\'none\'"');

        $template = $form->getTemplate();

        if ($this->document->id) {
            $template['CURRENT_DOCUMENT_LABEL'] = dgettext('filecabinet', 'Current document');
            $template['CURRENT_DOCUMENT_ICON']  = $this->document->getIconView();
            $template['CURRENT_DOCUMENT_FILE']  = $this->document->file_name;
        }
        $template['MAX_SIZE_LABEL'] = dgettext('filecabinet', 'Maximum file size');

        $sys_size = str_replace('M', '', ini_get('upload_max_filesize'));

        $sys_size = $sys_size * 1000000;

        if((int)$sys_size < (int)$this->max_size) {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes (system wide)'), $sys_size);
        } else {
            $template['MAX_SIZE'] = sprintf(dgettext('filecabinet', '%d bytes'), $this->max_size);
        }

        $this->cabinet->content = PHPWS_Template::process($template, 'filecabinet', 'document_edit.tpl');
    }

    function loadDocument($document_id=0)
    {
        if (!$document_id && isset($_REQUEST['document_id'])) {
            $document_id = $_REQUEST['document_id'];
        }

        $this->document = new PHPWS_Document($document_id);
    }

    function loadSettings()
    {
        if (isset($_REQUEST['itemname'])) {
            $this->setItemname($_REQUEST['itemname']);
        }

        if (isset($_REQUEST['ms']) && $_REQUEST['ms'] > 1000) {
            $this->setMaxSize($_REQUEST['ms']);
        } else {
            $this->setMaxSize(PHPWS_Settings::get('filecabinet', 'max_document_size'));
        }
    }

    function postDocumentUpload()
    {
        $this->loadDocument();

        // importPost in File_Common
        $result = $this->document->importPost('file_name');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $vars['timeout'] = '3';
            $vars['refresh'] = 0;
            $this->cabinet->content = dgettext('filecabinet', 'An error occurred when trying to save your document.');
            javascript('close_refresh', $vars);
            return;
        } elseif ($result) {
            $result = $this->document->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            if (!isset($_POST['im'])) {
                javascript('close_refresh');
            } else {
                javascript('modules/filecabinet/refresh_manager', array('document_id'=>$this->document->id));
            }
        } else {
            $this->cabinet->message = $this->document->printErrors();
            $this->edit();
            return;
        }

    }

    function setMaxSize($size)
    {
        $this->max_size = (int)$size;
    }


}

?>