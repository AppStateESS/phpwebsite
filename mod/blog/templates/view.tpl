<div class="blog">
  <div class="box">
    <div class="box-title">
    <h1>{TITLE}</h1>
    <h3>{POSTED_BY} {AUTHOR}</h3>
    <h3>{POSTED_ON} {LOCAL_DATE}</h3>
    </div>
    <!-- BEGIN categories -->
    <div class="category-links">{CATEGORIES}</div>
    <!-- END categories -->
    <div class="box-content">
      {ENTRY}
      <!-- BEGIN edit-link --><div class="align-right">{EDIT_LINK}</div><!-- END edit-link -->
      <!-- BEGIN comment-info -->
      <div class="read-more">
      {COMMENT_LINK}
      <!-- BEGIN last-poster -->- {LAST_POSTER_LABEL}:
      {LAST_POSTER}<!-- END last-poster -->
      </div>
      <!-- END comment-info -->
      <!-- BEGIN comments -->
      {COMMENTS}
      <!-- END comments -->
    </div>
  </div>
</div>
