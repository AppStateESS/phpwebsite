<?php
/**
 * Class for individual pages within volumes
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::requireInc('webpage', 'error_defines.php');

class Webpage_Page {
    public $id          = 0;
    // Id of volume page belongs to
    public $volume_id   = 0;
    public $title       = null;
    public $content     = null;
    public $page_number = null;
    public $template    = null;
    public $approved    = 0;
    public $image_id    = 0;
    public $_error      = null;
    public $_volume     = null;
    public $_volume_ver = 0;

    public function __construct($id=null)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('webpage_page');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return;
        }

        if (empty($this->_volume)) {
            $this->loadVolume();
        }

    }

    public function loadVolume()
    {
        $this->_volume = new Webpage_Volume($this->volume_id);
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    /**
     * Returns linked title
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = PHPWS_Text::parseInput($content);
    }


    public function getTemplateDirectory()
    {
        if (FORCE_MOD_TEMPLATES) {
            $directory = PHPWS_SOURCE_DIR . 'mod/webpage/templates/page/';
        } else {
            $directory = 'templates/webpage/page/';
        }
        return $directory;
    }

    public function getContent()
    {
        return PHPWS_Text::parseTag(PHPWS_Text::parseOutput($this->content));
    }

    public function getTemplateList()
    {
        $directory = $this->getTemplateDirectory();
        $files = @scandir($directory);

        if (empty($files)) {
            PHPWS_Error::log(WP_PAGE_DIRECTORY, 'webpage', 'Webpage_Page::getTemplateList', $directory);
            return null;
        }

        foreach ($files as $key => $f_name) {
            if (preg_match('/\.tpl$/i', $f_name)) {
                $file_list[$f_name] = $f_name;
            }
        }
        if (!isset($file_list)) {
            return null;
        } else {
            return $file_list;
        }
    }

    public function checkTemplate()
    {
        $directory = $this->getTemplateDirectory() . $this->template;
        return is_file($directory);
    }


    public function post()
    {
        if (empty($_POST['volume_id'])) {
            return PHPWS_Error::get('WP_MISSING_VOLUME_ID', 'webpage', 'Webpage_Page::post');
        }

        $this->volume_id = (int)$_POST['volume_id'];

        if (empty($_POST['title'])) {
            $this->title = null;
        } else {
            $this->setTitle($_POST['title']);
        }

        if (empty($_POST['content'])) {
            $errors[] = dgettext('webpage', 'Missing page content.');
        } else {
            $this->setContent($_POST['content']);
        }

        if (empty($_POST['template'])) {
            return PHPWS_Error::get(WP_MISSING_TEMPLATE, 'webpage', 'Webpage_Page::post');
        }

        $this->template = strip_tags($_POST['template']);

        if (isset($_POST['page_version_id']) || Current_User::isRestricted('webpage')) {
            $this->approved = 0;
        } else {
            $this->approved = 1;
        }

        if (isset($_POST['image_id'])) {
            $this->image_id = (int)$_POST['image_id'];
        }

        if (isset($errors)) {
            return $errors;
        } else {
            return true;
        }
    }

    public function viewBasic()
    {
        $template['PAGE_TITLE'] = $this->getTitle();
        $template['CONTENT'] = $this->getContent();
        return PHPWS_Template::process($template, 'webpage', 'page/' . $this->template);
    }

    public function getImage()
    {
        if (!$this->image_id) {
            return null;
        } else {
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            return Cabinet::getTag($this->image_id);
        }
    }

    public function getTplTags($admin=false, $include_header=true, $version=0)
    {
        $template['TITLE'] = $this->getTitle();
        $template['IMAGE'] = $this->getImage();
        $template['CONTENT'] = $this->getContent();
        $template['CURRENT_PAGE'] = $this->page_number;

        if ($this->_volume->canEdit()) {
            $vars = array('page_id'   => $this->id,
                          'volume_id' => $this->volume_id);
            if ($version) {
                $vars['version_id'] = $version;
            }

            $vars['wp_admin'] = 'edit_page';

            $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Edit page'), 'webpage', $vars);

            if ($admin) {
                $this->moreAdminLinks($links);
            } else {
                $vars['wp_admin'] = 'edit_webpage';
                $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Sort'), 'webpage', $vars);
            }

            $template['ADMIN_LINKS'] = implode(' | ', $links);
        }

        if (!empty($this->_volume) && $include_header) {
            $header_tags = $this->_volume->getTplTags(!$admin, $version);
            $template = array_merge($template, $header_tags);
        }
        return $template;
    }

    public function moreAdminLinks(&$links)
    {
        if (Current_User::allow('webpage', 'delete_page', null, null, true)) {
            $jsvar['QUESTION'] = dgettext('webpage', 'Are you sure you want to remove this page?');
            $jsvar['ADDRESS'] = sprintf('index.php?module=webpage&amp;wp_admin=delete_page&amp;page_id=%s&amp;volume_id=%s&amp;authkey=%s',
                                        $this->id, $this->volume_id, Current_User::getAuthKey());
            $jsvar['LINK'] = ('Delete');

            $links[] = javascript('confirm', $jsvar);
        }

        if (Current_User::allow('webpage', 'edit_page', null, null, true)) {
            $vars = array('wp_admin'=>'restore_page', 'volume_id'=>$this->volume_id, 'page_id'=>$this->id);
            $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Restore'), 'webpage', $vars);

            if($this->page_number < count($this->_volume->_pages)) {
                $jsvar['QUESTION'] = dgettext('webpage', 'Are you sure you want to join this page to the next?');
                $jsvar['ADDRESS'] = sprintf('index.php?module=webpage&amp;wp_admin=join_page&amp;page_id=%s&amp;volume_id=%s&amp;authkey=%s',
                                            $this->id, $this->volume_id, Current_User::getAuthKey());
                $jsvar['LINK'] = ('Join next');
                $links[] = javascript('confirm', $jsvar);

                $jsvar['QUESTION'] = dgettext('webpage', 'Are you sure you want to join ALL the pages into just one page? Warning: You will lose all page backups!');
                $jsvar['ADDRESS'] = sprintf('index.php?module=webpage&amp;wp_admin=join_all_pages&amp;volume_id=%s&amp;authkey=%s',
                                            $this->volume_id, Current_User::getAuthKey());
                $jsvar['LINK'] = ('Join all');
                $links[] = javascript('confirm', $jsvar);
            }

            $vars['wp_admin'] = 'page_up';
            if ($this->page_number > 1) {
                $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Move up'), 'webpage', $vars);
            } elseif (count($this->_volume->_pages) > 1) {
                $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Move to end'), 'webpage', $vars);
            }

            $total_pages = $this->_volume->getTotalPages();
            $vars['wp_admin'] = 'page_down';
            if ($this->page_number < $total_pages) {
                $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Move down'), 'webpage', $vars);
            } elseif (count($this->_volume->_pages) > 1) {
                $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Move to front'), 'webpage', $vars);
            }
        }
    }

    public function moveUp()
    {
        $db = new PHPWS_DB('webpage_page');
        $db->addWhere('volume_id', $this->volume_id);
        $total_pages = $db->count();

        if ($this->page_number == 1) {
            $db->reduceColumn('page_number');
            $this->page_number = $total_pages;
        } else {
            $db->addWhere('page_number', $this->page_number - 1);
            $db->addValue('page_number', $this->page_number);
            $db->update();
            $this->page_number--;
        }
        $this->save();
    }

    public function moveDown()
    {
        $db = new PHPWS_DB('webpage_page');
        $db->addWhere('volume_id', $this->volume_id);
        $total_pages = $db->count();

        if ($this->page_number == $total_pages) {
            $this->page_number = 1;
            $db->incrementColumn('page_number');
        } else {
            $db->addWhere('page_number', $this->page_number + 1);
            $db->addValue('page_number', $this->page_number);
            $db->update();
            $this->page_number++;
        }
        $this->save();
    }

    public function getPageLink($verbose=false)
    {
        $id = $this->_volume->id;
        $page = (int)$this->page_number;

        if ($verbose) {
            $address = $this->page_number . ' ' . $this->title;
        } else {
            $address = $this->page_number;
        }

        return PHPWS_Text::rewriteLink($address, 'webpage', $id, $page);
    }

    public function getPageUrl()
    {
        if (MOD_REWRITE_ENABLED) {
            return sprintf('webpage/%s/%s', $this->volume_id, $this->page_number);
        } else {
            return sprintf('index.php?module=webpage&amp;id=%s&amp;page=%s', $this->volume_id, $this->page_number);
        }
    }

    public function view($admin=false, $version_id=0)
    {
        $template = $this->getTplTags($admin, true, $version_id);

        if (!is_file($this->getTemplateDirectory() . $this->template)) {
            return implode('<br />', $template);
        }

        $this->_volume->flagKey();

        if ( Current_User::isLogged() &&
             ( Current_User::isUser($this->_volume->create_user_id) ||
               Current_User::allow('webpage', 'edit_page', $this->volume_id, 'volume') ) ) {
            $vars = array('wp_admin'  => 'edit_page',
                          'page_id'   => $this->id,
                          'volume_id' => $this->volume_id);
            if ($version_id) {
                $vars['version_id'] = $version_id;
            }

            $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Edit web page'), 'webpage', $vars);

            $vars['wp_admin'] = 'edit_header';
            $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'Edit page header'), 'webpage', $vars);
            $links[] = PHPWS_Text::secureLink(dgettext('webpage', 'View page list'), 'webpage', array('tab' => 'list'));

            MiniAdmin::add('webpage', $links);
        }

        return PHPWS_Template::process($template, 'webpage', 'page/' . $this->template);
    }

    public function delete()
    {
        $db = new PHPWS_DB('webpage_page');
        $db->addWhere('id', $this->id);

        $result = $db->delete();

        if (PEAR::isError($result)) {
            return $result;
        }

        $db->reset();
        $sql = sprintf('UPDATE webpage_page
                        SET page_number = page_number - 1
                        WHERE volume_id = %s
                        AND page_number > %s',
                       $this->volume_id, $this->page_number);

        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            return $result;
        } else {
            return true;
        }
    }

    public function save()
    {
        PHPWS_Core::initModClass('version', 'Version.php');
        if (empty($this->volume_id)) {
            return PHPWS_Error::get('WP_MISSING_VOLUME_ID', 'webpage', 'Webpage_Page::save');
        }

        if (empty($this->_volume)) {
            $volume = new Webpage_Volume($this->volume_id);
        } else {
            $volume = & $this->_volume;
        }

        if (empty($this->content)) {
            $this->content = dgettext('webpage', 'Page is missing content.');
        }

        if (!$this->checkTemplate()) {
            return PHPWS_Error::get(WP_TPL_FILE_MISSING, 'webpages', 'Webpage_Page::save');
        }

        if (empty($this->page_number)) {
            $this->page_number = count($volume->_pages) + 1;
        }

        if ($this->approved || !$this->id) {
            $db = new PHPWS_DB('webpage_page');
            $result = $db->saveObject($this);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        if ($this->approved) {
            $volume->saveSearch();
        } else {
            $vol_version = new Version('webpage_volume');
            $vol_version->setSource($volume);
            $vol_version->setApproved(false);
            $vol_version->save();
            $page->_volume_ver = $vol_version->id;
        }

        $version = new Version('webpage_page');
        $version->setSource($this);
        $version->setApproved($this->approved);
        return $version->save();
    }

}


?>