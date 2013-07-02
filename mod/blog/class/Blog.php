<?php

/**
 * The blog object class.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * $Id$
 */
if (!defined('BLOG_PAGER_DATE_FORMAT')) {
    define('BLOG_PAGER_DATE_FORMAT', '%c');
}

class Blog {

    public $id = 0;
    public $key_id = 0;
    public $title = null;
    public $summary = null;
    public $entry = null;
    public $author_id = 0;
    public $author = null;
    public $create_date = 0;
    public $updater_id = 0;
    public $updater = null;
    public $update_date = 0;
    public $allow_comments = 0;
    public $approved = 0;
    public $allow_anon = 0;
    public $publish_date = 0;
    public $expire_date = 0;
    public $image_id = 0;
    public $sticky = 0;
    public $thumbnail = 0;

    /**
     * default    : let image control linking
     * readmore   : link image to complete entry
     * parent     : if a resized image, link to full size image
     * none       : don't link even if image has one
     * (url)      : http address
     */
    public $image_link = 'default';
    public $_error = null;
    public $_comment_approval = 0;

    public function __construct($id = null)
    {
        $this->update_date = time();

        if (empty($id)) {
            $this->allow_comments = PHPWS_Settings::get('blog', 'allow_comments');
            $this->image_link = PHPWS_Settings::get('blog', 'image_link');
            return;
        }

        $this->id = (int) $id;
        $result = $this->init();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    public function init()
    {
        if (!$this->id) {
            return false;
        }

        $db = new PHPWS_DB('blog_entries');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    public function getFile($thumbnail = false)
    {
        if (!$this->image_id) {
            return null;
        }

        $file = Cabinet::getFile($this->image_id);
        if ($file->isImage(true)) {
            if ($this->image_link == 'default') {
                if ($thumbnail) {
                    return $file->getThumbnail();
                } else {
                    return $file->getTag();
                }
            }

            $file->allowImageLink(false);
            if ($this->image_link == 'none') {
                if ($thumbnail) {
                    return $file->getThumbnail();
                } else {
                    return $file->getTag();
                }
            }

            if ($this->image_link == 'parent') {
                return $file->parentLinked($thumbnail);
            } elseif ($this->image_link == 'readmore') {
                $url = $this->getViewLink(true);
            } else {
                $url = $this->image_link;
            }

            if ($thumbnail) {
                return sprintf('<a href="%s">%s</a>', $url,
                        $file->getThumbnail());
            } else {
                return sprintf('<a href="%s">%s</a>', $url, $file->getTag());
            }
        } elseif ($thumbnail && ($file->isMedia() && $file->_source->isVideo())) {
            return sprintf('<a href="%s">%s</a>', $this->getViewLink(true),
                    $file->getThumbnail());
        } else {
            return $file->getTag();
        }
    }

    public function setEntry($entry)
    {
        if (PHPWS_Text::breakPost('entry')) {
            $entry = PHPWS_Text::breaker($entry);
        }

        $this->entry = PHPWS_Text::parseInput($entry);
    }

    public function getEntry($print = false)
    {
        if (empty($this->entry)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->entry);
        } else {
            return $this->entry;
        }
    }

    public function setSummary($summary)
    {
        if (PHPWS_Text::breakPost('summary')) {
            $summary = PHPWS_Text::breaker($summary);
        }
        $this->summary = PHPWS_Text::parseInput($summary);
    }

    public function getSummary($print = false)
    {
        if (empty($this->summary)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->summary);
        } else {
            return $this->summary;
        }
    }

    public function getSummaryAndEntry()
    {
        if (!empty($this->entry)) {
            return PHPWS_Text::parseOutput($this->summary) . '<hr />' . PHPWS_Text::parseOutput($this->entry);
        } else {
            return PHPWS_Text::parseOutput($this->summary);
        }
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function getLocalDate($type = BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, PHPWS_Time::getUserTime($this->create_date));
    }

