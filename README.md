Welcome to phpWebSite!
======================

* [phpWebSite Home](https://phpwebsite.appstate.edu)
* [phpWebSite Continuous Integration](https://code.appstate.edu/jenkins/job/phpwebsite)
* [phpWebSite on Github](https://github.com/AppStateESS/phpwebsite)

phpWebSite comes with its own set of Pear files. Most of the time you
should just use the files included with distribution.

After installation you will need to install some content modules.

1. Login and go to the Administration tab.
2. Click on Boost.
3. Click on Other Modules
4. Install any of the modules included. We recommend you try them all.

**Remember after you install to restrict your directory permissions.**

These directories should always be writable and executable by Apache:
* files/
* images/
* logs/

These directories should be writable and executable by Apache only during module or core installs, updates, and uninstalls:
* javascript/
* templates/

Additional permission suggestions:
* logs/     should NOT be world readable
* setup/    should be moved or made unreadable AFTER an installation
* convert/  should be removed after converting

**Before updating the core, make sure to make a copy of your
config/core directory or some of your settings could get
overwritten.**

If you are upgrading, the only file you must keep in your config/core
directory is language.php.

phpWebSite uses UTF-8 character encoding. Some older database versions
of MySQL (pre 4.1) do not support it.
