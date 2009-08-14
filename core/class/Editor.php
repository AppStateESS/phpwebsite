<?php

  /**
   * Used to load DHTML editors. Plugin files must be available.
   *
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initCoreClass('File.php');

// If true, then force the usage of the selected editor
if (!defined('FORCE_EDITOR')) {
    define ('FORCE_EDITOR', false);
 }

class Editor {
    public $data       = NULL; // Contains the editor text
    public $name       = NULL;
    public $id         = NULL; // text area id
    public $type       = NULL; // WYSIWYG file
    public $editorList = NULL;
    public $error      = NULL;
    public $limited    = false;
    public $width      = 0;
    public $height     = 0;

    public function __construct($name=NULL, $data=NULL, $id=NULL, $type=NULL)
    {
        $editorList = $this->getEditorList();

        if (PEAR::isError($editorList)) {
            PHPWS_Error::log($editorList);
            $this->type = null;
            return;
        }

        if (empty($type)) {
            $type = $this->getUserType();
        }

        $this->editorList = $editorList;
        if (isset($type)) {
            $result = $this->setType($type);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $this->type = null;
                return;
            }
        }

        if (isset($id)) {
            $this->id = $id;
        }

        if (isset($name)) {
            $this->setName($name);
            if (empty($this->id)) {
                $this->id = $name;
            }
        }

        if (isset($data)) {
            $this->setData(trim($data));
        }
    }

    public function get()
    {
        if (empty($this->type)) {
            return null;
        }
        $formData['NAME']    = $this->name;
        $formData['ID']      = $this->id;
        $formData['VALUE']   = $this->data;
        $formData['LIMITED'] = $this->limited;
        if ($this->width > 200) {
            $formData['WIDTH'] = (int)$this->width;
        }

        if ($this->height > 200) {
            $formData['HEIGHT'] = (int)$this->height;
        }
        return Layout::getJavascript('editors/' . $this->type, $formData);
    }

    public function getEditorList()
    {
        return PHPWS_File::readDirectory('javascript/editors/', true);
    }

    public function getError()
    {
        return $this->error;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUserType()
    {
        if ($user_type = PHPWS_Cookie::read('phpws_editor')) {
            if ($user_type == 'none') {
                return null;
            }
            // prevent shenanigans
            if (preg_match('/\W/', $user_type)) {
                return DEFAULT_EDITOR_TOOL;
            }

            if (Editor::isType($user_type)) {
                return $user_type;
            } else {
                PHPWS_Cookie::delete('phpws_editor');
            }
        }

        return DEFAULT_EDITOR_TOOL;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isType($type_name)
    {
        return in_array($type_name, Editor::getEditorList());
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setType($type)
    {
        if ($this->isType($type)) {
            $this->type = $type;
        }
        else {
            return PHPWS_Error::get(EDITOR_MISSING_FILE, 'core', 'Editor::constructor', $type);
        }
    }

    public function useLimited($value=true)
    {
        $this->limited = (bool)$value;
    }


    public function willWork($type=null)
    {
        if (empty($type)) {
            $type = Editor::getUserType();
        }

        // if type is null, user doesn't want an editor
        if (empty($type)) {
            return false;
        }


        if (FORCE_EDITOR) {
            return true;
        }

        if (USE_WYSIWYG_EDITOR == false) {
            return false;
        }

        if (!javascriptEnabled()) {
            return false;
        }


        if (isset($_SESSION['Editor_Works'][$type])) {
            return $_SESSION['Editor_Works'][$type];
        }
        $browser = strtolower($GLOBALS['browser']);
        $support_file = sprintf('javascript/editors/%s/supported.php', $type);
        if (is_file($support_file)) {
            include $support_file;
            if (isset($supported)) {
                foreach ($supported as $spt) {
                    /**
                     * This used to check three variables for compatibility. We don't pull them all anymore
                     * because browsers don't have a standard identifier.
                     */

                    if (!isset($spt['browser'])) {
                        continue;
                    }

                    if ( $browser == $spt['browser'] ) {
                        $_SESSION['Editor_Works'][$type] = true;
                        return true;
                    }
                }
                $_SESSION['Editor_Works'][$type] = false;
                return false;
            } else {
                $_SESSION['Editor_Works'][$type] = true;
                return true;
            }
        } else {
            $_SESSION['Editor_Works'][$type] = true;
            return true;
        }
    }

}

?>