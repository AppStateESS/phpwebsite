CREATE TABLE rolodex_member (
    user_id int not null default 0,
    key_id int not null default 0,
    allow_comments smallint NOT NULL default 0,
    allow_anon smallint NOT NULL default 0,
    date_created int NOT NULL default 0,
    date_updated int NOT NULL default 0,
    date_expires int NOT NULL,
    description text,
    image text,
    privacy smallint NOT NULL default 0,
    email_privacy smallint NOT NULL default 0,
    active smallint NOT NULL default 0,
    custom1 varchar(255),
    custom2 varchar(255),
    custom3 varchar(255),
    custom4 varchar(255),
    custom5 varchar(255),
    custom6 varchar(255),
    custom7 varchar(255),
    custom8 varchar(255)

);

CREATE INDEX rolodexmember_idx on rolodex_member(key_id);

CREATE TABLE rolodex_location (
    id int not null default 0,
    title varchar(80) not null,
    description text,
    image_id INT NOT NULL default 0,
    PRIMARY KEY  (id)
);

CREATE TABLE rolodex_feature (
    id int not null default 0,
    title varchar(80) not null,
    description text,
    image_id INT NOT NULL default 0,
    PRIMARY KEY  (id)
);

CREATE TABLE rolodex_location_items (
    location_id int NOT NULL default 0,
    member_id int NOT NULL default 0
);

CREATE TABLE rolodex_feature_items (
    feature_id int NOT NULL default 0,
    member_id int NOT NULL default 0
);

