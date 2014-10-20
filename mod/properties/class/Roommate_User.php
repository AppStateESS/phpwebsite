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
\PHPWS_Core::initModClass('properties', 'Roommate.php');
\PHPWS_Core::initModClass('properties', 'Message.php');
\PHPWS_Core::initModClass('properties', 'Report.php');

class Roommate_User {

    private $content = null;
    private $title = null;
    private $message = null;
    private $errors = null;
    private $roommate = null;

    public function __construct()
    {
        if (!isset($_SESSION['properties_user_checked'])) {
            $db = new \PHPWS_DB('prop_report');
            $db->addWhere('offender_id', \Current_User::getId());
            $db->addWhere('block', 1);
            $db->addColumn('block_reason');
            $result = $db->select('one');

            if ($result) {
                $_SESSION['properties_user_checked'] = $result;
            } else {
                $_SESSION['properties_user_checked'] = false;
            }
        }
    }

    public function denyAccess()
    {
        $this->title = 'Access denied';
        $this->content = '<p>You are no longer able to access the roommate system because of the following:</p>';
        $this->content .= '<p>' . $_SESSION['properties_user_checked'] . '</p>';
        $this->display();
    }

    private function loadUserRoommate()
    {
        $id = \Current_User::getId();
        if (!$id) {
            throw new \Exception('You will need to log in to perform this action.');
        }

        // if the user has not created their roommate yet, we reset the id
        // to assure an insert
        $this->roommate = new Roommate($id);
    }

    private function loadCurrentRoommate()
    {
        $id = $_REQUEST['id'];
        $this->roommate = new Roommate((int) $id);
    }

    private function deleteMessage($message_id)
    {
        $message = new Message($message_id);
        if ($message->reported) {
            $this->setCarryMessage('Cannot delete a reported message');
            return;
        }

        if ($message->to_user_id != \Current_User::getId()) {
            $this->setCarryMessage('Could not delete message.');
            return;
        }

        $message->delete();
    }

    public function get()
    {
        $this->loadCarryMessage();
        switch ($_GET['rop']) {
            case 'edit':
                $this->loadUserRoommate();
                if ($this->roommate->id != \Current_User::getId()) {
                    \PHPWS_Core::errorPage('You may not edit this roommate');
                }
                $this->editRoommate();
                break;

            case 'delete_message':
                $this->deleteMessage($_GET['id']);
                \PHPWS_Core::goBack();
                break;

            case 'contact':
                if (isset($_GET['id'])) {
                    $this->contactRenter((int) $_GET['id']);
                    $this->loadCarryMessage('Message sent!');
                    exit();
                } else {
                    \PHPWS_Core::errorPage('404');
                }
                break;

            case 'report':
                if (isset($_GET['id'])) {
                    $this->reportRenter((int) $_GET['id']);
                    $this->loadCarryMessage('Report made');
                    exit();
                } else {
                    \PHPWS_Core::errorPage('404');
                }
                exit();

            case 'view':
                if (isset($_GET['id'])) {
                    $this->loadCurrentRoommate();
                    $this->content = $this->roommate->view();
                } else {
                    $this->listRoommates();
                }
                break;

            case 'clear':
                $this->loadUserRoommate();
                if (!$this->roommate->delete()) {
                    Layout::add('Could not clear your roommate request. Please contact the site owner.');
                    return;
                }
                \PHPWS_Core::goBack();
                break;

            case 'search':
                $this->listRoommates();
                break;

            case 'remove':
                $this->removeSearch($_GET['s']);
                $this->listRoommates();
                break;

            case 'timeout':
                $this->loadUserRoommate();
                $this->roommate->update();
                \PHPWS_Core::goBack();
                break;

            case 'read_messages':
                $this->title = 'Roommate messages';
                $this->readMessages();
                break;

            default:
                $this->listRoommates();
                break;
        }
        $this->display();
    }

    private function readMessages()
    {
        \Layout::addStyle('properties', 'view.css');
        javascriptMod('properties', 'contact', array('id' => \Current_User::getId()));
        $db = new \PHPWS_DB('prop_messages');
        $db->addWhere('to_user_id', \Current_User::getID());
        $db->addOrder('date_sent desc');
        $db->addWhere('hidden', 0);
        $result = $db->getObjects('\Properties\Message');
        if (empty($result)) {
            $this->content = 'You do not have any roommate messages.<br /><a href="index.php?module=properties&amp;rop=view">Back to list</a>';
            return;
        }
        foreach ($result as $message) {
            $row[] = $message->getRow();
        }
        $tpl['message_rows'] = $row;
        $this->content = \PHPWS_Template::process($tpl, 'properties', 'message_listing.tpl');
    }

