<div class="row">
    <div class="col-lg-4">
        <form class="{FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}"{FORM_ENCODE}>
            {HIDDEN_FIELDS}

            <fieldset>
                <legend>{COMMENTS_LABEL}</legend>
                <div class="form-group">
                    {COMMENT_SCRIPT_LABEL} {COMMENT_SCRIPT}
                </div>
            </fieldset>

            <fieldset>
                <legend>{VIEW_LABEL}</legend>

                <div class="form-group checkbox">
                    <label>{HOME_PAGE_DISPLAY} {HOME_PAGE_DISPLAY_LABEL_TEXT}</label>
                </div>

                <div class="form-group">
                    <label for="{BLOG_LIMIT_ID}">{BLOG_LIMIT_LABEL_TEXT}</label> {BLOG_LIMIT}
                </div>

                <div class="form-group">
                    <label for="{PAST_ENTRIES_ID}">{PAST_ENTRIES_LABEL_TEXT}</label> {PAST_ENTRIES} <span class="help-block">{PAST_NOTE}</span>
                </div>

                <div class="form-group">
                    <label for="{SHOW_RECENT_ID}">{SHOW_RECENT_LABEL_TEXT}</label> {SHOW_RECENT}
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

            <fieldset>
                <legend>Image Manager</legend>
                <div class="checkbox">
                    <label>{SIMPLE_IMAGE} {SIMPLE_IMAGE_LABEL}</label>
                </div>
                <div class="checkbox">
                    <label>{MOD_FOLDERS_ONLY} {MOD_FOLDERS_ONLY_LABEL}</label>
                </div>

                <div class="form-group">{MAX_WIDTH_LABEL} {MAX_WIDTH}</div>
                <div class="form-group">{MAX_HEIGHT_LABEL} {MAX_HEIGHT}</div>
            </fieldset>

            <!-- BEGIN purge -->
            <fieldset>
                <legend>Purge</legend>
                <div class="form-group">{PURGE_DATE_LABEL} {PURGE_DATE}</div>

                <button class="btn btn-danger pull-right" id="{PURGE_CONFIRM_ID}">{PURGE_CONFIRM_VALUE}</button>
                {date_script}
            </fieldset>
            <!-- END purge -->

            <hr />

            <button class="btn btn-primary" type="submit" id="{SUBMIT_ID}">{SUBMIT_VALUE}</button>

        </form>
    </div>

</div>
