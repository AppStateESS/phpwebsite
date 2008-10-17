
<h2>{RANK_NEW_TITLE}</h2>

<!-- BEGIN add_new_rank -->

        <span style="display: none">
                {RANK_MIN_TXT_LABEL}{RANK_MIN_TXT}{RANK_TITLE_LABEL}{RANK_TITLE_TXT}{RANK_IMAGE_PIC}
        </span>
        <table id="editrank_{RANK_ID}" style="margin-left:5%; border: 1px solid rgb(144, 238, 144); padding: 5px; text-align: left; width: 95%" cellpadding="2" cellspacing="2">
          <tbody>
            <tr>
              <td>{RANK_TITLE_LABEL} </td>
              <td>{RANK_TITLE}</td>
            </tr>
            <tr>
              <td>{RANK_IMAGE_LABEL} </td>
              <td>
                <span class="smaller">{RANK_IMAGE_HELP}<br /></span>
                {RANK_IMAGE}
              </td>
            </tr>
        <tr>
          <td>{RANK_REPEAT_IMAGE_LABEL}</td>
          <td>{RANK_REPEAT_IMAGE} {RANK_REPEAT_TIMES}</td>
        </tr>
            <tr>
              <td>{RANK_MIN_LABEL} </td>
              <td>{RANK_MIN}</td>
            </tr>
            <tr>
              <td>{RANK_USERGROUP_LABEL} </td>
              <td>{RANK_USERGROUP}</td>
            </tr>
            <tr>
              <td>{RANK_STACK_LABEL}</td>
              <td>{RANK_STACK_1_LABEL}{RANK_STACK_1} {RANK_STACK_2_LABEL}{RANK_STACK_2}</td>
            </tr>
          </tbody>
        </table>
<!-- END add_new_rank -->

<br />
<br />
{SUBMIT}
