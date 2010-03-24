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

class vShop_Tax {

    public $id             = 0;
    public $title          = null;
    public $zones          = null;
    public $rate           = 0;

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
        $db = new PHPWS_DB('vshop_taxes');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
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

    public function setZones($zones)
    {
        $this->zones = $zones;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
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

    public function getRate($print=false)
    {
        if (empty($this->rate)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->rate) . ' %';
        } else {
            return $this->rate;
        }
    }

    public function getZones($print=false)
    {
        if (empty($this->zones)) {
            return null;
        }

        if ($print) {
            foreach ($this->zones as $zone) {
                $zones[] = PHPWS_Text::parseOutput($zone);
            }
            return  implode(', ', $zones);
        } else {
            return $this->zones;
        }
    }


    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $tpl['TAX_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['ZONES'] = $this->getZones(true);
        $tpl['ZONES_NOTE'] = dgettext('vshop', 'Tax applies to the following zone(s)');
        $tpl['RATE'] = $this->getRate(true);

        return PHPWS_Template::process($tpl, 'vshop', 'view_tax.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_tax';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit tax'), 'vshop', $vars);
        }

        $links = array_merge($links, vShop::navLinks());

        if($links)
        return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('vshop_taxes');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());
    }


    public function rowTag()
    {
        $vars['id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['aop']  = 'edit_tax';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);

            $vars['aop'] = 'delete_tax';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the tax %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['RATE'] = $this->getRate(true);

        if($links)
        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('vshop_taxes');

        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }
    }


    public function viewLink()
    {
        $vars['aop']  = 'view_tax';
        $vars['tax'] = $this->id;
        return PHPWS_Text::moduleLink(dgettext('vshop', $this->title), 'vshop', $vars);
    }



}

?>