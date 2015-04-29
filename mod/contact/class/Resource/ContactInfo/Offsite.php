<?php
namespace contact\Resource\ContactInfo;
/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Offsite extends \Data
{
    /**
     * Array of offsite links in use
     * @var array
     */
    private $links = array();
    
    public function __construct()
    {
        $this->loadLinks();
    }
    
    /**
     * Fills in offsite links to the link variable
     */
    public function loadLinks()
    {
        $link_array = $this->pullSavedLinks();
        if (!empty($link_array)) {
            $this->links = & $link_array;
        }
    }
    
    /**
     * Pulls link arrays from settings
     * @return array
     */
    private function pullSavedLinks()
    {
        $link_array = \Settings::get('contact', 'links');
        if (!empty($link_array)) {
            $link_array = unserialize($link_array);
        }
        return $link_array;
    }
    
    public function getLinks()
    {
        return $this->links;
    }
    
}
