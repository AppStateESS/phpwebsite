<?php

  /**
   * Search object
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Search {
    var $key_id   = 0;
    var $module   = NULL;
    var $keywords = NULL;
    var $created  = 0;
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
        $db = new PHPWS_DB('search');
        $db->addWhere('key_id', $this->key_id);
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }

        $this->loadKeywords();
    }

    function loadKeywords()
    {
        if (!empty($this->keywords)) {
            $words = explode(' ', trim($this->keywords));
            $this->keywords = &$words;
        }

    }

    function setKey($key)
    {
        if ( (strtolower(get_class($key)) == 'key') && $key->id > 0) {
            $this->key_id = (int)$key->id;
        } elseif (is_numeric($key)) {
            $this->key_id = (int)$key;
        }
    }

    function resetKeywords()
    {
        $this->keywords = null;
    }

    function addKeywords($keywords, $parse_keywords=true)
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
            $parse_text = $this->filterWords($keywords, false);
        }            

        if (empty($parse_text)) {
            return;
        }
        // removes extra spaces
        $parse_text = preg_replace('/\s{2,}/', ' ', $parse_text);

        $keyword_list = explode(' ', trim($parse_text));

        if (empty($keyword_list)) {
            return FALSE;
        }

        $current_keywords = $this->keywords;

        if (is_array($current_keywords) && !empty($current_keywords)) {
            $this->keywords = array_merge($current_keywords, $keyword_list);
        } else {
            $this->keywords = $keyword_list;
        }
        $this->keywords = array_unique($this->keywords);
    }

    function filterWords($text, $encode=true)
    {
        // can't use strip_tags because we need the spaces
        $text = preg_replace('/(<|&lt;).*(>|&gt;)/sUi', ' ', $text);
        $text = preg_replace('/[^\w\-\s;&]/', '', $text);
        $text = str_replace(' - ', ' ', $text);
        if ($encode) {
            $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        }
        $text = strtolower($text);
        $text = preg_replace('/(-{2,}|\/)/U', ' ', $text);
        return $text;
    }

    /**
     * Filters text and clears common words from the inputed text
     */
    function parseKeywords($text)
    {
        if (empty($text)) {
            return;
        }

        $text = $this->filterWords($text, false);

        $file_name = translateFile('wordlist.txt');

        // Removes trademark/registered, contractions, and website suffix
        $text = preg_replace('/\d|(n\'t|\'([sd]|ll|re|ve))|\.(com|edu|net|org)|\(tm\)|\(r\)/', '', $text);

        $config_file = PHPWS_Core::getConfigFile('search', $file_name);
        if (!$config_file) {
            $config_file = PHPWS_Core::getConfigFile('search', 'wordlist.txt');
            if (!$config_file) {
                return $text;
            }
        }

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

    function removeKeyword($keyword)
    {
        $key = array_search($keyword, $this->keywords);

        if ($key !== FALSE) {
            unset($this->keywords[$key]);
        }
    }

    function save()
    {
        if (empty($this->key_id) || empty($this->keywords)) {
            return FALSE;
        }
        $db = new PHPWS_DB('search');
        $db->addWhere('key_id', $this->key_id);
        $db->delete();
        $db->reset();

        $key = new Key($this->key_id);

        $db->addValue('key_id', $key->id);
        $db->addValue('module', $key->module);
        $db->addValue('created', $key->create_date);
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