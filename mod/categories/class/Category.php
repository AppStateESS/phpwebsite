<?php
/**
 * Category class.
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */

class Category{
  var $id          = NULL;
  var $title       = NULL;
  var $description = NULL;
  var $parent      = NULL;
  var $image       = NULL;
  var $thumbnail   = NULL;
  var $children    = NULL;


  function Category($id=NULL){
    if (empty($id))
      return;

    $this->setId($id);
    $result = $this->init();
    if (PEAR::isError($result))
      PHPWS_Error::log($result);
  }
  
  function init(){
    $db = & new PHPWS_DB("categories");
    $db->addWhere("id", $this->id);
    $result = $db->loadObject($this);
    if (PEAR::isError($result))
      return $result;

    $this->loadImage();
    $this->loadChildren();
  }

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function setTitle($title){
    $this->title = strip_tags($title);
  }

  function getTitle(){
    return $this->title;
  }

  function setDescription($description){
    $this->description = PHPWS_Text::parseInput($description);
  }

  function getDescription(){
    return PHPWS_Text::parseOutput($this->description);
  }

  function setParent($parent){
    $this->parent = (int)$parent;
  }

  function getParent(){
    return $this->parent;
  }

  function getParentTitle(){
    static $parentTitle = array();

    if ($this->parent == 0) {
      return _("Top Level");
    }

    if (isset($parentTitle[$this->parent]))
      return $parentTitle[$this->parent];

    $parent = & new Category($this->parent);
    $parentTitle[$parent->id] = $parent->title;

    return $parent->title;
  }

  function setImage($image){
    $this->image = $image;

    if (is_numeric($image))
      $this->loadImage();
  }

  function getImage(){
    return $this->image;
  }

  function loadImage(){
    PHPWS_Core::initCoreClass("Image.php");
    if (!empty($this->image))
      $this->image = new PHPWS_Image($this->image);
  }

  function loadChildren(){
    $db = & new PHPWS_DB("categories");
    $db->addWhere("parent", $this->id);
    $db->addOrder("title");
    $result = $db->getObjects("Category");
    if (empty($result)) {
      $this->children = NULL;
      return;
    }

    $this->children = Categories::initList($result);
  }

  function setThumbnail($thumbnail){
    $this->thumbnail = $thumbnail;
  }

  function getThumbnail(){
    return $this->thumbnail;
  }
  
  function save(){
    $db = & new PHPWS_DB("categories");

    if (isset($this->id))
      $db->addWhere("id", $this->id);

    $tmpImage = $this->image;
    $this->image = $this->image->getId();
    $result = $db->saveObject($this);
    $this->image = $tmpImage;
    return $result;
  }

  function kill(){
    if (empty($this->id))
      return FALSE;
    $db = & new PHPWS_DB("categories");
    $db->addWhere("id", $this->id);
    return $db->delete();
  }
}

?>