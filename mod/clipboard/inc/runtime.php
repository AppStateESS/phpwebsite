<?php

if (!isset($_SESSION['Clipboard'])) {
  return;
}

Clipboard::show();
?>