    private function display()
    {
        \Layout::addStyle('properties');
        $tpl['TITLE'] = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;
        $final_content = \PHPWS_Template::process($tpl, 'properties', 'admin.tpl');
        \Layout::add($final_content);
    }

    public function reportRenter($message_id)
    {
        $form = new \PHPWS_Form('report');
        $form->addHidden('module', 'properties');
        $form->addHidden('rop', 'report_post');
        $form->addHidden('message_id', $message_id);
        $form->addTextArea('reason');
        $form->setWidth('reason', '100%');
        $form->setRows('reason', '10');
        $form->setLabel('reason', 'Please give your reason for reporting this message. You will be unable to delete the message until the matter is investigated.');
        $form->addSubmit('Report message');
        $tpl = $form->getTemplate();

        echo \PHPWS_Template::process($tpl, 'properties', 'report_form.tpl');
    }

    public function contactRenter($id)
    {
        $form = new \PHPWS_Form('contact-renter');
        $form->addHidden('module', 'properties');
        $form->addHidden('rop', 'send_message');
        $form->addHidden('id', $id);
        $form->addTextArea('message');
        $form->setWidth('message', '100%');
        $form->setRows('message', '13');
        $form->setLabel('message', 'Enter your message below. Your message will not reveal your identity. Only give contact information with those you feel comfortable. Improper messages may be reported and acted upon.');
        $form->addSubmit('Send message');

        $tpl = $form->getTemplate();

        echo \PHPWS_Template::process($tpl, 'properties', 'contact_form.tpl');
    }

    public function post()
    {
        switch ($_POST['rop']) {
            case 'post_roommate':
                $this->loadUserRoommate();
                if ($this->roommate->post()) {
                    try {
                        $this->roommate->save();
                        $this->setCarryMessage('Roommate saved successfully.');
                        \PHPWS_Core::reroute($this->roommate->viewLink());
                    } catch (\Exception $e) {
                        $this->setCarryMessage($e->getMessage());
                        \PHPWS_Core::reroute('index.php?module=properties&rop=view');
                    }
                } else {
                    $this->editRoommate();
                    $this->display();
                }
                break;

            case 'send_message':
                if (!$this->sendMessage()) {
                    $this->content = 'Sorry, but we couldn\'t save your message.';
                    return;
                }
                if (!$this->roommate->id) {
                    $this->content = 'Sorry, could not find this roommate. <a href="index.php?module=properties&rop=view">Go back to the list?</a>';
                } else {
                    $this->setCarryMessage('Message sent');
                    \PHPWS_Core::goBack();
                }
                break;

            case'report_post':
                $this->reportPost();
                \PHPWS_Core::reroute('index.php?module=properties&rop=read_messages');
                break;
        }
    }

    private function reportPost()
    {
        $message = new Message($_POST['message_id']);
        $message->reported = 1;
        $result = $message->save();

        if (!\PHPWS_Error::isError($result)) {
            $report = new Report;
            $report->setMessageId($message->id);
            $report->setReporterId($message->to_user_id);
            $report->setOffenderId($message->from_user_id);
            $report->setReason($_POST['reason']);
            $report->save();
        } else {
            return $result;
        }
    }

