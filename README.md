Welcome to phpWebSite!
======================

phpWebSite comes with its own set of Pear files. Most of the time you
should just use the files included with distribution.

After installation you will need to install some content modules.

Login and go to the Administration tab.
Click on Boost.
Click on Other Modules
Install any of the modules included. We recommend you try them all.

Remember after you install to restrict your directory permissions.

files/
images/
logs/
^ Should be writable to by apache.

javascript/
templates/
config/
^ Should be writable ONLY during module or core installs/updates/uninstalls

logs/     should NOT be world readable
setup/    should be moved or made unreadable AFTER an installation
convert/  should be removed after converting

> Before updating the core, make sure to make a copy of your
> config/core directory or some of your settings could get
> overwritten.

If you are upgrading, the only file you must keep in your config/core
directory is language.php.

phpWebSite uses UTF-8 character encoding. Some older database versions
of MySQL (pre 4.1) do not support it.
