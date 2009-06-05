<?php
/**
    * vlist - phpwebsite module
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
    * @author Verdon Vaillancourt <verdonv at users dot sourceforge dot net>
*/

class vList_Group {

    public $id             = 0;
    public $title          = null;
    public $description    = null;
    public $image_id       = 0;
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
        $db = new PHPWS_DB('vlist_group');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
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
        $this->description = PHPWS_Text::parseInput($description);
    }


    public function setImage_id($image_id)
    {
        $this->image_id = $image_id;
    }


    public function getTitle($print=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->title);
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
            return PHPWS_Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }


    public function getListDescription($length=60){
        if (empty($this->description)) {
            return '';
        }
//        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
        return substr(ltrim(str_replace('<br />', ' ', $this->getDescription(true))), 0, $length) . ' ...';
    }


    public function getQtyMembers()
    {
        $db = new PHPWS_DB('vlist_group_items');
        $db->addWhere('group_id', $this->id);
        if (!Current_User::isUnrestricted('vlist')) {
            $db->addColumn('vlist_group_items.*');
            $db->addColumn('vlist_listing.id');
            $db->addWhere('vlist_listing.id', 'vlist_group_items.listing_id');
            $db->addWhere('vlist_listing.active', 1);
            $db->addGroupBy('vlist_listing.id'); 
        }
        $num = $db->count();
        return $num;
    }


    public function getFile()
    {
        if (!$this->image_id) {
            return null;
        }
        return Cabinet::getTag($this->image_id);
    }


    public function getThumbnail($link=false)
    {
        if (empty($this->image_id)) {
            return null;
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $image = Cabinet::getFile($this->image_id);

        if ($image->isImage(true)) {
            $image->allowImageLink(false);
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(true), $image->getThumbnail());
            } else {
                return $image->getThumbnail();
            }
        } elseif ($image->isMedia() && $image->_source->isVideo()) {
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(), $image->getThumbnail());
            } else {
                return $image->getThumbnail();
            }
        } else {
            return $image->getTag();
        }
    }


    public function deleteLink($icon=false)
    {
        $vars['group'] = $this->id;
        $vars['aop'] = 'delete_group';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('vlist', $vars, true);
        $js['QUESTION'] = sprintf(dgettext('vlist', 'Are you sure you want to delete the group %s?'), $this->getTitle());
        if ($icon) {
            $js['LINK'] = sprintf('<img src="images/mod/vlist/delete.png" title="%s" alt="%s" />',
                                  dgettext('vlist', 'Delete'), dgettext('vlist', 'Delete'));
        } else {
            $js['LINK'] = dgettext('vlist', 'Delete');
        }
        return javascript('confirm', $js);
    }


    public function editLink($label=null, $icon=false)
    {

        if ($icon) {
            $label = sprintf('<img src="images/mod/vlist/edit.png" title="%s" alt="%s" >',
                             dgettext('vlist', 'Edit group'), dgettext('vlist', 'Edit group'));
        } elseif (empty($label)) {
                $label = dgettext('vlist', 'Edit');
        }

        $vars['group'] = $this->id;
        $vars['aop'] = 'edit_group';
        return PHPWS_Text::secureLink($label, 'vlist', $vars);
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('vlist_group');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());
        $db = new PHPWS_DB('vlist_group_items');
        $db->addWhere('group_id', $this->id);
        PHPWS_Error::logIfError($db->delete());
        
    }


    public function rowTag()
    {
        $links = null;

        if (Current_User::allow('vlist', 'settings', null, null, true)){
            $links[] = $this->editLink(null, true);
            $links[] = $this->deleteLink(true);
        }

        $tpl['TITLE'] = $this->viewLink() . ' ('.$this->getQtyMembers().')';
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['THUMB'] = $this->getThumbnail(true);
        if($links)
            $tpl['ACTION'] = implode(' ', $links);
        return $tpl;
    }


    public function groupLinks()
    {
        $links = array();
        if (Current_User::allow('vlist', 'settings', null, null, true)) {
            $vars['group'] = $this->id;
            $vars['aop']  = 'edit_group';
            $links[] = PHPWS_Text::secureLink(dgettext('vlist', 'Edit group'), 'vlist', $vars);
        }
        
        $links = array_merge($links, vList::navLinks());

        if($links)
            return implode(' | ', $links);
    }


    public function save()
    {
        $db = new PHPWS_DB('vlist_group');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

    }


    public function viewLink($bare=false)
    {
        $vars['uop']  = 'view_group';
        $vars['group'] = $this->id;
        $link = new PHPWS_Link($this->title, 'vlist', $vars);
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }


}

?>