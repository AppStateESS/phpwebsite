<?php

class Blog_User {

  function main()
  {
    if (!isset($_REQUEST['blog_id']) && isset($_REQUEST['id'])) {
      $blog = & new Blog((int)$_REQUEST['id']);
    } elseif (isset($_REQUEST['blog_id'])) {
      $blog = & new Blog((int)$_REQUEST['blog_id']);
    } else {
      $blog = & new Blog();
    }

    switch ($_REQUEST['action']) {
    case 'view_comments':
      $content = $blog->view(TRUE, FALSE);
      break;

    case 'make_comment':
      $content = Blog_User::makeComment($blog);
      break;

    case 'save_comment':
      $content = Blog_User::postComment($blog);

      break;
    }

    Layout::add($content);
  }


  function postComment(&$blog)
  {
    PHPWS_Core::initModClass('comments', 'Comments.php');
    $key = $blog->getKey();
    $result = Comments::post($key);
    if (PEAR::isError($result)) {
      PHPWS_Error::log($result);
      return _('Sorry but there was a problem saving your comment.') . '<br />' . _('Please try again later.');
    }

    $vars['action'] = 'view_comments';
    $vars['blog_id'] = $blog->getId();

    $content[] = _('Your comment has been saved successfully.');
    $content[] = _('You will be returned to the blog in a moment.');
    $content[] = PHPWS_Text::moduleLink(_('If you wish, you can click here to return immediately.'), 'blog', $vars);
    Layout::metaRoute(PHPWS_Text::linkAddress('blog', $vars));
    return implode('<br />', $content);

  }

  function makeComment(&$blog)
  {
    PHPWS_Core::initModClass('comments', 'Comments.php');
    $key = $blog->getKey();
    $form = Comments::getForm();
    $form->addHidden('module', 'blog');
    $form->addHidden('action', 'save_comment');
    $form->addHidden('blog_id', $blog->getId());
    $form->addSubmit(_('Post Comment'));
    $template = $form->getTemplate();
    $template['TITLE'] = sprintf(_('Comment on "%s" blog.'), $blog->getTitle());
    return PHPWS_Template::process($template, 'blog', 'make_comment.tpl');
  }

  function show(){
    $key = "front blog page";

    if (!Current_User::isLogged()    &&
	!Current_User::allow("blog") &&
	$content = PHPWS_Cache::get($key)) {
      return $content;
    }

    $limit = 5;

    $db = & new PHPWS_DB("blog_entries");
    $db->setLimit($limit);
    $db->addOrder('date desc');
    if (!Current_User::isLogged()) {
      $db->addWhere('restricted', '0');
    }

    $result = $db->getObjects("Blog");

    if (empty($result))
      return ("No blog entries found.");
    
    foreach ($result as $blog) {
      if ($blog->getRestricted() == 2 &&
	  !Current_User::allow('blog', 'view_blog', $blog->getId(), 'entry')) {
	continue;
      }
      $view = $blog->view();
      if (!empty($view)) {
	$list[] = $view;
      }
    }

    $content = implode("", $list);
    if (!Current_User::allow("blog")) {
      PHPWS_Cache::save($key, $content);
    }

    return $content;
  }

}

?>