php docker/init-db.php
exec php -S 0.0.0.0:${PORT:-8000} -t /app
