CREATE TABLE cycle_slots (
  slot_order smallint NOT NULL,
  thumbnail_path varchar(255) NOT NULL,
  thumbnail_text text NOT NULL,
  background_path varchar(255) NOT NULL,
  feature_text text default NULL,
  feature_x smallint NOT NULL default 10,
  feature_y smallint NOT NULL default 10,
  f_width smallint NOT NULL default 100,
  f_height smallint NOT NULL default 100,
  destination_url text NOT NULL,
  UNIQUE KEY slot_order (slot_order)
);