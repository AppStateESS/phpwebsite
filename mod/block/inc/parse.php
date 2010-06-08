<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function block_view($block_id) {
    $block = new Block_Item((int)$block_id);
    if (empty($block->id)) {
        return NULL;
    }
    $template['BLOCK'] = $block->view(FALSE, FALSE);
    return Core\Template::process($template, 'block', 'embedded.tpl');
}

?>