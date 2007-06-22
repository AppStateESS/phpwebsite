The files in this directory allow the FCKeditor to work in phpWebSite.

We do not supply the editor. You will need to download it from here:
http://www.fckeditor.net/

Unzip the file into this directory. You should see _something_ like
the following when reading the directory contents:

body.js
default.php
_docs (dir)
editor (dir)
fckconfig.js
fckeditor.js
fckstyles.xml
head.js
license.txt
_packager (dir)
readme.txt (this file)

You should be able to remove any other files and folders.

--> DO NOT remove the license.txt file. <--

The "custom.php" file in the editor directory allows you to change the
default options for FCKeditor.

***  Make sure to read spellcheck_readme.txt ***
***        to enable the spellchecker.       ***

The word 'content' is restricted. Do not use it as the name of
your text area.

Style choices are set in editor/phpwsstyles.xml. The format should be
easy to understand. Please be aware that if your theme does not have
class definitions for float-left, float-right, smaller, and larger,
those styles will not work. Rename the classes in the xml file if you
have other plans for your style sheets.

Please see the Editors.txt file in the docs/ directory for more
information.

One final word: FCK's ease of use is due to the hard work of the
FCKeditor team. If one of the main reasons you enjoy working in
phpWebSite is because of the editor, PLEASE support (i.e. send money
to) the FCKeditor team.
