CREATE TABLE blog_entries (
id INT NOT NULL ,
title VARCHAR( 40 ) NOT NULL ,
entry TEXT NOT NULL ,
date INT NOT NULL ,
restricted SMALLINT NOT NULL ,
PRIMARY KEY ( id )
);
