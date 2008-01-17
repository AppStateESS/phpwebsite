<?php
  /**
   * The blog object class.
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * $Id$
   */

class Blog {
    var $id             = 0;
    var $key_id         = 0;
    var $title          = null;
    var $summary        = null;
    var $entry          = null;
    var $author_id      = 0;
    var $author         = null;
    var $create_date    = 0;
    var $updater_id     = 0;
    var $updater        = null;
    var $update_date    = 0;
    var $allow_comments = 0;
    var $approved       = 0;
    var $allow_anon     = 0;
    var $publish_date   = 0;
    var $expire_date    = 0;
    var $image_id        = 0;
    var $sticky         = 0;
    var $_error         = null;

    function Blog($id=null)
    {
        $this->update_date = mktime();

        if (empty($id)) {
            $this->allow_comments = PHPWS_Settings::get('blog', 'allow_comments');
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    function init()
    {
        if (!$this->id) {
            return false;
        }

        $db = new PHPWS_DB('blog_entries');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    function getFile()
    {
        if (!$this->image_id) {
            return null;
        }
        return Cabinet::getFile($this->image_id);
    }

    function setEntry($entry)
    {
        $this->entry = PHPWS_Text::parseInput($entry);
    }


    function getEntry($print=false)
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

    function setSummary($summary)
    {
        $this->summary = PHPWS_Text::parseInput($summary);
    }


    function getSummary($print=false)
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


    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getLocalDate($type=BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, PHPWS_Time::getUserTime($this->create_date));
    }

    function getPublishDate()
    {
        if ($this->publish_date) {
            return strftime('%Y%m%d %H:00', $this->publish_date);
        } else {
            return strftime('%Y%m%d %H:00', mktime());
        }
    }

    function getExpireDate()
    {
        if ($this->expire_date) {
            return strftime('%Y%m%d %H:00', $this->expire_date);
        } else {
            return null;
        }
    }

    function relativeCreateDate($type=BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, PHPWS_Time::getServerTime($this->create_date));
    }


    function relativePublishDate($type=BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, PHPWS_Time::getServerTime($this->publish_date));
    }


    function relativeExpireDate($type=BLOG_VIEW_DATE_FORMAT)
    {
        if (!$this->expire_date) {
            return dgettext('blog', 'No expiration');
        } else {
            return strftime($type, PHPWS_Time::getServerTime($this->expire_date));
        }
    }


    function save()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        $db = new PHPWS_DB('blog_entries');
        if (empty($this->id)) {
            $this->create_date = mktime();
            
            if (!$this->publish_date) {
                $this->publish_date = $this->create_date;
            }

            if (Current_User::isLogged()) {
                $this->author_id = Current_User::getId();
                $this->author    = Current_User::getDisplayName();
            } elseif (empty($this->author)) {
                $this->author_id = 0;
                $this->author    = dgettext('blog', 'Anonymous');
            }
        }

        if (Current_User::isLogged()) {
            $this->updater_id = Current_User::getId();
            $this->updater    = Current_User::getDisplayName();
        } elseif (empty($this->updater)) {
            $this->updater_id = 0;
            $this->updater    = dgettext('blog', 'Anonymous');
        }

        $this->update_date = mktime();

        $version = new Version('blog_entries');

        if (empty($this->entry)) {
            $this->entry = '';
        }

        if ($this->approved || !$this->id) {
            $result = $db->saveObject($this);
        }

