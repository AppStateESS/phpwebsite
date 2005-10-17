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
  }
  return TRUE;
}


function comments_update_002(&$content)
{
    PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');
    if (!@mkdir('images/mod/comments')) {
        $content[] = _('Unable to create image directory.');
        return FALSE;
    }
    return PHPWS_ControlPanel::registerModule('comments', $content);
}

?>