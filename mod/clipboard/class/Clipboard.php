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

    public function action()
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

    public function view()
    {
        Layout::addStyle('clipboard');
        javascript('jquery');
        javascript('modules/clipboard/pick_view');
        $clip = $_SESSION['Clipboard']->components[$_REQUEST['key']]->content;

        $template['TITLE'] = dgettext('clipboard', 'Clipboard');
        $template['CONTENT'] = $clip;
        $template['VIEW_LINK'] = '<a href="#" id="view-link" onclick="return false">View</a>';
        $template['SOURCE_LINK'] = '<a href="#" id="source-link" onclick="return false">Source</a>';

        $button = dgettext('clipboard', 'Close Window');
        $template['BUTTON'] = sprintf('<input type="button" onclick="window.close()" value="%s" />', $button);
        Layout::nakedDisplay(PHPWS_Template::process($template, 'clipboard', 'clipboard.tpl'));
    }


    public function show()
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

        $data['width'] = '340';
        $data['height'] = '410';

        $clipVars['action'] = 'drop';

        foreach ($_SESSION['Clipboard']->components as $key => $component){
            $clipVars['key'] = $key;
            $drop = javascript('modules/clipboard/drop_clip', array('link'=>CLIPBOARD_DROP_LINK,
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

    public function init()
    {
        $_SESSION['Clipboard'] = new Clipboard;
    }

    public function copy($title, $content)
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

    public function clear()
    {
        unset($_SESSION['Clipboard']);
    }

}


class Clipboard_Component {
    public $title;
    public $content;

    public function Clipboard_Component($title, $content){
        $this->title = strip_tags($title);
        $this->content = trim($content);
    }

}

?>