<?php

if (!isset($_REQUEST['module'])) {
  Block::show(Key::getHomeKey());
}

?>