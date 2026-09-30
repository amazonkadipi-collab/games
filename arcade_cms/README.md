# Arcade CMS

Installed GamePortalScript / GameMonetize Free CMS 9.1 code, kept under `arcade_cms/` so the existing Next.js game frontend remains intact.

## Local/server setup

1. Use PHP 8.0+ with MySQLi, cURL, mbstring, ZipArchive, and MySQL/MariaDB.
2. Create an empty database and database user.
3. Copy `assets/includes/config.php.example` to `assets/includes/config.php` and fill in the database values plus a random 64-character hex encryption key.
4. Configure the document root to `arcade_cms/` and enable Apache/LiteSpeed URL rewriting. Nginx needs equivalent rewrite rules.
5. Import the CMS database catalog separately if required; do not commit production database credentials or install locks.

The live server configuration and installation lock were intentionally excluded from Git.
