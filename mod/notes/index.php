<?php

if (!isset($_REQUEST['command'])) {
  return;
}

switch ($_REQUEST['command']) {
 case 'close_notes':
   $_SESSION['No_Notes'] = 1;
   PHPWS_Core::goBack();
   break;
}

?>