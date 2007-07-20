<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Page {
    var $id           = 0;
    var $key_id       = 0;
    var $title        = null;
    var $template     = null;
    var $create_date  = 0;
    var $last_updated = 0;

    var $_tpl         = null;
    var $_sections    = array();
    var $_content     = null;
    var $_error       = null;

    function PS_Page($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
        if ($this->id) {
            $this->_content['page_title'] = & $this->title;
        }
    }

    function init()
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


    function getSectionContent($section_name)
    {
        return $this->_sections[$section_name]->content;
    }


    function loadSections($load_content=true, $form_mode=false)
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
        PHPWS_Core::initModClass('pagesmith', 'PS_Block.php');

        if (empty($this->_tpl)) {
            $this->loadTemplate();
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

            $text_sections = $text_db->getObjects('PS_Text');
            $block_sections = $block_db->getObjects('PS_Block');

            if (!empty($text_sections)) {
                foreach ($text_sections as $secname=>$section) {
                    $this->_sections[$secname] = $section;
                    $this->_content[$secname] = $this->_sections[$secname]->getContent();
                }
            }

            if (!empty($block_sections)) {
                foreach ($block_sections as $secname=>$section) {
                    $section->loadContent($form_mode);
                    $this->_sections[$secname] = $section;
                    $this->_content[$secname] = & $this->_sections[$secname]->content;
                }
            }
        } else {
            foreach ($this->_tpl->structure as $section_xml) {
                if ($section_xml['TYPE'] == 'image') {
                    $section = new PS_Block;
                } else {
                    $section = new PS_Text;
                }
                $section->plugSection($section_xml, $this->id);
                if ($load_content) {
                    $section->loadContent($form_mode);
                }
                $this->_sections[$section->secname] = $section;
            }
        }
    }

    /**
     * Loads a single template into the page object from the file
     */
    function loadTemplate()
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

    function row_tags()
    {
        $vars['uop'] = 'view_page';
        $vars['id'] = $this->id;
        $tpl['TITLE'] = PHPWS_Text::moduleLink($this->title, 'pagesmith', $vars);

        $links[] = $this->editLink();
        $links[] = $this->deleteLink();

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function deleteLink()
    {
        $vars['id']  = $this->id;
        $vars['aop'] = 'delete_page';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('pagesmith', $vars,true);
        $js['QUESTION'] = dgettext('pagesmith', 'Are you sure you want to delete this page?');
        $js['LINK'] = dgettext('pagesmith', 'Delete');
        return javascript('confirm', $js);
    }

    function editLink($label=null)
    {
        if (empty($label)) {
            $label = dgettext('pagesmith', 'Edit');
        }
        $vars['id']  = $this->id;
        $vars['aop'] = 'edit_page';
        return PHPWS_Text::secureLink($label, 'pagesmith', $vars);
    }

    function save()
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

        foreach ($this->_sections as $section) {
            $section->pid = $this->id;
            PHPWS_Error::logIfError($section->save());
        }
    }

    function saveKey()
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
        $key->setItemName('page');
        $key->setItemId($this->id);
        $key->setEditPermission('edit');

        if (MOD_REWRITE_ENABLED) {
            $key->setUrl('pagesmith/' . $this->id);
        } else {
            $key->setUrl('index.php?module=pagesmith&amp;id=' . $this->id);
        }

        $key->setTitle($this->title);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('ps_page');
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }

    function flag()
    {
        if ($this->key_id) {
            $key = new Key($this->key_id);
            $key->flag();
        }
    }

    function view()
    {
        if (Current_User::allow('pagesmith', 'edit', $this->id)) {
            MiniAdmin::add('pagesmith', $this->editLink(dgettext('pagesmith', 'Edit page')));
        }
        $this->loadSections();
        $this->_tpl->loadStyle();
        if (!empty($this->title)) {
            Layout::addPageTitle($this->title);
        }

        $this->flag();

        return PHPWS_Template::process($this->_content, 'pagesmith', $this->_tpl->page_path . 'page.tpl');
    }

    function delete()
    {
        $db = new PHPWS_DB('ps_page');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        Key::drop($this->key_id);
        return true;
    }
}

?>