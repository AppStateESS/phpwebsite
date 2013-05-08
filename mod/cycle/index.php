<?php
if (isset($_REQUEST['aop'])) {
    if (!Current_User::allow('cycle')) {
        Current_User::disallow();
    }
    PHPWS_Core::initModClass('cycle', 'Cycle.php');

    $cycle = new Cycle;
    if (!empty($_POST)) {
        $cycle->post();
    } else {
        $cycle->get();
    }
}
?>