<?php

/**
 * Controls the general user functionality of the module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::requireInc('webpage', 'error_defines.php');
core\Core::initModClass('webpage', 'Volume.php');

class Webpage_User {
    public function main($command=NULL)
    {
        if (empty($command)) {
            if (isset($_REQUEST['wp_user'])) {
                $command = $_REQUEST['wp_user'];
            } else {
                \core\Core::errorPage(404);
                exit();
            }
        }

        switch ($command) {
            case 'view':
                if (!isset($_REQUEST['id'])) {
                    \core\Core::errorPage(404);
                    exit();
                }

                $volume = new Webpage_Volume($_GET['id']);
                $volume->loadKey();
                if (!$volume->_key->allowView()) {
                    Current_User::requireLogin();
                }
                @$page = $_GET['page'];
                Layout::add($volume->view($page));
                \core\Core::initModClass('menu', 'Menu.php');
                break;

            default:
                \core\Core::errorPage('404');
                break;
        }

    }

    public static function showFeatured()
    {
        if (isset($_REQUEST['module'])) {
            return NULL;
        }

        $db = new \core\DB('webpage_featured');
        $db->addColumn('webpage_volume.*');
        $db->addWhere('webpage_featured.id', 'webpage_volume.id');
        $db->addOrder('webpage_featured.vol_order');
        $result = $db->getObjects('webpage_volume');
        if (empty($result)) {
            return null;
        } elseif (core\Error::isError($result)) {
            \core\Error::log($result);
        } else {
            foreach ($result as $volume) {
                $key = new \core\Key($volume->key_id);
                if (!$key->allowView()) {
                    continue;
                }
                $tpl['TITLE'] = $volume->getTitle();
                $tpl['SUMMARY'] = $volume->getSummary();

                if (Current_User::allow('webpage', 'featured') && Current_User::isUnrestricted('users')) {
                    $vars['volume_id'] = $volume->id;
                    $vars['wp_admin'] = 'drop_feature';
                    $links[1] = \core\Text::secureLink(dgettext('webpage', 'Drop'), 'webpage', $vars);

                    $vars['wp_admin'] = 'up_feature';
                    $links[2] = \core\Text::secureLink(dgettext('webpage', 'Up'), 'webpage', $vars);

                    $vars['wp_admin'] = 'down_feature';
                    $links[3] = \core\Text::secureLink(dgettext('webpage', 'Down'), 'webpage', $vars);

                    $tpl['LINKS'] = implode(' | ', $links);
                }
                $template['volume'][] = $tpl;
            }
        }
        $template['FEATURED_TITLE'] = dgettext('webpage', 'Featured pages');

        $content = \core\Template::process($template, 'webpage', 'featured.tpl');
        Layout::add($content, 'webpage', 'featured');
    }

    public static function showFrontPage()
    {
        if (isset($_REQUEST['module'])) {
            return NULL;
        }

        \core\Core::initModClass('webpage', 'Volume.php');

        $db = new \core\DB('webpage_volume');
        $db->addWhere('frontpage', 1);
        $db->addWhere('approved', 1);
        \core\Key::restrictView($db, 'webpage');
        $result = $db->getObjects('Webpage_Volume');

        if (core\Error::isError($result)) {
            \core\Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            return NULL;
        }

        foreach ($result as $volume) {
            $volume->loadPages();
            Layout::add($volume->view(null, false), 'webpage', 'page_view', TRUE);
        }
    }
}


?>