#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
/var/www/html/docker/scripts/wait-for-db.sh mysql_local root password transfer /bin/true

# Initialize the application
echo "Initializing application..."
/var/www/html/docker/scripts/init-app.sh