<?php
/**
 * The blog object class.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * $Id$
 */

class Blog {
    var $id         = NULL;
    var $title      = NULL;
    var $entry      = NULL;
    var $author     = NULL;
    var $date       = NULL;
    var $restricted = 0;

    function Blog($id=NULL)
    {
        if (empty($id)) {
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
        if (!isset($this->id)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('blog_entries');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }

    function setEntry($entry)
    {
        $this->entry = PHPWS_Text::parseInput($entry);
    }

    function getEntry($print=FALSE)
    {
        if ($print) {
            return PHPWS_Text::parseOutput($this->entry);
        } else {
            return $this->entry;
        }
    }

    function getAuthor()
    {
        return $this->author;
    }

    function getId()
    {
        return $this->id;
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle($print=FALSE)
    {
        return $this->title;
    }

    function getFormatedDate($type=BLOG_VIEW_DATE_FORMAT)
    {
        return strftime($type, $this->date);
    }

    function isRestricted()
    {
        return (bool)$this->restricted;
    }

    function getRestricted()
    {
        return $this->restricted;
    }

    function &makeThread()
    {
        return Comments::makeThread($this->getKey(),
                                    'index.php?module=blog&action=view_comments&id=' . $this->id);
    }

    function save()
    {
        $db = & new PHPWS_DB('blog_entries');
        if (empty($this->id)) {
            $this->date = mktime();
        }

        if (empty($this->author)) {
            $this->author = Current_User::getDisplayName();
        }

        $result = $db->saveObject($this);
        return $result;
    }

    function getViewLink($bare=FALSE){
        if ($bare) {
            if (MOD_REWRITE_ENABLED) {
                return 'blog' . $this->id . '.html';
            } else {
                return 'index.php?module=blog&amp;action=view_comments&amp;id=' . $this->id;
            }
        } else {
            return PHPWS_Text::rewriteLink(_('View'), 'blog', $this->id);
        }
    }

    function &getKey()
    {
        return new Key('blog', 'entry', $this->id);
    }


    function createCommentLink()
    {
        $vars['action'] = 'make_comment';
        $vars['blog_id'] = $this->getId();
        return PHPWS_Text::moduleLink(_('Make Comment'), 'blog', $vars);
    }


    function view($edit=TRUE, $limited=TRUE)
    {
        PHPWS_Core::initModClass('menu', 'Menu.php');
        PHPWS_Core::initModClass('comments', 'Comments.php');
        PHPWS_Core::initModClass('access', 'Access.php');
        $key = $this->getKey();

        PHPWS_Core::initModClass('categories', 'Categories.php');
        $template['TITLE'] = $this->getTitle(TRUE);
        $template['DATE']  = $this->getFormatedDate();
        $template['ENTRY'] = PHPWS_Text::parseTag($this->getEntry(TRUE));

        if ($edit && Current_User::allow('blog', 'edit_blog', $this->getId(), 'entry')){
            $vars['blog_id'] = $this->getId();
            $vars['action']  = 'admin';
            $vars['command'] = 'edit';
            $template['EDIT_LINK'] = PHPWS_Text::secureLink(_('Edit'), 'blog', $vars);
        }

        $comments = $this->makeThread();

        if ($limited) {
            $link = $comments->countComments(TRUE);
            $template['COMMENT_LINK'] = PHPWS_Text::rewriteLink($link, 'blog', $this->id);
      
            $last_poster = $comments->getLastPoster();
            if (!empty($last_poster)) {
                $template['LAST_POSTER_LABEL'] = _('Last poster');
                $template['LAST_POSTER'] = $last_poster;
            }
        } elseif ($this->id) {
            $template['COMMENTS'] = $comments->view();
            $title = $this->getTitle(TRUE);
            $url = $this->getViewLink(TRUE);

            if (class_exists('Block')) {
                Block::show($key);
            }

            if (class_exists('Access')) {
                Access::shortcut();
            }

            if (class_exists('Menu')) {
                Menu::show($key, $title, $url);
            }

            Related::show($key, $title, $url);
        }

        $result = Categories::getSimpleLinks('blog', $this->id);
        if (!empty($result)) {
            $template['CATEGORIES'] = implode(', ', $result);
        }

        $template['POSTED_BY'] = _('Posted by');
        $template['POSTED_ON'] = _('Posted on');
        $template['AUTHOR'] = $this->getAuthor();
    
        return PHPWS_Template::process($template, 'blog', 'view.tpl');
    }



    function getPagerTags()
    {
        $template['DATE'] = $this->getFormatedDate();
        $template['ENTRY'] = $this->getListEntry();
        $template['ACTION'] = $this->getListAction();
        return $template;
    }

    function getListAction(){
        $link['action'] = 'admin';
        $link['blog_id'] = $this->getId();

        if (Current_User::allow('blog', 'edit_blog', $this->getId(), 'entry')){
            $link['command'] = 'edit';
            $list[] = PHPWS_Text::secureLink(_('Edit'), 'blog', $link);
        }
    
        if (Current_User::allow('blog', 'delete_blog')){
            $link['command'] = 'delete';
            $confirm_vars['QUESTION'] = _('Are you sure you want to permanently delete this blog entry?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('blog', $link, TRUE);
            $confirm_vars['LINK'] = _('Delete');
            $list[] = Layout::getJavascript('confirm', $confirm_vars);
        }

        if (Current_User::isUnrestricted('blog')){
            $link['command'] = 'restore';
            $list[] = PHPWS_Text::secureLink(_('Restore'), 'blog', $link);
        }

        if (isset($list))
            return implode(' | ', $list);
        else
            return _('No action');
    }

    function getListEntry(){
        return substr(strip_tags(str_replace('<br />', ' ', $this->getEntry(TRUE))), 0, 30) . ' . . .';
    }

    function post_entry()
    {
        $set_permissions = FALSE;

        if ($this->id && !Current_User::authorized('blog', 'edit_blog')) {
            Current_User::disallow();
            return FALSE;
        } elseif (empty($this->id) && !Current_User::authorized('blog')) {
            Current_User::disallow();
            return FALSE;
        }

        if (!isset($_POST['blog_id']) && PHPWS_Core::isPosted()) {
            return TRUE;
        }

        if (empty($_POST['title'])) {
            return array(_('Missing title.'));
        } else {
            $this->title = strip_tags($_POST['title']);
        }

        $this->setEntry($_POST['entry']);

        if (isset($_POST['viewable'])) {
            $this->restricted = (int)$_POST['viewable'];
        } else {
            $this->restricted = 0;
        }

        if (isset($_POST['version_id'])) {
            $version = & new Version('blog_entries', $_REQUEST['version_id']);
        }
        else {
            $version = & new Version('blog_entries');
        }

        if (empty($this->author)) {
            $this->author = Current_User::getDisplayName();
        }

        if (empty($this->id)) {
            $this->date = mktime();
        }

        $version->setSource($this);

        // User is restricted, everything is unapproved
        // from them
        if (Current_User::isRestricted('blog')) {
            $version->setApproved(FALSE);
        } else {
            // User is unrestricted
            if ($version->id) {
                // A version is getting approved
                if(isset($_POST['approve_entry'])) {
                    $version->setApproved(TRUE);
                } else {
                    $version->setApproved(FALSE);
                }
            } else {
                // A regular blog from a unrestricted user
                // needs saving.
                $version->setApproved(TRUE);
                $set_permissions = TRUE;
            }
        }

        $result = $version->save();
        if (PEAR::isError($result)) {
            return FALSE;
        }

        $this->id = $version->getSourceId();

        if ($set_permissions) {
            PHPWS_User::savePermissions($this->getKey());
        }


        PHPWS_Core::initModClass('categories', 'Category_Item.php');

        $category_item = & new Category_Item('blog');
        $category_item->setItemId($this->id);
        $category_item->setVersionId($version->id);
        $category_item->setApproved($version->isApproved());
        $category_item->setTitle($this->getTitle());
        $category_item->setLink($this->getViewLink(TRUE));
        $category_item->savePost();

        return TRUE;
    }

    function approvalTags()
    {
        $tags[0]['title'] = _('Title');
        $tags[0]['data'] = $this->getTitle();

        $tags[1]['title'] = _('Entry');
        $tags[1]['data'] = $this->getEntry();

        return $tags;
    }


    function kill()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        Version::flush('blog_entries', $this->id);
        $db = & new PHPWS_DB('blog_entries');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }
}

?>