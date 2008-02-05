-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE blog_entries (
id INT NOT NULL,
key_id INT NOT NULL,
title VARCHAR( 100 ) NOT NULL ,
summary TEXT NULL,
entry TEXT NOT NULL,
author_id INT NOT NULL default 0,
author varchar(50) NOT NULL,
create_date INT NOT NULL default 0,
updater_id INT NOT NULL default 0,
updater varchar(50) NOT NULL,
update_date INT NOT NULL default 0,
publish_date INT NOT NULL default 0,
expire_date int not null default 0,
allow_comments SMALLINT NOT NULL default 0,
approved INT NOT NULL default 0,
allow_anon SMALLINT NOT NULL default 0,
sticky smallint not null default 0,
image_id INT NOT NULL default 0,
image_link varchar(255) default 'default',
PRIMARY KEY ( id )
);

CREATE INDEX blogentries_idx on blog_entries(key_id);
