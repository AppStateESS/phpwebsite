<?php
/**
 * vshop - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

class vShop_Dept {

    public $id             = 0;
    public $key_id         = 0;
    public $title          = null;
    public $description    = null;
    public $file_id        = 0;

    public $_error         = null;


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
        $db = new \core\DB('vshop_depts');
        $result = $db->loadObject($this);
        if (core\Error::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setDescription($description)
    {
        $this->description = \core\Text::parseInput($description);
    }

    public function setFile_id($file_id)
    {
        $this->file_id = $file_id;
    }



    public function getTitle($print=false, $breadcrumb=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            if ($breadcrumb) {
                if (vShop::countDepts() !== 1) {
                    return \core\Text::moduleLink(core\Text::parseOutput(core\Settings::get('vshop', 'mod_title')), 'vshop') . ' &#187; ' . \core\Text::parseOutput($this->title);
                } else {
                    return \core\Text::parseOutput($this->title);
                }
            } else {
                return \core\Text::parseOutput($this->title);
            }
        } else {
            return $this->title;
        }
    }

    public function getDescription($print=false)
    {
        if (empty($this->description)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }

    public function getListDescription($length=60){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
    }

    public function getFile()
    {
        if (!$this->file_id) {
            return null;
        }
        return Cabinet::getTag($this->file_id);
    }

    public function getThumbnail($link=false)
    {
        if (empty($this->file_id)) {
            return null;
        }

        \core\Core::initModClass('filecabinet', 'Cabinet.php');
        $file = Cabinet::getFile($this->file_id);

        if ($file->isImage(true)) {
            $file->allowImageLink(false);
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(true), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } elseif ($file->isMedia() && $file->_source->isVideo()) {
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(true), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } else {
            return $file->getTag();
        }
    }


    public function view()
    {
        if (!$this->id) {
            \core\Core::errorPage(404);
        }

        $key = new \core\Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();            
        }

        Layout::addPageTitle($this->getTitle());
        $tpl['DEPT_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['DESCRIPTION'] = \core\Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();

        $items = $this->getAllItems();

        if (core\Error::logIfError($items)) {
            $this->vshop->content = dgettext('vshop', 'An error occurred when accessing this dept\'s items.');
            return;
        }

        if ($items) {
            foreach ($items as $item) {
                $tpl['items'][] = $item->viewTpl();
            }
        } else {
            if (Current_User::allow('vshop', 'edit_items'))
                $tpl['EMPTY'] = dgettext('vshop', 'Click on "New item" to start.');
        }

        $key->flag();

        return \core\Template::process($tpl, 'vshop', 'view_dept.tpl');
    }


    public function getAllItems($limit=false)
    {
        \core\Core::initModClass('vshop', 'vShop_Item.php');
        $db = new \core\DB('vshop_items');
        $db->addOrder('title asc');
        $db->addWhere('dept_id', $this->id);
        if ($limit) {
            $db->setLimit((int)$limit);
        }
        $result = $db->getObjects('vShop_Item');
        return $result;
    }


    public function getQtyItems()
    {
        $db = new \core\DB('vshop_items');
        $db->addWhere('dept_id', $this->id);
        $qty = $db->count();
        return $qty;
    }
    

    public function links()
    {
        $links = array();

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_item';
            $vars['dept_id'] = $this->id;
            $links[] = \core\Text::secureLink(dgettext('vshop', 'Add Item'), 'vshop', $vars);
        }
        
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_dept';
            $links[] = \core\Text::secureLink(dgettext('vshop', 'Edit department'), 'vshop', $vars);
        }

        if (is_array(vShop::navLinks())) { 
            $links = array_merge($links, vShop::navLinks());
        }

        if($links)
            return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the related items */
        $db = new \core\DB('vshop_items');
        $db->addWhere('dept_id', $this->id);
        \core\Error::logIfError($db->delete());
        
        /* delete the dept */
        $db = new \core\DB('vshop_depts');
        $db->addWhere('id', $this->id);
        \core\Error::logIfError($db->delete());

        \core\Key::drop($this->key_id);

    }


    public function rowTag()
    {
        $vars['id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_item';
            $vars['dept_id'] = $this->id;
            $label = \core\Icon::show('add', dgettext('vshop', 'Add Item'));
            $links[] = \core\Text::secureLink($label, 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_dept';
            $label = \core\Icon::show('edit');
            $links[] = \core\Text::secureLink($label, 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_dept';
            $js['ADDRESS'] = \core\Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the department %s?'), $this->getTitle());
            $js['LINK'] = \core\Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['THUMB'] = $this->getThumbnail(true);
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['ITEMS'] = $this->getQtyItems();

        if($links)
            $tpl['ACTION'] = implode(' ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new \core\DB('vshop_depts');

        $result = $db->saveObject($this);
        if (core\Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

        $search = new Search($this->key_id);
        $search->resetKeywords();
        $search->addKeywords($this->title);
        $search->addKeywords($this->description);
        $result = $search->save();
        if (core\Error::isError($result)) {
            return $result;
        }

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new \core\Key;
        } else {
            $key = new \core\Key($this->key_id);
            if (core\Error::isError($key->_error)) {
                $key = new \core\Key;
            }
        }

        $key->setModule('vshop');
        $key->setItemName('dept');
        $key->setItemId($this->id);
        $key->setUrl($this->viewLink(true));
        $key->active = 1;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (core\Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new \core\DB('vshop_depts');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            \core\Error::logIfError($db->update());
        }
        return true;
    }


    public function viewLink($bare=false)
    {
                $link = new \core\Link($this->title, 'vshop', array('dept'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }

    }




}

?>