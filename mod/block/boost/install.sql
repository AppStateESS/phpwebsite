-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE block (
id INT DEFAULT 0 NOT NULL ,
key_id INT DEFAULT 0 NOT NULL,
title VARCHAR( 255 ),
content TEXT NOT NULL,
file_id int not null default 0,
hide_title smallint not null default 0,
PRIMARY KEY ( id )
);

CREATE TABLE block_pinned (
block_id INT DEFAULT 0 NOT NULL,
key_id INT DEFAULT 0 NOT NULL
);
