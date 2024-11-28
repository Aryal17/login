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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
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

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'], $_POST['post_id'])) {
    $comment_content = trim($_POST['comment_content']);
    $post_id = $_POST['post_id'];
    
    if (!empty($comment_content)) {
        // Insert the comment into the database
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user['id'], $comment_content);
        $stmt->execute();
        $stmt->close();
        
        // Redirect to avoid re-posting on refresh
        header("Location: dashboard.php");
        exit();
    }
}

// Fetch posts for the feed
$posts = $conn->query("SELECT posts.id, posts.content, posts.created_at, users.email 
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
    <script>
        // JavaScript to handle the visibility of the comment button
        document.addEventListener("DOMContentLoaded", function() {
            const commentTextArea = document.querySelectorAll('.comment-textarea');
            const commentButtons = document.querySelectorAll('.comment-button');

            commentTextArea.forEach((textarea, index) => {
                textarea.addEventListener('input', function() {
                    if (textarea.value.trim() !== '') {
                        commentButtons[index].style.display = 'inline-block';
                    } else {
                        commentButtons[index].style.display = 'none';
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo $user['email']; ?>!</h2>
        <div class="status">
            <?php
                if ($user['status'] == 'active') {
                    echo "<span class='active'>Active🟢</span>";
                } else {
                    echo "<span class='inactive'>Inactive🔴</span>";
                }
            ?>
        </div>

        <a href="logout.php" class="logout-button">Logout</a>

        <!-- Status Post Form -->
        <form action="dashboard.php" method="post" class="status-form">
            <textarea name="content" placeholder="What's on your mind?" rows="1" required></textarea>
            <button type="submit" class="post-button">Post</button>
        </form>

        <!-- Display Feed -->
        <div class="feed">
            <h3>Your Feed</h3>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="feed-item">
                    <strong><?php echo $post['email']; ?></strong>
                    <p><?php echo $post['content']; ?></p>

                    <!-- Comment Form -->
                    <form action="dashboard.php" method="post" class="comment-form">
                        <textarea name="comment_content" class="comment-textarea" placeholder="Write your comment..." rows="1" required></textarea>
                        <button type="submit" class="comment-button" style="display:none;">Submit</button>
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>" />
                    </form>

                    <small><?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small>

                    <!-- Display Comments for this Post -->
                    <div class="comments">
                        <?php
                            // Fetch comments for the current post
                          $stmt = $conn->prepare("SELECT comments.content, comments.created_at, users.email 
                                                  FROM comments 
                                                    JOIN users ON comments.user_id = users.id 
                                                    WHERE comments.post_id = ? 
                                                    ORDER BY comments.created_at ASC");
                            $stmt->bind_param("i", $post['id']);
                            $stmt->execute();
                            $comments_result = $stmt->get_result();

                            while ($comment = $comments_result->fetch_assoc()):
                        ?>
                            <div class="comment-item">
                                <strong><?php echo $comment['email']; ?></strong>
                                <p><?php echo $comment['content']; ?></p>
                                <small><?php echo date("F j, Y, g:i a", strtotime($comment['created_at'])); ?></small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
