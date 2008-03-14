Enabling spellchecking
by Matthew McNaney

--------------------------------------------------------------------
Update: future versions of phpwebsite will ship with the Unix file
uncommented. If you are running a Windows version of FCKeditor you
will need to do the opposite of the below. Uncomment the 
"C:\\Program Files\etc." line instead.

If you are running Unix and update FCK, expect to have to edit this
file again.
--------------------------------------------------------------------

To enable spellchecking with FCKeditor you need to perform a little
work.

Look in javascript/editors/fckeditor/editor/custom.php

This is the configuration file for FCKeditor. You can set up the
toolbar as you wish. Notice the SpellCheck entry in the ToolbarSets.
If you can't get spell check to work you may want to remove it.

Look below that for FCKConfig.SpellChecker. Again, if you can't get
the script to work, you can comment this out.

Now look in:
javascript/editors/fckeditor/editor/dialog/fck_spellerpages/spellerpages/server-scripts/spellchecker.php

This file comes from FCKeditor so we can't offer a default version (it
would get overwritten when you unzip FCK).

Look in the file for:
//$aspell_prog = 'aspell';									// by FredCK (for Linux)
$aspell_prog = '"C:\Program Files\Aspell\bin\aspell.exe"';	// by FredCK (for Windows)

FCK defaults to a windows server. If you are using Windows, then just
make sure that aspell.exe is in the proper directory. Edit the
directory path if not.

If you are using linux/unix, comment the Windows path out and
uncomment the previous line.

It should look like this:
$aspell_prog = 'aspell';
//$aspell_prog = '"C:\Program Files\Aspell\bin\aspell.exe"';	// by FredCK (for Windows)

If you are not sure if you have aspell then, in unix,
'which aspell'

In Windows, ask the helpful animated doggie to search for it.

If you don't have aspell go here:
http://aspell.net/

One last thing, I haven't tried this in other languages. There is a
$lang variable if you wish to test.
