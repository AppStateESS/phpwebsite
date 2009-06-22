-- vlist - phpwebsite module
-- @version $Id$
-- @author Verdon Vaillancourt <verdonv at gmail dot com>

CREATE TABLE vlist_listing (
    id              int not null default 0,
    key_id          int not null default 0,
    created         int NOT NULL default 0,
    updated         int NOT NULL default 0,
    owner_id        int NOT NULL default 0,
    editor_id       int NOT NULL default 0,
    active          smallint not null default 0,
    title           varchar(255),
    description     text,
    file_id         int not null default 0,
    image_id        int not null default 0,
    PRIMARY KEY     (id)
);

CREATE TABLE vlist_group (
    id              int not null default 0,
    title           varchar(255) not null,
    description     text,
    image_id        int not null default 0,
    PRIMARY KEY     (id)
);

CREATE TABLE vlist_group_items (
    group_id       int NOT NULL default 0,
    listing_id      int NOT NULL default 0
);

CREATE TABLE vlist_element (
    id              int not null default 0,
    title           varchar(255) not null,
    type            varchar(80) not null,
    value           varchar(255),
    active          smallint not null default 0,
    required        smallint not null default 0,
    numoptions      int not null default 0,
    size            int not null default 0,
    maxsize         int not null default 0,
    rows            int not null default 0,
    cols            int not null default 0,
    sort            int not null default 0,
    list            smallint not null default 0,
    search          smallint not null default 0,
    private         smallint not null default 0,
    PRIMARY KEY     (id)
);

CREATE TABLE vlist_element_option (
    id              int not null default 0,
    element_id      int NOT NULL default 0,
    label           varchar(255) not null,
    sort            int not null default 0,
    PRIMARY KEY     (id)
);

CREATE TABLE vlist_element_items (
    element_id      int NOT NULL default 0,
    option_id       int NOT NULL default 0,
    listing_id      int NOT NULL default 0,
    value           text
);
