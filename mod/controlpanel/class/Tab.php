<?php

class PHPWS_ControlPanel_Tab {
  var $_id          = NULL;
  var $_title       = NULL;
  var $_link        = NULL;
  var $_directory   = NULL;
  var $_inactivetpl = NULL;
  var $_activetpl   = NULL;
  var $_tab_order   = NULL;
  var $_color       = NULL;
  var $_itemname    = NULL;
  var $_style       = NULL;

  function PHPWS_ControlPanel_Tab($id=NULL) {

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

  function getTitle(){
    return str_replace(" ", "&nbsp;", $this->_title);
  }

  function setLink($link){
    $this->_link = $link;
  }

  function getLink($addTitle=TRUE){
    if ($addTitle){
      $title = $this->getTitle();
      $link = $this->getLink(FALSE);
      return "<a href=\"$link\">$title</a>";
    } else
      return $this->_link;
  }


  function setDirectory($directory){
    $this->_directory = $directory;
  }

  function getDirectory(){
    return $this->_directory;
  }

  function setInactiveTpl($template){
    $this->_inactivetpl = $template;
  }

  function setActiveTpl($template){
    $this->_activetpl = $template;
  }

  function getTemplate($active){
    if ((bool)$active == TRUE)
      return $this->_activetpl;
    else
      return $this->_inactivetpl;
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

  function setItemname($itemname){
    $this->_itemname = $itemname;
  }

  function getItemname(){
    return $this->_itemname;
  }

  function save(){
    $DB = @ new PHPWS_DB("controlpanel_tab");

    $id                   = $this->getId();
    $save['title']        = $this->getTitle();
    $save['link']         = $this->getLink(FALSE);
    $save['directory']    = $this->getDirectory();
    $save['activetpl']    = $this->getTemplate(TRUE);
    $save['inactivetpl']  = $this->getTemplate(FALSE);
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

  function view($active=FALSE, $linkable=TRUE){
    if ($linkable)
      $tpl['TITLE'] = $this->getLink();
    else
      $tpl['TITLE'] = $this->getTitle();

    $filename = $this->getDirectory() . $this->getTemplate($active);

    if ($color = $this->getColor())
      $tpl['COLOR'] = " style=\"background-color : #" . $color . "\"";

    $phptpl = & new PHPWS_Template();

    if ($phptpl->setFile($filename, TRUE) == FALSE)
      return PEAR::raiseError("Unable to find template <b>$filename</b>");

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