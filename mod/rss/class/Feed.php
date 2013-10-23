<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::initCoreClass('XMLParser.php');

PHPWS_Core::requireConfig('rss');

class RSS_Feed {

    public $id = 0;
    public $title = NULL;
    public $address = NULL;
    public $display = 1;
    public $item_limit = RSS_FEED_LIMIT;
    public $refresh_time = RSS_FEED_REFRESH;
    public $_error = NULL;
    public $_parser = NULL;
    public $mapped = NULL;

    public function RSS_Feed($id = 0)
    {
        $this->id = $id;

        if (empty($this->id)) {
            return;
        }

        $this->init();
    }

    public function init()
    {
        if (empty($this->id)) {
            return FALSE;
        }
        $db = new PHPWS_DB('rss_feeds');
        return $db->loadObject($this);
    }

    public function setAddress($address)
    {
        $this->address = trim($address);
    }

    public function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    public function loadTitle()
    {
        $this->title = $this->mapped['CHANNEL']['TITLE'];
    }

    public function pagerTags()
    {
        $vars['command'] = 'reset_feed';
        $vars['feed_id'] = $this->id;
        $links[] = PHPWS_Text::secureLink('<i class="fa fa-refresh" title="' . dgettext('rss',
                                'Reset') . '"></i>', 'rss', $vars);

        $jsvars['address'] = sprintf('index.php?module=rss&command=edit_feed&feed_id=%s&authkey=%s',
                $this->id, Current_User::getAuthKey());
        $jsvars['label'] = '<i class="fa fa-edit" title="' . dgettext('rss',
                                'Edit the feed') . '"></i>';
        $jsvars['height'] = '280';
        $links[] = javascript('open_window', $jsvars);

        $js['QUESTION'] = dgettext('rss',
                'Are you sure you want to delete this RSS feed?');
        $js['ADDRESS'] = sprintf('index.php?module=rss&command=delete_feed&feed_id=%s&authkey=%s',
                $this->id, Current_User::getAuthKey());
        $js['LINK'] = '<i class="fa fa-trash-o" title="' . dgettext('rss', 'Delete feed') . '"></i>';
        $links[] = javascript('confirm', $js);

        $tpl['ACTION'] = implode(' ', $links);

        if ($this->display) {
            $vars['command'] = 'turn_off_display';
            $tpl['DISPLAY'] = PHPWS_Text::secureLink(dgettext('rss', 'Yes'),
                            'rss', $vars);
        } else {
            $vars['command'] = 'turn_on_display';
            $tpl['DISPLAY'] = PHPWS_Text::secureLink(dgettext('rss', 'No'),
                            'rss', $vars);
        }

        $hours = floor($this->refresh_time / 3600);

        $remaining = $this->refresh_time - ($hours * 3600);

        $minutes = floor($remaining / 60);

        $seconds = $remaining - $minutes * 60;

        $time = NULL;

        if ($seconds) {
            $time = sprintf(dgettext('rss', '%d seconds'), $seconds);
        }

        if ($minutes) {
            if (isset($time)) {
                $time = sprintf(dgettext('rss', '%d minutes, '), $minutes) . $time;
            } else {
                $time = sprintf(dgettext('rss', '%d minutes'), $minutes) . $time;
            }
        }

        if ($hours) {
            if (isset($time)) {
                $time = sprintf(dgettext('rss', '%d hours, '), $hours) . $time;
            } else {
                $time = sprintf(dgettext('rss', '%d hours'), $hours) . $time;
            }
        }

        $refresh_time = sprintf(dgettext('rss', 'Every %s'), $time);

        $tpl['ADDRESS'] = sprintf('<a href="%s">%s</a>', $this->address,
                PHPWS_Text::shortenUrl($this->address));
        $tpl['REFRESH_TIME'] = $refresh_time;

        return $tpl;
    }

    public function loadParser($use_cache = TRUE)
    {
        if (empty($this->address)) {
            return FALSE;
        }

        if ($use_cache) {
            $cache_key = $this->address;
            $data = PHPWS_Cache::get($cache_key, $this->refresh_time);
        }

        if (!empty($data)) {
            $this->mapped = unserialize($data);
            return TRUE;
        } else {
            if (isset($this->_parser) && empty($this->_parser->error)) {
                return TRUE;
            }

            $this->_parser = new XMLParser($this->address);
            if ($this->_parser->error) {
                PHPWS_Error::log($this->_parser->error);
                return FALSE;
            }

            $this->mapData();
            if ($use_cache) {
                PHPWS_Cache::save($cache_key, serialize($this->mapped));
            }
        }
        return TRUE;
    }

    /**
     * Resets the cache on the RSS feed
     */
    public function reset()
    {
        $cache_key = $this->address;
        PHPWS_Cache::remove($cache_key);
    }

    public function post()
    {
        if (!empty($_POST['title'])) {
            $this->setTitle($_POST['title']);
        } else {
            $this->title = NULL;
        }

        if (!isset($_POST['address'])) {
            $error[] = dgettext('rss', 'You must enter an address.');
        } else {
            $address = trim($_POST['address']);
            if (!preg_match('|^https?://|', $address)) {
                $error[] = dgettext('rss',
                        'RSS import needs to be an offsite link.');
            } else {
                $this->setAddress($address);
            }
        }

        if (!$this->loadParser(FALSE)) {
            $error[] = dgettext('rss', 'Invalid feed address.');
        }

        $item_limit = (int) $_POST['item_limit'];

        if (empty($item_limit)) {
            $this->item_limit = RSS_FEED_LIMIT;
        } elseif ($item_limit > RSS_MAX_FEED) {
            $error[] = sprintf(dgettext('rss',
                            'You may not pull more than %s feeds.'),
                    RSS_MAX_FEED);
            $this->item_limit = RSS_FEED_LIMIT;
        } else {
            $this->item_limit = $item_limit;
        }


        $refresh_time = (int) $_POST['refresh_time'];

        if ($refresh_time < 60) {
            $error[] = dgettext('rss',
                    'Refresh time is too low. It must be over 60 seconds.');
            $this->refresh_time = RSS_FEED_REFRESH;
        } elseif ($refresh_time > 2592000) {
            $error[] = dgettext('rss',
                    'You should refresh more often than every month.');
            $this->refresh_time = RSS_FEED_REFRESH;
        } else {
            $this->refresh_time = &$refresh_time;
        }

        if (isset($error)) {
            return $error;
        } else {
            return TRUE;
        }
    }

