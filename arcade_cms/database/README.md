# Arcade CMS database

The production database is the Neon PostgreSQL project **Games** on its `production` branch. The CMS uses the PostgreSQL compatibility adapter at `assets/includes/db.php` and reads its connection from `DATABASE_URL`.

## Verified production state

- 17 CMS tables present
- 37,882 games
- 23 categories
- 1 active administrator account
- `gm_setting.site_url`: `https://pokicrazygames.vercel.app`
- `gm_setting.site_theme`: `kizi`

`schema.postgres.sql` is the reproducible schema/seed reference. It must not be applied directly to production without a backup and a migration review. Existing Neon data is preserved by the application deployment.

## Deployment requirements

1. Add `DATABASE_URL` to Vercel Production using the Neon project `Games` production connection string.
2. Redeploy after changing database credentials.
3. Verify `/`, `/login`, `/admin`, `/sitemap.xml`, and a game URL.
4. Keep generated runtime files and secrets out of Git.
