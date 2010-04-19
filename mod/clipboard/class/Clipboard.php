<?php

/**
 * Stores information to get pasted elsewhere within phpwebsite
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Clipboard
{
    public $components = NULL;

    public static function action()
    {
        if (!isset($_REQUEST['action']))
        return;

        switch ($_REQUEST['action']){
            case 'showclip':
                Clipboard::view();
                break;

            case 'drop':
                if (isset($_REQUEST['key'])) {
                    unset($_SESSION['Clipboard']->components[$_REQUEST['key']]);
                    exit();
                }
                break;

            case 'clear':
                unset($_SESSION['Clipboard']);
                PHPWS_Core::goBack();
                break;
        }

    }

    public static function view()
    {
        Layout::addStyle('clipboard');
        javascript('jquery');
        $js = array();

        $clip = $_SESSION['Clipboard']->components[$_REQUEST['key']];

        if (!empty($clip->smarttag)) {
            $template['SMART_TAG'] = strip_tags($clip->smarttag);
            $template['SMART_TAG_LINK'] = sprintf('<a href="#" id="smart-link" onclick="return false">%s</a>',
            dgettext('clipboard', 'Smart Tag'));
        }

        javascriptMod('clipboard', 'pick_view', $js);

        $template['TITLE']   = dgettext('clipboard', 'Clipboard');
        $template['CONTENT'] = $clip->content;
        $template['SOURCE']  = htmlentities($clip->source, ENT_QUOTES, 'UTF-8');


        $template['VIEW_LINK'] = sprintf('<a href="#" id="view-link" onclick="return false">%s</a>',
        dgettext('clipboard', 'View'));
        if (!empty($clip->source)) {
            $template['SOURCE_LINK'] = sprintf('<a href="#" id="source-link" onclick="return false">%s</a>',
            dgettext('clipboard', 'Source'));
        }

        $button = dgettext('clipboard', 'Close Window');
        $template['BUTTON'] = sprintf('<input type="button" onclick="window.close()" value="%s" />', $button);
        Layout::nakedDisplay(PHPWS_Template::process($template, 'clipboard', 'clipboard.tpl'));
    }


    public static function show()
    {
        javascript('jquery');
        PHPWS_Core::configRequireOnce('clipboard', 'config.php');

        if (!isset($_SESSION['Clipboard'])) {
            Clipboard::init();
        }

        if (empty($_SESSION['Clipboard']->components)) {
            Clipboard::clear();
            return NULL;
        }

        $data['width'] = '640';
        $data['height'] = '480';
        $data['center'] = false;

        $clipVars['action'] = 'drop';

        foreach ($_SESSION['Clipboard']->components as $key => $component){
            $clipVars['key'] = $key;
            $drop = javascriptMod('clipboard', 'drop_clip', array('link'=>CLIPBOARD_DROP_LINK,
                                                                    'id'=>$key));

            $data['address']     = 'index.php?module=clipboard&action=showclip&key=' . $key;
            $data['label']       = $component->title;
            $data['window_name'] = 'clipboard-' . $component->title;
            $content[] = sprintf('<div id="%s">%s</div>',
                                 'clip-' . $key,
            Layout::getJavascript('open_window', $data) . ' ' . $drop);
        }

        unset($clipVars['key']);
        $clipVars['action'] = 'clear';
        $template['CLEAR'] = PHPWS_Text::moduleLink(dgettext('clipboard', 'Clear'), 'clipboard', $clipVars);
        $template['LINKS'] = implode('', $content);

        $vars['CONTENT'] = PHPWS_Template::process($template, 'clipboard', 'list.tpl');
        $vars['TITLE'] = dgettext('clipboard', 'Clipboard');

        $layout = PHPWS_Template::process($vars, 'clipboard', 'show.tpl');

        Layout::set($layout, 'clipboard', 'clipboard');
    }

    public static function init()
    {
        $_SESSION['Clipboard'] = new Clipboard;
    }

    public static function copy($title, $content=null, $source=null, $smarttag=null, $show_source=true)
    {
        if (empty($title) || empty($content)) {
            return false;
        }

        if (!isset($_SESSION['Clipboard'])) {
            Clipboard::init();
        }

        $key = md5($title . $content);

        if (!isset($_SESSION['Clipboard']->components[$key])) {
            $_SESSION['Clipboard']->components[$key] = new Clipboard_Component($title, $content, $source, $smarttag);
        }
        Clipboard::show();
    }

    public static function clear()
    {
        unset($_SESSION['Clipboard']);
    }

}


class Clipboard_Component {
    public $title       = null;
    public $content     = null;
    public $source      = null;
    public $smarttag    = null;

    public function Clipboard_Component($title, $content=null, $source=null, $smarttag=null)
    {
        if (empty($content) && empty($source) && empty($smarttag)) {
            return;
        }
        $this->title = strip_tags($title);
        $this->content = trim($content);
        $this->smarttag = trim($smarttag);
        if ($source === true) {
            $this->source = trim($content);
        }
    }

}

?>