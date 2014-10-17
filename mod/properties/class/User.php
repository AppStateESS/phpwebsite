<?php

namespace Properties;

/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
\PHPWS_Core::requireConfig('properties', 'defines.php');
\PHPWS_Core::initModClass('properties', 'Photo.php');

class User {

    public function get()
    {
        switch ($_GET['uop']) {
            case 'search':
                $this->setSearchParameters();
                $this->searchPanel();
                $this->propertyListing();
                break;

            case 'remove':
                $this->removeSearch($_GET['s']);
                $this->searchPanel();
                $this->propertyListing();
                break;

            case 'list':
                $this->searchPanel();
                $this->propertyListing();
                break;

            default:
                \PHPWS_Core::errorPage('404');
        }
    }

    private function removeSearch($remove)
    {
        switch ($remove) {
            case 'ac':
            case 'ch':
            case 'dish':
            case 'furn':
            case 'pet':
            case 'tr':
            case 'wo':
            case 'wash':
                unset($_SESSION['property_search']['amenities'][$remove]);
                break;

            case 'allsub':
                unset($_SESSION['property_search']['nosub']);
                unset($_SESSION['property_search']['sub']);
                break;

            default:
                if (isset($_SESSION['property_search'][$remove])) {
                    unset($_SESSION['property_search'][$remove]);
                }
                break;
        }
        if (isset($_SESSION['property_search'])) {
            \PHPWS_Cookie::write('property_search',
                    serialize($_SESSION['property_search']));
        }
    }

    public function post()
    {
        \PHPWS_Core::errorPage('404');
    }

    public function loadSearchParameters()
    {
        if (!isset($_SESSION['property_search'])) {
            $_SESSION['property_search'] = unserialize(\PHPWS_Cookie::read('property_search'));
        }
        return $_SESSION['property_search'];
    }

    public function clearSearch()
    {
        unset($_SESSION['property_search']);
        \PHPWS_Cookie::delete('property_search');
        $this->loadSearchParameters();
    }

    public function setSearchParameters()
    {
        if (isset($_GET['clear'])) {
            $this->clearSearch();
        }

        if (isset($_GET['manager_submit'])) {
            if (!empty($_GET['manager'])) {
                $manager = preg_replace('/[^\w\s\-]/', '', $_GET['manager']);
                $manager = preg_replace('/\s{2,}/', ' ', trim($manager));
                $_SESSION['property_search']['manager'] = & $manager;
            } else {
                unset($_SESSION['property_search']['manager']);
            }
        }

        if (isset($_GET['property_name_submit'])) {
            if (!empty($_GET['property_name'])) {
                $property = preg_replace('/[^\w\s\-]/', '',
                        $_GET['property_name']);
                $property = preg_replace('/\s{2,}/', ' ', trim($property));
                $_SESSION['property_search']['property'] = & $property;
            } else {
                unset($_SESSION['property_search']['property']);
            }
        }

        if (isset($_GET['d'])) {
            if ($_GET['d'] == 'any') {
                unset($_SESSION['property_search']['distance']);
            } else {
                $_SESSION['property_search']['distance'] = $_GET['d'];
            }
        }

        if (isset($_GET['p'])) {
            if ($_GET['p'] == 'any') {
                unset($_SESSION['property_search']['price']);
            } else {
                if (strstr($_GET['p'], '-')) {
                    list($min, $max) = explode('-', $_GET['p']);
                    $_SESSION['property_search']['price']['min'] = (int) $min;
                    $_SESSION['property_search']['price']['max'] = (int) $max;
                }
            }
        }

        if (isset($_GET['eff'])) {
            $_SESSION['property_search']['eff'] = $_GET['eff'];
            unset($_SESSION['property_search']['beds']);
        } elseif (isset($_GET['beds'])) {
            $_SESSION['property_search']['beds'] = $_GET['beds'];
            unset($_SESSION['property_search']['eff']);
        }


        if (isset($_GET['a'])) {
            $_SESSION['property_search']['a'] = $_GET['a'];
        }

        if (isset($_GET['bath'])) {
            $_SESSION['property_search']['bath'] = $_GET['bath'];
        }

        if (isset($_GET['amen'])) {
            $_SESSION['property_search']['amenities'][$_GET['amen']] = 1;
        }

        if (isset($_GET['sub'])) {
            $_SESSION['property_search']['sub'] = 1;
            unset($_SESSION['property_search']['nosub']);
        }

        if (isset($_GET['nosub'])) {
            unset($_SESSION['property_search']['sub']);
            $_SESSION['property_search']['nosub'] = 1;
        }

        \PHPWS_Cookie::write('property_search',
                serialize($_SESSION['property_search']));
    }

