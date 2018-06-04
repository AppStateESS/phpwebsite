<?php

/**
 * Class for individual block items
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
class Block_Item {

    public $id = 0;
    public $key_id = 0;
    public $title = null;
    public $content = '';
    public $file_id = 0;
    public $hide_title = 0;
    public $hide_narrow = 0;
    public $_pin_key = null;

    public function __construct($id = null)
    {
        if (empty($id)) {
            return;
        }

        $this->setId($id);
        $this->init();
    }

    public function setId($id)
    {
        $this->id = (int) $id;
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
        $this->content = PHPWS_Text::parseInput($content);
    }

    public function getContent($format = TRUE)
    {
        if ($format) {
            return PHPWS_Text::parseTag(PHPWS_Text::parseOutput($this->content),
                            null, 'block');
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
        $key = new \Canopy\Key('block', 'block', $this->id);
        return $key;
    }

    public function getTag()
    {
        return '[block:' . $this->id . ']';
    }

    public function summarize()
    {
        return substr(strip_tags($this->getContent()), 0, 40);
    }

    public function init()
    {
        if (empty($this->id)) {
            return FALSE;
        }

        $db = new PHPWS_DB('block');
        return $db->loadObject($this);
    }

    public function save($save_key = TRUE)
    {
        $db = new PHPWS_DB('block');
        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if ($save_key) {
            $this->saveKey();
        }
    }

    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new \Canopy\Key;
            $key->module = $key->item_name = 'block';
            $key->item_id = $this->id;
        } else {
            $key = new \Canopy\Key($this->key_id);
        }

        $key->edit_permission = 'edit_block';
        $key->title = $this->title;
        $result = $key->save();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if (empty($this->key_id)) {
            $this->key_id = $key->id;
            $this->save(FALSE);
        }
    }

    public function clearPins()
    {
        $db = new PHPWS_DB('block_pinned');
        $db->addWhere('block_id', $this->id);
        $db->delete();
    }

    public function kill()
    {
        $this->clearPins();
        $db = new PHPWS_DB('block');
        $db->addWhere('id', $this->id);

        $result = $db->delete();

        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }

        $key = new \Canopy\Key($this->key_id);
        $result = $key->delete();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    public function view($pin_mode = FALSE, $admin_icon = TRUE)
    {
        $edit = $opt = null;
        if (Current_User::allow('block', 'edit_block', $this->id)) {
            $img = '<i class="fa fa-edit" title="Edit block"></i>';
            $edit = PHPWS_Text::secureLink($img, 'block',
                            array('block_id' => $this->id,
                        'action' => 'edit'));

            if (!empty($this->_pin_key) && $pin_mode) {
                $link['action'] = 'lock';
                $link['block_id'] = $this->id;
                $link['key_id'] = $this->_pin_key->id;
                $img = '<img src="' . PHPWS_SOURCE_HTTP . '/mod/block/img/pin.png" />';
                $opt = PHPWS_Text::secureLink($img, 'block', $link);
            } elseif (!empty($this->_pin_key) && $admin_icon) {
                $vars['action'] = 'remove';
                $vars['block_id'] = $this->id;
                $vars['key_id'] = $this->_pin_key->id;
                $js_var['ADDRESS'] = PHPWS_Text::linkAddress('block', $vars,
                                TRUE);
                $js_var['QUESTION'] = 'Are you sure you want to remove this block from this page?';
                $js_var['LINK'] = '<i class="fa fa-times-circle" title="Remove block from page"></i>';

                $opt = Layout::getJavascript('confirm', $js_var);
            }
        }

        $link['block_id'] = $this->id;
        $template = array('CONTENT' => $this->getContent(), 'OPT' => $opt, 'EDIT' => $edit, 'ID' => $this->getId());
        if (!$this->hide_title) {
            $template['TITLE'] = $this->getTitle();
        }
        if ($this->hide_narrow) {
            $template['HIDDEN'] = ' hidden-xs';
        }
        return PHPWS_Template::process($template, 'block', 'default.tpl');
    }

    public function allPinned()
    {
        static $all_pinned = null;

        if (empty($all_pinned)) {
            $db = new PHPWS_DB('block_pinned');
            $db->addWhere('key_id', -1);
            $db->addColumn('block_id');
            $result = $db->select('col');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
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
            $links[] = PHPWS_Text::secureLink(Icon::show('edit',
                                    'Edit'), 'block', $vars);
            if ($this->allPinned()) {
                $vars['action'] = 'remove';
                $links[] = PHPWS_Text::secureLink("<i class='fa fa-flag' title='" . dgettext('block',
                                        'Remove block from all pages') . "'></i>", 'block', $vars);
            } else {
                $vars['action'] = 'pin_all';
                $links[] = PHPWS_Text::secureLink("<i class='fa fa-flag-o' title='" . dgettext('block',
                                        'Display block on all pages') . "'></i>", 'block', $vars);
            }

            if (Current_User::isUnrestricted('block')) {
                $links[] = Current_User::popupPermission($this->key_id, null,
                                'icon');
            }
        }

        if (Current_User::allow('block', 'delete_block')) {
            $vars['action'] = 'delete';
            $confirm_vars['QUESTION'] = 'Are you sure you want to permanently delete this block?';
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('block', $vars,
                            TRUE);
            $confirm_vars['LINK'] = '<i class="fa fa-trash-o" title="Delete"></i>';
            $links[] = javascript('confirm', $confirm_vars);
        }

        if (!empty($links)) {
            $template['ACTION'] = implode('', $links);
        } else {
            $template['ACTION'] = ' ';
        }
        if (empty($this->title)) {
            $template['TITLE'] = '<em>' . 'Untitled' . '</em>';
        }
        if (empty($this->content)) {
            $template['CONTENT'] = '<em>' . 'Empty' . '</em>';
        } else {
            $template['CONTENT'] = $this->summarize();
        }
        return $template;
    }

}