    private function sendMessage()
    {
        $this->loadCurrentRoommate();
        if (!$this->roommate->id) {
            $this->message = 'Roommate entry could not be found. <a href="mailto:%s">Please contact us</a>
             if you continue to have difficulties.';
            return;
        }
        $sender_id = \Current_User::getId();
        $message = new Message;
        $message->setToUser($this->roommate->id);
        $message->setFromUser($sender_id);
        $message->setBody($_POST['message']);
        $message->setSenderName(\Current_User::getUsername());
        $result = $message->save();

        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Previously, messages would be emailed to users instead of messaging back and forth.
     * Keeping in case needed again.
     */

    /**
      private function sendEmail()
      {
      $user = new \PHPWS_User((int) $_POST['id']);
      if (!$user->id) {
      $this->message = 'Roommate entry could not be found. <a href="mailto:%s">Please contact us</a>
      if you continue to have difficulties.';
      return;
      }

      $subject = 'Roommate query';
      $reply_to = \Current_User::getEmail();
      $from = \PHPWS_Settings::get('properties', 'email');
      $message[] = sprintf('%s from %s has emailed you about your roommate request.
      Replying to this email will send an email to the interested party.',
      \Current_User::getDisplayName(), \PHPWS_Core::getHomeHttp());
      $message[] = '--------------------------------------------------------------';
      $message[] = trim(strip_tags($_POST['message']));

      $mail = new \PHPWS_Mail;
      $mail->addSendTo($user->getEmail());
      $mail->setSubject($subject);
      $mail->setFrom($from);
      $mail->setReplyTo($reply_to);
      $mail->setMessageBody(implode("\n", $message));
      $result = $mail->send();
      if (!\PEAR::isError($result)) {
      \PHPWS_Error::log($result);
      $this->message = 'Service could not send email at this time. Please try again later.';
      }
      }
     * */
    private function editRoommate()
    {
        $this->title = 'Edit my roommate information';
        $this->content = $this->roommate->form();
    }

    public function listRoommates()
    {
        $this->setSearchParameters();
        $this->searchPanel();
        \Layout::addStyle('properties', 'forms.css');
        if (!\Current_User::isLogged()) {
            $login = \PHPWS_Settings::get('properties', 'login_link');
            if (empty($login)) {
                $login = './admin';
            }

            $tpl['LOGIN'] = sprintf('Want to request or contact a roommate? <a href="%s">You will need to login</a>', $login);
        } else {
            $tpl['LOGIN'] = $this->options();
        }

        $pager = new \DBPager('prop_roommate', 'properties\Roommate');
        $pager->addPageTags($tpl);
        $pager->setModule('properties');
        $pager->setTemplate('roommates.tpl');
        $pager->setDefaultOrder('updated', 'desc');
        $pager->addRowTags('rowtags');
        $pager->addSortHeader('name', 'Title');
        $pager->addSortHeader('monthly_rent', 'Monthly rent');
        $pager->addSortHeader('share_bedroom', 'bedroom?');
        $pager->addSortHeader('share_bathroom', 'bathroom?');
        $pager->addSortHeader('campus_distance', 'Campus distance');
        $pager->addSortHeader('move_in_date', 'Move in date');
        $pager->setSearch('name');
        if (!empty($_SESSION['roommate_search'])) {
            foreach ($_SESSION['roommate_search'] as $key => $value) {
                switch ($key) {
                    case 'sub':
                        $pager->db->addWhere('sublease', '1', '=', 'and', 'search');
                        break;

                    case 'nosub':
                        $pager->db->addWhere('sublease', '0', '=', 'and', 'search');
                        break;

                    case 'gen':
                        $pager->db->addWhere('gender', array(0, $value), 'in', 'and', 'search');
                        break;

                    case 'smoke':
                        $pager->db->addWhere('smoking', array(0, $value), 'in', 'and', 'search');
                        break;

                    case 'distance':
                        $pager->db->addWhere('campus_distance', $value, '=', 'and', 'search');
                        break;

                    case 'beds':
                        // notice the reverse
                        $value = $value ? 0 : 1;
                        $pager->db->addWhere('share_bedroom', $value, '=', 'and', 'search');
                        break;

                    case 'bath':
                        // notice the reverse
                        $value = $value ? 0 : 1;
                        $pager->db->addWhere('share_bathroom', $value, '=', 'and', 'search');
                        break;

                    case 'manager':
                        $value = preg_replace('/[^\w\s]|\s{2,}/', ' ', $value);
                        $vlist = explode(' ', $value);
                        $db2 = new \PHPWS_DB('prop_contacts');
                        foreach ($vlist as $v) {
                            $db2->addWhere('company_name', "%$value%", 'like', 'or');
                        }
                        $db2->addColumn('id');
                        $managers = $db2->select('col');
                        if (!empty($managers)) {
                            $pager->db->addWhere('contact_id', $managers, 'in', 'and', 'properties');
                        } else {
                            $pager->db->addWhere('id', 0, '=', 'and', 'cancel');
                        }
                        break;

                    case 'price':
                        $pager->db->addWhere('monthly_rent', $value['min'] * 100, '>=', 'and', 'search');
                        $pager->db->addWhere('monthly_rent', $value['max'] * 100, '<=', 'and', 'search');
                        break;

                    case 'amenities':
                        foreach ($value as $amen_name => $foo) {
                            switch ($amen_name) {
                                case 'ac':
                                    $pager->db->addWhere('appalcart', 1, '=', 'and', 'search');
                                    break;

                                case 'ch':
                                    $pager->db->addWhere('clubhouse', 1, '=', 'and', 'search');
                                    break;

                                case 'dish':
                                    $pager->db->addWhere('dishwasher', 1, '=', 'and', 'search');
                                    break;

                                case 'furn':
                                    $pager->db->addWhere('furnished', 1, '=', 'and', 'search');
                                    break;

                                case 'pet':
                                    $pager->db->addWhere('pets_allowed', 1, '=', 'and', 'search');
                                    break;

                                case 'tr':
                                    $pager->db->addWhere('trash_type', 1, '=', 'and', 'search');
                                    break;

                                case 'wo':
                                    $pager->db->addWhere('workout_room', 1, '=', 'and', 'search');
                                    break;

                                case 'wash':
                                    $pager->db->addWhere('laundry_type', 1, '=', 'and', 'search');
                                    break;
                            }
                        }
                        break;

                    case 'property':
                        $value = preg_replace('/[^\w\s]|\s{2,}/', ' ', $value);
                        $vlist = explode(' ', $value);
                        foreach ($vlist as $v) {
                            $pager->db->addWhere('name', "%$v%", 'like', 'or', 'property');
                        }
                        break;
                }
            }
        }

        // roommates that are a month past move in date, are not shown
        $cut_off_date = time() - (86400 * 30);
        $pager->addWhere('move_in_date', $cut_off_date, '>');
        $pager->setEmptyMessage('No one is currently looking for a roommate. Try again later.');
        $this->content = $pager->get();
    }

    private function options()
    {
        $opt[] = \PHPWS_Text::moduleLink('Create/Edit request', 'properties', array('rop' => 'edit'));
        $opt[] = javascript('confirm', array('question' => 'Are you sure you want to clear your roommate request?',
            'address' => \PHPWS_Text::linkAddress('properties', array('rop' => 'clear')),
            'link' => 'Clear my request', 'title' => 'Clear my request'));
        $opt[] = \PHPWS_Text::moduleLink('Extend my deadline', 'properties', array('rop' => 'timeout'));

        $db = new \PHPWS_DB('prop_messages');
        $db->addWhere('to_user_id', \Current_User::getId());
        $db->addOrder('date_sent desc');
        $db->addWhere('hidden', 0);
        $db->addColumn('id');
        $messages = $db->select('col');
        if (\PHPWS_Error::isError($messages)) {
            \PHPWS_Error::log($messages);
        } else {
            $opt[] = \PHPWS_Text::moduleLink('Messages (' . count($messages) . ')', 'properties', array('rop' => 'read_messages'));
        }
        return implode(' | ', $opt);
    }

    protected function setCarryMessage($message)
    {
        $_SESSION['Properties_Message'] = $message;
    }

    protected function loadCarryMessage()
    {
        if (isset($_SESSION['Properties_Message'])) {
            $this->message = $_SESSION['Properties_Message'];
            unset($_SESSION['Properties_Message']);
        }
        return false;
    }

    public function loadSearchParameters()
    {
        if (!isset($_SESSION['roommate_search'])) {
            $_SESSION['roommate_search'] = unserialize(\PHPWS_Cookie::read('roommate_search'));
        }
        return $_SESSION['roommate_search'];
    }

    public function clearSearch()
    {
        unset($_SESSION['roommate_search']);
        \PHPWS_Cookie::delete('roommate_search');
        $this->loadSearchParameters();
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
                unset($_SESSION['roommate_search']['amenities'][$remove]);
                break;

            default:
                unset($_SESSION['roommate_search'][$remove]);
                break;
        }
        \PHPWS_Cookie::write('roommate_search', serialize($_SESSION['roommate_search']));
    }

