-- rolodex - phpwebsite module
-- @version $Id: $
-- @author Verdon Vaillancourt <verdonv at gmail dot com>

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

