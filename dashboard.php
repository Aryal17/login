<?php
session_start();
include "database.php";

// Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Fetch user from database
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

//  new post 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user['id'], $content);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php");
        exit();
    }
}

//  comment 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'], $_POST['post_id'])) {
    $comment_content = trim($_POST['comment_content']);
    $post_id = $_POST['post_id'];
    if (!empty($comment_content)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user['id'], $comment_content);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php");
        exit();
    }
}

//  like/unlike 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_button'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_POST['user_id'];
    
    // Check if the user has already liked this post
    $like_check_stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $like_check_stmt->bind_param("ii", $user_id, $post_id);
    $like_check_stmt->execute();
    $like_check_result = $like_check_stmt->get_result();
    
    if ($like_check_result->num_rows > 0) {
        $delete_like_stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $delete_like_stmt->bind_param("ii", $user_id, $post_id);
        $delete_like_stmt->execute();
        $delete_like_stmt->close();
    } else {
        $insert_like_stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $insert_like_stmt->bind_param("ii", $user_id, $post_id);
        $insert_like_stmt->execute();
        $insert_like_stmt->close();
    }
    header("Location: dashboard.php");
    exit();
}

// Fetch posts for the feed
$posts_query = "SELECT posts.id, posts.content, posts.created_at, users.email 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                ORDER BY posts.created_at DESC";
$posts = $conn->query($posts_query);
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
            <?php echo $user['status'] == 'active' ? "<span class='active'>Active🟢</span>" : "<span class='inactive'>Inactive🔴</span>"; ?>
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
                    <small><?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small>

                    
                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="like_button">Like</button>
                    </form>

                    
                    <?php
                        $like_query = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?";
                        $like_stmt = $conn->prepare($like_query);
                        $like_stmt->bind_param("i", $post['id']);
                        $like_stmt->execute();
                        $like_result = $like_stmt->get_result();
                        $like_count = $like_result->fetch_assoc()['like_count'];
                        $like_stmt->close();
                    ?>
                    <p><?php echo $like_count; ?> Like(s)</p>

                    
                    <form action="dashboard.php" method="post" class="comment-form">
                        <textarea name="comment_content" placeholder="Write your comment..." rows="1" required></textarea>
                        <button type="submit" class="comment-button">Submit</button>
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>" />
                    </form>

                    
                    <div class="comments">
                        <?php
                            $comments_query = "SELECT comments.content, comments.created_at, users.email 
                                               FROM comments 
                                               JOIN users ON comments.user_id = users.id 
                                               WHERE comments.post_id = ? 
                                               ORDER BY comments.created_at ASC";
                            $comments_stmt = $conn->prepare($comments_query);
                            $comments_stmt->bind_param("i", $post['id']);
                            $comments_stmt->execute();
                            $comments_result = $comments_stmt->get_result();
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
