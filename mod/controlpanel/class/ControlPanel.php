<?php
/**
 * PHPWS_ControlPanel
 *
 * Main control stucture to link all the administration features 
 * of phpWebSite together
 */

require (PHPWS_SOURCE_DIR . "mod/controlpanel/class/Tab.php");

class PHPWS_ControlPanel {

  /**
   * All of the tabs for the control panel
   *
   * @var     array
   * @access  private
   * @example $this->_tabs[1] = "Site Content";
   */
  var $_tabs = array();

  /**
   * Stores the id of the current active tab
   *
   * @var     integer
   * @access  private
   * @example $this->_currentTab = 2;
   */
  var $_currentTab = NULL;

  /**
   * Constructor for the control panel
   *
   * Initializes the array of tabs for this control panel
   * @access public
   */
  function PHPWS_ControlPanel() {
    $DB = new PHPWS_DB("mod_controlpanel_tab");
    $DB->addOrder("taborder");

    $tabResult = $DB->select("col", "id");

    if($tabResult) {
      $default = TRUE;
      foreach($tabResult as $tab){
	$tabObject = new PHPWS_ControlPanel_Tab($tab);

	/* only add to to tabs array if it contains links for the user */
	if(!$tabObject->isEmpty()) {
	  $title = $tabObject->getTitle();
	  $this->_tabs[$tab] = $title;

	  /* set the current tab to the first tab */
	  if($default) {
	    $this->_currentTab = $tab;
	    $default = FALSE;
	  }
	}
      }
    }
  } // END FUNC PHPWS_ControlPanel()

  /**
   * Display for the control panel
   *
   * Displays this control panel to the user
   * @access public
   * @return TRUE on success and FALSE on failure
   */
  function display() {
    if(!$_SESSION["User"]->getID()) {
      header("Location: index.php");
      exit();
    }

    /* set the current tab if there is an ID being passed */
    if(isset($_REQUEST['CP_TAB']) && is_numeric($_REQUEST['CP_TAB'])) {
      $this->_currentTab = $_REQUEST['CP_TAB'];
    }

    if(is_array($this->_tabs) && (sizeof($this->_tabs) > 0)) {
      foreach($this->_tabs as $id => $title) {
	/* checking to see what was the current tab clicked */
	if($this->_currentTab == $id) {
	  $tabTags['TITLE'] = $title;

	  $panelTags['TABS'][] = PHPWS_Template::process($tabTags, "controlpanel", "tab/active.tpl");
	} else {
	  /* only create the link if it is not the current tab */
	  $tabTags['HREF'] = "./index.php?module=controlpanel&amp;CP_TAB=" . $id;
	  $tabTags['TITLE'] = $title;

	  $panelTags['TABS'][] = PHPWS_Template::process($tabTags, "controlpanel", "tab/inactive.tpl");
	}
      }

      $tab = new PHPWS_ControlPanel_Tab($this->_currentTab);

      $panelTags['TABS'] = implode("", $panelTags['TABS']);
      $panelTags['LINKS'] = $tab->getTab();

      $template['content'] = PHPWS_Template::process($panelTags, "controlpanel", "panel.tpl");

      PHPWS_Layout::add('CNT_controlpanel', $template);

      return TRUE;
    } else {
      return FALSE;
    }
  }// END FUNC display()

  /**
   * Set the currentTab private variable
   *
   * @access public
   * @param  integer $tab the of the tab to make current
   * @return boolean TRUE on success and FALSE on failure
   */
  function setCurrentTab($CP_TAB = NULL) {
    if(isset($CP_TAB) && is_int($CP_TAB)) {
      $this->_currentTab = $CP_TAB;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setCurrentTab()

  /**
   * Get the currentTab private variable
   *
   * @access public
   * @return integer the id of the current tab
   */
  function getCurrentTab() {
    return $this->_currentTab;
  } // END FUNC getCurrentTab()

  function import($module){
    if(!$modInfo = $GLOBALS['core']->getModuleInfo($module)){
      $message = $_SESSION["translate"]->it("The requested module does not exist.");
      return new PHPWS_Error("controlpanel", "PHPWS_ControlPanel::import", $message);
    }

    $file = PHPWS_SOURCE_DIR . "mod/" . $modInfo["mod_directory"] . "/conf/controlpanel.php";

    if (is_file($file))
      include($file);
    else
      return FALSE;

    if (isset($tab) && is_array($tab)){
      foreach($tab as $tabData){
	if(PHPWS_ControlPanel_Tab::tabExists($tabData['label']))
	  continue;
	$newtab = new PHPWS_ControlPanel_Tab();
	$newtab->setLabel($tabData['label']);
	$newtab->setTitle($tabData['title']);
	if (isset($tabData['grid']))
	    $newtab->setGrid($tabData['grid']);
	$newtab->save();
      }
    }

    if (isset($link) && is_array($link)){
      foreach($link as $linkData){
	$newlink = new PHPWS_ControlPanel_Link();
	$newlink->setLabel($linkData['label']);
	$newlink->setModule($linkData['module']);
	$newlink->setURL($linkData['url']);

	if(isset($linkData['admin'])) {
	  $newlink->setAdmin($linkData['admin']);
	}

	if (isset($linkData['description']))
	    $newlink->setDescription($linkData['description']);

	if (is_array($linkData['image'])){
	    $result = $newlink->setImage($linkData['image']);
	    if (PHPWS_Error::isError($result))
	      echo $result->_message;
	}

	$newlink->save();

	if (isset($linkData['tab']) && $newlink->getId() && PHPWS_ControlPanel_Tab::tabExists($linkData['tab'])){
	  $tab = new PHPWS_ControlPanel_Tab();
	  $tab->load($linkData['tab']);
	  
	  $id = $newlink->getId();
	  $tab->addLink($id);
	  $tab->save();
	}
      }
    }
    if (isset($_SESSION['PHPWS_ControlPanel']))
      PHPWS_Core::killSession("PHPWS_ControlPanel");


    return TRUE;
  } // END FUNC import()

  function drop($module){
    if (!($result = $GLOBALS['core']->getAll("select id from mod_controlpanel_tab", TRUE)))
      return;

    foreach ($result as $id)
      $tablist[] = new PHPWS_ControlPanel_Tab($id['id']);

    if (!($idlist = $GLOBALS['core']->getAll("select id from mod_controlpanel_link where module='$module'", TRUE)))
      return;

    foreach ($idlist as $id){
      $link = new PHPWS_ControlPanel_Link($id['id']);
      $link->kill();
      foreach ($tablist as $tab){
	if ($tab->dropLink($id['id']))
	  $tab->save();
      }
    }

    if (isset($_SESSION['PHPWS_ControlPanel']))
      PHPWS_Core::killSession("PHPWS_ControlPanel");
  }

} // END CLASS PHPWS_ControlPanel

?>