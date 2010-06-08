<?php
/**
 * finc - phpwebsite module
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

class Finc_File {

    var $id             = 0;
    var $key_id         = 0;
    var $title          = null;
    var $path           = null;
    var $description    = null;
    var $active         = 1;
    var $_error         = null;


    function Finc_File($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    function init()
    {
        $db = new \core\DB('finc_file');
        $result = $db->loadObject($this);
        if (core\Error::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }


    function setPath($path)
    {
        $this->path = strip_tags($path);
    }


    function setDescription($description)
    {
        $this->description = \core\Text::parseInput($description);
    }


    function setActive($active)
    {
        $this->active = $active;
    }


    function getTitle($print=false)
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


    function getPath($print=false)
    {
        if (empty($this->path)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->path);
        } else {
            return $this->path;
        }
    }


    function getDescription($print=false)
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


    function getListDescription($length=60){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
    }


    function getContents()
    {
        if (!$this->id) {
            \core\Core::errorPage(404);
        }
        $key = new \core\Key($this->key_id);

        $filename = $this->getPath();
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $contents = dgettext('finc', 'Sorry, the specified file does not exist.');
        }

        $key->flag();
        return $contents;
    }


    function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new \core\DB('finc_file');
        $db->addWhere('id', $this->id);
        \core\Error::logIfError($db->delete());

        \core\Key::drop($this->key_id);

    }


    function rowTag()
    {
        $vars['id'] = $this->id;

        if (Current_User::isUnrestricted('finc')) {
            $vars['aop']  = 'edit_file';
            $label = \core\Icon::show('edit');
            $links[] = \core\Text::secureLink($label, 'finc', $vars);
            if ($this->active) {
                $vars['aop'] = 'deactivate_file';
                $label = \core\Icon::show('active', dgettext('finc', 'Deactivate'));
                $active = \core\Text::secureLink($label, 'finc', $vars);
            } else {
                $vars['aop'] = 'activate_file';
                $label = \core\Icon::show('inactive', dgettext('finc', 'Activate'));
                $active = \core\Text::secureLink($label, 'finc', $vars);
            }
            $links[] = $active;
            $vars['aop'] = 'delete_file';
            $js['ADDRESS'] = \core\Text::linkAddress('finc', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('finc', 'Are you sure you want to delete the file %s?\n\nOnly the databse record will be destroyed. You will still have to physically remove "%s" from your file system.'), $this->getTitle(true), $this->getPath());
            $js['LINK'] = \core\Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['PATH'] = $this->getPath(true);
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        if($links)
            $tpl['ACTION'] = implode(' ', $links);
        return $tpl;
    }


    function save()
    {
        $db = new \core\DB('finc_file');

        $result = $db->saveObject($this);
        if (core\Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

    }


    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new \core\Key;
        } else {
            $key = new \core\Key($this->key_id);
            if (core\Error::isError($key->_error)) {
                $key = new \core\Key;
            }
        }

        $key->setModule('finc');
        $key->setItemName('file');
        $key->setItemId($this->id);
        $key->setUrl($this->viewLink(true));
        $key->active = (int)$this->active;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (core\Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new \core\DB('finc_file');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            \core\Error::logIfError($db->update());
        }
        return true;
    }


    function viewLink($bare=false)
    {
                $link = new \core\Link($this->title, 'finc', array('id'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }

    }



}

?>