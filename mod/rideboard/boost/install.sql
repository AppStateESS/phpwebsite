CREATE TABLE rb_ride (
  id int NOT NULL default 0,
  ride_type smallint default 1,
  user_id int NOT NULL default 0,
  s_location int NOT NULL default 0,
  d_location int NOT NULL default 0,
  depart_time int NOT NULL default 0,
  smoking smallint NOT NULL,
  detour smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);


CREATE TABLE rb_location (
id INT NOT NULL default 0,
city_state VARCHAR( 255 ) NOT NULL,
PRIMARY KEY  (id)
);
