<?php
PHPWS_Core::initModClass('comments', 'Comment_User.php');

class Comments_My_Page {
  
  function main()
  {
    if (isset($_REQUEST['my_page_op'])) {
      $command = &$_REQUEST['my_page_op'];
    } else {
      $command = 'main';
    }
    
    switch ($command) {
    case 'main':
      $title = "MAIN!";
      $content = 'whatever';
      break;
      
    }

    return Layout::boxUp($title, $content);
  }

  function editOptions()
  {
    $comment_user = & new Comment_User(Current_User::getId());
    
    $form = & new PHPWS_Form;
    $hidden['module'] = 'users';
    $hidden['action'] = 'user';
    $hidden['tab']    = 'comments';
    $form->addHidden($hidden);
    $form->addText('signature', $comment_user->getSignature());

    $template = $form->getTemplate();
    
  }

}

?>