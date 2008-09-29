CREATE TABLE rb_ride (
  id int NOT NULL default 0,
  title varchar(255) default NULL,
  ride_type smallint default 1,
  user_id int NOT NULL default 0,
  s_location int NOT NULL default 0,
  d_location int NOT NULL default 0,
  depart_time int NOT NULL default 0,
  smoking smallint NOT NULL,
  comments text,
  detour smallint NOT NULL default 0,
  marked smallint NOT NULL default 0,
  gender_pref smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);


CREATE TABLE rb_location (
id INT NOT NULL default 0,
city_state VARCHAR( 255 ) NOT NULL,
PRIMARY KEY  (id)
);

CREATE TABLE rb_carpool (
id INT NOT NULL,
user_id INT NOT NULL default 0,
email VARCHAR( 255 ) NOT NULL,
created INT NOT NULL default 0,
start_address VARCHAR( 255 ) NOT NULL,
dest_address VARCHAR( 255 ) NOT NULL,
comment TEXT,
PRIMARY KEY ( id )
);