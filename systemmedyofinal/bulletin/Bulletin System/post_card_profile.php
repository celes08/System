<article class="post-card" data-post-id="<?php echo $post['post_id']; ?>" data-department="<?php echo strtolower($departments[$post['target_department_id']] ?? 'all'); ?>">
    <div class="post-header">
        <div class="post-avatar">
            <?php
            $profilePic = $post['profile_picture'] ?? '';
            if ($profilePic) {
                $profilePic = preg_replace('#^uploads/#', '', $profilePic);
                $imgSrc = 'uploads/' . htmlspecialchars($profilePic);
            } else {
                $imgSrc = 'img/avatar-placeholder.png';
            }
            ?>
            <img src="<?php echo $imgSrc; ?>" alt="User Avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
        </div>
        <div class="post-user-info">
            <h4 class="post-author"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h4>
            <p class="post-username">@<?php echo htmlspecialchars(strtolower($post['first_name'] . $post['last_name'])); ?></p>
            <span class="post-timestamp"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
        </div>
    </div>
    <div class="post-content">
        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
        <p class="post-text"><?php echo htmlspecialchars($post['content']); ?></p>
        <span class="post-department <?php echo strtolower($departments[$post['target_department_id']] ?? 'all'); ?>">
            <?php
            echo ($post['target_department_id'] == 1) ? 'DIT'
                : (($post['target_department_id'] == 2) ? 'DOM'
                : (($post['target_department_id'] == 3) ? 'DAS'
                : (($post['target_department_id'] == 4) ? 'TED'
                : 'ALL DEPARTMENTS')));
            ?>
        </span>
    </div>
    <div class="post-actions">
        <?php
            // Comments
            $comment_count = 0;
            $comment_q = $con->prepare("SELECT COUNT(*) FROM post_comments WHERE post_id=?");
            $comment_q->bind_param("i", $post['post_id']);
            $comment_q->execute();
            $comment_q->bind_result($comment_count);
            $comment_q->fetch();
            $comment_q->close();
        ?>
        <button class="action-btn comment-btn" type="button">
            <i class="fas fa-comment"></i>
            <span class="action-count"><?php echo $comment_count; ?></span>
        </button>
        <?php
            // Likes
            $like_count = 0;
            $like_q = $con->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id=?");
            $like_q->bind_param("i", $post['post_id']);
            $like_q->execute();
            $like_q->bind_result($like_count);
            $like_q->fetch();
            $like_q->close();
            $liked = false;
            if (isset($user_id)) {
                $like_check = $con->prepare("SELECT like_id FROM post_likes WHERE post_id=? AND user_id=?");
                $like_check->bind_param("ii", $post['post_id'], $user_id);
                $like_check->execute();
                $like_check->store_result();
                if ($like_check->num_rows > 0) $liked = true;
                $like_check->close();
            }
        ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="like_post_id" value="<?php echo $post['post_id']; ?>">
            <button class="action-btn like-btn<?php echo $liked ? ' liked' : ''; ?>" type="submit">
                <i class="fas fa-heart"></i>
                <span class="action-count"><?php echo $like_count; ?></span>
            </button>
        </form>
        <?php
            // Views
            $view_count = 0;
            $view_q = $con->prepare("SELECT COUNT(*) FROM post_views WHERE post_id=?");
            $view_q->bind_param("i", $post['post_id']);
            $view_q->execute();
            $view_q->bind_result($view_count);
            $view_q->fetch();
            $view_q->close();
        ?>
        <button class="action-btn view-btn" disabled>
            <i class="fas fa-eye"></i>
            <span class="action-count"><?php echo $view_count; ?></span>
        </button>
        <?php
            // Bookmarks
            $bookmark_count = 0;
            $bookmark_q = $con->prepare("SELECT COUNT(*) FROM post_bookmarks WHERE post_id=?");
            $bookmark_q->bind_param("i", $post['post_id']);
            $bookmark_q->execute();
            $bookmark_q->bind_result($bookmark_count);
            $bookmark_q->fetch();
            $bookmark_q->close();
            $bookmarked = false;
            if (isset($user_id)) {
                $bookmark_check = $con->prepare("SELECT bookmark_id FROM post_bookmarks WHERE post_id=? AND user_id=?");
                $bookmark_check->bind_param("ii", $post['post_id'], $user_id);
                $bookmark_check->execute();
                $bookmark_check->store_result();
                if ($bookmark_check->num_rows > 0) $bookmarked = true;
                $bookmark_check->close();
            }
        ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="bookmark_post_id" value="<?php echo $post['post_id']; ?>">
            <button class="action-btn bookmark-btn<?php echo $bookmarked ? ' bookmarked' : ''; ?>" type="submit">
                <i class="fas fa-bookmark"></i>
                <span class="action-count"><?php echo $bookmark_count; ?></span>
            </button>
        </form>
    </div>
    <div class="post-comments-scroll" style="max-height:120px; overflow-y:auto; margin-top:10px;">
    <?php
    // Fetch all comments for this post, including user info
    $comments = [];
    $comment_q = $con->prepare(
        "SELECT c.comment, c.created_at, u.first_name, u.profile_picture
         FROM post_comments c 
         JOIN signuptbl u ON c.user_id = u.user_id 
         WHERE c.post_id=? 
         ORDER BY c.created_at ASC"
    );
    $comment_q->bind_param("i", $post['post_id']);
    $comment_q->execute();
    $comment_q->bind_result($comment_text, $comment_created, $comment_user, $comment_avatar);
    while ($comment_q->fetch()) {
        $comments[] = [
            'text' => $comment_text,
            'created' => $comment_created,
            'user' => $comment_user,
            'avatar' => $comment_avatar
        ];
    }
    $comment_q->close();
    // Display all comments 
    foreach ($comments as $c) {
        $avatar = $c['avatar'] ?? '';
        if ($avatar) {
            $avatar = preg_replace('#^uploads/#', '', $avatar);
            $avatarSrc = 'uploads/' . htmlspecialchars($avatar);
        } else {
            $avatarSrc = 'img/avatar-placeholder.png';
        }
        $user = htmlspecialchars($c['user']);
        $text = htmlspecialchars($c['text']);
        $date = date('M j, Y H:i', strtotime($c['created']));
        echo '<div class="post-comment" style="display:flex;align-items:flex-start;gap:10px;margin-bottom:8px;">';
        echo '  <img src="' . $avatarSrc . '" alt="User" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">';
        echo '  <div>';
        echo '    <strong>' . $user . '</strong><br>';
        echo '    <span>' . $text . '</span><br>';
        echo '    <small>' . $date . '</small>';
        echo '  </div>';
        echo '</div>';
    }
    ?>
    </div>
</article> 