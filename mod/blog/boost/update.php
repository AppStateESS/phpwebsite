<?php

function blog_update(&$content, $currentVersion)
{
  switch ($currentVersion) {
  case version_compare($currentVersion, '0.0.3', '<'):
    $result = blog_update_003();
    if (PEAR::isError($result)) {
      return $result;
    }
    $content[] = '+ added the author column';
    break;
  }
  return TRUE;
}


function blog_update_003()
{
  $filename = PHPWS_SOURCE_DIR . 'mod/blog/boost/update_0_0_3.sql';
  $db = & new PHPWS_DB;
  return $db->importFile($filename);
}
?>