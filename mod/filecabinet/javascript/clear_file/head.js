<script type="text/javascript">
//<![CDATA[

    function clear_file(id) {
        placeholder = document.getElementById('pl_' + id);
        placeholder.innerHTML = '{img}';
        hidden = document.getElementById('h_' + id);
        hidden.value = 0;

        edit_link = document.getElementById('l_' + id);
        onclick_val = edit_link.innerHTML;
        edit_link.innerHTML = onclick_val.replace(/fid=\d+&/, 'fid=0&')
    }

//]]>
</script>
