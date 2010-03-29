<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
require_once('../../../../config/core/config.php');
?>
<html>
<head>
<script type="text/javascript"
    src="<?php echo PHPWS_SOURCE_HTTP ?>/javascript/editors/ckeditor/ckeditor.js"
></script>
<script type="text/javascript">
function send_url()
{
image_url = '<?php echo PHPWS_SOURCE_HTTP ?>' + 'images/icons/default/tango2.png';
alert(image_url);
window.opener.CKEDITOR.tools.callFunction('1', image_url);
window.close();
}
</script>
</head>
<body>
<input type="button" onclick="send_url()" name="thing" value="Click me" />
</body>
</html>
