<?php

function comments_unregister($module, &$content)
{
  PHPWS_Core::initModClass('comments', 'Comments.php');
  $content[] = _('Removing module\'s comments.');
  if (Comments::unregister($module)) {
    $content[] = _('Comments removed successfully');
  } else {
    $content[] = _('An error occurred when trying to remove comments.');
  }
}

?>