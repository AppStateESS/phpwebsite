ALTER TABLE blog_entries ADD author VARCHAR( 40 ) NOT NULL AFTER entry;
UPDATE blog_entries set author='Update me';
