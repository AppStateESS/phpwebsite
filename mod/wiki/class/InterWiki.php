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
 * $Id: InterWiki.php,v 1.11 2007/05/28 19:00:16 blindman1344 Exp $
 */

class InterWiki
{
    var $id         = 0;
    var $owner_id   = 0;
    var $editor_id  = 0;
    var $label      = NULL;
    var $created    = 0;
    var $updated    = 0;
    var $url        = NULL;


    function InterWiki($id=NULL)
    {
        if (empty($id))
        {
            return;
        }
        $this->setId($id);

        $db = new PHPWS_DB('wiki_interwiki');
        $db->loadObject($this);
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function setOwnerId($owner_id)
    {
        if (!$this->getId())
        {
            $this->owner_id = (int)$owner_id;
        }
    }

    function getOwnerId()
    {
        return $this->owner_id;
    }

    function setEditorId($editor_id)
    {
        $this->editor_id = (int)$editor_id;
    }

    function getEditorId()
    {
        return $this->editor_id;
    }

    function setLabel($label)
    {
        $this->label = strip_tags($label);
    }

    function getLabel()
    {
        return $this->label;
    }

    function setCreated($created)
    {
        if (!$this->getId())
        {
            $this->created = (int)$created;
        }
    }

    function getCreated($format=WIKI_DATE_FORMAT)
    {
        return strftime($format, PHPWS_Time::getUserTime($this->created));
    }

    function setUpdated($updated)
    {
        $this->updated = (int)$updated;
    }

    function getUpdated($format=WIKI_DATE_FORMAT)
    {
        return strftime($format, PHPWS_Time::getUserTime($this->updated));
    }

    function setUrl($url)
    {
        $this->url = PHPWS_Text::parseInput($url);
    }

    function getUrl()
    {
        return $this->url;
    }

    /**
     * Add interwiki link form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function add()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'wiki');
        $form->addHidden('op', 'addinterwiki');

        $form->addText('label');
        $form->setSize('label', 35, 100);
        $form->setLabel('label', dgettext('wiki', 'Site Name'));

        $form->addText('url');
        $form->setSize('url', 50);
        $form->setLabel('url', dgettext('wiki', 'URL'));

        $form->addSubmit('save', dgettext('wiki', 'Add'));

        $tags = $form->getTemplate();
        $tags['URL_NOTE'] = dgettext('wiki', 'Use %s in the URL string to represent the page name');
        $tags['TOP_LABEL'] = dgettext('wiki', 'Add new interwiki link');

        return $tags;
    }

    /**
     * Edit interwiki link form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function edit()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'wiki');
        $form->addHidden('op', 'saveinterwiki');
        $form->addHidden('id', $this->getId());

        $form->addText('label', $this->getLabel());
        $form->setSize('label', 35, 100);

        $form->addText('url', $this->getUrl());
        $form->setSize('url', 50);

        $form->addSubmit('save', dgettext('wiki', 'Edit'));

        $tags = $form->getTemplate();
        $tags['LABEL_LABEL'] = dgettext('wiki', 'Site Name');
        $tags['URL_LABEL'] = dgettext('wiki', 'URL');
        $tags['URL_NOTE'] = dgettext('wiki', 'Use %s in the URL string to represent the page name');
        $tags['TOP_LABEL'] = dgettext('wiki', 'Edit interwiki link');

        return $tags;
    }

    /**
     * Save
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function save($do_post=TRUE)
    {
        if ($do_post)
        {
            if (empty($_POST['label']))
            {
                return dgettext('wiki', 'Please provide a site name.');
            }
            if (empty($_POST['url']))
            {
                return dgettext('wiki', 'Please provide a URL.');
            }

            $this->setLabel($_POST['label']);
            $this->setUrl($_POST['url']);
        }

        $this->setOwnerId(Current_User::getId());
        $this->setEditorId(Current_User::getId());
        $this->setCreated(mktime());
        $this->setUpdated(mktime());

        $db = new PHPWS_DB('wiki_interwiki');
        $result = $db->saveObject($this);
        if (PEAR::isError($result))
        {
            PHPWS_Error::log($result);
            return dgettext('wiki', 'Error saving link.');
        }

        return dgettext('wiki', 'Link Saved!');
    }

    /**
     * Delete the Interwiki
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function kill()
    {
        if (isset($_REQUEST['yes']))
        {
            $db = new PHPWS_DB('wiki_interwiki');
            $db->addWhere('id', $this->getId());
            $result = $db->delete();
            if (PEAR::isError($result))
            {
                PHPWS_Error::log($result);
                return dgettext('wiki', 'Error deleting interwiki link.');
            }
            return dgettext('wiki', 'Interwiki link deleted!');
        }
        else if (isset($_REQUEST['no']))
        {
            return dgettext('wiki', 'Interwiki link was not deleted!');
        }
        else
        {
            $tags = array();
            $tags['TOP_LABEL'] = dgettext('wiki', 'Are you sure you want to delete this interwiki link?');

            $tags['LABEL_LABEL'] = dgettext('wiki', 'Site Name');
            $tags['URL_LABEL'] = dgettext('wiki', 'URL');
            $tags['LABEL'] = $this->getLabel();
            $tags['URL'] = $this->getUrl();

            $tags['YES'] = PHPWS_Text::secureLink(dgettext('wiki', 'Yes'), 'wiki',
                           array('op'=>'dodeleteinterwiki', 'yes'=>1, 'id'=>$this->getId()));
            $tags['NO'] = PHPWS_Text::secureLink(dgettext('wiki', 'No'), 'wiki',
                          array('op'=>'dodeleteinterwiki', 'no'=>1, 'id'=>$this->getId()));

            return $tags;
        }
    }

    function getTpl()
    {
        $vars['id'] = $this->getId();

        $vars['op'] = 'editinterwiki';
        $links[] = PHPWS_Text::secureLink(dgettext('wiki', 'Edit'), 'wiki', $vars);

        $vars['op'] = 'copyinterwiki';
        $links[] = PHPWS_Text::secureLink(dgettext('wiki', 'Copy'), 'wiki', $vars);

        $vars['op'] = 'deleteinterwiki';
        $links[] = PHPWS_Text::secureLink(dgettext('wiki', 'Delete'), 'wiki', $vars);

        $template['ACTIONS'] = implode(' | ', $links);
        $template['LABEL'] = $this->getLabel();
        $template['URL'] = $this->getUrl();
        $template['UPDATED'] = $this->getUpdated();

        return $template;
    }

    /**
     * Interwiki Setup
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function setup()
    {
        if (!Current_User::authorized('wiki', 'edit_page') &&
            !(PHPWS_Settings::get('wiki', 'allow_page_edit') && Current_User::isLogged()))
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to Interwiki setup.'));
            return;
        }

        Core\Core::initCoreClass('DBPager.php');

        if ($_REQUEST['op'] == 'editinterwiki')
        {
            $tags = $this->edit();
        }
        else if ($_REQUEST['op'] == 'deleteinterwiki')
        {
            $tags = $this->kill();
        }
        else
        {
            $tags = $this->add();
        }

        if (($_REQUEST['op'] == 'addinterwiki') || ($_REQUEST['op'] == 'saveinterwiki'))
        {
            WikiManager::sendMessage($this->save(), 'interwikisetup');
        }
        else if ($_REQUEST['op'] == 'dodeleteinterwiki')
        {
            WikiManager::sendMessage($this->kill(), 'interwikisetup');
        }

        $tags['MESSAGE']         = WikiManager::getMessage();
        $tags['BACK']            = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Wiki'), 'wiki');
        $tags['SITE_LIST_LABEL'] = dgettext('wiki', 'Site list');
        $tags['USAGE']           = sprintf(dgettext('wiki', 'To link to an interwiki site, use %s.'), 'WikiName:PageName');
        $tags['LIST_LABEL']      = dgettext('wiki', 'Site Name');
        $tags['LIST_URL']        = dgettext('wiki', 'URL');
        $tags['LIST_UPDATED']    = dgettext('wiki', 'Updated');
        $tags['LIST_ACTIONS']    = dgettext('wiki', 'Actions');

        $pager = new DBPager('wiki_interwiki', 'InterWiki');
        $pager->setModule('wiki');
        $pager->setTemplate('interwiki/setup.tpl');
        $pager->addToggle(' class="bgcolor1"');
        $pager->addPageTags($tags);
        $pager->addRowTags('getTpl');
        $pager->setSearch('label');
        $pager->setDefaultOrder('label', 'asc');

        $template['TITLE'] = dgettext('wiki', 'Interwiki Setup');
        $template['CONTENT'] = $pager->get();
        Layout::add(PHPWS_Template::process($template, 'wiki', 'box.tpl'), 'wiki', 'wiki_mod', TRUE);
    }
}

?>