CREATE TABLE block (
id INT DEFAULT '0' NOT NULL ,
title VARCHAR( 255 ) ,
content TEXT NOT NULL ,
PRIMARY KEY ( id )
);

CREATE TABLE block_pinned (
block_id INT DEFAULT '0' NOT NULL ,
module VARCHAR( 40 ) NOT NULL ,
itemname VARCHAR( 40 ) NOT NULL ,
item_id INT DEFAULT '0' NOT NULL
);
