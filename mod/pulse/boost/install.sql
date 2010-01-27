CREATE TABLE pulse_schedule (
id int not null default 0,
name varchar(255),
execute_at int not null default 0,
module varchar( 50 ) not null,
class_file varchar(255) not null,
class varchar(255) not null,
began_execution int,
finished_execution int,
status int,
PRIMARY KEY ( id )
);

CREATE INDEX pulsesch_idx on pulse_schedule(execute_after, execute_time);
