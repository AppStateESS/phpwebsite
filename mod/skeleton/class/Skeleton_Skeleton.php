<?php
/**
 * skeleton - phpwebsite module
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

class Skeleton_Skeleton {

    public $id             = 0;
    public $key_id         = 0;
    public $title          = null;
    public $description    = null;
    public $file_id        = 0;
    public $died           = 0;

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
        $db = new PHPWS_DB('skeleton_skeletons');
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

    public function setFile_id($file_id)
    {
        $this->file_id = $file_id;
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

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
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
                return sprintf('<a href="%s">%s</a>', $this->viewLink(), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } else {
            return $file->getTag();
        }
    }

    public function getDied($type=SKELETON_DATE_FORMAT)
    {
        if ($this->died) {
            return strftime($type, $this->died);
        } else {
            return strftime($type, time());
        }
    }


    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $key = new Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        Layout::addPageTitle($this->getTitle());
        $tpl['ITEM_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();

        $bones = $this->getAllBones();

        if (PHPWS_Error::logIfError($bones)) {
            $this->skeleton->content = dgettext('skeleton', 'An error occurred when accessing this skeleton\'s bones.');
            return;
        }

        if ($bones) {
            foreach ($bones as $bone) {
                $tpl['bones'][] = $bone->viewTpl();
            }
        } else {
            if (Current_User::allow('skeleton', 'edit_bone'))
            $tpl['EMPTY'] = dgettext('skeleton', 'Click on "New bone" to start.');
        }

        $key->flag();

        return PHPWS_Template::process($tpl, 'skeleton', 'view_skeleton.tpl');
    }


    public function getAllBones($limit=false)
    {
        PHPWS_Core::initModClass('skeleton', 'Skeleton_Bone.php');
        $db = new PHPWS_DB('skeleton_bones');
        $db->addOrder('title asc');
        $db->addWhere('skeleton_id', $this->id);
        if ($limit) {
            $db->setLimit((int)$limit);
        }
        $result = $db->getObjects('Skeleton_Bone');
        return $result;
    }


    public function getQtyBones()
    {
        $db = new PHPWS_DB('skeleton_bones');
        $db->addWhere('skeleton_id', $this->id);
        $qty = $db->count();
        return $qty;
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('skeleton', 'edit_bone')) {
            $vars['aop']  = 'edit_bone';
            $vars['skeleton_id'] = $this->id;
            $links[] = PHPWS_Text::secureLink(dgettext('skeleton', 'Add Bone'), 'skeleton', $vars);
        }

        if (Current_User::allow('skeleton', 'edit_skeleton')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_skeleton';
            $links[] = PHPWS_Text::secureLink(dgettext('skeleton', 'Edit skeleton'), 'skeleton', $vars);
        }

        $links = array_merge($links, Skeleton::navLinks());

        if($links)
        return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the related bones */
        $db = new PHPWS_DB('skeleton_bones');
        $db->addWhere('skeleton_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* delete the skeleton */
        $db = new PHPWS_DB('skeleton_skeletons');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);

    }


    public function rowTag()
    {
        $vars['id'] = $this->id;
        $links = array();

        if (Current_User::allow('skeleton', 'edit_bone')) {
            $vars['aop']  = 'edit_bone';
            $vars['skeleton_id'] = $this->id;
            $links[] = PHPWS_Text::secureLink(dgettext('skeleton', 'Add Bone'), 'skeleton', $vars);
        }
        if (Current_User::allow('skeleton', 'edit_skeleton')) {
            $vars['aop']  = 'edit_skeleton';
            $links[] = PHPWS_Text::secureLink(dgettext('skeleton', 'Edit'), 'skeleton', $vars);
        }
        if (Current_User::allow('skeleton', 'delete_skeleton')) {
            $vars['aop'] = 'delete_skeleton';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('skeleton', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('skeleton', 'Are you sure you want to delete the skeleton %s?'), $this->getTitle());
            $js['LINK'] = dgettext('skeleton', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['DIED'] = $this->getDied();
        $tpl['BONES'] = $this->getQtyBones();

        if($links)
        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('skeleton_skeletons');

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        $this->saveKey();

        $search = & new Search($this->key_id);
        $search->resetKeywords();
        $search->addKeywords($this->title);
        $search->addKeywords($this->description);
        $result = $search->save();
        if (PEAR::isError($result)) {
            return $result;
        }

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

        $key->setModule('skeleton');
        $key->setItemName('skeleton');
        $key->setItemId($this->id);
        $key->setUrl($this->viewLink(true));
        $key->active = 1;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('skeleton_skeletons');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }


    public function viewLink($bare=false)
    {
        $link = new PHPWS_Link($this->title, 'skeleton', array('skeleton'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }
}

?>