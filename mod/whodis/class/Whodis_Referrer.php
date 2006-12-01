<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at appstate dot edu>
   */

class Whodis_Referrer {
    var $id      = 0;
    var $created = 0;
    var $updated = 0;
    var $url     = null;
    var $visits  = 0;

    function setUrl($url)
    {
        $this->url = htmlentities($url, ENT_QUOTES, 'UTF-8');
    }

    function getUrl()
    {
        if (version_compare(phpversion(), '5.0.0', '>=')) {
            return html_entity_decode($this->url, ENT_QUOTES, 'UTF-8');
        } else {
            return PHPWS_Text::decode_entities($this->url);
        }
    }

    function save($url)
    {
        $this->setUrl($url);
        $db = new PHPWS_DB('whodis');
        $db->addWhere('url', $url);
        $result = $db->select('row');

        $db->reset();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        if ($result) {
            extract($result);
            $this->visits = $visits + 1;
            $this->id = $id;
            $this->created = $created;
        } else {
            $this->visits = 1;
            $this->created = mktime();
        }

        $this->updated = mktime();
        return $db->saveObject($this);
    }

    function getTags()
    {
        $url = $this->getUrl();
        $tags['URL'] = sprintf('<a href="%s" target="_index" title="%s" alt="%s">%s</a>',
                       $url, $url, $url, substr($url, 0, 40));
        $tags['CREATED'] = strftime('%H:%M %Y/%m/%d', $this->created);
        $tags['UPDATED'] = strftime('%H:%M %Y/%m/%d', $this->updated);
        return $tags;
    }
}
?>