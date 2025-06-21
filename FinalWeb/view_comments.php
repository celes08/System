<?php
include("connections.php");
session_start();
$post_id = intval($_GET['post_id'] ?? 0);

$stmt = $con->prepare("SELECT comment, created_at FROM post_comments WHERE post_id=? ORDER BY created_at ASC");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($comment, $created_at);

echo "<h2>All Comments</h2>";
while ($stmt->fetch()) {
    echo '<div class="post-comment"><span>' . htmlspecialchars($comment) . '</span> <small>' . date('M j, Y H:i', strtotime($created_at)) . '</small></div>';
}
$stmt->close();
?>
<a href="dashboard.php">Back to Dashboard</a>