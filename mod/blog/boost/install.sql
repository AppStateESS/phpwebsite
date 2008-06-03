-- @author Matthew McNaney <mcnaney at gmail dot com>
-- @version $Id$

CREATE TABLE blog_entries (
id INT UNSIGNED NOT NULL,
key_id INT UNSIGNED NOT NULL,
title VARCHAR( 100 ) NOT NULL ,
summary TEXT NULL,
entry TEXT NOT NULL,
author_id INT UNSIGNED NOT NULL default 0,
author varchar(50) NOT NULL,
create_date INT UNSIGNED NOT NULL default 0,
updater_id INT UNSIGNED NOT NULL default 0,
updater varchar(50) NOT NULL,
update_date INT UNSIGNED NOT NULL default 0,
publish_date INT UNSIGNED NOT NULL default 0,
expire_date int unsigned not null default 0,
allow_comments SMALLINT UNSIGNED NOT NULL default 0,
approved INT NOT NULL default 0,
allow_anon SMALLINT NOT NULL default 0,
sticky smallint not null default 0,
image_id INT UNSIGNED NOT NULL default 0,
image_link varchar(255) default 'default',
thumbnail smallint not null default 0,
PRIMARY KEY ( id )
);

CREATE INDEX blogentries_idx on blog_entries(key_id);
