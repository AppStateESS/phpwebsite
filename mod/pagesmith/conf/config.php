<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
$icon = Core\Icon::get('edit');
$icon->setStyle('float : left; margin-right : 5px;');

define('PS_EDIT', $icon->__toString());

define('PS_ALLOWED_HEADER_TAGS', '<b><strong><i><u><em>');

/**
 * PageSmith uses a text column to store data. If the data exceeds the
 * 65,535, it will cut the data off.
 * Some databases have column types with higher limits. If you want to take
 * advantage of them you will need to alter your ps_text.content column and
 * change the below to false. Do so at your own risk.
 */
define('PS_CHECK_CHAR_LENGTH', true);

?>