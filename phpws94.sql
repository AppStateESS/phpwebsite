-- MySQL dump 8.22
--
-- Host: localhost    Database: super
---------------------------------------------------------
-- Server version	3.23.56

--
-- Table structure for table 'cache'
--

CREATE TABLE cache (
  title varchar(30) NOT NULL default '',
  id varchar(32) NOT NULL default '',
  data mediumtext NOT NULL,
  ttl int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table 'cache'
--



--
-- Table structure for table 'controlpanel_link'
--

CREATE TABLE controlpanel_link (
  id int(11) NOT NULL default '0',
  label varchar(255) NOT NULL default '',
  active smallint(6) NOT NULL default '1',
  itemname varchar(50) NOT NULL default '',
  restricted smallint(6) NOT NULL default '1',
  tab int(11) NOT NULL default '0',
  url text NOT NULL,
  description text,
  image int(11) NOT NULL default '0',
  link_order smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'controlpanel_link'
--


INSERT INTO controlpanel_link VALUES (8,'User Administration',1,'users',1,2,'index.php?module=users&action[admin]=main','Lets you create and edit users and groups.',2,4);
INSERT INTO controlpanel_link VALUES (9,'Change my settings',1,'users',0,1,'index.php?module=users&norm_user_op=user_options','Allows you to change your email address and password.',2,3);

--
-- Table structure for table 'controlpanel_tab'
--

CREATE TABLE controlpanel_tab (
  id int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  link varchar(255) NOT NULL default '',
  tab_order smallint(6) NOT NULL default '0',
  tabfile varchar(255) default NULL,
  color varchar(50) default NULL,
  itemname varchar(255) NOT NULL default '',
  style varchar(50) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'controlpanel_tab'
--


INSERT INTO controlpanel_tab VALUES (1,'My Settings','index.php?module=controlpanel',1,NULL,NULL,'controlpanel',NULL);
INSERT INTO controlpanel_tab VALUES (2,'Administration','index.php?module=controlpanel',2,NULL,NULL,'controlpanel',NULL);

--
-- Table structure for table 'controlpanel_tab_seq'
--

CREATE TABLE controlpanel_tab_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'controlpanel_tab_seq'
--


INSERT INTO controlpanel_tab_seq VALUES (5);

--
-- Table structure for table 'images'
--

CREATE TABLE images (
  id int(11) NOT NULL default '0',
  owner varchar(20) default NULL,
  editor varchar(20) default NULL,
  ip varchar(20) default NULL,
  created int(11) NOT NULL default '0',
  updated int(11) NOT NULL default '0',
  active smallint(6) NOT NULL default '0',
  approved smallint(6) NOT NULL default '0',
  directory varchar(255) NOT NULL default '',
  filename varchar(255) NOT NULL default '',
  title varchar(255) default NULL,
  width smallint(6) NOT NULL default '0',
  height smallint(6) NOT NULL default '0',
  alt varchar(255) default NULL,
  type smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'images'
--


INSERT INTO images VALUES (1,NULL,NULL,'127.0.0.1',1060630032,1060630032,1,1,'images/mod/users/','down.gif','down.gif',15,16,'down.gif',1);
INSERT INTO images VALUES (2,NULL,NULL,'127.0.0.1',1060777646,1060777646,1,1,'images/mod/users/','users.png','users.png',48,48,'users.png',3);

--
-- Table structure for table 'images_seq'
--

CREATE TABLE images_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'images_seq'
--


INSERT INTO images_seq VALUES (2);

--
-- Table structure for table 'language_en'
--

CREATE TABLE language_en (
  phrase varchar(32) NOT NULL default '',
  translation text NOT NULL,
  active smallint(6) NOT NULL default '0',
  KEY phrase (phrase)
) TYPE=MyISAM;

--
-- Dumping data for table 'language_en'
--


INSERT INTO language_en VALUES ('a48df38ec86b366b526d846e73ef2a3f','Please Login',1);
INSERT INTO language_en VALUES ('f6039d44b29456b20f8f373155ae4973','Username',0);
INSERT INTO language_en VALUES ('dc647eb65e6711e155375218212b3964','Password',0);
INSERT INTO language_en VALUES ('3bbbad631029e3575da7a151bba4f37c','Log In',0);
INSERT INTO language_en VALUES ('9cecc806d7bcf51b47acf2b0b1226933','Hello [var1]',0);
INSERT INTO language_en VALUES ('39f6796e665f9c1cace476535ba6e5bb','Control Panel',0);
INSERT INTO language_en VALUES ('b0c2b25b3312c7a32d7aa9d701b6ae1d','Log Out',0);
INSERT INTO language_en VALUES ('8cf04a9734132302f96da8e113e80ce5','Home',0);
INSERT INTO language_en VALUES ('1f02120804f6e3ae7bd82bfd92507683','The module or url was not set for the link.',0);
INSERT INTO language_en VALUES ('5ae4af9947ebc8cb4a0f07951df03a8d','Image Off',0);
INSERT INTO language_en VALUES ('c30058798fca882f0d3ada203757583f','Desc Off',0);

--
-- Table structure for table 'layout_box'
--

CREATE TABLE layout_box (
  id int(11) NOT NULL default '0',
  theme varchar(40) NOT NULL default '',
  content_var varchar(40) default NULL,
  theme_var varchar(40) default NULL,
  template varchar(40) default NULL,
  box_order smallint(6) NOT NULL default '0',
  active smallint(6) NOT NULL default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'layout_box'
--


INSERT INTO layout_box VALUES (5,'default','CNT_user_small','right_col_top','default_box.tpl',1,1);
INSERT INTO layout_box VALUES (4,'tableless','CNT_user_small','right_col_top','box.tpl',1,1);
INSERT INTO layout_box VALUES (8,'clean','CNT_user_small','left_col_top','default_box.tpl',1,1);
INSERT INTO layout_box VALUES (1,'newdef','CNT_user_small','BODY','default_box.tpl',1,1);
INSERT INTO layout_box VALUES (2,'default','User_Main','BODY','default_box.tpl',1,1);

--
-- Table structure for table 'layout_box_seq'
--

CREATE TABLE layout_box_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'layout_box_seq'
--


INSERT INTO layout_box_seq VALUES (2);

--
-- Table structure for table 'layout_config'
--

CREATE TABLE layout_config (
  default_theme varchar(50) NOT NULL default '',
  userAllow smallint(6) NOT NULL default '1',
  page_title varchar(255) default NULL,
  meta_keywords text,
  meta_description varchar(180) default NULL,
  meta_robots char(2) default NULL,
  meta_owner varchar(40) default NULL,
  meta_author varchar(40) default NULL,
  meta_content varchar(40) NOT NULL default ''
) TYPE=MyISAM;

--
-- Dumping data for table 'layout_config'
--


INSERT INTO layout_config VALUES ('tableless',1,'phpWebSite','phpwebsite',NULL,'11',NULL,NULL,'ISO-8859-1');

--
-- Table structure for table 'modules'
--

CREATE TABLE modules (
  title varchar(40) NOT NULL default '',
  priority smallint(6) NOT NULL default '0',
  active smallint(6) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table 'modules'
--


INSERT INTO modules VALUES ('layout',99,1);
INSERT INTO modules VALUES ('users',5,1);
INSERT INTO modules VALUES ('controlpanel',50,1);
INSERT INTO modules VALUES ('language',1,1);

--
-- Table structure for table 'user_groups'
--

CREATE TABLE user_groups (
  id int(11) NOT NULL default '0',
  active smallint(6) NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  user_id int(6) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_groups'
--


INSERT INTO user_groups VALUES (1,1,'matt',2);
INSERT INTO user_groups VALUES (2,1,'Fatty Group',0);
INSERT INTO user_groups VALUES (3,1,'Anonymous',1);
INSERT INTO user_groups VALUES (4,1,'larry',3);
INSERT INTO user_groups VALUES (5,1,'badasste@#d',4);
INSERT INTO user_groups VALUES (6,1,'thomas',5);
INSERT INTO user_groups VALUES (7,1,'Mercury',6);
INSERT INTO user_groups VALUES (8,1,'admin',7);

--
-- Table structure for table 'user_groups_seq'
--

CREATE TABLE user_groups_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'user_groups_seq'
--


INSERT INTO user_groups_seq VALUES (8);

--
-- Table structure for table 'user_members'
--

CREATE TABLE user_members (
  group_id int(11) NOT NULL default '0',
  member_id int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table 'user_members'
--


INSERT INTO user_members VALUES (2,1);

--
-- Table structure for table 'users'
--

CREATE TABLE users (
  id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  updated int(11) NOT NULL default '0',
  active smallint(6) NOT NULL default '0',
  approved smallint(6) NOT NULL default '0',
  username varchar(30) NOT NULL default '',
  password varchar(32) default NULL,
  deity smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'users'
--


INSERT INTO users VALUES (1,0,0,1,1,'Anonymous',NULL,0);
INSERT INTO users VALUES (7,1061583098,1061583098,1,1,'admin','b4474e3b4b42dce8ab06db3e83066662',1);

--
-- Table structure for table 'users_item_permissions'
--

CREATE TABLE users_item_permissions (
  group_id int(11) NOT NULL default '0',
  item_id int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table 'users_item_permissions'
--



--
-- Table structure for table 'users_permissions'
--

CREATE TABLE users_permissions (
  group_id int(11) NOT NULL default '0',
  add_edit_users smallint(6) NOT NULL default '0',
  delete_users smallint(6) NOT NULL default '0',
  add_edit_groups smallint(6) NOT NULL default '0',
  delete_groups smallint(6) NOT NULL default '0',
  edit_permissions smallint(6) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table 'users_permissions'
--


INSERT INTO users_permissions VALUES (1,1,1,0,0,0);
INSERT INTO users_permissions VALUES (2,0,0,0,2,0);

--
-- Table structure for table 'users_seq'
--

CREATE TABLE users_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'users_seq'
--


INSERT INTO users_seq VALUES (8);

--
-- Table structure for table 'users_settings'
--

CREATE TABLE users_settings (
  anonymous int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table 'users_settings'
--


INSERT INTO users_settings VALUES (1);

