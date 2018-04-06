FROM centos:7
MAINTAINER "Ted Eberhard" <eberhardtm@appstate.edu>
ENV container docker
USER root

# install postgres

RUN yum -y install postgresql postgresql-server postgresql-contrib postgresql-devel vim sudo git
RUN yum -y update
RUN yum -y install gcc make

#RUN mkdir /var/lib/pgsql
RUN chown postgres:postgres /var/lib/pgsql

USER postgres
RUN initdb -D "/var/lib/pgsql/data"

#swap back to root for entry point and gmake
USER root

# install temporal tables
RUN git clone https://github.com/arkhipov/temporal_tables.git

ENV POSTGRES_PASSWORD=canopy \
    POSTGRES_USER=canopy \
    POSTGRES_DB=canopy

ADD docker_postgres/db_script.sh /
RUN ./db_script.sh

# Allow any host to connect to postgres_db
RUN echo -e "host \t all \t all \t all \t md5" >> /var/lib/pgsql/data/pg_hba.conf
RUN echo "listen_addresses = '*'" >> /var/lib/pgsql/data/postgresql.conf

ADD docker_postgres/docker-postgres-entry.sh /docker-entry.sh
ENTRYPOINT ["/docker-entry.sh"]