    public function searchPanel()
    {
        $form = new \PHPWS_Form('search-properties');
        $form->addHidden('uop', 'search');
        $form->addHidden('module', 'properties');
        $form->setMethod('get');
        $form->addText('manager');
        $form->setSize('manager', 15);
        $form->addSubmit('manager_submit', 'Add');

        $form->addText('property_name');
        $form->setSize('property_name', 15);
        $form->addSubmit('property_name_submit', 'Add');
        $tpl = $form->getTemplate();
        //javascriptMod('properties', 'search');

        $vars['uop'] = 'search';
        $vars['d'] = 'any';
        $distances[] = \PHPWS_Text::moduleLink('Any', 'properties', $vars);
        $vars['d'] = '0';
        $distances[] = \PHPWS_Text::moduleLink('0 - 5 miles', 'properties',
                        $vars);
        $vars['d'] = '5';
        $distances[] = \PHPWS_Text::moduleLink('5 - 10 miles', 'properties',
                        $vars);
        $vars['d'] = '10';
        $distances[] = \PHPWS_Text::moduleLink('10 - 25 miles', 'properties',
                        $vars);
        $vars['d'] = '25';
        $distances[] = \PHPWS_Text::moduleLink('Over 25 miles', 'properties',
                        $vars);

        $tpl['DISTANCE_OPTIONS'] = '<ul><li>' . implode('</li><li>', $distances) . '</li></ul>';

        unset($vars['d']);

        $vars['a'] = UNDERGRAD;
        $age1 = \PHPWS_Text::moduleLink('Undergraduate', 'properties', $vars);
        $vars['a'] = GRAD_STUDENT;
        $age2 = \PHPWS_Text::moduleLink('Graduate', 'properties', $vars);
        $tpl['STUDENT_TYPE'] = $age1 . '<br />' . $age2;
        unset($vars['a']);

        $vars['p'] = 'any';
        $prices[] = \PHPWS_Text::moduleLink('Any', 'properties', $vars);
        $vars['p'] = '0-100';
        $prices[] = \PHPWS_Text::moduleLink('$100 and under', 'properties',
                        $vars);
        $vars['p'] = '100-200';
        $prices[] = \PHPWS_Text::moduleLink('$100 to $200', 'properties', $vars);
        $vars['p'] = '200-300';
        $prices[] = \PHPWS_Text::moduleLink('$200 to $300', 'properties', $vars);
        $vars['p'] = '300-400';
        $prices[] = \PHPWS_Text::moduleLink('$300 to $400', 'properties', $vars);
        $vars['p'] = '400-500';
        $prices[] = \PHPWS_Text::moduleLink('$400 to $500', 'properties', $vars);
        $vars['p'] = '500-600';
        $prices[] = \PHPWS_Text::moduleLink('$500 to $600', 'properties', $vars);
        $vars['p'] = '600-750';
        $prices[] = \PHPWS_Text::moduleLink('$600 to $750', 'properties', $vars);
        $vars['p'] = '750-1000';
        $prices[] = \PHPWS_Text::moduleLink('$750 to $1000', 'properties', $vars);
        $vars['p'] = '1000-9999';
        $prices[] = \PHPWS_Text::moduleLink('$1000 and above', 'properties',
                        $vars);

        $tpl['PRICE_OPTIONS'] = '<ul><li>' . implode('</li><li>', $prices) . '</li></ul>';

        unset($vars['p']);

        $vars['eff'] = '1';
        $rooms[] = \PHPWS_Text::moduleLink('One room efficiency', 'properties',
                        $vars);

        unset($vars['eff']);

        $vars['beds'] = '1';
        $rooms[] = \PHPWS_Text::moduleLink('1+', 'properties', $vars);
        $vars['beds'] = '2';
        $rooms[] = \PHPWS_Text::moduleLink('2+', 'properties', $vars);
        $vars['beds'] = '3';
        $rooms[] = \PHPWS_Text::moduleLink('3+', 'properties', $vars);
        $vars['beds'] = '4';
        $rooms[] = \PHPWS_Text::moduleLink('4+', 'properties', $vars);
        $vars['beds'] = '5';
        $rooms[] = \PHPWS_Text::moduleLink('5+', 'properties', $vars);

        $tpl['BEDROOM_CHOICE'] = '<ul><li>' . implode('</li><li>', $rooms) . '</li></ul>';

        unset($vars['beds']);

        $vars['bath'] = '1';
        $bath[] = \PHPWS_Text::moduleLink('1+', 'properties', $vars);
        $vars['bath'] = '1.5';
        $bath[] = \PHPWS_Text::moduleLink('1 1/2+', 'properties', $vars);
        $vars['bath'] = '2';
        $bath[] = \PHPWS_Text::moduleLink('2+', 'properties', $vars);
        $vars['bath'] = '2.5';
        $bath[] = \PHPWS_Text::moduleLink('2 1/2+', 'properties', $vars);
        $vars['bath'] = '3';
        $bath[] = \PHPWS_Text::moduleLink('3+', 'properties', $vars);
        $vars['bath'] = '3.5';
        $bath[] = \PHPWS_Text::moduleLink('3 1/2+', 'properties', $vars);
        $tpl['BATHROOM_CHOICE'] = '<ul><li>' . implode('</li><li>', $bath) . '</li></ul>';

        unset($vars['bath']);

        $vars['sub'] = 1;
        $tpl['SUBLEASE'] = \PHPWS_Text::moduleLink('Sublease only',
                        'properties', $vars);
        unset($vars['sub']);

        $vars['nosub'] = 1;
        $tpl['NOSUB'] = \PHPWS_Text::moduleLink('No subleases', 'properties',
                        $vars);
        unset($vars['nosub']);

        $features = null;
        $search = $this->loadSearchParameters();
        if (!isset($search['amenities']['ac'])) {
            $vars['amen'] = 'ac';
            $features[] = \PHPWS_Text::moduleLink('AppalCart', 'properties',
                            $vars);
        }
        if (!isset($search['amenities']['ch'])) {
            $vars['amen'] = 'ch';
            $features[] = \PHPWS_Text::moduleLink('Clubhouse', 'properties',
                            $vars);
        }
        if (!isset($search['amenities']['dish'])) {
            $vars['amen'] = 'dish';
            $features[] = \PHPWS_Text::moduleLink('Dishwasher', 'properties',
                            $vars);
        }

        if (!isset($search['amenities']['furn'])) {
            $vars['amen'] = 'furn';
            $features[] = \PHPWS_Text::moduleLink('Furnished', 'properties',
                            $vars);
        }

        if (!isset($search['amenities']['pet'])) {
            $vars['amen'] = 'pet';
            $features[] = \PHPWS_Text::moduleLink('Pet allowed', 'properties',
                            $vars);
        }
        if (!isset($search['amenities']['tr'])) {
            $vars['amen'] = 'tr';
            $features[] = \PHPWS_Text::moduleLink('Trash pickup', 'properties',
                            $vars);
        }
        if (!isset($search['amenities']['wo'])) {
            $vars['amen'] = 'wo';
            $features[] = \PHPWS_Text::moduleLink('Workout room', 'properties',
                            $vars);
        }
        if (!isset($search['amenities']['wash'])) {
            $vars['amen'] = 'wash';
            $features[] = \PHPWS_Text::moduleLink('Washer/Dryer', 'properties',
                            $vars);
        }

        if ($features) {
            $tpl['FEATURES'] = '<ul><li>' . implode('</li><li>', $features) . '</li></ul>';
        }

        $tpl['CRITERIA'] = $this->getCriteria();

        unset($vars['amen']);
        $vars['clear'] = 1;
        $tpl['CLEAR'] = \PHPWS_Text::moduleLink('Clear all criteria', 'properties', $vars, null, 'Clear all criteria', 'btn btn-danger');

        $content = \PHPWS_Template::process($tpl, 'properties', 'search.tpl');
        \Layout::add($content, 'properties', 'search_settings');
    }

