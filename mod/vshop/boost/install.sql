CREATE TABLE vshop_depts (
    id              int not null default 0,
    key_id          int not null default 0,
    title           varchar(255),
    description     text,
    file_id         int not null default 0,
    PRIMARY KEY   (id)
);

CREATE TABLE vshop_items (
    id              int not null default 0,
    key_id          int not null default 0,
    dept_id         int not null default 0,
    title           varchar(255),
    description     text,
    file_id         int not null default 0,
    price           decimal(11,2) not null default 0.00,
    taxable         smallint not null default 0,
    stock           int not null default 0,
    weight          decimal(8,2) not null default 0.00,
    shipping        decimal(8,2) not null default 0.00,
    PRIMARY KEY   (id)
);

CREATE TABLE vshop_taxes (
    id              int not null default 0,
    title           varchar(255),
    zones           text,
    rate            int not null default 0,
    PRIMARY KEY (id)
);

CREATE TABLE vshop_orders (
    id              int not null default 0,
    first_name      varchar(120),
    last_name       varchar(120),
    email           varchar(120),
    phone           varchar(20),
    address_1       varchar(255),
    address_2       varchar(255),
    city            varchar(120),
    state           varchar(5),
    country         varchar(120),
    postal_code     varchar(20),
    comments        text,
    pay_method      varchar(120),
    order_array     text,
    order_date      int not null default 0,
    update_date     int not null default 0,
    completed       smallint not null default 0,
    cancelled       smallint not null default 0,
    status          smallint not null default 0,
    ip_address      varchar(15),
    PRIMARY KEY (id)
);

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

