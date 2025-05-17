<?php
session_start();
require_once __DIR__ . '/../config.php';
$auth = Auth::getInstance();

$auth->requireLogin();

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Emergency - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/emergency-form.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div class="container-fluid">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Report Emergency</h2>
            <div class="emergency-form-container">
                <form id="emergencyForm">
                    <div class="form-group">
                        <label for="type">Emergency Type</label>
                        <select id="type" name="type" required>
                            <option value="medical">Medical Emergency</option>
                            <option value="fire">Fire</option>
                            <option value="police">Police</option>
                            <option value="accident">Accident</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="severityLevel">Severity Level</label>
                        <select id="severityLevel" name="severityLevel" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <div id="emergency-map"></div>
                        <small>Click on the map to set the emergency location</small>
                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>
                    </div>

                    <button type="submit" class="btn-submit">Report Emergency</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/emergency-form.js"></script>
</body>
</html>