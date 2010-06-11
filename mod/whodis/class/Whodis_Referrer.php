<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at appstate dot edu>
 */

class Whodis_Referrer {
    public $id      = 0;
    public $created = 0;
    public $updated = 0;
    public $url     = null;
    public $visits  = 0;

    public function setUrl($url)
    {
        $this->url = htmlentities($url, ENT_QUOTES, 'UTF-8');
    }

    public function getUrl()
    {
        if (version_compare(phpversion(), '5.0.0', '>=')) {
            return html_entity_decode($this->url, ENT_QUOTES, 'UTF-8');
        } else {
            return PHPWS_Text::decode_entities($this->url);
        }
    }

    public function save($url)
    {
        $this->setUrl($url);
        $db = new PHPWS_DB('whodis');
        $db->addWhere('url', $this->url);
        $result = $db->select('row');

        $db->reset();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if ($result) {
            extract($result);
            $this->visits = $visits + 1;
            $this->id = $id;
            $this->created = $created;
        } else {
            $this->visits = 1;
            $this->created = time();
        }

        $this->updated = time();
        return $db->saveObject($this);
    }

    public function getTags()
    {
        $url = $this->getUrl();
        $tags['CHECKBOX'] = sprintf('<input type="checkbox" name="referrer[]" value="%s" />',
        $this->id);
        $tags['URL'] = sprintf('<a href="%s" target="blank" title="%s">%s</a>',
        $url, $url, substr($url, 0, 40));
        $tags['CREATED'] = strftime('%H:%M %Y/%m/%d', $this->created);
        $tags['UPDATED'] = strftime('%H:%M %Y/%m/%d', $this->updated);
        return $tags;
    }
}
?>