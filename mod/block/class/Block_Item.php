<?php
/**
 * Class for individual block items
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Block_Item {
    var $id         = 0;
    var $key_id     = 0;
    var $title      = null;
    var $content    = null;
    var $file_id    = 0;
    var $hide_title = 0;
    var $_pin_key   = null;
    

    function Block_Item($id=null)
    {
        if (empty($id)) {
            return;
        }

        $this->setId($id);
        $this->init();
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle()
    {
        return $this->title;
    }

    function getContentVar()
    {
        return 'block_' . $this->id;
    }

    function setContent($content)
    {
        $this->content = PHPWS_Text::parseInput($content);
    }

    function getContent($format=TRUE)
    {
        if ($format) {
            return PHPWS_Text::parseTag(PHPWS_Text::parseOutput($this->content), null, 'block');
        } else {
            return $this->content;
        }
    }

    function setPinKey($key)
    {
        $this->_pin_key = $key;
    }

    function getKey()
    {
        $key = new Key('block', 'block', $this->id);
        return $key;
    }

    function getTag()
    {
        return '[block:' . $this->id . ']';
    }

    function summarize(){
        return substr(strip_tags($this->getContent()), 0, 40);
    }

    function init()
    {
        if (empty($this->id)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('block');
        return $db->loadObject($this);
    }

    function save($save_key=TRUE)
    {
        $db = new PHPWS_DB('block');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        if ($save_key) {
            $this->saveKey();
        }
    }

    function saveKey()
    {
        if (empty($this->key_id)) {
            $key = & new Key;
            $key->module = $key->item_name = 'block';
            $key->item_id = $this->id;
        } else {
            $key = & new Key($this->key_id);
        }

        $key->edit_permission = 'edit_block';
        $key->title = $this->title;
        $result = $key->save();
        if (PEAR::isError($result)) {
            return $result;
        }

        if (empty($this->key_id)) {
            $this->key_id = $key->id;
            $this->save(FALSE);
        }
        
    }

    function clearPins()
    {
        $db = & new PHPWS_DB('block_pinned');
        $db->addWhere('block_id', $this->id);
        $db->delete();
    }

    function kill()
    {
        $this->clearPins();
        $db = & new PHPWS_DB('block');
        $db->addWhere('id', $this->id);

        $result = $db->delete();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }

        $key = & new Key($this->key_id);
        $result = $key->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    function view($pin_mode=FALSE, $admin_icon=TRUE)
    {
        $edit = $opt = null;
        if (Current_User::allow('block')) {
            $img = sprintf('<img src="./images/mod/block/edit.png" alt="%s" title="%s" />', dgettext('block', 'Edit block'), dgettext('block', 'Edit block'));
            $edit = PHPWS_Text::secureLink($img, 'block', array('block_id'=>$this->id,
                                                                'action'=>'edit'));
            
            if (!empty($this->_pin_key) && $pin_mode) {
                $link['action']   = 'lock';
                $link['block_id'] = $this->id;
                $link['key_id'] = $this->_pin_key->id;
                $img = '<img src="./images/mod/block/pin.png" />';
                $opt = PHPWS_Text::secureLink($img, 'block', $link);
            } elseif (!empty($this->_pin_key) && $admin_icon) {
                $vars['action'] = 'remove';
                $vars['block_id'] = $this->id;
                $vars['key_id'] = $this->_pin_key->id;
                $js_var['ADDRESS'] = PHPWS_Text::linkAddress('block', $vars, TRUE);
                $js_var['QUESTION'] = dgettext('block', 'Are you sure you want to remove this block from this page?');
                $js_var['LINK'] = sprintf('<img src="./images/mod/block/remove.png" alt="%s" title="%s" />', dgettext('block', 'Delete block'), ('Delete block'));
        
                $opt = Layout::getJavascript('confirm', $js_var);
            }
        }
        
        $link['block_id'] = $this->id;
        $template = array('CONTENT' => $this->getContent(),
                          'FILE'    => Cabinet::getTag($this->file_id),
                          'OPT'     => $opt,
                          'EDIT'    => $edit
                          );
        if (!$this->hide_title) {
            $template['TITLE'] = $this->getTitle();
        }
        return PHPWS_Template::process($template, 'block', 'sample.tpl');
    }

    function isPinned()
    {
        if (!isset($_SESSION['Pinned_Blocks'])) {
            return FALSE;
        }

        return isset($_SESSION['Pinned_Blocks'][$this->id]);
    }

    function allPinned()
    {
        static $all_pinned = null;

        if (empty($all_pinned)) {
            $db = new PHPWS_DB('block_pinned');
            $db->addWhere('key_id', -1);
            $db->addColumn('block_id');
            $result = $db->select('col');
            if (PEAR::isError($result)) {
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

    function getTpl()
    {
        $vars['block_id'] = $this->getId();

        if (Current_User::allow('block', 'edit_block', $this->id)) {
            $vars['action'] = 'edit';
            $links[] = PHPWS_Text::secureLink(dgettext('block', 'Edit'), 'block', $vars);
        }

        if ($this->isPinned()) {
            $vars['action'] = 'unpin';
            $links[] = PHPWS_Text::secureLink(dgettext('block', 'Unpin'), 'block', $vars);
        } else {
            if ($this->allPinned()) {
                $vars['action'] = 'remove';
                $links[] = PHPWS_Text::secureLink(dgettext('block', 'Unpin all'), 'block', $vars);
            } else {
                $vars['action'] = 'pin';
                $links[] = PHPWS_Text::secureLink(dgettext('block', 'Pin'), 'block', $vars);
                $vars['action'] = 'pin_all';
                $links[] = PHPWS_Text::secureLink(dgettext('block', 'Pin all'), 'block', $vars);
            }
        }

        if (Current_User::isUnrestricted('block')) {
            $links[] = Current_User::popupPermission($this->key_id);
        }


        $vars['action'] = 'copy';
        $links[] = PHPWS_Text::secureLink(dgettext('block', 'Copy'), 'block', $vars);

        if (Current_User::allow('block', 'delete_block')) {
            $vars['action'] = 'delete';
            $confirm_vars['QUESTION'] = dgettext('block', 'Are you sure you want to permanently delete this block?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('block', $vars, TRUE);
            $confirm_vars['LINK'] = dgettext('block', 'Delete');
            $links[] = javascript('confirm', $confirm_vars);
        }

        $template['ACTION'] = implode(' | ', $links);
        $template['CONTENT'] = $this->summarize();
        return $template;
    }

}

?>