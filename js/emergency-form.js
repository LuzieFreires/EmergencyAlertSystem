function initMap() {
    // Initialize map
    const map = L.map('emergency-map').setView([0, 0], 2);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Try to get user's location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 13);
            },
            (error) => {
                console.error('Geolocation error:', error);
            }
        );
    }

    return map;
}

document.addEventListener('DOMContentLoaded', function() {
    const emergencyForm = document.getElementById('emergencyForm');
    const map = initMap();
    let marker = null;

    // Handle map clicks to set location
    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });

    // Handle form submission
    emergencyForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!document.getElementById('latitude').value || 
            !document.getElementById('longitude').value) {
            alert('Please select a location on the map');
            return;
        }

        const formData = new FormData(this);
        try {
            const response = await fetch('../pages/handlers/emergency_handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Emergency reported successfully!');
                window.location.href = 'dashboard.php';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while submitting the emergency report.');
        }
    });
});