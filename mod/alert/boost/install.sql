CREATE TABLE alert_participant (
id INT NOT NULL default 0 ,
email VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( id )
);

ALTER TABLE alert_participant ADD UNIQUE (email);

CREATE TABLE alert_prt_to_type (
prt_id INT NOT NULL default 0 ,
type_id INT NOT NULL default 0 ,
INDEX ( prt_id , type_id )
);


CREATE TABLE alert_contact (
prt_id INT NOT NULL default 0 ,
item_id INT NOT NULL default 0,
email varchar(60) NOT NULL
);

CREATE TABLE alert_item (
id INT NOT NULL default 0 ,
title VARCHAR( 255 ) NOT NULL ,
description TEXT NOT NULL ,
image_id INT NOT NULL default 0,
create_date INT NOT NULL default 0,
update_date INT NOT NULL default 0,
created_by_id INT NOT NULL default 0,
created_name varchar(50) not null,
updated_by_id INT NOT NULL default 0,
updated_name varchar(50) not null,
type_id INT NOT NULL default 0,
contact_complete INT NOT NULL default 0,
active SMALLINT NOT NULL default 0,
PRIMARY KEY ( id )
);

CREATE TABLE alert_type (
id INT NOT NULL default 0 ,
title VARCHAR( 255 ) NOT NULL ,
email SMALLINT NOT NULL default 0 ,
rssfeed SMALLINT NOT NULL default 0 ,
post_type SMALLINT NOT NULL default 0 ,
default_alert TEXT NULL ,
PRIMARY KEY ( id )
);
