<?php

if (!isset($_SESSION['Clipboard'])) {
  Clipboard::init();
}

Clipboard::action();

?>