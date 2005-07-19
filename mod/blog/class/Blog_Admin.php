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

    function main(){
        if (!Current_User::authorized('blog')){
            Current_User::disallow(_('User attempted access to Blog administration.'));
            return;
        }

        $previous_version = $title = $message = $content = NULL;
        $panel = & Blog_Admin::cpanel();
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
            $panel->setCurrentTab('list');;
            if (!Current_User::authorized('blog', 'edit_blog', $_REQUEST['blog_id'], 'entry')){
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
                    $content = Blog_Admin::edit($unapproved_blog, $version->getId());
                } else {
                    $link = _('A version of this entry is awaiting approval.');
                    $linkVar['action']     = 'admin';
                    $linkVar['command']    = 'editUnapproved';
                    $linkVar['version_id'] = $approval_id;
                    $message = PHPWS_Text::secureLink($link, 'blog', $linkVar);
                    $content = Blog_Admin::edit($blog);
                }
        
            } else {
                $content = Blog_Form::edit($blog);
            }

            break;

        case 'approval':
            $title = _('Blog Entries Awaiting Approval');
            $approval = & new Version_Approval('blog', 'blog_entries', 'blog', 'approvalTags');
            $approval->setEditUrl('index.php?module=blog&amp;action=admin&amp;command=edit_unapproved&amp;authkey=' . Current_User::getAuthKey());
            $approval->setViewUrl('index.php?module=blog&amp;action=admin&amp;command=view_version&amp;authkey=' . Current_User::getAuthKey());
            $approval->setApproveUrl('index.php?module=blog&amp;action=admin&amp;command=approve_item&amp;authkey=' . Current_User::getAuthKey());
            $approval->setDisapproveUrl('index.php?module=blog&amp;action=admin&amp;command=disapprove_item&amp;authkey=' . Current_User::getAuthKey());

            $approval->setColumns('title', 'entry');
            $content = $approval->getList();
            break;

        case 'disapprove_item':
            if (Current_User::isRestricted('blog')) {
                Current_User::disallow('Attempted to disapprove an entry as a restricted user.');
                return;
            }
            $version = & new Version('blog_entries', $_REQUEST['version_id']);
            $result = $version->kill();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Error');
                $content = _('A problem occurred when trying to disapprove this entry.');
            } else {
                $title = _('Blog entry disapproved.');
                $content = _('Returning you to the approval list.');
            }
            Layout::metaRoute('index.php?module=blog&amp;action=admin&amp;tab=approval&amp;authkey='
                              . Current_User::getAuthKey());
            break;

        case 'approve_item':
            if (Current_User::isRestricted('blog')) {
                Current_User::disallow('Attempted to approve an entry as a restricted user.');
                return;
            }

            $version = & new Version('blog_entries', $_REQUEST['version_id']);
            $version->setApproved(TRUE);
            $result = $version->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Sorry');
                $content = _('An error occurred when saving your version.');
            } else {
                PHPWS_Core::initModClass('categories', 'Category_Item.php');
                $category_item = & new Category_Item('blog');
                $category_item->setVersionId($version->getId());
                $category_item->setItemId($version->getSourceId());
                $category_item->saveVersion();

                $version->authorizeCreator('blog', 'entry');
                $title = _('Blog entry approved.');
                $content = _('Returning you to the approval list.');
                Layout::metaRoute('index.php?module=blog&amp;action=admin&amp;tab=approval&amp;authkey='
                                  . Current_User::getAuthKey());
            }
            break;

        case 'edit_unapproved':
            if (!Current_User::authorized('blog', 'edit_blog')){
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
            $title = _('Blog Archive');
            $message = _('Blog entry deleted.');
            $result = $blog->kill();
            $content = Blog_Admin::entry_list();
            break;

        case 'list':
            $title = _('Blog Archive');
            $content = Blog_Admin::entry_list();
            break;

        case 'restore':
            $title = _('Blog Restore') . ' : ' . $blog->getTitle();
            $content = Blog_Admin::restoreVersionList($blog);
            break;

        case 'restorePrevBlog':
            if (Current_User::isRestricted('blog') || !Current_User::authorized('blog')) {
                Current_User::disallow();
                return;
            }
        
            Blog_Admin::restoreBlog($_REQUEST['version_id']);
            $title = _('Blog Archive');
            $message = _('Blog entry restored.');
            $content = Blog_Admin::entry_list();
            break;

        case 'removePrevBlog':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
                return;
            }
      
            Blog_Admin::removePrevBlog($_REQUEST['version_id']);
            $title = _('Blog Restore');
            $message = _('Blog entry removed.');
            $content = Blog_Admin::restoreVersionList($blog);
            break;

        case 'postEntry':
            $title = _('Blog Archive');

            $panel->setCurrentTab('list');

            $result = $blog->post_entry();

            $link_back = PHPWS_Text::linkAddress('blog', array('action' => 'admin', 'tab'=>'list'), TRUE);

            if ($result == FALSE) {
                $content = _('An error occurred when trying to save your entry. Please check your logs.');
                Layout::metaRoute($link_back);
            } elseif (is_array($result)) {
                $message = implode('<br />', $result);
                if (empty($blog->id)) {
                    $panel->setCurrentTab('new');
                }
                $content = Blog_Admin::edit($blog);
            } else {
                if (Current_User::isRestricted('blog')) {
                    $content = _('Your entry is being held for approval.');
                } else {
                    $content = _('Entry saved successfully.');
                }
                Layout::metaRoute($link_back);
            }

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

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $newLink = 'index.php?module=blog&amp;action=admin';
        $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
        
        $listLink = 'index.php?module=blog&amp;action=admin';
        $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

        $approvalLink = 'index.php?module=blog&amp;action=admin';
        $approvalCommand = array ('title'=>_('Approval'), 'link'=> $approvalLink);

        $tabs['new'] = $newCommand;

        if (Current_User::allow('blog', 'edit_blog')) {
            $tabs['list'] = $listCommand;
            $tabs['approval'] = $approvalCommand;
        }

        $panel = & new PHPWS_Panel('blog');
        $panel->quickSetTabs($tabs);

        $panel->setModule('blog');
        $panel->setPanel('panel.tpl');
        return $panel;
    }


    function entry_list(){
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['TITLE']  = _('Title');
        $pageTags['ENTRY']  = _('Entry');
        $pageTags['DATE']   = _('Creation Date');
        $pageTags['ACTION'] = _('Action');

        $pager = & new DBPager('blog_entries', 'Blog');
        $pager->setModule('blog');
        $pager->setTemplate('list.tpl');
        $pager->setLink('index.php?module=blog&amp;action=admin&amp;authkey=' . Current_User::getAuthKey());
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);
        $pager->setSearch('title');
        $content = $pager->get();
        return $content;
    }

    function _loadCategory(&$cat_item, &$blog, $version=NULL)
    {
        $cat_item->setItemId($blog->getId());
        $cat_item->setTitle($blog->getTitle() . ' - ' . $blog->getFormatedDate());
        if (MOD_REWRITE_ENABLED) {
            $link = 'blog/view/' . $blog->getId();
        } else {
            $link = 'index.php?module=blog&amp;action=view&amp;id=' . $blog->getId();
        }
    
        $cat_item->setLink($link);

        if (isset($version)) {
            $cat_item->setVersionId($version->getId());
        }
    }

    function restoreVersionList(&$blog)
    {
        $version = & new Version('blog_entries');
        $version->setSource($blog);
        $version_list = $version->getBackupList();

        $count = 0;

        $vars['action'] = 'admin';
        if (empty($version_list)) {
            $tpl['INSTRUCTION'] = _('No backups of this blog entry exist.');
            return PHPWS_Template::processTemplate($tpl, 'blog', 'version.tpl');
        }
        foreach ($version_list as $backup_id => $backup){
            $count++;
            if ($count%2)
                $template['TOGGLE'] = 'class="toggle1"';
            else
                $template['TOGGLE'] = 'class="toggle2"';

            $blog = & new Blog;
            $backup->loadObject($blog);

            $vars['version_id'] = $backup->getId();
            $template['CREATED'] = $backup->getCreationDate(TRUE);
            $template['BLOG'] = $blog->view(FALSE);

            $vars['command'] = 'restorePrevBlog';
            $template['RESTORE_LINK'] = PHPWS_Text::secureLink(_('Restore'), 'blog', $vars);

            if (Current_User::isDeity()) {
                $vars['command'] = 'removePrevBlog';
                $vars['blog_id'] = $blog->getId();
                $confirm['QUESTION'] = _('Are you sure you want to purge this backup copy?');
                $confirm['ADDRESS'] = PHPWS_Text::linkAddress('blog', $vars, TRUE);
                $confirm['LINK'] = _('Remove');
                $template['REMOVE_LINK'] = Layout::getJavascript('confirm', $confirm);
            }
            $tpl['repeat_row'][] = $template;
        }
        $tpl['INSTRUCTION'] = _('Choose the blog entry you want to restore.');
        return PHPWS_Template::processTemplate($tpl, 'blog', 'version.tpl');
    }
  
    function restoreBlog($version_id) {
        $version = & new Version('blog_entries', $version_id);
        $version->restore();
    }

    function removePrevBlog($version_id){
        $version = & new Version('blog_entries', $version_id);
        $version->kill();
    }
}

?>