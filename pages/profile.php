<?php
session_start();
require_once __DIR__ . '/../config.php';

$auth = Auth::getInstance();
$auth->requireLogin();

$conn = Database::getInstance()->getConnection();

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];
$userType = $_SESSION['userType'];

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get additional data based on user type
if ($userType === 'resident') {
    $stmt = $conn->prepare("SELECT * FROM residents WHERE residentID = ?");
    $stmt->execute([$userID]);
    $additionalData = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT * FROM responders WHERE responderID = ?");
    $stmt->execute([$userID]);
    $additionalData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">       
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Profile Information</h2>
            <div class="profile-container">
                <div class="profile-section">
                    <h3>Personal Information</h3>
                    <div class="info-group">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>

                    <div class="info-group">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>

                    <div class="info-group">
                        <label>Contact Number:</label>
                        <span><?php echo htmlspecialchars($user['contact_num']); ?></span>
                    </div>

                    <div class="info-group">
                        <label>Default Location:</label>
                        <span><?php echo htmlspecialchars($user['location']); ?></span>
                    </div>
                </div>

                <?php if ($userType === 'resident'): ?>
                <div class="profile-section">
                    <h3>Medical Information</h3>
                    <div class="info-group">
                        <label>Medical Conditions:</label>
                        <span><?php echo htmlspecialchars($additionalData['medicalCondition'] ?? 'None'); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($userType === 'responder'): ?>
                <div class="profile-section">
                    <h3>Responder Information</h3>
                    <div class="info-group">
                        <label>Specialization:</label>
                        <span><?php echo htmlspecialchars($additionalData['specialization']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Availability Status:</label>
                        <span class="status-<?php echo $additionalData['availabilityStatus'] ? 'available' : 'unavailable'; ?>">
                            <?php echo $additionalData['availabilityStatus'] ? 'Available' : 'Unavailable'; ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="profile-actions">
                    <a href="settings.php" class="btn-edit">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>