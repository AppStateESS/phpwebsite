<?php
  /**
   * This file attempts to fill in the blanks on older phpwebsite modules
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


  // If gettext is not compiled into your php installation, this function
  // becomes needed.
  /* revisit
   function _($text){
   return $text;
   }
  */
  /**
   * Pre-1.x code
   */
if (!defined('TABLE_PREFIX')) {
    define('TABLE_PREFIX', NULL);
 }
define('PHPWS_TBL_PREFIX', TABLE_PREFIX);
define('CRUTCH_MODULE_ACTIVE', TRUE);
PHPWS_Core::initCoreClass('WizardBag.php');
PHPWS_Core::initCoreClass('Crutch_Form.php');
PHPWS_Core::initCoreClass('Crutch_DB.php');
PHPWS_Core::initModClass('help', 'Help.php');

class CLS_Help extends PHPWS_Help{}

class oldTranslate {
    var $test = 1;
    function it($phrase, $var1=NULL, $var2=NULL, $var3=NULL)
    {
        $phrase = str_replace('[var1]', $var1, $phrase);
        $phrase = str_replace('[var2]', $var2, $phrase);
        $phrase = str_replace('[var3]', $var3, $phrase);

        return $phrase;
    }
}

class Fatcat {
    function getCategoryList()
    {
        return Categories::getCategories();
    }

    function showSelect($module_id=NULL, $mode="multiple", $rows = NULL, $module_title=NULL, $purge=FALSE, $setSticky=TRUE)
    {
        PHPWS_Core::initModClass('categories', 'Category_Item.php');
        if (empty($module_title)) {
            $module_title = PHPWS_Core::getCurrentModule();
        }
        $cat_item = & new Category_Item($module_title);
        $cat_item->setItemId($module_id);
        return $cat_item->getForm();
    }

    function purge($id, $module)
    {
        PHPWS_Core::initModClass('categories', 'Category_Item.php');
        $cat_item = & new Category_Item($module);
        $cat_item->setItemId($id);
        $cat_item->clear();
    }

    function activate()
    {
        echo 'activate function needs to be written';
    }

    function deactivate()
    {
        echo 'deactivate function needs to be written';
    }

    function saveSelect($title, $link, $item_id, $groups=NULL, $module_title=NULL,
                        $href=NULL, $rating=NULL, $active=NULL)
    {
        PHPWS_Core::initModClass('categories', 'Category_Item.php');
        if (empty($module_title)) {
            $module_title = PHPWS_Core::getCurrentModule();
        }

        $cat_item = & new Category_Item($module_title);
        $cat_item->setItemId((int)$item_id);
        $cat_item->setTitle(strip_tags($title));
        $cat_item->setLink($link);
        test($cat_item->save());
    }
}

class oldCore extends oldDB{
    var $home_dir = NULL;
    var $datetime = NULL;

    function oldCore()
    {
        $this->home_dir = '';
        $this->datetime = new PHPWS_DateTime;
    }

    function moduleExists($module)
    {
        PHPWS_Core::moduleExists($module);
    }

    function killSession($session)
    {
        $_SESSION[$session] = NULL;
        unset($_SESSION[$session]);
    }

}

class PHPWS_DateTime{
    var $date_month;
    var $date_day;
    var $date_year;
    var $day_mode;
    var $day_start;
    var $date_order;
    var $time_format;
    var $time_dif;

    function PHPWS_DateTime()
    {
        $this->date_month  = 'm';
        $this->date_day    = 'd';
        $this->date_year   = 'Y';
        $this->day_mode    = 'l';
        $this->day_start   = PHPWS_DAY_START;
        $this->time_dif    = PHPWS_TIME_DIFF * 3600;
    
        // Deprecated.  Use above defines
        $this->date_order  = PHPWS_DATE_FORMAT;
        $this->time_format = PHPWS_TIME_FORMAT;
    }
}

class PHPWS_Layout {
    var $current_theme;

    function PHPWS_Layout()
    {
        $this->current_theme = Layout::getTheme();
    }

    function addPageTitle($title)
    {
        return Layout::addPageTitle($title);
    }

}

class PHPWS_Crutch {

    function setModule()
    {
        if (isset($_REQUEST['module']))
            $GLOBALS['module'] = $_REQUEST['module'];
        else
            $GLOBALS['module'] = 'home';
    }

    function initializeModule($module)
    {
        PHPWS_Crutch::setModule();

        $includeFile = PHPWS_SOURCE_DIR . 'mod/' . $module . '/conf/boost.php';
        include($includeFile);
        if (isset($mod_class_files) && is_array($mod_class_files)){
            foreach ($mod_class_files as $requireMe)
                PHPWS_Core::initModClass($module, $requireMe);
        }

        if (isset($init_object)) {
            $GLOBALS['Crutch_Sessions'][$module] = $init_object;
        }
    }

    function startSessions()
    {
        if (!isset($_SESSION['OBJ_user'])) {
            $_SESSION['OBJ_user'] = $_SESSION['User'];
            $_SESSION['OBJ_user']->admin_switch = & $_SESSION['User']->deity;
        }

        if (!isset($_SESSION['translate'])) {
            $_SESSION['translate'] = & new oldTranslate;
        }

        if (!isset($_SESSION['OBJ_layout'])) {
            $_SESSION['OBJ_layout'] = & new PHPWS_Layout;
        }

        if (!isset($_SESSION['OBJ_fatcat'])) {
            $_SESSION['OBJ_fatcat'] = & new Fatcat;
        }

        $GLOBALS['Crutch_Session_Started'] = TRUE;
    }

    function closeSessions()
    {
        PHPWS_Core::killSession('OBJ_user');
        PHPWS_Core::killSession('translate');
        PHPWS_Core::killSession('OBJ_layout');
    }

    function getOldLayout()
    {
        if (!isset($GLOBALS['pre094_modules']) || !is_array($GLOBALS['pre094_modules']))
            return;

        foreach ($GLOBALS['pre094_modules'] as $module){
            $file = PHPWS_SOURCE_DIR . 'mod/' . $module . '/conf/layout.php';
            if (!is_file($file))
                continue;

            include $file;

            if (!isset($layout_info))
                continue;

            foreach ($layout_info as $layout){
                if (isset($GLOBALS[$layout['content_var']]))
                    Layout::add($GLOBALS[$layout['content_var']], $module, $layout['content_var']);
            }
        }
    }

}

$GLOBALS['core'] = & new oldCore;
if (isset($_REQUEST['module'])){
    $GLOBALS['module'] = $_REQUEST['module'];
 }

PHPWS_Core::initModClass('help', 'Help.php');

?>