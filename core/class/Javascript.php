<?php
/**
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

abstract class Javascript {
    private $head_content = null;
    /**
     * If true, head script wrapped with <script> tag
     * @var boolean
     */
    private $wrap_header_script = false;
    
    /**
     * If true, head script wrapped with <script> tag
     * @var boolean
     */
    private $wrap_body_script = false;

    public static function factory($script_name)
    {
        static $script_list = null;

        if (!isset($script_list[$script_name])) {
            $js_path = PHPWS_SOURCE_DIR . 'javascript/' . $script_name . '/factory.php';
            if (!is_file($js_path)) {
                throw new PEAR_Exception(dgettext('core', 'Could not find javascript factory file.'));
            }
            require_once $js_path;
        }
        
        $factory_class_name = 'javascript_' . $script_name; 
        
        $js = new $factory_class_name;
        return $js;
    }
}
?>