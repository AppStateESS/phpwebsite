// Our method which is called during initialization of the toolbar.
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
	window.open('../../../../index.php?module=filecabinet&instance=' + FCK.Name + '&aop=fckeditor', 'File Cabinet', 'top=0,left=0,screenY=0,screenX=0,scrollbars=yes,menubar=yes,location=no,resizable=yes,width=640,height=480');
}

// Register the command.
FCKCommands.RegisterCommand('filecabinet', new Filecabinet());

// Add the button.
var item = new FCKToolbarButton('filecabinet', 'File Cabinet');
item.IconPath = FCKPlugins.Items['filecabinet'].Path + 'cabinet.png';
FCKToolbarItems.RegisterItem('filecabinet', item);
