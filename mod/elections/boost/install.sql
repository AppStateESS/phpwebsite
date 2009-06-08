CREATE TABLE elections_ballots (
    id              int not null default 0,
    key_id          int not null default 0,
    title           varchar(255),
    description     text,
    image_id        int not null default 0,
    pubview         smallint not null default 0,
    pubvote         smallint not null default 0,
    votegroups      text default null,
    opening         int not null default 0,
    closing         int not null default 0,
    showin_block    smallint not null default 1,
    minchoice       int not null default 1,
    maxchoice       int not null default 1,
    ranking         smallint not null default 0,
    custom1label    varchar(255),
    custom2label    varchar(255),
    custom3label    varchar(255),
    custom4label    varchar(255),
    PRIMARY KEY   (id)
);

CREATE INDEX electionsballots_idx on elections_ballots(key_id);

CREATE TABLE elections_candidates (
    id              int not null default 0,
    ballot_id       int not null default 0,
    title           varchar(255),
    description     text,
    image_id        int not null default 0,
    votes           int not null default 0,
    custom1         varchar(255),
    custom2         varchar(255),
    custom3         varchar(255),
    custom4         varchar(255),
    PRIMARY KEY (id),
    index (ballot_id)
);

CREATE TABLE elections_votes (
    id              int not null default 0,
    ballot_id       int not null default 0,
    username        varchar(255),
    votedate        int not null default 0,
    ip              varchar(255) null,
    PRIMARY KEY (id),
    index (ballot_id)
);
