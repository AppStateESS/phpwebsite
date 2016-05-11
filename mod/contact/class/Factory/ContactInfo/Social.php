<?php

namespace contact\Factory\ContactInfo;

use contact\Resource\ContactInfo\Social as SocialResource;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Social
{

    public static function load()
    {
        $social = new SocialResource;
        self::getLinks($social);
        return $social;
    }

    /**
     * Fills in offsite links to the link variable
     */
    public static function getLinks()
    {
        $new_links = self::getDefaultLinks();
        if (empty($new_links)) {
            throw new \Exception('Problem loading link objects using current social configuration file.');
        }

        $filled_links = self::fillLinks($new_links);
        return $filled_links;
    }

    private static function fillLinks($current_links)
    {
        $link_array = self::pullSavedLinks();
        if (!empty($link_array)) {
            foreach ($link_array as $label => $url) {
                $current_links[$label]['url'] = $url;
            }
        }
        return $current_links;
    }

    /**
     * Creates empty links based on config/social_links.php array
     * @throws \Exception
     */
    private static function getDefaultLinks()
    {
        $social_links = null;
        // included file contains a social_links array
        include PHPWS_SOURCE_DIR . 'mod/contact/config/social_links.php';

        if (empty($social_links)) {
            throw new \Exception('Social links are missing from config/social_links.php');
        } else {
            return $social_links;
        }
    }

    /**
     * Pulls link arrays from settings
     * @return array
     */
    public static function pullSavedLinks()
    {
        $link_array = \Settings::get('contact', 'social');
        if (!empty($link_array)) {
            return unserialize($link_array);
        }
        return $link_array;
    }

    public static function saveLinks(array $links)
    {
        $serial_links = serialize($links);
        \Settings::set('contact', 'social', $serial_links);
    }

    public static function getLinksAsJavascriptObject(array $links)
    {
        $stdObj = new \stdClass();
        foreach ($links as $label => $link) {
            $obj1 = new \stdClass();
            // $link[url] will be blank if nothing has been saved
            if (isset($link['url'])) {
                $obj1->url = $link['url'];
            } else {
                $obj1->url = '';
            }
            $obj1->title = $link['title'];
            $obj1->icon = $link['icon'];
            $obj1->prefix = $link['prefix'];

            $stdObj->$label = $obj1;
        }
        return json_encode($stdObj);
    }

}
