<?php

if (!isset($_REQUEST['module'])){
     $content = Blog_User::show();
     Layout::add($content, 'blog', 'view', TRUE);
}

?>