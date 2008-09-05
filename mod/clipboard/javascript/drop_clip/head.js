<script type="text/javascript">
//<![CDATA[

function drop_clip(clip_id) {
    $.get('index.php', { module : "clipboard", action : "drop", key : clip_id},
          function() {
              $('#clip-' + clip_id).remove();
          });
}

//]]>
</script>