    public function setSearchParameters()
    {
        $this->loadSearchParameters();
        if (isset($_GET['clear'])) {
            $this->clearSearch();
        }

        if (isset($_GET['property_name_submit'])) {
            if (!empty($_GET['property_name'])) {
                $property = preg_replace('/[^\w\s\-]/', '', $_GET['property_name']);
                $property = preg_replace('/\s{2,}/', ' ', trim($property));
                $_SESSION['roommate_search']['property'] = & $property;
            } else {
                unset($_SESSION['roommate_search']['property']);
            }
        }

        if (isset($_GET['d'])) {
            if ($_GET['d'] == 'any') {
                unset($_SESSION['roommate_search']['distance']);
            } else {
                $_SESSION['roommate_search']['distance'] = $_GET['d'];
            }
        }

        if (isset($_GET['p'])) {
            if ($_GET['p'] == 'any') {
                unset($_SESSION['roommate_search']['price']);
            } else {
                if (strstr($_GET['p'], '-')) {
                    list($min, $max) = explode('-', $_GET['p']);
                    $_SESSION['roommate_search']['price']['min'] = (int) $min;
                    $_SESSION['roommate_search']['price']['max'] = (int) $max;
                }
            }
        }

        if (isset($_GET['beds'])) {
            $_SESSION['roommate_search']['beds'] = $_GET['beds'];
        }

        if (isset($_GET['bath'])) {
            $_SESSION['roommate_search']['bath'] = $_GET['bath'];
        }

        if (isset($_GET['amen'])) {
            $_SESSION['roommate_search']['amenities'][$_GET['amen']] = 1;
        }

        if (isset($_GET['nosub'])) {
            unset($_SESSION['roommate_search']['sub']);
            $_SESSION['roommate_search']['nosub'] = 1;
        }

        if (isset($_GET['sub'])) {
            $_SESSION['roommate_search']['sub'] = 1;
            unset($_SESSION['roommate_search']['nosub']);
        }

        if (isset($_GET['gen'])) {
            $_SESSION['roommate_search']['gen'] = $_GET['gen'];
        }

        if (isset($_GET['smoke'])) {
            $_SESSION['roommate_search']['smoke'] = $_GET['smoke'];
        }

        \PHPWS_Cookie::write('roommate_search', serialize($_SESSION['roommate_search']));
    }

