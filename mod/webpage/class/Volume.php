<?php
/**
 * Class that holds individual pages
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::requireInc('webpage', 'error_defines.php');
PHPWS_Core::requireConfig('webpage', 'config.php');
PHPWS_Core::initModClass('webpage', 'Page.php');

if (!defined('WP_VOLUME_DATE_FORMAT')) {
    define('WP_VOLUME_DATE_FORMAT', '%c');
}

class Webpage_Volume {
    public $id             = 0;
    public $key_id         = 0;
    public $title          = null;
    public $summary        = null;
    public $date_created   = 0;
    public $date_updated   = 0;
    public $create_user_id = 0;
    public $created_user   = null;
    public $update_user_id = 0;
    public $updated_user   = null;
    public $frontpage      = false;
    public $approved       = 0;
    public $active         = 1;
    public $featured       = 0;
    public $_current_page  = 1;
    // array of pages indexed by order, value is id
    public $_key           = null;
    public $_pages         = null;
    public $_error         = null;
    public $_db            = null;

    public function __construct($id=null)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
        $this->loadPages();
    }

    public function resetDB()
    {
        if (empty($this->_db)) {
            $this->_db = new PHPWS_DB('webpage_volume');
        } else {
            $this->_db->reset();
        }
    }

    public function loadApprovalPages()
    {
        $this->loadPages();
        $approval = new Version_Approval('webpage', 'webpage_page', 'Webpage_Page');
        $approval->_db->addOrder('page_number');
        $approval->_db->addWhere('volume_id', $this->id);
        $pages = $approval->get();

        if (!empty($pages)) {
            foreach ($pages as $version) {
                $page = new Webpage_Page;
                $page->_volume = & $this;
                $version->loadObject($page);
                $this->_pages[$page->id] = $page;
            }
        }
    }

    public function loadPages()
    {
        $db = new PHPWS_DB('webpage_page');
        $db->addWhere('volume_id', $this->id);
        if ($this->approved) {
            $db->addWhere('approved', 1);
        }
        $db->setIndexBy('id');
        $db->addOrder('page_number');
        $result = $db->getObjects('Webpage_Page');

        if (!empty($result)) {
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return;
            } else {
                foreach ($result as $key => $page) {
                    $page->_volume = & $this;
                    $this->_pages[$key] = $page;
                }
            }
        }
    }

    public function init()
    {
        $this->resetDB();
        $result = $this->_db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return;
        }
    }


    public function getDateCreated($format=null)
    {
        if (empty($format)) {
            $format = WP_VOLUME_DATE_FORMAT;
        }

        return strftime($format, $this->date_created);
    }

    public function getDateUpdated($format=null)
    {
        if (empty($format)) {
            $format = WP_VOLUME_DATE_FORMAT;
        }

        return strftime($format, $this->date_updated);
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }


    public function getSummary()
    {
        return PHPWS_Text::parseOutput($this->summary);
    }

    public function getTotalPages()
    {
        return count($this->_pages);
    }

    public function post()
    {
        if (empty($_POST['title'])) {
            $errors[] = dgettext('webpage', 'Missing page title');
        } else {
            $this->setTitle($_POST['title']);
        }

        if (empty($_POST['summary'])) {
            $this->summary = null;
        } else {
            $this->setSummary($_POST['summary']);
        }

        if (isset($_POST['volume_version_id']) || Current_User::isRestricted('webpage')) {
            $this->approved = 0;
        } else {
            $this->approved = 1;
        }

        if (isset($errors)) {
            return $errors;
        } else {
            return true;
        }
    }

    public function getViewLink($base=false)
    {
        if ($base) {
            if (MOD_REWRITE_ENABLED) {
                return sprintf('webpage/id/%s', $this->id);
            } else {
                return 'index.php?module=webpage&amp;id=' . $this->id;
            }
        } else {
            return PHPWS_Text::rewriteLink(dgettext('webpage', 'View'), 'webpage', array('id'=>$this->id));
        }
    }

    public function getCurrentPage()
    {
        $page = $this->getPagebyNumber($this->_current_page);
        // Necessary for php 4
        $page->_volume->_current_page = $this->_current_page;
        return $page;
    }

    public function getPageLink()
    {
        $page = $this->getCurrentPage();
        return $page->getPageLink();
    }

    public function getPageUrl()
    {
        $page = $this->getCurrentPage();
        if ($page) {
            return $page->getPageUrl();
        } else {
            return null;
        }
    }

    /**
     * returns an associative array for the dbpager listing of volumes
     */
    public function rowTags()
    {
        $vars['volume_id'] = $this->id;

        /**
         * Show edit link if volume is not approved and the last person to update
         * is currently logged in. Otherwise allow to edit if user has normal
         * permissions.
         */
        if ($this->canEdit()) {
            $vars['wp_admin'] = 'edit_webpage';
            if (Current_User::isRestricted('webpage')) {
                $version = new Version('webpage_volume');
                $version->setSource($this);
                $approval_id = $version->isWaitingApproval();
                if ($approval_id) {
                    $vars['version_id'] = & $approval_id;
                }
            }
            $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Edit'), 'webpage', $vars);
        }

        if ($this->canView()) {
            $links[] = $this->getViewLink();
        }

        if (Current_User::isUnrestricted('webpage')) {
            $links[] = Current_User::popupPermission($this->key_id);
            if (Current_User::allow('webpage', 'delete_page')) {
                $vars['wp_admin'] = 'delete_wp';
                $js_vars['QUESTION'] = sprintf(dgettext('webpage', 'Are you sure you want to delete &quot;%s&quot and all its pages?'),
                                               $this->title);
                $js_vars['ADDRESS'] = PHPWS_Text::linkAddress('webpage', $vars, true);
                $js_vars['LINK'] = dgettext('webpage', 'Delete');
                $links[] = javascript('confirm', $js_vars);
            }
        }

        $tpl['DATE_CREATED'] = $this->getDateCreated();
        $tpl['DATE_UPDATED'] = $this->getDateUpdated();
        if (!empty($links)) {
            $tpl['ACTION']       = implode(' | ', $links);
        }

        if ($this->canView()) {
            $tpl['TITLE'] = sprintf('<a href="%s">%s</a>', $this->getViewLink(true), $this->title);
        } else {
            $tpl['TITLE'] = $this->title;
        }

        if (!$this->approved) {
            $tpl['TITLE'] .= ' ' . dgettext('webpage', '[Unapproved]');
        }

        if (Current_User::isUnrestricted('webpage')) {
            $tpl['CHECKBOX'] = sprintf('<input type="checkbox" name="webpage[]" id="webpage" value="%s" />', $this->id);
        }

        if (Current_User::isUnrestricted('webpage')) {
            if ($this->frontpage) {
                $vars['wp_admin'] = 'move_off_frontpage';
                $fp = PHPWS_Text::secureLink(dgettext('webpage', 'Yes'), 'webpage', $vars);
            } else {
                $vars['wp_admin'] = 'move_to_frontpage';
                $fp = PHPWS_Text::secureLink(dgettext('webpage', 'No'), 'webpage', $vars);
            }
            $tpl['FRONTPAGE'] = $fp;
        } else {
            $tpl['FRONTPAGE'] = $this->frontpage ? dgettext('webpage', 'Yes') : dgettext('webpage', 'No');
        }

        if (Current_User::isUnrestricted('webpage')) {
            if ($this->active) {
                $vars['wp_admin'] = 'deactivate_vol';
                $active = PHPWS_Text::secureLink(dgettext('webpage', 'Yes'), 'webpage', $vars);
            } else {
                $vars['wp_admin'] = 'activate_vol';
                $active = PHPWS_Text::secureLink(dgettext('webpage', 'No'), 'webpage', $vars);
            }
            $tpl['ACTIVE'] = $active;
        } else {
            $tpl['ACTIVE'] = $this->active ? dgettext('webpage', 'Yes') : dgettext('webpage', 'No');
        }

        return $tpl;
    }

    public function delete()
    {
        $pagedb = new PHPWS_DB('webpage_page');
        $pagedb->addWhere('volume_id', $this->id);
        $result = $pagedb->delete();

        if (PEAR::isError($result)) {
            return $result;
        }

        $page_version = new PHPWS_DB('webpage_page_version');
        $page_version->addWhere('volume_id', $this->id);
        $page_version->delete();

        Key::drop($this->key_id);

        $this->resetDB();
        $this->_db->addWhere('id', $this->id);
        $result = $this->_db->delete();
        if (PEAR::isError($result)) {
            return $result;
        }

        Version::flush('webpage_volume', $this->id);

        return true;
    }

    public function save($version_update=false)
    {
        PHPWS_Core::initModClass('version', 'Version.php');

        if (empty($this->title)) {
            return PHPWS_Error::get(WP_TPL_TITLE_MISSING, 'webpages', 'Volume::save');
        }

        if (!$version_update) {
            $this->update_user_id = Current_User::getId();
            $this->updated_user   = Current_User::getUsername();
            $this->date_updated   = mktime();
        }

        if (!$this->id) {
            $new_vol = true;
            $this->create_user_id = Current_User::getId();
            $this->created_user = Current_User::getUsername();
            $this->date_created = mktime();
        } else {
            $new_vol = false;
        }

        // If unapproved, we create an unapproved source volume
        if ($this->approved || !$this->id) {
            $this->resetDB();
            $result = $this->_db->saveObject($this);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if ($this->approved) {
            $update = (!$this->key_id) ? true : false;

            $this->_key = $this->saveKey();
            if ($update) {
                $this->_db->saveObject($this);
            }
            $search = new Search($this->key_id);
            $search->addKeywords($this->title);
            $search->addKeywords($this->summary);
            PHPWS_Error::logIfError($search->save());
        }

        if (!$version_process) {
            $version = new Version('webpage_volume');
            $version->setSource($this);
            $version->setApproved($this->approved);

            return $version->save();
        }
        return true;
    }

    public function saveKey()
    {
        if ($this->key_id) {
            $key = new Key($this->key_id);
        } else {
            $key = new Key;
            $key->setModule('webpage');
            $key->setItemName('volume');
            $key->setItemId($this->id);
            $key->setEditPermission('edit_page');
        }

        $key->active = (int)$this->active;
        $key->setTitle($this->title);
        $key->setSummary($this->summary);
        $key->setUrl($this->getViewLink(true));

        if (PHPWS_Error::logIfError($key->save())) {
            return null;
        }
        $this->key_id = $key->id;
        return $key;
    }


    public function getPagebyNumber($page_number)
    {
        if (!$page_number) {
            return null;
        }

        $page_number = (int)$page_number;

        if (empty($page_number) || empty($this->_pages)) {
            return null;
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

    public function getPagebyId($page_id)
    {
        if (!isset($this->_pages[(int)$page_id])) {
            return null;
        }
        return $this->_pages[(int)$page_id];
    }


    public function getPageSelect($alist)
    {
        $form = new PHPWS_Form('page_select');
        $form->setMethod('get');
        $form->noAuthKey();
        $form->addHidden('module', 'webpage');
        $form->addHidden('id', $this->id);
        $form->addSelect('page', $alist);
        $form->setMatch('page', $this->_current_page);
        $form->setLabel('page', dgettext('webpage', 'Page'));
        if (javascriptEnabled()) {
            $form->setExtra('page', 'onchange="this.form.submit()"');
        } else {
            $form->addSubmit('go', dgettext('webpage', 'Go!'));
        }
        $formtpl = $form->getTemplate();
        return implode("\n", $formtpl);
    }

    public function getTplTags($page_links=true, $version=0)
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
                    $template['verbose-link'][] = array('VLINK' => $page->getPageLink(true));
                }
                $alist[$page->page_number] = $page->title;
            }

            $template['PAGE_SELECT'] = $this->getPageSelect($alist);

            if ($this->_current_page > 1) {
                $page = $this->_current_page - 1;
                $template['PAGE_LEFT'] = PHPWS_Text::rewriteLink(WP_PAGE_LEFT, 'webpage', array('id'=>$this->id, 'page'=>$page));
                $template['PREVIOUS_PAGE']  = PHPWS_Text::rewriteLink(WP_PREVIOUS_PAGE, 'webpage', array('id'=>$this->id, 'page'=>$page));
            }

            if ($this->_current_page < count($this->_pages)) {
                $page = $this->_current_page + 1;
                $template['PAGE_RIGHT'] = PHPWS_Text::rewriteLink(WP_PAGE_RIGHT, 'webpage', array('id'=>$this->id, 'page'=>$page));
                $template['NEXT_PAGE']  = PHPWS_Text::rewriteLink(WP_NEXT_PAGE, 'webpage', array('id'=>$this->id, 'page'=>$page));
            }


            $template['BRIEF_PAGE_LINKS'] = implode('&nbsp;', $brief_link);
            $template['PAGE_LABEL'] = dgettext('webpage', 'Page');
        }

        if ( (Current_User::allow('webpage', 'edit_page') && Current_User::isUser($this->create_user_id)) ||
             Current_User::allow('webpage', 'edit_page', $this->id, 'volume')) {
            $vars['wp_admin'] = 'edit_header';
            $vars['volume_id'] = $this->id;
            if ($version) {
                $vars['version_id'] = $version;
            }
            $template['EDIT_HEADER'] = PHPWS_Text::secureLink(dgettext('webpage', 'Edit header'), 'webpage', $vars);
        }

        if (!$version && Current_User::allow('webpage', 'edit_page', null, null, true)) {
            $vars = array('wp_admin' => 'restore_volume', 'volume_id'=>$this->id);
            $template['RESTORE'] = PHPWS_Text::secureLink(dgettext('webpage', 'Restore'), 'webpage', $vars);
            if ($this->featured) {
                $vars['wp_admin'] = 'unfeature';
            } else {
                $vars['wp_admin'] = 'feature';
            }
            $template['FEATURE'] = PHPWS_Text::secureLink(dgettext('webpage', 'Feature'), 'webpage', $vars);
        }

        $result = Categories::getSimpleLinks($this->key_id);
        if (!empty($result)) {
            $template['CATEGORIES'] = implode(', ', $result);
        }

        return $template;
    }

    public function viewHeader($version=0)
    {
        if (!$this->frontpage) {
            $this->flagKey();
        }
        $template = $this->getTplTags(false, $version);
        return PHPWS_Template::process($template, 'webpage', 'header.tpl');
    }

    public function forceTemplate($template)
    {
        $template_dir = Webpage_Page::getTemplateDirectory();

        if (empty($this->id) || !is_file($template_dir . $template)) {
            return false;
        }

        $db = new PHPWS_DB('webpage_page');
        $db->addValue('template', $template);
        $db->addWhere('volume_id', $this->id);
        return $db->update();
    }

    public function view($page=null, $show_page_title=true)
    {
        if (!$this->canView()) {
            if ($this->frontpage && PHPWS_Core::atHome()) {
                return null;
            } else {
                return dgettext('webpage', 'Sorry, this web page is restricted.');
            }
        }

        Layout::addStyle('webpage');
        if ($show_page_title) {
            Layout::addPageTitle($this->title);
        }

        if (!empty($page)) {
            $this->_current_page = (int)$page;
        }

        if (!empty($this->_pages)) {
            if ($page == 'all') {
                $content = $this->showAllPages();
            } else {
                $oPage = $this->getCurrentPage();

                if (!is_object($oPage)) {
                    PHPWS_Error::log(WP_PAGE_FROM_VOLUME, 'webpage', 'Webpage_Volume::view');
                    return null;
                }
                $content = $oPage->view();
            }
        } else {
            $content = dgettext('webpage', 'Page is not complete.');
        }

        $this->flagKey();
        return $content;
    }

    public function showAllPages($admin=false)
    {
        $template = $this->getTplTags(false);
        foreach ($this->_pages as $page) {
            $template['multiple'][] = $page->getTplTags(false, false);
        }

        return PHPWS_Template::process($template, 'webpage', 'multiple/default.tpl');
    }

    public function flagKey()
    {
        if ($this->frontpage) {
            $key = Key::getHomeKey();
            $key->flag();
            return;
        }
        $this->loadKey();
        $this->_key->flag();
    }

    public function loadKey()
    {
        if (empty($this->_key)) {
            $this->_key = new Key($this->key_id);
        }
    }

    public function joinAllPages()
    {
        foreach ($this->_pages as $page) {
            if (!isset($first_page)) {
                $first_page = $page;
                $all_content[] = $page->content;
                continue;
            }

            $all_content[] = '<h2>' . $page->title . '</h2>' . $page->content;
            $page->delete();
        }

        $first_page->content = implode("\n", $all_content);
        $first_page->save();
    }

    public function joinPage($page_id)
    {
        if (!isset($this->_pages[$page_id])) {
            return true;
        } else {
            $source = $this->_pages[$page_id];
        }

        foreach ($this->_pages as $id => $page) {
            if ($id == $page_id) {
                break;
            }
        }

        $next_page = current($this->_pages);

        $source->content .= '&lt;br /&gt;&lt;h2&gt;' . $next_page->title . '&lt;/h2&gt;' . $next_page->content;
        $source->save();
        $result = $next_page->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return true;
        }
        unset($this->_pages[$next_page->id]);
    }

    public function dropPage($page_id)
    {
        if (!isset($this->_pages[$page_id])) {
            return true;
        }

        $this->_pages[$page_id]->delete();
        unset($this->_pages[$page_id]);
        Version::flush('webpage_page', $page_id);

        $count = 1;
        foreach ($this->_pages as $id => $page) {
            $page->page_number = $count;
            $page->save();
            $count++;
        }
    }

    public function approval_view()
    {
        $template['TITLE'] = $this->title;
        $template['SUMMARY'] = $this->getSummary();
        return PHPWS_Template::process($template, 'webpage', 'approval_list.tpl');
    }

    public function saveSearch()
    {
        $this->loadPages();
        if (empty($this->_pages)) {
            return true;
        }

        $search = new Search($this->key_id);
        $search->resetKeywords();
        foreach ($this->_pages as $page) {
            $content[] = $page->title;
            $content[] = $page->content;
        }

        $all_search_content[] = implode(' ', $content);
        $search->addKeywords($all_search_content);
        return $search->save();
    }

    /**
     * Decides if a user can edit a volume.
     */
    public function canEdit()
    {
        // If this is a new volume or unapproved and the current user created it
        // let them edit
        if ( (!$this->id || !$this->approved) && $this->create_user_id == Current_User::getId()) {
            return true;
        }

        if (Current_User::allow('webpage', 'edit_page', $this->id, 'volume')) {
            return true;
        }

        return false;
    }

    public function canView()
    {
        $this->loadKey();
        return $this->_key->allowView();
    }

}

?>