<script type="text/javascript">
//<![CDATA[

    function clear_file(id, authkey) {
        placeholder = $('#pl_' + id).html('{img}');
        $('#h_' + id).val(0);

        edit_link = document.getElementById('l_' + id);
        onclick_val = edit_link.innerHTML;
        edit_link.innerHTML = onclick_val.replace(/fid=\d+&/, 'fid=0&');
        edit_link.innerHTML = onclick_val.replace(/authkey=\s+&/, 'authkey=' + authkey + '&');
    }

//]]>
</script>
