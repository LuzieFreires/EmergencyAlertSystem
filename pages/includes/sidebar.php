<?php
$userType = $_SESSION['userType'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../assets/logo.png" alt="EAS Logo" class="logo">
        <h3>Emergency Alert System</h3>
        <button id="sidebar-toggle" class="sidebar-toggle">
            <span></span>
        </button>
    </div>

    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-details">
            <p class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></p>
            <p class="user-role"><?php echo ucfirst(htmlspecialchars($userType)); ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo isActive('dashboard.php'); ?>">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($userType === 'resident'): ?>
            <li class="<?php echo isActive('emergency-form.php'); ?>">
                <a href="emergency-form.php">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Report Emergency</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($userType === 'responder'): ?>
            <li class="<?php echo isActive('active-emergencies.php'); ?>">
                <a href="active-emergencies.php">
                    <i class="fas fa-ambulance"></i>
                    <span>Active Emergencies</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="<?php echo isActive('alerts.php'); ?>">
                <a href="alerts.php">
                    <i class="fas fa-bell"></i>
                    <span>Alerts</span>
                    <span class="alert-badge" id="alertCount"></span>
                </a>
            </li>

            <li class="<?php echo isActive('settings.php'); ?>">
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>

            <li>
                <a href="auth/logout_handler.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>