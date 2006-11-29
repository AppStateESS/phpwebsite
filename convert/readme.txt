Conversion
------------------------
Each module has its own convert directory

base/convert/modules/blog
base/convert/modules/block

Make sure to check each for a "readme" file.

None of the convert modules copy files from the previous version. It
is up to you to move images and documents to your new installation.

If there is a problem with your conversion, I recommend that you
do not continue with other mods until you can fix it. 

If you have perform the conversion more than once, you must clear the
tables and drop the sequence tables. All the conversions assume you
are starting from scratch and you haven't enter ANY content.

Go slow, backup your data, and test heavily before going live.

When you are satified with the results, you may want to move the
convert directory elsewhere, remove read priviledges, or delete it
entirely.

You must installed phpwebsite 1.0 successfully _before_ attempting to
convert modules. Make sure the modules you are trying to convert have
been installed on the new version.
