<?php
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    
    if (!empty($text)) {
        $sql = "INSERT INTO tasks (text) VALUES (:text)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':text' => $text]);
        header('Location: index.php');
        exit;
    }
}

// Fetch all tasks
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get environment variables
$executionEnv = getenv('AWS_EXECUTION_ENV');
$taskArn = getenv('ECS_TASK_ARN');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS Community Day 2025 - Message Board</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --aws-blue: #232F3E;
            --aws-orange: #FF9900;
            --aws-light-blue: #1A365D;
            --aws-gray: #EAEDED;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, var(--aws-blue) 0%, #2C5282 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 40px 0;
            color: white;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            color: var(--aws-orange);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.2em;
            color: #CBD5E0;
        }

        .task-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--aws-blue);
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--aws-orange);
        }

        .submit-btn {
            background: var(--aws-orange);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background: #e88c00;
            transform: translateY(-2px);
        }

        .task-list {
            display: grid;
            gap: 20px;
        }

        .task-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            border-left: 5px solid var(--aws-orange);
        }

        .task-item:hover {
            transform: translateY(-3px);
        }

        .task-content {
            font-size: 1.1em;
            color: #2D3748;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .task-meta {
            color: #718096;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .banner {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
  <header class="header">
            <h1>AWS Community Day 2025</h1>
            <p>Bangkok, Thailand - Message Board</p>
            <div class="banner">
                Share your thoughts, questions, and insights with the AWS community!
                <?php if ($executionEnv || $taskArn): ?>
                    <div style="margin-top: 10px; font-size: 0.9em; opacity: 0.8;">
                        <?php if ($executionEnv): ?>
                            <div>Environment: <?= htmlspecialchars($executionEnv) ?></div>
                        <?php endif; ?>
                        <?php if ($taskArn): ?>
                            <div>Task ARN: <?= htmlspecialchars($taskArn) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

                    <?php if (isset($_GET['cleared']) && $_GET['cleared'] === 'true'): ?>
                <div style="background: var(--aws-orange); color: white; padding: 10px; border-radius: 8px; margin-top: 10px; text-align: center;">
                    Database cleared successfully!
                </div>
            <?php endif; ?>

        <div class="task-form">
            <form method="POST">
                <div class="form-group">
                    <label for="text">Share your message:</label>
                    <input 
                        type="text" 
                        id="text" 
                        name="text" 
                        required 
                        placeholder="Type your message here..."
                    >
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    Share Message
                </button>
            </form>
        </div>

        <div class="task-list">
            <?php if (empty($tasks)): ?>
                <div class="task-item">
                    <div class="task-content">No messages yet. Be the first to share!</div>
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item">
                        <div class="task-content">
                            <?= htmlspecialchars($task['text']) ?>
                        </div>
                        <div class="task-meta">
                            <i class="far fa-clock"></i>
                            <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>