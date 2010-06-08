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

class Skeleton_Bone {

    public $id             = 0;
    public $skeleton_id    = 0;
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
        $db = new \core\DB('skeleton_bones');
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

    public function setSkeleton_id($skeleton_id)
    {
        if (!is_numeric($skeleton_id)) {
            return false;
        } else {
            $this->skeleton_id = (int)$skeleton_id;
            return true;
        }
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
            return \core\Text::parseOutput($this->title);
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
                return sprintf('<a href="%s">%s</a>', $this->viewLink(), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } else {
            return $file->getTag();
        }
    }

    public function getSkeleton($print=false)
    {
        if (empty($this->skeleton_id)) {
            return null;
        }

        if ($print) {
            \core\Core::initModClass('skeleton', 'Skeleton_Skeleton.php');
            $skeleton = new Skeleton_Skeleton($this->skeleton_id);
            return $skeleton->viewLink();
        } else {
            return $this->skeleton_id;
        }
    }


    public function view()
    {
        if (!$this->id) {
            \core\Core::errorPage(404);
        }

        Layout::addPageTitle($this->getTitle());
        $tpl['ITEM_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['DESCRIPTION'] = \core\Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();

        return \core\Template::process($tpl, 'skeleton', 'view_bone.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('skeleton', 'edit_bone')) {
            $vars['skeleton_id'] = $this->skeleton_id;
            $vars['bone_id'] = $this->id;
            $vars['aop']  = 'edit_bone';
            $links[] = \core\Text::secureLink(dgettext('skeleton', 'Edit bone'), 'skeleton', $vars);
        }
        $links[] = sprintf(dgettext('skeleton', 'Belongs to: %s'), $this->getSkeleton(true));

        $links = array_merge($links, Skeleton::navLinks());

        if($links)
            return implode(' | ', $links);
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the bone */
        $db = new \core\DB('skeleton_bones');
        $db->addWhere('id', $this->id);
        \core\Error::logIfError($db->delete());

    }


    public function rowTag()
    {
        $vars['bone_id'] = $this->id;
        $vars['skeleton_id'] = $this->skeleton_id;
        $links = array();

        if (Current_User::allow('skeleton', 'edit_bone')) {
            $label = \core\Icon::show('edit');
            $vars['aop']  = 'edit_bone';
            $links[] = \core\Text::secureLink($label, 'skeleton', $vars);
        }
        if (Current_User::allow('skeleton', 'delete_bone')) {
            $vars['aop'] = 'delete_bone';
            $js['ADDRESS'] = \core\Text::linkAddress('skeleton', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('skeleton', 'Are you sure you want to delete the bone %s?'), $this->getTitle());
            $js['LINK'] = \core\Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['SKELETON'] = $this->getSkeleton(true);

        if($links)
            $tpl['ACTION'] = implode(' ', $links);

        return $tpl;
    }


    public function viewTpl()
    {
        $vars['bone_id'] = $this->id;
        $vars['skeleton_id'] = $this->skeleton_id;
        $links = array();

        if (Current_User::allow('skeleton', 'edit_bone')) {
            $label = \core\Icon::show('edit');
            $vars['aop']  = 'edit_bone';
            $links[] = \core\Text::secureLink($label, 'skeleton', $vars);
        }
        if (Current_User::allow('skeleton', 'delete_bone')) {
            $vars['aop'] = 'delete_bone';
            $js['ADDRESS'] = \core\Text::linkAddress('skeleton', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('skeleton', 'Are you sure you want to delete the bone %s?'), $this->getTitle());
            $js['LINK'] = \core\Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['BONE_TITLE'] = $this->viewLink();
        $tpl['BONE_DESCRIPTION'] = $this->getDescription(true);

        if ($this->file_id) {
            $tpl['BONE_THUMBNAIL'] = $this->getThumbnail(true);
        } else {
            $tpl['BONE_THUMBNAIL'] = null;
        }

        if($links)
            $tpl['BONE_LINKS'] = implode(' ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new \core\DB('skeleton_bones');

        $result = $db->saveObject($this);
        if (core\Error::isError($result)) {
            return $result;
        }
    }


    public function viewLink($bare=false)
    {
        $link = new \core\Link($this->title, 'skeleton', array('skeleton'=>$this->skeleton_id, 'bone'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }
}

?>