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
    var $active        = 1;
    var $frontpage     = FALSE;
    var $restricted    = 0;
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
                foreach ($result as $key => $page) {
                    $page->_volume = &$this;
                    $this->_pages[$key] = $page;
                }
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

    function isActive()
    {
        return (bool)$this->active;
    }

    function isRestricted()
    {
        return (bool)$this->restricted;
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


    function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
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

        if (isset($errors)) {
            return $errors;
        } else {
            return TRUE;
        }
    }

    function getViewLink()
    {
        return PHPWS_Text::rewriteLink(_('View'), 'webpage', $this->id);
    }

    function &getCurrentPage()
    {
        return $this->getPagebyNumber($this->_current_page);
    }

    function getPageLink()
    {
        $page = $this->getCurrentPage();
        return $page->getPageLink();
    }

    function getPageUrl()
    {
        $page = $this->getCurrentPage();
        return $page->getPageUrl();
    }

    function rowTags()
    {
        $vars['volume_id'] = $this->id;
        $vars['wp_admin'] = 'edit_webpage';
        $links[] = PHPWS_Text::moduleLink(_('Edit'), 'webpage', $vars);

        $links[] = $this->getViewLink();

        
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
        $page_number = (int)$page_number;

        if (empty($page_number) || empty($this->_pages)) {
            return NULL;
        }

        $i = 1;

        foreach ($this->_pages as $id => $page) {
            if ($page_number != $i) {
                $i++;
                continue;
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


    function getTplTags($page_links=TRUE)
    {
        $template['PAGE_TITLE'] = $this->title;
        $template['SUMMARY'] = $this->getSummary();

        if ($page_links && count($this->_pages) > 1) {
            foreach ($this->_pages as $key => $page) {
                if ($this->_current_page == $page->page_number) {
                    $brief_link[] = $page->page_number;
                    $template['verbose-link'][] = array('VLINK' => $page->page_number . ' ' . $page->title);
                } else {
                    $brief_link[] = $page->getPageLink();
                    $template['verbose-link'][] = array('VLINK' => $page->getPageLink(TRUE));
                }
            }

            if ($this->_current_page > 1) {
                $page = $this->_current_page - 1;
                $template['PAGE_LEFT'] = PHPWS_Text::rewriteLink(WP_PAGE_LEFT, 'webpage', $this->id, $page);
            } 

            if ($this->_current_page < count($this->_pages)) {
                $page = $this->_current_page + 1;
                $template['PAGE_RIGHT'] = PHPWS_Text::rewriteLink(WP_PAGE_RIGHT, 'webpage', $this->id, $page);
            }

           
            $template['BRIEF_PAGE_LINKS'] = implode('&nbsp;', $brief_link);
            $template['PAGE_LABEL'] = _('Page');
        }
        
        if (Current_User::allow('webpage', 'edit_page', $this->id)) {
            $template['EDIT_HEADER'] = PHPWS_Text::moduleLink(_('Edit header'), 'webpage', array('wp_admin'=>'edit_header',
                                                                                   'volume_id' => $this->id));
        }
        return $template;
    }

    function viewHeader()
    {
        $template = $this->getTplTags(FALSE);
        return PHPWS_Template::process($template, 'webpage', 'header.tpl');
    }

    function forceTemplate($template)
    {
        $template_dir = Webpage_Page::getTemplateDirectory();

        if (empty($this->id) || !is_file($template_dir . $template)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('webpage_page');
        $db->addValue('template', $template);
        $db->addWhere('volume_id', $this->id);
        return $db->update();
    }
    
    function view($page=NULL)
    {
        Layout::addStyle('webpage');
        if ($this->isRestricted() && !Current_User::allow('webpage', 'view_page', $this->id)) {
            PHPWS_Error::errorPage(403);
        }

        Layout::addPageTitle($this->title);

        if (!empty($page)) {
            $this->_current_page = (int)$page;
        }

        if (!empty($this->_pages)) {
            $oPage = $this->getCurrentPage();
            if (!is_object($oPage)) {
                exit('major error');
            }
            $content = $oPage->view();

        } else {
            $content = _('Page is not complete.');
        }

        return $content;
    }

    function &getKey()
    {
        return new Key('webpage', 'volume', $this->id);
        // . '.' . $this->_current_page
    }

}

?>