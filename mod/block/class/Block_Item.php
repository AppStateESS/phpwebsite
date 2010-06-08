<?php
/**
 * Class for individual block items
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Block_Item {
	public $id         = 0;
	public $key_id     = 0;
	public $title      = null;
	public $content    = null;
	public $file_id    = 0;
	public $hide_title = 0;
	public $_pin_key   = null;


	public function __construct($id=null)
	{
		if (empty($id)) {
			return;
		}

		$this->setId($id);
		$this->init();
	}

	public function setId($id)
	{
		$this->id = (int)$id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setTitle($title)
	{
		$this->title = strip_tags($title);
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getContentVar()
	{
		return 'block_' . $this->id;
	}

	public function setContent($content)
	{
		$this->content = \core\Text::parseInput($content);
	}

	public function getContent($format=TRUE)
	{
		if ($format) {
			return \core\Text::parseTag(core\Text::parseOutput($this->content), null, 'block');
		} else {
			return $this->content;
		}
	}

	public function setPinKey($key)
	{
		$this->_pin_key = $key;
	}

	public function getKey()
	{
		$key = new \core\Key('block', 'block', $this->id);
		return $key;
	}

	public function getTag()
	{
		return '[block:' . $this->id . ']';
	}

	public function summarize(){
		return substr(strip_tags($this->getContent()), 0, 40);
	}

	public function init()
	{
		if (empty($this->id)) {
			return FALSE;
		}

		$db = new \core\DB('block');
		return $db->loadObject($this);
	}

	public function save($save_key=TRUE)
	{
		$db = new \core\DB('block');
		$result = $db->saveObject($this);
		if (core\Error::isError($result)) {
			return $result;
		}

		if ($save_key) {
			$this->saveKey();
		}
	}

	public function saveKey()
	{
		if (empty($this->key_id)) {
			$key = new \core\Key;
			$key->module = $key->item_name = 'block';
			$key->item_id = $this->id;
		} else {
			$key = new \core\Key($this->key_id);
		}

		$key->edit_permission = 'edit_block';
		$key->title = $this->title;
		$result = $key->save();
		if (core\Error::isError($result)) {
			return $result;
		}

		if (empty($this->key_id)) {
			$this->key_id = $key->id;
			$this->save(FALSE);
		}

	}

	public function clearPins()
	{
		$db = new \core\DB('block_pinned');
		$db->addWhere('block_id', $this->id);
		$db->delete();
	}

	public function kill()
	{
		$this->clearPins();
		$db = new \core\DB('block');
		$db->addWhere('id', $this->id);

		$result = $db->delete();

		if (core\Error::isError($result)) {
			core\Error::log($result);
		}

		$key = new \core\Key($this->key_id);
		$result = $key->delete();
		if (core\Error::isError($result)) {
			core\Error::log($result);
		}
	}

	public function view($pin_mode=FALSE, $admin_icon=TRUE)
	{
		$edit = $opt = null;
		if (Current_User::allow('block', 'edit_block', $this->id)) {
			$img = \core\Icon::show('edit', dgettext('block', 'Edit block'));
			$edit = \core\Text::secureLink($img, 'block', array('block_id'=>$this->id,
                                                                'action'=>'edit'));

			if (!empty($this->_pin_key) && $pin_mode) {
				$link['action']   = 'lock';
				$link['block_id'] = $this->id;
				$link['key_id'] = $this->_pin_key->id;
				$img = '<img src="' . PHPWS_SOURCE_HTTP . '/mod/block/img/pin.png" />';
				$opt = \core\Text::secureLink($img, 'block', $link);
			} elseif (!empty($this->_pin_key) && $admin_icon) {
				$vars['action'] = 'remove';
				$vars['block_id'] = $this->id;
				$vars['key_id'] = $this->_pin_key->id;
				$js_var['ADDRESS'] = \core\Text::linkAddress('block', $vars, TRUE);
				$js_var['QUESTION'] = dgettext('block', 'Are you sure you want to remove this block from this page?');
				$icon = \core\Icon::get('close');
				$icon->setAlt(dgettext('block', 'Delete block'));
				$icon->setStyle('margin : 3px');
				$js_var['LINK'] = $icon->__toString();

				$opt = Layout::getJavascript('confirm', $js_var);
			}
		}

		$link['block_id'] = $this->id;
		$template = array('CONTENT'=>$this->getContent(), 'FILE'=>Cabinet::getTag($this->file_id), 'OPT'=>$opt, 'EDIT'=>$edit, 'ID' => $this->getId());
		if (!$this->hide_title) {
			$template['TITLE'] = $this->getTitle();
		}
		return \core\Template::process($template, 'block', 'sample.tpl');
	}

	public function isPinned()
	{
		if (!isset($_SESSION['Pinned_Blocks'])) {
			return FALSE;
		}

		return isset($_SESSION['Pinned_Blocks'][$this->id]);
	}

	public function allPinned()
	{
		static $all_pinned = null;

		if (empty($all_pinned)) {
			$db = new \core\DB('block_pinned');
			$db->addWhere('key_id', -1);
			$db->addColumn('block_id');
			$result = $db->select('col');
			if (core\Error::isError($result)) {
				core\Error::log($result);
				return false;
			}
			if ($result) {
				$all_pinned = & $result;
			} else {
				$all_pinned = true;
			}
		}

		if (is_array($all_pinned)) {
			return in_array($this->id, $all_pinned);
		} else {
			return false;
		}

	}

	public function getTpl()
	{
		$vars['block_id'] = $this->getId();

		if (Current_User::allow('block', 'edit_block', $this->id)) {
			$vars['action'] = 'edit';
			$links[] = \core\Text::secureLink(core\Icon::show('edit', dgettext('block', 'Edit')), 'block', $vars);
			if ($this->isPinned()) {
				$vars['action'] = 'unpin';
				$links[] = \core\Text::secureLink(core\Icon::show('unsticky', dgettext('block', 'Unpin')), 'block', $vars);
			} else {
				if ($this->allPinned()) {
					$vars['action'] = 'remove';
					$links[] = \core\Text::secureLink(core\Icon::show('unsticky', dgettext('block', 'Unpin all')), 'block', $vars);
				} else {
					$vars['action'] = 'pin';
					$links[] = \core\Text::secureLink(core\Icon::show('clip', dgettext('block', 'Clip')), 'block', $vars);
					$vars['action'] = 'pin_all';
					$links[] = \core\Text::secureLink(core\Icon::show('sticky_all', dgettext('block', 'Pin all')), 'block', $vars);
				}
			}

			if (Current_User::isUnrestricted('block')) {
				$links[] = Current_User::popupPermission($this->key_id, null, 'icon');
			}


			$vars['action'] = 'copy';
			$links[] = \core\Text::secureLink(core\Icon::show('copy', dgettext('block', 'Copy')), 'block', $vars);
		}


		if (Current_User::allow('block', 'delete_block')) {
			$vars['action'] = 'delete';
			$confirm_vars['QUESTION'] = dgettext('block', 'Are you sure you want to permanently delete this block?');
			$confirm_vars['ADDRESS'] = \core\Text::linkAddress('block', $vars, TRUE);
			$confirm_vars['LINK'] = \core\Icon::show('delete');
			$links[] = javascript('confirm', $confirm_vars);
		}

		if (!empty($links)) {
			$template['ACTION'] = implode('', $links);
		} else {
			$template['ACTION'] = ' ';
		}
		$template['CONTENT'] = $this->summarize();
		return $template;
	}

}

?>