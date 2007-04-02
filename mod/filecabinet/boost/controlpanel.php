<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
translate('filecabinet');
$link[] = array('label'       => _('File Cabinet'),
                'restricted'  => TRUE,
                'url'         => 'index.php?module=filecabinet&amp;aop=image',
                'description' => _('Manages images and documents uploaded to your site.'),
                'image'       => 'cabinet.png',
                'tab'         => 'admin'
                );
translate();
?>