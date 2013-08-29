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

            </fieldset>

            <fieldset>
                <legend>{CATEGORY_LABEL}</legend>
                <div class="checkbox">
                    <label>{SHOW_CATEGORY_LINKS} {SHOW_CATEGORY_LINKS_LABEL_TEXT}</label>
                </div>
                <div class="checkbox">
                    <label>{SHOW_CATEGORY_ICONS} {SHOW_CATEGORY_ICONS_LABEL_TEXT}</label>
                </div>
                <div class="checkbox" style="margin-left: 1.5em;">
                    <label>{SINGLE_CAT_ICON} {SINGLE_CAT_ICON_LABEL_TEXT}</label>
                </div>
            </fieldset>

            <fieldset>
                <legend>{SUBMISSION_LABEL}</legend>
                <div class="checkbox">
                    <label>{ALLOW_ANONYMOUS_SUBMITS} {ALLOW_ANONYMOUS_SUBMITS_LABEL_TEXT}</label>
                </div>
                <!-- BEGIN menu-link -->
                <small>{MENU_LINK}</small>
                <!-- END menu-link -->
                <div class="checkbox">
                    <label>{CAPTCHA_SUBMISSIONS} {CAPTCHA_SUBMISSIONS_LABEL_TEXT}</label>
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
            </fieldset>
            <!-- END purge -->

            <hr />

            <button class="btn btn-primary" type="submit" id="{SUBMIT_ID}">{SUBMIT_VALUE}</button>

        </form>
    </div>

</div>