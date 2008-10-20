<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Comment_User_Rank {
    public $id           = 0;
    public $rank_id      = 0;
    public $title        = null;
    public $min_posts    = 1;
    public $image        =null;
    public $stack        = 1;
    public $repeat_image = false;

    public function __construct($id=0)
    {
        $this->id = (int)$id;
        
        if ($this->id) {
            $this->init();
        }
    }

    private function init()
    {
        $db = new PHPWS_DB('comments_user_ranks');
        if (PHPWS_Error::logIfError($db->loadObject($this)) || !$this->id) {
            $this->id = 0;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('comments_user_ranks');
        return !PHPWS_Error::logIfError($db->saveObject($this));
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setMinPosts($posts)
    {
        $this->min_posts = (int)$posts;
        if ($this->min_posts < 0) {
            $this->min_posts = 1;
        }
    }

    public function setImage($image)
    {
        $allowed = array('jpg', 'gif', 'png');
        $ext = PHPWS_File::getFileExtension($image);
        if (is_file($image) && in_array($ext, $allowed)) {
            $this->image = $image;
        } else {
            $this->image = null;
        }
    }

    public function setRepeatImage($repeat)
    {
        $repeat = (int)$repeat;
        if ($repeat < 0 || $repeat > 20) {
            $this->repeat_image = 1;
        } else {
            $this->repeat_image = $repeat;
        }
    }

    public function delete()
    {
        $db = new PHPWS_DB('comments_user_ranks');
        $db->addWhere('id', $this->id);
        return !PHPWS_Error::logIfError($db->delete());
    }

    function getImage()
    {
        $tag = sprintf('<img src="%s" class="user_rank" alt="%s" title="%s" />',
                       $this->image, $this->title, $this->title);
        return $tag;
    }

    function loadInfo(&$images, &$composites, &$titles) {
        $titles[] = $this->title;
    	if (!empty($this->image)) {
            $tag = sprintf('<img src="%s" class="user_rank" alt="%s" title="%s" />',
                           $this->image,
                           $this->title,
                           $this->title);
            if (!empty($this->stack)) {
                $tag .= "<br />\n";
            }

            if (!empty($this->repeat_image)) {
                $tag = implode('', array_fill(0, $this->repeat_image, $tag));
            }

            $composites[] = $tag;
            $images[] = $tag;
        } else {
            $composites[] = $this->title . "<br />\n";
        }
    }
}
?>