<?php
/**
 * Class for individual block items
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Block_Item {
    var $id        = 0;
    var $title     = NULL;
    var $content   = NULL;
    var $_pin_key  = NULL;
    

    function Block_Item($id=NULL)
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
            return PHPWS_Text::parseOutput($this->content);
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
        $key = & new Key('block', 'block', $this->id);
        return $key;
    }

    function getTag()
    {
        return '[block:' . $this->id . ']';
    }

    function summarize(){
        return substr(strip_tags($this->getContent(TRUE)), 0, 40);
    }

    function init()
    {
        if (empty($this->id)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('block');
        return $db->loadObject($this);
    }

    function save()
    {
        $db = & new PHPWS_DB('block');
        return $db->saveObject($this);
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
        $db->delete();
    }

    function view($pin_mode=FALSE, $admin_icon=TRUE)
    {
        if (!empty($this->_pin_key) && $pin_mode) {
            $link['action']   = 'lock';
            $link['block_id'] = $this->id;
            $link['key_id'] = $this->_pin_key->id;
            $img = '<img src="./images/mod/block/pin.png" />';
            $opt = PHPWS_Text::secureLink($img, 'block', $link);
        } elseif (!empty($this->_pin_key) && Current_User::allow('block') && $admin_icon) {
            $js_var['ADDRESS'] = 'index.php?module=block&amp;action=remove'
                . '&amp;block_id=' . $this->id
                . '&amp;key_id=' . $this->_pin_key->id;        
            $js_var['QUESTION'] = _('Are you sure you want to remove this block from this page?');
            $js_var['LINK'] = '<img src="./images/mod/block/remove.png" />';
        
            $opt = Layout::getJavascript('confirm', $js_var);
        } else {
            $opt = NULL;
        }

        $link['block_id'] = $this->id;
        $template = array('TITLE'   => $this->getTitle(),
                          'CONTENT' => $this->getContent(),
                          'OPT'     => $opt
                          );
    
        return PHPWS_Template::process($template, 'block', 'sample.tpl');
    }

    function isPinned()
    {
        if (!isset($_SESSION['Pinned_Blocks'])) {
            return FALSE;
        }

        return isset($_SESSION['Pinned_Blocks'][$this->id]);
    }

    function getTpl()
    {
        $vars['block_id'] = $this->getId();

        $vars['action'] = 'edit';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'block', $vars);

        if ($this->isPinned()) {
            $vars['action'] = 'unpin';
            $links[] = PHPWS_Text::secureLink(_('Unpin'), 'block', $vars);
        } else {
            $vars['action'] = 'pin';
            $links[] = PHPWS_Text::secureLink(_('Pin'), 'block', $vars);
        }

        $vars['action'] = 'copy';
        $links[] = PHPWS_Text::secureLink(_('Copy'), 'block', $vars);

        $vars['action'] = 'delete';
        $confirm_vars['QUESTION'] = _('Are you sure you want to permanently delete this block?');
        $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('block', $vars, TRUE);
        $confirm_vars['LINK'] = _('Delete');
        $links[] = Layout::getJavascript('confirm', $confirm_vars);

        $template['ACTION'] = implode(' | ', $links);
        $template['CONTENT'] = $this->summarize();

        return $template;
    }

}

?>