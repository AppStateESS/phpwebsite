<script type="text/javascript">
$(document).ready(function() {
     $('.fck-video-insert').click(function() {
             id = $(this).attr('id');
             furl = 'index.php?module=filecabinet&uop=fetch_media&mid='+id+'&rnd='+Math.floor(Math.random()*1000000);
             $.ajax({type:'GET', url:furl, success: function(data) { $('#'+id).replaceWith(data); }, async : false });
         });
});
</script>
<style type="text/css">
.fck-video-insert:hover{
	cursor : pointer;
}
</style>