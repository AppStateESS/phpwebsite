<article>
    <header>
        <h2>{TITLE_NO_LINK}</h2>

        <!-- BEGIN edit-link -->
        <div class="pull-right">
            <a href="{EDIT_URI}" class="btn"><i class="icon-pencil"></i> Edit</a>
        </div>
        <!-- END edit-link -->

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