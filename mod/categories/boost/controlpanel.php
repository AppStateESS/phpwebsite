<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id
 */

translate('categories');
$link[] = array('label'       => _('Categories'),
                'restricted'  => TRUE,
                'url'         =>
                'index.php?module=categories&amp;action=admin',
                'description' => _('Administrate your site\'s categories.'),
                'image'       => 'categories.png',
                'tab'         => 'content'
                );
translate();
?>