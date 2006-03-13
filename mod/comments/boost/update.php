<?php

function comments_update(&$content, $currentVersion)
{
  switch ($currentVersion) {
  case version_compare($currentVersion, '0.0.2', '<'):
    $result = comments_update_002($content);
    if (PEAR::isError($result)) {
      return $result;
    } elseif (!$result) {
        return false;
    } else {
        $content[] = '+ Added a shortcut icon.';
    }
    break;

  case version_compare($currentVersion, '0.0.3', '<'):
    $result = comments_update_003($content);
    if (PEAR::isError($result)) {
      return $result;
    }
    break;

  }
  return TRUE;
}


function comments_update_002(&$content)
{
    PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');
    if (!@mkdir('images/mod/comments')) {
        $content[] = 'Unable to create image directory.';
        return FALSE;
    }
    return PHPWS_ControlPanel::registerModule('comments', $content);
}

function comments_update_003(&$content) {
    $content[] = 'Update control panel link.';
    $db = & new PHPWS_DB('controlpanel_link');
    $db->addWhere('itemname', 'comments');
    $db->addValue('url', 'index.php?module=comments&admin_action=admin_menu');
    return $db->update();
}

?>