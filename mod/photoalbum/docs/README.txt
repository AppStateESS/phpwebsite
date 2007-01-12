-----------------------------------------------------------------------------------
phpWebSite Photo Album Module README
-----------------------------------------------------------------------------------
Author: Steven Levin <steven at NOSPAM tux dot appstate dot edu>

-------------------------------
REQUIREMENTS:
-------------------------------
phpwebsite:
----------------
versions <= 0.62 - phpWebSite v0.9.2 or CVS
versions >= 0.72 - phpWebSite v0.9.3 or CVS
versions >= 1.1.0 - phpWebSite v0.9.3-2 or CVS

GD:
--------
all versions - GD libs 1.6.2 or greater

-------------------------------
INSTALL:
-------------------------------
The photoalbum module install is powered by the boost module installer.

1. Put photoalbum into the mods directory of the phpWebSite base.
2. Point your browser at your site and go to the boost module.
3. Find photoalbum in the list and click install.
4. Enjoy!

-------------------------------
UPGRADE:
-------------------------------
The photoalbum module upgrade is powered by the boost module installer.

1. Put the new photoalbum into the mods directory of the phpWebSite base.
2. Point your browser at your site and go to the boost module.
3. Find photoablum in the list and click update.
4. Boost should do the rest.
5. Enjoy!

Note (if upgrading to version 1.1.0 or greater):
--------------------------------------------------------
Versions 1.1.0 had an update to the way its URL's are formed. As a result 
some internal links may need to be updated including links registered with 
fatcat.  All you must do to update these is just to edit the settings for 
each of your albums which are categorzed and re-save them.  This will update 
the fatcat database with the new link.  Sorry for any inconvenience.
