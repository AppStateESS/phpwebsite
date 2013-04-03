<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Layout {

    /**
     * Loads a javascript file into memory
     * @param string $directory
     * @param array $data
     * @param string $base
     * @return string|Exception
     */
    public static function getJavascript($directory, array $data = NULL, $base = NULL)
    {
        // previously a choice, now mandated. Leaving this in for backwards
        // compatibility
        if (preg_match('/^modules\//', $directory)) {
            $directory = preg_replace('@^\./@', '', $directory);
            $js_dir = explode('/', $directory);
            foreach ($js_dir as $key => $dir) {
                if ($dir == 'modules') {
                    $start_key = $key + 1;
                    break;
                }
            }
            $js = null;
            $directory = sprintf('mod/%s/javascript/%s', $js_dir[$start_key++], $js_dir[$start_key]);
        } else {
            $js = 'Global/Backward/Javascript/';
        }

        PHPWS_CORE::initCoreClass('File.php');
        $headfile = $js . $directory . '/head.js';
        $bodyfile = $js . $directory . '/body.js';
        $defaultfile = $js . $directory . '/default.php';

        if (is_file($defaultfile)) {
            require $defaultfile;
        }

        if (isset($default)) {
            if (isset($data)) {
                $data = array_merge($default, $data);
            } else {
                $data = $default;
            }
        }

        $data['source_http'] = SHARED_ASSETS;
        $data['source_dir'] = ROOT_DIRECTORY;
        $data['home_http'] = Server::getSiteUrl();
        $data['home_dir'] = SITE_DIRECTORY;

        self::loadJavascriptFile($headfile, $directory, $data);

        if (is_file($bodyfile)) {
            if (!empty($data)) {
                return PHPWS_Template::process($data, 'layout', $bodyfile, TRUE);
            } else {
                return file_get_contents($bodyfile);
            }
        }
    }

    public static function loadJavascriptFile($filename, $index, $data = null)
    {
        if (is_file($filename)) {
            throw new \Exception(t('Missing javascript file: %s', $filename));
        }

        if (isset($data)) {

        }
    }

    public static function add($text, $module = NULL, $content_var = NULL, $default_body = FALSE)
    {
        if (empty($module)) {
            Body::add($text);
        } else {
            $key = $module . $content_var;
            Body::add($text, $key, Body::Sub);
        }
    }

}

?>
