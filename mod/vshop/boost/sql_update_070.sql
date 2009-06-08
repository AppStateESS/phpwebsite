-- vshop - phpwebsite module
-- @version $Id: $
-- @author Verdon Vaillancourt <verdonv at gmail dot com>

CREATE TABLE vshop_option_sets (
    id              int not null default 0,
    title           varchar(255),
    type            int not null default 0,
    PRIMARY KEY (id)
);

CREATE TABLE vshop_option_values (
    id              int not null default 0,
    set_id          int not null default 0,
    sort            int not null default 0,
    title           varchar(255),
    PRIMARY KEY (id)
);

CREATE TABLE vshop_attributes (
    id              int not null default 0,
    set_id          int not null default 0,
    value_id        int not null default 0,
    item_id         int not null default 0,
    price_mod       decimal(11,2) not null default 0.00,
    price_prefix    varchar(1),
    weight_mod      decimal(8,2) not null default 0.00,
    weight_prefix   varchar(1),
    PRIMARY KEY (id)
);

