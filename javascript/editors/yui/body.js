<div class="yui-editor">
<textarea name="{NAME}" id="{ID}" cols="50" rows="10">{VALUE}</textarea>
</div>
<script>
var myEditor = new YAHOO.widget.Editor('{ID}', {
    height: '{HEIGHT}',
    width: '{WIDTH}',
    dompath: true,
    animate: true
});
myEditor.render();
</script>
