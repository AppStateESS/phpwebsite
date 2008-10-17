<!-- BEGIN info -->
<div style="overflow : auto">
<div style="float : right">{RANK_IMAGE_PIC}</div>
{EDIT_ICON}
{RANK_MIN_TXT_LABEL} : {RANK_MIN_TXT}<br />
<span style="margin-left : 2em">{RANK_TITLE_LABEL} : {RANK_TITLE_TXT}</span>
</div>
<!-- END info -->

{START_FORM}
<div id="user-rank-t-{TAG_RANK_ID}" style="{HIDE} border: 1px solid rgb(144, 238, 144); padding : 5px">
        <table style="margin-left:5%; text-align: left; width: 95%;" cellpadding="3">
          <tbody>
            <tr>
              <td>{TITLE_LABEL} </td>
              <td>{TITLE}</td>
            </tr>
            <tr>
              <td>{IMAGE_LABEL} </td>
              <td>
                <span class="smaller">{IMAGE_HELP}<br /></span>
                {IMAGE}
              </td>
            </tr>
            <tr>
              <td>{REPEAT_IMAGE_LABEL}</td>
              <td>{REPEAT_IMAGE} {REPEAT_TIMES}</td>
            </tr>
            <tr>
              <td>{MIN_POSTS_LABEL} </td>
              <td>{MIN_POSTS}</td>
            </tr>
            <tr>
              <td>{RANK_ID_LABEL} </td>
              <td>{RANK_ID}</td>
            </tr>
            <tr>
              <td>{STACK_LABEL}&nbsp;&nbsp;</td>
              <td>{STACK_1_LABEL}{STACK_1} {STACK_2_LABEL}{STACK_2} {SAVE_USER_RANK}</td>
            </tr>
          </tbody>
        </table>
<div style="text-align : right"><!-- BEGIN delete -->{DELETE} --- <!-- END delete --> {SUBMIT}</div>
</div>
{END_FORM}
