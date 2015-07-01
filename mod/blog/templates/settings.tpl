<div class="row">
    <form class="{FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}"{FORM_ENCODE}>
    <div class="col-md-6">
            {HIDDEN_FIELDS}
            <fieldset>
                <legend>{VIEW_LABEL}</legend>

                <div class="form-group checkbox">
                    <label>{HOME_PAGE_DISPLAY} {HOME_PAGE_DISPLAY_LABEL_TEXT}</label>
                </div>

                <div class="form-group">
                    <label for="{SHOW_RECENT_ID}">{SHOW_RECENT_LABEL_TEXT}</label> {SHOW_RECENT}
                    <small>Shows blog titles in side bar if entries are not shown on the front page.</small>
                </div>
                <div class="form-group">
                    <label for="{BLOG_LIMIT_ID}">{BLOG_LIMIT_LABEL_TEXT}</label> {BLOG_LIMIT}
                </div>

                <div class="form-group">
                    <label for="{PAST_ENTRIES_ID}">{PAST_ENTRIES_LABEL_TEXT}</label> {PAST_ENTRIES} <small>Entries not show on the front page.<br />{PAST_NOTE}</small>
                </div>

                <div class="form-group checkbox">
                    <label>{LOGGED_USERS_ONLY} {LOGGED_USERS_ONLY_LABEL_TEXT}</label>
                </div>

                <div class="form-group">
                    <label for="{VIEW_ONLY_ID}">{VIEW_ONLY_LABEL_TEXT}</label> {VIEW_ONLY}
                </div>

                <div class="form-group checkbox">
                    <label for="{SHOW_POSTED_DATE_ID}">{SHOW_POSTED_DATE} {SHOW_POSTED_DATE_LABEL_TEXT}</label>
                </div>

                <div class="form-group checkbox">
                    <label for="{SHOW_POSTED_BY_ID}">{SHOW_POSTED_BY} {SHOW_POSTED_BY_LABEL_TEXT}</label>
                </div>

            </fieldset>
    </div>
    <div class="col-md-6">
            <fieldset>
                <legend>{COMMENTS_LABEL}</legend>
                <div class="form-group">
                    {COMMENT_SCRIPT_LABEL} {COMMENT_SCRIPT}
                </div>
            </fieldset>
            <!-- BEGIN purge -->
            <fieldset>
                <legend>Purge</legend>
                <div class="form-group">{PURGE_DATE_LABEL} {PURGE_DATE}</div>
                <div class="pull-right">{PURGE_CONFIRM}</div>
                {date_script}
            </fieldset>
            <!-- END purge -->
        <hr />
    </div>
    <div style="clear:both" class="text-center"><button class="btn btn-primary btn-lg" type="submit" id="{SUBMIT_ID}">{SUBMIT_VALUE}</button></div>
    </form>
</div>
