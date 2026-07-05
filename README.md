# example_webapp – PHP Blog

A simple PHP blog with a public front page and a password-protected admin backend.

## Features

- **Front page** – lists all published articles with excerpts, newest first
- **Single-post view** – full article content
- **Admin dashboard** – list, create and delete blog posts
- **Login protection** – session-based authentication with CSRF protection
- **Zero setup database** – SQLite via PDO (no database server needed)

## Requirements

- PHP 8.0+ (with `pdo_sqlite` extension enabled)
- A web server (Apache, Nginx, or PHP's built-in server)

## Quick Start

```bash
# From the project root
php -S localhost:8080
```

Then open <http://localhost:8080> in your browser.

## Default Admin Credentials

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `blogadmin`|

> ⚠️ **Change the password before deploying to a public server.**  
> Generate a new hash and replace `ADMIN_PASS` in `admin.php`:
> ```bash
> php -r "echo password_hash('YOUR_NEW_PASSWORD', PASSWORD_BCRYPT, ['cost' => 12]);"
> ```

## Project Structure

```
.
├── index.php          # Public blog front page
├── post.php           # Single post view
├── admin.php          # Admin login + dashboard (new / delete posts)
├── includes/
│   ├── db.php         # PDO / SQLite helper
│   └── functions.php  # CSRF, auth helpers, output escaping
├── assets/
│   └── style.css      # Stylesheet
└── data/              # SQLite database is created here automatically
```

## Security Notes

- All user-supplied output is escaped with `htmlspecialchars`.
- Database queries use prepared statements (no SQL injection).
- Forms are protected with a per-session CSRF token.
- Passwords are hashed with bcrypt (`password_hash`/`password_verify`).
- The `data/` directory should not be publicly accessible in production  
  (add `Deny from all` in Apache, or a `location` block in Nginx).