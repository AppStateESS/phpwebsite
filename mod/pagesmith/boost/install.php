<?php
function pagesmith_install(&$content)
{
	PHPWS_Core::initModClass('pagesmith', 'PS_Page.php');
	PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
	$page = new PS_Page;
	$page->setTitle('Welcome to phpWebSite!');
	$page->template = 'text_only';
	$page->front_page = 1;
	$page->save();

	$section = new PS_Text;
	$section->pid = $page->id;
	$section->content = '&lt;p&gt;Thank you for installing phpWebSite.
	Its developers hope you enjoy it.&lt;/p&gt;&lt;p&gt;
	The page you are reading was created under
	&lt;a href=&quot;./index.php?module=pagesmith&amp;amp;aop=menu&amp;amp;authkey=be5c993fffbc1193e3763d43ef5b2c78&quot;&gt;PageSmith&lt;/a&gt;
	, which can be found in the &lt;a href=&quot;index.php?module=controlpanel&amp;amp;command=panel_view&quot;&gt;Control Panel&lt;/a&gt; under the Content Tab.&lt;/p&gt;&lt;p&gt;
	PageSmith has many default templates to get you started. If you are comfortable with HTML, you may wish to just use the &quot;Text Only&quot;
	 template under the &quot;No image&quot; category.&lt;/p&gt;&lt;p&gt;This particular page was added to the home page by using the &quot;
	 Add to front page&quot; option found in the MiniAdmin or the &quot;List&quot; tab under the PageSmith administrative options. You may edit this page or remove it from the List view as well.&lt;/p&gt;';
	$section->secname = 'text1';
	$section->sectype = 'text';
	$section->save($page->key_id);
	return true;
}
?>