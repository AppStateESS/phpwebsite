CREATE TABLE blog_entries (
id INT NOT NULL,
key_id INT NOT NULL,
title VARCHAR( 40 ) NOT NULL ,
entry TEXT NOT NULL ,
author varchar(50) NOT NULL default '',
create_date INT NOT NULL ,
allow_comments SMALLINT NOT NULL default'0',
PRIMARY KEY ( id )
);
