<?php

/**
 * Controls layout's settings
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('layout', 'Box.php');

class Layout_Settings {
    var $current_theme    = NULL;
    var $default_theme    = NULL;
    var $userAllow        = 0;
    var $page_title       = NULL;
    var $meta_keywords    = NULL;
    var $meta_description = NULL;
    var $meta_robots      = NULL;
    var $meta_owner       = NULL;
    var $meta_author      = NULL;
    var $meta_content     = NULL;
    var $header           = NULL;
    var $footer           = NULL;
    var $cache            = TRUE;

    // !!! Make sure to update your saveSettings function !!!
    // Remove all hidden variables from the update
    var $_contentVars     = array();
    var $_boxes           = array();
    var $_box_order       = array();
    var $_move_box        = FALSE;
    var $_theme_variables = NULL;
    var $_default_box     = NULL;
    var $_style_sheets    = NULL;
    var $_extra_styles    = NULL;
    var $_key_styles      = NULL;
    var $_allowed_move    = null;

    function Layout_Settings()
    {
        $this->loadSettings();
        $this->loadContentVars();
        $this->loadBoxes();
        $GLOBALS['Layout_Robots'] = $this->meta_robots;
    }

    function getBoxThemeVar($module, $contentVar)
    {
        if (isset($this->_boxes[$module][$contentVar])) {
            return $this->_boxes[$module][$contentVar]->getThemeVar();
        } else {
            return FALSE;
        }
    }

    function getBoxOrder($module, $contentVar)
    {
        if (isset($this->_boxes[$module][$contentVar])) {
            return $this->_boxes[$module][$contentVar]->getBoxOrder();
        } else {
            return FALSE;
        }
    }

    function getPageTitle($only_root=FALSE)
    {
        if (isset($GLOBALS['Layout_Page_Title_Add']) && !$only_root) {
            return implode(PAGE_TITLE_DIVIDER, $GLOBALS['Layout_Page_Title_Add']) . PAGE_TITLE_DIVIDER . $this->page_title;
        } else {
            return $this->page_title;
        }
    }

    function getContentVars()
    {
        return $this->_contentVars();
    }

    function getMetaTags()
    {
        $meta['meta_author']      = $this->meta_author;
        $meta['meta_keywords']    = $this->meta_keywords;
        $meta['meta_description'] = $this->meta_description;
        $meta['meta_owner']       = $this->meta_owner;
        $meta['meta_robots']      = $this->meta_robots;
        $meta['page_title']       = $this->page_title;

        return $meta;
    }

    function getPageMetaTags($key_id)
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

    function getThemeVariables()
    {
        return $this->_theme_variables;
    }

    function getAllowedVariables()
    {
        return $this->_allowed_move;
    }

    function isContentVar($module, $contentVar)
    {
        return in_array($module . '_' . $contentVar, $this->_contentVars);
    }

    function isMoveBox()
    {
        return (bool)$this->_move_box;
    }
  
    function loadBoxes()
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

        $this->_boxes = $final;
    }


    function loadContentVars()
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

    function loadSettings()
    {
        $db = new PHPWS_DB('layout_config');
        $result = $db->loadObject($this, FALSE);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            PHPWS_Core::errorPage();
        }

        if (empty($this->current_theme)) {
            $this->current_theme = $this->default_theme;
        }

        $themeInit = 'themes/' . $this->current_theme . '/theme.ini';

        if (is_file($themeInit)){
            $themeVars = parse_ini_file($themeInit, TRUE);
            $this->loadBoxSettings($themeVars);
            $this->loadStyleSheets($themeVars);
        } else {
            PHPWS_Error::log(LAYOUT_INI_FILE, 'layout', 'Layout_Settings::loadSettings', $themeInit);
            PHPWS_Core::errorPage();
        }
    }

    function loadStyleSheets($themeVars)
    {
        $this->_extra_styles = NULL;
        $this->_style_sheets = NULL;
        $directory = sprintf('themes/%s/', $this->current_theme);
        @$cookie = PHPWS_Cookie::read('layout_style');

        for ($i = 1; $i < 20; $i++) {
            if (isset($themeVars['style_sheet_' . $i])) {
                $style = &$themeVars['style_sheet_' . $i];
                $style['file'] = $directory . $style['file'];

                if ($cookie) {
                    if (isset($style['title'])) {
                        if ($cookie == $style['file']) {
                            $style['alternate'] = FALSE;
                        } else {
                            $style['alternate'] = TRUE;
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


    function loadBoxSettings($themeVars)
    {
        $theme_variables[] = DEFAULT_THEME_VAR;
        $theme_variables[] = DEFAULT_BOX_VAR;

        if (isset($themeVars['theme_variables'])) {
            $theme_variables = array_merge($theme_variables, $themeVars['theme_variables']);
        }

        $this->_theme_variables = $theme_variables;

        if (isset($themeVars['locked']['ignore'])) {
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

    function saveSettings()
    {
        $db = & new PHPWS_DB('layout_config');
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

    function loadKeyStyle($key_id)
    {
        $db = & new PHPWS_DB('layout_styles');
        $db->addWhere('key_id', (int)$key_id);
        $db->addColumn('style');
        $result = $db->select('one');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $this->_key_styles[$key_id] = NULL;
            return FALSE;
        }

        $this->_key_styles[$key_id] = $result;
        return TRUE;
    }

}

?>