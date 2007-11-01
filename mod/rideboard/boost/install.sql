CREATE TABLE rb_driver (
  id int NOT NULL default 0,
  user_id int NOT NULL default 0,
  s_location int NOT NULL default 0,
  d_location int NOT NULL default 0,
  allow_smoking smallint NOT NULL,
  gender_pref smallint NOT NULL,
  comments text,
  contact_email varchar(100) NOT NULL,
  depart_time int NOT NULL default 0,
  return_time int NOT NULL default 0,
  detour_distance smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);

-- --------------------------------------------------------

-- 
-- Table structure for table 'rb_passenger'
-- 

CREATE TABLE rb_passenger (
  id int NOT NULL default 0,
  user_id int NOT NULL default 0,
  s_location int NOT NULL default 0,
  d_location int NOT NULL default 0,
  allow_smoking smallint NOT NULL default 0,
  gender_pref smallint NOT NULL default 0,
  comments text,
  contact_email varchar(100) NOT NULL,
  depart_time int NOT NULL default 0,
  return_time int NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE rb_location (
id INT NOT NULL default 0,
city_state VARCHAR( 255 ) NOT NULL
);
