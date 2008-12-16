PHPWSBB CONVERSION FROM VER. 1.0.3 (and 1.0.4) TO 2.0
-----------------------------------------------------------------------------
This conversion copies phpws Bulletin Board forums & topics into the 
phpWsbb 2.0 version.

There are two defines you should be aware of in the convert.php file.

BB_USER_BATCH_LIMIT - this is how many users' information to convert at one 
time.  If you get a time out error or run out of memory (white screen usually),
you should lower this number. The default should be fairly low.

BB_THREAD_BATCH_LIMIT - this is how many threads to convert at one time. If
you get a time out error or run out of memory (white screen usually),
you should lower this number. The default should be fairly low.

BAN_IPS - If you want to import ip-based bans, set this to true.
Note: Be aware that some ISPs identify all user traffic by 1 ip address.
Blocking it would prevent *all* of their users from accessing your website!

This conversion will allow you to use manual or auto mode. Auto mode
is hands off. Manual mode requires you to click continue for each
batch that is converted.


-----------------------------------------------------------------------------
PRE_CONVERSION CHECKLIST
-----------------------------------------------------------------------------
-	Convert *ALL* other modules that  use Comments first!  All comment data 
    must be transferred over to the new site before phpwsbb starts new numbers 
    for the comments it will be creating.

-	Be sure to copy the contents of your old /images/phpwsbb directory to the
	/convert/images/phpwsbb (which you will create) directory on the new site.  
	Check to make sure that the file/folder permissions don't change.
	You can delete this directory after conversion is complete.

-	Back up your entire database first!
	There is no SQL undo script because the best way of reverting data is to 
	restore the entire database from a recent backup.
	If you haven't done so yet, install a copy of a database utility (like 
	phpMyAdmin) & learn how to use it.  It will make your life MUCH easier!
	
-   Install phpwsbb & review the module's Control Panel settings.  The old 
    settings will not be converted automatically because many things have 
    changed.  The User Ranking system will also not be converted.  Its a LOT
    more powerful now (multiple group-based ranks), so its best if you re-enter 
    it manually.

-----------------------------------------------------------------------------
WHAT TO DO IF THE CONVERSION FAILS
-----------------------------------------------------------------------------
Sometimes the easiest way to recover from a failed conversion is to restore 
the entire database from the backup that you just made.  That way you don't 
have to start from scratch and re-convert all of the other modules that 
you've already converted.
