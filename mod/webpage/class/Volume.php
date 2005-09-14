<?php
/**
 * Class that holds individual pages
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::requireConfig('webpage', 'config.php');

if (!defined('WP_VOLUME_DATE_FORMAT')) {
    define('WP_VOLUME_DATE_FORMAT', '%c'); 
}

class Webpage_Volume {
    var $id            = 0;
    var $title         = NULL;
    var $summary       = NULL;
    var $date_created  = 0;
    var $date_updated  = 0;
    var $created_user  = NULL;
    var $updated_user  = NULL;
    var $template      = NULL;
    var $frontpage     = FALSE;
    var $_current_page = 1;
    var $_error        = NULL;
    var $_db           = NULL;

    function Webpage_Volume($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function resetDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('webpage_volume');
        } else {
            $this->_db->reset;
        }
    }

    function init()
    {
        $result = $this->_db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return;
        }
    }

    function getDateCreated($format=NULL)
    {
        if (empty($format)) {
            $format = WP_VOLUME_DATE_FORMAT;
        }

        return strftime($format, $this->date_created);
    }

    function getDateUpdated($format=NULL)
    {
        if (empty($format)) {
            $format = WP_VOLUME_DATE_FORMAT;
        }

        return strftime($format, $this->date_updated);
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    function setTemplate($template)
    {
        $this->template = strip_tags($template);
    }

    function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
    }

    function getTemplateDirectory()
    {
        if (FORCE_MOD_TEMPLATES) {
            $directory = PHPWS_SOURCE_DIR . 'mod/webpage/templates/volume/';
        } else {
            $directory = 'templates/webpage/volume/';
        }
        return $directory;
    }

    function getTemplateList()
    {
        $directory = $this->getTemplateDirectory();

        $files = scandir($directory);
        if (empty($files)) {
            return NULL;
        }

        foreach ($files as $key => $f_name) {
            if ($f_name == '.' || $f_name == '..' || preg_match('/~$/i', $f_name)) {
                continue;
            } else {
                $file_list[$f_name] = $f_name;
            }
        }
        if (!isset($file_list)) {
            return NULL;
        } else {
            return $file_list;
        }
    }

    function post()
    {
        if (empty($_POST['title'])) {
            $errors[] = _('Missing page title');
        } else {
            $this->setTitle($_POST['title']);
        }

        if (empty($_POST['summary'])) {
            $this->summary = NULL;
        } else {
            $this->setSummary($_POST['summary']);
        }

        if (empty($_POST['template'])) {
            return PHPWS_Error(WP_MISSING_TEMPLATE, 'webpage', 'Volume::post');
        }

        $this->setTemplate($_POST['template']);

        if (isset($errors)) {
            return $errors;
        } else {
            return TRUE;
        }
    }

    function checkTemplate()
    {
        $directory = $this->getTemplateDirectory() . $this->template;
        return is_file($directory);
    }

    function rowTags()
    {
        $tpl['DATE_CREATED'] = $this->getDateCreated();
        $tpl['DATE_UPDATED'] = $this->getDateUpdated();
        $tpl['ACTION']       = 'action stuff';

        return $tpl;
    }

    function save()
    {
        if (empty($this->title)) {
            return PHPWS_Error::get(WP_TPL_TITLE_MISSING, 'webpages', 'Volume::save');
        }

        if (!$this->checkTemplate()) {
            return PHPWS_Error::get(WP_TPL_FILE_MISSING, 'webpages', 'Volume::save');
        }

        $this->updated_user = Current_User::getUsername();
        $this->date_updated = mktime();
        if (!$this->id) {
            $this->created_user = Current_User::getUsername();
            $this->date_created = mktime();
        }

        $this->resetDB();

        $result = $this->_db->saveObject($this);

        return $result;
    }
}

?>