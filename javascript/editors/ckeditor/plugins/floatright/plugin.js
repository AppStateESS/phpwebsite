CKEDITOR.plugins.add('floatright',
        {
            init: function(editor)
            {
                editor.addCommand('floatright',
                        {
                            exec: function(editor)
                            {
                                var highlighted = editor.getSelection().getSelectedElement();
                                highlighted.$.style.float = 'right';
                                highlighted.$.style.marginTop = '0px';
                                highlighted.$.style.marginBottom = '8px';
                                highlighted.$.style.marginLeft = '8px';
                                highlighted.$.style.marginRight = '0px';
                            }
                        });
                editor.ui.addButton('floatright',
                        {
                            label: 'Float image right',
                            command: 'floatright',
                            icon: this.path + 'images/float-right.png'
                        });
            }
        });