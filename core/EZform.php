<?php

/* Crutch file */

PHPWS_Core::initCoreClass('Form.php');

class EZform extends PHPWS_Form {
    function EZform($form_name) {
        parent::PHPWS_Form($form_name);
    }
}

?>