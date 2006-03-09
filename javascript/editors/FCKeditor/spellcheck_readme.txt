Enabling spellchecking
by Matthew McNaney

To enable spellchecking with FCKeditor you need to perform a little
work.

Look in javascript/editors/FCKeditor/editor/custom.php

This is the configuration file for FCKeditor. You can set up the
toolbar as you wish. Notice the SpellCheck entry. If you can't get
spell check to work you may want to remove it.

Look below that for FCKConfig.SpellChecker. Again, if you can't get
the script to work, you can comment this out.

Now look in:
javascript/editors/FCKeditor/editor/dialog/fck_spellerpages/spellerpages/server-scripts/spellchecker.php

Look in the file for:
//$aspell_prog = 'aspell';									// by FredCK (for Linux)
$aspell_prog = '"C:\Program Files\Aspell\bin\aspell.exe"';	// by FredCK (for Windows)

FCK defaults to a windows server (heh). If that is the case,
then just make sure that aspell.exe is in the proper directory. Edit
the directory path if not.

If you are using li/unix, comment the Windows path out and uncomment the
previous line.

If you are not sure if you have aspell then, in unix,
'which aspell'

In Windows, ask the helpful animated doggie to search for it.

If you don't have aspell go here:
http://aspell.net/

One last thing, I haven't tried this in other languages. There is a
$lang variable if you wish to test.
