CREATE TABLE signup_sheet (
    id int unsigned not null default 0,
    key_id int unsigned not null default 0,
    title varchar(80) not null,
    description text,
    start_time int unsigned not null default 0,
    end_time int unsigned not null default 0,
    primary key (id)
);

CREATE TABLE signup_peeps (
  id int unsigned NOT NULL default 0,
  sheet_id int unsigned NOT NULL default 0,
  slot_id int unsigned NOT NULL default 0,
  first_name varchar(60) NOT NULL,
  last_name varchar(60) NOT NULL,
  email varchar(100) NOT NULL,
  phone varchar(30) NOT NULL,
  organization varchar(100) default NULL,
  hashcheck char(32) default NULL,
  timeout int unsigned NOT NULL default 0,
  registered smallint NOT NULL default 0,
  PRIMARY KEY  (id)
);


CREATE TABLE signup_slots (
    id int unsigned not null default 0,
    sheet_id int unsigned not null default 0,
    title varchar(80) not null,
    openings int unsigned not null default 0,
    s_order smallint unsigned not null default 1,
    primary key (id)
);
