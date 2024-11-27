<?php
session_start();
include "database.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Fetch the user from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        // Insert the post into the database
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user['id'], $content);
        $stmt->execute();
        $stmt->close();
        
        // Redirect to avoid re-posting on refresh
        header("Location: dashboard.php");
        exit();
    }
}

// Fetch posts for the feed
$posts = $conn->query("SELECT posts.content, posts.created_at, users.email 
                       FROM posts 
                       JOIN users ON posts.user_id = users.id 
                       ORDER BY posts.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo $user['email']; ?>!</h2>
       

        
        <div class="status">
        
            <?php
             
            if ($user['status'] == 'active') {
                echo "<span class='active'>Active🟢</span>";
            } else {
                echo "<span class='inactive'>Inactive</span>";
            }
            ?>
        </div>

        <a href="logout.php" class="logout-button">Logout</a>

        <!-- Status Post Form -->
        <form action="dashboard.php" method="post" class="status-form">
            <textarea name="content" placeholder="What's on your mind?" rows="3" required></textarea>
            <button type="submit" class="post-button">Post</button>
        </form>

        <!-- Feed -->
        <div class="feed">
            <h3>Your Feed</h3>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="feed-item">
                    <strong><?php echo $post['email']; ?></strong>
                    <p><?php echo $post['content']; ?></p>
                    <small><?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
