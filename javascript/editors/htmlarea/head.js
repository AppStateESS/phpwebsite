<script type="text/javascript">
  _editor_url = "./javascript/editors/htmlarea/";
  _editor_lang = "en";
</script>

<script type="text/javascript" src="./javascript/editors/htmlarea/htmlarea.js"></script>
<script type="text/javascript" src="./javascript/editors/htmlarea/custom.js"></script>

<script type="text/javascript">
      HTMLArea.loadPlugin("TableOperations");
      HTMLArea.loadPlugin("ImageManager");

      function initDocument() {
	var config = new HTMLArea.Config();
	config.toolbar = custom_toolbar;
	config.width   = custom_width;
	config.height   = custom_height;

	var editor = new HTMLArea("{NAME}", config);

        editor.registerPlugin(TableOperations);
        editor.registerPlugin(ImageManager);
	editor.generate();
      }
</script>

<script type="text/javascript">
</script>
