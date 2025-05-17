<?php
session_start();
require_once __DIR__ . '/../config.php';
$auth = Auth::getInstance();

$auth->requireLogin();

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];
$userType = $_SESSION['userType'];

// Get alerts with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("SELECT * FROM alerts ORDER BY timestamp DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total alerts count for pagination
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM alerts");
$stmt->execute();
$totalAlerts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalAlerts / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/alerts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Alerts</h2>
            <div class="alerts-container">
                <?php if ($userType === 'responder'): ?>
                <div class="alert-actions">
                    <button id="createAlertBtn" class="btn-create">Create New Alert</button>
                </div>
                <?php endif; ?>

                <div class="alerts-list">
                    <?php foreach ($alerts as $alert): ?>
                    <div class="alert-card priority-<?php echo htmlspecialchars($alert['priorityLevel']); ?>">
                        <div class="alert-header">
                            <span class="alert-time"><?php echo date('M d, Y H:i', strtotime($alert['timestamp'])); ?></span>
                            <span class="alert-priority"><?php echo ucfirst(htmlspecialchars($alert['priorityLevel'])); ?></span>
                        </div>
                        <div class="alert-body">
                            <p><?php echo htmlspecialchars($alert['message']); ?></p>
                        </div>
                        <?php if ($userType === 'responder'): ?>
                        <div class="alert-actions">
                            <button onclick="editAlert(<?php echo $alert['alertID']; ?>)" class="btn-edit">Edit</button>
                            <button onclick="deleteAlert(<?php echo $alert['alertID']; ?>)" class="btn-delete">Delete</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $page === $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alert Creation Modal -->
    <div id="alertModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Create New Alert</h3>
            <form id="alertForm">
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <div class="form-group">
                    <label for="priorityLevel">Priority Level</label>
                    <select id="priorityLevel" name="priorityLevel" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Create Alert</button>
            </form>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script src="../js/alerts.js"></script>
</body>
</html>