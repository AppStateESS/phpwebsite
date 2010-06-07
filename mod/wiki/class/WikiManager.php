<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * $Id: WikiManager.php,v 1.69 2008/03/29 20:01:55 blindman1344 Exp $
 */

class WikiManager
{
    function sendMessage($message, $command, $secure=TRUE)
    {
        $_SESSION['wiki_message'] = $message;
        if (is_array($command))
        {
            if (MOD_REWRITE_ENABLED && ($secure == FALSE) && isset($command['page']) &&
                !isset($command['page_op']) && !isset($command['op']))
            {
                Core\Core::reroute('wiki/' . $command['page']);
            }
            else
            {
                Core\Core::reroute(PHPWS_Text::linkAddress('wiki', $command, $secure));
            }
        }
        else
        {
            Core\Core::reroute(PHPWS_Text::linkAddress('wiki', array('op'=>$command), $secure));
        }
    }

    function getMessage()
    {
        if (isset($_SESSION['wiki_message']))
        {
            $message = $_SESSION['wiki_message'];
            unset($_SESSION['wiki_message']);
            return $message;
        }

        return NULL;
    }

    /**
     * Transform text using Wiki library
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function transform($wikitext)
    {
        require_once 'Text/Wiki.php';

        if (!defined('WIKI_PARSER'))
        {
            define('WIKI_PARSER', 'Default');
        }

        $wiki =& Text_Wiki::factory(WIKI_PARSER);
        if (PEAR::isError($wiki))
        {
            return sprintf(dgettext('wiki', 'Error! %s parser not found.'), WIKI_PARSER);
        }

        $wikitext = str_replace("&#39;", "'", $wikitext);

        $db = new PHPWS_DB('wiki_pages');
        $db->addColumn('title');
        $pages = $db->select('col');
        if (PEAR::isError($pages))
        {
            PHPWS_Error::log($pages);
            $pages = NULL;
        }

        // Add custom parser rules
        $wiki->addPath('parse', PHPWS_SOURCE_DIR . 'mod/wiki/class/parse/');
        $wiki->insertRule('Template', '');

        $wiki->setRenderConf('xhtml', 'wikilink', 'pages', $pages);
        $wiki->setRenderConf('xhtml', 'wikilink', 'view_url',
                             (MOD_REWRITE_ENABLED ? 'wiki/%s' : './index.php?module=wiki&amp;page=%s'));
        $wiki->setRenderConf('xhtml', 'wikilink', 'new_url',
                             (MOD_REWRITE_ENABLED ? 'wiki/%s' : './index.php?module=wiki&amp;page=%s'));
        $wiki->setRenderConf('xhtml', 'toc', 'title', '<strong>' . dgettext('wiki', 'Table of Contents') . '</strong>');
        $wiki->setRenderConf('xhtml', 'image', 'base', 'images/wiki/');
        $wiki->setRenderConf('xhtml', 'url', 'target', PHPWS_Settings::get('wiki', 'ext_page_target'));
        $wiki->setRenderConf('xhtml', 'interwiki', 'target', PHPWS_Settings::get('wiki', 'ext_page_target'));

        $sites = array();
        $db = new PHPWS_DB('wiki_interwiki');
        $result = $db->select();
        foreach ($result as $row)
        {
            $sites[$row['label']] = $row['url'];
        }
        $wiki->setRenderConf('xhtml', 'interwiki', 'sites', $sites);

        if (PHPWS_Settings::get('wiki', 'ext_chars_support'))
        {
            $wiki->setParseConf('Wikilink', 'ext_chars', true);
        }

        // Setting CSS styles for tags
        $wiki->setRenderConf('xhtml', 'blockquote', 'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'code',       'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'code',       'css_code',     'wiki');
        $wiki->setRenderConf('xhtml', 'code',       'css_php',      'wiki');
        $wiki->setRenderConf('xhtml', 'code',       'css_html',     'wiki');
        $wiki->setRenderConf('xhtml', 'deflist',    'css_dl',       'wiki');
        $wiki->setRenderConf('xhtml', 'deflist',    'css_dt',       'wiki');
        $wiki->setRenderConf('xhtml', 'deflist',    'css_dd',       'wiki');
        $wiki->setRenderConf('xhtml', 'emphasis',   'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'heading',    'css_h1',       'wiki');
        $wiki->setRenderConf('xhtml', 'heading',    'css_h2',       'wiki');
        $wiki->setRenderConf('xhtml', 'heading',    'css_h3',       'wiki');
        $wiki->setRenderConf('xhtml', 'heading',    'css_h4',       'wiki');
        $wiki->setRenderConf('xhtml', 'heading',    'css_h5',       'wiki');
        $wiki->setRenderConf('xhtml', 'heading',    'css_h6',       'wiki');
        $wiki->setRenderConf('xhtml', 'horiz',      'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'image',      'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'interwiki',  'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'list',       'css_ol',       'wiki');
        $wiki->setRenderConf('xhtml', 'list',       'css_ol_li',    'wiki');
        $wiki->setRenderConf('xhtml', 'list',       'css_ul',       'wiki');
        $wiki->setRenderConf('xhtml', 'list',       'css_ul_li',    'wiki');
        $wiki->setRenderConf('xhtml', 'paragraph',  'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'table',      'css_table',    'wiki');
        $wiki->setRenderConf('xhtml', 'table',      'css_tr',       'wiki');
        $wiki->setRenderConf('xhtml', 'table',      'css_th',       'wiki');
        $wiki->setRenderConf('xhtml', 'table',      'css_td',       'wiki');
        $wiki->setRenderConf('xhtml', 'tt',         'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'url',        'css_inline',   'wiki');
        $wiki->setRenderConf('xhtml', 'url',        'css_footnote', 'wiki');
        $wiki->setRenderConf('xhtml', 'url',        'css_descr',    'wiki');
        $wiki->setRenderConf('xhtml', 'url',        'css_img',      'wiki');
        $wiki->setRenderConf('xhtml', 'wikilink',   'css',          'wiki');
        $wiki->setRenderConf('xhtml', 'wikilink',   'css_new',      'wiki');

        if (ALLOW_PROFANITY)
        {
            $wikitext = $wiki->transform($wikitext);
        }
        else
        {
            $wikitext = $wiki->transform(PHPWS_Text::profanityFilter($wikitext));
        }

        if (PHPWS_Settings::get('wiki', 'allow_bbcode'))
        {
            $wikitext = PHPWS_Text::bb2html($wikitext, 'wiki');
        }

        return PHPWS_Text::parseTag($wikitext);
    }// END FUNC transform

    /**
     * Format the wiki title text
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function formatTitle($title)
    {
        if (PHPWS_Settings::get('wiki', 'format_title'))
        {
            $title = trim(ereg_replace("[A-Z]", " \\0", $title));
        }
        return $title;
    }// END FUNC formatTitle

    /**
     * Sends email to Wiki Admin if option enabled
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function sendEmail()
    {
        if(PHPWS_Settings::get('wiki', 'monitor_edits'))
        {
            $pagetitle = WikiManager::formatTitle(strip_tags($_REQUEST['page']));
            $message = PHPWS_Settings::get('wiki', 'email_text');
            $message = str_replace('[page]', $pagetitle, $message);
            $message = str_replace('[url]', Core\Core::getHomeHttp() .
                                   (MOD_REWRITE_ENABLED ? 'wiki/' : 'index.php?module=wiki&page=') .
                                   $_REQUEST['page'], $message);

            Core\Core::initCoreClass('Mail.php');
            $mail = new PHPWS_Mail;
            $mail->addSendTo(PHPWS_Settings::get('wiki', 'admin_email'));
            $mail->setSubject(sprintf(dgettext('wiki', '%s updated!'), $pagetitle));
            $mail->setFrom(PHPWS_User::getUserSetting('site_contact'));
            $mail->setMessageBody($message);

            $mail->send();
        }
    }// END FUNC sendEmail

    /**
     * Image upload
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function imageUpload()
    {
        if (!Current_User::authorized('wiki', 'upload_images') &&
            !(PHPWS_Settings::get('wiki', 'allow_image_upload') && Current_User::isLogged()))
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to image upload.'));
            return;
        }

        Core\Core::initModClass('wiki', 'WikiImage.php');
        Core\Core::initCoreClass('DBPager.php');

        if (isset($_POST['op']) && ($_POST['op'] == 'doimageupload'))
        {
            $newImage = new WikiImage;
            WikiManager::sendMessage($newImage->save(), 'imageupload');
        }

        if ($_REQUEST['op'] == 'doimagedelete')
        {
            $delImage = new WikiImage($_REQUEST['id']);
            WikiManager::sendMessage($delImage->delete(), 'imageupload');
        }

        $tags = WikiImage::add();
        $tags['BACK']               = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Wiki'), 'wiki');
        $tags['MESSAGE']            = WikiManager::getMessage();
        $tags['IMAGE_UPLOAD_LABEL'] = dgettext('wiki', 'Image Upload');
        $tags['IMAGE_LIST_LABEL']   = dgettext('wiki', 'Image List');
        $tags['USAGE']              = sprintf(dgettext('wiki', 'To include an image in a page, use %s.'),
                                              '[[image picture.jpg]]');
        $tags['LIST_FILENAME']      = dgettext('wiki', 'Filename');
        $tags['LIST_SIZE']          = dgettext('wiki', 'Size');
        $tags['LIST_TYPE']          = dgettext('wiki', 'Type');
        $tags['LIST_OWNER']         = dgettext('wiki', 'Uploader');
        $tags['LIST_CREATED']       = dgettext('wiki', 'Upload Date');
        $tags['LIST_ACTIONS']       = dgettext('wiki', 'Actions');

        $pager = new DBPager('wiki_images', 'WikiImage');
        $pager->setModule('wiki');
        $pager->setTemplate('images/admin.tpl');
        $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
        $pager->addPageTags($tags);
        $pager->addRowTags('getTpl');
        $pager->setSearch('filename', 'summary');
        $pager->setDefaultOrder('filename', 'asc');
        $pager->setEmptyMessage(dgettext('wiki', 'No images found.'));

        $template['TITLE'] = dgettext('wiki', 'Wiki Images');
        $template['CONTENT'] = $pager->get();
        Layout::add(PHPWS_Template::process($template, 'wiki', 'box.tpl'), 'wiki', 'wiki_mod', TRUE);
    }// END FUNC imageUpload

    /**
     * Recent Changes
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function recentChanges()
    {
        Core\Core::initModClass('wiki', 'OldWikiPage.php');
        Core\Core::initCoreClass('DBPager.php');

        $tags['BACK']     = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Wiki'), 'wiki');
        $tags['PAGE']     = dgettext('wiki', 'Page Name');
        $tags['UPDATED']  = dgettext('wiki', 'Updated');
        $tags['EDITOR']   = dgettext('wiki', 'Editor');
        $tags['COMMENT']  = dgettext('wiki', 'Comment');
        $tags['VIEW']     = dgettext('wiki', 'View');

        $pager = new DBPager('wiki_pages_version', 'OldWikiPage');
        $pager->setModule('wiki');
        $pager->setTemplate('recentchanges/list.tpl');
        $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
        $pager->addPageTags($tags);
        $pager->addRowTags('getRecentChangesTpl');
        $pager->setSearch('pagetext', 'comment');
        $pager->setDefaultOrder('id', 'desc');
        $pager->setLimitList(array(10,25,50,75,100));

        return $pager->get();
    }// END FUNC recentChanges

    /**
     * Gets random page from the database
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function random()
    {
        $db = new PHPWS_DB('wiki_pages');
        $db->addOrder('random');
        $db->setLimit(1);
        $db->addColumn('title');
        $result = $db->select('col');

        if (!PHPWS_Error::logIfError($result) && ($result != NULL))
        {
            Core\Core::reroute(PHPWS_Text::linkAddress('wiki', array('page'=>$result[0])));
        }
        Core\Core::reroute(PHPWS_Text::linkAddress('wiki'));
    }// END FUNC random

    /**
     * Adds wiki links to the MiniAdmin box
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function addToMiniAdmin()
    {
        $admin = dgettext('wiki', 'Admin');
        $image = dgettext('wiki', 'Image upload');
        $linkshere = dgettext('wiki', 'What links here');
        $recentchanges = dgettext('wiki', 'Recent changes');
        $randompage = dgettext('wiki', 'Random page');
        $interwiki = dgettext('wiki', 'Interwiki setup');

        if (isset($_REQUEST['page']) && isset($_REQUEST['page_op']) &&
            ($_REQUEST['page_op']=='view') && (PHPWS_Settings::get('wiki', 'what_links_here')))
        {
            $links[] = PHPWS_Text::moduleLink($linkshere, 'wiki',
                                              array('page'=>$_REQUEST['page'], 'page_op'=>'whatlinkshere'));
        }

        if (PHPWS_Settings::get('wiki', 'recent_changes'))
        {
            $links[] = PHPWS_Text::moduleLink($recentchanges, 'wiki', array('op'=>'recentchanges'));
        }

        if (PHPWS_Settings::get('wiki', 'random_page'))
        {
            $links[] = PHPWS_Text::moduleLink($randompage, 'wiki', array('op'=>'random'));
        }

        if ((PHPWS_Settings::get('wiki', 'allow_image_upload') && Current_User::isLogged()) ||
            Current_User::allow('wiki', 'upload_images'))
        {
            $links[] = PHPWS_Text::secureLink($image, 'wiki', array('op'=>'imageupload'));
        }

        if (Current_User::allow('wiki', 'edit_page') ||
            (PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged()))
        {
            $links[] = PHPWS_Text::secureLink($interwiki, 'wiki', array('op'=>'interwikisetup'));
        }

        if (Current_User::allow('wiki', 'edit_settings'))
        {
            $links[] = PHPWS_Text::secureLink($admin, 'wiki', array('op'=>'admin'));
        }

        if (isset($links))
        {
            /* Clear out any existing links that may be present before adding these */
            $GLOBALS['MiniAdmin']['wiki'] = NULL;
            MiniAdmin::add('wiki', $links);
        }
    }// END FUNC addToMiniAdmin

    /**
     * Action
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function action()
    {
        Layout::addStyle('wiki');

        if (!PHPWS_Settings::get('wiki', 'allow_anon_view') && !Current_User::isLogged())
        {
            Current_User::requireLogin();
            return;
        }

        if (isset($_REQUEST['page_id']) && is_numeric($_REQUEST['page_id']))
        {
            Core\Core::initModClass('wiki', 'WikiPage.php');
            $wikipage = new WikiPage($_REQUEST['page_id']);
        }
        else if (isset($_REQUEST['page']) && is_string($_REQUEST['page']))
        {
            Core\Core::initModClass('wiki', 'WikiPage.php');
            $wikipage = new WikiPage($_REQUEST['page']);
        }

        if(!isset($_REQUEST['op']) && !isset($_REQUEST['page_op']))
        {
            // phpWebSite 1.5.0 and later mod_rewrite method
            if (isset($_GET['var1']))
            {
                $_REQUEST['id'] = $_GET['id'] = $_GET['var1'];
            }

            if (isset($_REQUEST['id']) && is_string($_REQUEST['id']))
            {
                Core\Core::initModClass('wiki', 'WikiPage.php');
                $wikipage = new WikiPage($_REQUEST['id']);
            }

            $_REQUEST['page_op'] = 'view';
        }

        WikiManager::addToMiniAdmin();

        if (isset($_REQUEST['page_op']) && isset($wikipage))
        {
            $wikipage->action();
            return;
        }

        switch(@$_REQUEST['op'])
        {
            case 'admin':
            case 'savesettings':
                Core\Core::initModClass('wiki', 'WikiSettings.php');
                WikiSettings::admin();
                break;

            case 'doimagedelete':
            case 'doimageupload':
            case 'imageupload':
                WikiManager::imageUpload();
                break;

            case 'imagedelete':
                Core\Core::initModClass('wiki', 'WikiImage.php');
                $delImage = new WikiImage($_REQUEST['id']);
                $template['TITLE'] = dgettext('wiki', 'Wiki Images');
                $template['CONTENT'] = $delImage->delete();
                Layout::add(PHPWS_Template::process($template, 'wiki', 'box.tpl'), 'wiki', 'wiki_mod', TRUE);
                break;

            case 'imagecopy':
                Core\Core::initModClass('wiki', 'WikiImage.php');
                $image = new WikiImage($_REQUEST['id']);
                Clipboard::copy($image->getFilename(), $image->getTag());
                Core\Core::goBack();
                break;

            case 'recentchanges':
                $template['TITLE'] = dgettext('wiki', 'Recent changes');
                $template['CONTENT'] = WikiManager::recentChanges();
                Layout::add(PHPWS_Template::process($template, 'wiki', 'box.tpl'), 'wiki', 'wiki_mod', TRUE);
                break;

            case 'random':
                WikiManager::random();
                break;

            case 'interwikisetup':
            case 'addinterwiki':
                Core\Core::initModClass('wiki', 'InterWiki.php');
                $interwiki = new InterWiki;
                $interwiki->setup();
                break;

            case 'editinterwiki':
            case 'saveinterwiki':
            case 'deleteinterwiki':
            case 'dodeleteinterwiki':
                Core\Core::initModClass('wiki', 'InterWiki.php');
                $interwiki = new InterWiki($_REQUEST['id']);
                $interwiki->setup();
                break;

            case 'copyinterwiki':
                Core\Core::initModClass('wiki', 'InterWiki.php');
                $interwiki = new InterWiki($_REQUEST['id']);
                Clipboard::copy($interwiki->getLabel(), $interwiki->getLabel() . ':PageName');
                Core\Core::goBack();
                break;

            default:
                $_REQUEST['page'] = PHPWS_Settings::get('wiki', 'default_page');
                WikiManager::action();
        }
    }// END FUNC action
}

?>