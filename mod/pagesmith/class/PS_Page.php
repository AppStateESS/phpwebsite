<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Page {
    public $id            = 0;
    public $key_id        = 0;
    public $title         = null;
    public $template      = null;
    public $create_date   = 0;
    public $last_updated  = 0;
    public $front_page    = 0;

    public $_tpl          = null;
    public $_sections     = array();
    public $_content      = null;
    public $_error        = null;
    public $_key          = null;

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

        $this->id = (int)$id;
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
                foreach ($text_sections as $secname=>$section) {
                    PHPWS_Core::plugObject($this->_sections[$secname], $section);
                    $this->_content[$secname] = $this->_sections[$secname]->getContent();
                }
            }

            if (!empty($block_sections)) {
                foreach ($block_sections as $secname=>$section) {
                    PHPWS_Core::plugObject($this->_sections[$secname], $section);
                    if ($form_mode && $this->_sections[$secname]->type_id) {
                        //reload the image form if the image is set
                        $this->_sections[$secname]->loadFiller();
                    }
                    $this->_content[$secname] = $this->_sections[$secname]->getContent();
                }
            }
        }
    }

    /**
     * Loads a single template into the page object from the file
     */
    public function loadTemplate()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Template.php');
        if (!empty($this->template)) {
            $this->_tpl = new PS_Template($this->template);
        } elseif (isset($_REQUEST['tpl'])) {
            $this->_tpl = new PS_Template($_REQUEST['tpl']);
        } else {
            $this->_tpl = null;
        }
    }

    public function row_tags()
    {
        $vars['uop'] = 'view_page';
        $vars['id'] = $this->id;
        $tpl['TITLE'] = PHPWS_Text::moduleLink($this->title, 'pagesmith', $vars);

        $links[] = $this->editLink();
        $links[] = $this->deleteLink();
        $links[] = $this->frontPageToggle();


        $tpl['ACTION'] = implode(' | ', $links);
        $tpl['CREATE_DATE'] = strftime('%d %b %y, %X', $this->create_date);
        $tpl['LAST_UPDATED'] = strftime('%d %b %y, %X', $this->last_updated);

        return $tpl;
    }

    public function deleteLink()
    {
        $vars['id']  = $this->id;
        $vars['aop'] = 'delete_page';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('pagesmith', $vars,true);
        $js['QUESTION'] = dgettext('pagesmith', 'Are you sure you want to delete this page?');
        $js['LINK'] = dgettext('pagesmith', 'Delete');
        return javascript('confirm', $js);
    }

    public function editLink($label=null)
    {
        if (empty($label)) {
            $label = dgettext('pagesmith', 'Edit');
        }
        $vars['id']  = $this->id;
        $vars['aop'] = 'edit_page';
        return PHPWS_Text::secureLink($label, 'pagesmith', $vars);
    }

    public function frontPageToggle()
    {
        if ($this->front_page) {
            $label = dgettext('pagesmith', 'Remove from front');
            $title = dgettext('pagesmith', 'Click to remove from front page');
            $vars['fp'] = 0;
        } else {
            $label = dgettext('pagesmith', 'Add to front');
            $title = dgettext('pagesmith', 'Click to display on front page');
            $vars['fp'] = 1;

        }

        $vars['aop'] = 'front_page_toggle';
        $vars['id'] = $this->id;

        return PHPWS_Text::secureLink($label, 'pagesmith', $vars, null, $title);
    }

    public function save()
    {
        if (!$this->id) {
            $this->create_date = mktime();
        }

        $this->last_updated = mktime();

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
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('pagesmith');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_page');

        $key->setUrl($this->url());

        foreach ($this->_sections as $sec) {
            if ($sec->sectype=='text') {
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
            if (PHPWS_Core::moduleExists('menu')){
                PHPWS_Core::initModClass('menu', 'Menu.php');
                Menu::updateKeyLink($this->key_id);
            }
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
        if (Current_User::allow('pagesmith', 'edit_page', $this->id)) {
            MiniAdmin::add('pagesmith', $this->editLink(sprintf(dgettext('pagesmith', 'Edit %s'), $this->title)));
            MiniAdmin::add('pagesmith', $this->frontPageToggle());
        }
        Layout::getCacheHeaders($this->cacheKey());
        $content = PHPWS_Cache::get($this->cacheKey());

        $this->loadTemplate();
        $this->_tpl->loadStyle();
        $this->flag();

        if (!empty($content)) {
            // needed for filecabinet
            javascript('open_window');
            return $content;
        }

        $this->loadSections();
        if (!empty($this->title) && !PHPWS_Core::atHome()) {
            Layout::addPageTitle($this->title);
        }

        $this->_content['page_title'] = & $this->title;
        $content = PHPWS_Template::process($this->_content, 'pagesmith', $this->_tpl->page_path . 'page.tpl');
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

        return true;
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
}

?>