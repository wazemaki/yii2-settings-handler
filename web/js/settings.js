/**
 * Settings form interactions
 */
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    document.querySelectorAll('.password-toggle-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const wrapper = this.closest('.password-wrapper');
            const input = wrapper.querySelector('.password-input');
            const eyeIcon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    });

    // Reset checkbox behavior
    document.querySelectorAll('.reset-checkbox').forEach(resetCheckbox => {
        const label = document.querySelector('label[for="' + resetCheckbox.id + '"]');

        resetCheckbox.addEventListener('change', function() {
            const key = this.getAttribute('id').replace('settings-reset_', '');
            const type = this.getAttribute('data-setting-type');
            const input = document.getElementById('dynamicmodel-' + key);
            const defaultValue = this.getAttribute('data-default');

            // Update label visual state
            if (label) {
                label.classList.toggle('is-checked', this.checked);
            }

            if (this.checked) {
                // Store current value before resetting
                if (type === 'checkbox') {
                    this.setAttribute('data-previous-checked', input.checked);
                } else {
                    this.setAttribute('data-previous-value', input.value);
                }

                // Reset to default
                if (type === 'checkbox') {
                    input.checked = defaultValue === '1';
                } else {
                    input.value = defaultValue || '';
                }
            } else {
                // Uncheck - restore previous value
                if (type === 'checkbox') {
                    input.checked = this.getAttribute('data-previous-checked') === 'true';
                } else {
                    input.value = this.getAttribute('data-previous-value') || '';
                }
            }
        });
    });

    // Uncheck reset when input changes
    document.querySelectorAll('.setting-input').forEach(input => {
        input.addEventListener('change', function() {
            const key = this.getAttribute('id').replace('dynamicmodel-', '');
            const resetCheckbox = document.getElementById('settings-reset_' + key);
            let label;

            if (resetCheckbox) {
                resetCheckbox.checked = false;
                label = document.querySelector('label[for="' + resetCheckbox.id + '"]');
            }
            if (label) {
                label.classList.remove('is-checked');
            }
        });

        // For text inputs, also handle 'input' event
        if (input.type === 'text' || input.type === 'number' || input.type === 'password' || input.tagName === 'TEXTAREA') {
            input.addEventListener('input', function() {
                const key = this.getAttribute('id').replace('dynamicmodel-', '');
                const resetCheckbox = document.getElementById('settings-reset_' + key);
                let label;

                if (resetCheckbox) {
                    resetCheckbox.checked = false;
                    label = document.querySelector('label[for="' + resetCheckbox.id + '"]');
                }
                if (label) {
                    label.classList.remove('is-checked');
                }
            });
        }
    });
});
