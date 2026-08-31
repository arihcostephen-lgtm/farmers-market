<?php
include "connection.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id']) || (int) ($_SESSION['role'] ?? 0) !== 4) {
    header("Location: login.php");
    exit;
}

$db = new mysqli('localhost', 'root', '', 'farmersmkt_db', 3306);

if (!$db) {
    echo 'Cannot connect to database server';
    exit;
}

$managerId = (int) $_SESSION['user_id'];
$managerName = htmlspecialchars($_SESSION['user_name'] ?? 'Manager');

// Get filter parameters
$filterType = trim($_GET['type'] ?? '');
$filterStatus = trim($_GET['status'] ?? 'all');

// Handle marking notification as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $notificationId = (int) ($_POST['notification_id'] ?? 0);
    if ($notificationId > 0) {
        $db->query("UPDATE manager_notifications SET is_read = 1, read_at = NOW() WHERE notification_id = $notificationId AND manager_id = $managerId");
    }
}

// Handle marking all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_as_read'])) {
    $db->query("UPDATE manager_notifications SET is_read = 1, read_at = NOW() WHERE manager_id = $managerId AND is_read = 0");
}

// Handle deleting notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $notificationId = (int) ($_POST['notification_id'] ?? 0);
    if ($notificationId > 0) {
        $db->query("DELETE FROM manager_notifications WHERE notification_id = $notificationId AND manager_id = $managerId");
    }
}

// Build query for notifications
$querySql = "SELECT * FROM manager_notifications WHERE manager_id = $managerId";

if ($filterStatus === 'read') {
    $querySql .= " AND is_read = 1";
} elseif ($filterStatus === 'unread') {
    $querySql .= " AND is_read = 0";
}

if (!empty($filterType)) {
    $querySql .= " AND notification_type = '" . $db->real_escape_string($filterType) . "'";
}

$querySql .= " ORDER BY is_read ASC, created_at DESC";

$notificationsResult = $db->query($querySql);
$totalNotifications = $notificationsResult ? $notificationsResult->num_rows : 0;

// Get stats
$unreadCount = (int) $db->query("SELECT COUNT(*) as count FROM manager_notifications WHERE manager_id = $managerId AND is_read = 0")->fetch_assoc()['count'];
$totalCount = (int) $db->query("SELECT COUNT(*) as count FROM manager_notifications WHERE manager_id = $managerId")->fetch_assoc()['count'];

// Get notification types
$typesResult = $db->query("SELECT DISTINCT notification_type FROM manager_notifications WHERE manager_id = $managerId ORDER BY notification_type");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Notifications | Local Farm Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap');
        
        body {
            background: #f8faf6;
            color: #333;
            font-family: 'Manrope', sans-serif;
        }
        
        .notifications-container {
            min-height: 100vh;
            padding: 30px 0;
        }
        
        .notifications-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .notifications-header h1 {
            color: #092214;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .notifications-header p {
            color: #75b16c;
            font-size: 1rem;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stat-card h5 {
            color: #75b16c;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            color: #092214;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .filters-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }
        
        .notification-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .notification-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #75b16c;
        }
        
        .notification-item.unread {
            background: #f0f8f0;
            border-left: 4px solid #75b16c;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .notification-title {
            color: #092214;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .notification-type {
            display: inline-block;
            background: #75b16c;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .notification-type.user {
            background: #0084ff;
        }
        
        .notification-type.order {
            background: #ff9500;
        }
        
        .notification-type.payment {
            background: #34c759;
        }
        
        .notification-type.system {
            background: #5856d6;
        }
        
        .notification-message {
            color: #555;
            font-size: 1rem;
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .notification-time {
            color: #999;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: flex-end;
        }
        
        .notification-actions form {
            display: inline;
        }
        
        .btn-notification {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 4px;
        }
        
        .unread-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #75b16c;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="notifications-header">
        <div class="container">
            <h1><i class="fas fa-bell me-2" style="color: #75b16c;"></i>Manager Notifications</h1>
            <p>Stay updated with system alerts, orders, and important events</p>
        </div>
    </div>

    <div class="notifications-container">
        <div class="container">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5><i class="fas fa-envelope me-2"></i>Total Notifications</h5>
                        <div class="number"><?php echo (int) $totalCount; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5><i class="fas fa-envelope-open me-2"></i>Unread</h5>
                        <div class="number"><?php echo (int) $unreadCount; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5><i class="fas fa-check-circle me-2"></i>Read</h5>
                        <div class="number"><?php echo (int) ($totalCount - $unreadCount); ?></div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="toolbar">
                    <div class="flex-grow-1">
                        <form method="GET" class="row g-3 align-items-end" style="flex-wrap: wrap;">
                            <div class="col-auto">
                                <label for="status-filter" class="form-label mb-0">Status</label>
                                <select class="form-select" id="status-filter" name="status">
                                    <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>All</option>
                                    <option value="unread" <?php echo $filterStatus === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                    <option value="read" <?php echo $filterStatus === 'read' ? 'selected' : ''; ?>>Read</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label for="type-filter" class="form-label mb-0">Type</label>
                                <select class="form-select" id="type-filter" name="type">
                                    <option value="" <?php echo empty($filterType) ? 'selected' : ''; ?>>All Types</option>
                                    <?php if ($typesResult && $typesResult->num_rows > 0): while ($type = $typesResult->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($type['notification_type']); ?>" <?php echo $filterType === $type['notification_type'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst($type['notification_type'])); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-filter me-1"></i>Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php if ($unreadCount > 0): ?>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="mark_all_as_read" class="btn btn-outline-secondary btn-notification">
                                <i class="fas fa-check-double me-1"></i>Mark All as Read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="notifications-list">
                <?php if ($totalNotifications > 0): ?>
                    <?php while ($notification = $notificationsResult->fetch_assoc()): ?>
                        <div class="notification-item <?php echo (int) $notification['is_read'] === 0 ? 'unread' : ''; ?>">
                            <div class="notification-header">
                                <div>
                                    <span class="notification-type <?php echo strtolower($notification['notification_type']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($notification['notification_type'])); ?>
                                    </span>
                                </div>
                                <div style="color: #999; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($notification['created_at']))); ?>
                                </div>
                            </div>
                            
                            <h5 class="notification-title">
                                <?php if ((int) $notification['is_read'] === 0): ?>
                                    <span class="unread-badge"></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($notification['title']); ?>
                            </h5>
                            
                            <p class="notification-message">
                                <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                            </p>
                            
                            <?php if (!empty($notification['related_entity_type'])): ?>
                                <small style="color: #75b16c;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo htmlspecialchars($notification['related_entity_type']); ?>
                                    <?php if ($notification['related_entity_id']): echo ' #' . (int) $notification['related_entity_id']; endif; ?>
                                </small>
                            <?php endif; ?>
                            
                            <div class="notification-actions">
                                <?php if ((int) $notification['is_read'] === 0): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="notification_id" value="<?php echo (int) $notification['notification_id']; ?>">
                                        <button type="submit" name="mark_as_read" class="btn btn-sm btn-outline-success btn-notification">
                                            <i class="fas fa-check me-1"></i>Mark as Read
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (!empty($notification['action_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="btn btn-sm btn-success btn-notification">
                                        <i class="fas fa-arrow-right me-1"></i>View
                                    </a>
                                <?php endif; ?>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="notification_id" value="<?php echo (int) $notification['notification_id']; ?>">
                                    <button type="submit" name="delete_notification" class="btn btn-sm btn-outline-danger btn-notification" onclick="return confirm('Delete this notification?');">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h5>No Notifications</h5>
                        <p>You don't have any notifications matching these filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
