<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney gmail dot com>
 */

require_once 'Cache/Lite.php';

class PHPWS_Cache {

    public static function initCache($lifetime=CACHE_LIFETIME)
    {
        $options = array(
                         'cacheDir' => CACHE_DIRECTORY,
                         'lifeTime' => (int)$lifetime
        );
        $cache = new Cache_Lite($options);

        return $cache;
    }

    public static function isEnabled()
    {
        if (defined('ALLOW_CACHE_LITE')) {
            return ALLOW_CACHE_LITE;
        } else {
            return FALSE;
        }
    }

    /**
     * Saves the cache content
     * @param string key      Name of cache key.
     * @param int    lifetime Seconds to retain cache
     * @returns string on success, FALSE otherwise
     */
    public static function get($key, $lifetime=CACHE_LIFETIME)
    {
        if (!PHPWS_Cache::isEnabled()) {
            return;
        }

        $cache = PHPWS_Cache::initCache($lifetime);
        $key .= SITE_HASH . \Settings::get('Global', 'language');
        return $cache->get(md5($key));
    }

    public static function writeIni($switch=0)
    {
        PHPWS_Core::initCoreClass('File.php');
        $info = "cache = $switch\n";
        return PHPWS_File::writeFile(CACHE_DIRECTORY . 'phpws_cache.ini', $info, TRUE);
    }

    public static function remove($key)
    {
        $key .= SITE_HASH . \Settings::get('Global', 'language');
        $cache = PHPWS_Cache::initCache();
        return $cache->remove(md5($key));
    }

    public static function clearCache()
    {
        $cache = PHPWS_Cache::initCache();
        $cache->clean();
    }

    /**
     * Saves the cache content
     * @param string key     Name of cache key.
     * @param string content Content stored in the cache
     * @returns boolean TRUE on success, FALSE otherwise
     */
    public static function save($key, $content)
    {
        $key .= SITE_HASH . \Settings::get('Global', 'language');
        if (!PHPWS_Cache::isEnabled()) {
            return;
        }

        if (!is_string($content)) {
            return PHPWS_Error::get(PHPWS_VAR_TYPE, 'core', __CLASS__ . '::' .__FUNCTION__);
        }
        $cache = PHPWS_Cache::initCache();
        return $cache->save($content, md5($key));
    }

}

?>