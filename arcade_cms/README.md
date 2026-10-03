# Arcade CMS — production runtime

This repository now contains the PHP Arcade CMS as the only application. The previous Next.js frontend was removed so Vercel routes every request to the CMS container.

## Production architecture

- **Runtime:** FrankenPHP / PHP 8.3 container on Vercel
- **Database:** Neon PostgreSQL project `Games`, production branch
- **Site:** https://pokicrazygames.vercel.app
- **Theme:** Kizi
- **Catalog:** 37,882 games in Neon at the time of migration

## Required Vercel configuration

Set `DATABASE_URL` in Vercel Production to the Neon connection string for project `Games`. Do not commit that value to GitHub. The repository intentionally excludes `assets/includes/config.php` and installation-state files.

`vercel.json` and the root `Dockerfile.vercel` route the complete domain to `arcade_cms/`.

## Local checks

```bash
php -l arcade_cms/index.php
php -S 127.0.0.1:8080 -t arcade_cms
```

The CMS uses the PostgreSQL compatibility adapter in `arcade_cms/assets/includes/db.php`; do not restore the original MySQL installer into the production Vercel container.
