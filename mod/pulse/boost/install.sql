CREATE TABLE pulse_schedule (
id int not null default 0,
module varchar( 50 ) not null,
parameters varchar( 255 ) default null,
pulse_type varchar( 255 ) default null,
pulse_time int not null default 0,
last_run int not null default 0,
repeats int not null default 0,
completed int not null default 0,
active smallint not null default 0,
PRIMARY KEY ( id )
);

CREATE INDEX pulsesch_idx on pulse_schedule(pulse_time);
