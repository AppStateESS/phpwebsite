<?php

function users_update(&$content, $currentVersion)
{

  switch ($currentVersion) {

  case version_compare($currentVersion, '2.0.2', '<'):
    $result = users_update_202($content);
    if (PEAR::isError($result)) {
      return $result;
    }
    $content[] = '+ added ability to pick a default user menu.';
    $content[] = '+ added graphi confirmation option';
    $content[] = '- dropped default_group column';
    break;
  }

  return TRUE;
}

function users_update_202(&$content)
{
  $filename = PHPWS_SOURCE_DIR . 'mod/users/boost/update_2_0_2.sql';
  $db = & new PHPWS_DB;
  return $db->importFile($filename);
}

?>