<div class="blog">
  <div class="box">
    <h1>{TITLE}</h1>
    <h3>{POSTED_BY} {AUTHOR}</h3>
    <h3>{POSTED_ON} {DATE}</h3>
    <!-- BEGIN categories -->
    <div class="bgcolor2 padded">{CATEGORIES}</div>
    <!-- END categories -->
    <div class="box-content">
      {ENTRY}<br />{EDIT_LINK}
      <!-- BEGIN comment-info -->
      <div class="padded border-top">
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
