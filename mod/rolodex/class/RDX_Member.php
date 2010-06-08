<?php
/**
 * rolodex - phpwebsite module
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

core\Core::requireConfig('rolodex');
core\Core::initModClass('demographics', 'Demographics.php');

class Rolodex_Member extends Demographics_User {

    /* new demographics fields */
    public $tollfree_phone     = null;
    public $business_name      = null;

    /* stock demographics fields */
    public $courtesy_title     = null;
    public $honorific          = null;
    public $first_name         = null;
    public $last_name          = null;
    public $middle_initial     = null;
    public $department         = null;
    public $position_title     = null;
    public $day_phone          = null;
    public $day_phone_ext      = null;
    public $evening_phone      = null;
    public $fax_number         = null;
    public $contact_email      = null;
    public $website            = null;
    public $mailing_address_1  = null;
    public $mailing_address_2  = null;
    public $mailing_city       = null;
    public $mailing_state      = null;
    public $mailing_country    = null;
    public $mailing_zip_code   = null;
    public $business_address_1 = null;
    public $business_address_2 = null;
    public $business_city      = null;
    public $business_state     = null;
    public $business_country   = null;
    public $business_zip_code  = null;

    /* rolodex fields */
    public $user_id            = 0;
    public $key_id             = 0;
    public $allow_comments     = 0;
    public $allow_anon         = 0;
    public $date_created       = 0;
    public $date_updated       = 0;
    public $date_expires       = 0;
    public $description        = null;
    public $image              = null;
    public $privacy            = 0;
    public $email_privacy      = 0;
    public $active             = 1;
    public $custom1            = null;
    public $custom2            = null;
    public $custom3            = null;
    public $custom4            = null;
    public $custom5            = null;
    public $custom6            = null;
    public $custom7            = null;
    public $custom8            = null;

    /* using a second table with demographics */
    public $_table             = 'rolodex_member';

    /* I might need this for the advanced join in list but am unsure */
    public $title              = null;

    /* to hold the categories */
