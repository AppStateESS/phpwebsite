CREATE TABLE blog_entries (
id INT NOT NULL,
key_id INT NOT NULL,
title VARCHAR( 40 ) NOT NULL ,
entry TEXT NOT NULL ,
author varchar(50) NOT NULL default '',
date INT NOT NULL ,
PRIMARY KEY ( id )
);
