FROM postgres:16.3 as dev

RUN mkdir -p /docker-entrypoint-initdb.d

# Initialize the database (reference https://github.com/docker-library/docs/blob/master/postgres/README.md#initialization-scripts)
COPY ./postgres/tables.sql     /docker-entrypoint-initdb.d/0-tables.sql
COPY ./postgres/functions.sql  /docker-entrypoint-initdb.d/1-functions.sql
COPY ./postgres/users.sql      /docker-entrypoint-initdb.d/2-users.sql
COPY ./postgres/samples.sql    /docker-entrypoint-initdb.d/3-samples.sql

FROM dev as prod