//    public $_categories        = null; // I don't think I need this anymore


    public function __construct($user_id=null)
    {
        if (!$user_id) {
            return;
        }

        $this->user_id = (int)$user_id;
        $this->load();
    }



    public function get_categories($print=false, $nolink=false)
    {
        if ($print) {
            $cat_result = Categories::catList($this->key_id);
            if (empty($cat_result)) {
                $link[] = null;
            } else {
                foreach ($cat_result as $cat){
                    if (!$cat->id) {
                        continue;
                    }
                    if ($nolink) {
                        $link[] = $cat->getTitle();
                    } else {
                        $link[] = \core\Text::moduleLink($cat->getTitle(), "rolodex",  array('uop'=>'view_category', 'category'=>$cat->getId()));
                    }
                }
            }
            $result = $link;
        } else {
            $db = new \core\DB('category_items');
            $db->addWhere('key_id', (int)$this->key_id);
            $db->addColumn('cat_id');
            $result = $db->select('col');
        }
        if (!isset($result[0])) {
            $result = null;
        }
        return $result;
    }


    public function get_locations($print=false, $nolink=false)
    {
        $db = new \core\DB('rolodex_location_items');
        $db->addWhere('member_id', (int)$this->user_id);
        $db->addColumn('location_id');
        $result = $db->select('col');
        if ($print) {
            if (empty($result)) {
                $link[] = null;
            } else {
                \core\Core::initModClass('rolodex', 'RDX_Location.php');
                foreach ($result as $id){
                    $location = new Rolodex_Location($id);
                    if ($nolink) {
                        $link[] = $location->getTitle(true);
                    } else {
                        $link[] = $location->viewLink();
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


    public function get_features($print=false, $nolink=false)
    {
        $db = new \core\DB('rolodex_feature_items');
        $db->addWhere('member_id', (int)$this->user_id);
        $db->addColumn('feature_id');
        $result = $db->select('col');
        if ($print) {
            if (empty($result)) {
                $link[] = null;
            } else {
                \core\Core::initModClass('rolodex', 'RDX_Feature.php');
                foreach ($result as $id){
                    $feature = new Rolodex_Feature($id);
                    if ($nolink) {
                        $link[] = $feature->getTitle(true);
                    } else {
                        $link[] = $feature->viewLink();
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


    public function setImage($img_array)
    {
        $this->image = $img_array;
    }

    public function getImage($print=false)
    {
        if (empty($this->image)) {
            return null;
        }
        if ($print) {
            return sprintf('<img src="images/rolodex/%s" width="%s" height="%s" alt="%s" />', $this->image['name'], $this->image['width'], $this->image['height'], $this->getDisplay_name(true));
        } else {
            return $this->image;
        }
    }


    public function getThumbnail($print=false, $link=false)
    {
        if (empty($this->image)) {
            return null;
        }
        if ($print) {
            $thumb = sprintf('<img src="images/rolodex/%s" width="%s" height="%s" alt="%s" />', $this->image['thumb_name'], $this->image['thumb_width'], $this->image['thumb_height'], $this->getDisplay_name(true));
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(false, true), $thumb);
            } else {
                return $thumb;
            }
        } else {
            return $this->image;
        }
    }


    public function setCourtesy_title($courtesy_title)
    {
        $this->courtesy_title = \core\Text::parseInput($courtesy_title);
    }

    public function getCourtesy_title($print=false)
    {
        if (empty($this->courtesy_title)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->courtesy_title);
        } else {
            return $this->courtesy_title;
        }
    }


    public function setHonorific($honorific)
    {
        $this->honorific = \core\Text::parseInput($honorific);
    }

    public function getHonorific($print=false)
    {
        if (empty($this->honorific)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->honorific);
        } else {
            return $this->honorific;
        }
    }


    public function setFirst_name($first_name)
    {
        $this->first_name = \core\Text::parseInput($first_name);
    }

    public function getFirst_name($print=false)
    {
        if (empty($this->first_name)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->first_name);
        } else {
            return $this->first_name;
        }
    }


    public function setLast_name($last_name)
    {
        $this->last_name = \core\Text::parseInput($last_name);
    }

    public function getLast_name($print=false)
    {
        if (empty($this->last_name)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->last_name);
        } else {
            return $this->last_name;
        }
    }


    public function setMiddle_initial($middle_initial)
    {
        $this->middle_initial = \core\Text::parseInput($middle_initial);
    }

    public function getMiddle_initial($print=false)
    {
        if (empty($this->middle_initial)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->middle_initial);
        } else {
            return $this->middle_initial;
        }
    }


    public function getDisplay_name($print=false)
    {

        $display_name = null;
        if (core\Settings::get('rolodex', 'sortby')) {
            if (!empty($this->last_name)) {
                $display_name .= $this->last_name;
                if (!empty($this->first_name))
                    $display_name .= ', ' . $this->first_name;
                if (!empty($this->middle_initial))
                    $display_name .= ' ' . $this->middle_initial;
            } elseif (!empty($this->first_name)) {
                $display_name .= $this->first_name;
            } else {
                $user = new PHPWS_User($this->user_id);
                $display_name .= $user->getDisplayName();
            }
        } else {
            if (!empty($this->business_name)) {
                $display_name .= $this->business_name;
            } else {
                if (!empty($this->last_name)) {
                    $display_name .= $this->last_name;
                    if (!empty($this->first_name))
                        $display_name .= ', ' . $this->first_name;
                    if (!empty($this->middle_initial))
                        $display_name .= ' ' . $this->middle_initial;
                } elseif (!empty($this->first_name)) {
                    $display_name .= $this->first_name;
                } else {
                    $user = new PHPWS_User($this->user_id);
                    $display_name .= $user->getDisplayName();
                }
            }
        }

        if ($print) {
            return \core\Text::parseOutput($display_name);
        } else {
            return $display_name;
        }
    }


    public function setDepartment($department)
    {
        $this->department = \core\Text::parseInput($department);
    }

    public function getDepartment($print=false)
    {
        if (empty($this->department)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->department);
        } else {
            return $this->department;
        }
    }


    public function setPosition_title($position_title)
    {
        $this->position_title = \core\Text::parseInput($position_title);
    }

    public function getPosition_title($print=false)
    {
        if (empty($this->position_title)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->position_title);
        } else {
            return $this->position_title;
        }
    }


    public function setDay_phone($day_phone)
    {
        $this->day_phone = \core\Text::parseInput($day_phone);
    }

    public function getDay_phone($print=false)
    {
        if (empty($this->day_phone)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->day_phone);
        } else {
            return $this->day_phone;
        }
    }


    public function setDay_phone_ext($day_phone_ext)
    {
        $this->day_phone_ext = \core\Text::parseInput($day_phone_ext);
    }

    public function getDay_phone_ext($print=false)
    {
        if (empty($this->day_phone_ext)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->day_phone_ext);
        } else {
            return $this->day_phone_ext;
        }
    }


    public function setEvening_phone($evening_phone)
    {
        $this->evening_phone = \core\Text::parseInput($evening_phone);
    }

    public function getEvening_phone($print=false)
    {
        if (empty($this->evening_phone)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->evening_phone);
        } else {
            return $this->evening_phone;
        }
    }


    public function setFax_number($fax_number)
    {
        $this->fax_number = \core\Text::parseInput($fax_number);
    }

    public function getFax_number($print=false)
    {
        if (empty($this->fax_number)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->fax_number);
        } else {
            return $this->fax_number;
        }
    }


    public function setTollfree_phone($tollfree_phone)
    {
        $this->tollfree_phone = \core\Text::parseInput($tollfree_phone);
    }

    public function getTollfree_phone($print=false)
    {
        if (empty($this->tollfree_phone)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->tollfree_phone);
        } else {
            return $this->tollfree_phone;
        }
    }


    public function setContact_email($contact_email)
    {
        if (core\Text::isValidInput($contact_email, 'email')) {
            $this->contact_email = $contact_email;
            return true;
        } else {
            return false;
        }
    }

    public function getContact_email($print=false, $icon=false)
    {
        if ($print) {
            if (empty($this->contact_email)) {
                return '';
            }
            if ($icon) {
                if (core\Settings::get('rolodex', 'contact_type')) {
                    return \core\Text::moduleLink(sprintf('<img src="%smod/rolodex/img/email.png" alt="%s" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('rolodex', 'Email'), dgettext('rolodex', 'Email')), "rolodex",  array('uop'=>'message_member', 'id'=>$this->user_id));
                } else {
                    return sprintf('<a class="email" href="mailto:%s"><img src="%smod/rolodex/img/email.png" alt="%s" title="%s" /></a>', $this->contact_email, PHPWS_SOURCE_HTTP, dgettext('rolodex', 'Email'), dgettext('rolodex', 'Email'));
                }
            } else {
                if (core\Settings::get('rolodex', 'contact_type')) {
                    return \core\Text::moduleLink($this->getDisplay_name(true), "rolodex",  array('uop'=>'message_member', 'id'=>$this->user_id));
                } else {
                    return '<a href="mailto:' . $this->contact_email . '" />' . $this->getDisplay_name(true) . '</a>';
                }
            }
        } else {
            if (empty($this->contact_email)) {
                return null;
            }
            return $this->contact_email;
        }
    }


    public function getDisplay_email($print=false, $icon=false)
    {
        if (empty($this->contact_email)) {
            $user = new PHPWS_User($this->user_id);
            $email = $user->email;
        } else {
            $email = $this->contact_email;
        }
        if ($print) {
            if ($icon) {
                if (core\Settings::get('rolodex', 'contact_type')) {
                    return \core\Text::moduleLink(sprintf('<img src="%smod/rolodex/img/email.png" alt="%s" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('rolodex', 'Email'), dgettext('rolodex', 'Email')), "rolodex",  array('uop'=>'message_member', 'id'=>$this->user_id));
                } else {
                    return sprintf('<a class="email" href="mailto:%s"><img src="%smod/rolodex/img/email.png" alt="%s" title="%s" /></a>', $email, PHPWS_SOURCE_HTTP, dgettext('rolodex', 'Email'), dgettext('rolodex', 'Email'));
                }
            } else {
                if (core\Settings::get('rolodex', 'contact_type')) {
                    return \core\Text::moduleLink($this->getDisplay_name(true), "rolodex",  array('uop'=>'message_member', 'id'=>$this->user_id));
                } else {
                    return '<a href="mailto:' . $email . '" />' . $this->getDisplay_name(true) . '</a>';
                }
            }
        } else {
            return $email;
        }
    }


    public function setWebsite($website)
    {
        $website = strip_tags($website);
        if (core\Text::isValidInput($website, 'url')) {
            $this->website = $website;
            return true;
        } else {
            return false;
        }
    }

    public function getWebsite($print=false, $icon=false)
    {
        if ($print) {
            if (empty($this->website)) {
                return '';
            }
            if ($icon) {
                return sprintf('<a class="url" href="%s"><img src="%smod/rolodex/img/website.png" alt="%s" title="%s" /></a>', $this->tidyUrl($this->website), PHPWS_SOURCE_HTTP, dgettext('rolodex', 'Web site'), dgettext('rolodex', 'Web site'));
            } else {
                return sprintf('<a href="%s" title="%s">%s</a>',
                               $this->tidyUrl($this->website),
                               sprintf(dgettext('rolodex', '%s\'s Website'), $this->getDisplay_name(true)),
                               \core\Text::shortenUrl($this->tidyUrl($this->website)));
            }
        } else {
            if (empty($this->website)) {
                return null;
            }
            return $this->website;
        }
    }

    public function tidyUrl($url)
    {
        if (!preg_match('/^(http(s){0,1}:\/\/)/', $url)) {
            $http = 'http://' . $url;
        } else {
            $http = &$url;
        }
        return $http;
    }


    public function setMailing_address_1($mailing_address_1)
    {
        $this->mailing_address_1 = \core\Text::parseInput($mailing_address_1);
    }

    public function getMailing_address_1($print=false)
    {
        if (empty($this->mailing_address_1)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->mailing_address_1);
        } else {
            return $this->mailing_address_1;
        }
    }


    public function setMailing_address_2($mailing_address_2)
    {
        $this->mailing_address_2 = \core\Text::parseInput($mailing_address_2);
    }

    public function getMailing_address_2($print=false)
    {
        if (empty($this->mailing_address_2)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->mailing_address_2);
        } else {
            return $this->mailing_address_2;
        }
    }


    public function setMailing_city($mailing_city)
    {
        $this->mailing_city = \core\Text::parseInput($mailing_city);
    }

    public function getMailing_city($print=false)
    {
        if (empty($this->mailing_city)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->mailing_city);
        } else {
            return $this->mailing_city;
        }
    }


    public function setMailing_state($mailing_state)
    {
        $this->mailing_state = \core\Text::parseInput($mailing_state);
    }

    public function getMailing_state($print=false)
    {
        if (empty($this->mailing_state)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->mailing_state);
        } else {
            return $this->mailing_state;
        }
    }


    public function setMailing_country($mailing_country)
    {
        $this->mailing_country = \core\Text::parseInput($mailing_country);
    }

    public function getMailing_country($print=false)
    {
        if (empty($this->mailing_country)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->mailing_country);
        } else {
            return $this->mailing_country;
        }
    }


    public function setMailing_zip_code($mailing_zip_code)
    {
        $this->mailing_zip_code = \core\Text::parseInput($mailing_zip_code);
    }

    public function getMailing_zip_code($print=false)
    {
        if (empty($this->mailing_zip_code)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->mailing_zip_code);
        } else {
            return $this->mailing_zip_code;
        }
    }


    public function setBusiness_name($business_name)
    {
        $this->business_name = \core\Text::parseInput($business_name);
    }

    public function getBusiness_name($print=false)
    {
        if (empty($this->business_name)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_name);
        } else {
            return $this->business_name;
        }
    }


    public function setBusiness_address_1($business_address_1)
    {
        $this->business_address_1 = \core\Text::parseInput($business_address_1);
    }

    public function getBusiness_address_1($print=false)
    {
        if (empty($this->business_address_1)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_address_1);
        } else {
            return $this->business_address_1;
        }
    }


    public function setBusiness_address_2($business_address_2)
    {
        $this->business_address_2 = \core\Text::parseInput($business_address_2);
    }

    public function getBusiness_address_2($print=false)
    {
        if (empty($this->business_address_2)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_address_2);
        } else {
            return $this->business_address_2;
        }
    }


    public function setBusiness_city($business_city)
    {
        $this->business_city = \core\Text::parseInput($business_city);
    }

    public function getBusiness_city($print=false)
    {
        if (empty($this->business_city)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_city);
        } else {
            return $this->business_city;
        }
    }


    public function setBusiness_state($business_state)
    {
        $this->business_state = \core\Text::parseInput($business_state);
    }

    public function getBusiness_state($print=false)
    {
        if (empty($this->business_state)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_state);
        } else {
            return $this->business_state;
        }
    }


    public function setBusiness_country($business_country)
    {
        $this->business_country = \core\Text::parseInput($business_country);
    }

    public function getBusiness_country($print=false)
    {
        if (empty($this->business_country)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_country);
        } else {
            return $this->business_country;
        }
    }


    public function setBusiness_zip_code($business_zip_code)
    {
        $this->business_zip_code = \core\Text::parseInput($business_zip_code);
    }

    public function getBusiness_zip_code($print=false)
    {
        if (empty($this->business_zip_code)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->business_zip_code);
        } else {
            return $this->business_zip_code;
        }
    }


    public function setAllow_comments($allow_comments)
    {
        $this->allow_comments = $allow_comments;
    }


    public function setAllow_anon($allow_anon)
    {
        $this->allow_anon = $allow_anon;
    }


    public function getDate_created($print=false, $format=null)
    {
        if (empty($this->date_created)) {
            return null;
        }

        if ($print) {
            if ($format) {
                $format = $format;
            } else {
                $format = RDX_DATE_FORMAT;
            }
            return strftime($format, $this->date_created);
        } else {
            return $this->date_created;
        }
    }


    public function getDate_updated($print=false, $format=null)
    {
        if (empty($this->date_updated)) {
            return null;
        }

        if ($print) {
            if ($format) {
                $format = $format;
            } else {
                $format = RDX_DATE_FORMAT;
            }
            return strftime($format, $this->date_updated);
        } else {
            return $this->date_updated;
        }
    }


    public function getDate_expires($print=false, $format=null)
    {
        if (empty($this->date_expires)) {
            return null;
        }

        if ($print) {
            if ($format) {
                $format = $format;
            } else {
                $format = RDX_DATE_FORMAT;
            }
            return strftime($format, $this->date_expires);
        } else {
            return $this->date_expires;
        }
    }


    public function setDescription($description)
    {
        $this->description = \core\Text::parseInput($description);
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


    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }


    public function setEmail_privacy($email_privacy)
    {
        $this->email_privacy = $email_privacy;
    }


    public function setActive($active)
    {
        $this->active = $active;
    }


    public function setCustom1($custom1)
    {
        $this->custom1 = \core\Text::parseInput($custom1);
    }

    public function getCustom1($print=false)
    {
        if (empty($this->custom1)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom1);
        } else {
            return $this->custom1;
        }
    }


    public function setCustom2($custom2)
    {
        $this->custom2 = \core\Text::parseInput($custom2);
    }

    public function getCustom2($print=false)
    {
        if (empty($this->custom2)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom2);
        } else {
            return $this->custom2;
        }
    }


    public function setCustom3($custom3)
    {
        $this->custom3 = \core\Text::parseInput($custom3);
    }

    public function getCustom3($print=false)
    {
        if (empty($this->custom3)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom3);
        } else {
            return $this->custom3;
        }
    }


    public function setCustom4($custom4)
    {
        $this->custom4 = \core\Text::parseInput($custom4);
    }

    public function getCustom4($print=false)
    {
        if (empty($this->custom4)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom4);
        } else {
            return $this->custom4;
        }
    }


    public function setCustom5($custom5)
    {
        $this->custom5 = \core\Text::parseInput($custom5);
    }

    public function getCustom5($print=false)
    {
        if (empty($this->custom5)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom5);
        } else {
            return $this->custom5;
        }
    }


    public function setCustom6($custom6)
    {
        $this->custom6 = \core\Text::parseInput($custom6);
    }

    public function getCustom6($print=false)
    {
        if (empty($this->custom6)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom6);
        } else {
            return $this->custom6;
        }
    }


    public function setCustom7($custom7)
    {
        $this->custom7 = \core\Text::parseInput($custom7);
    }

    public function getCustom7($print=false)
    {
        if (empty($this->custom7)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom7);
        } else {
            return $this->custom7;
        }
    }


    public function setCustom8($custom8)
    {
        $this->custom8 = \core\Text::parseInput($custom8);
    }

    public function getCustom8($print=false)
    {
        if (empty($this->custom8)) {
            return null;
        }

        if ($print) {
            return \core\Text::parseOutput($this->custom8);
        } else {
            return $this->custom8;
        }
    }


    public function memberLinks()
    {
        $links = array();
        if ($this->editLink()) {
            $links[] = $this->editLink();
        }

        $links = array_merge($links, Rolodex::navLinks());

        if($links)
            return implode(' | ', $links);
    }


    public function view()
    {
        if (!$this->user_id) {
            \core\Core::errorPage(404);
        }

        $key = new \core\Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        if (Current_User::allow('rolodex', 'edit_member') || $this->isMine()) {
            if ($this->isMine()) {
                $vars['user_id'] = $this->user_id;
                $vars['uop']  = 'edit_member';
                MiniAdmin::add('rolodex', array(core\Text::secureLink(dgettext('rolodex', 'Edit my profile'), 'rolodex', $vars)));
            } else {
                $vars['user_id'] = $this->user_id;
                $vars['aop']  = 'edit_member';
                MiniAdmin::add('rolodex', array(core\Text::secureLink(dgettext('rolodex', 'Edit member'), 'rolodex', $vars)));
            }
        }
        $tpl['MEMBER_LINKS'] = $this->memberLinks();

        Layout::addPageTitle($this->getDisplay_name(true));
        $tpl['TITLE'] = $this->getDisplay_name(true);
        $tpl['DESCRIPTION'] = \core\Text::parseTag($this->getDescription(true));
        $tpl['IMAGE'] = $this->getImage(true);

        if ($this->get_categories(true) && \core\Settings::get('rolodex', 'use_categories')) {
            $tpl['CATEGORY_LINKS'] = implode(', ', $this->get_categories(true));
            $tpl['CATEGORY_LINKS_LABEL'] = dgettext('rolodex', 'Category(s)');
        }

        if ($this->get_locations(true) && \core\Settings::get('rolodex', 'use_locations')) {
            $tpl['LOCATION_LINKS'] = implode(', ', $this->get_locations(true));
            $tpl['LOCATION_LINKS_LABEL'] = dgettext('rolodex', 'Location(s)');
        }
        if ($this->get_features(true) && \core\Settings::get('rolodex', 'use_features')) {
            $tpl['FEATURE_LINKS'] = implode(', ', $this->get_features(true));
            $tpl['FEATURE_LINKS_LABEL'] = dgettext('rolodex', 'Feature(s)');
        }

        $tpl['PROFILE_GROUP_LABEL'] = dgettext('rolodex', 'Profile');
        $tpl['COURTESY_TITLE'] = $this->getCourtesy_title(true);
        $tpl['FIRST_NAME'] = $this->getFirst_name(true);
        $tpl['LAST_NAME'] = $this->getLast_name(true);
        $tpl['MIDDLE_INITIAL'] = $this->getMiddle_initial(true);
        $tpl['HONORIFIC'] = $this->getHonorific(true);
        $tpl['BUSINESS_NAME'] = $this->getBusiness_name(true);
        $tpl['DEPARTMENT'] = $this->getDepartment(true);
        $tpl['POSITION_TITLE'] = $this->getPosition_title(true);

        $tpl['CONTACT_GROUP_LABEL'] = dgettext('rolodex', 'Contact information');

        if ($this->isDataVisible('privacy_bus_phone')) {
            if ($this->getDay_phone()) {
                $tpl['DAY_PHONE_LABEL'] = dgettext('rolodex', 'Business phone');
                $tpl['DAY_PHONE'] = $this->getDay_phone(true);
            }
            if ($this->getDay_phone_ext()) {
                $tpl['DAY_PHONE_EXT'] = $this->getDay_phone_ext(true);
                $tpl['DAY_PHONE_EXT_LABEL'] = dgettext('rolodex', 'Ext');
            }
            if ($this->getFax_number()) {
                $tpl['FAX_NUMBER'] = $this->getFax_number(true);
                $tpl['FAX_NUMBER_LABEL'] = dgettext('rolodex', 'Fax');
            }
            if ($this->getTollfree_phone()) {
                $tpl['TOLLFREE_PHONE'] = $this->getTollfree_phone(true);
                $tpl['TOLLFREE_PHONE_LABEL'] = dgettext('rolodex', 'Tollfree');
            }
        }
        if ($this->isDataVisible('privacy_home_phone')) {
            if ($this->getEvening_phone()) {
                $tpl['EVENING_PHONE_LABEL'] = dgettext('rolodex', 'Home phone');
                $tpl['EVENING_PHONE'] = $this->getEvening_phone(true);
            }
        }

        if ($this->isDataVisible('privacy_contact')) {
            if ($this->email_privacy && !Current_User::allow('rolodex', 'view_privates')) {
                $tpl['CONTACT_EMAIL'] = null;
                $tpl['CONTACT_EMAIL_LABEL'] = null;
            } else {
                $tpl['CONTACT_EMAIL'] = $this->getDisplay_email(true);
                if (Current_User::allow('rolodex', 'view_privates')) {
                    $tpl['CONTACT_EMAIL'] .= ' (' . $this->getDisplay_email() . ')';
                }
                $tpl['CONTACT_EMAIL_LABEL'] = dgettext('rolodex', 'E-mail');
            }
        }

        if ($this->isDataVisible('privacy_web')) {
            if ($this->getWebsite()) {
                $tpl['WEBSITE'] = $this->getWebsite(true);
                $tpl['WEBSITE_LABEL'] = dgettext('rolodex', 'Web');
            }
        }

        if ($this->isDataVisible('privacy_home')) {
            $tpl['MAILING_ADDRESS_1'] = $this->getMailing_address_1(true);
            $tpl['MAILING_ADDRESS_2'] = $this->getMailing_address_2(true);
            $tpl['MAILING_CITY'] = $this->getMailing_city(true);
            $tpl['MAILING_STATE'] = $this->getMailing_state(true);
            $tpl['MAILING_COUNTRY'] = $this->getMailing_country(true);
            $tpl['MAILING_ZIP_CODE'] = $this->getMailing_zip_code(true);
            if ($this->getMailing_address_1() || $this->getMailing_address_2() || $this->getMailing_city() || $this->getMailing_state() || $this->getMailing_country() || $this->getMailing_zip_code()) {
                $tpl['HOME_LABEL'] = dgettext('rolodex', 'Home address');
            }
            $tpl['MAILING_GOOGLE_MAP'] = $this->getGoogleMap('mailing');
        }

        if ($this->isDataVisible('privacy_business')) {
            $tpl['BUSINESS_ADDRESS_1'] = $this->getBusiness_address_1(true);
            $tpl['BUSINESS_ADDRESS_2'] = $this->getBusiness_address_2(true);
            $tpl['BUSINESS_CITY'] = $this->getBusiness_city(true);
            $tpl['BUSINESS_STATE'] = $this->getBusiness_state(true);
            $tpl['BUSINESS_COUNTRY'] = $this->getBusiness_country(true);
            $tpl['BUSINESS_ZIP_CODE'] = $this->getBusiness_zip_code(true);
            if ($this->getBusiness_address_1() || $this->getBusiness_address_2() || $this->getBusiness_city() || $this->getBusiness_state() || $this->getBusiness_country() || $this->getBusiness_zip_code()) {
                $tpl['BUSINESS_LABEL'] = dgettext('rolodex', 'Business address');
            }
            $tpl['BUSINESS_GOOGLE_MAP'] = $this->getGoogleMap('business');
        }

        if (Current_User::allow('rolodex', 'edit_member') || $this->isMine()) {
            $tpl['DATE_CREATED_LABEL'] = dgettext('rolodex', 'Creation date');
            $tpl['DATE_CREATED'] = $this->getDate_created(true);
            $tpl['DATE_UPDATED_LABEL'] = dgettext('rolodex', 'Updated date');
            $tpl['DATE_UPDATED'] = $this->getDate_updated(true);
            if (core\Settings::get('rolodex', 'enable_expiry')) {
                $tpl['DATE_EXPIRES_LABEL'] = dgettext('rolodex', 'Expiry date');
                $tpl['DATE_EXPIRES'] = $this->getDate_expires(true);
            }
        }

        if (core\Settings::get('rolodex', 'custom1_name')) {
            if ($this->getCustom1()) {
                $tpl['CUSTOM1_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom1_name'));
                $tpl['CUSTOM1'] = $this->getCustom1(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom2_name')) {
            if ($this->getCustom2()) {
                $tpl['CUSTOM2_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom2_name'));
                $tpl['CUSTOM2'] = $this->getCustom2(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom3_name')) {
            if ($this->getCustom3()) {
                $tpl['CUSTOM3_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom3_name'));
                $tpl['CUSTOM3'] = $this->getCustom3(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom4_name')) {
            if ($this->getCustom4()) {
                $tpl['CUSTOM4_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom4_name'));
                $tpl['CUSTOM4'] = $this->getCustom4(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom5_name') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom5()) {
                $tpl['CUSTOM5_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom5_name'));
                $tpl['CUSTOM5'] = $this->getCustom5(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom6_name') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom6()) {
                $tpl['CUSTOM6_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom6_name'));
                $tpl['CUSTOM6'] = $this->getCustom6(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom7_name') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom7()) {
                $tpl['CUSTOM7_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom7_name'));
                $tpl['CUSTOM7'] = $this->getCustom7(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom8_name') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom8()) {
                $tpl['CUSTOM8_LABEL'] = \core\Text::parseOutput(core\Settings::get('rolodex', 'custom8_name'));
                $tpl['CUSTOM8'] = $this->getCustom8(true);
            }
        }
        if (core\Settings::get('rolodex', 'custom1_name') || \core\Settings::get('rolodex', 'custom2_name') || \core\Settings::get('rolodex', 'custom3_name') || \core\Settings::get('rolodex', 'custom4_name') || \core\Settings::get('rolodex', 'custom5_name') || \core\Settings::get('rolodex', 'custom6_name') || \core\Settings::get('rolodex', 'custom7_name') || \core\Settings::get('rolodex', 'custom8_name')) {
            $tpl['META_GROUP_LABEL'] = dgettext('rolodex', 'Extra');
        }


        \core\Core::initModClass('comments', 'Comments.php');
        if ($this->allow_comments) {
            $comments = Comments::getThread($key);
            if ($comments) {
                $tpl['COMMENTS'] = $comments->view();
            }
        }
        $key->flag();

        return \core\Template::process($tpl, 'rolodex', 'view_member.tpl');
    }


    public function rowTag()
    {
        $vars['id'] = $this->user_id;
        $vars2['id'] = $this->user_id;
        $vars2['uop'] = 'view_rss';
        $links = null;

        if (Current_User::allow('rolodex', 'edit_member')){
            $vars['aop']  = 'edit_member';
            $label = \core\Icon::show('edit');
            $links[] = \core\Text::secureLink($label, 'rolodex', $vars);
        }

        if (Current_User::isUnrestricted('rolodex')) {
            if ($this->active) {
                $vars['aop'] = 'deactivate_member';
                $label = \core\Icon::show('active', dgettext('rolodex', 'Deactivate'));
                $active = \core\Text::secureLink($label, 'rolodex', $vars);
            } else {
                $vars['aop'] = 'activate_member';
                $label = \core\Icon::show('inactive', dgettext('rolodex', 'Activate'));
                $active = \core\Text::secureLink($label, 'rolodex', $vars);
            }
            $links[] = $active;
        } else {
            if (Current_User::allow('rolodex'))
                $links[] = $this->active ? \core\Icon::show('active') : \core\Icon::show('inactive');
        }

        if (Current_User::allow('rolodex', 'delete_member')){
            $vars['aop'] = 'delete_member';
            $js['ADDRESS'] = \core\Text::linkAddress('rolodex', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('rolodex', 'Are you sure you want to delete the member %s?\nDemographic information will be retained for other modules, but all Rolodex member information will be permanently removed.'), addslashes(htmlspecialchars_decode($this->getDisplay_name(true),ENT_QUOTES)));
            $js['LINK'] = \core\Icon::show('delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink(true);
        $tpl['DATE_UPDATED'] = $this->getDate_updated(true, '%a %b %e %Y');
        if ($this->description) {
            $tpl['DESCRIPTION'] = $this->getListDescription(180);
        }
        $tpl['THUMBNAIL'] = $this->getThumbnail(true, true);
        if ($this->isDataVisible('privacy_contact')) {
//            if ($this->email_privacy && !Current_User::getId()) {
            if ($this->email_privacy && !Current_User::allow('rolodex', 'view_privates')) {
                $tpl['CONTACT_EMAIL_LINK'] = '';
            } else {
                $tpl['CONTACT_EMAIL_LINK'] = $this->getDisplay_email(true, true);
            }
        }
        if ($this->isDataVisible('privacy_web')) {
            $tpl['WEBSITE_LINK'] = $this->getWebsite(true, true);
        }
        if (core\Settings::get('rolodex', 'list_address') == 1 || \core\Settings::get('rolodex', 'list_address') == 3) {
            if ($this->isDataVisible('privacy_business')) {
                $tpl['B_ADDRESS_1'] = $this->getBusiness_address_1(true);
                $tpl['B_ADDRESS_2'] = $this->getBusiness_address_2(true);
                $tpl['B_CITY'] = $this->getBusiness_city(true);
                $tpl['B_STATE'] = $this->getBusiness_state(true);
                $tpl['B_COUNTRY'] = $this->getBusiness_country(true);
                $tpl['B_ZIP_CODE'] = $this->getBusiness_zip_code(true);
                if ($this->getBusiness_address_1() || $this->getBusiness_address_2() || $this->getBusiness_city() || $this->getBusiness_state() || $this->getBusiness_country() || $this->getBusiness_zip_code()) {
                    $tpl['B_LABEL'] = dgettext('rolodex', 'Business address');
                }
                $tpl['B_GOOGLE_MAP'] = $this->getGoogleMap('business');
            }
        }
        if (core\Settings::get('rolodex', 'list_address') == 2 || \core\Settings::get('rolodex', 'list_address') == 3) {
            if ($this->isDataVisible('privacy_home')) {
                $tpl['H_ADDRESS_1'] = $this->getMailing_address_1(true);
                $tpl['H_ADDRESS_2'] = $this->getMailing_address_2(true);
                $tpl['H_CITY'] = $this->getMailing_city(true);
                $tpl['H_STATE'] = $this->getMailing_state(true);
                $tpl['H_COUNTRY'] = $this->getMailing_country(true);
                $tpl['H_ZIP_CODE'] = $this->getMailing_zip_code(true);
                if ($this->getMailing_address_1() || $this->getMailing_address_2() || $this->getMailing_city() || $this->getMailing_state() || $this->getMailing_country() || $this->getMailing_zip_code()) {
                    $tpl['H_LABEL'] = dgettext('rolodex', 'Home address');
                }
                $tpl['H_GOOGLE_MAP'] = $this->getGoogleMap('mailing');
            }
        }

        if (core\Settings::get('rolodex', 'list_phone')) {
            if ($this->isDataVisible('privacy_bus_phone')) {
                if ($this->getDay_phone()) {
                    $tpl['LIST_DAY_PHONE_LABEL'] = dgettext('rolodex', 'Business phone');
                    $tpl['LIST_DAY_PHONE'] = $this->getDay_phone(true);
                }
                if ($this->getDay_phone_ext()) {
                    $tpl['LIST_DAY_PHONE_EXT'] = $this->getDay_phone_ext(true);
                    $tpl['LIST_DAY_PHONE_EXT_LABEL'] = dgettext('rolodex', 'Ext');
                }
                if ($this->getFax_number()) {
                    $tpl['LIST_FAX_NUMBER'] = $this->getFax_number(true);
                    $tpl['LIST_FAX_NUMBER_LABEL'] = dgettext('rolodex', 'Fax');
                }
                if ($this->getTollfree_phone()) {
                    $tpl['LIST_TOLLFREE_PHONE'] = $this->getTollfree_phone(true);
                    $tpl['LIST_TOLLFREE_PHONE_LABEL'] = dgettext('rolodex', 'Tollfree');
                }
            }
            if ($this->isDataVisible('privacy_home_phone')) {
                if ($this->getEvening_phone()) {
                    $tpl['LIST_EVENING_PHONE_LABEL'] = dgettext('rolodex', 'Home phone');
                    $tpl['LIST_EVENING_PHONE'] = $this->getEvening_phone(true);
                }
            }
        }

        if (core\Settings::get('rolodex', 'list_categories') && \core\Settings::get('rolodex', 'use_categories')) {
            if ($this->get_categories(true)) {
                $tpl['CATEGORY_LINKS'] = implode(', ', $this->get_categories(true));
                $tpl['CATEGORY_LINKS_LABEL'] = dgettext('rolodex', 'Category(s)');
            }
        }

        if (core\Settings::get('rolodex', 'list_locations') && \core\Settings::get('rolodex', 'use_locations')) {
            if ($this->get_locations(true)) {
                $tpl['LOCATION_LINKS'] = implode(', ', $this->get_locations(true));
                $tpl['LOCATION_LINKS_LABEL'] = dgettext('rolodex', 'Location(s)');
            }
        }

        if (core\Settings::get('rolodex', 'list_features') && \core\Settings::get('rolodex', 'use_features')) {
            if ($this->get_features(true)) {
                $tpl['FEATURE_LINKS'] = implode(', ', $this->get_features(true));
                $tpl['FEATURE_LINKS_LABEL'] = dgettext('rolodex', 'Feature(s)');
            }
        }

        if (core\Settings::get('rolodex', 'custom1_name') && \core\Settings::get('rolodex', 'custom1_list')) {
            if ($this->getCustom1()) {
                $tpl['CUSTOM1'] = $this->getCustom1(true);
            } else {
                $tpl['CUSTOM1'] = '';
            }
        } else {
            $tpl['CUSTOM1'] = null;
        }
        if (core\Settings::get('rolodex', 'custom2_name') && \core\Settings::get('rolodex', 'custom2_list')) {
            if ($this->getCustom2()) {
                $tpl['CUSTOM2'] = $this->getCustom2(true);
            } else {
                $tpl['CUSTOM2'] = '';
            }
        } else {
            $tpl['CUSTOM2'] = null;
        }
        if (core\Settings::get('rolodex', 'custom3_name') && \core\Settings::get('rolodex', 'custom3_list')) {
            if ($this->getCustom3()) {
                $tpl['CUSTOM3'] = $this->getCustom3(true);
            } else {
                $tpl['CUSTOM3'] = '';
            }
        } else {
            $tpl['CUSTOM3'] = null;
        }
        if (core\Settings::get('rolodex', 'custom4_name') && \core\Settings::get('rolodex', 'custom4_list')) {
            if ($this->getCustom4()) {
                $tpl['CUSTOM4'] = $this->getCustom4(true);
            } else {
                $tpl['CUSTOM4'] = '';
            }
        } else {
            $tpl['CUSTOM4'] = null;
        }
        if (core\Settings::get('rolodex', 'custom5_name') && \core\Settings::get('rolodex', 'custom5_list') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom5()) {
                $tpl['CUSTOM5'] = $this->getCustom5(true);
            } else {
                $tpl['CUSTOM5'] = '';
            }
        } else {
            $tpl['CUSTOM5'] = null;
        }
        if (core\Settings::get('rolodex', 'custom6_name') && \core\Settings::get('rolodex', 'custom6_list') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom6()) {
                $tpl['CUSTOM6'] = $this->getCustom6(true);
            } else {
                $tpl['CUSTOM6'] = '';
            }
        } else {
            $tpl['CUSTOM6'] = null;
        }
        if (core\Settings::get('rolodex', 'custom7_name') && \core\Settings::get('rolodex', 'custom7_list') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom7()) {
                $tpl['CUSTOM7'] = $this->getCustom7(true);
            } else {
                $tpl['CUSTOM7'] = '';
            }
        } else {
            $tpl['CUSTOM7'] = null;
        }
        if (core\Settings::get('rolodex', 'custom8_name') && \core\Settings::get('rolodex', 'custom8_list') && Current_User::allow('rolodex', 'view_privates')) {
            if ($this->getCustom8()) {
                $tpl['CUSTOM8'] = $this->getCustom8(true);
            } else {
                $tpl['CUSTOM8'] = '';
            }
        } else {
            $tpl['CUSTOM8'] = null;
        }

        if($links)
            $tpl['ACTION'] = implode(' ', $links);
        return $tpl;
    }


    public function viewLink($icon=false, $bare=false)
    {

        if ($icon) {
            $name = $this->getDisplay_name(true) . sprintf('&#160<img src="%smod/rolodex/img/view.gif" alt="%s" title="%s" />', PHPWS_SOURCE_HTTP, dgettext('rolodex', 'View Details'), dgettext('rolodex', 'View Details'));
        } else {
            $name = $this->getDisplay_name(true);
        }

                $link = new \core\Link($name, 'rolodex', array('id'=>$this->user_id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }

    }


    public function editLink()
    {
        $link = null;
        if (Current_User::allow('rolodex', 'edit_member') || $this->isMine()) {
            if ($this->isMine()) {
                $vars['user_id'] = $this->user_id;
                $vars['uop']  = 'edit_member';
                $link = \core\Text::secureLink(dgettext('rolodex', 'Edit my profile'), 'rolodex', $vars);
            } else {
                $vars['user_id'] = $this->user_id;
                $vars['aop']  = 'edit_member';
                $link = \core\Text::secureLink(dgettext('rolodex', 'Edit member'), 'rolodex', $vars);
            }
        }
        return $link;
    }


    public function editUserLink()
    {
        $link = null;
        if (Current_User::allow('users', 'edit_users')) {
            $user = new PHPWS_User($this->user_id);
            $vars['user_id'] = $this->user_id;
            $vars['action']  = 'admin';
            $vars['command']  = 'editUser';
            $link = \core\Text::secureLink(sprintf(dgettext('rolodex', 'Edit email and password for %s (%s)'), $user->display_name, $user->username), 'users', $vars);
        }
        return $link;
    }


    public function activateUserLink()
    {
        $link = null;
        if (Current_User::allow('users', 'edit_users')) {
            $user = new PHPWS_User($this->user_id);
            $vars['user_id'] = $this->user_id;
            $vars['action']  = 'admin';
            $vars['command']  = 'activateUser';
            $link = \core\Text::secureLink(sprintf(dgettext('rolodex', 'Activate user %s (%s)'), $user->display_name, $user->username), 'users', $vars);
        }
        return $link;
    }


    public function deactivateUserLink()
    {
        $link = null;
        if (Current_User::allow('users', 'edit_users')) {
            $user = new PHPWS_User($this->user_id);
            $vars['user_id'] = $this->user_id;
            $vars['action']  = 'admin';
            $vars['command']  = 'deactivateUser';
            $link = \core\Text::secureLink(sprintf(dgettext('rolodex', 'Deactivate user %s (%s)'), $user->display_name, $user->username), 'users', $vars);
        }
        return $link;
    }


    public function activeLink()
    {
        $link = null;
        if (Current_User::allow('users', 'edit_users')) {
            $user = new PHPWS_User($this->user_id);
            if ($user->active) {
                $vars['user_id'] = $this->user_id;
                $vars['action']  = 'admin';
                $vars['command']  = 'deactivateUser';
                $link = \core\Text::secureLink(sprintf(dgettext('rolodex', 'Deactivate user %s (%s)'), $user->display_name, $user->username), 'users', $vars);
            } else {
                $vars['user_id'] = $this->user_id;
                $vars['action']  = 'admin';
                $vars['command']  = 'activateUser';
                $link = \core\Text::secureLink(sprintf(dgettext('rolodex', 'Activate user %s (%s)'), $user->display_name, $user->username), 'users', $vars);
            }
        }
        return $link;
    }


    public function getGoogleMap($location='mailing') {

        $address = null;

        if ($location == 'mailing') {
            if (!empty($this->mailing_address_1))
                $address[] = $this->mailing_address_1;
            if (!empty($this->mailing_address_2))
                $address[] = $this->mailing_address_2;
            if (!empty($this->mailing_city))
                $address[] = $this->mailing_city;
            if (!empty($this->mailing_state))
                $address[] = $this->mailing_state;
            if (!empty($this->mailing_country))
                $address[] = $this->mailing_country;
            if (!empty($this->mailing_zip_code))
                $address[] = $this->mailing_zip_code;
        } else {
            if (!empty($this->business_address_1))
                $address[] = $this->business_address_1;
            if (!empty($this->business_address_2))
                $address[] = $this->business_address_2;
            if (!empty($this->business_city))
                $address[] = $this->business_city;
            if (!empty($this->business_state))
                $address[] = $this->business_state;
            if (!empty($this->business_country))
                $address[] = $this->business_country;
            if (!empty($this->business_zip_code))
                $address[] = $this->business_zip_code;
        }

        if ($address) {
            $string = urlencode(htmlentities(implode(",", $address)));
            $googlemap = sprintf('<a class="url" href="http://maps.google.com/maps?f=q&q=%s">%s</a>', $string, dgettext('rolodex', 'Get Google Map'));
        } else {
            $googlemap = null;
        }

        return $googlemap;
    }


    public function deleteMember()
    {
        \core\Key::drop($this->key_id);
        $key = new \core\Key($this->key_id);
        $key->delete();

        $db = new \core\DB('rolodex_location_items');
        $db->addWhere('member_id', $this->user_id);
        \core\Error::logIfError($db->delete());
        $db = new \core\DB('rolodex_feature_items');
        $db->addWhere('member_id', $this->user_id);
        \core\Error::logIfError($db->delete());

        $this->deleteImage();

        return $this->delete();
    }

    public function hasError()
    {
        return isset($this->_error);
    }

    public function getError()
    {
        return $this->_error;
    }

    public function logError()
    {
        if (core\Error::isError($this->_error)) {
            \core\Error::log($this->_error);
        }
    }


    public function deleteImage()
    {
        if (!empty($this->image)) {
            $path = PHPWS_HOME_DIR . 'images/rolodex/';
            $img = $path . $this->image['name'];
            $thumb = $path . $this->image['thumb_name'];
            if (is_file($img)) {
                if (!@unlink($img)) {
                    return \core\Error::get(RDX_COULD_NOT_DELETE_IMG, 'rolodex', 'Rolodex_Member::deleteImage', $img);
                }
            }
            if (is_file($thumb)) {
                if (!@unlink($thumb)) {
                    return \core\Error::get(RDX_COULD_NOT_DELETE_IMG, 'rolodex', 'Rolodex_Member::deleteImage', $thumb);
                }
            }
        }
    }


    public function saveMember()
    {

        if ($this->isNew()) {
            $this->date_created = time();
            $expires = mktime(0, 0, 0, date("m"), date("d")+core\Settings::get('rolodex', 'expiry_interval'), date("Y"));
            $this->date_expires = $expires;
            if (core\Settings::get('rolodex', 'req_approval') && !Current_User::allow('rolodex', 'edit_member')) {
                $this->active = 0;
                if (core\Settings::get('rolodex', 'send_notification') && \core\Settings::get('rolodex', 'admin_contact')) {
                    $this->sendNotification(true);
                }
            }
        } else {
            if (core\Settings::get('rolodex', 'notify_all_saves') && \core\Settings::get('rolodex', 'admin_contact') && !Current_User::allow('rolodex')) {
                $this->sendNotification(false);
            }
        }

        if (isset($_POST['date_expires']) && !$this->isNew()) {
            $expires = strtotime($_POST['date_expires']);
            $this->date_expires = $expires;
        }
        $this->date_updated = time();

        $this->saveKey();

        if (core\Settings::get('rolodex', 'comments_enable')) {
            if (core\Settings::get('rolodex', 'comments_enforce')) {
                    $this->setAllow_comments(1);
            } else {
                isset($_POST['allow_comments']) ?
                    $this->setAllow_comments(1) :
                    $this->setAllow_comments(0);
            }
            if (core\Settings::get('rolodex', 'comments_anon_enable')) {
                if (core\Settings::get('rolodex', 'comments_anon_enforce')) {
                    $this->setAllow_anon(1);
                    \core\Core::initModClass('comments', 'Comments.php');
                    $thread = Comments::getThread($this->key_id);
                    $thread->allowAnonymous(1);
                    $thread->save();
                } else {
                    isset($_POST['allow_anon']) ?
                        $this->setAllow_anon(1) :
                        $this->setAllow_anon(0);
                    \core\Core::initModClass('comments', 'Comments.php');
                    $thread = Comments::getThread($this->key_id);
                    $thread->allowAnonymous($this->allow_anon);
                    $thread->save();
                }
            }
        }

        if (core\Settings::get('rolodex', 'privacy_use_search')) {
            $search = new Search($this->key_id);
            $search->resetKeywords();
            $search->addKeywords($this->getDisplay_name());
            $search->addKeywords($this->description);
            $result = $search->save();
            if (core\Error::isError($result)) {
                return $result;
            }
        }

        if (isset($_POST['categories']) && $_POST['categories'][0] !== '0') {
            $db = new \core\DB('category_items');
            $db->addWhere('key_id', (int)$this->key_id);
            \core\Error::logIfError($db->delete());
            \core\Core::initModClass('categories', 'Action.php');
            foreach ($_POST['categories'] as $var => $val) {
                Categories_Action::addCategoryItem($val, $this->key_id);
            }
        } else {
            $db = new \core\DB('category_items');
            $db->addWhere('key_id', (int)$this->key_id);
            \core\Error::logIfError($db->delete());
        }

        if (isset($_POST['locations']) && $_POST['locations'][0] !== '0') {
            $db = new \core\DB('rolodex_location_items');
            $db->addWhere('member_id', (int)$this->user_id);
            \core\Error::logIfError($db->delete());
            foreach ($_POST['locations'] as $var => $val) {
                $this->addItem('location', $val, $this->user_id);
            }
        } else {
            $db = new \core\DB('rolodex_location_items');
            $db->addWhere('member_id', (int)$this->user_id);
            \core\Error::logIfError($db->delete());
        }

        if (isset($_POST['features']) && $_POST['features'][0] !== '0') {
            $db = new \core\DB('rolodex_feature_items');
            $db->addWhere('member_id', (int)$this->user_id);
            \core\Error::logIfError($db->delete());
            foreach ($_POST['features'] as $var => $val) {
                $this->addItem('feature', $val, $this->user_id);
            }
        } else {
            $db = new \core\DB('rolodex_feature_items');
            $db->addWhere('member_id', (int)$this->user_id);
            \core\Error::logIfError($db->delete());
        }

        return $this->save();
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

        $key->setModule('rolodex');
        $key->setItemName('member');
        $key->setItemId($this->user_id);
        $key->setEditPermission('edit_member');
        $key->setUrl($this->viewLink(false,true));
        $key->active = (int)$this->active;
        $key->setTitle($this->getDisplay_name(true));
        $key->setSummary($this->description);
        $result = $key->save();
        if (core\Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new \core\DB('rolodex_member');
            $db->addWhere('user_id', $this->user_id);
            $db->addValue('key_id', $this->key_id);
            \core\Error::logIfError($db->update());
        }
        return true;
    }


    public function addItem($type, $item_id, $member_id)
    {
        if ($type =='location') {
            $db = new \core\DB('rolodex_location_items');
            $db->addValue('location_id', (int)$item_id);
        } elseif ($type =='feature') {
            $db = new \core\DB('rolodex_feature_items');
            $db->addValue('feature_id', (int)$item_id);
        }
        $db->addValue('member_id', (int)$member_id);
        return $db->insert();
    }


    public function isDataVisible($group)
    {
        $visibility = \core\Settings::get('rolodex', $group);
        if ($visibility == 0) {
            return true;
        } elseif ($visibility == 1) {
            if (Current_User::getId()) {
                return true;
            } else {
                return false;
            }
        } elseif ($visibility == 2) {
            if (Current_User::allow('rolodex', 'view_privates')) {
                return true;
            } else {
                return false;
            }
        }
    }


    public function isMemberVisible()
    {
        if ($this->privacy == 0) {
            return true;
        } elseif ($this->privacy == 1) {
            if (Current_User::getId()) {
                return true;
            } else {
                return false;
            }
        } elseif ($this->privacy == 2) {
            if (Current_User::allow('rolodex', 'view_privates')) {
                return true;
            } else {
                return false;
            }
        }
    }


    public function isMine()
    {
        if ($this->user_id == $_SESSION['User']->getId()) {
            return true;
        } else {
            return false;
        }
    }


    public function printCSVHeader()
    {
        $content = null;

        $content .= '"' . dgettext('rolodex', 'Last Name') . '",';
        $content .= '"' . dgettext('rolodex', 'First Name') . '",';
        $content .= '"' . dgettext('rolodex', 'Title') . '",';
        $content .= '"' . dgettext('rolodex', 'Honorific') . '",';
        $content .= '"' . dgettext('rolodex', 'Business/Organization') . '",';
        $content .= '"' . dgettext('rolodex', 'Department') . '",';
        $content .= '"' . dgettext('rolodex', 'Position title') . '",';
        $content .= '"' . dgettext('rolodex', 'Category(s)') . '",';
        $content .= '"' . dgettext('rolodex', 'Location(s)') . '",';
        $content .= '"' . dgettext('rolodex', 'Feature(s)') . '",';
        if (Rolodex_member::isDataVisible('privacy_contact')) {
            $content .= '"' . dgettext('rolodex', 'E-Mail') . '",';
        }
        if (Rolodex_member::isDataVisible('privacy_web')) {
            $content .= '"' . dgettext('rolodex', 'Website') . '",';
        }
        if (Rolodex_member::isDataVisible('privacy_bus_phone')) {
            $content .= '"' . dgettext('rolodex', 'Business phone') . '",';
            $content .= '"' . dgettext('rolodex', 'Ext') . '",';
            $content .= '"' . dgettext('rolodex', 'Fax') . '",';
            $content .= '"' . dgettext('rolodex', 'Toll-free phone') . '",';
        }
        if (Rolodex_member::isDataVisible('privacy_home_phone')) {
            $content .= '"' . dgettext('rolodex', 'Home phone') . '",';
        }
        if (Rolodex_member::isDataVisible('privacy_home')) {
            $content .= '"' . dgettext('rolodex', 'Home Address 1') . '",';
            $content .= '"' . dgettext('rolodex', 'Home Address 2') . '",';
            $content .= '"' . dgettext('rolodex', 'Home City') . '",';
            $content .= '"' . dgettext('rolodex', 'Home Province/State') . '",';
            $content .= '"' . dgettext('rolodex', 'Home Country') . '",';
            $content .= '"' . dgettext('rolodex', 'Home Postal/Zip') . '",';
        }
        if (Rolodex_member::isDataVisible('privacy_business')) {
            $content .= '"' . dgettext('rolodex', 'Business Address 1') . '",';
            $content .= '"' . dgettext('rolodex', 'Business Address 2') . '",';
            $content .= '"' . dgettext('rolodex', 'Business City') . '",';
            $content .= '"' . dgettext('rolodex', 'Business Province/State') . '",';
            $content .= '"' . dgettext('rolodex', 'Business Country') . '",';
            $content .= '"' . dgettext('rolodex', 'Business Postal/Zip') . '",';
        }

        if (core\Settings::get('rolodex', 'custom1_name'))
            $content .= '"' . \core\Settings::get('rolodex', 'custom1_name') . '",';
        if (core\Settings::get('rolodex', 'custom2_name'))
            $content .= '"' . \core\Settings::get('rolodex', 'custom2_name') . '",';
        if (core\Settings::get('rolodex', 'custom3_name'))
            $content .= '"' . \core\Settings::get('rolodex', 'custom3_name') . '",';
        if (core\Settings::get('rolodex', 'custom4_name'))
            $content .= '"' . \core\Settings::get('rolodex', 'custom4_name') . '",';

        if (Current_User::allow('rolodex', 'view_privates')) {
            if (core\Settings::get('rolodex', 'custom5_name'))
                $content .= '"' . \core\Settings::get('rolodex', 'custom5_name') . '",';
            if (core\Settings::get('rolodex', 'custom6_name'))
                $content .= '"' . \core\Settings::get('rolodex', 'custom6_name') . '",';
            if (core\Settings::get('rolodex', 'custom7_name'))
                $content .= '"' . \core\Settings::get('rolodex', 'custom7_name') . '",';
            if (core\Settings::get('rolodex', 'custom8_name'))
                $content .= '"' . \core\Settings::get('rolodex', 'custom8_name') . '",';
        }

        $content .= '"' . dgettext('rolodex', 'Description') . '"';

        $content .= "\n";
        return $content;
    }


    public function printCSV()
    {
        $content = null;

        $content .= '"' . $this->getLast_name() . '",';
        $content .= '"' . $this->getFirst_name() . '",';
        $content .= '"' . $this->getCourtesy_title() . '",';
        $content .= '"' . $this->getHonorific() . '",';
        $content .= '"' . $this->getBusiness_name() . '",';
        $content .= '"' . $this->getDepartment() . '",';
        $content .= '"' . $this->getPosition_title() . '",';
        $content .= '"' . implode('; ', $this->get_categories(true, true)) . '",';
        $content .= '"' . implode('; ', $this->get_locations(true, true)) . '",';
        $content .= '"' . implode('; ', $this->get_features(true, true)) . '",';
        if ($this->isDataVisible('privacy_contact')) {
            if ($this->email_privacy && !Current_User::allow('rolodex', 'view_privates')) {
                $content .= '"' . dgettext('rolodex', 'private') . '",';
            } else {
                $content .= '"' . $this->getDisplay_email() . '",';
            }
        }
        if ($this->isDataVisible('privacy_web')) {
            $content .= '"' . $this->getWebsite() . '",';
        }
        if ($this->isDataVisible('privacy_bus_phone')) {
            $content .= '"' . $this->getDay_phone() . '",';
            $content .= '"' . $this->getDay_phone_ext() . '",';
            $content .= '"' . $this->getFax_number() . '",';
            $content .= '"' . $this->getTollfree_phone() . '",';
        }
        if ($this->isDataVisible('privacy_home_phone')) {
            $content .= '"' . $this->getEvening_phone() . '",';
        }
        if ($this->isDataVisible('privacy_home')) {
            $content .= '"' . $this->getMailing_address_1() . '",';
            $content .= '"' . $this->getMailing_address_2() . '",';
            $content .= '"' . $this->getMailing_city() . '",';
            $content .= '"' . $this->getMailing_state() . '",';
            $content .= '"' . $this->getMailing_country() . '",';
            $content .= '"' . $this->getMailing_zip_code() . '",';
        }
        if ($this->isDataVisible('privacy_business')) {
            $content .= '"' . $this->getBusiness_address_1() . '",';
            $content .= '"' . $this->getBusiness_address_2() . '",';
            $content .= '"' . $this->getBusiness_city() . '",';
            $content .= '"' . $this->getBusiness_state() . '",';
            $content .= '"' . $this->getBusiness_country() . '",';
            $content .= '"' . $this->getBusiness_zip_code() . '",';
        }

        if (core\Settings::get('rolodex', 'custom1_name'))
            $content .= '"' . $this->getCustom1() . '",';
        if (core\Settings::get('rolodex', 'custom2_name'))
            $content .= '"' . $this->getCustom2() . '",';
        if (core\Settings::get('rolodex', 'custom3_name'))
            $content .= '"' . $this->getCustom3() . '",';
        if (core\Settings::get('rolodex', 'custom4_name'))
            $content .= '"' . $this->getCustom4() . '",';

        if (Current_User::allow('rolodex', 'view_privates')) {
            if (core\Settings::get('rolodex', 'custom5_name'))
                $content .= '"' . $this->getCustom5() . '",';
            if (core\Settings::get('rolodex', 'custom6_name'))
                $content .= '"' . $this->getCustom6() . '",';
            if (core\Settings::get('rolodex', 'custom7_name'))
                $content .= '"' . $this->getCustom7() . '",';
            if (core\Settings::get('rolodex', 'custom8_name'))
                $content .= '"' . $this->getCustom8() . '",';
        }

        $content .= '"' . $this->stripLF($this->getDescription()) . '"';

        $content .= "\n";
        return $content;
    }


    public function stripLF($str)
    {
        $str = str_replace("\r\n", '; ', $str);
        $str = str_replace("\n", '; ', $str);
        return $str;
    }


    public function sendNotification($new=true)
    {

        $page_title = $_SESSION['Layout_Settings']->getPageTitle(true);
        $site_contact = PHPWS_User::getUserSetting('site_contact');
        $url = \core\Core::getHomeHttp();
        if ($new) {
            $message = sprintf(dgettext('rolodex', 'You have a new %s application from %s waiting for your review at %s.'), \core\Settings::get('rolodex', 'module_title'), $this->getDisplay_name(true), $url);
            $subject = sprintf(dgettext('rolodex', 'Pending %s Application'), \core\Settings::get('rolodex', 'module_title'));
        } else {
            $message = sprintf(dgettext('rolodex', 'The %s profile for %s has been modified at %s.'), \core\Settings::get('rolodex', 'module_title'), $this->getDisplay_name(true), $url);
            $subject = sprintf(dgettext('rolodex', 'Modified %s Profile'), \core\Settings::get('rolodex', 'module_title'));
        }

                $mail = new PHPWS_Mail;
        $mail->addSendTo(core\Settings::get('rolodex', 'admin_contact'));
        $mail->setSubject($subject);
        $mail->setFrom(sprintf('%s<%s>', $page_title, $site_contact));
        $mail->setMessageBody($message);

        return $mail->send();

    }



}

?>