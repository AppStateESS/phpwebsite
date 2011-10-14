CREATE TABLE cycle_slots (
  slot_order smallint NOT NULL,
  thumbnail_path varchar(255) NOT NULL,
  thumbnail_text text NOT NULL,
  background_path varchar(255) NOT NULL,
  feature_text text NOT NULL,
  feature_x smallint NOT NULL,
  feature_y smallint NOT NULL,
  destination_url text NOT NULL,
  UNIQUE KEY slot_order (slot_order)
);