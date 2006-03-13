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

    case 'save_options':
        Comments_My_Page::saveOptions();
        break;
      
    }

    $box['TITLE'] = &$title;
    $box['CONTENT'] = &$content;
    return PHPWS_Template::process($box, 'comments', 'my_page.tpl');
  }

  function saveOptions()
  {
      if (PHPWS_Settings::get('comments', 'allow_image_signatures')) {
          $signature = trim(strip_tags($_POST['signature'], '<img>'));
      } else {
          if (preg_match('/<img/', $_POST['signature'])) {
              $errors[] = _('Image signatures not allowed.');
          }
          $signature = trim(strip_tags($_POST['signature']));
      }

      if (empty($signature)) {
          $val['signature'] = NULL;
      } else {
          $val['signature'] = $signature;
      }
      
      if (empty($_POST['picture'])) {
          $val['picture'] = NULL;
      } else {
          $image_info = @getimagesize($_POST['picture']);
          if (!$image_info) {
              $errors[] = _('Could not access image url.');
          }
      }

      

      test($val);
  }

  function editOptions()
  {
    $comment_user = & new Comment_User(Current_User::getId());
    
    $form = & new PHPWS_Form;
    $hidden['module'] = 'users';
    $hidden['action'] = 'user';
    $hidden['tab']    = 'comments';
    $hidden['my_page_op'] = 'save_options';
    
    $form->addHidden($hidden);
    $form->addText('signature', $comment_user->getSignature());
    $form->setSize('signature', 60);
    $form->setMaxSize('signature', 254);
    $form->setLabel('signature', _('Signature'));
    if (PHPWS_Settings::get('comments', 'allow_avatars')) {
        if (PHPWS_Settings::get('comments', 'local_avatars')) {
            $form->addFile('local_pic', $comment_user->getPicture()); 
        } else {
            $form->addText('remove_pic', $comment_user->getPicture());
        }
    }

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