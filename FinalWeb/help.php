<?php
// help.php 
// Connect to the database
include("connections.php"); // This should set up $con as your MySQLi connection

session_start();
// For demo: set a user_id (replace with your actual session logic)
$user_id = $_SESSION['user_id'] ?? 1;

// Tab logic
$active_tab = $_GET['tab'] ?? 'all';

// Modal logic
$show_modal = isset($_POST['showModal']) || isset($_POST['submitTicket']);

// Handle ticket submission
$ticket_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitTicket'])) {
    $subject = trim($_POST['subject'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = trim($_POST['priority'] ?? '');
    // Use correct ENUM value for status
    $status = 'Open';
    $created_at = date('Y-m-d H:i:s');

    // Validate priority and status
    $allowed_priorities = ['Low', 'Medium', 'High', 'Urgent'];
    if (!in_array($priority, $allowed_priorities)) $priority = 'Medium';

    if ($subject && $category && $description && $priority) {
        $stmt = $con->prepare("INSERT INTO support_tickets (user_id, subject, description, category, status, priority, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $subject, $description, $category, $status, $priority, $created_at);
        if ($stmt->execute()) {
            $ticket_success = "Your ticket has been submitted!";
            $show_modal = false;
        } else {
            $ticket_success = "Failed to submit ticket. Please try again.";
        }
        $stmt->close();
    } else {
        $ticket_success = "All fields are required.";
    }
}

// Fetch tickets from DB
$tickets = [];
$where = "";
if ($active_tab === 'pending') {
    $where = "WHERE status = 'Open' OR status = 'In Progress'";
} elseif ($active_tab === 'resolved') {
    $where = "WHERE status = 'Resolved' OR status = 'Closed'";
}
$sql = "SELECT * FROM support_tickets $where ORDER BY created_at DESC";
$result = $con->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="help.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container" id="dashboardContainer">
        <!-- Left Sidebar Navigation (Same as dashboard) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="CVSU Logo" class="sidebar-logo">
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li>
                        <a href="organizational-chart.php"><i class="fas fa-sitemap"></i> Organizational Chart</a>
                    </li>
                    <li>
                        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                    </li>
                    <li>
                        <a href="bookmarks.php"><i class="fas fa-bookmark"></i> Bookmarks</a>
                    </li>
                    <li>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    </li>
                </ul>
            </nav>
            <div class="post-button-container">
                <button class="post-button" id="postButton" disabled>
                    <i class="fas fa-plus"></i> Post
                </button>
            </div>
            <div class="sidebar-footer">
                <ul>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li class="active"><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="img/avatar-placeholder.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4>Person</h4>
                        <p>@person</p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>
        <!-- Main Content Area -->
        <main class="help-main-content">
            <header class="help-header">
                <div class="help-header-left">
                    <h1>Help</h1>
                </div>
                <div class="help-header-right">
                    <form method="post" style="display:inline;">
                        <button class="submit-ticket-btn" name="showModal" type="submit">
                            Submit a ticket
                        </button>
                    </form>
                </div>
            </header>
            <!-- Tabs Container (separate from header) -->
            <div class="help-tabs-container">
                <div class="help-tabs">
                    <form method="get" style="display:inline;">
                        <button type="submit" name="tab" value="all" class="help-tab<?php echo ($active_tab === 'all' ? ' active' : ''); ?>">All</button>
                    </form>
                    <form method="get" style="display:inline;">
                        <button type="submit" name="tab" value="pending" class="help-tab<?php echo ($active_tab === 'pending' ? ' active' : ''); ?>">Pending</button>
                    </form>
                    <form method="get" style="display:inline;">
                        <button type="submit" name="tab" value="resolved" class="help-tab<?php echo ($active_tab === 'resolved' ? ' active' : ''); ?>">Resolved</button>
                    </form>
                </div>
            </div>
            <div class="help-content">
                <?php if ($ticket_success): ?>
                    <div class="notification success"><?php echo $ticket_success; ?></div>
                <?php endif; ?>
                <div class="help-tickets" id="helpTickets">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-notifications">
                            <i class="fas fa-bell empty-icon"></i>
                            <h2>No Tickets Yet</h2>
                            <p>You don't have any tickets at this time.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <div class="help-ticket" data-status="<?php echo htmlspecialchars($ticket['status']); ?>">
                            <div class="ticket-avatar">
                                <img src="img/avatar-placeholder.png" alt="Person">
                            </div>
                            <div class="ticket-content">
                                <div class="ticket-header">
                                    <div class="ticket-user-info">
                                        <span class="ticket-author">Person</span>
                                        <span class="ticket-username">@person</span>
                                        <span class="ticket-date"><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="ticket-body">
                                    <h3 class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                                    <p class="ticket-description"><?php echo htmlspecialchars($ticket['description']); ?></p>
                                    <span class="ticket-status <?php echo htmlspecialchars($ticket['status']); ?>">
                                        <?php echo ucfirst($ticket['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <!-- Submit Ticket Modal (PHP-only, no JS) -->
    <div class="modal-overlay<?php echo $show_modal ? ' active' : ''; ?>" id="ticketModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Submit a Help Ticket</h2>
                <form method="post" style="display:inline; float:right;">
                    <button class="modal-close" name="closeModal" type="submit" style="background:none;border:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="form-group">
                        <label for="ticketSubject">Subject</label>
                        <input type="text" id="ticketSubject" name="subject" placeholder="Brief description of your issue" required>
                    </div>
                    <div class="form-group">
                        <label for="ticketCategory">Category</label>
                        <div class="select-wrapper">
                            <select id="ticketCategory" name="category" required>
                                <option value="" disabled selected>Select Category</option>
                                <option value="technical">Technical Issue</option>
                                <option value="account">Account Problem</option>
                                <option value="feature">Feature Request</option>
                                <option value="bug">Bug Report</option>
                                <option value="other">Other</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ticketDescription">Description</label>
                        <textarea id="ticketDescription" name="description" placeholder="Please provide detailed information about your issue..." rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ticketPriority">Priority</label>
                        <div class="select-wrapper">
                            <select id="ticketPriority" name="priority" required>
                                <option value="" disabled selected>Select Priority</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="action-buttons">
                            <button type="button" class="action-btn" title="Attach File" disabled>
                                <i class="fas fa-paperclip"></i>
                            </button>
                        </div>
                        <div class="form-options">
                            <button type="submit" class="submit-btn" name="submitTicket">
                                Submit Ticket
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>