    public function getPublishDate($type = BLOG_VIEW_DATE_FORMAT)
    {
        if ($this->publish_date) {
            return strftime($type, $this->publish_date);
        } else {
            return strftime($type, time());
        }
    }

    public function getExpireDate()
    {
        if ($this->expire_date) {
            return strftime('%Y/%m/%d %H:00', $this->expire_date);
        } else {
            return null;
        }
    }

    public function relativeCreateDate($type = BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, PHPWS_Time::getServerTime($this->create_date));
    }

    public function relativePublishDate($type = BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, PHPWS_Time::getServerTime($this->publish_date));
    }

    public function relativeExpireDate($type = BLOG_VIEW_DATE_FORMAT)
    {
        if (!$this->expire_date) {
            return dgettext('blog', 'No expiration');
        } else {
            return strftime($type, PHPWS_Time::getServerTime($this->expire_date));
        }
    }

    public function save()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        $db = new PHPWS_DB('blog_entries');
        if (empty($this->id)) {
            $this->create_date = time();

            if (!$this->publish_date) {
                $this->publish_date = $this->create_date;
            }

            if (Current_User::isLogged()) {
                $this->author_id = Current_User::getId();
                $this->author = Current_User::getDisplayName();
            } elseif (empty($this->author)) {
                $this->author_id = 0;
                $this->author = dgettext('blog', 'Anonymous');
            }
        }

        if (Current_User::isLogged()) {
            $this->updater_id = Current_User::getId();
            $this->updater = Current_User::getDisplayName();
        } elseif (empty($this->updater)) {
            $this->updater_id = 0;
            $this->updater = dgettext('blog', 'Anonymous');
        }

        $this->update_date = time();

        $version = new Version('blog_entries');

        if (empty($this->entry)) {
            $this->entry = '';
        }
        if ($this->approved || !$this->id) {
            $result = $db->saveObject($this);
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }


        if ($this->approved) {
            $update = (!$this->key_id) ? true : false;

            $this->saveKey();
            if ($update) {
                $db->saveObject($this);
            }
            PHPWS_Core::initModClass('comments', 'Comments.php');
            $thread = Comments::getThread($this->key_id);
            $thread->allowAnonymous($this->allow_anon);
            $thread->setApproval($this->_comment_approval);
            $thread->save();

            $search = new Search($this->key_id);
            $search->resetKeywords();
            $search->addKeywords($this->title);
            $search->addKeywords($this->summary);
            $search->addKeywords($this->entry);
            $result = $search->save();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        }

        $version->setSource($this);
        $version->setApproved($this->approved);
        return $version->save();
    }

    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->getError())) {
                $key = new Key;
            }
        }

        $key->setModule('blog');
        $key->setItemName('entry');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_blog');
        $key->setUrl($this->getViewLink(true));
        $key->setTitle($this->title);
        $key->setShowAfter($this->publish_date);
        $key->setHideAfter($this->expire_date);
        if (!empty($this->summary)) {
            $key->setSummary($this->summary);
        } else {
            $key->setSummary($this->entry);
        }
        $key->save();
        $this->key_id = $key->id;
        return $key;
    }

    public function getViewLink($bare = false)
    {
        $link = new PHPWS_Link(dgettext('blog', 'View'), 'blog',
                array('id' => $this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }

    public function brief_view()
    {
        $template['TITLE'] = $this->title;
        $template['LOCAL_DATE'] = $this->getPublishDate();
        $template['PUBLISHED_DATE'] = PHPWS_Time::getDTTime($this->publish_date);
        $template['SUMMARY'] = PHPWS_Text::parseTag($this->getSummary(true));
        $template['ENTRY'] = PHPWS_Text::parseTag($this->getEntry(true));
        $template['IMAGE'] = $this->getFile($this->thumbnail);

        $template['POSTED_BY'] = dgettext('blog', 'Posted by');
        $template['POSTED_ON'] = dgettext('blog', 'Posted at');
        if ($this->author_id) {
            $template['AUTHOR'] = $this->author;
        } else {
            $template['AUTHOR'] = dgettext('blog', 'Anonymous');
        }

        return PHPWS_Template::process($template, 'blog', 'view_full.tpl');
    }

    /**
     * Displays the blog entry
     *
     * @param boolean edit       If true, show edit link
     * @param boolean summarized If true, this is a summarized entry
     */
    public function view($edit = true, $summarized = true)
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        PHPWS_Core::initModClass('comments', 'Comments.php');

        $key = new Key($this->key_id);

        if (!$key->allowView() || !Blog_User::allowView()) {
            Current_User::requireLogin();
            return dgettext('blog',
                    'You do not have permission to view this entry.');
        }

        $template['TITLE'] = sprintf('<a href="%s" rel="bookmark">%s</a>',
                $this->getViewLink(true), $this->title);

        if ($this->publish_date > time()) {
            $template['UNPUBLISHED'] = dgettext('blog', 'Unpublished');
        } elseif ($this->expire_date && $this->expire_date < time()) {
            $template['UNPUBLISHED'] = dgettext('blog', 'Expired');
        }

        $template['LOCAL_DATE'] = $this->getPublishDate();

        $summary = $this->getSummary(true);
        $entry = $this->getEntry(true);

        if ($summarized) {
            if (empty($summary)) {
                $template['SUMMARY'] = PHPWS_Text::parseTag($entry);
            } else {
                if (!empty($entry)) {
                    $template['READ_MORE'] = PHPWS_Text::rewriteLink(dgettext('blog',
                                            'Read more'), 'blog',
                                    array('id' => $this->id));
                }
                $template['SUMMARY'] = PHPWS_Text::parseTag($summary);
            }
        } else {
            $template['SUMMARY'] = PHPWS_Text::parseTag($summary);
            $template['ENTRY'] = PHPWS_Text::parseTag($entry);
        }

        $template['IMAGE'] = $this->getFile($this->thumbnail && $summarized);

        if ($edit &&
                ( Current_User::allow('blog', 'edit_blog', $this->id, 'entry') ||
                ( Current_User::allow('blog', 'edit_blog') && $this->author_id == Current_User::getId() )
                )) {

            $vars['blog_id'] = $this->id;
            $vars['action'] = 'admin';
            $vars['command'] = 'edit';

            $template['EDIT_LINK'] = PHPWS_Text::secureLink(dgettext('blog',
                                    'Edit'), 'blog', $vars);
            if (!$summarized) {
                MiniAdmin::add('blog',
                        array(PHPWS_Text::secureLink(dgettext('blog',
                                    'Edit blog'), 'blog', $vars)));
            }
        }

        if ($this->allow_comments && $this->approved) {
            $comments = Comments::getThread($key);

            if ($summarized && !empty($comments)) {
                $link = $comments->countComments(true);
                $comment_link = new PHPWS_Link($link, 'blog',
                        array('id' => $this->id));
                $comment_link->setRewrite();
                $comment_link->setAnchor('comments');
                $template['COMMENT_LINK'] = $comment_link->get();

                if (isset($template['READ_MORE'])) {
                    $template['SEPARATOR'] = '|';
                }

                $last_poster = $comments->getLastPoster();

                if (!empty($last_poster)) {
                    $template['LAST_POSTER_LABEL'] = dgettext('blog',
                            'Last poster');
                    $template['LAST_POSTER'] = $last_poster;
                }
            } elseif ($this->id) {
                if ($comments) {
                    $template['COMMENTS'] = $comments->view();
                }
                $key->flag();
            }
        } else {
            if (!$summarized) {
                $key->flag();
            }
        }

        if (PHPWS_Settings::get('blog', 'show_category_icons')) {
            $result = Categories::getIcons($key);
            if (!empty($result)) {
                if (PHPWS_Settings::get('blog', 'single_cat_icon')) {
                    $template['cat-icons'][] = array('CAT_ICON' => array_shift($result));
                } else {
                    foreach ($result as $icon) {
                        $template['cat-icons'][] = array('CAT_ICON' => $icon);
                    }
                }
            }
        }

        if (PHPWS_Settings::get('blog', 'show_category_links')) {
            $result = Categories::getSimpleLinks($key);
            if (!empty($result)) {
                $template['CATEGORIES'] = implode(', ', $result);
            }
        }

        $template['POSTED_BY'] = dgettext('blog', 'Posted by');
        $template['POSTED_ON'] = dgettext('blog', 'Posted on');
        $template['AUTHOR'] = $this->author;

        if ($summarized) {
            $view_tpl = 'view_list.tpl';
        } else {
            $view_tpl = 'view_full.tpl';
        }
        return PHPWS_Template::process($template, 'blog', $view_tpl);
    }

    public function getPagerTags()
    {
        $template['TITLE'] = sprintf('<a href="%s">%s</a>',
                $this->getViewLink(true), $this->title);
        $template['CREATE_DATE'] = $this->relativeCreateDate(BLOG_PAGER_DATE_FORMAT);
        $template['PUBLISH_DATE'] = $this->relativePublishDate(BLOG_PAGER_DATE_FORMAT);
        $template['EXPIRE_DATE'] = $this->relativeExpireDate(BLOG_PAGER_DATE_FORMAT);
        $template['SUMMARY'] = $this->getListSummary();
        $template['ACTION'] = $this->getListAction();
        return $template;
    }

    public function getListAction()
    {
        $link['action'] = 'admin';
        $link['blog_id'] = $this->id;

        if (( Current_User::allow('blog', 'edit_blog') && Current_User::getId() == $this->author_id ) || Current_User::allow('blog',
                        'edit_blog', $this->id, 'entry')) {

            $link['command'] = 'edit';
            $icon = Icon::show('edit');
            $list[] = PHPWS_Text::secureLink($icon, 'blog', $link);
        }

        if (Current_User::allow('blog', 'delete_blog')) {
            $link['command'] = 'delete';
            $confirm_vars['QUESTION'] = dgettext('blog',
                    'Are you sure you want to permanently delete this blog entry?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('blog', $link,
                            true);

            $confirm_vars['LINK'] = Icon::show('delete');
            $list[] = Layout::getJavascript('confirm', $confirm_vars);
        }

        if (Current_User::isUnrestricted('blog')) {
            /*
              $link['command'] = 'restore';
              $icon = Icon::show('redo', dgettext('blog', 'Restore'));
              $list[] = PHPWS_Text::secureLink($icon, 'blog', $link);
             */


            if ($this->sticky) {
                $link['command'] = 'unsticky';
                $icon = Icon::show('unsticky');
                $list[] = PHPWS_Text::secureLink($icon, 'blog', $link);
            } else {
                $link['command'] = 'sticky';
                $icon = Icon::show('sticky');
                $list[] = PHPWS_Text::secureLink($icon, 'blog', $link);
            }
        }

        if (isset($list)) {
            $response = implode(' ', $list);
        } else {
            $response = dgettext('blog', 'No action');
        }
        return $response;
    }

    public function getListSummary()
    {
        return substr(ltrim(strip_tags(str_replace('<br />', ' ',
                                        $this->getSummary(true)))), 0, 60);
    }

    public function post_entry()
    {
        if ($this->id && !Current_User::authorized('blog', 'edit_blog')) {
            Current_User::disallow();
        } elseif (empty($this->id) && !Current_User::authorized('blog')) {
            Current_User::disallow();
        }

        if (empty($_POST['title'])) {
            $this->_error[] = dgettext('blog', 'Missing title.');
        } else {
            $this->title = strip_tags($_POST['title']);
        }
        $summary_and_entry = $_POST['summary'];

        if (!$this->id && strlen($summary_and_entry) > 1000) {
            $paragraphs = explode('<p>', $summary_and_entry);
            if (count($paragraphs) > 3) {
                $paragraphs[2] .= '<hr />';
                $summary_and_entry = implode('<p>', $paragraphs);
            }
        }


        if (empty($summary_and_entry)) {
            $this->_error[] = dgettext('blog',
                    'Your submission must have some content.');
        } else {
            // We don't catch the regular expression result because we only care about matches
            preg_replace_callback('@(.*?)<hr[^>]*/>(.*)@s',
                    function($matches) {
                        $GLOBALS['split_summary'] = $matches;
                    }, $summary_and_entry);
            if (isset($GLOBALS['split_summary'])) {
                $this->setSummary($GLOBALS['split_summary'][1]);
                $this->setEntry($GLOBALS['split_summary'][2]);
            } else {
                $this->setSummary($summary_and_entry);
                $this->entry = null;
            }
        }

        if (isset($_POST['image_id'])) {
            $this->image_id = (int) $_POST['image_id'];
        }

        if (isset($_POST['allow_comments'])) {
            $this->allow_comments = 1;
        } else {
            $this->allow_comments = 0;
        }

        $this->_comment_approval = (int) $_POST['comment_approval'];

        if (isset($_POST['allow_anon'])) {
            $this->allow_anon = 1;
        } else {
            $this->allow_anon = 0;
        }

        if (isset($_POST['thumbnail'])) {
            $this->thumbnail = 1;
        } else {
            $this->thumbnail = 0;
        }

        if (empty($this->author)) {
            $this->author = Current_User::getDisplayName();
        }

        if (empty($_POST['publish_date'])) {
            $this->publish_date = time();
        } else {
            $this->publish_date = strtotime($_POST['publish_date']);
        }

        if (empty($_POST['expire_date'])) {
            $this->expire_date = 0;
        } else {
            $this->expire_date = strtotime($_POST['expire_date']);
        }

        if (isset($_POST['version_id']) || Current_User::isRestricted('blog')) {
            $this->approved = 0;
        } else {
            $this->approved = 1;
        }
        $link_choices[] = 'none';
        $link_choices[] = 'default';
        $link_choices[] = 'readmore';
        $link_choices[] = 'parent';
        $link_choices[] = 'url';

        $image_link = &$_POST['image_link'];
        if (!in_array($image_link, $link_choices)) {
            $this->image_link = 'default';
        } elseif ($_POST['image_link'] != 'url') {
            $this->image_link = $image_link;
        } else {
            $url = $_POST['image_url'];
            if (!empty($url) || $url == 'http://') {
                $this->image_link = PHPWS_Text::checkLink($url);
            } else {
                $this->image_link = 'default';
            }
        }
        return true;
    }

    public function delete()
    {
        $all_is_well = true;

        PHPWS_Core::initModClass('version', 'Version.php');
        Version::flush('blog_entries', $this->id);
        $db = new PHPWS_DB('blog_entries');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = false;
        }

        $key = new Key($this->key_id);
        $key->delete();
        return $all_is_well;
    }

    private function reportClean($text)
    {
        $text = str_replace("\r", '', strip_tags($text));
        $text = str_replace('&#160;', ' ', $text);
        return $text;
    }

    public function report_rows()
    {
        $row['id'] = $this->id;
        $row['title'] = $this->title;

        $row['summary'] = $this->reportClean($this->getSummary(true));
        $row['entry'] = $this->reportClean($this->getEntry(true));
        $row['author'] = $this->author;
        $row['creation date'] = strftime('%c', $this->create_date);
        $row['publish date'] = strftime('%c', $this->publish_date);
        if ($this->expire_date) {
            $row['expiration date'] = strftime('%c', $this->expire_date);
        } else {
            $row['expiration date'] = dgettext('blog', 'None');
        }
        return $row;
    }

}

?>
