<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PS_Page {

    public $id = 0;
    public $key_id = 0;
    public $title = null;
    public $template = null;
    public $create_date = 0;
    public $last_updated = 0;
    public $front_page = 0;
    public $parent_page = 0;
    public $page_order = 0;
    public $_tpl = null;
    public $_sections = array();

    /**
     * Contains content left over after change the template
     */
    public $_orphans = array();
    public $_content = null;
    public $_error = null;
    public $_key = null;

    /**
     * Determines whether the menu link will be updated
     * @var boolean
     */
    public $_title_change = false;

    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int) $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('ps_page');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result)) {
            return $result;
        }
        if (!$result) {
            $this->id = 0;
            return false;
        } else {
            return true;
        }
    }

    public function getSectionContent($section_name)
    {
        return $this->_sections[$section_name]->content;
    }

    public function loadSections($form_mode=false, $filler=true)
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
        PHPWS_Core::initModClass('pagesmith', 'PS_Block.php');

        if (empty($this->_tpl)) {
            $this->loadTemplate();
        }

        if (empty($this->_tpl->structure)) {
            PHPWS_Error::log(PS_PG_TPL_ERROR, 'pagesmith', 'PS_Page::loadSections', $this->_tpl->file);
            PHPWS_Core::errorPage();
        }

        foreach ($this->_tpl->structure as $section_xml) {
            switch ($section_xml['TYPE']) {
                case 'image':
                case 'document':
                case 'media':
                case 'block':
                    $section = new PS_Block;
                    break;

                default:
                    $section = new PS_Text;
            }

            $section->plugSection($section_xml, $this->id);

            if ($form_mode) {
                if (!$result = $section->loadSaved()) {
                    if ($filler) {
                        $section->loadFiller();
                    }
                }
            }

            $this->_sections[$section->secname] = $section;
        }

        if ($this->id) {
            // load sections from database
            // load sections should handle template
            $text_db = new PHPWS_DB('ps_text');
            $block_db = new PHPWS_DB('ps_block');

            $text_db->addWhere('pid', $this->id);
            $block_db->addWhere('pid', $this->id);

            $text_db->setIndexBy('secname');
            $block_db->setIndexBy('secname');

            $text_sections = $text_db->select();
            $block_sections = $block_db->select();

            if (!empty($text_sections)) {
                foreach ($text_sections as $secname => $section) {
                    if (isset($this->_sections[$secname])) {
                        PHPWS_Core::plugObject($this->_sections[$secname], $section);
                        // we don't want smarttags parsed
                        $this->_content[$secname] = $this->_sections[$secname]->getContent(!$form_mode);
                    } else {
                        $this->_orphans[$secname] = $section;
                    }
                }
            }

            if (!empty($block_sections)) {
                foreach ($block_sections as $secname => $section) {
                    if (isset($this->_sections[$secname])) {
                        if ($this->_sections[$secname]->width) {
                            $default_w = $this->_sections[$secname]->width;
                        }
                        if ($this->_sections[$secname]->height) {
                            $default_h = $this->_sections[$secname]->height;
                        }

                        PHPWS_Core::plugObject($this->_sections[$secname], $section);
                        
                        if ($this->_sections[$secname]->width) {
                            $this->_sections[$secname]->width = $default_w;
                        }
                        if ($this->_sections[$secname]->height) {
                            $this->_sections[$secname]->height = $default_h;
                        }
                        if ($form_mode && $this->_sections[$secname]->type_id) {
                            //reload the image form if the image is set
                            $this->_sections[$secname]->loadFiller();
                        }
                        $this->_content[$secname] = $this->_sections[$secname]->getContent();
                    } else {
                        $this->_orphans[$secname] = $section;
                    }
                }
            }
        }
    }

    /**
     * Loads a single template into the page object from the file
     */
    public function loadTemplate($tpl=null)
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Template.php');
        if (!empty($tpl)) {
            $this->_tpl = new PS_Template($tpl);
        } elseif (!empty($this->template)) {
            $this->_tpl = new PS_Template($this->template);
        } elseif (isset($_REQUEST['tpl'])) {
            $this->_tpl = new PS_Template($_REQUEST['tpl']);
        } else {
            $this->_tpl = null;
        }
    }

    public function row_tags($subpage=false)
    {
        $vars['uop'] = 'view_page';
        $tpl['ID'] = $vars['id'] = $this->id;
        $tpl['TITLE'] = PHPWS_Text::moduleLink($this->title, 'pagesmith', $vars);

        if (Current_User::allow('pagesmith', 'edit_page', $this->id)) {
            $links[] = $this->editLink(null, true);
            if (!$subpage) {
                $links[] = $this->addPageLink(null, true);
            }
        }

        if (Current_User::allow('pagesmith', 'delete_page')) {
            $links[] = $this->deleteLink(true);
        }

        if (Current_User::allow('pagesmith', 'edit_page', $this->id)) {
            if (!$subpage) {
                $links[] = $this->frontPageToggle(true);
            }
        }

        if (isset($links)) {
            $tpl['ACTION'] = implode(' ', $links);
        }
        $tpl['CREATE_DATE'] = strftime('%d %b %y, %H:%M', $this->create_date);
        $tpl['LAST_UPDATED'] = strftime('%d %b %y, %H:%M', $this->last_updated);

        if ($subpage) {
            $tpl['PAGE_NO'] = $this->page_order + 1;
        }

        if (!$this->parent_page) {
            $db = new PHPWS_DB('ps_page');
            $db->addWhere('parent_page', $this->id);
            $db->addOrder('page_order');
            $children = $db->getObjects('PS_Page');
            $subtpl['ID_LABEL'] = dgettext('pagesmith', 'Id');
            $subtpl['TITLE_LABEL'] = dgettext('pagesmith', 'Title');
            $subtpl['PAGE_LABEL'] = sprintf('<abbr title="%s">%s</a>', dgettext('pagesmith', 'Page number'), dgettext('pagesmith', 'Pg. No.'));
            if (!empty($children)) {
                foreach ($children as $subpage) {
                    $subtpl['subpages'][] = $subpage->row_tags(true);
                }
                $tpl['SUBPAGES'] = PHPWS_Template::process($subtpl, 'pagesmith', 'sublist.tpl');
            }
        }

        return $tpl;
    }

    public function addPageLink($label=null, $icon=false)
    {
        if (empty($label)) {
            $label = dgettext('pagesmith', 'Add page');
        }

        if ($icon) {
            $label = Icon::show('add', $label);
        }

        $vars['pid'] = $this->id;
        $vars['aop'] = 'menu';
        $vars['tab'] = 'new';
        return PHPWS_Text::secureLink($label, 'pagesmith', $vars);
    }

    public function deleteLink($icon=false)
    {
        $vars['id'] = $this->id;
        $vars['aop'] = 'delete_page';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('pagesmith', $vars, true);
        $js['QUESTION'] = dgettext('pagesmith', 'Are you sure you want to delete this page?');
        if ($icon) {
            $js['LINK'] = Icon::show('delete');
        } else {
            $js['LINK'] = dgettext('pagesmith', 'Delete');
        }
        return javascript('confirm', $js);
    }

    public function editLink($label=null, $icon=false)
    {
        if ($icon) {
            $label = Icon::show('edit', dgettext('pagesmith', 'Edit page'));
        } elseif (empty($label)) {
            $label = dgettext('pagesmith', 'Edit');
        }

        $vars['id'] = $this->id;
        $vars['aop'] = 'edit_page';
        return PHPWS_Text::secureLink($label, 'pagesmith', $vars);
    }

    public function frontPageToggle($icon=false)
    {
        if ($this->front_page) {
            $label = dgettext('pagesmith', 'Remove from front');
            if ($icon) {
                $label = sprintf('<img src="%smod/pagesmith/img/back.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, $label, $label);
            }
            $title = dgettext('pagesmith', 'Click to remove from front page');
            $vars['fp'] = 0;
        } else {
            $label = dgettext('pagesmith', 'Add to front');
            if ($icon) {
                $label = sprintf('<img src="%smod/pagesmith/img/front.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP, $label, $label);
            }
            $title = dgettext('pagesmith', 'Click to display on front page');
            $vars['fp'] = 1;
        }

        $vars['aop'] = 'front_page_toggle';
        $vars['id'] = $this->id;

        return PHPWS_Text::secureLink($label, 'pagesmith', $vars, null, $title);
    }

    public function save()
    {
        PHPWS_Core::initModClass('search', 'Search.php');
        if (!$this->id) {
            $this->create_date = time();
        }

        $this->last_updated = time();

        // If this page has a parent and the order is not set
        // then increment
        if (!$this->page_order && $this->parent_page) {
            $page_order = $this->getLastPage();

            if (!PHPWS_Error::logIfError($page_order)) {
                $this->page_order = $page_order + 1;
            } else {
                $this->page_order = 1;
            }
        }

        $db = new PHPWS_DB('ps_page');
        if (PHPWS_Error::logIfError($db->saveObject($this))) {
            return false;
        }

        $this->saveKey();

        $search = new Search($this->key_id);
        $search->resetKeywords();
        $search->addKeywords($this->title);
        PHPWS_Error::logIfError($search->save());

        foreach ($this->_sections as $section) {
            $section->pid = $this->id;
            PHPWS_Error::logIfError($section->save($this->key_id));
        }
        PHPWS_Cache::remove($this->cacheKey());
    }

    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('pagesmith');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_page');

        $key->setUrl($this->url());

        foreach ($this->_sections as $sec) {
            if ($sec->sectype == 'text') {
                $key->setSummary($sec->getContent());
                break;
            }
        }

        $key->setTitle($this->title);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('ps_page');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        } elseif ($this->_title_change) {
            PHPWS_Core::initModClass('menu', 'Menu.php');
            Menu::updateKeyLink($this->key_id);
        }
        return true;
    }

    public function createShortcut()
    {
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        PHPWS_Core::initModClass('menu', 'Menu.php');

        $key = new Key($this->key_id);
        $shortcut = new Access_Shortcut;
        $shortcut->setUrl($key->module, $key->url);

        $shortcut = new Access_Shortcut;
        $shortcut->setUrl('pagesmith', $key->url);

        $result = $shortcut->setKeyword($this->title);
        if (PHPWS_Error::isError($result) || $result == FALSE) {
            return $result;
        }
        $result = $shortcut->save();
        if (PHPWS_Error::isError($result) || $result == FALSE) {
            return $result;
        }

        if ($this->page->parent_page || !PHPWS_Settings::get('pagesmith', 'auto_link')) {
            return true;
        }

        return $this->createMenuShortcut($shortcut, $key);
    }

    public function createMenuShortcut($shortcut, $key)
    {

        $menus = Menu::getPinAllMenus();
        if (PHPWS_Error::logIfError($menus) || empty($menus)) {
            return $menus;
        }

        foreach ($menus as $mn) {
            $link = new Menu_Link;
            $link->setMenuId($mn->id);
            $link->setKeyId($key->id);
            $link->setTitle($key->title);
            $link->url = './' . $shortcut->keyword;
            $link->save();
        }

        return true;
    }

    public function loadKey()
    {
        if (empty($this->_key)) {
            $this->_key = new Key($this->key_id);
        }
    }

    public function flag()
    {
        $this->loadKey();
        if (!$this->front_page && $this->key_id) {
            $this->_key->flag();
        }
    }

    public function cacheKey()
    {
        return 'pagesmith' . $this->id;
    }

    public function view()
    {
        Layout::addStyle('pagesmith');
        if (Current_User::allow('pagesmith', 'edit_page', $this->id)) {
            MiniAdmin::setTitle('pagesmith', 'index.php?module=pagesmith&amp;aop=menu', true);
            MiniAdmin::add('pagesmith', $this->editLink(sprintf(dgettext('pagesmith', 'Edit %s'), $this->title)));
            MiniAdmin::add('pagesmith', $this->frontPageToggle());
        }
        Layout::getCacheHeaders($this->cacheKey());
        $cache = PHPWS_Cache::get($this->cacheKey());

        $this->loadTemplate();
        $this->_tpl->loadStyle();
        $this->flag();

        if (!empty($cache)) {
// needed for filecabinet
            javascript('open_window');
            return $cache;
        }

        $this->loadSections();
        if (!empty($this->title) && !PHPWS_Core::atHome()) {
            Layout::addPageTitle($this->title);
        }

        $this->_content['page_title'] = & $this->title;

        $anchor_title = $tpl['ANCHOR'] = preg_replace('/\W/', '-', $this->title);

        $tpl['CONTENT'] = PHPWS_Template::process($this->_content, 'pagesmith', $this->_tpl->page_path . 'page.tpl');
        $this->pageLinks($tpl);
        if (PHPWS_Settings::get('pagesmith', 'back_to_top')) {
            $tpl['BACK_TO_TOP'] = sprintf('<a href="%s#%s">%s</a>', PHPWS_Core::getCurrentUrl(), $anchor_title, dgettext('pagesmith', 'Back to top'));
        }
        $content = PHPWS_Template::process($tpl, 'pagesmith', 'page_frame.tpl');

        Layout::cacheHeaders($this->cacheKey());
        PHPWS_Cache::save($this->cacheKey(), $content);
        return $content;
    }

    public function delete()
    {
        $db = new PHPWS_DB('ps_page');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        Key::drop($this->key_id);

        $db = new PHPWS_DB('ps_text');
        $db->addWhere('pid', $this->id);
        $db->delete();

        $db = new PHPWS_DB('ps_block');
        $db->addWhere('pid', $this->id);
        $db->delete();

        if ($this->parent_page) {
            $db = new PHPWS_DB('ps_page');
            $db->addWhere('parent_page', $this->parent_page);
            $db->addWhere('page_order', $this->page_order, '>');
            PHPWS_Error::logIfError($db->reduceColumn('page_order'));
        }

        return true;
    }

    private function pageLinks(&$tpl)
    {
        $db = new PHPWS_DB('ps_page');
        $db->addColumn('id');
        $db->addColumn('page_order');
        $db->setIndexBy('page_order');
        $db->addOrder('page_order asc');
        if ($this->parent_page) {
            $db->addWhere('id', $this->parent_page);
            $db->addWhere('parent_page', $this->parent_page, null, 'or');
        } else {
            $db->addWhere('parent_page', $this->id);
        }

        $pages = $db->select('col');

        if (PHPWS_Error::logIfError($pages) || empty($pages)) {
            return;
        }

        if (!$this->parent_page) {
            array_unshift($pages, $this->id);
        }

        if ($this->page_order) {
            $prev_page = $pages[$this->page_order - 1];
        } else {
            $prev_page = 0;
        }

        foreach ($pages as $page_no => $id) {
            if ($page_no == 0 && $prev_page) {
                $link = new PHPWS_Link('<span>&lt;&lt;</span>&#160;' . dgettext('pagesmith', 'Previous'),
                                'pagesmith', array('id' => $prev_page));
                $links[] = $link->get();
            }

            if ($id == $this->id) {
                $links[] = $page_no + 1;
                if (isset($pages[$page_no + 1])) {
                    $next_page = $pages[$page_no + 1];
                } else {
                    $next_page = null;
                }
            } else {
                $link = new PHPWS_Link($page_no + 1, 'pagesmith', array('id' => $id));
                $link->setRewrite();
                $links[] = $link->get();
            }
        }

        if ($next_page) {
            $link->setLabel(dgettext('pagesmith', 'Next') . '&#160;<span>&gt;&gt;</span>');
            $link->setValue('id', $next_page);
            $links[] = $link->get();
        }

        $tpl['PAGE_LINKS'] = implode('&#160;|&#160;', $links);
    }

    public function url()
    {
        $vars['uop'] = 'view_page';
        $vars['id'] = $this->id;

        if (MOD_REWRITE_ENABLED) {
            return 'pagesmith/' . $vars['id'];
        } else {
            return PHPWS_Text::linkAddress('pagesmith', $vars);
        }
    }

    public function getLastPage()
    {
        $db = new PHPWS_DB('ps_page');
        if (!$this->parent_page) {
            $db->addWhere('parent_page', $this->id);
        } else {
            $db->addWhere('parent_page', $this->parent_page);
        }

        $db->addColumn('page_order', 'max');

        $result = $db->select('one');

        if (empty($result)) {
            return 0;
        } else {
            return $result;
        }
    }

    public function setTitle($title)
    {
        $this->title = trim(strip_tags($title, '<em><i><u>'));
    }

}

?>