<?php

namespace contact\Factory\ContactInfo;

use contact\Resource\ContactInfo\Social as SocialResource;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Social
{

    public static function getValues(SocialResource $social)
    {
        $values = null;
        $links = $social->getLinks();
        if (!empty($links)) {
            foreach ($links as $l) {
                $values['social_links'][] = array('icon' => $l->getIcon(), 'title' => $l->getTitle(),
                    'url' => $l->getUrl());
            }
        }
        return $values;
    }

    public static function load()
    {
        $social = new SocialResource;
        self::loadLinks($social);
        return $social;
    }

    /**
     * Fills in offsite links to the link variable
     */
    private static function loadLinks(SocialResource $social)
    {
        $new_links = self::createNewLinks($social);
        if (empty($new_links)) {
            throw new \Exception('Problem loading link objects using current social configuration file.');
        }

        $saved_links = self::fillLinks($social);
        if (empty($saved_links)) {
            $social->setLinks($new_links);
        } else {
            exit('need to join saved with new links');
        }
    }

    private static function fillLinks(SocialResource $social)
    {
        $link_array = self::pullSavedLinks();
        if (empty($link_array)) {
            return;
        }
        exit('need to fill in saved information here class/Factory/ContactInfo/Social.php');
        $current_links = $social->getLinks();
    }

    /**
     * Creates empty links based on config/social_links.php array
     * @throws \Exception
     */
    private static function createNewLinks()
    {
        $social_links = null;
        // included file contains a social_links array
        include PHPWS_SOURCE_DIR . 'mod/contact/config/social_links.php';

        if (empty($social_links)) {
            throw new \Exception('Social links are missing from config/social_links.php');
        } else {
            foreach ($social_links as $title => $icon) {
                $link = new SocialResource\Link;
                $link->setTitle($title);
                $link->setIcon($icon);
                $link_array[] = $link;
            }
            return $link_array;
        }
    }

    /**
     * Pulls link arrays from settings
     * @return array
     */
    private static function pullSavedLinks()
    {
        $link_array = \Settings::get('contact', 'links');
        if (!empty($link_array)) {
            $link_array = unserialize($link_array);
        }
        return $link_array;
    }

    public static function getLinksAsJavascriptObject(array $links)
    {
        $social_prefix = null;
        include PHPWS_SOURCE_DIR . 'mod/contact/config/social_links.php';
        $stdObj = new \stdClass();
        foreach ($links as $link) {
            $icon = new \stdClass();
            $icon->url = $link['url'];
            
            if (isset($social_prefix[$link['title']])) {
                $icon->prefix = $social_prefix[$link['title']];
            } else {
                $icon->prefix = 'href://';
            }
            $stdObj->$link['title'] = $icon;
        }
        return json_encode($stdObj);
    }

}
