<article>
    <header>
        <h2>{TITLE_NO_LINK}</h2>
        <!-- BEGIN unpub -->
        <span class="unpublished"> ({UNPUBLISHED}) </span>
        <!-- END unpub -->
        <div>
            <small>{AUTHOR}</small>
        </div>
        <div>
            <small class="muted">{PUBLISHED} {PUBLISHED_DATE}</small>
        </div>
    </header>
    <!-- BEGIN image -->
    <div class="entry-image">{IMAGE}</div>
    <!-- END image -->
    <p>
        {SUMMARY}
    </p>
    <!-- BEGIN entry-content -->
    <div class="entry-content"><p>{ENTRY}</p></div>
    <!-- END entry-content -->
</article>
<!-- BEGIN edit-link -->
<div>
    <hr />
    <a href="{EDIT_URI}" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit blog</a>
</div>
<!-- END edit-link -->
{COMMENT_SCRIPT}