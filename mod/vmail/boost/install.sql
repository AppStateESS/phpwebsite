CREATE TABLE vmail_recipients (
    id              int not null default 0,
    key_id          int not null default 0,
    label           varchar(255),
    address         varchar(255),
    prefix          varchar(255),
    subject         varchar(255),
    submit_message  text,
    lock_subject    smallint NOT NULL default 0,
    active          smallint NOT NULL default 1,
    PRIMARY KEY   (id)
);