    public function save()
    {
        if (empty($this->title)) {
            $this->loadTitle();
        }

        $db = new PHPWS_DB('rss_feeds');
        return $db->saveObject($this);
    }

    public function delete()
    {
        $db = new PHPWS_DB('rss_feeds');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    /**
     * Displays the feed
     */
    public function view()
    {
        if (!$this->loadParser()) {
            $tpl['MESSAGE'] = dgettext('rss', 'Sorry, unable to grab feed.');
        } else {
            if (isset($this->mapped['ITEMS'])) {
                $count = 0;
                foreach ($this->mapped['ITEMS'] as $item_data) {
                    if ($count >= $this->item_limit) {
                        break;
                    }
                    if (strlen($item_data['DESCRIPTION']) > RSS_SHORT_DESC_SIZE) {
                        $item_data['SHORT_DESCRIPTION'] = substr($item_data['DESCRIPTION'],
                                        0, RSS_SHORT_DESC_SIZE) . '...';
                    } else {
                        $item_data['SHORT_DESCRIPTION'] = &$item_data['DESCRIPTION'];
                    }

                    $tpl['item_list'][] = $item_data;
                    $count++;
                }
            } else {
                $tpl['MESSAGE'] = dgettext('rss', 'Unable to list feed.');
            }
        }
        $tpl['FEED_LINK'] = &$this->mapped['CHANNEL']['LINK'];

        if (isset($this->mapped['IMAGE'])) {
            $image = & $this->mapped['IMAGE'];

            if (isset($image['LINK'])) {
                $tpl['IMAGE'] = sprintf('<a href="%s"><img src="%s" title="%s" border="0" /></a>',
                        $image['LINK'], $image['URL'], $image['TITLE']);
            } else {
                $tpl['IMAGE'] = sprintf('<img src="%s" title="%s" border="0" />',
                        $image['URL'], $image['TITLE']);
            }
        } else {
            $tpl['FEED_TITLE'] = &$this->title;
        }

        $content = PHPWS_Template::process($tpl, 'rss', 'feeds/view_rss.tpl');

        return $content;
    }

    public function pullChannel($data, $version)
    {
        foreach ($data as $info) {
            extract($info);

            switch ($name) {
                case 'ITEM':
                    $this->addItem($info['child']);
                    break;

                case 'ITEMS':
                    if ($version == '1.0') {
                        $items = &$child[0]['child'];
                        if (empty($items)) {
                            continue;
                        }
                        foreach ($items as $item) {
                            list(, $resource) = each($item['attributes']);
                            $this->mapped['CHANNEL']['ITEM_RESOURCES'][] = $resource;
                        }
                    } elseif ($version == '2.0' || $version == '0.92') {
                        $this->addItem($info['child']);
                    }
                    break;

                case 'IMAGE':
                    if ($version == '1.0' && isset($item['attributes']) && is_array($item['attributes'])) {
                        foreach ($item['attributes'] as $ignore => $resource)
                            ;
                        $this->mapped['CHANNEL']['IMAGE'] = $resource;
                    } elseif ($version == '2.0' || $version == '0.92') {
                        $this->pullImage($info['child']);
                    }
                    break;

                case 'TEXTINPUT':
                    if (isset($item['attributes']) && is_array($item['attributes'])) {
                        foreach ($item['attributes'] as $ignore => $resource)
                            ;
                        $this->mapped['CHANNEL']['TEXTINPUT'] = $resource;
                    }
                    break;

                default:
                    $this->mapped['CHANNEL'][$name] = $content;
            }
        }
    }

    public function pullImage($data)
    {
        foreach ($data as $info) {
            extract($info);
            $this->mapped['IMAGE'][$name] = $content;
        }
    }

    public function addItem($data)
    {
        foreach ($data as $info) {
            extract($info);
            $item[$name] = $content;
        }
        $this->mapped['ITEMS'][] = $item;
    }

    public function pullTextInput($data)
    {
        foreach ($data as $info) {
            extract($info);
            $this->mapped['TEXT_INPUT'][$name] = $content;
        }
    }

    public function mapData()
    {
        if (isset($this->_parser->data[0]['attributes']['VERSION'])) {
            $version = &$this->_parser->data[0]['attributes']['VERSION'];
        } else {
            $version = '1.0';
        }

        $section = &$this->_parser->data[0]['child'];

        foreach ($section as $sec_key => $sec_value) {
            switch ($sec_value['name']) {

                case 'CHANNEL':
                    $this->pullChannel($sec_value['child'], $version);
                    break;

                case 'IMAGE':
                    $this->pullImage($sec_value['child']);
                    break;

                case 'ITEM':
                    $this->addItem($sec_value['child']);
                    break;

                case 'TEXTINPUT':
                    $this->pullTextInput($sec_value['child']);
                    break;
            }
        }
    }

}

?>