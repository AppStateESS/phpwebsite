  <div class="hentry box">
    <div class="box-title">
    <h2 class="entry-title">{TITLE}</h2>
    <h3>{POSTED_BY} <abbr class="author">{AUTHOR}</abbr></h3>
    <h3>{POSTED_ON} <abbr class="published" title="{PUBLISHED_DATE}">{LOCAL_DATE}</abbr></h3>
    </div>
    <!-- BEGIN categories -->
    <div class="blog-category-links">{CATEGORIES}</div>
    <!-- END categories -->
    <!-- BEGIN icons --><ul class="blog-category-icons">
    <!-- BEGIN cat-icons --><li>{CAT_ICON}</li><!-- END cat-icons -->
    </ul><!-- END icons -->
    <div class="box-content">
      <div class="entry-summary"><!-- BEGIN image --><div class="entry-image">{IMAGE}</div><!-- END image -->{SUMMARY}</div>
      <div class="entry-content">{ENTRY}</div>
      <!-- BEGIN edit-link --><div class="align-right">{EDIT_LINK}</div><!-- END edit-link -->
      <!-- BEGIN comment-info -->
      <div class="read-more">
      <!-- BEGIN read-more -->{READ_MORE} |<!-- END read-more --> {COMMENT_LINK}

      <!-- BEGIN last-poster -->- {LAST_POSTER_LABEL}:
      {LAST_POSTER}<!-- END last-poster -->
      </div>
      <!-- END comment-info -->
      <!-- BEGIN comments -->
      {COMMENTS}
      <!-- END comments -->
    </div>
  </div>

