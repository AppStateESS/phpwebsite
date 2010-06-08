<?php

/**
 * Administration of blocks
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::requireConfig('block');

class Block_Admin {

	public static function action()
	{
		if (!Current_User::allow('block')) {
			Current_User::disallow();
			return;
		}

		$panel = Block_Admin::cpanel();
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

	public static function cpanel()
	{
		Core\Core::initModClass('controlpanel', 'Panel.php');
		$linkBase = 'index.php?module=block';
		$tabs['new']  = array ('title'=>dgettext('block', 'New'),  'link'=> $linkBase);
		$tabs['list'] = array ('title'=>dgettext('block', 'List'), 'link'=> $linkBase);
		$tabs['settings'] = array ('title'=>dgettext('block', 'Settings'), 'link'=> $linkBase);

		$panel = new PHPWS_Panel('categories');
		$panel->enableSecure();
		$panel->quickSetTabs($tabs);

		$panel->setModule('block');
		return $panel;
	}

	public static function route($action)
	{
		$title = $content = NULL;
		$message = Block_Admin::getMessage();

		if (isset($_REQUEST['block_id'])) {
			$block = new Block_Item($_REQUEST['block_id']);
		} else {
			$block = new Block_Item();
		}

		switch ($action) {
			case 'new':
				$title = dgettext('block', 'New Block');
				$content = Block_Admin::edit($block);
				break;

			case 'delete':
				if (!Current_User::authorized('block', 'delete_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}

				$block->kill();
				Block_Admin::sendMessage(dgettext('block', 'Block deleted.'));
				Core\Core::goBack();
				break;

			case 'edit':
				if (!Current_User::authorized('block', 'edit_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}
				$title = ('Edit Block');
				$content = Block_Admin::edit($block);
				break;

			case 'pin':
				if (!Current_User::authorized('block', 'delete_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}

				Block_Admin::pinBlock($block);
				Block_Admin::sendMessage(dgettext('block', 'Block pinned'), 'list');
				break;

			case 'pin_all':
				if (!Current_User::authorized('block', 'delete_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}

				Block_Admin::pinBlockAll($block);
				Block_Admin::sendMessage(dgettext('block', 'Block pinned'), 'list');
				break;

			case 'unpin':
				if (!Current_User::authorized('block', 'delete_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}

				unset($_SESSION['Pinned_Blocks']);
				Block_Admin::sendMessage(dgettext('block', 'Block unpinned'), 'list');
				break;

			case 'remove':
				if (!Current_User::authorized('block', 'edit_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}
				Block_Admin::removeBlock();
				Core\Core::goBack();
				break;

			case 'copy':
				if (!Current_User::authorized('block', 'delete_block', $_REQUEST['block_id'])) {
					Current_User::disallow();
				}

				Block_Admin::copyBlock($block);
				Core\Core::goBack();
				break;

			case 'postBlock':
				Block_Admin::postBlock($block);
				$result = $block->save();
				Block_Admin::sendMessage(dgettext('block', 'Block saved'), 'list');
				break;

			case 'settings':
				$title = dgettext('block', 'Settings');
				$content = Block_Admin::settings();
				break;

			case 'post_settings':
				$result = Block_Admin::postSettings();
				if (is_array($result)) {
					$message = implode('<br />', $result);
					$title = dgettext('block', 'Settings');
					$content = Block_Admin::settings();
				} else {
					Block_Admin::sendMessage(dgettext('block', 'Settings saved'), 'settings');
				}
				break;

			case 'postJSBlock':
				if (!Core\Core::isPosted()) {
					Block_Admin::postBlock($block);
					$result = $block->save();
					if (Core\Error::isError($result)) {
						Core\Error::log($result);
					} elseif (isset($_REQUEST['key_id'])) {
						Block_Admin::lockBlock($block->id, $_REQUEST['key_id']);
					}
				}
				javascript('close_refresh');
				break;

			case 'lock':
				$result = Block_Admin::lockBlock($_GET['block_id'], $_GET['key_id']);
				if (Core\Error::isError($result)) {
					Core\Error::log($result);
				}
				Core\Core::goBack();
				break;

			case 'list':
				$title = dgettext('block', 'Block list');
				$content = Block_Admin::blockList();
				break;

			case 'js_block_edit':
				$template['TITLE'] = dgettext('block', 'New Block');
				$template['CONTENT'] = Block_Admin::edit($block, TRUE);
				$content = Core\Template::process($template, 'block', 'admin.tpl');
				Layout::nakedDisplay($content);
				break;
		}

		$template['TITLE'] = &$title;
		if (isset($message)) {
			$template['MESSAGE'] = &$message;
		}
		$template['CONTENT'] = &$content;
		return Core\Template::process($template, 'block', 'admin.tpl');
	}

	public function sendMessage($message, $command=null)
	{
		$_SESSION['block_message'] = $message;
		if (isset($command)) {
			Core\Core::reroute(Core\Text::linkAddress('block', array('action'=>$command), TRUE));
		}
	}

	public static function getMessage()
	{
		if (isset($_SESSION['block_message'])) {
			$message = $_SESSION['block_message'];
			unset($_SESSION['block_message']);
			return $message;
		}

		return NULL;
	}

	public function removeBlock()
	{
		if (!isset($_GET['block_id'])) {
			return;
		}

		$db = new Core\DB('block_pinned');
		$db->addWhere('block_id', $_GET['block_id']);
		if (isset($_GET['key_id'])) {
			$db->addWhere('key_id', $_GET['key_id']);
		}
		$result = $db->delete();

		if (Core\Error::isError($result)) {
			Core\Error::log($result);
		}

	}

	public static function edit(Block_Item $block, $js=FALSE)
	{
		Core\Core::initModClass('filecabinet', 'Cabinet.php');
				$form = new Core\Form;
		$form->addHidden('module', 'block');

		$form->addCheck('hide_title', 1);
		$form->setMatch('hide_title', $block->hide_title);
		$form->setLabel('hide_title', dgettext('block', 'Hide title'));

		if ($js) {
			$form->addHidden('action', 'postJSBlock');
			if (isset($_REQUEST['key_id'])) {
				$form->addHidden('key_id', (int)$_REQUEST['key_id']);
			}
			$form->addButton('cancel', dgettext('block', 'Cancel'));
			$form->setExtra('cancel', 'onclick="window.close()"');
		} else {
			$form->addHidden('action', 'postBlock');
		}

		$form->addText('title', $block->getTitle());
		$form->setLabel('title', dgettext('block', 'Title'));
		$form->setSize('title', 50);

		if (empty($block->id)) {
			$form->addSubmit('submit', dgettext('block', 'Save New Block'));
		} else {
			$form->addHidden('block_id', $block->getId());
			$form->addSubmit('submit', dgettext('block', 'Update Current Block'));
		}

		$form->addTextArea('block_content', $block->getContent(false));
		$form->setRows('block_content', '10');
		$form->setWidth('block_content', '80%');
		$form->setLabel('block_content', dgettext('block', 'Entry'));
		$form->useEditor('block_content');
		$template = $form->getTemplate();

		$manager = Cabinet::fileManager('file_id', $block->file_id);
		$manager->maxImageWidth(Core\Settings::get('block', 'max_image_width'));
		$manager->maxImageHeight(Core\Settings::get('block', 'max_image_height'));

		$template['FILE_ID'] = $manager->get();

		$content = Core\Template::process($template, 'block', 'edit.tpl');
		return $content;
	}

	public function postBlock(Block_Item $block)
	{
		$block->setTitle($_POST['title']);
		$block->setContent($_POST['block_content']);
		$block->file_id = (int)$_POST['file_id'];
		if (isset($_POST['hide_title'])) {
			$block->hide_title = 1;
		} else {
			$block->hide_title = 0;
		}
		return TRUE;
	}


	public static function blockList()
	{
		
		$pageTags['CONTENT'] = dgettext('block', 'Content');
		$pageTags['ACTION']  = dgettext('block', 'Action');
		$pager = new Core\DBPager('block', 'Block_Item');
		$pager->setModule('block');
		$pager->setTemplate('list.tpl');
		$pager->addToggle('class="bgcolor1"');
		$pager->addPageTags($pageTags);
		$pager->addRowTags('getTpl');
		$pager->addSortHeader('title', dgettext('block', 'Title'));

		$content = $pager->get();
		return $content;
	}


	public function pinBlock(Block_Item $block)
	{
		$_SESSION['Pinned_Blocks'][$block->getID()] = $block;
	}

	public function pinBlockAll(Block_Item $block)
	{
		$values['block_id'] = $block->id;
		$db = new Core\DB('block_pinned');
		$db->addWhere($values);
		$result = $db->delete();
		$db->resetWhere();

		$values['key_id'] = -1;
		$db->addValue($values);

		return $db->insert();
	}


	public function lockBlock($block_id, $key_id)
	{
		$block_id = (int)$block_id;
		$key_id = (int)$key_id;

		unset($_SESSION['Pinned_Blocks'][$block_id]);

		$values['block_id'] = $block_id;
		$values['key_id']   = $key_id;

		$db = new Core\DB('block_pinned');
		$db->addWhere($values);
		$result = $db->delete();
		$db->addValue($values);
		return $db->insert();
	}

	public function copyBlock(Block_Item $block)
	{
		Clipboard::copy($block->getTitle(), $block->getTag());
	}

	public static function settings()
	{
		$form = new Core\Form('block-form');
		$form->addHidden('module', 'block');
		$form->addHidden('action', 'post_settings');

		$form->addText('max_image_width', Core\Settings::get('block', 'max_image_width'));
		$form->setLabel('max_image_width', dgettext('block', 'Max image width (50 - 1024)'));
		$form->setSize('max_image_width', 4, 4);

		$form->addText('max_image_height', Core\Settings::get('block', 'max_image_height'));
		$form->setLabel('max_image_height', dgettext('block', 'Max image height (50 - 3000)'));
		$form->setSize('max_image_height', 4, 4);

		$form->addSubmit(dgettext('block', 'Save settings'));

		$tpl = $form->getTemplate();

		return Core\Template::process($tpl, 'block', 'settings.tpl');
	}

	public function postSettings()
	{
		if (empty($_POST['max_image_width']) || $_POST['max_image_width'] < 50) {
			$error[] = dgettext('block', 'Max image width must be greater than 50px');
		} elseif ($_POST['max_image_width'] > 1024) {
			$error[] = dgettext('block', 'Max image width must be smaller than 1024px');
		} else {
			Core\Settings::set('block', 'max_image_width', (int)$_POST['max_image_width']);
		}

		if (empty($_POST['max_image_height']) || $_POST['max_image_height'] < 50) {
			$error[] = dgettext('block', 'Max image height must be greater than 50px');
		} elseif ($_POST['max_image_height'] > 3000) {
			$error[] = dgettext('block', 'Max image height must be smaller than 3000px');
		} else {
			Core\Settings::set('block', 'max_image_height', (int)$_POST['max_image_height']);
		}

		Core\Settings::save('block');

		if (isset($error)) {
			return $error;
		} else {
			return true;
		}
	}
}
?>