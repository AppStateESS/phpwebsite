// Our method which is called during initialization of the toolbar
var fc_width  = 300;
var fc_height = 500;

function Filecabinet()
{
}

// Disable button toggling.
Filecabinet.prototype.GetState = function()
{
    return FCK_TRISTATE_OFF;
}

// Our method which is called on button click.
Filecabinet.prototype.Execute = function()
{
    x = 320;
    y = 240;
    
    if (screen) {
        y = (screen.availHeight - fc_height)/2;
        x = (screen.availWidth - fc_width)/2;
    }
    
    window.open('../../../../index.php?module=filecabinet&instance=' + FCK.Name + '&aop=fckeditor', 'File Cabinet', 'top='+ y +',left='+ x +',screenY='+ y +',screenX='+ x +',toolbar=no,titlebar=no,scrollbars=yes,menubar=no,location=no,resizable=yes,width='+fc_width+',height='+fc_height);
}

// Register the command.
FCKCommands.RegisterCommand('filecabinet', new Filecabinet());

// Add the button.
var item = new FCKToolbarButton('filecabinet', 'File Cabinet');
item.IconPath = FCKPlugins.Items['filecabinet'].Path + 'cabinet.png';
FCKToolbarItems.RegisterItem('filecabinet', item);
