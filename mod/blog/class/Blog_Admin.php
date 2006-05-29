<?php
/**
 * Manages administrative functions including the creation and editing
 * of blog entries.
 *
 * @version $Id$
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 */


PHPWS_Core::initModClass('blog', 'Blog_Form.php');

class Blog_Admin {

    function main()
    {
        if (!Current_User::authorized('blog')) {
            Current_User::disallow(_('User attempted access to Blog administration.'));
            return;
        }

        $previous_version = $title = $content = NULL;
        $message = Blog_Admin::getForward();

        $panel = Blog_Admin::cpanel();
        $panel->enableSecure();
        PHPWS_Core::initModClass('version', 'Version.php');

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['blog_id'])) {
            $blog = & new Blog((int)$_REQUEST['blog_id']);
        } else {
            $blog = & new Blog();
        }

        switch ($command){
        case 'edit':
            $panel->setCurrentTab('list');
            if ( !Current_User::isUser($blog->author_id) && 
                 !Current_User::authorized('blog', 'edit_blog', $_REQUEST['blog_id'], 'entry')) {
                Current_User::disallow(_('User tried to edit a blog.'));
                return;
            }

            $title = _('Update Blog Entry');

            $version = & new Version('blog_entries');
            $version->setSource($blog);
            $approval_id = $version->isWaitingApproval();

            if (isset($approval_id)) {
                $version->setId($approval_id);
                $version->init();

                $unapproved_blog = & new Blog;
                $version->loadObject($unapproved_blog);

                if (Current_User::isRestricted('blog')) {
                    $message = _('This version has not been approved.');
                    $content = Blog_Form::edit($unapproved_blog, $version->id);
                } else {
                    $link = _('A version of this entry is awaiting approval.');
                    $linkVar['action']     = 'admin';
                    $linkVar['command']    = 'edit_unapproved';
                    $linkVar['version_id'] = $approval_id;
                    $message = PHPWS_Text::secureLink($link, 'blog', $linkVar);
                    $content = Blog_Form::edit($blog);
                }
        
            } else {
                $content = Blog_Form::edit($blog);
            }

            break;

        case 'approval':
            $title = _('Blog Entries Awaiting Approval');
            $approval = & new Version_Approval('blog', 'blog_entries', 'blog', 'brief_view');

            $vars['action'] = 'admin';

            $vars['command'] = 'edit_unapproved';
            $approval->setEditUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

            $vars['command'] = 'view_version';
            $approval->setViewUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

            $vars['command'] = 'approve_item';
            $approval->setApproveUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

            $vars['command'] = 'disapprove_item';
            $approval->setDisapproveUrl(PHPWS_Text::linkAddress('blog', $vars, TRUE));

            $content = $approval->getList();
            break;

        case 'disapprove_item':
            if (!Current_User::isUnrestricted('blog')) {
                Current_User::disallow('Attempted to disapprove an entry as a restricted user.');
                return;
            }
            $version = & new Version('blog_entries', $_REQUEST['version_id']);
            $result = $version->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                Blog_Admin::setForward(_('A problem occurred when trying to disapprove this entry.'), 'approval');
            } else {
                Blog_Admin::setForward(_('Blog entry disapproved.'), 'approval');
            }
            break;

        case 'approve_item':
            if (!Current_User::isUnrestricted('blog')) {
                Current_User::disallow('Attempted to approve an entry as a restricted user.');
                return;
            }

            $version = & new Version('blog_entries', $_REQUEST['version_id']);
            $version->loadObject($blog);
            $blog->approved = 1;
            $blog->save();
            $version->setSource($blog);
            $version->setApproved(TRUE);
            $result = $version->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                Blog_Admin::setForward(_('An error occurred when saving your version.'), 'approval');
            } else {
                $key = & new Key($version->source_data['key_id']);
                $version->authorizeCreator($key);
                Blog_Admin::setForward(_('Blog entry approved.'), 'approval');
            }
            break;

        case 'edit_unapproved':
            if (!Current_User::authorized('blog', 'edit_blog')) {
                Current_User::disallow(_('Tried to edit an unapproved item.'));
                return;
            }

            $version = & new Version('blog_entries', $_REQUEST['version_id']);
            $version->loadObject($blog);

            $title = _('Update Unapproved Blog Entry');
            $content = Blog_Form::edit($blog, $_REQUEST['version_id']);
            break;

        case 'new':
            $title = _('New Blog Entry');
            $content = Blog_Form::edit($blog);
            break;

        case 'delete':
            PHPWS_Cache::remove(BLOG_CACHE_KEY);
            $result = $blog->delete();
            Blog_Admin::setForward(_('Blog entry deleted.'), 'list');
            break;

        case 'list':
            $title = _('Blog Archive');
            $content = Blog_Admin::entry_list();
            break;

        case 'restore':
            $title = _('Blog Restore') . ' : ' . $blog->title;
            $content = Blog_Admin::restoreVersionList($blog);
            break;

        case 'restorePrevBlog':
            if (Current_User::isRestricted('blog') || !Current_User::authorized('blog')) {
                Current_User::disallow();
                return;
            }
            PHPWS_Cache::remove(BLOG_CACHE_KEY);
            Blog_Admin::restoreBlog($_REQUEST['version_id']);
            Blog_Admin::setForward(_('Blog entry restored.'), 'list');
            break;

        case 'removePrevBlog':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
                return;
            }
      
            $blog_id = &$_REQUEST['blog_id'];

            Blog_Admin::removePrevBlog($_REQUEST['version_id']);
            Blog_Admin::setForward(_('Blog entry removed.'), 'restore&blog_id=' . $blog_id);
            break;

        case 'postEntry':
            $title = _('Blog Archive');

            $panel->setCurrentTab('list');

            $result = $blog->post_entry();

            $link_back = PHPWS_Text::linkAddress('blog', array('action' => 'admin', 'tab'=>'list'), TRUE);

            if (is_array($result)) {
                $message = implode('<br />', $result);
                if (empty($blog->id)) {
                    $panel->setCurrentTab('new');
                }
                $content = Blog_Form::edit($blog);
            } else {
                $result = $blog->save();
                if (PEAR::isError($result)) {
                    $message = _('An error occurred when trying to save your entry. Please check your logs.');
                    PHPWS_Error::log($result);
                    Blog_Admin::setForward($message);
                } 

                if (!$blog->approved) {
                    Blog_Admin::setForward(_('Your entry is being held for approval.'), 'list');
                } else {
                    Blog_Admin::setForward(_('Entry saved successfully.'), 'list');
                }
            }
            break;

        case 'view_version':
            $title = _('View version');
            $content = Blog_Admin::viewVersion($_REQUEST['version_id']);
            break;
        }

        $template['TITLE']   = $title;
        $template['MESSAGE'] = $message;
        $template['CONTENT'] = $content;
        $final = PHPWS_Template::process($template, 'blog', 'main.tpl');

        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));

    }

    function viewVersion($version_id)
    {
        $version = & new Version('blog_entries', (int)$_REQUEST['version_id']);
        $blog = & new Blog;
        $version->loadObject($blog);

        $vars['action'] = 'admin';
        $vars['version_id'] = $version->id;
        $vars['command'] = 'edit_unapproved';
        $options[] = PHPWS_Text::secureLink(_('Edit'), 'blog', $vars);

        if (!$version->vr_approved && Current_User::isUnrestricted('blog')) {
            $vars['command'] = 'approve_item';
            $options[] = PHPWS_Text::secureLink(_('Approve'), 'blog', $vars);

            $vars['command'] = 'disapprove_item';
            $options[] = PHPWS_Text::secureLink(_('Disapprove'), 'blog', $vars);
        }

        $vars['command'] = 'approval';
        $options[] = PHPWS_Text::secureLink(_('Approval list'), 'blog', $vars);

        $template['OPTIONS'] = implode(' | ', $options);
        $template['VIEW'] = $blog->approval_view();
        return PHPWS_Template::process($template, 'blog', 'version_view.tpl');
    }

    function setForward($message, $command)
    {
        $_SESSION['Blog_Forward'] = $message;
        $link = PHPWS_Text::linkAddress('blog', array('action'=>'admin', 'command' => $command), TRUE);
        PHPWS_Core::reroute($link);
    }

    function getForward()
    {
        if (!isset($_SESSION['Blog_Forward'])) {
            return NULL;
        }

        $message = $_SESSION['Blog_Forward'];
        unset($_SESSION['Blog_Forward']);
        return $message;
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $newLink = 'index.php?module=blog&amp;action=admin';
        $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
        
        $listLink = 'index.php?module=blog&amp;action=admin';
        $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

        $version = & new Version('blog_entries');
        $unapproved = $version->countUnapproved();

        $approvalLink = 'index.php?module=blog&amp;action=admin';
        $approvalCommand = array ('title'=>sprintf(_('Approval (%s)'), $unapproved), 'link'=> $approvalLink);

        $tabs['new'] = &$newCommand;

        if (Current_User::allow('blog', 'edit_blog')) {
            $tabs['list'] = &$listCommand;
            $tabs['approval'] = &$approvalCommand;
        }

        $panel = & new PHPWS_Panel('blog');
        $panel->quickSetTabs($tabs);

        $panel->setModule('blog');
        $panel->setPanel('panel.tpl');
        return $panel;
    }


    function entry_list()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['TITLE']  = _('Title');
        $pageTags['ENTRY']  = _('Entry');
        $pageTags['DATE']   = _('Creation Date');
        $pageTags['ACTION'] = _('Action');

        $pager = & new DBPager('blog_entries', 'Blog');
        $pager->setModule('blog');
        $pager->setTemplate('list.tpl');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);
        $pager->setSearch('title');
        $pager->setDefaultOrder('create_date', 'desc');
        $content = $pager->get();
        return $content;
    }

    function _loadCategory(&$cat_item, &$blog, $version=NULL)
    {
        $cat_item->setItemId($blog->id);
        $cat_item->setTitle($blog->getTitle() . ' - ' . $blog->getFormatedDate());
        if (MOD_REWRITE_ENABLED) {
            $link = 'blog/view/' . $blog->id;
        } else {
            $link = 'index.php?module=blog&amp;action=view&amp;id=' . $blog->id;
        }
    
        $cat_item->setLink($link);

        if (isset($version)) {
            $cat_item->setVersionId($version->id);
        }
    }

    function restoreVersionList(&$blog)
    {
        PHPWS_Core::initModClass('version', 'Restore.php');
        $vars['action'] = 'admin';
        $vars['command'] = 'restorePrevBlog';
        $vars['blog_id'] = $blog->id;
        $restore_link = PHPWS_Text::linkAddress('blog', $vars, TRUE);

        $vars['command'] = 'removePrevBlog';
        $remove_link = PHPWS_Text::linkAddress('blog', $vars, TRUE);

        $restore = & new Version_Restore('blog', 'blog_entries', $blog->id, 'blog', 'brief_view');
        $restore->setRestoreUrl($restore_link);
        $restore->setRemoveUrl($remove_link);
        $result = $restore->getList();
        return $result;
    }
  
    function restoreBlog($version_id)
    {
        $version = & new Version('blog_entries', $version_id);
        $version->restore();
    }

    function removePrevBlog($version_id)
    {
        $version = & new Version('blog_entries', $version_id);
        $version->delete();
    }
}

?>