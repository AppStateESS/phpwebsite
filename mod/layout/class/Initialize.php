<?php


class PHPWS_Layout_Init{

  function loadSettings(){
    $DB = new PHPWS_DB("mod_layout_config");
    $settings = $DB->select("row");

    //if (isset($user_cookie_theme))
    // $settings['current_theme'] = $cookieTheme;

    $settings['current_theme'] = $settings['default_theme'];
    return $settings;
  }

  function loadContentVar(){
    $DB = new PHPWS_DB("mod_layout_vars");
    if (DB::isError($result = $DB->select()))
      return PEAR::raiseError("layout", "loadContentVar", $result->getMessage());
    elseif (is_null($result))
      return NULL;

    foreach ($result as $value)
      $content_vars[$value['content_var']] = $value;

    return $content_vars;
  }


  function loadBoxes(){
    $theme = PHPWS_Layout::getTheme();
    $DB = new PHPWS_DB("mod_layout_box");
    $DB->addWhere("theme", $theme);
    if(!$boxes = $DB->select())
      return NULL;

    foreach ($boxes as $row)
      $final[$row['content_var']] = $row;

    return $final;
  }


  function createBoxes($theme){
    if (isset($_SESSION['Layout_Content_Vars']))
      $content_vars = $_SESSION['Layout_Content_Vars'];
    else
      $content_vars = PHPWS_Layout_Init::loadContentVar();

    foreach ($content_vars as $row)
      PHPWS_Layout::createBox($theme, $row['content_var'], $row['theme_var'], "default.box.tpl");

  }

  function installModule($module){
    include PHPWS_SOURCE_DIR . "mod/$module/conf/layout.php";
    foreach ($layout_info as $row){
      if (PHPWS_Layout::isContentVar($row['content_var']))
	continue;
      PHPWS_Layout_Init::addVar($row['content_var'], $module, $row['theme_var']);
    }
  }


  function addVar($content_var, $module, $theme_var=NULL, $active=1){
    if (!isset($theme_var))
      $theme_var = DEFAULT_THEME_VAR;

    if ((bool)$active)
      $active = 1;
    else
      $active = 0;
    $DB = new PHPWS_DB("mod_layout_vars");
    $DB->addValue("content_var", $content_var);
    $DB->addValue("module", $module);
    $DB->addValue("theme_var", $theme_var);

    $DB->insert();
    PHPWS_Layout::createBox(PHPWS_Layout::getTheme(), $content_var, $theme_var, "default_box.tpl");
  }

  function initTheme(){
    $DB = new PHPWS_DB("mod_layout_box");
  }

}

?>