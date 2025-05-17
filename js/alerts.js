async function createAlert(formData) {
    try {
        const response = await fetch('/group3/pages/handlers/alert_handler.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to create alert');
        }

        // Show success message
        showNotification('Alert created successfully', 'success');
        
        // Refresh alerts list
        loadRecentAlerts();
        
        return data;
    } catch (error) {
        showNotification(error.message, 'error');
        throw error;
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

async function loadRecentAlerts() {
    const alertsContainer = document.getElementById('alerts-container');
    
    try {
        const response = await fetch('/group3/pages/handlers/get_alerts.php');
        const alerts = await response.json();
        
        alertsContainer.innerHTML = alerts.map(alert => `
            <div class="alert-card ${alert.priorityLevel}">
                <div class="alert-header">
                    <span class="priority">${alert.priorityLevel.toUpperCase()}</span>
                    <span class="timestamp">${new Date(alert.timestamp).toLocaleString()}</span>
                </div>
                <div class="alert-message">${alert.message}</div>
            </div>
        `).join('');
    } catch (error) {
        showNotification('Failed to load alerts', 'error');
    }
}

// Initialize alerts page
document.addEventListener('DOMContentLoaded', () => {
    loadRecentAlerts();
    
    // Handle alert form submission
    const alertForm = document.getElementById('alert-form');
    if (alertForm) {
        alertForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(alertForm);
            try {
                await createAlert(formData);
                alertForm.reset();
            } catch (error) {
                console.error('Alert creation failed:', error);
            }
        });
    }
});