    public function searchPanel()
    {
        $vars['rop'] = 'search';
        $vars['d'] = 'any';
        $distances[] = \PHPWS_Text::moduleLink('Any', 'properties', $vars);
        $vars['d'] = '0';
        $distances[] = \PHPWS_Text::moduleLink('0 - 5 miles', 'properties', $vars);
        $vars['d'] = '5';
        $distances[] = \PHPWS_Text::moduleLink('5 - 10 miles', 'properties', $vars);
        $vars['d'] = '10';
        $distances[] = \PHPWS_Text::moduleLink('10 - 25 miles', 'properties', $vars);
        $vars['d'] = '25';
        $distances[] = \PHPWS_Text::moduleLink('Over 25 miles', 'properties', $vars);

        $tpl['DISTANCE_OPTIONS'] = '<ul><li>' . implode('</li><li>', $distances) . '</li></ul>';

        unset($vars['d']);

        $vars['gen'] = \GENDER_MALE;
        $tpl['GENDER_MALE'] = \PHPWS_Text::moduleLink('Male', 'properties', $vars);
        $vars['gen'] = \GENDER_FEMALE;
        $tpl['GENDER_FEMALE'] = \PHPWS_Text::moduleLink('Female', 'properties', $vars);
        unset($vars['gen']);

        $vars['smoke'] = SMOKER;
        $tpl['SMOKING_YES'] = \PHPWS_Text::moduleLink('Smoker preferred', 'properties', $vars);
        $vars['smoke'] = NONSMOKER;
        $tpl['SMOKING_NO'] = \PHPWS_Text::moduleLink('Non-smoker preferred', 'properties', $vars);
        unset($vars['smoke']);

        $vars['p'] = 'any';
        $prices[] = \PHPWS_Text::moduleLink('Any', 'properties', $vars);
        $vars['p'] = '0-100';
        $prices[] = \PHPWS_Text::moduleLink('$100 and under', 'properties', $vars);
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
        $prices[] = \PHPWS_Text::moduleLink('$1000 and above', 'properties', $vars);

        $tpl['PRICE_OPTIONS'] = '<ul><li>' . implode('</li><li>', $prices) . '</li></ul>';

        unset($vars['p']);
        $vars['beds'] = 1;
        $tpl['BEDROOM_CHOICE'] = \PHPWS_Text::moduleLink('Personal bedroom', 'properties', $vars);

        unset($vars['beds']);

        $vars['bath'] = 1;
        $tpl['BATHROOM_CHOICE'] = \PHPWS_Text::moduleLink('Personal bathroom', 'properties', $vars);

        unset($vars['bath']);

        $vars['sub'] = 1;
        $tpl['SUBLEASE'] = \PHPWS_Text::moduleLink('Sublease only', 'properties', $vars);

        unset($vars['sub']);

        $vars['nosub'] = 1;
        $tpl['NOSUB'] = \PHPWS_Text::moduleLink('No subleases', 'properties', $vars);

        unset($vars['nosub']);

        $features = null;
        $search = $this->loadSearchParameters();
        if (!@$search['amenities']['ac']) {
            $vars['amen'] = 'ac';
            $features[] = \PHPWS_Text::moduleLink('AppalCart', 'properties', $vars);
        }
        if (!@$search['amenities']['ch']) {
            $vars['amen'] = 'ch';
            $features[] = \PHPWS_Text::moduleLink('Clubhouse', 'properties', $vars);
        }
        if (!@$search['amenities']['dish']) {
            $vars['amen'] = 'dish';
            $features[] = \PHPWS_Text::moduleLink('Dishwasher', 'properties', $vars);
        }
        if (!@$search['amenities']['pet']) {
            $vars['amen'] = 'pet';
            $features[] = \PHPWS_Text::moduleLink('Pet allowed', 'properties', $vars);
        }
        if (!@$search['amenities']['tr']) {
            $vars['amen'] = 'tr';
            $features[] = \PHPWS_Text::moduleLink('Trash pickup', 'properties', $vars);
        }
        if (!@$search['amenities']['wo']) {
            $vars['amen'] = 'wo';
            $features[] = \PHPWS_Text::moduleLink('Workout room', 'properties', $vars);
        }
        if (!@$search['amenities']['wash']) {
            $vars['amen'] = 'wash';
            $features[] = \PHPWS_Text::moduleLink('Washer/Dryer', 'properties', $vars);
        }

        if ($features) {
            $tpl['FEATURES'] = '<ul><li>' . implode('</li><li>', $features) . '</li></ul>';
        }

        $tpl['CRITERIA'] = $this->getCriteria();

        unset($vars['amen']);
        $vars['clear'] = 1;
        $tpl['CLEAR'] = \PHPWS_Text::moduleLink('Clear all', 'properties', $vars);

        $content = \PHPWS_Template::process($tpl, 'properties', 'rm_search.tpl');
        \Layout::add($content, 'properties', 'search_settings');
    }

