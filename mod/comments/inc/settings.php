<?php
/**
 * captcha : 0 - none, 1 - anonymous only, 2 - all users
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$settings = array('allow_signatures'       => 0,
                  'allow_avatars'          => 1,
                  'local_avatars'          => 1,
                  'allow_image_signatures' => 0,
                  'default_order'          => 'old_all',
                  'captcha'                => 1,
                  'anonymous_naming'       => 0,
                  'recent_comments'        => 0,
                  'default_approval'       => 0,
                  'use_editor'             => 1,
                  'email_subject'          => 'New comment to thread',
                  'email_subject'      	   => "New reply to thread '::thread_title::'",
                  'email_text'       	   => "Hello ::username::,
<br />
::postername:: has just replied to a topic you have subscribed to!
<br />
'::thread_title::' is located at:
::thread_url::
<br />
Here is the message that was just posted:
==================================
::reply_msg::
==================================
<br />
<br />
There may also be other replies, but you will not receive any more notifications until you visit the topic again.
<br />
Thanks for posting!
<br />
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Unsubscription information:
To unsubscribe from this topic, please visit this page, login and select 'Stop Monitoring' from the Mini Admin menu:
::thread_url::
To unsubscribe from ALL topics, please login and visit this page:
::unsubscribeall_url:: ",
                  'monitor_posts'          => 0,
                  'allow_user_monitors'    => 1,
                  'reported_comments'      => 0,
                  'unapproved_comments'    => 0,
                  'default_rank'           => 1,
                  'avatar_folder_id'       => 0);
?>