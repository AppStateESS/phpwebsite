<?php

/**
 * Stores information to get pasted elsewhere within phpwebsite
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Clipboard
{
    var $components = NULL;

    function action()
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
                PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
            }
            break;

        case 'clear':
            unset($_SESSION['Clipboard']);
            PHPWS_Core::goBack();
            break;
        }

    }

    function view()
    {
        $clip = $_SESSION['Clipboard']->components[$_REQUEST['key']]->content;
        $clip =  sprintf('<textarea cols="35" rows="4">%s</textarea>', $clip);
   
        $template['TITLE'] = dgettext('clipboard', 'Clipboard');
        $template['DIRECTIONS'] = dgettext('clipboard', 'Copy the text below and paste it into the text box.');
        $template['CONTENT'] = $clip;
    
        $button = dgettext('clipboard', 'Close Window');
        $template['BUTTON'] = sprintf('<input type="button" onclick="window.close()" value="%s" />', $button);
        Layout::nakedDisplay(PHPWS_Template::process($template, 'clipboard', 'clipboard.tpl'));
    }


    function show()
    {
        PHPWS_Core::configRequireOnce('clipboard', 'config.php');

        if (!isset($_SESSION['Clipboard'])) {
            Clipboard::init();
        }

        if (empty($_SESSION['Clipboard']->components)) {
            Clipboard::clear();
            return NULL;
        }

        $data['width'] = '280';
        $data['height'] = '150';

        $clipVars['action'] = 'drop';

        foreach ($_SESSION['Clipboard']->components as $key => $component){
            $clipVars['key'] = $key;
            $drop = PHPWS_Text::moduleLink(CLIPBOARD_DROP_LINK, 'clipboard', $clipVars);
            $data['address']     = 'index.php?module=clipboard&action=showclip&key=' . $key;
            $data['label']       = $component->title;
            $data['width']       = 300;
            $data['height']      = 200;
            $data['window_name'] = 'clipboard-' . $component->title;
            $content[] = Layout::getJavascript('open_window', $data) . ' ' . $drop;
        }

        unset($clipVars['key']);
        $clipVars['action'] = 'clear';
        $template['CLEAR'] = PHPWS_Text::moduleLink(dgettext('clipboard', 'Clear'), 'clipboard', $clipVars);
        $template['LINKS'] = implode('<br />', $content);

        $vars['CONTENT'] = PHPWS_Template::process($template, 'clipboard', 'list.tpl');
        $vars['TITLE'] = dgettext('clipboard', 'Clipboard');

        $layout = PHPWS_Template::process($vars, 'clipboard', 'show.tpl');

        Layout::set($layout, 'clipboard', 'clipboard');
    }

    function init()
    {
        $_SESSION['Clipboard'] = new Clipboard;
    }

    function copy($title, $content)
    {
        if (empty($title) || empty($content)) {
            return false;
        }
        if (!isset($_SESSION['Clipboard'])) {
            Clipboard::init();
        }

        $key = md5($title . $content);

        if (!isset($_SESSION['Clipboard']->components[$key])) {
            $_SESSION['Clipboard']->components[$key] = new Clipboard_Component($title, $content);
        }
        Clipboard::show();
    }

    function clear()
    {
        unset($_SESSION['Clipboard']);
    }

}


class Clipboard_Component {
    var $title;
    var $content;

    function Clipboard_Component($title, $content){
        $this->title = strip_tags($title);
        $this->content = htmlspecialchars($content);
    }

}

?>