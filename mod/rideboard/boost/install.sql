CREATE TABLE rb_ride (
  id int unsigned NOT NULL default 0,
  title varchar(255) default NULL,
  ride_type smallint unsigned default 1,
  user_id int unsigned NOT NULL default 0,
  s_location int unsigned NOT NULL default 0,
  d_location int unsigned NOT NULL default 0,
  depart_time int unsigned NOT NULL default 0,
  smoking smallint NOT NULL,
  comments text,
  detour smallint NOT NULL default 0,
  marked smallint NOT NULL default 0,
  gender_pref smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);


CREATE TABLE rb_location (
id INT UNSIGNED NOT NULL default 0,
city_state VARCHAR( 255 ) NOT NULL,
PRIMARY KEY  (id)
);
