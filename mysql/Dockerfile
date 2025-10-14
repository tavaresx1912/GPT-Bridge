# Minimal Dockerfile to run a MySQL server for local development
# Uses the official MySQL image
FROM mysql:8.0

# Default database credentials (override at runtime with -e or in Compose)
# WARNING: These defaults are for local development only.
ENV MYSQL_DATABASE=appdb \
    MYSQL_USER=appuser \
    MYSQL_PASSWORD=apppass \
    MYSQL_ROOT_PASSWORD=rootpass

# Copy optional initialization scripts. Any .sql or .sh files placed in
# the initdb directory will be executed on first container startup.
# You may delete this COPY if you don't need automatic initialization.
COPY initdb/ /docker-entrypoint-initdb.d/

# Expose default MySQL port
EXPOSE 3306

# The upstream image provides the default ENTRYPOINT/CMD that launches mysqld.