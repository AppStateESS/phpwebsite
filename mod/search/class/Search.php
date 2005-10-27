<?php

  /**
   * Search object
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Search {
    var $key_id   = 0;
    var $keywords = NULL;
    var $_error   = NULL;
    
    function Search($key=NULL)
    {
        if (empty($key)) {
            return;
        }

        $this->setKey($key);
        $this->init();
    }

    function init()
    {
        $db = & new PHPWS_DB('search');
        $db->addWhere('key_id', $this->key_id);
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }

        $this->loadKeywords();
    }

    function loadKeywords()
    {
        $words = explode(' ', trim($this->keywords));
        $this->keywords = &$words;
    }

    function setKey($key)
    {
        if ( (strtolower(get_class($key)) == 'key') && $key->id > 0) {
            $this->key_id = (int)$key->id;
        } elseif (is_numeric($key)) {
            $this->key_id = (int)$key;
        }
    }

    function addKeywords($keywords, $parse_keywords=TRUE)
    {
        if ( !is_array($keywords) && !is_string($keywords) ) {
            return FALSE;
        }

        if ( is_array($keywords) ) {
            $keywords = implode(' ', $keywords);
        }
        
        if ($parse_keywords) {
            $parse_text = $this->parseKeywords($keywords);
        } else {
            $parse_text = $this->filterWords($keywords);
        }            

        // removes extra spaces
        $parse_text = preg_replace('/\s{2,}/', ' ', $parse_text);

        $keyword_list = explode(' ', $parse_text);
        if (empty($keyword_list)) {
            return FALSE;
        }
        
        $current_keywords = $this->keywords;
        if (is_array($current_keywords)) {
            $this->keywords = array_merge($current_keywords, $keyword_list);
        } else {
            $this->keywords = $keyword_list;
        }
        $this->keywords = array_unique($this->keywords);
    }

    function filterWords($text)
    {
        // can't use strip_tags because we need the spaces
        $text = preg_replace('/(<|&lt;).*(>|&gt;)/sUi', ' ', $text);

        $text = strtolower($text);
        $text = preg_replace('/[^\w\-\s]/', '', $text);
        $text = preg_replace('/(-{2,}|\/)/U', ' ', $text);

        return $text;
    }

    function parseKeywords($text)
    {
        if (empty($text)) {
            return;
        }
        
        $text = $this->filterWords($text);

        // temporary. this will probably be from the database
        $file_name = 'wordlist.en.txt';

        // Removes trademark/registered, contractions, and website suffix
        $text = preg_replace('/\d|(n\'t|\'([sd]|ll|re|ve))|\.(com|edu|net|org)|\(tm\)|\(r\)/', '', $text);

        $config_file = PHPWS_Core::getConfigFile('search', $file_name);
        $common_words = file($config_file);
        foreach ($common_words as $word) {
            $word = trim($word);
            // This line below does the majority of the work
            $text = preg_replace("/^$word\s|\s$word\s|\s$word$/", ' ', $text);

            // This line removes repeats AND English language suffixes.
            $text = preg_replace("/ $word(es|s|ing|ed|d|ly|ings|ful|er|est)? /", ' ', $text);
        }

        return $text;
    }


    function save()
    {
        if (empty($this->key_id) || empty($this->keywords)) {
            return FALSE;
        }
        $db = & new PHPWS_DB('search');
        $db->addWhere('key_id', $this->key_id);
        $db->delete();
        $db->reset();

        $db->addValue('key_id', $this->key_id);
        if (is_array($this->keywords)) {
            $keywords = implode(' ', $this->keywords);
        } else {
            $keywords = $this->keywords;
        }
        $db->addValue('keywords', $keywords);
        return $db->insert();
    }

}

?>