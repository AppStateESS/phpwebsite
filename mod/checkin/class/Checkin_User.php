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
        
        $form->addText('last_name');
        $form->setLabel('last_name', dgettext('checkin', 'Last name'));
        
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
        $tpl['MESSAGE'] = $this->message;
        $tpl['CONTENT'] = & $this->content;

        return PHPWS_Template::process($tpl, 'checkin', 'main.tpl');
    }

    function process()
    {
        @$command = $_REQUEST['uop'];
        switch ($command) {
            
        default:
            PHPWS_Core::errorPage('404');
        }
    }
}

?>