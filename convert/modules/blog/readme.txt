Conversion from Announcements to Blog
-------------------------------------
This conversion copies 0.10.x announcements into the Blog
module.

There are two defines you should be aware of in the convert.php file.

BLOG_BATCH - this is how many announcements to convert at one time. If
you get a time out error or run out of memory (white screen usually),
you should lower this number. The default should be fairly low.

IGNORE_BEFORE - This date must be in a YYYY-MM-DD format. If it is
uncommented, announcements before this date will NOT be
converted. This date is commented out by default. This means every
announcement will get converted. Depending on the age of your site,
the conversion may take several minutes.

This conversion will allow you to use manual or auto mode. Auto mode
is hands off. Manual mode requires you to click continue for each
batch that is converted.

If your conversion fails, you should clear the data like so:

truncate blog_entries;
drop table blog_entries_seq;
truncate phpws_key;
drop table phpws_key_seq;
truncate converted;
truncate search;

Notice that I completely clear search and phpws_key. This is a
all-or-nothing conversion process. If one module fails, we start
conversion completely from the beginning.

If you refuse to follow this advice, you can try to delete rows from
phpws_key and converted based on the "blog" module title. With search,
you will need to cross-reference the phpws_key ids.

Final note: I STRONGLY recommend that you convert a small batch of
announcements before doing your whole site. Uncomment the
IGNORE_BEFORE and set it for a week or so in the past. After the
conversion finishes, go back to your home page and review the results.
