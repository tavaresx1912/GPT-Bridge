MySQL Docker image for local development

This folder contains a minimal Dockerfile to run a MySQL 8 server using the official upstream image.

Build
- From the project root (the folder that contains this mysql directory):
  docker build -f mysql/Dockerfile -t my-mysql:dev .

Run
- Start a container exposing port 3306. You can override credentials at runtime.
  docker run --name my-mysql -p 3306:3306 \
    -e MYSQL_DATABASE=appdb \
    -e MYSQL_USER=appuser \
    -e MYSQL_PASSWORD=apppass \
    -e MYSQL_ROOT_PASSWORD=rootpass \
    -d my-mysql:dev

Initialization scripts (optional)
- Place .sql or .sh files under mysql/initdb/. They will run on first container startup.

Notes
- The default credentials are intended for local development only. Always override them in any non-local environment.
- If you already have MySQL running on your machine, change the host port mapping (e.g., -p 3307:3306).
