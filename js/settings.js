document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.settings-form');

    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formId = this.id;

            // Password validation for password form
            if (formId === 'passwordForm') {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (newPassword && newPassword !== confirmPassword) {
                    alert('New passwords do not match!');
                    return;
                }
            }

            const formData = new FormData(this);
            formData.append('form_type', formId); // Identify which section is updated

            try {
                const response = await fetch('../pages/handlers/settings_handler.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });

                const data = await response.json(); // Await the JSON conversion

                if (data.success) {
                    alert(data.message);
                    window.location.href = 'profile.php';
                } else {
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating settings.');
            }
        });
    });
});