    private function getCriteria()
    {
        $search = $this->loadSearchParameters();
        if (!empty($_SESSION['property_search'])) {
            foreach ($_SESSION['property_search'] as $key => $value) {
                switch ($key) {
                    case 'a':
                        switch ($value) {
                            case UNDERGRAD:
                                $criteria[] = 'Undergrad' . $this->getCancel('a');
                                break;
                            case GRAD_STUDENT:
                                $criteria[] = 'Graduate' . $this->getCancel('a');
                                break;
                        }
                        break;

                    case 'distance':
                        switch ($value) {
                            case 0:
                                $d = '0 to 5 miles';
                                break;
                            case 5:
                                $d = '5 to 10 miles';
                                break;
                            case 10:
                                $d = '10 to 25 miles';
                                break;
                            case 25:
                                $d = '25 miles or more';
                                break;
                        }
                        $criteria[] = "Campus distance: $d" . $this->getCancel('distance');
                        break;

                    case 'eff':
                        $criteria[] = "One room efficiency" . $this->getCancel('eff');
                        break;

                    case 'beds':
                        $criteria[] = "Bedrooms: $value" . $this->getCancel('beds');
                        break;

                    case 'bath':
                        $criteria[] = "Bathrooms: $value" . $this->getCancel('bath');
                        break;

                    case 'manager':
                        $criteria[] = "Manager name like \"$value\"" . $this->getCancel('manager');
                        break;

                    case 'price':
                        $criteria[] = sprintf('Price: $%s to $%s',
                                        $value['min'], $value['max']) . $this->getCancel('price');
                        break;

                    case 'amenities':
                        foreach ($value as $amen => $null) {
                            $criteria[] = $this->amenityTranslate($amen);
                        }
                        break;

                    case 'property':
                        $plist = explode(' ', $value);
                        $value = implode('" OR "', $plist);
                        $criteria[] = "Property name like \"$value\"" . $this->getCancel('property');
                        break;

                    case 'sub':
                        $criteria[] = 'Subleases only' . $this->getCancel('sub');
                        break;

                    case 'nosub':
                        $criteria[] = 'No subleases' . $this->getCancel('nosub');
                        break;
                }
            }
        }
        if (!empty($criteria)) {
            return implode("</li><li>", $criteria);
        }
    }

