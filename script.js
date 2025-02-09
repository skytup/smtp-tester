document.addEventListener('DOMContentLoaded', () => {

    // tinymce.init({
    //     selector: '#message',
    //     height: 400,
    //     menubar: false,
    //     plugins: [
    //         'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    //         'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    //         'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
    //     ],
    //     toolbar: 'undo redo | blocks | ' +
    //         'bold italic forecolor | alignleft aligncenter ' +
    //         'alignright alignjustify | bullist numlist outdent indent | ' +
    //         'removeformat | help',
    //     content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
    // });


    const smtpForm = document.getElementById('smtpForm');
    const emailForm = document.getElementById('emailForm');
    const connectBtn = document.getElementById('connectBtn');
    const sendBtn = document.getElementById('sendBtn');
    const alertDiv = document.getElementById('alert');

    // Load saved SMTP settings
    const savedSettings = JSON.parse(localStorage.getItem('smtpSettings') || '{}');
    if (savedSettings.host) {
        document.getElementById('host').value = savedSettings.host;
        document.getElementById('username').value = savedSettings.username;
        document.getElementById('port').value = savedSettings.port;
    }

    // Form validation function
    function validateEmailForm() {
        const to = document.getElementById('to').value;
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;
        sendBtn.disabled = !(to && subject && message);
    }

    ['to', 'subject', 'message'].forEach(id => {
        document.getElementById(id).addEventListener('input', validateEmailForm);
    });

    function showAlert(message, type) {
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;
        alertDiv.style.display = 'flex';
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 5000);
    }

    function setLoading(button, isLoading) {
        button.classList.toggle('loading', isLoading);
        button.disabled = isLoading;
    }

    smtpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        setLoading(connectBtn, true);

        const formData = {
            host: document.getElementById('host').value,
            username: document.getElementById('username').value,
            password: document.getElementById('password').value,
            port: document.getElementById('port').value
        };

        try {
            const response = await fetch('backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'test_connection',
                    ...formData
                })
            });

            const data = await response.json();

            if (data.success) {
                const settingsToSave = { ...formData };
                delete settingsToSave.password;
                localStorage.setItem('smtpSettings', JSON.stringify(settingsToSave));

                showAlert('SMTP connection successful!', 'success');
                emailForm.style.display = 'block';
            } else {
                showAlert(data.message || 'Connection failed!', 'error');
            }
        } catch (error) {
            showAlert('An error occurred while testing the connection.', 'error');
        } finally {
            setLoading(connectBtn, false);
        }
    });

    emailForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        setLoading(sendBtn, true);

        const emailData = {
            to: document.getElementById('to').value,
            subject: document.getElementById('subject').value,
            message: document.getElementById('message').value,
            ...JSON.parse(localStorage.getItem('smtpSettings')),
            password: document.getElementById('password').value
        };

        try {
            const response = await fetch('backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_email',
                    ...emailData
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('Email sent successfully!', 'success');
                emailForm.reset();
                sendBtn.disabled = true;
            } else {
                alert(data.message);
                showAlert(data.message || 'Failed to send email!', 'error');
            }
        } catch (error) {
            showAlert('An error occurred while sending the email.', 'error');
        } finally {
            setLoading(sendBtn, false);
        }
    });

    // Add input validation for real-time feedback
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('invalid', (e) => {
            e.preventDefault();
            input.classList.add('invalid');
        });

        input.addEventListener('input', () => {
            input.classList.remove('invalid');
        });
    });

    // Add password visibility toggle
    const passwordInput = document.getElementById('password');
    const togglePassword = document.createElement('i');
    togglePassword.className = 'fas fa-eye password-toggle';
    togglePassword.style.cssText = `
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--text-secondary);
    `;

    passwordInput.parentElement.appendChild(togglePassword);

    togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePassword.className = `fas fa-eye${type === 'password' ? '' : '-slash'} password-toggle`;
    });

    // Add responsive design adjustments
    function adjustLayoutForScreenSize() {
        const card = document.querySelector('.card');
        if (window.innerWidth <= 640) {
            card.style.padding = '1.5rem';
        } else {
            card.style.padding = '2rem';
        }
    }

    window.addEventListener('resize', adjustLayoutForScreenSize);
    adjustLayoutForScreenSize();
});