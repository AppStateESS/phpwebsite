CREATE TABLE block (
id INT DEFAULT '0' NOT NULL ,
key_id INT DEFAULT '0' NOT NULL,
title VARCHAR( 255 ) ,
content TEXT NOT NULL ,
PRIMARY KEY ( id )
);

CREATE TABLE block_pinned (
block_id INT DEFAULT '0' NOT NULL,
key_id INT DEFAULT '0' NOT NULL
);