        if (PEAR::isError($result)) {
            return $result;
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
            $thread->save();

            $search = new Search($this->key_id);
            $search->resetKeywords();
            $search->addKeywords($this->title);
            $search->addKeywords($this->summary);
            $search->addKeywords($this->entry);
            $result = $search->save();
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        $version->setSource($this);
        $version->setApproved($this->approved);
        return $version->save();
    }

    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('blog');
        $key->setItemName('entry');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_blog');
        $key->setUrl($this->getViewLink(true));
        $key->setTitle($this->title);
        if (!empty($this->summary)) {
            $key->setSummary($this->summary);
        } else {
            $key->setSummary($this->entry);
        }
        $key->save();
        $this->key_id = $key->id;
        return $key;
    }

    function getViewLink($bare=false){
        if ($bare) {
            if (MOD_REWRITE_ENABLED) {
                return 'blog/' . $this->id;
            } else {
                return 'index.php?module=blog&amp;action=view_comments&amp;id=' . $this->id;
            }
        } else {
            return PHPWS_Text::rewriteLink(dgettext('blog', 'View'), 'blog', $this->id);
        }
    }

    function brief_view()
    {
        $template['TITLE'] = $this->title;
        $template['LOCAL_DATE']  = $this->getLocalDate();
        $template['PUBLISHED_DATE'] = PHPWS_Time::getDTTime($this->create_date);
        $template['SUMMARY'] = PHPWS_Text::parseTag($this->getSummary(true));
        $template['ENTRY'] = PHPWS_Text::parseTag($this->getEntry(true));
        $template['IMAGE'] = $this->getImage();

        $template['POSTED_BY'] = dgettext('blog', 'Posted by');
        $template['POSTED_ON'] = dgettext('blog', 'Posted on');
        if ($this->author_id) {
            $template['AUTHOR'] = $this->author;
        } else {
            $template['AUTHOR'] = dgettext('blog', 'Anonymous');
        }

        return PHPWS_Template::process($template, 'blog', 'view.tpl');
    }


    /**
     * Displays the blog entry
     *
     * @param boolean edit       If true, show edit link
     * @param boolean summarized If true, this is a summarized entry
     */
    function view($edit=true, $summarized=true)
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        PHPWS_Core::initModClass('comments', 'Comments.php');

        $key = new Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();            
        }

        $template['TITLE'] = sprintf('<a href="%s" rel="bookmark">%s</a>',
                                     $this->getViewLink(true), $this->title);

        $template['LOCAL_DATE']  = $this->getLocalDate();

        $summary = $this->getSummary(true);
        $entry   = $this->getEntry(true);

        if ($summarized) {
            if (empty($summary)) {
                $template['SUMMARY'] = PHPWS_Text::parseTag($entry);
            } else {
                $template['READ_MORE'] = PHPWS_Text::rewriteLink(dgettext('blog', 'Read more'), 'blog', $this->id);
                $template['SUMMARY'] =  PHPWS_Text::parseTag($summary);
            }
        } else {
            $template['SUMMARY'] =  PHPWS_Text::parseTag($summary);
            $template['ENTRY'] = PHPWS_Text::parseTag($entry);
        }

        $template['IMAGE'] = $this->getFile();

        if ( $edit && 
             ( Current_User::allow('blog', 'edit_blog', $this->id, 'entry') ||
               ( Current_User::allow('blog', 'edit_blog') && $this->author_id == Current_User::getId() )
               ) ) {

            $vars['blog_id'] = $this->id;
            $vars['action']  = 'admin';
            $vars['command'] = 'edit';

            $template['EDIT_LINK'] = PHPWS_Text::secureLink(dgettext('blog', 'Edit'), 'blog', $vars);
            if (!$summarized) {
                MiniAdmin::add('blog', array(PHPWS_Text::secureLink(dgettext('blog', 'Edit blog'), 'blog', $vars)));
            }        
        }
        
        if ($this->allow_comments) {
            $comments = Comments::getThread($key);
           
            if ($summarized && !empty($comments)) {
                $link = $comments->countComments(true);
                if (MOD_REWRITE_ENABLED) {
                    $template['COMMENT_LINK'] = sprintf('<a href="blog/%d#comments">%s</a>', $this->id, $link);
                } else {
                    $template['COMMENT_LINK'] = sprintf('<a href="index.php?module=blog&amp;id=%d#comments">%s</a>', $this->id, $link);
                }

                if (isset($template['READ_MORE'])) {
                    $template['SEPARATOR'] = '|';
                }

                $last_poster = $comments->getLastPoster();
                
                if (!empty($last_poster)) {
                    $template['LAST_POSTER_LABEL'] = dgettext('blog', 'Last poster');
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
                    $template['cat-icons'][] = array('CAT_ICON'=>array_shift($result));
                } else {
                    foreach ($result as $icon) {
                        $template['cat-icons'][] = array('CAT_ICON'=>$icon);
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

        return PHPWS_Template::process($template, 'blog', 'view.tpl');
    }



    function getPagerTags()
    {
        $template['TITLE'] = sprintf('<a href="%s">%s</a>', $this->getViewLink(true), $this->title);
        $template['CREATE_DATE'] = $this->relativeCreateDate();
        $template['PUBLISH_DATE'] = $this->relativePublishDate();
        $template['EXPIRE_DATE'] = $this->relativeExpireDate();
        $template['SUMMARY'] = $this->getListSummary();
        $template['ACTION'] = $this->getListAction();
        return $template;
    }

    function getListAction(){
        $link['action'] = 'admin';
        $link['blog_id'] = $this->id;

        if ( ( Current_User::allow('blog', 'edit_blog') && Current_User::getId() == $this->author_id )
            || Current_User::allow('blog', 'edit_blog', $this->id, 'entry') ){

            $link['command'] = 'edit';
            $list[] = PHPWS_Text::secureLink(dgettext('blog', 'Edit'), 'blog', $link);
        }
    
        if (Current_User::allow('blog', 'delete_blog')){
            $link['command'] = 'delete';
            $confirm_vars['QUESTION'] = dgettext('blog', 'Are you sure you want to permanently delete this blog entry?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('blog', $link, true);
            $confirm_vars['LINK'] = dgettext('blog', 'Delete');
            $list[] = Layout::getJavascript('confirm', $confirm_vars);
        }

        if (Current_User::isUnrestricted('blog')){
            $link['command'] = 'restore';
            $list[] = PHPWS_Text::secureLink(dgettext('blog', 'Restore'), 'blog', $link);
            if ($this->sticky) {
                $link['command'] = 'unsticky';
                $list[] = PHPWS_Text::secureLink(dgettext('blog', 'Unsticky'), 'blog', $link);
            } else {
                $link['command'] = 'sticky';
                $list[] = PHPWS_Text::secureLink(dgettext('blog', 'Sticky'), 'blog', $link);
            }
        }

        if (isset($list)) {
            $response = implode(' | ', $list);
        }
        else {
            $response = dgettext('blog', 'No action');
        }
        return $response;
    }

    function getListSummary(){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getSummary(true)))), 0, 60);
    }

    function post_entry()
    {
        $set_permissions = false;
        
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

        $summary = $_POST['summary'];
        if (empty($summary)) {
            $this->_error[] = dgettext('blog', 'Your submission must have a summary.');
        } else {
            $this->setSummary($summary);
        }
        $this->setEntry($_POST['entry']);

        if (isset($_POST['image_id'])) {
            $this->image_id = (int)$_POST['image_id'];
        }

        if (isset($_POST['allow_comments'])) {
            $this->allow_comments = 1;
        } else {
            $this->allow_comments = 0;
        }

        if (isset($_POST['allow_anon'])) {
            $this->allow_anon = 1;
        } else {
            $this->allow_anon = 0;
        }

        if (empty($this->author)) {
            $this->author = Current_User::getDisplayName();
        }

        if (empty($_POST['publish_date'])) {
            $this->publish_date = mktime();
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

        return true;
    }

    function delete()
    {
        $all_is_well = true;
        Key::drop($this->key_id);
        PHPWS_Core::initModClass('version', 'Version.php');
        Version::flush('blog_entries', $this->id);
        $db = new PHPWS_DB('blog_entries');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $all_is_well = false;
        }

        $key = new Key($this->key_id);
        $key->delete();
        return $all_is_well;
    }
}

?>
