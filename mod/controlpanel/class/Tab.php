<?php

class PHPWS_Panel_Tab {
  var $_id          = NULL;
  var $_title       = NULL;
  var $_link        = NULL;
  var $_tab_order   = NULL;
  var $_color       = NULL;
  var $_itemname    = NULL;
  var $_style       = NULL;
  var $_tabfile     = NULL;

  function PHPWS_Panel_Tab($id=NULL) {

    if(isset($id)) {
      $this->setId($id);
      $this->init();
    }
  }

  function setId($id){
    $this->_id = $id;
  }

  function getId(){
    return $this->_id;
  }

  function init(){
    $DB = new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("id", $this->getId());
    $result = $DB->select("row");

    foreach ($result as $key=>$value)
      $this->{'_' . $key} = $value;

  }

  function getId(){
    return $this->_id;
  }

  function setTitle($title){
    $this->_title = strip_tags($title);
  }

  function getTitle($noBreak=TRUE){
    if ($noBreak)
      return str_replace(" ", "&nbsp;", $this->_title);
    else
      return $this->_title;
  }

  function setLink($link){
    $this->_link = $link;
  }

  function getLink($addTitle=TRUE){
    if ($addTitle){
      $title = $this->getTitle();
      $link = $this->getLink(FALSE);
      return "<a href=\"$link" . "&amp;tab=" . $this->getId() . "\">$title</a>";
    } else
      return $this->_link;
  }


  function setOrder($order){
    $this->_tab_order = $order;
  }

  function getOrder(){
    if (isset($this->_tab_order))
      return $this->_tab_order;

    $DB = @ new PHPWS_DB("controlpanel_tab");
    $DB->addWhere('itemname', $this->getItemname());
    $DB->addColumn('tab_order');
    $max = $DB->select("max");
    
    if (PEAR::isError($max))
      exit($max->getMessage());

    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

  function setColor($color){
    $this->_color = $color;
  }

  function getColor(){
    return $this->_color;
  }
  
  function setStyle($style){
    $this->_style = $style;
  }

  function getStyle(){
    return $this->_style = $style;
  }

  function setTabFile($tabfile){
    $this->_tabfile = $tabfile;
  }


  function getTabFile(){

    return $this->_tabfile;
  }

  function setItemname($itemname){
    $this->_itemname = $itemname;
  }

  function getItemname(){
    return $this->_itemname;
  }

  function save(){
    // MUST HAVE ITEMNAME!
    $DB = @ new PHPWS_DB("controlpanel_tab");

    $id                   = $this->getId();
    $save['title']        = $this->getTitle(FALSE);
    $save['link']         = $this->getLink(FALSE);
    $save['color']        = $this->getColor();
    $save['itemname']     = $this->getItemname();
    $save['style']        = $this->getStyle();
    $save['tab_order']    = $this->getOrder();

    foreach ($save as $key=>$value)
      if (is_null($value))
	unset ($save[$key]);

    $DB->addValue($save);

    if (isset($id)){
      $DB->addWhere("id", $id);
      $result = $DB->update();
    } else
      $result = $DB->insert();

    return $result;
  }

  function view($active=FALSE){
    include_once PHPWS_SOURCE_DIR . "mod/controlpanel/conf/config.php";

    $tpl['TITLE'] = $this->getLink();

    if ($active){
      $tpl['STATUS'] = "class=\"active\"";
      $tpl['ACTIVE'] = " ";
    }
    else {
      $tpl['STATUS'] = "class=\"inactive\"";
      $tpl['INACTIVE'] = " ";
    }

    $tabfile = $this->getTabFile();

    if (!isset($tabfile))
      $tabfile = CP_DEFAULT_TAB;

    if ($color = $this->getColor())
      $tpl['COLOR'] = " style=\"background-color : #" . $color . "\"";

    $phptpl = & new PHPWS_Template();

    if ($phptpl->setFile("mod/controlpanel/templates/" . $tabfile, TRUE) == FALSE)
      return PEAR::raiseError("Unable to find template <b>$tabfile</b>");

    $phptpl->setData($tpl);

    $result = $phptpl->get();

    return $result;
  }

  function nextBox(){

    $DB->addWhere("theme", $this->getTheme());
    $DB->addWhere("theme_var", $this->getThemeVar());
    $DB->addColumn("box_order");
    $max = $DB->select("max");
    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

}

?>