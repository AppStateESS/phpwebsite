<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney gmail dot com>
   */

require_once 'Cache/Lite.php';

class PHPWS_Cache {

    function &initCache($lifetime=CACHE_LIFETIME){
        $options = array(
                         'cacheDir' => CACHE_DIRECTORY,
                         'lifeTime' => (int)$lifetime
                         );

        $cache = new Cache_Lite($options);

        return $cache;
    }

    function isEnabled(){
        if (defined('ALLOW_CACHE_LITE'))
            return ALLOW_CACHE_LITE;
        else
            return FALSE;
    }

    function get($key){
        if (!PHPWS_Cache::isEnabled()) {
            return;
        }

        $cache = & PHPWS_Cache::initCache();

        return $cache->get(md5($key));
    }

    function writeIni($switch=0){
        PHPWS_Core::initCoreClass('File.php');
        $info = "cache = $switch\n";
        return PHPWS_File::writeFile(CACHE_DIRECTORY . 'phpws_cache.ini', $info, TRUE);
    }

    function remove($key)
    {
        $cache = & PHPWS_Cache::initCache();
        return $cache->remove(md5($key));
    }

    function clearCache()
    {
        $cache = & PHPWS_Cache::initCache();
        $cache->clean();
    }

    function save($key, $content, $lifetime=CACHE_LIFETIME){
        if (!PHPWS_Cache::isEnabled())
            return;

        if (!is_string($content)) {
            return PHPWS_Error::get(PHPWS_VAR_TYPE, 'core', __CLASS__ . '::' .__FUNCTION__);
        }
        $cache = & PHPWS_Cache::initCache($lifetime);
        return $cache->save($content, md5($key));
    }

}

?>