<?php

class Block_Item {
  var $id          = 0;
  var $title       = NULL;
  var $content     = NULL;
  var $_module     = NULL;
  var $_item_id    = NULL;
  var $_itemname   = NULL;

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

  function summarize(){
    return substr(strip_tags($this->getContent()), 0, 40);
  }

  function getContent()
  {
    return PHPWS_Text::parseOutput($this->content);
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

  function view($pin_mode=FALSE)
  {
    if ($pin_mode) {
      $link['action']   = 'pin';
      $link['block_id'] = $this->id;
      $link['mod']   = $this->_module;
      $link['item']  = $this->_item_id;
      $link['itname'] = $this->_itemname;
      $img = '<img src="./images/mod/block/pin.png" />';
      $opt = PHPWS_Text::secureLink($img, 'block', $link);
    } elseif (Current_User::allow('block')) {
      $js_var['ADDRESS'] = 'index.php?module=block&amp;action=remove'
	. '&amp;block_id=' . $this->id
	. '&amp;mod=' . $this->_module
	. '&amp;item=' . $this->_item_id
	. '&amp;itname=' . $this->_itemname
	. '&amp;authkey=' . Current_User::getAuthKey();
	
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

}

?>