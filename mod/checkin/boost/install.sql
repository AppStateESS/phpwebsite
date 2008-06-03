CREATE TABLE checkin_staff (
  id int unsigned NOT NULL default 0,
  user_id int unsigned NOT NULL default 0,
  filter varchar(50) default NULL,
  available smallint NOT NULL default 0,
  visitor_id int unsigned NOT NULL default 0,
  filter_type smallint NOT NULL default 0,
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id)
)

CREATE TABLE checkin_reasons (
  id int unsigned NOT NULL default 0,
  summary varchar(255) NOT NULL default ''
  PRIMARY KEY  (id)
);

CREATE TABLE checkin_visitor (
  id int unsigned NOT NULL default 0,
  firstname varchar(80) NOT NULL default '',
  lastname varchar(80) NOT NULL default '',
  reason smallint unsigned NOT NULL default 0,
  arrival_time int unsigned NOT NULL default 0,
  start_meeting int unsigned NOT NULL default 0,
  end_meeting int unsigned NOT NULL default 0,
  assigned int NOT NULL default 0,
  note text,
  finished smallint unsigned NOT NULL default 0,
  PRIMARY KEY  (id)
);

CREATE TABLE checkin_rtos (
  reason_id INT UNSIGNED NOT NULL default 0,
  staff_id INT UNSIGNED NOT NULL default 0
);