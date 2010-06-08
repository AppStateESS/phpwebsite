<?php
/**
 * podcaster - phpwebsite module
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

class Podcaster_Category {

    var $id             = 0;
    var $title          = null;
    var $parent_id      = 0;
    var $_error         = null;


    function Podcaster_Category($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    function init()
    {
        $db = new Core\DB('podcaster_category');
        $result = $db->loadObject($this);
        if (Core\Error::isError($result)) {
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


    function setParent_id($parent_id)
    {
        $this->parent_id = $parent_id;
    }


    function getTitle($print=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            return Core\Text::parseOutput($this->title);
        } else {
            return $this->title;
        }
    }




    function view()
    {
        if (!$this->id) {
            Core\Core::errorPage(404);
        }

        $template['TITLE'] = $this->getTitle(true);
        $template['PARENT'] = $this->getParent(true);

        return Core\Template::process($template, 'podcaster', 'view_category.tpl');

    }


    function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new Core\DB('podcaster_category');
        $db->addWhere('id', $this->id);
        Core\Error::logIfError($db->delete());

        Core\Error::logIfError($db->delete());
    }


    function rowTag()
    {
        $vars['id'] = $this->id;
        $vars2['id'] = $this->id;
        $vars2['uop'] = 'view_rss';

        $links[] = '<a href="./index.php?module=podcaster&amp;id=' . $this->id . '&amp;uop=view_rss"><img src="' . PHPWS_SOURCE_HTTP . 'mod/podcaster/img/rss.gif" width="80" height="15" border="0" alt="' . dgettext('podcaster', 'Subscribe RSS') . '" title="' . dgettext('podcaster', 'Subscribe RSS') . '" /></a>';

        if (Current_User::allow('podcaster', 'edit_episode')){
            $vars['aop']  = 'new_episode';
            $links[] = Core\Text::secureLink(dgettext('podcaster', 'New Episode'), 'podcaster', $vars);
        }

        if (Current_User::allow('podcaster', 'edit_channel')){
            $vars['aop']  = 'edit_channel';
            $links[] = Core\Text::secureLink(dgettext('podcaster', 'Edit'), 'podcaster', $vars);
        }

        if (Current_User::isUnrestricted('podcaster')) {
            if ($this->active) {
                $vars['aop'] = 'deactivate_channel';
                $active = Core\Text::secureLink(dgettext('podcaster', 'Deactivate'), 'podcaster', $vars);
            } else {
                $vars['aop'] = 'activate_channel';
                $active = Core\Text::secureLink(dgettext('podcaster', 'Activate'), 'podcaster', $vars);
            }
            $links[] = $active;
        } else {
            if (Current_User::allow('podcaster'))
                $links[] = $this->active ? dgettext('podcaster', 'Active') : dgettext('podcaster', 'Not Active');
        }

        if (Current_User::allow('podcaster', 'delete_channel')){
            $vars['aop'] = 'delete_channel';
            $js['ADDRESS'] = Core\Text::linkAddress('podcaster', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('podcaster', 'Are you sure you want to delete the channel %s?\nAll related episodes and channel information will be permanently removed.'), $this->getTitle());
            $js['LINK'] = dgettext('podcaster', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DATE_UPDATED'] = $this->getDateUpdated();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        if($links)
            $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }


    function save()
    {
        $db = new Core\DB('podcaster_category');

        $result = $db->saveObject($this);
        if (Core\Error::isError($result)) {
            return $result;
        }
    }

}

?>