    public function propertyListing()
    {
        \PHPWS_Core::initModClass('properties', 'Base.php');
        \PHPWS_Core::initModClass('properties', 'Property.php');
        \Layout::addStyle('properties');
        $new_class = 'other';
        $all_class = 'current';
        $sub_class = 'other';

        if (\Current_User::isLogged()) {
            $page['ROOMMATE'] = \PHPWS_Text::moduleLink('Roommate/Sublease search',
                            'properties', array('rop' => 'view'), null,
                            'Roommate or Sublease search', 'btn btn-primary');
        } else {
            $page['ROOMMATE'] = '<div class="alert alert-info">Looking for a roommate? Need to sublease? <a class="alert-link" href="' . \propertiesLoginLink() . '">Login to the site!</a></div>';
        }

        $pager = new \DBPager('properties', 'properties\Property');

        $pager->setModule('properties');
        $pager->setTemplate('listing.tpl');
        $pager->addRowTags('listRows');
        $pager->setDefaultOrder('updated', 'desc');
        $pager->setLink('index.php?module=properties&uop=list');
        $pager->joinResult('id', 'prop_photo', 'pid', 'path', 'thumbnail');
        $pager->db->addWhere('dbp1.main_pic', 1);
        $pager->db->addWhere('dbp1.main_pic', null, null, 'or');
        $pager->db->addWhere('active', 1, '=', 'and', 'search');
        $pager->setEmptyMessage('No properties found. You may want to reduce your search criteria.');
        $all_class = 'active';
        if (!empty($_SESSION['property_search'])) {
            foreach ($_SESSION['property_search'] as $key => $value) {
                switch ($key) {
                    case 'a':
                        if ($value) {
                            $pager->db->addWhere('student_type', $value, '=',
                                    'and', 'search');
                        }
                        break;

                    case 'sub':
                        $new_class = null;
                        $all_class = null;
                        $sub_class = 'active';
                        $pager->db->addWhere('sublease', '1', '=', 'and',
                                'search');
                        break;

                    case 'nosub':
                        $new_class = 'active';
                        $all_class = null;
                        $sub_class = null;
                        $pager->db->addWhere('sublease', '0', '=', 'and',
                                'search');
                        break;

                    case 'distance':
                        $pager->db->addWhere('campus_distance', $value, '=',
                                'and', 'search');
                        break;

                    case 'beds':
                        $value = (int) $value;
                        $pager->db->addWhere('bedroom_no', (int) $value, '>=',
                                'and', 'search');
                        break;

                    case 'eff':
                        $pager->db->addWhere('efficiency', '1', '=', 'and',
                                'search');
                        break;

                    case 'bath':
                        $value = (int) $value;
                        $pager->db->addWhere('bathroom_no', $value, '>=', 'and',
                                'search');
                        break;

                    case 'manager':
                        $value = preg_replace('/[^\w\s]|\s{2,}/', ' ', $value);
                        $vlist = explode(' ', $value);
                        $db2 = new \PHPWS_DB('prop_contacts');
                        foreach ($vlist as $v) {
                            $db2->addWhere('company_name', "%$value%", 'like',
                                    'or');
                        }
                        $db2->addColumn('id');
                        $managers = $db2->select('col');
                        if (!empty($managers)) {
                            $pager->db->addWhere('contact_id', $managers, 'in',
                                    'and', 'properties');
                        } else {
                            $pager->db->addWhere('id', 0, '=', 'and', 'cancel');
                        }
                        break;

                    case 'price':
                        $pager->db->addWhere('monthly_rent',
                                $value['min'] * 100, '>=', 'and', 'search');
                        $pager->db->addWhere('monthly_rent',
                                $value['max'] * 100, '<=', 'and', 'search');
                        break;

                    case 'amenities':
                        foreach ($value as $amen_name => $foo) {
                            switch ($amen_name) {
                                case 'ac':
                                    $pager->db->addWhere('appalcart', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'ch':
                                    $pager->db->addWhere('clubhouse', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'dish':
                                    $pager->db->addWhere('dishwasher', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'furn':
                                    $pager->db->addWhere('furnished', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'pet':
                                    $pager->db->addWhere('pets_allowed', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'tr':
                                    $pager->db->addWhere('trash_type', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'wo':
                                    $pager->db->addWhere('workout_room', 1, '=',
                                            'and', 'search');
                                    break;

                                case 'wash':
                                    $pager->db->addWhere('laundry_type', 1, '=',
                                            'and', 'search');
                                    break;
                            }
                        }
                        break;

                    case 'property':
                        $value = preg_replace('/[^\w\s]|\s{2,}/', ' ', $value);
                        $vlist = explode(' ', $value);
                        foreach ($vlist as $v) {
                            $pager->db->addWhere('name', "%$v%", 'like', 'or',
                                    'property');
                        }
                        break;
                }
            }
        }

        $link = 'index.php?module=properties&uop=search';
        $page['ALL'] = sprintf('<li class="%s"><a href="%s">All properties</a></li>',
                $all_class, 'index.php?module=properties&uop=remove&s=allsub');
        $page['NEW'] = sprintf('<li class="%s"><a href="%s%s">New leases only</a></li>',
                $new_class, $link, '&amp;nosub=1');
        $page['SUB'] = sprintf('<li class="%s"><a href="%s%s">Subleases only</a></li>',
                $sub_class, $link, '&amp;sub=1');

        $pager->addPageTags($page);
        $content = $pager->get();
        \Layout::add($content);
    }

    public function newProperties()
    {
        \PHPWS_Core::initModClass('properties', 'Property.php');
        $db = new \PHPWS_DB('properties');
        $db->addOrder('created desc');
        $db->setLimit('5');
        $properties = $db->getObjects('Properties\Property');

        if (empty($properties)) {
            return null;
        }

        foreach ($properties as $prop) {
            $new_properties[] = array('NAME' => $prop->getViewLink());
        }
        return $new_properties;
    }

    public static function getContracts()
    {
        return array(C_MONTHLY => 'Monthly',
            C_FIVE_MONTH => 'Five months',
            C_SIX_MONTH => 'Six months',
            C_TEN_MONTH => 'Ten months',
            C_YEARLY => 'Twelve months',
            C_SUMMER => 'Summer only',
            C_SEMESTER => 'per Semester',
            C_TWO_SEMESTER => 'School year (two semesters)'
        );
    }

    public static function getContacts($active_only = false)
    {
        $db = new \PHPWS_DB('prop_contacts');
        $db->addOrder('company_name');
        $db->addColumn('id');
        $db->addColumn('company_name');
        $db->setIndexBy('id');
        if ($active_only) {
            $db->addWhere('active', 1);
        }
        $contacts = $db->select('col');
        if (\PHPWS_Error::isError($contacts)) {
            \PHPWS_Error::log($contacts);
            return array('0' => 'Error retrieving contacts');
        } else {
            return $contacts;
        }
    }

    private function getCancel($s)
    {
        $img = ' <i style="color : red" class="fa fa-times-circle"></i>';
        $vars['uop'] = 'remove';
        $vars['s'] = $s;
        return \PHPWS_Text::moduleLink($img, 'properties', $vars);
    }

    private function amenityTranslate($abbr)
    {
        switch ($abbr) {
            case 'ac':
                $cancel = $this->getCancel($abbr);
                return 'AppalCart' . $cancel;
            case 'ch':
                $cancel = $this->getCancel($abbr);
                return 'Clubhouse' . $cancel;

            case 'dish':
                $cancel = $this->getCancel($abbr);
                return 'Dishwasher' . $cancel;

            case 'furn':
                $cancel = $this->getCancel($abbr);
                return 'Furnished' . $cancel;

            case 'pet':
                $cancel = $this->getCancel($abbr);
                return 'Pets allowed' . $cancel;

            case 'tr':
                $cancel = $this->getCancel($abbr);
                return 'Trash pickup' . $cancel;

            case 'wo':
                $cancel = $this->getCancel($abbr);
                return 'Workout room' . $cancel;

            case 'wash':
                $cancel = $this->getCancel($abbr);
                return 'Washer/Dryer' . $cancel;
        }
    }

}

?>