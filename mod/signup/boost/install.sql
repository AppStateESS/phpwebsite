CREATE TABLE signup_sheet (
    id int not null default 0,
    key_id int not null default 0,
    title varchar(80),
    description text,
    start_time int not null default 0,
    end_time int not null default 0,
    primary key (id)
);

CREATE TABLE signup_peeps (
    id int not null default 0,
    form_id int not null default 0,
    slot_id int not null default 0,
    hash char(32),
    timeout int not null default 0,
    primary key (id)
);


CREATE TABLE signup_slots (
    id int not null default 0,
    form_id int not null default 0,
    start_time int not null default 0,
    duration smallint not null default 0,
    primary key (id)
);
