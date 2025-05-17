<?php
session_start();
require_once '../config.php';
require_once '../classes/Auth.php';  
$auth = Auth::getInstance();

$auth->requireLogin();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$userID = $_SESSION['userID'];
$userType = $_SESSION['userType'];

// Get statistics based on user type
$stats = [];
$recentAlerts = [];
$activeEmergencies = [];

try {
    if ($userType === 'responder') {
        // Get responder-specific stats
        $stmt = $conn->prepare("SELECT COUNT(*) as total_assigned FROM emergencies WHERE responderID = ? AND status != 'resolved'");
        $stmt->execute([$userID]);
        $stats['assigned'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_assigned'];
        
        // Get active emergencies assigned to this responder
        $stmt = $conn->prepare("SELECT e.*, r.name as resident_name 
                               FROM emergencies e 
                               JOIN users r ON e.residentID = r.userID 
                               WHERE e.responderID = ? AND e.status != 'resolved' 
                               ORDER BY e.created_at DESC LIMIT 5");
        $stmt->execute([$userID]);
        $activeEmergencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Get resident-specific stats
        $stmt = $conn->prepare("SELECT COUNT(*) as total_requests FROM emergencies WHERE residentID = ?");
        $stmt->execute([$userID]);
        $stats['total_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_requests'];
    }
    
    // Get recent alerts for all users
    $stmt = $conn->prepare("SELECT * FROM alerts ORDER BY timestamp DESC LIMIT 5");
    $stmt->execute();
    $recentAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- Add this in the head section -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Add this in the main content section -->
    <div class="map-container">
        <h3>Emergency Map</h3>
        <div id="dashboard-map"></div>
    </div>
</head>
<body>
    <div class="container-fluid">
    <?php include 'includes/sidebar.php'; ?>
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Emergency Alert System</h3>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="emergency-form.php">Report Emergency</a></li>
                <li><a href="alerts.php">Alerts</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
                <p class="user-type"><?php echo ucfirst(htmlspecialchars($userType)); ?></p>
            </div>

            <div class="dashboard-grid">
                <!-- Statistics Cards -->
                <div class="card stats-card">
                    <h3>Statistics</h3>
                    <?php if ($userType === 'responder'): ?>
                        <p>Active Assignments: <?php echo $stats['assigned']; ?></p>
                    <?php else: ?>
                        <p>Total Requests: <?php echo $stats['total_requests']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Recent Alerts -->
                <div class="card alerts-card">
                    <h3>Recent Alerts</h3>
                    <div class="alerts-list">
                        <?php foreach ($recentAlerts as $alert): ?>
                            <div class="alert-item priority-<?php echo htmlspecialchars($alert['priorityLevel']); ?>">
                                <p class="alert-message"><?php echo htmlspecialchars($alert['message']); ?></p>
                                <span class="alert-time"><?php echo date('M d, H:i', strtotime($alert['timestamp'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Active Emergencies for Responders -->
                <?php if ($userType === 'responder' && !empty($activeEmergencies)): ?>
                    <div class="card emergencies-card">
                        <h3>Active Emergencies</h3>
                        <div class="emergencies-list">
                            <?php foreach ($activeEmergencies as $emergency): ?>
                                <div class="emergency-item severity-<?php echo htmlspecialchars($emergency['severityLevel']); ?>">
                                    <h4>Emergency #<?php echo $emergency['emergencyID']; ?></h4>
                                    <p>Resident: <?php echo htmlspecialchars($emergency['resident_name']); ?></p>
                                    <p>Type: <?php echo htmlspecialchars($emergency['type']); ?></p>
                                    <p>Status: <?php echo htmlspecialchars($emergency['status']); ?></p>
                                    <button onclick="viewEmergencyDetails(<?php echo $emergency['emergencyID']; ?>)">View Details</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Emergency Map -->
                <div class="card map-card">
                    <h3>Emergency Map</h3>
                    <div id="dashboard-map"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="../js/main.js"></script>
    <script>
        // Initialize dashboard map
        const dashboardMap = initMap();

        // Function to view emergency details
        function viewEmergencyDetails(emergencyID) {
            window.location.href = `emergency-details.php?id=${emergencyID}`;
        }
    </script>
</body>
</html>