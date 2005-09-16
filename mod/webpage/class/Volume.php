<?php
/**
 * Class that holds individual pages
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::requireConfig('webpage', 'config.php');
PHPWS_Core::initModClass('webpage', 'Page.php');

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
    // array of pages indexed by order, value is id
    var $_pages        = NULL;
    var $_error        = NULL;
    var $_db           = NULL;

    function Webpage_Volume($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
        $this->loadPages();
    }

    function resetDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('webpage_volume');
        } else {
            $this->_db->reset();
        }
    }

    function loadPages()
    {
        $db = & new PHPWS_DB('webpage_page');
        $db->addWhere('volume_id', $this->id);
        $db->setIndexBy('id');
        $db->addOrder('page_number');
        $result = $db->getObjects('Webpage_Page');

        if (!empty($result)) {
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return;
            } else {
                $this->_pages = $result;
            }
        }
    }

    function init()
    {
        $this->resetDB();
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
            $directory = PHPWS_SOURCE_DIR . 'mod/webpage/templates/header/';
        } else {
            $directory = 'templates/webpage/header/';
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
            if (preg_match('/\.tpl$/i', $f_name)) {
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
        if (PHPWS_Core::isPosted()) {
            return TRUE;
        }

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
        $vars['volume_id'] = $this->id;
        $vars['wp_admin'] = 'edit_webpage';
        $links[] = PHPWS_Text::moduleLink(_('Edit'), 'webpage', $vars);

        $tpl['DATE_CREATED'] = $this->getDateCreated();
        $tpl['DATE_UPDATED'] = $this->getDateUpdated();
        $tpl['ACTION']       = implode(' | ', $links);
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

    function &getPagebyNumber($page_number)
    {
        if ($page_number == 1) {
            return current($this->_pages);
        } else {
            for($i=1; $i < $page_number; $i++) {
                $page = next($this->_pages);
            }
            return $page;
        }
    }

    function &getPagebyId($page_id)
    {
        if (!isset($this->_pages[(int)$page_id])) {
            return NULL;
        }
        return $this->_pages[(int)$page_id];
    }

    function viewHeader()
    {
        $template['TITLE'] = $this->title;
        $template['SUMMARY'] = $this->getSummary();
        
        if (!is_file($this->getTemplateDirectory() . $this->template)) {
            return implode('<br />', $template);
        }

        if (Current_User::allow('webpage', 'edit_page', $this->id)) {
            $template['EDIT_HEADER'] = PHPWS_Text::moduleLink(_('Edit header'), 'webpage', array('wp_admin'=>'edit_header',
                                                                                   'volume_id' => $this->id));
        }
        return PHPWS_Template::process($template, 'webpage', 'header/' . $this->template);
    }

}

?>