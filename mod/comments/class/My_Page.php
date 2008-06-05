<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
PHPWS_Core::initModClass('comments', 'Comment_User.php');

class Comments_My_Page {
  
    function main()
    {
        
        if (isset($_REQUEST['my_page_op'])) {
            $command = &$_REQUEST['my_page_op'];
        } else {
            $command = 'main';
        }
   
        $message = Comments_My_Page::getMessage();
 
        switch ($command) {
        case 'main':
            $title = dgettext('comments', 'Comment Settings');
            $C_User = new Comment_User(Current_User::getId());
            $content = Comments_My_Page::editOptions($C_User);
            break;

        case 'save_options':
            $C_User = new Comment_User(Current_User::getId());
            $result = $C_User->saveOptions();
            if (is_array($result)) {
                $message = implode('<br />', $result);
                $title = dgettext('comments', 'Comment Settings');
                $content = Comments_My_Page::editOptions($C_User);
            } else {
                Comments_My_Page::sendMessage(dgettext('comments', 'Settings saved.'));
            }
            break;
        }

        $box['TITLE'] = &$title;
        $box['CONTENT'] = &$content;
        if (isset($message)) {
            $box['MESSAGE'] = &$message;
        }
        
        return PHPWS_Template::process($box, 'comments', 'my_page.tpl');
    }

    function sendMessage($message, $command=NULL)
    {
        $_SESSION['Comment_Message'] = $message;
        PHPWS_Core::reroute('index.php?module=users&action=user&tab=comments');
    }

    function getMessage()
    {
        if (!isset($_SESSION['Comment_Message'])) {
            return NULL;
        }
        $message = $_SESSION['Comment_Message'];
        unset($_SESSION['Comment_Message']);
        return $message;
    }

    function editOptions($user)
    {
        $form = new PHPWS_Form;
        $hidden['module'] = 'users';
        $hidden['action'] = 'user';
        $hidden['tab']    = 'comments';
        $hidden['my_page_op'] = 'save_options';

        $form->addHidden($hidden);
        if (PHPWS_Settings::get('comments', 'allow_signatures')) {
            $form->addText('signature', $user->getSignature());
            $form->setSize('signature', 60);
            $form->setMaxSize('signature', 254);
            $form->setLabel('signature', dgettext('comments', 'Signature'));
        }

        $form->addTplTag('CURRENT_AVATAR', $user->getAvatar(TRUE));

        if (PHPWS_Settings::get('comments', 'allow_avatars')) {
            if (PHPWS_Settings::get('comments', 'local_avatars')) {
                $form->addFile('avatar');
            } else {
                $form->addText('avatar', $user->getAvatar(false));
                $form->setSize('avatar', 60);
            }
            $form->setLabel('avatar', dgettext('comments', 'Avatar'));
        }
            
        $form->addText('contact_email', $user->getContactEmail());
        $form->setLabel('contact_email', dgettext('comments', 'Contact Email'));
        $form->setSize('contact_email', 40);

        $form->addSelect('order_pref', array(1=>dgettext('comments', 'Oldest first'),
                                             2=>dgettext('comments', 'Newest first')));
        $form->setLabel('order_pref', dgettext('comments', 'Comment order preference'));
        $form->setMatch('order_pref', PHPWS_Cookie::read('cm_order_pref'));

        $form->addSubmit(dgettext('comments', 'Update'));
        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'comments', 'user_settings.tpl');
    }

}

?>