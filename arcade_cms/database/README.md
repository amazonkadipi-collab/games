# Arcade CMS database migration

The production site is the Arcade CMS under `arcade_cms/`. The existing theme and UI are intentionally unchanged.

## Target database

Neon PostgreSQL project: `games`.

The CMS currently uses MySQLi and MySQL-specific SQL, so the migration is staged. The live application must not be switched to PostgreSQL until the SQL and data-access layer are converted and verified.

## Migration order

1. Keep the existing MySQLi path available during the transition.
2. Add PostgreSQL/PDO runtime support to the Vercel container.
3. Inventory every CMS table and every MySQL-specific query.
4. Build the PostgreSQL schema from the CMS schema source; do not guess missing columns.
5. Add a PDO/PostgreSQL data-access layer.
6. Convert MySQL-only SQL constructs such as AUTO_INCREMENT, SHOW COLUMNS, ALTER ... AFTER, INSERT IGNORE, ON DUPLICATE KEY, and MySQL quoting.
7. Move persistent generated files such as sitemaps/images out of the ephemeral Vercel filesystem.
8. Verify public and admin routes without changing the existing design.
9. Only then switch production database configuration to Neon.

## Current blocker

The connected Neon tool has not resolved the user's `games` project by name yet. No production database schema or data was changed in this migration step.
