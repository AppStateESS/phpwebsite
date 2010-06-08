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
 * $Id: WikiImage.php,v 1.10 2008/03/29 20:01:55 blindman1344 Exp $
 */

class WikiImage
{
    var $id        = 0;
    var $owner_id  = 0;
    var $created   = 0;
    var $filename  = NULL;
    var $size      = 0;
    var $type      = NULL;
    var $summary   = NULL;


    function WikiImage($id=NULL)
    {
        if (!empty($id))
        {
            $this->setId($id);

            $db = new Core\DB('wiki_images');
            Core\Error::logIfError($db->loadObject($this));
        }
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

    function getOwnerUsername()
    {
        $db = new Core\DB('users');
        $db->addWhere('id', $this->getOwnerId());
        $db->addColumn('username');
        $result = $db->select('col');
        if (Core\Error::logIfError($result))
        {
            return dgettext('wiki', 'N/A');
        }
        return $result[0];
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

    function setFilename($filename)
    {
        if (!$this->getId())
        {
            $this->filename = $filename;
        }
    }

    function getFilename()
    {
        return $this->filename;
    }

    function setSize($size)
    {
        if (!$this->getId())
        {
            $this->size = (int)$size;
        }
    }

    function getSize()
    {
        if($this->size < 1024)
        {
            // Display in bytes
            return number_format($this->size, 2) . ' bytes';
        }
        else if($this->size < pow(2, 20))
        {
            // Display in kilobytes
            return number_format(round(($this->size/1024),2), 2) . ' KB';
        }
        else
        {
            // Display in megabytes
            return number_format(round(($this->size/1024)/1024,2), 2) . ' MB';
        }
    }

    function setType($type)
    {
        if (!$this->getId())
        {
            $this->type = $type;
        }
    }

    function getType()
    {
        return $this->type;
    }

    function setSummary($summary)
    {
        $this->summary = Core\Text::parseInput($summary);
    }

    function getSummary($parse=TRUE)
    {
        if ($parse)
        {
            return Core\Text::parseOutput($this->summary);
        }

        return $this->summary;
    }

    /**
     * Add image form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function add()
    {
        $form = new Core\Form;
        $form->addHidden('module', 'wiki');
        $form->addHidden('op', 'doimageupload');

        $form->addFile('filename');
        $form->setSize('filename', 50);
        $form->setLabel('filename', dgettext('wiki', 'Filename'));

        $form->addText('summary');
        $form->setSize('summary', 50, 200);
        $form->setLabel('summary', dgettext('wiki', 'Summary'));

        $form->addSubmit('save', dgettext('wiki', 'Upload'));
        return $form->getTemplate();
    }

    /**
     * Save
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function save()
    {
        if (empty($_POST['summary']))
        {
            return dgettext('wiki', 'You need to supply a summary.');
        }

        Core\Core::initModClass('filecabinet', 'Image.php');

        $this->setSummary($_POST['summary']);
        $this->setOwnerId(Current_User::getId());
        $this->setCreated(mktime());

        $image = new PHPWS_Image;
        $image->setDirectory('images/wiki/');
        if (!$image->importPost('filename'))
        {
            if (isset($image->_errors) && sizeof($image->_errors))
            {
                foreach ($image->_errors as $oError)
                {
                    $imageErrors[] = $oError->getMessage();
                }
                return implode(' ', $imageErrors);
            }

            return dgettext('wiki', 'Please specify a valid file to upload.');
        }
        else
        {
            $image->setFilename(str_replace(' ', '_', $image->file_name));
            if (is_file(PHPWS_HOME_DIR . 'images/wiki/' . $image->file_name))
            {
                $image->setFilename($this->created . '_' . $image->file_name);
            }

            if (Core\Error::logIfError($image->write()))
            {
                return dgettext('wiki', 'There was a problem saving your image.');
            }

            $this->setFilename($image->file_name);
            $this->setSize($image->getSize());
            $this->setType($image->file_type);
        }

        $db = new Core\DB('wiki_images');
        if (Core\Error::logIfError($db->saveObject($this)))
        {
            @unlink(PHPWS_HOME_DIR . 'images/wiki/' . $this->getFilename());
            return dgettext('wiki', 'There was a problem saving your image.');
        }
        return dgettext('wiki', 'Image Saved!');
    }

    /**
     * Delete
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function delete()
    {
        if (!Current_User::authorized('wiki', 'upload_images') &&
            !(Core\Settings::get('wiki', 'allow_image_upload') && Current_User::isLogged()))
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to image delete.'));
            return;
        }

        if (isset($_REQUEST['yes']))
        {
            @unlink(PHPWS_HOME_DIR . 'images/wiki/' . $this->getFilename());
            $db = new Core\DB('wiki_images');
            $db->addWhere('id', $this->getId());
            if (Core\Error::logIfError($db->delete()))
            {
                return dgettext('wiki', 'Error deleting image.');
            }
            return dgettext('wiki', 'Image deleted!');
        }
        else if (isset($_REQUEST['no']))
        {
            return dgettext('wiki', 'Image was not deleted!');
        }

        $tags = array();
        $tags['MESSAGE'] = dgettext('wiki', 'Are you sure you want to delete this image?');
        $tags['YES'] = Core\Text::secureLink(dgettext('wiki', 'Yes'), 'wiki',
                                              array('op'=>'doimagedelete', 'yes'=>1, 'id'=>$this->getId()));
        $tags['NO'] = Core\Text::secureLink(dgettext('wiki', 'No'), 'wiki',
                                             array('op'=>'doimagedelete', 'no'=>1, 'id'=>$this->getId()));
        $tags['WIKIPAGE'] = '<img src="images/wiki/' . $this->getFilename() . '" alt="" />';

        return Core\Template::processTemplate($tags, 'wiki', 'confirm.tpl');
    }

    function getTag()
    {
        return '[[image ' . $this->getFilename() . ']]';
    }

    function getTpl()
    {
        $links[] = '<a href="images/wiki/' . $this->getFilename() . '">' . dgettext('wiki', 'View') . '</a>';

        $vars['id'] = $this->getId();

        $vars['op'] = 'imagecopy';
        $links[] = Core\Text::secureLink(dgettext('wiki', 'Copy'), 'wiki', $vars);

        $vars['op'] = 'imagedelete';
        $links[] = Core\Text::secureLink(dgettext('wiki', 'Delete'), 'wiki', $vars);

        $template['ACTIONS'] = implode(' | ', $links);
        $template['FILENAME'] = $this->getFilename();
        $template['SIZE'] = $this->getSize();
        $template['TYPE'] = $this->getType();
        $template['OWNER'] = $this->getOwnerUsername();
        $template['CREATED'] = $this->getCreated();
        $template['SUMMARY'] = $this->getSummary();

        return $template;
    }
}

?>