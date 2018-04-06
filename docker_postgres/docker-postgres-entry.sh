#!/bin/bash
exec sudo -u postgres /usr/bin/postgres -D "/var/lib/pgsql/data" -c "config_file=/var/lib/pgsql/data/postgresql.conf"
