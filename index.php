<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Tester - Skytup</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --error-color: #dc2626;
            --success-color: #16a34a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
            flex: 1;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }

        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        button {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: var(--secondary-color);
        }

        button:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }

        .email-form {
            display: none;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background-color: #fee2e2;
            color: var(--error-color);
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #dcfce7;
            color: var(--success-color);
            border: 1px solid #bbf7d0;
        }

        footer {
            text-align: center;
            padding: 1rem;
            background-color: white;
            color: #6b7280;
            margin-top: 2rem;
        }

        footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .container {
                margin: 1rem auto;
            }

            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>SMTP Tester</h1>
            <div id="alert" style="display: none;"></div>
            
            <form id="smtpForm">
                <div class="form-group">
                    <label for="host">SMTP Host</label>
                    <input type="text" id="host" required placeholder="e.g., smtp.gmail.com">
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" required placeholder="Email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" required placeholder="SMTP password or app password">
                </div>
                
                <div class="form-group">
                    <label for="port">Port</label>
                    <input type="number" id="port" required placeholder="e.g., 587" value="587">
                </div>
                
                <button type="submit" id="connectBtn">Connect</button>
            </form>
            
            <form id="emailForm" class="email-form">
                <div class="form-group">
                    <label for="to">To Email</label>
                    <input type="email" id="to" required placeholder="recipient@example.com">
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" required placeholder="Email subject">
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" rows="5" required placeholder="Your message"></textarea>
                </div>
                
                <button type="submit" id="sendBtn" disabled>Send Email</button>
            </form>
        </div>
    </div>
    
    <footer>
        <p>© 2024 <a href="https://www.skytup.com" target="_blank">www.skytup.com</a></p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const smtpForm = document.getElementById('smtpForm');
            const emailForm = document.getElementById('emailForm');
            const sendBtn = document.getElementById('sendBtn');
            const alertDiv = document.getElementById('alert');

            // Load saved SMTP settings
            const savedSettings = JSON.parse(localStorage.getItem('smtpSettings') || '{}');
            if (savedSettings.host) {
                document.getElementById('host').value = savedSettings.host;
                document.getElementById('username').value = savedSettings.username;
                document.getElementById('port').value = savedSettings.port;
                // Don't load the password for security reasons
            }

            // Form validation function
            function validateEmailForm() {
                const to = document.getElementById('to').value;
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value;
                sendBtn.disabled = !(to && subject && message);
            }

            // Add validation listeners
            ['to', 'subject', 'message'].forEach(id => {
                document.getElementById(id).addEventListener('input', validateEmailForm);
            });

            // Show alert function
            function showAlert(message, type) {
                alertDiv.className = `alert alert-${type}`;
                alertDiv.textContent = message;
                alertDiv.style.display = 'block';
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }

            // Handle SMTP connection
            smtpForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
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
                        // Save settings to localStorage (excluding password)
                        const settingsToSave = {...formData};
                        delete settingsToSave.password;
                        localStorage.setItem('smtpSettings', JSON.stringify(settingsToSave));
                        
                        showAlert('SMTP connection successful!', 'success');
                        emailForm.style.display = 'block';
                    } else {
                        showAlert(data.message || 'Connection failed!', 'error');
                    }
                } catch (error) {
                    showAlert('An error occurred while testing the connection.', 'error');
                }
            });

            // Handle email sending
            emailForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
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
                        showAlert(data.message || 'Failed to send email!', 'error');
                    }
                } catch (error) {
                    showAlert('An error occurred while sending the email.', 'error');
                }
            });
        });
    </script>
</body>
</html>