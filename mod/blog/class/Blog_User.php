<?php

class Blog_User {

  function show(){
    $limit = 5;

    $db = & new PHPWS_DB("blog_entries");
    $db->setLimit($limit);
    $db->addOrder("date desc");
    $result = $db->getObjects("Blog");
    
    foreach ($result as $blog){
      Layout::add($blog->view());
    }
  }

}

?>