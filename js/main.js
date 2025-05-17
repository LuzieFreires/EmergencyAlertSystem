document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });

    // Handle responsive behavior
    function handleResize() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
        }
    }

    // Initial check and event listener for window resize
    handleResize();
    window.addEventListener('resize', handleResize);

    // Update alert count
    async function updateAlertCount() {
        try {
            const response = await fetch('../pages/handlers/get_unread_alerts.php');
            const data = await response.json();
            const alertCount = document.getElementById('alertCount');
            
            if (data.count > 0) {
                alertCount.textContent = data.count;
                alertCount.style.display = 'inline';
            } else {
                alertCount.style.display = 'none';
            }
        } catch (error) {
            console.error('Error updating alert count:', error);
        }
    }

    // Update alert count every 30 seconds
    updateAlertCount();
    setInterval(updateAlertCount, 30000);
});

// Emergency map initialization
function initMap() {
    const map = L.map('emergency-map').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Get user's location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const { latitude, longitude } = position.coords;
            map.setView([latitude, longitude], 13);
            L.marker([latitude, longitude]).addTo(map);
        });
    }

    return map;
}