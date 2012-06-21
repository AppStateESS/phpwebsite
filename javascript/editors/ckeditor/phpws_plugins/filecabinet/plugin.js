CKEDITOR.plugins.add('filecabinet',
   {
      requires : ['iframedialog'],
      init : function(editor) {
         var pluginName = 'filecabinet';
         var mypath = this.path;
         editor.ui.addButton(
            'Filecabinet',
            {
               label : "Filecabinet",
               command : 'filecabinet.cmd',
               icon : mypath + 'images/filecabinet.jpg'
            }
         );
         var cmd = editor.addCommand('filecabinet.cmd', {exec:showDialogPlugin});
         CKEDITOR.dialog.addIframe(
            'filecabinet.dlg',
            'Filecabinet',
            'index.php?module=filecabinet&aop=ckeditor',
            800,
            600,
            function(){
            }
         );
      }
   }
);

function showDialogPlugin(e){
   e.openDialog('filecabinet.dlg');
}