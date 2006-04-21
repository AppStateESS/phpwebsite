Close and Refresh by Matthew McNaney
------------------------------------

Javascript which closes a popup window and refreshes the parent
window.

C&R doesn't require any variables. Just call:

javascript('close_refresh');

There are some variables you may call however.

timeout - seconds to wait until closing and refreshing, default to 0.

refresh - if you do not want to refresh the parent window, set this to
          0 (zero)

location - if want the refresh to be send to another page, send the
           url in this variable
