<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Comment_Rank {
    public $id                   = 0;
    public $group_id             = 0;
    public $allow_local_avatars  = false;
    public $minimum_local_posts  = 100;
    public $allow_remote_avatars = false;
    public $minimum_remote_posts = 1000;
    public $user_ranks           = null;
    public $group_name           = null;

    public function __construct($id=0, $load_user_ranks=true)
    {
        $this->id = (int)$id;
        $this->init($load_user_ranks);
    }

    private function init($load_user_ranks=true)
    {
        $db = new PHPWS_DB('comments_ranks');
        $result = $db->loadObject($this);

        if (!$this->group_id) {
            $this->group_name = dgettext('comments', 'All Members');
        }

        if ($load_user_ranks) {
            $this->loadUserRanks();
        }
    }

    public function postPlug($load_user_ranks=true)
    {
        if ($load_user_ranks) {
            $this->loadUserRanks();
        }
    }

    public function loadUserRanks()
    {
        PHPWS_Core::initModClass('comments', 'User_Rank.php');
        $db = new PHPWS_DB('comments_user_ranks');
        $db->setIndexBy('id');
        $db->addWhere('rank_id', $this->id);
        $db->addOrder('min_posts');
        $result = $db->getObjects('Comment_User_Rank');

        if (!PHPWS_Error::logIfError($result)) {
            $this->user_ranks = & $result;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('comments_ranks');
        $result = $db->saveObject($this);

        return !PHPWS_Error::logIfError($result);
    }

    public function delete()
    {
        // no group id means it is the default rank
        if (!$this->group_id) {
            return false;
        }
        $db = new PHPWS_DB('comments_ranks');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        $db = new PHPWS_DB('comments_user_ranks');
        $db->addWhere('rank_id', $this->id);
        return !PHPWS_Error::logIfError($db->delete());
    }

    public function allowLocal($comments_made)
    {
        return $this->allow_local_avatars && (int)$comments_made >= $this->minimum_local_posts;
    }

    public function allowRemote($comments_made)
    {
        return $this->allow_remote_avatars && (int)$comments_made >= $this->minimum_remote_posts;
    }
}

?>