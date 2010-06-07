{BACK}

<h1>{SETTINGS_LABEL}</h1>

<!-- BEGIN MESSAGE -->
<h4>{MESSAGE}</h4>
<!-- END MESSAGE -->

{START_FORM}
{SHOW_ON_HOME} {SHOW_ON_HOME_LABEL}<br />
{ALLOW_ANON_VIEW} {ALLOW_ANON_VIEW_LABEL}<br />
{ALLOW_PAGE_EDIT} {ALLOW_PAGE_EDIT_LABEL}<br />
{ALLOW_IMAGE_UPLOAD} {ALLOW_IMAGE_UPLOAD_LABEL}<br />
{ALLOW_BBCODE} {ALLOW_BBCODE_LABEL}<br />
{EXT_CHARS_SUPPORT} {EXT_CHARS_SUPPORT_LABEL}<br />
{ADD_TO_TITLE} {ADD_TO_TITLE_LABEL}<br />
{FORMAT_TITLE} {FORMAT_TITLE_LABEL}<br />
{SHOW_MODIFIED_INFO} {SHOW_MODIFIED_INFO_LABEL}<br />
{DIFF_TYPE} {DIFF_TYPE_LABEL}<br />
{MONITOR_EDITS} {MONITOR_EDITS_LABEL}<br /><br />

{ADMIN_EMAIL_LABEL}: {ADMIN_EMAIL}<br /><br />

{EMAIL_TEXT_LABEL}:<br />
{EMAIL_TEXT}<br /><br />

{DEFAULT_PAGE_LABEL}: {DEFAULT_PAGE}<br /><br />

{EXT_PAGE_TARGET_LABEL}: {EXT_PAGE_TARGET}<br /><br />

<em>{MENU_ITEMS_LABEL}</em><br />
{IMMUTABLE_PAGE} {IMMUTABLE_PAGE_LABEL}<br />
{RAW_TEXT} {RAW_TEXT_LABEL}<br />
{PRINT_VIEW} {PRINT_VIEW_LABEL}<br />
{WHAT_LINKS_HERE} {WHAT_LINKS_HERE_LABEL}<br />
{RECENT_CHANGES} {RECENT_CHANGES_LABEL}<br />
{RANDOM_PAGE} {RANDOM_PAGE_LABEL}<br /><br />

<em>{DISCUSSION_SECTION_LABEL}</em><br />
{DISCUSSION} {DISCUSSION_LABEL}<br />
{DISCUSSION_ANON} {DISCUSSION_ANON_LABEL}<br /><br />

{SAVE}
{END_FORM}

<hr />

<h1>{PAGES_LABEL}</h1>

<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{TITLE} {TITLE_SORT}</th>
    <th>{UPDATED} {UPDATED_SORT}</th>
    <th>{VERSION}</th>
    <th>{HITS} {HITS_SORT}</th>
    <th>{ORPHANED}</th>
    <th>{ACTIONS}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{TITLE}</td>
    <td>{UPDATED}</td>
    <td>{VERSION}</td>
    <td>{HITS}</td>
    <td>{ORPHANED}</td>
    <td>{ACTIONS}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="6">{EMPTY_MESSAGE}</td>
  </tr>
<!-- END empty_message -->
</table>

<!-- BEGIN navigation -->
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<!-- END navigation -->
<!-- BEGIN search -->
<div class="align-right">
{SEARCH}
</div>
<!-- END search -->