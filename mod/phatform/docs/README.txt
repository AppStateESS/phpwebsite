Phatform Readme
---------------

ATTENTION
---------
Make sure you have read and understand the requirements and
installation procedures for phatform.

This document is meant to cover the basic functionality and
features of the phatform module.  It is written in a question
answer format.  If you need help accessing the features covered
here, check out the user manual in mod/phatform/docs/MANUAL.txt

- What is Phatform?
  Phatform is a form generation module built for phpWebSite. It
  is designed to make it easy for users to convert any existing
  form-like document into an online version of the form.  Once
  the form is created, users of your phpWebSite site can visit
  and fill out the form you created.

- Has anyone else used this software before?
  Yes. This software has been tested in a production environment
  at Appalachian State University.  The test run was actually quite
  interesting.  A form from the old phatform v1 was converted into
  a form for phatform v2. This form contained around 153 quiestions
  and was survey like in nature.  After the form was converted, a
  user of the software added several more questions to it and moved
  the new elements around to get them exactly where they wanted
  them.  Once the form was complete and ready for the public an email
  was sent to around 2000+ users to visit the site where the form
  was located and fill it out.  Out of the users who were sent the
  request, over 900 visited the site and successfully filled out the
  form.  They were using an array of browsers, OSs, and
  internet connections.  No one reported any problems.

- What type of properties can a form have?
  Phatform can create forms with many different properties:

  1. A form can be an anonymous form.  Like in the case of an
     anonymous survey.  This allows any user that visits your
     site to fill out the form and no information is stored
     about the identity of that user.

  2. A form can be set to only allow registered users on your
     site access to fill out the form.  In this case as the
     user enters their information, phatform stores the username
     of the user, giving the administrator a way to identify
     the person who submitted that data.

  3. A form can allow users to return and edit their data they
     submitted.  In this case, the form cannot allow anonymous
     submissions, since phatform needs a method of identifying
     the user's data in the database.  An example of a form like
     this might be a membership form for a club.  If a member of
     the club has an address or phone number change, they can
     return to the site, log in, and change their data.

  4. A form can have multiple pages. A page limit can be set
     which constrains the number of form elements shown per page
     in the form.

  5. A form can have a blurb of text representing instructions
     for the users of the form.  This text is displayed on the
     first page of the form.

  6. A form can have a blurb of text representing a submission
     message. This text is displayed whrn the user finishes their
     submission to the form.  It can be anything from "Thank you"
     to "Data Saved"...even "Thanks now buzz off!".

  7. Forms can have a variety of elements in them. Dropboxes,
     textfields, textareas, checkboxes, radiobuttons, and
     multiple selection boxes are the current available elements.

  8. Form page numbers can be turned on or off.

  9. Form elements can be automagically numbered or not.

- How do I view data that my users have submitted?
  Phatform has an integrated reporting side that allows extensive
  manipulation and viewing of the data contained in your form.
  This feature is only available to saved forms since they are
  the only ones who should have data to report on.

- The reporting side just isn't enough...can I easily get the data
  and do my own reporting?
  Yes.  The reporting side of phatform has an export feature which
  allows you to download an archive file directly from your site
  containing the data from your form.  Within the archive file is
  a tab delimited text file which is easily imported into any
  spreadsheet program. Once imported, you can manipulate and
  examine the data til your heart is content.

- I was using a microsoft database solution and lost all my form data!
  What do I do?
  First, switch to a *nix based solution.  Second, phatform has
  archiving features which make it fairly painless to recover a lost
  form.  After saving a form in phatform, you can archive the form
  in a file in mod/phatform/archive/branchname/.  This file contains
  database dumps of your entire form structure and data that was
  collected from users. This feature is only powerful if the administrator
  of the forms remembers to archive their forms periodically.

- I think I found a bug in phatform...what do I do?
  First, make sure you can recreate the bug.  If you can, visit
  http://sourceforge.net/ and locate the phpWebSite project. Under
  the 'Bugs' section, submit a new bug with the word 'phatform' in the
  subject somewhere.  Make sure you list your server information including
  OS, web server software type and version, database type and version, PHP
  version, and PEAR version.  Include a detailed decription of a method
  where we can recreate your bug in our test environment.
