<?php

/**
 * Controls layout's settings
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('layout', 'Box.php');

class Layout_Settings {
    public $current_theme    = null;
    public $default_theme    = null;
    public $page_title       = null;
    public $meta_keywords    = null;
    public $meta_description = null;
    public $meta_robots      = null;
    public $meta_owner       = null;
    public $meta_author      = null;
    public $meta_content     = null;
    public $header           = null;
    public $footer           = null;
    public $cache            = true;
    public $deity_reload     = false;

    // !!! Make sure to update your saveSettings function !!!
    // Remove all hidden variables from the update
    public $_contentVars     = array();
    public $_boxes           = array();
    public $_box_order       = array();
    public $_move_box        = false;
    public $_theme_variables = null;
    public $_default_box     = null;
    public $_style_sheets    = null;
    public $_extra_styles    = null;
    public $_key_styles      = null;
    public $_allowed_move    = null;
    public $_true_theme      = null;

    public function __construct($theme=null)
    {
        $this->loadSettings($theme);
        $this->loadContentVars();
        $this->loadBoxes();
        $GLOBALS['Layout_Robots'] = $this->meta_robots;
    }

    public function getBoxThemeVar($module, $contentVar)
    {
        if (isset($this->_boxes[$module][$contentVar])) {
            return $this->_boxes[$module][$contentVar]->getThemeVar();
        } else {
            return false;
        }
    }

    public function getBoxOrder($module, $contentVar)
    {
        if (isset($this->_boxes[$module][$contentVar])) {
            return $this->_boxes[$module][$contentVar]->getBoxOrder();
        } else {
            return false;
        }
    }

    public function getPageTitle($only_root=false)
    {
        if (isset($GLOBALS['Layout_Page_Title_Add']) && !$only_root) {
            return $GLOBALS['Layout_Page_Title_Add'] . PAGE_TITLE_DIVIDER . $this->page_title;
        } else {
            return $this->page_title;
        }
    }

    public function getContentVars()
    {
        return $this->_contentVars();
    }

    public function getMetaTags()
    {
        $meta['meta_author']      = $this->meta_author;
        $meta['meta_keywords']    = $this->meta_keywords;
        $meta['meta_description'] = $this->meta_description;
        $meta['meta_owner']       = $this->meta_owner;
        $meta['meta_robots']      = $this->meta_robots;
        $meta['page_title']       = $this->page_title;

        return $meta;
    }

    public function getPageMetaTags($key_id)
    {
        $db = new PHPWS_DB('layout_metatags');
        $db->addWhere('key_id', $key_id);
        $row = $db->select('row');
        if (PEAR::isError($row)) {
            PHPWS_Error::log($row);
            return null;
        }

        return $row;
    }

    public function getThemeVariables()
    {
        return $this->_theme_variables;
    }

    public function getAllowedVariables()
    {
        return $this->_allowed_move;
    }

    public function isContentVar($module, $contentVar)
    {
        return in_array($module . '_' . $contentVar, $this->_contentVars);
    }

    public function isMoveBox()
    {
        return (bool)$this->_move_box;
    }

    public function loadBoxes()
    {
        $theme = $this->current_theme;
        $db = new PHPWS_db('layout_box');
        $db->addWhere('theme', $theme);
        if(!$boxes = $db->getObjects('Layout_Box')) {
            return;
        }

        foreach ($boxes as $box) {
            $final[$box->module][$box->content_var] = $box;
        }

        $this->_boxes = & $final;
    }


    public function loadContentVars()
    {
        $db = new PHPWS_db('layout_box');
        $db->addWhere('theme', $this->current_theme);
        $db->addColumn('content_var');
        $db->addColumn('module');
        $result = $db->select();

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            PHPWS_Core::errorPage();
        }

        if (empty($result)) {
            return;
        }

        foreach ($result as $c_vars) {
            extract($c_vars);
            $this->_contentVars[] = $module . '_' . $content_var;
        }
    }

    public function loadSettings($theme=null)
    {
        $db = new PHPWS_DB('layout_config');
        $result = $db->loadObject($this, false);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            PHPWS_Core::errorPage();
        }

        if ($theme && is_dir('themes/' . $theme)) {
            $this->default_theme = $theme;
        }

        if (empty($this->current_theme)) {
            $this->current_theme = $this->default_theme;
        }

        $themeInit = PHPWS_SOURCE_DIR . 'themes/' . $this->current_theme . '/theme.ini';

        if (is_file($themeInit)){
            $themeVars = parse_ini_file($themeInit, true);
            $this->loadBoxSettings($themeVars);
            $this->loadStyleSheets($themeVars);
        } else {
            PHPWS_Error::log(LAYOUT_INI_FILE, 'layout', 'Layout_Settings::loadSettings', $themeInit);
            PHPWS_Core::errorPage();
        }
        if (Current_User::isDeity()) {
            $this->deity_reload = true;
        }
    }

    public function loadStyleSheets($themeVars)
    {
        $this->_extra_styles = null;
        $this->_style_sheets = null;
        $directory = sprintf('themes/%s/', $this->current_theme);
        @$cookie = PHPWS_Cookie::read('layout_style');

        for ($i = 1; $i < 20; $i++) {
            if (isset($themeVars['style_sheet_' . $i])) {
                $style = &$themeVars['style_sheet_' . $i];
                $style_file = $style['file'];
                $style['file'] = $directory . $style['file'];

                // If the cookie is set, the alternate style sheet then becomes
                // a primary. The primary becomes an alternate.
                if ($cookie && is_file($directory . $cookie)) {
                    if (isset($style['title'])) {
                        if ($cookie == $style_file) {
                            $style['alternate'] = false;
                        } else {
                            $style['alternate'] = true;
                        }
                    }
                }

                $this->_style_sheets[] = $style;
            } else {
                break;
            }
        }
        if (isset($themeVars['extra_styles'])) {
            $this->_extra_styles = &$themeVars['extra_styles'];
        }
    }


    public function loadBoxSettings($themeVars)
    {
        $theme_variables[] = DEFAULT_THEME_VAR;
        $theme_variables[] = DEFAULT_BOX_VAR;

        if (isset($themeVars['theme_variables'])) {
            $theme_variables = array_merge($theme_variables, $themeVars['theme_variables']);
        }

        $this->_theme_variables = $theme_variables;

        // If a user is a deity, they can move the box where ever they want.
        if (!Current_User::isDeity() && isset($themeVars['locked']['ignore'])) {
            $sLocked = str_replace(' ', '', $themeVars['locked']['ignore']);
            $locked = explode(',', $sLocked);

            if (is_array($locked)) {
                foreach ($locked as $ignore) {
                    // add 2 because BODY and DEFAULT take the first two spaces
                    unset($theme_variables[$ignore + 2]);
                }
                $this->_allowed_move = $theme_variables;
            }
        } else {
            $this->_allowed_move = &$this->_theme_variables;
        }
    }

    public function saveSettings()
    {
        $db = new PHPWS_DB('layout_config');
        $vars = PHPWS_Core::stripObjValues($this);
        unset($vars['current_theme']);
        unset($vars['_contentVars']);
        unset($vars['_boxes']);
        unset($vars['_box_order']);
        unset($vars['_move_box']);
        unset($vars['_theme_variables']);
        unset($vars['_default_box']);
        unset($vars['_style_sheets']);
        unset($vars['_extra_styles']);
        unset($vars['_key_styles']);
        unset($vars['_allowed_move']);

        $db->addValue($vars);
        return $db->update();
    }

    public function loadKeyStyle($key_id)
    {
        $db = new PHPWS_DB('layout_styles');
        $db->addWhere('key_id', (int)$key_id);
        $db->addColumn('style');
        $result = $db->select('one');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $this->_key_styles[$key_id] = null;
            return false;
        }

        $this->_key_styles[$key_id] = $result;
        return true;
    }

}

?>