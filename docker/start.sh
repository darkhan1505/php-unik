#!/bin/sh
# Схема идемпотентна (IF NOT EXISTS / ON CONFLICT), поэтому безопасно применять при каждом старте.
php docker/init-db.php
exec php -S 0.0.0.0:${PORT:-8000} -t /app
