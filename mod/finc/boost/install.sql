CREATE TABLE finc_file (
    id int not null default 0,
    key_id int not null default 0,
    active smallint NOT NULL default 0,
    title varchar(80) not null,
    path varchar(255) not null,
    description text,
    PRIMARY KEY  (id)
);

CREATE INDEX fincfile_idx on finc_file(key_id);

