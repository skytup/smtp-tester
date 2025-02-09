# SMTP Tester

A simple SMTP tester using PHPMailer to verify email sending functionality.

## Features
- Send test emails using SMTP
- Configure SMTP settings easily
- Uses PHPMailer installed via Composer
- Responsive and user-friendly UI

## Installation

### Prerequisites
- PHP 7.4 or later
- Composer installed

### Steps
1. Clone the repository:
   ```sh
   git clone https://github.com/skytup/smtp-tester.git
   cd smtp-tester
   ```

2. Install dependencies using Composer:
   ```sh
   composer install
   ```

3. Configure SMTP settings in `.env` (if applicable) or in the UI modal.

## Usage
1. Start a local server:
   ```sh
   php -S localhost:8000
   ```
2. Open your browser and go to:
   ```
   http://localhost:8000
   ```
3. Enter your SMTP credentials and send a test email.

## License
This project is licensed under the Apache 2.0

## Author
[Akash Vishwakarma](https://github.com/akash2v)
