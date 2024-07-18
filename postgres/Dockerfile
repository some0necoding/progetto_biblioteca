FROM postgres:16.3

RUN mkdir -p /docker-entrypoint-initdb.d

# Initialize the database (reference https://github.com/docker-library/docs/blob/master/postgres/README.md#initialization-scripts)
COPY tables.sql     /docker-entrypoint-initdb.d/0-tables.sql
COPY functions.sql  /docker-entrypoint-initdb.d/1-functions.sql
COPY admin.sql      /docker-entrypoint-initdb.d/2-admin.sql
COPY samples.sql    /docker-entrypoint-initdb.d/3-samples.sql
