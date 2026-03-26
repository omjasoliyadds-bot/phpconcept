cd c:\wamp64\www\laravel\private-docs; cat > README.md <<'EOF'
# Private-Docs (Laravel)

Private-Docs is a secure document management system built with Laravel and Sanctum API authentication.

## Key features
- User registration with email verification
- Login/logout + session regeneration
- API token management (2h expiry, refresh endpoint, token revocation)
- Rate limiting on login/register/password reset
- Password reset with 15-minute token expiration
- File upload policies: extension + MIME + magic-byte validation
- Audit logging with immutability guard
- Role-based admin/user controls
- Document sharing and permission management

## Requirements
- PHP 8.2
- Composer
- MySQL (or compatible DB)
- Node.js + npm (for frontend asset build)
- Docker + Docker Compose (optional)

## Quick local setup (non-Docker)
1. Copy `.env.example` to `.env`:
   - `cp .env.example .env`
2. Install dependencies:
   - `composer install`
   - `npm install`
3. Generate app key:
   - `php artisan key:generate`
4. Update `.env` database settings:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=private_docs`
   - `DB_USERNAME=<youruser>`
   - `DB_PASSWORD=<yourpassword>`
5. Run migrations and seeders:
   - `php artisan migrate`
   - `php artisan db:seed` (if needed)
6. Run backend:
   - `php artisan serve`
7. Run frontend (optional):
   - `npm run dev`
8. Open: `http://localhost:8000`

## Docker setup
The project supports Docker via Dockerfile and docker-compose.

### Provided files
- `dockerfile` (PHP-FPM container)
- `docker-compose.yml` (app + nginx + mysql)
- `nginx.conf` (NGINX web server config)

### .env for Docker
- `DB_HOST=db`
- `DB_DATABASE=private_docs`
- `DB_USERNAME=appuser`
- `DB_PASSWORD=apppassword`
- `APP_URL=http://localhost:8080`

### Start
```bash
docker-compose up --build -d
```
Then:
```bash
docker-compose exec app php artisan migrate
```
Browse: `http://localhost:8080`

### Stop
```bash
docker-compose down
```

## API endpoints
- `POST /api/register`
- `POST /api/login`
- `POST /api/forgot-password`
- `POST /api/reset`
- `POST /api/token/refresh` (auth)
- `POST /api/documents/upload` (auth)

## Security notes
- Rate limits configured in `app/Providers/RouteServiceProvider.php`.
- Password resets use Laravel broker, 15-min expiry in `config/auth.php`.
- Audits in `app/Models/AuditLog.php` prevent updates/deletes and create content hash.
- Upload check in `app/Http/Controllers/Api/User/DocumentController.php` validates extension/mime/content.

## Switching to S3 storage
1. Configure `config/filesystems.php` and `.env` with S3 credentials.
2. Replace `Storage::disk('local')` calls with `Storage::disk('s3')` in repository methods.

## Troubleshooting
- Permission error: set `chmod -R 775 storage bootstrap/cache` and owner to webserver user.
- CORS/API: ensure `config/cors.php` is configured for your frontend domain.
- Clear caches: `php artisan config:clear`, `php artisan cache:clear`, `php artisan route:clear`, `php artisan view:clear`.

EOF