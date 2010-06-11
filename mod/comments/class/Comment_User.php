<?php

/**
 * Stores the user information specific to comments
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::requireConfig('comments');
PHPWS_Core::initModClass('comments', 'Comments.php');
PHPWS_Core::initModClass('demographics', 'Demographics.php');

class Comment_User extends Demographics_User {

    public $signature     = NULL;
    public $comments_made = 0;
    public $joined_date   = 0;
    public $avatar        = NULL;
    public $avatar_id     = 0;
    public $contact_email = NULL;
    public $website       = NULL;
    public $location      = NULL;
    public $locked        = 0;
    public $suspendmonitors = 0;
    public $monitordefault  = 1;
    public $securitylevel   = -1;
    public $groups          = null;

    // using a second table with demographics
    public $_table        = 'comments_users';


    public function __construct($user_id=NULL)
    {
        if ($user_id == 0) {
            $this->loadAnonymous();
            return;
        }
        $this->user_id = (int)$user_id;
        $this->load();
    }

    public function loadAnonymous()
    {
        $this->display_name = DEFAULT_ANONYMOUS_TITLE;
    }

    public function setSignature($sig)
    {
        if (empty($sig)) {
            $this->signature = NULL;
            return true;
        }
        // Signatures have a max length of 255
        $sig = substr(trim($sig), 0, 255);
        if (PHPWS_Settings::get('comments', 'allow_image_signatures')) {
            $this->signature = trim(strip_tags($sig, '<img>'));
        } else {
            if (preg_match('/<img/', $_POST['signature'])) {
                $this->_error[] = dgettext('comments', 'Image signatures not allowed.');
            }
            $this->signature = trim(strip_tags($sig));
        }
        return true;
    }

    public function getSignature()
    {
        return PHPWS_Text::parseOutput($this->signature, true, true);
    }

    public function bumpCommentsMade()
    {
        if (!$this->user_id) {
            return;
        }

        $db = new PHPWS_DB($this->_table);
        $db->addWhere('user_id', $this->user_id);
        $result = $db->incrementColumn('comments_made');
    }

    public function getJoinedDate($format=false)
    {
        if (empty($this->joined_date)) {
            $this->loadJoinedDate();
            $this->saveUser();
        }

        if ($format) {
            return strftime('%B, %Y', $this->joined_date);
        } else {
            return $this->joined_date;
        }
    }

    public function loadJoinedDate($date=NULL)
    {
        // If this is the current user, use the created date
        if (!isset($date)) {
            if ($this->user_id == Current_User::getId())
            $this->joined_date = Current_User::getCreatedDate();
            else { // otherwise, load the user's data
                $user = new PHPWS_User($this->user_id);
                $this->joined_date = $user->created;
            }
        } else {
            $this->joined_date = $date;
        }
    }

    /**
     * Sets an avatar's HTML tag and associated FileCabinet id .
     *
     * @param mixed $avatar : <integer> FileCabinet id, or <string> IMG tag, if not a FileCabinet file
     * @return none
     */
    public function setAvatar($avatar)
    {
        if (is_integer($avatar) && $avatar) {
            $this->avatar_id = $avatar;
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $file = Cabinet::getFile($this->avatar_id);
            $this->avatar = $file->getPath();
        }
        else {
            $this->avatar = $avatar;
            $this->avatar_id = 0;
        }
    }

    /**
     * Determines if the user can save custom and/or use remote avatars.
     *
     * @param none
     * @return none
     */
    public function getAvatarLevel()
    {

        // If user's securitylevel is not set, set it
        if ($this->securitylevel < 0) {
            $this->setCachedItems();
        }

        // admins get to use both
        if ($this->securitylevel > 0) {
            return array('local' => 1, 'remote' => 1);
        }

        $local = $remote = false;

        $user_ranks = Comments::getUserRanking();

        $user_groups = explode(',', $this->groups);
        if (!empty($user_ranks)) {
            foreach ($user_ranks as $rank) {
                if ($rank->group_id == 0 || in_array($rank->group_id, $user_groups)) {
                    $local = $local || $rank->allowLocal($this->comments_made);
                    $remote = $remote || $rank->allowRemote($this->comments_made);
                }
            }
        }
        return array('local' => $local, 'remote' => $remote);
    }

    /**
     * Retrieves an avatar's HTML tag.
     *
     * If there's no avatar tag, but there's a FileCabinet number listed,
     * it will retrieve & save a new avatar tag.
     * This is useful for when all cached avatar tags have been cleared
     * out because of corruption, server reconfig, or manual database editing.
     *
     * @param mixed $avatar : <integer> FileCabinet id, or <string> IMG tag, if not a FileCabinet file
     * @return none
     */
    public function getAvatar($format=true)
    {
        // If there's no avatar tag...
        if (empty($this->avatar)) {
            // if there's a FileCabinet number listed...
            if ($this->avatar_id) {
                // Retrieve & save a new avatar tag
                $this->setAvatar((int) $this->avatar_id);
                $this->saveUser();
            } else {
                return null;
            }
        }
        if ($format) {
            return sprintf('<img src="%s" />', $this->avatar);
        } else {
            return $this->avatar;
        }
    }

    public function setContactEmail($email_address)
    {
        if (PHPWS_Text::isValidInput($email_address, 'email')) {
            $this->contact_email = $email_address;
            return true;
        } else {
            return false;
        }
    }

    public function getContactEmail($format=false)
    {
        if ($format) {
            return '<a href="mailto:' . $this->contact_email . '" />' . $this->display_name . '</a>';
        } else {
            return $this->contact_email;
        }
    }

    public function setWebsite($website)
    {
        $this->website = strip_tags($website);
    }

    public function getWebsite($format=false)
    {
        if ($format && isset($this->website)) {
            return sprintf('<a href="%s" title="%s">%s</a>',
            PHPWS_Text::checkLink($this->website),
            sprintf(dgettext('comments', '%s\'s Website'), $this->display_name),
            dgettext('comments', 'Website'));
        } else {
            return $this->website;
        }
    }

    public function setLocation($location)
    {
        $this->location = strip_tags($location);
    }

    public function lock()
    {
        $this->locked = 1;
    }

    public function unlock()
    {
        $this->locked = 0;
    }


    public function kill()
    {
        if (preg_match('/^images\/comments/', $this->avatar) && !preg_match('@^http:@', $url)) {
            @unlink($this->avatar);
        }

        $db = new PHPWS_DB('comments_items');
        $db->addWhere('author_id', $this->user_id);
        $db->addValue('author_id', 0);
        PHPWS_Error::logIfError($db->update());

        $db = new PHPWS_DB('comments_monitors');
        $db->addWhere('user_id', $this->user_id);
        PHPWS_Error::logIfError($db->delete());

        return $this->delete();
    }

    public function hasError()
    {
        return isset($this->_error);
    }

    public function getError()
    {
        return $this->_error;
    }

    /**
     * Generates a user's template display tags.
     *
     * @param bool $isModerator  : Whether User is moderator of the phpwsbb forum that this thread may be in
     * @return array : Template display tags.
     */
    public function getTpl($isModerator=false)
    {
        $template['AUTHOR_NAME']   = $this->display_name;
        if ($this->user_id) {
            $template['COMMENTS_MADE'] = $this->getCommentsMade();
            $template['COMMENTS_MADE_LABEL'] = dgettext('comments', 'Posts');

            // Determine user's rank
            $rank = $this->getRank($isModerator);
            $template['RANK_TITLE'] = $rank['titles'];
            $template['RANK_IMG'] = $rank['images'];
            $template['RANK_LIST'] = $rank['composites'];
        }

        $str = sprintf(dgettext('comments', 'See all comments by %s'), $this->display_name);
        $signature = $this->getSignature();

        if (!empty($signature)) {
            $template['SIGNATURE'] = $signature;
            $template['SIGNATURE_LABEL'] = dgettext('comments', 'Signature');
        }

        if (!empty($this->joined_date)) {
            $template['JOINED_DATE'] = $this->getJoinedDate(true);
            $template['JOINED_DATE_LABEL'] = dgettext('comments', 'Joined');
        }

        if ($this->locked) {
            $template['AVATAR'] = CM_LOCK_IMAGE;
            $template['AUTHOR_NAME'] .= sprintf(' <span class="smaller">(%s)</span>', dgettext('comments', 'Locked'));
        } else {
            if (!empty($this->avatar) || !empty($this->avatar_id)) {
                $template['AVATAR'] = $this->getAvatar();
            }
        }

        if (!empty($this->contact_email)) {
            $template['CONTACT_EMAIL'] = $this->getContactEmail(true);
            $template['CONTACT_EMAIL_LABEL'] = dgettext('comments', 'Email');
        }

        if (!empty($this->website)) {
            $template['WEBSITE_LINK'] = $this->getWebsite(true);
            $template['WEBSITE'] = $this->getWebsite();
            $template['WEBSITE_LABEL'] = dgettext('comments', 'Website');
        }

        if (!empty($this->location)) {
            $template['LOCATION'] = $this->location;
            $template['LOCATION_LABEL'] = dgettext('comments', 'From');
        }

        return $template;
    }

    /**
     * Saves user's options from the My Page Form
     */
    public function saveOptions()
    {
        $errors = array();

        //signature
        if (PHPWS_Settings::get('comments', 'allow_signatures')) {
            $this->setSignature($_POST['signature']);
        } else {
            $this->signature = NULL;
        }

        //avatar
        // Get current Avatar permissions
        $perm = $this->getAvatarLevel();

        // If user wants to upload an image...
        if (!empty($_FILES['local_avatar']['name']) && $perm['local']) {
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $image = new PHPWS_Image;
            $image->setDirectory('images/comments/');
            $image->setMaxWidth(COMMENT_MAX_AVATAR_WIDTH);
            $image->setMaxHeight(COMMENT_MAX_AVATAR_HEIGHT);

            if (!$image->importPost('local_avatar', false, true)) {
                if (isset($image->_errors)) {
                    foreach ($image->_errors as $oError) {
                        $errors[] = $oError->getMessage();
                    }
                }
            } elseif ($image->file_name) {
                // the filename will be the user's id#
                $image->setFilename(Current_User::getId() .'.'. $image->getExtension());
                $result = $image->write();
                if (PHPWS_Error::logIfError($result)) {
                    $errors[] = array(dgettext('comments', 'There was a problem saving your image.'));
                } else {
                    $this->setAvatar($image->getPath());
                }
            }
        }
        // otherwise, if user wants to use a remote image...
        elseif (!empty($_POST['remote_avatar']) && $perm['remote']
        && strlen($_POST['remote_avatar']) > 10 && substr(trim($_POST['remote_avatar']),0,7) == 'http://'
        && $this->testAvatar($_POST['remote_avatar'], $errors)) {
            $this->setAvatar(trim($_POST['remote_avatar']));
        }
        // otherwise, use the selected gallery image...
        elseif (isset($_POST['avatar_id'])) {
            $this->setAvatar((int) $_POST['avatar_id']);
        }

        //website
        $this->setWebsite(@$_POST['website']);
        //location
        $this->setlocation(@$_POST['location']);
        //monitordefault
        if(!empty($_POST['monitordefault']))
        $this->monitordefault == (int) (bool) $_POST['monitordefault'];
        //suspendmonitors
        $this->suspendmonitors == (int) empty($_POST['suspendmonitors']);
        $db = new PHPWS_DB('comments_monitors');
        $db->addValue('suspended', $this->suspendmonitors);
        $db->addWhere('user_id', Current_User::getId());
        $db->update();
        //remove_all_monitors
        if(!empty($_POST['remove_all_monitors'])) {
            $this->unsubscribe(Current_User::getId(), 'all');
        }

        if (isset($_POST['order_pref'])) {
            PHPWS_Cookie::write('cm_order_pref', (int)$_POST['order_pref']);
        }

        /*        // need some error checking here
         if (empty($_POST['contact_email'])) {
         $this->contact_email = NULL;
         } else {
         if (!$this->setContactEmail($_POST['contact_email'])) {
         $errors[] = dgettext('comments', 'Your contact email is formatted improperly.');
         }
         }
         */

        if (!empty($errors)) {
            return $errors;
        } else {
            return $this->saveUser();
        }
    }

    public function saveUser()
    {
        $result = $this->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        $this->_base_id = $this->_extend_id = $this->user_id;
        if (PHPWS_Error::logIfError($result) || !$result) {
            $this->_error = null;
            return false;
        }
        return true;
    }

    public function getPostTpl($values)
    {
        // Create Comment_Item class
        $comment = new Comment_Item();
        PHPWS_Core::plugObject($comment, $values);
        // Get associated Comment_Thread
        if (!isset($GLOBALS['cm_threads'][$comment->thread_id]))
        $GLOBALS['cm_threads'][$comment->thread_id] = new Comment_Thread($comment->thread_id);
        $thread = & $GLOBALS['cm_threads'][$comment->thread_id];
        // If there's an associated PHPWSBB_Topic, get template variables
        if (!empty($thread->phpwsbb_topic))
        $t_tags = $thread->phpwsbb_topic->_get_tags();
        // otherwise, make some up
        else {
            $t_tags['THREAD_TITLE_LINK'] = $thread->_key->getUrl();
            $t_tags['THREAD_TITLE_LABEL'] = dgettext('comments', 'In').' '.dgettext($thread->_key->module, $thread->_key->item_name);
            $t_tags['THREAD_REPLIES'] = $thread->total_comments;
            $t_tags['THREAD_REPLIES_LABEL'] = dgettext('comments', 'Posts');
        }
        $template = array_merge($t_tags, $comment->getTpl($thread));
        return $template;
    }

    public function list_posts()
    {
        Layout::addStyle('comments');
        PHPWS_Core::initCoreClass('DBPager.php');

        $time_period = array('all'    => dgettext('comments', 'All'),
                             'today'  => dgettext('comments', 'Today'),
                             'yd'     => dgettext('comments', 'Since yesterday'),
                             'week'   => dgettext('comments', 'This week'),
                             'month'  => dgettext('comments', 'This month')
        );

        $order_list = array('old_all'  => dgettext('comments', 'Oldest first'),
                            'new_all'  => dgettext('comments', 'Newest first'));

        $pager = new DBPager('comments_items');
        $pager->setAnchor('comments');
        $form = new PHPWS_Form;

        $getVals = PHPWS_Text::getGetValues();
        if (!empty($getVals)) {
            $referer[] = 'index.php?';
            foreach ($getVals as $key=>$val) {
                $referer[] = "$key=$val";
            }
            $form->addHidden('referer', urlencode(implode('&', $referer)));
        }

        $form->addHidden('module', 'comments');
        $form->addHidden('uop', 'user_posts');
        $form->addHidden('user_id', $this->user_id);
        $form->addSelect('time_period', $time_period);
        $form->addSelect('order', $order_list);

        // set where clauses
        if (isset($_GET['time_period']) && $_GET['time_period'] != 'all') {
            $form->setMatch('time_period', $_GET['time_period']);
            $time_period = Comment_Thread::_getTimePeriod();
            $pager->addWhere('create_time', $time_period, '>=');
        }

        $pager->addWhere('author_id', $this->user_id);

        if (isset($_GET['order'])) {
            $default_order = &$_GET['order'];
        } else {
            $default_order = PHPWS_Settings::get('comments', 'default_order');
        }

        switch ($default_order) {
            case 'new_all':
                $pager->setOrder('create_time', 'desc');
                break;

            case 'old_all':
                $pager->setOrder('create_time', 'asc');
                break;
        }
        $form->setMatch('order', $default_order);

        $form->noAuthKey();
        $form->addSubmit(dgettext('comments', 'Go'));
        $form->setMethod('get');

        $page_tags = $form->getTemplate() + $this->getTpl();

        $pager->setModule('comments');
        $pager->setTemplate('list_posts.tpl');
        $pager->setCacheIdentifier('list_posts_'.$this->user_id);
        $pager->cacheQueries();
        $pager->addPageTags($page_tags);
        $pager->addRowFunction(array('Comment_User', 'getPostTpl'));
        $pager->setLimitList(array(10, 20, 50));
        $pager->setDefaultLimit(COMMENT_DEFAULT_LIMIT);
        $pager->setEmptyMessage(dgettext('comments', 'No comments'));
        $pager->initialize();
        $rows = $pager->getRows();
        if (!empty($rows))
        Comment_Thread::_createUserList($rows);

        $content = $pager->get();

        //        $GLOBALS['comments_viewed'] = true;

        return $content;
    }

    /**
     * Generates a user's rank tags.
     *
     * @param bool $isModerator  : Whether User is moderator of the phpwsbb forum that this thread may be in
     * @return string : HTML code of all user's ranks.
     */
    public function getRank($isModerator)
    {
        $images = $titles = $composites = array();
        $user_ranks = Comments::getUserRanking();

        if (empty($user_ranks)) {
            return;
        }

        // If user's securitylevel is not set, set it
        if ($this->securitylevel < 0) {
            $this->setCachedItems();
        }

        // If user is a supermoderator, show that rank
        if ($this->securitylevel == 2) {
            $titles[] = $str = dgettext('comments', 'Super Moderator');
            $images[] = $composites[] = '<div class="comment_supermod_icon"><span>'.$str."</span></div>\n";
        }     // otherwise, check for admin status
        elseif ($this->securitylevel == 1) {
            $titles[] = $str = dgettext('comments', 'Moderator');
            // if user is a moderator of this specific forum or unattached thread...
            if ($isModerator) {
                $images[] = $composites[] = '<div class="comment_activemod_icon"><span>'.$str."</span></div>\n";
            } else {
                $images[] = $composites[] = '<div class="comment_inactivemod_icon"><span>'.$str."</span></div>\n";
            }
        }

        $user_ranks = Comments::getUserRanking();
        $user_groups = explode(',', $this->groups);

        // Loop through all relevant usergroups to generate rank tags
        if (!empty($user_ranks)) {
            foreach ($user_ranks as $rank) {
                if ( ($rank->group_id == 0 || in_array($rank->group_id, $user_groups)) &&
                !empty($rank->user_ranks) ) {
                    /*
                     foreach ($rank->user_ranks as $user_rank) {
                     if ($user_rank->min_posts <= $this->comments_made) {
                     $user_rank->loadInfo($images, $composites, $titles);
                     }
                     }
                     */
                    foreach ($rank->user_ranks as $key => $user_rank) {
                        if ($user_rank->min_posts <= $this->comments_made) {
                            $pick = $key;
                        } else {
                            break;
                        }
                    }
                    $rank->user_ranks[$pick]->loadInfo($images, $composites, $titles);
                }
            }
        }

        $images = implode('', $images);
        if (substr($images, -7, 7) == "<br />\n") {
            $images = substr($images, 0, -7);
        }

        $composites = implode('', $composites);
        if (substr($composites, -7, 7) == "<br />\n") {
            $composites = substr($composites, 0, -7);
        }
        return array('titles' => implode("<br />\n", $titles), 'images' => $images, 'composites' => $composites);
    }

    /*
     * Sets cached authorization level & group membership for this user.
     *
     * @param none
     * @return none
     */
    public function setCachedItems()
    {
        // If this is the current user the object is already loaded
        if ($this->user_id == Current_User::getId()) {
            $user = $_SESSION['User'];
        } else {
            $user = new PHPWS_User($this->user_id);
        }

        // If user is a supermoderator...
        if (isset($GLOBALS['Modules']['phpwsbb']) && $user->allow('phpwsbb', 'manage_forums')) {
            $securitylevel = 2;
        }
        // otherwise, check for admin status
        elseif ($user->allow('comments')) {
            $securitylevel = 1;
        }
        // otherwise, this is a regular user
        else {
            $securitylevel = 0;
        }

        // Update this user's group membership cache
        $groups = null;
        if ($grouplist = $user->getGroups()) {
            $groups = implode(',', $grouplist);
        }

        if ($this->securitylevel != $securitylevel || $this->groups != $groups) {
            $this->securitylevel = $securitylevel;
            $this->groups = $groups;
            $this->saveUser();
        }
    }

    /*
     * Sets a monitor flag (subscribes) to a thread.
     *
     * @param int $user_id : User's id (unregistered users cannot subscribe)
     * @param int $thread_id : Thread id
     * @return bool : TRUE or FALSE
     */
    public function subscribe($user_id, $thread_id)
    {
        if (empty($user_id) || empty($thread_id))
        return;
        $db = new PHPWS_DB('comments_monitors');
        $db->addValue('thread_id', (int) $thread_id);
        $db->addValue('user_id', (int) $user_id);
        return PHPWS_Error::logIfError($db->insert());
    }

    /*
     * Removes a monitor flag (unsubscribes) to a thread.
     *
     * @param int $user_id : User's id (unregistered users cannot subscribe)
     * @param int $thread_id : Thread id or 'all' if all threads are being unsubscribed
     * @return bool : TRUE or FALSE
     */
    public function unsubscribe($user_id, $thread_id)
    {
        if (empty($user_id) || empty($thread_id))
        return;
        $db = new PHPWS_DB('comments_monitors');
        if ($thread_id !== 'all')
        $db->addWhere('thread_id', (int) $thread_id);
        $db->addWhere('user_id', (int) $user_id);
        return PHPWS_Error::logIfError($db->delete());
    }

    /**
     * Tests an image's url to see if it is the correct file type,
     * dimensions, etc.
     */
    public function testAvatar($url, &$errors)
    {
        if (!preg_match('@^http:@', $url)) {
            $errors[] = dgettext('comments', 'Avatar graphics must be from offsite.');
            return false;
        }

        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $ext = PHPWS_File::getFileExtension($url);
        echo $ext;

        if (!PHPWS_Image::allowImageType($ext)) {
            $errors[] = dgettext('comments', 'Unacceptable image file.');
            return false;
        }

        if (!PHPWS_File::checkMimeType($url, $ext)) {
            $errors[] = dgettext('comments', 'Unacceptable file type.');
            return false;
        }

        $test = @getimagesize($url);

        if (!$test || !is_array($test)) {
            $errors[] = dgettext('comments', 'Could not verify file dimensions.');
            return false;
        }


        if (COMMENT_MAX_AVATAR_WIDTH < $test[0] || COMMENT_MAX_AVATAR_HEIGHT < $test[1]) {
            $errors[] = sprintf(dgettext('comments', 'Your avatar must be smaller than %sx%spx.'),
            COMMENT_MAX_AVATAR_WIDTH, COMMENT_MAX_AVATAR_HEIGHT);
            return false;
        }

        return true;
    }

    /**
     * Returns the number of comments made by a user that is linked to their comment history
     */
    function getCommentsMade()
    {
        if (empty($this->user_id)) {
            // an anonymous user. return nothing
            return null;
        }
        $vars['uop'] = 'cm_history';
        $vars['uid'] = $this->user_id;
        return PHPWS_Text::moduleLink($this->comments_made, 'comments', $vars);
    }
}

?>
