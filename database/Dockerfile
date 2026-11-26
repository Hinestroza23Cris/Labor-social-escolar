FROM mariadb:11.4
COPY --chmod=644 database/01-schema.sql database/02-demo.sql /docker-entrypoint-initdb.d/
