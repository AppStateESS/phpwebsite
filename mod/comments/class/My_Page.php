<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
PHPWS_Core::initModClass('comments', 'Comment_User.php');

class Comments_My_Page {

    public function main()
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
                $message = dgettext('comments', 'Settings saved.');
                $content = Comments_My_Page::editOptions($C_User);
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

    public function sendMessage($message, $command=NULL)
    {
        $_SESSION['Comment_Message'] = $message;
        PHPWS_Core::reroute('index.php?module=users&action=user&tab=comments');
    }

    public function getMessage()
    {
        if (!isset($_SESSION['Comment_Message'])) {
            return NULL;
        }
        $message = $_SESSION['Comment_Message'];
        unset($_SESSION['Comment_Message']);
        return $message;
    }

    public function editOptions($user)
    {
        $form = new PHPWS_Form;
        $hidden['module'] = 'users';
        $hidden['action'] = 'user';
        $hidden['tab']    = 'comments';
        $hidden['my_page_op'] = 'save_options';

        $form->addHidden($hidden);
        //signature
        if (PHPWS_Settings::get('comments', 'allow_signatures')) {
            $form->addTextarea('signature', $user->signature);
            $form->setWidth('signature', '95%');
            $form->setRows('signature', 3);

            $form->setLabel('signature', dgettext('comments', 'Your Signature'));
            $form->addTplTag('SIGNATURE_HELP', dgettext('comments', 'This signature appears at the bottom of your comments.  It must be less than 255 characters, not annoying and compliant with site rules.'));
        }

        // Get current Avatar permissions
        $perm = $user->getAvatarLevel();

        //avatar
        if (PHPWS_Settings::get('comments', 'allow_avatars') && ($perm['local'] || $perm['remote'])) {
            $form->setEncode();
            $form->addTplTag('AVATAR_LABEL', dgettext('comments', 'Avatar'));
            $form->addTplTag('AVATAR_NOTE', sprintf(dgettext('comments', 'Note: Avatar images must be no greater than %1$s pixels high by %2$s pixels wide, and its filesize can be no greater than %3$sKb.'), COMMENT_MAX_AVATAR_HEIGHT, COMMENT_MAX_AVATAR_WIDTH, 20));
            if (!empty($user->avatar) || !empty($user->avatar_id)) {
                $form->addTplTag('CURRENT_AVATAR_LABEL', dgettext('comments', 'Current Avatar'));
                $form->addTplTag('CURRENT_AVATAR_IMG', $user->getAvatar());
            }

            // Show Avatar Gallery selection script
            if (PHPWS_Settings::get('comments', 'avatar_folder_id')) {
                echo 'avatar folder needed in My_Page.php';
                $manager = Cabinet::fileManager('avatar_id', $user->avatar_id);
                $manager->setMaxWidth(COMMENT_MAX_AVATAR_WIDTH);
                $manager->setMaxHeight(COMMENT_MAX_AVATAR_HEIGHT);
                $manager->setMaxSize(22000);
                $manager->moduleLimit();
                $manager->forceResize();
                $manager->imageOnly(false, false);
                $manager->setPlaceholderMaxWidth(COMMENT_MAX_AVATAR_WIDTH);
                $manager->setPlaceholderMaxHeight(COMMENT_MAX_AVATAR_HEIGHT);
                $form->addTplTag('GALLERY_AVATAR', $manager->get());
                $form->addTplTag('GALLERY_AVATAR_LABEL', dgettext('comments', 'Select an avatar from the gallery'));
            }
            
            // If local Custom Avatars are allowed, show file field
            if ($perm['local']) {
                $form->addFile('local_avatar');
                $form->setLabel('local_avatar', dgettext('comments', 'Upload an avatar image from your computer'));
                $form->addTplTag('LOCAL_CHOICE', dgettext('comments', 'Use a private local image'));
            }

            // If remote Custom Avatars are allowed, show URL text field
            if ($perm['remote']) {
                if (substr($user->avatar,0,7) == 'http://') {
                    $url = $user->getAvatar(false);
                } else {
                    $url = 'http://';
                }

                $form->addText('remote_avatar', $url);
                $form->setSize('remote_avatar', 60);
                $form->setLabel('remote_avatar', dgettext('comments', 'Enter a URL to an online image. (ex: `http://othersite.com/avatar.png`)'));
                $form->addTplTag('REMOTE_CHOICE', dgettext('comments', 'Use a remotely-hosted image'));
            }
            $form->addTplTag('AVATAR_MESSAGE', dgettext('comments', 'You may add an avatar using ONE of the methods below.'));
        }
        /*    //contact-email
        $form->addText('contact_email', $user->getContactEmail());
        $form->setLabel('contact_email', dgettext('comments', 'Contact Email'));
        $form->setSize('contact_email', 40);

        */
        //order_pref
        $form->addSelect('order_pref', array(1=>dgettext('comments', 'Oldest first'),
                                             2=>dgettext('comments', 'Newest first')));
        $form->setLabel('order_pref', dgettext('comments', 'Comment order preference'));
        $form->setMatch('order_pref', PHPWS_Cookie::read('cm_order_pref'));
        //website
        $form->addText('website', $user->website);
        $form->setSize('website', 60);
        $form->setMaxSize('website', 60);
        $form->setLabel('website', dgettext('comments', 'Website'));
        //location
        $form->addText('location', $user->location);
        $form->setSize('location', 50);
        $form->setMaxSize('location', 50);
        $form->setLabel('location', dgettext('comments', 'Where are you?'));
        //monitordefault
        $form->addCheckBox('monitordefault');
        $form->setMatch('monitordefault', $user->monitordefault);
        $form->setLabel('monitordefault', dgettext('comments', 'Automatically monitor topics that I post in'));
        $form->addTplTag('MONITORDEFAULT_HELP', dgettext('comments', 'When you create a new topic or post a comment, you can choose to automatically receive email notification of new replies to that topic.'));
        //suspendmonitors
        $form->addCheckBox('suspendmonitors');
        $form->setMatch('suspendmonitors', $user->suspendmonitors);
        $form->setLabel('suspendmonitors', dgettext('comments', 'Temporarily stop monitoring all topics'));
        //remove_all_monitors
        $form->addCheckBox('remove_all_monitors');
        $form->setMatch('remove_all_monitors', 0);
        $form->setLabel('remove_all_monitors', dgettext('comments', 'Permanently stop monitoring all topics'));
        $form->addTplTag('REMOVE_ALL_MONITORS_HELP', dgettext('comments', 'Be particularly careful with this next option.  If you select it, the only way that you can restore your monitors is to manually re-select each topic!'));

        // Section Titles
        $form->addTplTag('PERSONAL_HEADER', dgettext('comments', 'Personal Information'));
        $form->addTplTag('MONITOR_HEADER', dgettext('comments', 'Monitoring Topics'));

        $form->addSubmit(dgettext('comments', 'Update'));
        $template = $form->getTemplate();
        
        return PHPWS_Template::process($template, 'comments', 'user_settings.tpl');
    }

}

?>
