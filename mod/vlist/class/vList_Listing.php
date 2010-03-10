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
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

class vList_Listing {

    public $id              = 0;
    public $key_id          = 0;
    public $created         = 0;
    public $updated         = 0;
    public $owner_id        = 0;
    public $editor_id       = 0;
    public $approved        = 1;
    public $active          = 1;
    public $title           = null;
    public $description     = null;
    public $file_id         = 0;
    public $image_id        = 0;

    public $_error          = null;


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
        $db = new PHPWS_DB('vlist_listing');
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

    public function setImage_id($image_id)
    {
        $this->image_id = $image_id;
    }

    public function getCreated($type=VLIST_DATE_FORMAT)
    {
        if ($this->created) {
            return strftime($type, $this->created);
        } else {
            return null;
        }
    }

    public function getUpdated($type=VLIST_DATE_FORMAT)
    {
        if ($this->updated) {
            return strftime($type, $this->updated);
        } else {
            return null;
        }
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


    public function getFile($link_only=false)
    {
        if (!$this->file_id) {
            return null;
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $file = Cabinet::getFile($this->file_id);

        if ($link_only) {
            $tag = $file->getTag(true, false);
            $link = $tag['TITLE'];
        } else {
            $link = $file->getTag(false, false);
        }

        return $link;
    }

    public function getImage()
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


    public function get_groups($print=false, $nolink=false)
    {
        $db = new PHPWS_DB('vlist_group_items');
        $db->addWhere('listing_id', (int)$this->id);
        $db->addColumn('group_id');
        $result = $db->select('col');
        if ($print) {
            if (empty($result)) {
                $link[] = null;
            } else {
                PHPWS_Core::initModClass('vlist', 'vList_Group.php');
                foreach ($result as $id){
                    $group = new vList_Group($id);
                    if ($nolink) {
                        $link[] = $group->getTitle(true);
                    } else {
                        $link[] = $group->viewLink();
                    }
                }
            }
            $result = $link;
        }
        if (!isset($result[0])) {
            $result = null;
        }
        return $result;
    }


    public function getExtras()
    {
        $extras = null;
        /* get the elements */
        $db = new PHPWS_DB('vlist_element');
        $db->addWhere('active', 1);
        if (!Current_User::allow('vlist')) {
            $db->addWhere('private', 0);
        }
        $db->addOrder('sort asc');
        $result = $db->select();
        if ($result) {

            /* for each element */
            foreach ($result as $element) {
                $id = $element['id'];
                $type = $element['type'];
                $tpl['LABEL'] = $element['title'];
                if ($type == 'Div') {
                    $tpl['DIV'] = '<hr />';
                } else {
                    $tpl['DIV'] = '<br /><br />';
                }

                /* get the items */
                $db = new PHPWS_DB('vlist_element_items');
                $db->addColumn('vlist_element_items.*');
                $db->addWhere('element_id', $id);
                $db->addWhere('listing_id', $this->id);

                if ($type == 'Checkbox' || $type == 'Multiselect') {
                    $db->addColumn('vlist_element_option.label');
                    $db->addWhere('vlist_element_items.option_id', 'vlist_element_option.id');
                    $db->addWhere('vlist_element_option.element_id', $id);
                    $db->addGroupBy('vlist_element_option.id');
                } elseif ($type == 'Dropbox' || $type == 'Radiobutton') {
                    $db->addColumn('vlist_element_option.label');
                    $db->addWhere('vlist_element_items.option_id', 'vlist_element_option.id');
                    $db->addWhere('vlist_element_option.element_id', $id);
                    $db->addGroupBy('vlist_element_option.id');
                }

                $result = $db->select();
                //print_r($result); //exit;
                /* if there's an item */
                if ($result) {
                    $tpl['VALUE'] = null;
                    if ($type == 'Checkbox' || $type == 'Multiselect') {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput($option['label']) . '<br />';
                        }
                    } elseif ($type == 'Dropbox' || $type == 'Radiobutton') {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput($option['label']) . '<br />';
                        }
                    } elseif ($type == 'Link') {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput(sprintf('<a href="%s" title="%s">%s</a>', PHPWS_Text::checkLink($option['value']), dgettext('vlist', 'Visit this listings\'s link.'), $option['value']));
                        }
                    } elseif ($type == 'GPS') {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput(sprintf('<a href="%s" title="%s">%s</a>', 'http://maps.google.com/maps?q='.urlencode($option['value']), dgettext('vlist', 'See listings on google maps.'), sprintf(dgettext('vlist', 'See "%s" on Google Maps'), $option['value'])));
                        }
                    } elseif ($type == 'Email') {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput(sprintf('<a href="%s">%s</a>', 'mailto:' . $option['value'], $option['value']));
                        }
                    } elseif ($type == 'GMap') {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput(sprintf('<a href="%s" title="%s">%s</a>', 'http://maps.google.com/maps?q='.urlencode($option['value']), dgettext('vlist', 'See listings on google maps.'), sprintf(dgettext('vlist', 'See "%s" on Google Maps'), $option['value'])));
                        }
                    } else {
                        foreach ($result as $option) {
                            $tpl['VALUE'] .= PHPWS_Text::parseOutput($option['value']);
                        }
                    }
                } else {
                    $tpl['VALUE'] = null;
                }
                $extras .= PHPWS_Template::processTemplate($tpl, 'vlist', 'listing_extras_view.tpl');
            }
        } else {
            $extras = dgettext('vlist', 'Sorry, no custom elements have been setup.');
        }

        return $extras;
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
        $tpl['TITLE'] = sprintf(dgettext('vlist', '#%s %s'), $this->id, $this->getTitle(true));
        if ($this->get_groups(true) && PHPWS_Settings::get('vlist', 'enable_groups')) {
            $tpl['GROUP_LINKS'] = implode(', ', $this->get_groups(true));
            $tpl['GROUP_LINKS_LABEL'] = PHPWS_Settings::get('vlist', 'groups_title');
        }
        $tpl['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();
        $tpl['IMAGE'] = $this->getImage();
        if (PHPWS_Settings::get('vlist', 'enable_users') && (PHPWS_Settings::get('vlist', 'show_users') || Current_User::allow('vlist'))) {
            $tpl['USER'] = $this->ownerLink();
            $tpl['USER_LABEL'] = dgettext('vlist', 'Listed by, ');
        }
        if (PHPWS_Settings::get('vlist', 'view_created')) {
            $tpl['CREATED'] = $this->getCreated();
            $tpl['CREATED_LABEL'] = dgettext('vlist', 'Created on, ');
        }
        if (PHPWS_Settings::get('vlist', 'view_updated')) {
            $tpl['UPDATED'] = $this->getUpdated();
            $tpl['UPDATED_LABEL'] = dgettext('vlist', 'Updated on, ');
        }

        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
            $tpl['EXTRAS'] = $this->getExtras();
        }

        $key->flag();

        return PHPWS_Template::process($tpl, 'vlist', 'view_listing.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vlist', 'edit_listing')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_listing';
            $links[] = PHPWS_Text::secureLink(dgettext('vlist', 'Edit listing'), 'vlist', $vars);
        }

        $links = array_merge($links, vList::navLinks());

        if($links)
        return implode(' | ', $links);
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* tidy up extras' items */
        $db = new PHPWS_DB('vlist_element_items');
        $db->addWhere('listing_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* tidy up groups' items */
        $db = new PHPWS_DB('vlist_group_items');
        $db->addWhere('listing_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* delete the listing */
        $db = new PHPWS_DB('vlist_listing');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);

    }


    public function deleteLink($icon=false)
    {
        $vars['id'] = $this->id;
        $vars['aop'] = 'delete_listing';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('vlist', $vars, true);
        $js['QUESTION'] = sprintf(dgettext('vlist', 'Are you sure you want to delete the listing %s?'), $this->getTitle());
        if ($icon) {
            $js['LINK'] = sprintf('<img src="%s/mod/vlist/img/delete.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP,
            dgettext('vlist', 'Delete'), dgettext('vlist', 'Delete'));
        } else {
            $js['LINK'] = dgettext('vlist', 'Delete');
        }
        return javascript('confirm', $js);
    }


    public function editLink($label=null, $icon=false)
    {

        if ($icon) {
            $label = sprintf('<img src="%s/mod/vlist/img/edit.png" title="%s" alt="%s" >', PHPWS_SOURCE_HTTP,
            dgettext('vlist', 'Edit listing'), dgettext('vlist', 'Edit listing'));
        } elseif (empty($label)) {
            $label = dgettext('vlist', 'Edit');
        }

        $vars['id'] = $this->id;
        $vars['aop'] = 'edit_listing';
        return PHPWS_Text::secureLink($label, 'vlist', $vars);
    }


    public function approvedLink($label=null, $icon=false)
    {
        $vars['id'] = $this->id;
        if ($this->approved) {
            $vars['aop'] = 'unapprove_listing';
            if ($icon) {
                $label = sprintf('<img src="%s/mod/vlist/img/approved.png" title="%s" alt="%s" >', PHPWS_SOURCE_HTTP,
                dgettext('vlist', 'Unapprove'), dgettext('vlist', 'Unapprove'));
            } elseif (empty($label)) {
                $label = dgettext('vlist', 'Unapprove');
            }
        } else {
            $vars['aop'] = 'approve_listing';
            if ($icon) {
                $label = sprintf('<img src="%s/mod/vlist/img/unapproved.png" title="%s" alt="%s" >', PHPWS_SOURCE_HTTP,
                dgettext('vlist', 'Approve'), dgettext('vlist', 'Approve'));
            } elseif (empty($label)) {
                $label = dgettext('vlist', 'Approve');
            }
        }
        return PHPWS_Text::secureLink($label, 'vlist', $vars);
    }


    public function activeLink($label=null, $icon=false)
    {
        $vars['id'] = $this->id;
        if ($this->active) {
            $vars['aop'] = 'deactivate_listing';
            if ($icon) {
                $label = sprintf('<img src="%s/mod/vlist/img/active.png" title="%s" alt="%s" >', PHPWS_SOURCE_HTTP,
                dgettext('vlist', 'Deactivate'), dgettext('vlist', 'Deactivate'));
            } elseif (empty($label)) {
                $label = dgettext('vlist', 'Deactivate');
            }
        } else {
            $vars['aop'] = 'activate_listing';
            if ($icon) {
                $label = sprintf('<img src="%s/mod/vlist/img/inactive.png" title="%s" alt="%s" >', PHPWS_SOURCE_HTTP,
                dgettext('vlist', 'Activate'), dgettext('vlist', 'Activate'));
            } elseif (empty($label)) {
                $label = dgettext('vlist', 'Activate');
            }
        }
        return PHPWS_Text::secureLink($label, 'vlist', $vars);
    }


    public function rowTag()
    {
        $links = array();

        if (Current_User::allow('vlist', 'edit_listing')) {
            $links[] = $this->editLink(null, true);
        }
        if (Current_User::allow('vlist', 'delete_listing')) {
            $links[] = $this->deleteLink(true);
        }
        if (Current_User::allow('vlist', 'edit_listing')) {
            $links[] = $this->approvedLink(null, true);
            $links[] = $this->activeLink(null, true);
        }

        $tpl['ID'] = $this->id;
        $tpl['TITLE'] = $this->viewLink();

        if (PHPWS_Settings::get('vlist', 'enable_users') && (PHPWS_Settings::get('vlist', 'list_users') || Current_User::allow('vlist'))) {
            $tpl['OWNER'] = $this->ownerLink();
        } else {
            $tpl['OWNER'] = null;
        }

        if (PHPWS_Settings::get('vlist', 'list_created')) {
            $tpl['CREATED'] = $this->getCreated();
        } else {
            $tpl['CREATED'] = null;
        }

        if (PHPWS_Settings::get('vlist', 'list_updated')) {
            $tpl['UPDATED'] = $this->getUpdated();
        } else {
            $tpl['UPDATED'] = null;
        }

        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['THUMB'] = $this->getThumbnail(true);
        if (PHPWS_Settings::get('vlist', 'list_groups') && PHPWS_Settings::get('vlist', 'enable_groups')) {
            if ($this->get_groups(true)) {
                $tpl['GROUP_LINKS'] = implode(', ', $this->get_groups(true));
                $tpl['GROUP_LINKS_LABEL'] = dgettext('vlist', 'Group(s)');
            }
        }
        if ($this->file_id) {
            $tpl['FILE'] = $this->getFile(true);
            $tpl['FILE_LABEL'] = dgettext('vlist', 'Download');
        }

        $tpl['EXTRA_VALUES'] = null;

        if (PHPWS_Settings::get('vlist', 'enable_elements')) {
            $db = new PHPWS_DB('vlist_element');
            $db->addWhere('active', 1);
            if (!Current_User::allow('vlist')) {
                $db->addWhere('private', 0);
            }
            $db->addOrder('sort asc');
            $result = $db->select();
            if ($result) {
                foreach ($result as $element) {
                    $id = $element['id'];
                    $type = $element['type'];
                    /* add flagged extras to list as columns */
                    if ($element['list']) {
                        $tpl['EXTRA_VALUES'] .= '<td>';
                        /* get the items */
                        $db = new PHPWS_DB('vlist_element_items');
                        $db->addColumn('vlist_element_items.*');
                        $db->addWhere('element_id', $id);
                        $db->addWhere('listing_id', $this->id);
                        if ($type == 'Checkbox' || $type == 'Multiselect') {
                            $db->addColumn('vlist_element_option.label');
                            $db->addWhere('vlist_element_items.option_id', 'vlist_element_option.id');
                            $db->addWhere('vlist_element_option.element_id', $id);
                            $db->addGroupBy('vlist_element_option.id');
                        } elseif ($type == 'Dropbox' || $type == 'Radiobutton') {
                            $db->addColumn('vlist_element_option.label');
                            $db->addWhere('vlist_element_items.option_id', 'vlist_element_option.id');
                            $db->addWhere('vlist_element_option.element_id', $id);
                            $db->addGroupBy('vlist_element_option.id');
                        }
                        $result = $db->select();
                        if ($result) {
                            $value = null;
                            if ($type == 'Checkbox' || $type == 'Multiselect') {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput($option['label']);
                                }
                            } elseif ($type == 'Dropbox' || $type == 'Radiobutton') {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput($option['label']);
                                }
                            } elseif ($type == 'Link') {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput(sprintf('<a href="%s" title="%s">%s</a>', PHPWS_Text::checkLink($option['value']), dgettext('vlist', 'Visit this listings\'s link.'), $option['value'])) . '<br />';
                                }
                            } elseif ($type == 'GPS') {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput(sprintf('<a href="%s" title="%s">%s</a>', 'http://maps.google.com/maps?q='.urlencode($option['value']), dgettext('vlist', 'See listings on google maps.'), sprintf(dgettext('vlist', 'See "%s" on Google Maps'), $option['value'])));
                                }
                            } elseif ($type == 'Email') {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput(sprintf('<a href="%s">%s</a>', 'mailto:' . $option['value'], $option['value'])) . '<br />';
                                }
                            } elseif ($type == 'GMap') {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput(sprintf('<a href="%s" title="%s">%s</a>', 'http://maps.google.com/maps?q='.urlencode($option['value']), dgettext('vlist', 'See listings on google maps.'), sprintf(dgettext('vlist', 'See "%s" on Google Maps'), $option['value'])));
                                }
                            } else {
                                foreach ($result as $option) {
                                    $value[] = PHPWS_Text::parseOutput($option['value']);
                                }
                            }
                        } else {
                            $value = null;
                        }

                        if($value) {
                            //                            $tpl['EXTRA_VALUES'][] = implode('<br />', $value);
                            $tpl['EXTRA_VALUES'] .= implode('<br />', $value);
                        }
                        $tpl['EXTRA_VALUES'] .= '</td>';
                    }
                }
            }
        }

        if($links)
        $tpl['ACTION'] = implode('  ', $links);
        //print_r($tpl); //exit;
        return $tpl;
    }


    public function saveListing()
    {
        $this->editor_id = Current_User::getId();
        $this->updated = mktime();

        $db = new PHPWS_DB('vlist_listing');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }


    public function save()
    {
        $this->editor_id = Current_User::getId();
        $this->updated = mktime();
        if (!PHPWS_Settings::get('vlist', 'enable_users')) {
            if (!$this->id) {
                $this->owner_id = Current_User::getId();
            }
        }
        if (!$this->id) {
            $this->created = mktime();
            if (!Current_User::allow('vlist', 'edit_listing')) {
                $this->approved = 0;
                $this->active = 1;
                if (PHPWS_Settings::get('vlist', 'notify_submit') && PHPWS_Settings::get('vlist', 'admin_contact')) {
                    $this->sendNotification(true);
                }
            }
        } else {
            if (PHPWS_Settings::get('vlist', 'notify_edit') && PHPWS_Settings::get('vlist', 'admin_contact') && !Current_User::allow('vlist')) {
                $this->sendNotification(false);
            }
        }

        $db = new PHPWS_DB('vlist_listing');

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        $this->saveKey();

        if (isset($_POST['groups']) && $_POST['groups'][0] !== '0') {
            $db = new PHPWS_DB('vlist_group_items');
            $db->addWhere('listing_id', (int)$this->id);
            PHPWS_Error::logIfError($db->delete());
            foreach ($_POST['groups'] as $var => $val) {
                $this->addItem('group', $val, $this->id);
            }
        } else {
            $db = new PHPWS_DB('vlist_group_items');
            $db->addWhere('listing_id', (int)$this->id);
            PHPWS_Error::logIfError($db->delete());
        }

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

        $key->setModule('vlist');
        $key->setItemName('listing');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_listing');
        $key->setUrl($this->viewLink(true));
        $key->active = (int)$this->active;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('vlist_listing');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }


    /* am I using this - YES JUST ABOVE */
    public function addItem($type, $item_id, $listing_id)
    {
        if ($type =='group') {
            $db = new PHPWS_DB('vlist_group_items');
            $db->addValue('group_id', (int)$item_id);
        }
        $db->addValue('listing_id', (int)$listing_id);
        //print_r($db); exit;
        return $db->insert();
    }


    public function viewLink($bare=false)
    {
        $link = new PHPWS_Link($this->title, 'vlist', array('listing'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }

    public function ownerLink($bare=false)
    {

        if ($this->owner_id == 0) {
            return dgettext('vlist', 'Anonymous');
        }

        if (PHPWS_Core::moduleExists('rolodex')) {
            PHPWS_Core::initModClass('rolodex', 'RDX_Member.php');
            $user = new Rolodex_Member($this->owner_id);
            if ($user) {
                $name = $user->getDisplay_name();
            }
        } else {
            $user = new PHPWS_User($this->owner_id);
            $name = $user->getDisplayName();
        }


        $vars['uop']  = 'view_owner';
        $vars['owner'] = $this->owner_id;

        $link = new PHPWS_Link($name, 'vlist', $vars);
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }

    public function sendNotification($new=true)
    {

        $page_title = $_SESSION['Layout_Settings']->getPageTitle(true);
        $site_contact = PHPWS_User::getUserSetting('site_contact');
        $url = PHPWS_Core::getHomeHttp();
        if ($new) {
            $message = sprintf(dgettext('vlist', 'You have a new %s submission entitled %s waiting for your review at %s.'), PHPWS_Settings::get('vlist', 'module_title'), $this->getTitle(true), $url);
            $subject = sprintf(dgettext('vlist', 'Pending %s Submission'), PHPWS_Settings::get('vlist', 'module_title'));
        } else {
            $message = sprintf(dgettext('vlist', 'The %s listing %s has been modified at %s.'), PHPWS_Settings::get('vlist', 'module_title'), $this->getTitle(true), $url);
            $subject = sprintf(dgettext('vlist', 'Modified %s Listing'), PHPWS_Settings::get('vlist', 'module_title'));
        }

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = new PHPWS_Mail;
        $mail->addSendTo(PHPWS_Settings::get('vlist', 'admin_contact'));
        $mail->setSubject($subject);
        $mail->setFrom(sprintf('%s<%s>', $page_title, $site_contact));
        $mail->setMessageBody($message);

        return $mail->send();

    }

}

?>