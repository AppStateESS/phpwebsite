<?php

/**
 * Administration of blocks
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Block_Admin {

    function action()
    {
        if (!Current_User::allow('block')) {
            Current_User::disallow();
            return;
        }

        $panel = & Block_Admin::cpanel();
        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
        }
        else {
            $tab = $panel->getCurrentTab();
            if (empty($tab)) {
                $action = 'new';
            } else {
                $action = &$tab;
            }
        }

        $content = Block_Admin::route($action);

        $panel->setContent($content);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $linkBase = 'index.php?module=block';
        $tabs['new']  = array ('title'=>_('New'),  'link'=> $linkBase);
        $tabs['list'] = array ('title'=>_('List'), 'link'=> $linkBase);

        $panel = & new PHPWS_Panel('categories');
        $panel->enableSecure();
        $panel->quickSetTabs($tabs);

        $panel->setModule('block');
        return $panel;
    }

    function route($action)
    {
        $title = $content = NULL;
        $message = Block_Admin::getMessage();

        if (isset($_REQUEST['block_id'])) {
            $block = & new Block_Item($_REQUEST['block_id']);
        } else {
            $block = & new Block_Item();
        }

        switch ($action) {
        case 'new':
            $title = _('New Block');
            $content = Block_Admin::edit($block);
            break;

        case 'permissions':
            Block_Admin::setPermissions($block);
            exit();

        case 'delete':
            $block->kill();
            Block_Admin::sendMessage(_('Block deleted.'), 'list');
            break;

        case 'edit':
            $title = ('Edit Block');
            $content = Block_Admin::edit($block);
            break;

        case 'pin':
            Block_Admin::pinBlock($block);
            Block_Admin::sendMessage(_('Block pinned'), 'list');
            break;

        case 'unpin':
            unset($_SESSION['Pinned_Blocks']);
            Block_Admin::sendMessage(_('Block unpinned'), 'list');
            break;

        case 'remove':
            Block_Admin::removeBlock();
            PHPWS_Core::goBack();
            break;

        case 'copy':
            Block_Admin::copyBlock($block);
            PHPWS_Core::goBack();
            break;

        case 'postBlock':
            if (!PHPWS_Core::isPosted()) {
                Block_Admin::postBlock($block);
                $result = $block->save();
            }
            Block_Admin::sendMessage(_('Block saved'), 'list');
            break;

        case 'lock':
            $result = Block_Admin::lockBlock();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            PHPWS_Core::goBack();
            break;

        case 'list':
            $title = _('Block list');
            $content = Block_Admin::blockList();
            break;
        }

        $template['TITLE'] = &$title;
        if (isset($message)) {
            $template['MESSAGE'] = &$message;
        }
        $template['CONTENT'] = &$content;

        return PHPWS_Template::process($template, 'block', 'admin.tpl');
    }

    function sendMessage($message, $command)
    {
        $_SESSION['block_message'] = $message;
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('block', array('action'=>$command), TRUE));
    }

    function getMessage()
    {
        if (isset($_SESSION['block_message'])) {
            $message = $_SESSION['block_message'];
            unset($_SESSION['block_message']);
            return $message;
        }

        return NULL;
    }

    function setPermissions(&$block)
    {
        $key = & new Key($block->key_id);
        $tpl['VIEW_FORM'] = Current_User::getPermissionForm($key);
        $tpl['TITLE'] = _('Set Permissions');
        $content = PHPWS_Template::process($tpl, 'block', 'view_permission.tpl');
        Layout::nakedDisplay($content);   
    }

    function removeBlock()
    {
        if (!isset($_GET['key_id']) || !isset($_GET['block_id'])) {
            return;
        }

        $db = & new PHPWS_DB('block_pinned');
        $db->addWhere('block_id', $_GET['block_id']);
        $db->addWhere('key_id', $_GET['key_id']);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }

    }

    function edit(&$block)
    {
        PHPWS_Core::initCoreClass('Editor.php');
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'block');
        $form->addHidden('action', 'postBlock');

        $form->addText('title', $block->getTitle());
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 50);

        if (empty($block->id)) {
            $form->addSubmit('submit', _('Save New Block'));
        } else {
            $form->addHidden('block_id', $block->getId());
            $form->addSubmit('submit', _('Update Current Block'));
        }

        $form->addTextArea('block_content', $block->getContent());
        $form->setRows('block_content', '10');
        $form->setWidth('block_content', '80%');
        $form->setLabel('block_content', _('Entry'));
        $form->useEditor('block_content');


        $template = $form->getTemplate();

        $content = PHPWS_Template::process($template, 'block', 'edit.tpl');
        return $content;
    }

    function postBlock(&$block)
    {
        $block->setTitle($_POST['title']);
        $block->setContent($_POST['block_content']);
        return TRUE;
    }


    function blockList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
    
        $pageTags['TITLE']   = _('Title');
        $pageTags['CONTENT'] = _('Content');
        $pageTags['ACTION']  = _('Action');
        $pager = & new DBPager('block', 'Block_Item');
        $pager->setModule('block');
        $pager->setTemplate('list.tpl');
        $pager->addToggle('class="bgcolor1"');
        $pager->addPageTags($pageTags);
        $pager->addRowTags('getTpl');
    
        $content = $pager->get();

        return $content;
    }

  
    function pinBlock(&$block)
    {
        $_SESSION['Pinned_Blocks'][$block->getID()] = $block;
    }
  

    function lockBlock()
    {
        $block_id = (int)$_GET['block_id'];
        $key_id = (int)$_GET['key_id'];

        unset($_SESSION['Pinned_Blocks'][$block_id]);

        $values['block_id'] = $block_id;
        $values['key_id']   = $key_id;

        $db = & new PHPWS_DB('block_pinned');
        $db->addWhere($values);
        $result = $db->delete();
        $db->addValue($values);
        return $db->insert();
    }
  
    function copyBlock(&$block)
    {
        Clipboard::copy($block->getTitle(), $block->getTag());
    }
}
?>