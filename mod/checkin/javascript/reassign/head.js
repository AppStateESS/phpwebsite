<script type="text/javascript">
//<![CDATA[

function reassign(select, v_id)
{
    s_id = $(select).val();
    $.get('index.php', { module : 'checkin',
                            aop : 'reassign',
                       staff_id : s_id,
                     visitor_id : v_id,
                        authkey : '{authkey}' }, function(data) {location.reload()});
}

//]]>
</script>
