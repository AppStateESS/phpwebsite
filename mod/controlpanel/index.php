<?php

if (isset($_GET['cp_image_toggle'])){
  PHPWS_ControlPanel_Tab::toggleImage($_GET['tab']);
}
     
if (isset($_GET['cp_desc_toggle'])){
  PHPWS_ControlPanel_Tab::toggleDesc($_GET['tab']);
}
     
if ($_SESSION['User']->isLogged()){
  Layout::add(PHPWS_ControlPanel::display());
}

?>