<div class="blog">
  <div class="box">
    <div class="box-title">
    <h1>{TITLE}</h1>
    <h3>{POSTED_BY} {AUTHOR}</h3>
    <h3>{POSTED_ON} {LOCAL_DATE}</h3>
    </div>
    <!-- BEGIN categories -->
    <div class="bgcolor2 padded">{CATEGORIES}</div>
    <!-- END categories -->
    <div class="box-content">
      {ENTRY}
      <!-- BEGIN comment-info -->
      <div class="padded border-top">
      {COMMENT_LINK}
      <!-- BEGIN last-poster -->- {LAST_POSTER_LABEL}:
      {LAST_POSTER}<!-- END last-poster -->
      </div>
      <!-- END comment-info -->
      <!-- BEGIN comments -->
      <div class="comments-link">{COMMENTS}</div>
      <!-- END comments -->
    </div>
  </div>
</div>
