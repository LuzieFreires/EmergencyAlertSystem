document.addEventListener('DOMContentLoaded', function() {
    const settingsForm = document.getElementById('settingsForm');

    settingsForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate password fields if they're filled
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword && newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            return;
        }

        const formData = new FormData(settingsForm);
        try {
            const response = await fetch('../pages/handlers/settings_handler.php', {
                method: 'POST',
                body: formData,
                credentials: 'include' // Add this to ensure session is sent
            });

            const result = await response.json();
            if (result.success) {
                alert('Settings updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating settings.');
        }
    });
});