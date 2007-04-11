CREATE TABLE folders (
  id int not null default 0,
  title varchar(60) not null,
  description text null,
  ftype smallint not null default 1,
  public_folder smallint not null default 1,
  icon varchar(255) not null,
  primary key (id)
);

CREATE TABLE filecabinet_pins (
key_id INT NOT NULL default 0 ,
folder_id INT NOT NULL default 0
);
