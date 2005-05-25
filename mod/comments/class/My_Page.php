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
      $title = _('Comment Settings');
      $content = Comments_My_Page::editOptions();
      break;
      
    }

    $box['TITLE'] = &$title;
    $box['CONTENT'] = &$content;
    return PHPWS_Template::process($box, 'comments', 'my_page.tpl');
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
    $form->setSize('signature', 60);
    $form->setMaxSize('signature', 254);
    $form->setLabel('signature', _('Signature'));
    $form->addText('picture', $comment_user->getPicture());
    $form->setLabel('picture', _('Picture'));
    $form->setSize('picture', 60);
    $form->addText('contact_email', $comment_user->getContactEmail());
    $form->setLabel('contact_email', _('Contact Email'));
    $form->setSize('contact_email', 40);
    $form->addSubmit(_('Update'));

    $template = $form->getTemplate();
    return PHPWS_Template::process($template, 'comments', 'user_settings.tpl');
  }

}

?>