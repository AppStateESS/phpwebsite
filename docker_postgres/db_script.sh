#!/bin/bash

# Immediately exits if any error occurs during the script
# execution. If not set, an error could occur and the
# script would continue its execution.
set -o errexit


# Creating an array that defines the environment variables
# that must be set. This can be consumed later via arrray
# variable expansion ${REQUIRED_ENV_VARS[@]}.
readonly REQUIRED_ENV_VARS=(
  "POSTGRES_PASSWORD"
  "POSTGRES_USER"
  "POSTGRES_DB")


# Main execution:
# - verifies if all environment variables are set
# - runs the SQL code to create user and database
main() {
  sudo -u postgres pg_ctl -D "/var/lib/pgsql/data" -w start
  check_env_vars_set
  init_user_and_db
  setup_temporal_tables
  sudo -u postgres pg_ctl -D "/var/lib/pgsql/data" stop
}


# Checks if all of the required environment
# variables are set. If one of them isn't,
# echoes a text explaining which one isn't
# and the name of the ones that need to be
check_env_vars_set() {
  for required_env_var in ${REQUIRED_ENV_VARS[@]}; do
    if [[ -z "${!required_env_var}" ]]; then
      echo "Error:
    Environment variable '$required_env_var' not set.
    Make sure you have the following environment variables set:
      ${REQUIRED_ENV_VARS[@]}
Aborting."
      exit 1
    fi
  done
}

# Performs the initialization in the already-started PostgreSQL
# using the preconfigured POSTGRE_USER user.
init_user_and_db() {
    echo "begin init script"
  sudo -u postgres psql -v ON_ERROR_STOP=1 <<-EOSQL
     CREATE USER $POSTGRES_USER WITH PASSWORD '$POSTGRES_PASSWORD';
     ALTER USER $POSTGRES_USER WITH SUPERUSER;
     CREATE USER root WITH SUPERUSER;
     CREATE DATABASE $POSTGRES_DB;
     GRANT ALL PRIVILEGES ON DATABASE $POSTGRES_DB TO $POSTGRES_USER;
EOSQL

  echo "Created users and db"
}

setup_temporal_tables() {
  echo "begin temporal tables setup"
  cd temporal_tables && gmake && gmake install && gmake installcheck
  psql $POSTGRES_USER -U $POSTGRES_PASSWORD -v ON_ERROR_STOP=1 <<-EOSQL
     CREATE EXTENSION temporal_tables;
EOSQL
  echo "completed temporal tables setup"
}
# Executes the main routine with environment variables
# passed through the command line. We don't use them in
# this script but now you know 🤓
main "$@"
