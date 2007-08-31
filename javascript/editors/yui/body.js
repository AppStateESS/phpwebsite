<textarea name="{NAME}" id="{ID}" cols="50" rows="10">{VALUE}</textarea>
<script>
var myEditor = new YAHOO.widget.Editor('{ID}', {
    height: '{HEIGHT}px',
    width: '{WIDTH}px',
    dompath: true,
    animate: true
});
myEditor.render();
</script>
