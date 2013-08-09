(function() {
    CKEDITOR.dialog.add('youtube',
            function(editor)
            {
                return{title: editor.lang.youtube.title, minWidth: CKEDITOR.env.ie && CKEDITOR.env.quirks ? 368 : 350, minHeight: 240,
                    onShow: function() {
                        this.getContentElement('general', 'content').getInputElement().setValue('');
                    },
                    onOk: function() {
                        val = this.getContentElement('general', 'content').getInputElement().getValue();
                        val = val.replace(/.*v\=(\w+).*/gi, '$1');
                        var text = '<iframe title="YouTube video player" class="youtube-player" type="text/html" width="480" height="390" src="http://www.youtube.com/embed/'
                                //+this.getContentElement('general','content').getInputElement().getValue()
                                + val
                                + '?rel=0" frameborder="0"></iframe>';
                        this.getParentEditor().insertHtml(text);
                    },
                    contents: [{label: editor.lang.common.generalTab, id: 'general', elements:
                                    [{type: 'html', id: 'pasteMsg', html: '<div style="white-space:normal;width:500px;"><img style="margin:5px auto;" src="'
                                                    + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('youtube')
                                                    + 'images/youtube_large.png')
                                                    + '"><br />' + editor.lang.youtube.pasteMsg
                                                    + '</div>'}, {type: 'html', id: 'content', style: 'width:340px;height:90px', html: '<input size="100" style="' + 'border:1px solid black;' + 'background:white">', focus: function() {
                                                this.getElement().focus()
                                            }}]}]}
            });
})();
