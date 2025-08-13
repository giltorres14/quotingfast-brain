# 🚨 CRITICAL DATABASE CONFIGURATION 🚨

## THE ONLY DATABASE TO USE:
**PostgreSQL on Render** - `brain_production`
- Host: dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com
- Database: brain_production
- Username: brain_user
- Password: KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ

## ❌ NEVER USE:
- SQLite (database.sqlite)
- Any local database
- Any other database

## ENFORCEMENT RULES:
1. **ALWAYS** override DB config in scripts to use PostgreSQL
2. **NEVER** use the local .env file's DB settings
3. **ALWAYS** prefix commands with PostgreSQL env vars
4. **DELETE** database.sqlite to prevent mistakes

## COMMAND PREFIX FOR ALL OPERATIONS:
```bash
DB_CONNECTION=pgsql \
DB_HOST=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com \
DB_PORT=5432 \
DB_DATABASE=brain_production \
DB_USERNAME=brain_user \
DB_PASSWORD=KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ \
php artisan [command]
```

## MISTAKE COUNTER:
- Used SQLite instead of PostgreSQL: 5+ times
- This MUST be 0 going forward

Created: 2025-01-12
Last Mistake: 2025-01-12 (importing to wrong database)
