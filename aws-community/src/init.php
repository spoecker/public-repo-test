<?php
// Maximum number of retries
$maxRetries = 5;
$retryDelay = 2; // seconds

for ($i = 1; $i <= $maxRetries; $i++) {
    try {
        require_once 'config.php';

        // Check if table exists
        $stmt = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = 'tasks'
            )
        ");
        
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            // Create tasks table
            $sql = "
            CREATE TABLE tasks (
                id SERIAL PRIMARY KEY,
                text VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $pdo->exec($sql);
            echo "Database table 'tasks' created successfully\n";
            
            // Add initial welcome message
            $sql = "INSERT INTO tasks (text) VALUES (:text)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':text' => 'Welcome to AWS Community Day 2025!']);
            echo "Initial welcome message inserted successfully\n";
        } else {
            echo "Database table 'tasks' already exists\n";
        }
        
        // If we get here, everything worked
        break;
        
    } catch (PDOException $e) {
        echo "Attempt {$i} of {$maxRetries}: Database initialization failed: " . $e->getMessage() . "\n";
        
        if ($i === $maxRetries) {
            throw $e;
        }
        
        sleep($retryDelay);
    }
}

echo "Database initialization process completed\n";