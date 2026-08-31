# Allan Kimutai Portfolio

A personal portfolio website with a responsive front end, contact form, gallery lightbox, visitor reviews, and an admin page for moderating reviews and viewing contact messages.

## Features

- Portfolio landing page with About, Skills, Gallery, and Contact sections
- Responsive layout styled with `style.css`
- Gallery image lightbox with navigation controls
- Contact messages stored in MySQL
- Visitor reviews stored as pending and displayed after approval
- Password-protected review administration page
- Social links for WhatsApp, Instagram, Telegram, and Discord

## Requirements

- PHP 8.0 or newer with PDO MySQL enabled
- MySQL or MariaDB
- A browser

## Setup

1. Place the project folder in your PHP server directory, or use PHP's built-in development server.
2. Create the database and tables by running `schema.sql` in MySQL:

```sql
SOURCE schema.sql;
```

3. Check the connection settings in `database.php`:

```php
$databaseHost = '127.0.0.1';
$databaseName = 'portfolio';
$databaseUser = 'root';
$databasePassword = '';
```

Update these values if your MySQL installation uses different credentials.

4. From the project directory, start the local server:

```bash
php -S localhost:8000
```

5. Open [http://localhost:8000](http://localhost:8000) in your browser.

## Admin Page

Open `/admin_reviews.php` to manage reviews and view contact messages 
There contact button for whatsapp, telegram and discord

Set an admin password before using the page. The application reads `PORTFOLIO_ADMIN_PASSWORD`; if it is not set, the fallback password is `change-this-password`.

On Windows PowerShell, set it for the current terminal session with:

```powershell
$env:PORTFOLIO_ADMIN_PASSWORD = 'your-secure-password'
php -S localhost:8000
```

Change the fallback password in `admin_reviews.php` or configure the environment variable in production. Do not expose the admin page publicly without a strong password and a properly configured server.

## Project Structure

```text
.
├── index.html           # Main portfolio page
├── style.css            # Site styles and responsive layout
├── contact.php          # Saves contact form submissions
├── review.php           # Review API for reading and submitting reviews
├── admin_reviews.php    # Review moderation and message management
├── database.php         # PDO database connection
├── schema.sql           # Database and table definitions
└── images/              # Portfolio and background images
```

## Notes

- `index.html` can be viewed as a static page, but the contact and review features require PHP and MySQL.
- Reviews are submitted with a `pending` status and must be approved in the admin page before they appear publicly.
- Keep database credentials and the admin password out of source control in a production deployment.
