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
            formData.append('form_type', formId); // Add form type to identify which section is being updated

            try {
                const response = await fetch('../pages/handlers/settings_handler.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });

                const result = await response.json();
                if (result.success) {
                    alert(`${formId.replace('Form', '')} updated successfully!`);
                    if (formId === 'passwordForm') {
                        // Clear password fields on successful update
                        this.reset();
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating settings.');
            }
        });
    });
});