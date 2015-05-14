<?php
namespace contact\Resource\ContactInfo;

use contact\Resource\ContactInfo\Social\Link;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Social extends \Data
{
    /**
     * Array of offsite links in use
     * @var array
     */
    private $links = array();
    
    public function getLinks()
    {
        return $this->links;
    }
    
    public function setLinks(array $links)
    {
        $this->links = $links;
    }
    
}
