<?php
// Set timezone
date_default_timezone_set('Asia/Bangkok');

// Database configuration
$dbConfig = [
    'host' => getenv('DB_HOST'),
    'port' => getenv('DB_PORT') ?: '5432',
    'database' => getenv('DB_DATABASE'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD')
];

// Log connection attempt
error_log(sprintf(
    "Attempting database connection to: host=%s, port=%s, database=%s, user=%s", 
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['database'],
    $dbConfig['username']
));

// Base DSN
$dsn = sprintf(
    "pgsql:host=%s;port=%s;dbname=%s",
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['database']
);

// Add SSL configuration if PGSSLMODE is set
if (getenv('PGSSLMODE')) {
    $sslMode = getenv('PGSSLMODE');
    $dsn .= ";sslmode=" . $sslMode;
    $dsn .= ";sslrootcert=/etc/ssl/postgresql/root.crt";
    
    error_log(sprintf(
        "SSL Mode enabled: %s, using root certificate at: %s",
        $sslMode,
        "/etc/ssl/postgresql/root.crt"
    ));
    
    // Verify SSL certificate file exists
    if (!file_exists("/etc/ssl/postgresql/root.crt")) {
        error_log("WARNING: SSL root certificate file not found!");
    } else {
        error_log("SSL root certificate file exists and has permissions: " . 
            substr(sprintf('%o', fileperms("/etc/ssl/postgresql/root.crt")), -4));
    }
}

error_log("Final connection string (excluding password): " . $dsn);

try {
    error_log("Initiating database connection...");
    $pdo = new PDO(
        $dsn,
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Test the connection with a simple query
    $pdo->query("SELECT 1");
    error_log("Database connection established successfully");
    
    // Set timezone for PostgreSQL session
    $pdo->exec("SET timezone='Asia/Bangkok'");
    error_log("Timezone set to Asia/Bangkok");
    
} catch(PDOException $e) {
    error_log("Database connection failed with error: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    
    // Additional error information
    $errorInfo = $e->errorInfo ?? [];
    if (!empty($errorInfo)) {
        error_log("SQLSTATE: " . ($errorInfo[0] ?? 'N/A'));
        error_log("Driver error code: " . ($errorInfo[1] ?? 'N/A'));
        error_log("Driver error message: " . ($errorInfo[2] ?? 'N/A'));
    }
    
    // Check for SSL-related errors
    if (strpos($e->getMessage(), 'SSL') !== false) {
        error_log("SSL-related error detected. Current SSL Mode: " . (getenv('PGSSLMODE') ?: 'not set'));
    }
    
    if (!extension_loaded('pdo_pgsql')) {
        error_log("ERROR: PDO PostgreSQL extension is not loaded!");
    }
    
    die("Connection failed. Please check the logs.");
}