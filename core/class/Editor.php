<?php

  /**
   * Used to load DHTML editors. Plugin files must be available.
   *
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initCoreClass('File.php');

class Editor {
    var $data       = NULL; // Contains the editor text
    var $name       = NULL;
    var $type       = NULL; // WYSIWYG file
    var $editorList = NULL;
    var $error      = NULL;

    function Editor($name=NULL, $data=NULL, $type=NULL)
    {
        $editorList = PHPWS_File::readDirectory('./javascript/editors/', TRUE);

        if (PEAR::isError($editorList)) {
            PHPWS_Error::log($editorList);
            PHPWS_Core::errorPage();
        }

        if (empty($type)) {
            $type = DEFAULT_EDITOR_TOOL;
        }

        $this->editorList = $editorList;
        if (isset($type)) {
            $result = $this->setType($type);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                PHPWS_Core::errorPage();
            }
        }

        if (isset($name)) {
            $this->setName($name);
        }

        if (isset($data)) {
            $this->setData(trim($data));
        }

    }

    function get()
    {
        $formData['NAME'] = $this->name;
        $formData['VALUE'] = PHPWS_Text::breaker($this->data);
        return Layout::getJavascript('editors/' . $this->type, $formData);
    }

    function getError()
    {
        return $this->error;
    }

    function getName()
    {
        return $this->name;
    }

    function getType()
    {
        return $this->type;
    }

    function isType($type_name)
    {
        return in_array($type_name, $this->editorList);
    }

    function setData($data)
    {
        $this->data = $data;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setType($type)
    {
        if ($this->isType($type)) {
            $this->type = $type;
        }
        else {
            return PHPWS_Error::get(EDITOR_MISSING_FILE, 'core', 'Editor::constructor', $type);
        }
    }

    function willWork()
    {
        if (USE_WYSIWYG_EDITOR == FALSE) {
            return FALSE;
        }

        if (!javascriptEnabled()) {
            return FALSE;
        }

        extract($GLOBALS['browser_info']);

        if ($browser == 'Opera') {
            return FALSE;
        }

        if ($engine == 'Mozilla' &&
            ( ($engine_version >= '5.0') || ($browser == 'MSIE' && $browser_version > '5.5') )
            ) {

            return TRUE;
        }

        return FALSE;

    }

}

?>