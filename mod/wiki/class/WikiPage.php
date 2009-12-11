<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Wiki
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

class WikiPage
{
    var $id          = 0;
    var $key_id      = 0;
    var $owner_id    = 0;
    var $editor_id   = 0;
    var $title       = NULL;
    var $created     = 0;
    var $updated     = 0;
    var $pagetext    = NULL;
    var $hits        = 0;
    var $comment     = NULL;
    var $allow_edit  = 1;


    function WikiPage($id=NULL)
    {
        if(isset($id))
        {
            $db = new PHPWS_DB('wiki_pages');

            /* Check if page id or page title */
            if (is_numeric($id))
            {
                $this->setId($id);
                $db->loadObject($this);
            }
            else
            {
                $db->addWhere('title', $id);
                if (!$db->loadObject($this))
                {
                    $this->setTitle($id);
                }
            }
        }
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function getKey()
    {
        $key = new Key($this->key_id);
        return $key;
    }

    function setOwnerId($owner_id)
    {
        if (!$this->getId())
        {
            $this->owner_id = (int)$owner_id;
        }
    }

    function getOwnerId()
    {
        return $this->owner_id;
    }

    function setEditorId($editor_id)
    {
        $this->editor_id = (int)$editor_id;
    }

    function getEditorId()
    {
        return $this->editor_id;
    }

    function getEditor()
    {
        $db = new PHPWS_DB('users');
        $db->addWhere('id', $this->getEditorId());
        $db->addColumn('display_name');
        $result = $db->select('col');
        if (PHPWS_Error::logIfError($result))
        {
            return dgettext('wiki', 'N/A');
        }

        return $result[0];
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle($format=TRUE)
    {
        if ($format)
        {
            return WikiManager::formatTitle($this->title);
        }

        return $this->title;
    }

    function setCreated($created)
    {
        if (!$this->getId())
        {
            $this->created = (int)$created;
        }
    }

    function getCreated($format=WIKI_DATE_FORMAT)
    {
        return strftime($format, PHPWS_Time::getUserTime($this->created));
    }

    function setUpdated($updated)
    {
        $this->updated = (int)$updated;
    }

    function getUpdated($format=WIKI_DATE_FORMAT)
    {
        return strftime($format, PHPWS_Time::getUserTime($this->updated));
    }

    function setPagetext($pagetext)
    {
        $this->pagetext = PHPWS_Text::parseInput($pagetext, FALSE);
    }

    function getPagetext($transform=TRUE)
    {
        if ($transform)
        {
            return WikiManager::transform($this->pagetext);
        }
        return $this->pagetext;
    }

    function incHits($save=TRUE)
    {
        if($this->getId())
        {
            $this->hits++;
            if ($save)
            {
                $db = new PHPWS_DB('wiki_pages');
                PHPWS_Error::logIfError($db->saveObject($this));
            }
        }
    }

    function getHits()
    {
        return number_format($this->hits);
    }

    function setComment($comment)
    {
        $this->comment = PHPWS_Text::parseInput($comment);
    }

    function getComment()
    {
        return PHPWS_Text::parseOutput($this->comment);
    }

    function getVersion()
    {
        $db = new PHPWS_DB('wiki_pages_version');
        $db->addWhere('source_id', $this->getId());
        return $db->count();
    }

    function toggleLock()
    {
        if (!Current_User::authorized('wiki', 'toggle_lock'))
        {
            Current_User::disallow();
            return;
        }

        $this->allow_edit = ($this->allow_edit) ? 0 : 1;
        PHPWS_Error::logIfError($this->save(FALSE));
    }

    function getCategories()
    {
        if ($this->key_id)
        {
            $result = Categories::getSimpleLinks($this->key_id);
            if (!empty($result))
            {
                return implode(', ', $result);
            }
        }
    }

    function menu()
    {
        if (!$this->getId())
        {
            return NULL;
        }

        $links = NULL;
        if ((Current_User::allow('wiki', 'edit_page') ||
        (PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged())) && $this->allow_edit)
        {
            $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::secureLink(dgettext('wiki', 'Edit'), 'wiki',
            array('page_op'=>'edit', 'page'=>$this->getTitle(FALSE)))), 'wiki', 'menu_item.tpl');
            $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::secureLink(dgettext('wiki', 'Move'), 'wiki',
            array('page_op'=>'move', 'page'=>$this->getTitle(FALSE)))), 'wiki', 'menu_item.tpl');
        }
        else if (PHPWS_Settings::get('wiki', 'immutable_page'))
        {
            $links .= PHPWS_Template::process(array('LINK'=>dgettext('wiki', 'Immutable Page')), 'wiki', 'menu_item.tpl');
        }

        if (Current_User::allow('wiki', 'delete_page'))
        {
            $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::secureLink(dgettext('wiki', 'Delete'), 'wiki',
            array('page_op'=>'delete', 'page'=>$this->getTitle(FALSE)))), 'wiki', 'menu_item.tpl');
        }

        if (Current_User::allow('wiki', 'toggle_lock'))
        {
            if ($this->allow_edit)
            {
                $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::secureLink(dgettext('wiki', 'Unlocked'), 'wiki',
                array('page_op'=>'togglelock', 'page'=>$this->getTitle(FALSE)))), 'wiki', 'menu_item.tpl');
            }
            else
            {
                $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::secureLink(dgettext('wiki', 'Locked'), 'wiki',
                array('page_op'=>'togglelock', 'page'=>$this->getTitle(FALSE)))), 'wiki', 'menu_item.tpl');
            }
        }

        if (PHPWS_Settings::get('wiki', 'discussion'))
        {
            $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::moduleLink(dgettext('wiki', 'Discussion'), 'wiki',
            array('page_op'=>'discussion', 'page_id'=>$this->getId()))), 'wiki', 'menu_item.tpl');
        }

        if (PHPWS_Settings::get('wiki', 'raw_text'))
        {
            $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::moduleLink(dgettext('wiki', 'Raw Text'), 'wiki',
            array('page_op'=>'raw', 'page'=>$this->getTitle(FALSE)))), 'wiki', 'menu_item.tpl');
        }

        if (PHPWS_Settings::get('wiki', 'print_view'))
        {
            $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::moduleLink(dgettext('wiki', 'Print View'), 'wiki',
            array('page_op'=>'print', 'page'=>$this->getTitle(FALSE)), 'blank')), 'wiki', 'menu_item.tpl');
        }

        $links .= PHPWS_Template::process(array('LINK'=>PHPWS_Text::moduleLink(dgettext('wiki', 'History'), 'wiki',
        array('page_op'=>'history', 'page_id'=>$this->getId()))), 'wiki', 'menu_item.tpl');

        return $links;
    }

    function view()
    {
        $tags = array();

        if($this->getId())
        {
            $tags['PAGETEXT'] = $this->getPagetext();

            if (PHPWS_Settings::get('wiki', 'show_modified_info'))
            {
                $editor = $this->getEditor();
                if (Current_User::isLogged() && (Current_User::getId() != $this->getEditorId()))
                {
                    PHPWS_Core::initModClass('notes', 'My_Page.php');
                    PHPWS_Core::initModClass('notes', 'Note_Item.php');
                    $editor = str_replace(dgettext('wiki', 'Send note'), $editor, Note_Item::sendLink($this->getEditorId()));
                }

                $tags['UPDATED_INFO'] = sprintf(dgettext('wiki', 'Last modified %1$s by %2$s'), $this->getUpdated(), $editor);
            }

            if (isset($_REQUEST['module']) && PHPWS_Settings::get('wiki', 'add_to_title'))
            {
                Layout::addPageTitle($this->getTitle());
            }

            // Only set key flag if in viewing mode
            if (isset($_REQUEST['module']) && isset($_REQUEST['page_op']) && ($_REQUEST['page_op'] == 'view'))
            {
                $key = $this->getKey();
                $key->flag();
            }
        }
        else if(isset($this->pagetext))
        {
            $tags['PAGETEXT'] = $this->getPagetext();
        }
        else
        {
            $tags['PAGETEXT'] = dgettext('wiki', 'This page does not exist yet.');
            if (Current_User::allow('wiki', 'edit_page') ||
            (PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged()))
            {
                $tags['PAGETEXT'] .= ' ' . PHPWS_Text::secureLink(dgettext('wiki', 'Create new empty page'), 'wiki',
                array('page_op'=>'edit', 'page'=>$this->getTitle(FALSE)));
            }
        }
        // For print view only
        if (isset($_REQUEST['page_op']) && ($_REQUEST['page_op'] == 'print'))
        {
            $tags['PAGENAME'] = $this->getTitle();
        }

        // Display the menu and message if in view mode
        if (isset($_REQUEST['page_op']) && ($_REQUEST['page_op'] == 'view'))
        {
            $tags['MENU'] = $this->menu();
            $tags['MESSAGE'] = WikiManager::getMessage();
        }

        return PHPWS_Template::process($tags, 'wiki', 'view.tpl');
    }

    function edit($preview=FALSE)
    {
        if ((!Current_User::authorized('wiki', 'edit_page') &&
        !(PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged())) || !$this->allow_edit)
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to wiki page edit.'));
            return;
        }

        $form = new PHPWS_Form;
        $form->addHidden('module', 'wiki');
        $form->addHidden('page_op', 'save');
        $form->addHidden('page', $this->getTitle(FALSE));

        $form->addTextArea('pagetext', $this->getPagetext(FALSE));
        $form->setRows('pagetext', 25);
        $form->setWidth('pagetext', '99%');
        $form->setLabel('pagetext', dgettext('wiki', 'Page Text'));

        $form->addText('comment');
        $form->setSize('comment', 50, 200);
        $form->setLabel('comment', dgettext('wiki', 'Optional comment about this edit'));
        // Needed for preview case
        if (isset($_POST['comment']))
        {
            $form->setValue('comment', stripslashes($_POST['comment']));
        }

        $form->addSubmit('save', dgettext('wiki', 'Save'));
        $form->addSubmit('preview', dgettext('wiki', 'Preview'));
        $form->addSubmit('cancel', dgettext('wiki', 'Cancel'));

        if ($preview)
        {
            $form->addTplTag('PREVIEW_PAGE', $this->view());
        }

        return PHPWS_Template::process($form->getTemplate(), 'wiki', 'edit.tpl');
    }

    function post()
    {
        if ((!Current_User::authorized('wiki', 'edit_page') &&
        !(PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged())) || !$this->allow_edit)
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to wiki page save.'));
            return;
        }

        if (isset($_POST['cancel']))
        {
            /* sendMessage does not return */
            WikiManager::sendMessage(dgettext('wiki', 'Edit Cancelled!'), array('page'=>$this->getTitle(FALSE)), FALSE);
        }

        $this->setPagetext($_POST['pagetext']);

        if(isset($_POST['preview']))
        {
            return $this->edit(TRUE);
        }

        $this->setOwnerId(Current_User::getId());
        $this->setEditorId(Current_User::getId());
        $this->setCreated(mktime());
        $this->setUpdated(mktime());
        $this->setComment($_POST['comment']);

        $result = $this->save();
        if (PHPWS_Error::logIfError($result))
        {
            WikiManager::sendMessage(dgettext('wiki', 'Page could not be saved.'),
            array('page'=>$this->getTitle(FALSE)), FALSE);
        }

        PHPWS_Core::initModClass('version', 'Version.php');
        $version = new Version('wiki_pages');
        $version->setSource($this);
        $version->setApproved(1);
        $version->save();

        WikiManager::sendEmail();
        WikiManager::sendMessage(dgettext('wiki', 'Wiki Page Saved!'), array('page'=>$this->getTitle(FALSE)), FALSE);
    }

    function save($save_key=TRUE)
    {
        $db = new PHPWS_DB('wiki_pages');
        $result = $db->saveObject($this);
        if (PEAR::isError($result))
        {
            return $result;
        }

        if ($save_key)
        {
            $result = $this->saveKey();
            if (PEAR::isError($result))
            {
                return $result;
            }

            $search = new Search($this->key_id);
            $search->resetKeywords();
            $search->addKeywords($this->title);
            $search->addKeywords($this->pagetext);
            $search->save();
        }
    }

    function saveKey()
    {
        if (empty($this->key_id))
        {
            $key = new Key;
            $key->module = 'wiki';
            $key->item_name = 'page';
            $key->item_id = $this->getId();
            $key->edit_permission = 'edit_page';
        }
        else
        {
            $key = new Key($this->key_id);
        }

        $key->title = $this->getTitle();
        $key->url = (MOD_REWRITE_ENABLED ? 'wiki/' : 'index.php?module=wiki&page=') . $this->getTitle(FALSE);
        $result = $key->save();
        if (PEAR::isError($result))
        {
            return $result;
        }

        if (empty($this->key_id))
        {
            $this->key_id = $key->id;
            $this->save(FALSE);
        }
    }

    function kill()
    {
        if (!Current_User::authorized('wiki', 'delete_page'))
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to wiki page delete.'));
            return;
        }

        if (isset($_REQUEST['yes']))
        {
            $db = new PHPWS_DB('wiki_pages');
            $db->addWhere('id', $this->getId());
            if (PHPWS_Error::logIfError($db->delete()))
            {
                WikiManager::sendMessage(dgettext('wiki', 'Page could not be deleted.'),
                array('page'=>$this->getTitle(FALSE)), FALSE);
            }

            PHPWS_Core::initModClass('version', 'Version.php');
            Version::flush('wiki_pages', $this->getId());

            $key = new Key($this->key_id);
            PHPWS_Error::logIfError($key->delete());

            WikiManager::sendMessage(sprintf(dgettext('wiki', '%s deleted!'), $this->getTitle()), array(), FALSE);
        }
        else if (isset($_REQUEST['no']))
        {
            WikiManager::sendMessage(sprintf(dgettext('wiki', '%s was not deleted!'), $this->getTitle()),
            array('page'=>$this->getTitle(FALSE)), FALSE);
        }
        else
        {
            $tags = array();
            $tags['MESSAGE'] = dgettext('wiki', 'Are you sure you want to delete this wiki page?');
            $tags['YES'] = PHPWS_Text::secureLink(dgettext('wiki', 'Yes'), 'wiki', array('page_op'=>'delete', 'yes'=>1,
                                                  'page'=>$this->getTitle(FALSE)));
            $tags['NO'] = PHPWS_Text::secureLink(dgettext('wiki', 'No'), 'wiki', array('page_op'=>'delete', 'no'=>1,
                                                 'page'=>$this->getTitle(FALSE)));
            $tags['WIKIPAGE'] = $this->view();

            return PHPWS_Template::process($tags, 'wiki', 'confirm.tpl');
        }
    }

    function history()
    {
        PHPWS_Core::initModClass('wiki', 'OldWikiPage.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        if (PHPWS_Settings::get('wiki', 'add_to_title'))
        {
            Layout::addPageTitle($this->getTitle());
        }

        $tags['BACK']     = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Page'), 'wiki', array('page'=>$this->getTitle(FALSE)));
        $tags['TITLE']    = dgettext('wiki', 'Revision History');
        $tags['VERSION']  = dgettext('wiki', 'Version');
        $tags['UPDATED']  = dgettext('wiki', 'Updated');
        $tags['EDITOR']   = dgettext('wiki', 'Editor');
        $tags['COMMENT']  = dgettext('wiki', 'Comment');
        $tags['DIFF']     = dgettext('wiki', 'Compare To');
        $tags['ACTIONS']  = dgettext('wiki', 'Actions');

        $pager = new DBPager('wiki_pages_version', 'OldWikiPage');
        $pager->setModule('wiki');
        $pager->setTemplate('history/list.tpl');
        $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
        $pager->addPageTags($tags);
        $pager->addRowTags('getHistoryTpl');
        $pager->addWhere('source_id', $this->getId());
        $pager->setSearch('pagetext', 'comment');
        $pager->setDefaultOrder('vr_number', 'desc');

        return $pager->get();
    }

    function whatLinksHere()
    {
        $tags = array();
        $tags['BACK_PAGE'] = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Page'), 'wiki',
        array('page'=>$this->getTitle(FALSE)));
        $tags['TITLE'] = dgettext('wiki', 'The following pages link to here');
        $tags['LINKS'] = NULL;

        $db = new PHPWS_DB('wiki_pages');
        $db->addColumn('title');
        $db->addWhere('pagetext', '%' . $this->getTitle(FALSE) . '%', 'LIKE');
        $db->addWhere('title', $this->getTitle(FALSE), '!=');
        $db->addOrder('title');
        $result = $db->select('col');

        if (PHPWS_Error::logIfError($result) || ($result == NULL))
        {
            $tags['MESSAGE'] = dgettext('wiki', 'None');
        }
        else
        {
            foreach ($result as $row)
            {
                $link = PHPWS_Text::moduleLink(WikiManager::formatTitle($row), 'wiki', array('page'=>$row));
                $tags['LINKS'] .= PHPWS_Template::process(array('LINK'=>$link), 'wiki', 'whatlinkshere/link.tpl');
            }
        }

        return PHPWS_Template::process($tags, 'wiki', 'whatlinkshere/page.tpl');
    }

    function move()
    {
        if ((!Current_User::authorized('wiki', 'edit_page') &&
        !(PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged())) || !$this->allow_edit)
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to wiki page move.'));
            return;
        }

        $form = new PHPWS_Form;
        $form->addHidden('module', 'wiki');
        $form->addHidden('page_op', 'do_move');
        $form->addHidden('page', $this->getTitle(FALSE));

        $form->addText('newpage');
        $form->setSize('newpage', 40, 100);
        $form->setLabel('newpage', dgettext('wiki', 'New title'));

        $form->addSubmit('move', dgettext('wiki', 'Move'));

        $form->addTplTag('BACK_PAGE', PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Page'), 'wiki',
        array('page'=>$this->getTitle(FALSE))));
        $form->addTplTag('MESSAGE', WikiManager::getMessage());
        $form->addTplTag('INSTRUCTIONS', dgettext('wiki', 'Using the form below will rename a page, moving all of its history
                         to the new name. The old title will become a redirect page to the new title. Links to the old page
                         title will not be changed. You are responsible for making sure that links continue to point where they
                         are supposed to go. Note that the page will not be moved if there is already a page at the new title.'));

        return PHPWS_Template::process($form->getTemplate(), 'wiki', 'move.tpl');
    }

    function doMove()
    {
        if ((!Current_User::authorized('wiki', 'edit_page') &&
        !(PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged())) || !$this->allow_edit)
        {
            Current_User::disallow(dgettext('wiki', 'User attempted to execute a wiki page move.'));
            return;
        }

        if(strlen($_POST['newpage']) == 0)
        {
            WikiManager::sendMessage(dgettext('wiki', 'Please supply a new page title'),
            array('page_op'=>'move','page'=>$this->getTitle(FALSE)));
        }

        $db = new PHPWS_DB('wiki_pages');
        $db->addWhere('title', $_POST['newpage']);
        $result = $db->select();
        if ($result != NULL)
        {
            WikiManager::sendMessage(dgettext('wiki', 'Page with that name already exists!'),
            array('page_op'=>'move','page'=>$this->getTitle(FALSE)));
        }

        $this->setTitle($_POST['newpage']);
        $db->reset();
        $db->saveObject($this);

        $db2 = new PHPWS_DB('wiki_pages_version');
        $db2->addWhere('title', $_POST['page']);
        $db2->addValue('title', $this->getTitle(FALSE));
        $db2->update();

        $db3 = new PHPWS_DB('phpws_key');
        $db3->addWhere('item_id', $this->getId());
        $db3->addWhere('module', 'wiki');
        $db3->addValue('title', $this->getTitle());
        $db3->addValue('url', (MOD_REWRITE_ENABLED ? 'wiki/' : 'index.php?module=wiki&page=') . $this->getTitle(FALSE));
        $db3->update();

        // Create redirect page
        $redirect = new WikiPage($_POST['page']);
        $redirect->setPagetext(sprintf(dgettext('wiki', 'This page has moved to %s.  Please modify links to point to the new location.'),
        $this->getTitle(FALSE)));
        $redirect->setOwnerId(Current_User::getId());
        $redirect->setEditorId(Current_User::getId());
        $redirect->setCreated(mktime());
        $redirect->setUpdated(mktime());
        $redirect->setComment(sprintf(dgettext('wiki', 'Moved page to %s.'), $this->getTitle(FALSE)));
        $redirect->save();

        PHPWS_Core::initModClass('version', 'Version.php');
        $version = new Version('wiki_pages');
        $version->setSource($redirect);
        $version->setApproved(1);
        $version->save();

        WikiManager::sendMessage(dgettext('wiki', 'Wiki Page Moved!'), array('page'=>$this->getTitle(FALSE)), FALSE);
    }

    function discussion()
    {
        if (PHPWS_Settings::get('wiki', 'discussion'))
        {
            PHPWS_Core::initModClass('comments', 'Comments.php');
            $thread = Comments::getThread($this->key_id);

            /* Set anonymous posting each time in case setting has changed */
            $thread->allowAnonymous(PHPWS_Settings::get('wiki', 'discussion_anon'));
            $thread->save();

            $back = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Page'), 'wiki', array('page'=>$this->getTitle(FALSE)));
            return $back . '<br />' . $thread->view();
        }
    }

    function action()
    {
        switch($_REQUEST['page_op'])
        {
            case 'edit':
                $template['TITLE'] = dgettext('wiki', 'Edit') . ' ' . $this->getTitle();
                $template['CONTENT'] = $this->edit();
                break;

            case 'save':
                $template['TITLE'] = dgettext('wiki', 'Edit') . ' ' . $this->getTitle();
                $template['CONTENT'] = $this->post();
                break;

            case 'delete':
                $template['TITLE'] = dgettext('wiki', 'Delete') . ' ' . $this->getTitle();
                $template['CONTENT'] = $this->kill();
                break;

            case 'raw':
                Header('Content-type: text/plain');
                echo $this->getPagetext(FALSE);
                exit();
                break;

            case 'print':
                Layout::nakedDisplay($this->view());
                break;

            case 'history':
                $template['TITLE'] = $this->getTitle();
                $template['CONTENT'] = $this->history();
                break;

            case 'viewold':
                PHPWS_Core::initModClass('wiki', 'OldWikiPage.php');
                $oldpage = new OldWikiPage($_REQUEST['id']);
                $template['TITLE'] = $this->getTitle();
                $template['CONTENT'] = $oldpage->view();
                break;

            case 'restore':
                PHPWS_Core::initModClass('wiki', 'OldWikiPage.php');
                $oldpage = new OldWikiPage($_REQUEST['id']);
                $oldpage->restore($this->hits); /* Does not return */
                break;

            case 'removeold':
                PHPWS_Core::initModClass('wiki', 'OldWikiPage.php');
                $oldpage = new OldWikiPage($_REQUEST['id']);
                $oldpage->remove(); /* Does not return */
                break;

            case 'compare':
                PHPWS_Core::initModClass('wiki', 'WikiDiff.php');
                $wikiDiff = new WikiDiff(PHPWS_Settings::get('wiki', 'diff_type'));
                $template['TITLE'] = $this->getTitle();
                $template['CONTENT'] = $wikiDiff->diff($_REQUEST['oVer'], $_REQUEST['nVer']);
                break;

            case 'whatlinkshere':
                $template['TITLE'] = $this->getTitle();
                $template['CONTENT'] = $this->whatLinksHere();
                break;

            case 'move':
                $template['TITLE'] = dgettext('wiki', 'Move') . ' ' . $this->getTitle();
                $template['CONTENT'] = $this->move();
                break;

            case 'do_move':
                /* Function never returns: user will be redirected to new page. */
                $this->doMove();
                break;

            case 'discussion':
                $template['TITLE'] = $this->getTitle() . ' ' . dgettext('wiki', 'Discussion');
                $template['CONTENT'] = $this->discussion();
                break;

            case 'togglelock':
                $this->toggleLock();
                PHPWS_Core::goBack();
                break;

            default:
                $this->incHits();
                $template['TITLE'] = $this->getTitle();
                $template['CONTENT'] = $this->view();
                $template['CATEGORIES'] = $this->getCategories();
        }

        Layout::add(PHPWS_Template::process($template, 'wiki', 'box.tpl'), 'wiki', 'wiki_mod', TRUE);
    }

    function isOrphaned()
    {
        $db = new PHPWS_DB('wiki_pages');
        $db->addWhere('pagetext', '%' . $this->getTitle(FALSE) . '%', 'LIKE');
        $db->addWhere('id', $this->getId(), '!=');

        if($db->count())
        {
            return PHPWS_Text::moduleLink(dgettext('wiki', 'No'), 'wiki',
            array('page'=>$this->getTitle(FALSE), 'page_op'=>'whatlinkshere'));
        }

        return dgettext('wiki', 'Yes');
    }

    function getTpl()
    {
        $vars['page'] = $this->getTitle(FALSE);

        $links[] = PHPWS_Text::moduleLink(dgettext('wiki', 'View'), 'wiki', $vars);

        if (Current_User::allow('wiki', 'edit_page'))
        {
            $vars['page_op'] = 'edit';
            $links[] = PHPWS_Text::secureLink(dgettext('wiki', 'Edit'), 'wiki', $vars);
        }

        if (Current_User::allow('wiki', 'delete_page'))
        {
            $vars['page_op'] = 'delete';
            $links[] = PHPWS_Text::secureLink(dgettext('wiki', 'Delete'), 'wiki', $vars);
        }

        $template['ACTIONS']  = implode(' | ', $links);
        $template['TITLE']    = $this->getTitle();
        $template['UPDATED']  = $this->getUpdated();
        $template['VERSION']  = $this->getVersion();
        $template['HITS']     = $this->getHits();
        $template['ORPHANED'] = $this->isOrphaned();

        return $template;
    }
}

?>