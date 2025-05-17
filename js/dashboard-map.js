let dashboardMap;
let markers = [];

function initDashboardMap() {
    dashboardMap = L.map('dashboard-map').setView([0, 0], 2);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(dashboardMap);

    // Try to get user's location for initial view
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                dashboardMap.setView([latitude, longitude], 13);
            }
        );
    }

    return dashboardMap;
}

function updateEmergencyMarkers(emergencies) {
    // Clear existing markers
    markers.forEach(marker => marker.remove());
    markers = [];

    emergencies.forEach(emergency => {
        const marker = L.marker([emergency.latitude, emergency.longitude])
            .addTo(dashboardMap);

        // Create popup content
        const popupContent = `
            <div class="emergency-popup">
                <h4>${emergency.type}</h4>
                <p><strong>Severity:</strong> ${emergency.severityLevel}</p>
                <p><strong>Status:</strong> ${emergency.status}</p>
                <p><strong>Reported:</strong> ${new Date(emergency.created_at).toLocaleString()}</p>
                ${emergency.responderID ? `<p><strong>Assigned To:</strong> ${emergency.responder_name}</p>` : ''}
            </div>
        `;

        marker.bindPopup(popupContent);
        markers.push(marker);
    });

    // Fit map bounds to show all markers
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        dashboardMap.fitBounds(group.getBounds().pad(0.1));
    }
}

// Initialize map when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initDashboardMap();
    
    // Fetch and update emergency markers periodically
    async function fetchEmergencies() {
        try {
            const response = await fetch('../pages/handlers/get_emergencies.php');
            const emergencies = await response.json();
            updateEmergencyMarkers(emergencies);
        } catch (error) {
            console.error('Error fetching emergencies:', error);
        }
    }

    // Initial fetch
    fetchEmergencies();

    // Update every 30 seconds
    setInterval(fetchEmergencies, 30000);
});