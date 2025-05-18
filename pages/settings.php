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
    <title>Settings - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">       
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Settings</h2>
            <div class="settings-container">
                <form id="settingsForm">
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_num">Contact Number</label>
                            <input type="tel" id="contact_num" name="contact_num" value="<?php echo htmlspecialchars($user['contact_num']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="location">Default Location</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" required>
                        </div>
                    </div>

                    <?php if ($userType === 'resident'): ?>
                    <div class="form-section">
                        <h3>Medical Information</h3>
                        <div class="form-group">
                            <label for="medicalCondition">Medical Conditions</label>
                            <textarea id="medicalCondition" name="medicalCondition"><?php echo htmlspecialchars($additionalData['medicalCondition']); ?></textarea>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($userType === 'responder'): ?>
                    <div class="form-section">
                        <h3>Responder Information</h3>
                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($additionalData['specialization']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="availabilityStatus">Availability Status</label>
                            <select id="availabilityStatus" name="availabilityStatus">
                                <option value="1" <?php echo $additionalData['availabilityStatus'] ? 'selected' : ''; ?>>Available</option>
                                <option value="0" <?php echo !$additionalData['availabilityStatus'] ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-section">
                        <h3>Change Password</h3>
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="currentPassword">
                        </div>
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="newPassword">
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword">
                        </div>
                    </div>

                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/settings.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>