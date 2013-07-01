CKEDITOR.plugins.add('floatleft',
        {
            init: function(editor)
            {
                editor.addCommand('floatleft',
                        {
                            exec: function(editor)
                            {
                                var highlighted = editor.getSelection().getSelectedElement();
                                highlighted.$.style.float = 'left';
                                highlighted.$.style.marginTop = '0px';
                                highlighted.$.style.marginBottom = '8px';
                                highlighted.$.style.marginLeft = '0px';
                                highlighted.$.style.marginRight = '8px';
                            }
                        });
                editor.ui.addButton('floatleft',
                        {
                            label: 'Float image left',
                            command: 'floatleft',
                            icon: this.path + 'images/float-left.png'
                        });
            }
        });