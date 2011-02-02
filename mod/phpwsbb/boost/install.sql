-- $Id: install.sql,v 1.1 2008/08/23 04:19:20 adarkling Exp $

CREATE TABLE phpwsbb_forums (
 id int NOT NULL default 0,
 key_id int NOT NULL default 0,
 is_phpwsbb int NOT NULL default 1, 
 title varchar(255) NOT NULL default '',
 description varchar(255) NOT NULL default '',
 topics int NOT NULL default 0,
 sortorder int NOT NULL default 0,
 posts int NOT NULL default 0,
 moderators varchar(255) NOT NULL default '',
 allow_anon smallint NOT NULL default 0,
 default_approval smallint NOT NULL default 0,
 locked smallint NOT NULL default 0,
 PRIMARY KEY (id)
);

CREATE TABLE phpwsbb_topics (
 id int NOT NULL default 0,
 key_id int NOT NULL default 0,
 is_phpwsbb smallint NOT NULL default 1,
 fid int NOT NULL default 0,
 sticky smallint NOT NULL default 0,
 locked smallint NOT NULL default 0,
 total_posts int NOT NULL default 0,
 lastpost_post_id int NOT NULL default 0,
 lastpost_date int NOT NULL default 0,
 lastpost_author_id int NOT NULL default 0,
 lastpost_author varchar(50),
 PRIMARY KEY (id)
);
CREATE INDEX phpwsbb_topics_fid_idx ON phpwsbb_topics (fid);
CREATE INDEX phpwsbb_topics_key_id_idx ON phpwsbb_topics (key_id);

CREATE TABLE phpwsbb_users (
 user_id int NOT NULL,
 last_on int default 0,
 last_activity int default 0,
 PRIMARY KEY (user_id)
);

CREATE TABLE phpwsbb_moderators (
 forum_id int NOT NULL default 0,
 user_id int NOT NULL default 0,
 username varchar(255) NOT NULL
);
CREATE INDEX phpwsbb_moderators_forum_id_idx ON phpwsbb_moderators (forum_id);
CREATE INDEX phpwsbb_moderators_user_id_idx ON phpwsbb_moderators (user_id);


