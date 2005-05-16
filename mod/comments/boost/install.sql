CREATE TABLE comments_items (
  id int NOT NULL default '0',
  thread_id int NOT NULL default '0',
  parent int NOT NULL default '0',
  author_ip varchar(15) NOT NULL default '',
  subject varchar(100) NOT NULL default '',
  entry text NOT NULL,
  author_id int NOT NULL default '0',
  edit_author varchar(50) default NULL,
  create_time int NOT NULL default '0',
  edit_time int NOT NULL default '0',
  edit_reason varchar(255) default NULL,
  PRIMARY KEY  (id)
);

CREATE TABLE comments_threads (
  id int NOT NULL default '0',
  module varchar(40) NOT NULL default '',
  item_name varchar(40) NOT NULL default '',
  item_id int NOT NULL default '0',
  source_url varchar(255) NOT NULL default '',
  total_comments int NOT NULL default '0',
  PRIMARY KEY  (id)
);