    private function getCancel($s)
    {
        $img = ' <i style="color : red" class="fa fa-times-circle"></i>';
        $vars['rop'] = 'remove';
        $vars['s'] = $s;
        return \PHPWS_Text::moduleLink($img, 'properties', $vars);
    }

    private function getCriteria()
    {
        $search = $this->loadSearchParameters();
        if (!empty($_SESSION['roommate_search'])) {
            foreach ($_SESSION['roommate_search'] as $key => $value) {
                switch ($key) {
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

                    case 'beds':
                        $criteria[] = "Personal bedroom" . $this->getCancel('beds');
                        break;

                    case 'bath':
                        $criteria[] = "Personal bathroom" . $this->getCancel('bath');
                        break;

                    case 'gen':
                        if ($value == GENDER_MALE) {
                            $gender = 'Prefer male roommate';
                        } else {
                            $gender = 'Prefer female roommate';
                        }
                        $criteria[] = $gender . $this->getCancel('gen');
                        break;

                    case 'price':
                        $criteria[] = sprintf('Price: $%s to $%s', $value['min'], $value['max']) . $this->getCancel('price');
                        break;

                    case 'smoke':
                        if ($value == NONSMOKER) {
                            $smoke = 'Nonsmoker prefered';
                        } else {
                            $smoke = 'Smoker prefered';
                        }
                        $criteria[] = $smoke . $this->getCancel('smoke');
                        break;

                    case 'amenities':
                        foreach ($value as $amen => $null) {
                            $criteria[] = $this->amenityTranslate($amen);
                        }
                        break;

                    case 'sub':
                        $criteria[] = 'Subleases only' . $this->getCancel('sub');
                }
            }
        }
        if (!empty($criteria)) {
            return implode("<br />", $criteria);
        }
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