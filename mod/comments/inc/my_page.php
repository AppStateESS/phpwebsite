<?php

function my_page(){
  PHPWS_Core::initModClass('comments', 'My_Page.php');
  return Comments_My_Page::main();
}

?>