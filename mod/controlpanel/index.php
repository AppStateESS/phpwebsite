<?php


if(!isset($_SESSION['PHPWS_ControlPanel'])) {
  $_SESSION['PHPWS_ControlPanel'] = new PHPWS_ControlPanel;
}

if (isset($_GET['cp_image_toggle'])){
  PHPWS_ControlPanel_Tab::toggleImage($_GET['tab']);
}

if (isset($_GET['cp_desc_toggle'])){
  PHPWS_ControlPanel_Tab::toggleDesc($_GET['tab']);
}

$_SESSION['PHPWS_ControlPanel']->display();

?>