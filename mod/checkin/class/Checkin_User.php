<?php
/**
 * Handles the user interaction with checkin
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('checkin', 'Checkin.php');

class Checkin_User extends Checkin {

    function checkinForm() {
        $form = new PHPWS_Form('checkin');
        $form->addHidden('module', 'checkin');
        $form->addHidden('uop', 'post_checkin');
        
        $form->addText('first_name');
        $form->setLabel('first_name', dgettext('checkin', 'First name'));
        $form->setRequired('first_name');
        
        $form->addText('last_name');
        $form->setLabel('last_name', dgettext('checkin', 'Last name'));
        $form->setRequired('last_name');
        
        $reasons = $this->getReasons();

        if (!empty($reasons)) {
            $form->addSelect('reason', $reasons);
            $form->setLabel('reason', dgettext('checkin', 'Reason for visit'));
        }
        $form->addSubmit(dgettext('checkin', 'Check in'));
        
        $tpl = $form->getTemplate();
        $this->title =  dgettext('checkin', 'Please check in using the form below');
        $this->content = PHPWS_Template::process($tpl, 'checkin', 'signin.tpl');
        Layout::add($this->main());
    }

    function main()
    {
        $tpl['TITLE'] = & $this->title;
        if (is_array($this->message)) {
            $tpl['MESSAGE'] = implode('<br />', $this->message);
        } else {
            $tpl['MESSAGE'] = $this->message;
        }

        $tpl['CONTENT'] = & $this->content;

        return PHPWS_Template::process($tpl, 'checkin', 'main.tpl');
    }

    function process()
    {
        @$command = $_REQUEST['uop'];
        switch ($command) {
        case 'post_checkin':
            if ($this->postCheckin()) {
                $this->visitor->save();
                echo 'done';
            } else {
                echo 'fail';
            }
            break;

            
        default:
            PHPWS_Core::errorPage('404');
        }
    }


    function postCheckin()
    {
        $this->loadVisitor();
        $this->visitor->firstname = $_POST['first_name'];
        $this->visitor->lastname  = $_POST['last_name'];
        $this->visitor->reason_id = (int)$_POST['reason'];

        if (empty($this->visitor->firstname)) {
            $this->message[] = dgettext('checkin', 'Please enter your first name.');
        }

        if (empty($this->visitor->lastname)) {
            $this->message[] = dgettext('checkin', 'Please enter your last name.');
        }
        return empty($this->message);
    }
}

?>