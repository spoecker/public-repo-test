<?php
require_once 'config.php';

try {
    // Log the clear attempt
    error_log("Attempting to clear all entries from tasks table");
    
    // Delete all records from the tasks table
    $sql = "DELETE FROM tasks";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute();
    
    // Get number of deleted rows
    $count = $stmt->rowCount();
    error_log("Successfully deleted {$count} entries from tasks table");

    // Add a welcome message back
    $sql = "INSERT INTO tasks (text) VALUES (:text)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':text' => 'Welcome to AWS Community Day 2025!']);
    error_log("Added welcome message back to tasks table");

    // Redirect back to index with success message
    header('Location: index.php?cleared=true');
    exit;
    
} catch(PDOException $e) {
    error_log("Error clearing tasks table: " . $e->getMessage());
    die("Failed to clear entries. Please check the logs.");
}
?>