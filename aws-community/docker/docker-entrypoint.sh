#!/bin/sh
set -e

# Function to check if PostgreSQL is ready
wait_for_postgres() {
    echo "Waiting for PostgreSQL to be ready..."
    echo "Connection Details:"
    echo "Host: $DB_HOST"
    echo "Port: $DB_PORT"
    echo "Database: $DB_DATABASE"
    echo "User: $DB_USERNAME"

    # Construct base connection string
    CONN_STRING="host=$DB_HOST port=$DB_PORT dbname=$DB_DATABASE user=$DB_USERNAME"
    
    # Add SSL parameters if PGSSLMODE is set
    if [ ! -z "$PGSSLMODE" ]; then
        echo "SSL Mode: $PGSSLMODE"
        CONN_STRING="$CONN_STRING sslmode=$PGSSLMODE sslrootcert=/etc/ssl/postgresql/root.crt"
        
        echo "SSL Certificate Check:"
        echo "Certificate path: /etc/ssl/postgresql/root.crt"
        if [ -f "/etc/ssl/postgresql/root.crt" ]; then
            echo "Certificate exists: Yes"
            echo "Certificate permissions: $(ls -l /etc/ssl/postgresql/root.crt)"
        else
            echo "Certificate exists: No"
        fi
    else
        echo "SSL Mode: disabled"
    fi

    # Test connection
    until PGPASSWORD=$DB_PASSWORD psql "$CONN_STRING" -c "SELECT 1" > /dev/null 2>&1; do
        echo "PostgreSQL is unavailable - sleeping"
        PGPASSWORD=$DB_PASSWORD psql "$CONN_STRING" -c "\conninfo"
        sleep 1
    done
    echo "PostgreSQL is up and running!"
}

# Clean up failed health-check if needed
clean_up_failed_health_check() {
    echo "Cleaning up failed health-check..."
    if [ -f /tmp/unhealthy ]; then
        rm /tmp/unhealthy
        sleep 20s
    fi
    echo "Cleaning up failed health-check completed!"
}

# Cleaning up failed health-check on container start
clean_up_failed_health_check

# Wait for PostgreSQL
wait_for_postgres

# Run database initialization
init_database() {
    echo "Initializing database..."
    php /var/www/html/init.php
    echo "Database initialization completed!"
}

# Run database initialization
init_database

# Start PHP-FPM
exec "$@"