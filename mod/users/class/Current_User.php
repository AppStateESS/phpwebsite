<?php

class Current_User {
  
  function allow($itemName, $subpermission=NULL, $item_id=NULL){
    return $_SESSION['User']->allow($itemName, $subpermission, $item_id);
  }

}

?>