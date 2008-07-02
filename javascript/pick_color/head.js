<script type="text/javascript" src="javascript/pick_color/farbtastic.js"></script>
<link rel="stylesheet" href="javascript/pick_color/farbtastic.css" type="text/css" />
<script type="text/javascript">
function farb(pick_id, input_id)
{
    input_id = '#' + input_id;
    pick_id = '#' + pick_id;
    $(pick_id).toggle();
    $(pick_id).farbtastic(input_id);
}
</script>
<style type="text/css">
a.color_pick {
    cursor : pointer;
}
</style>