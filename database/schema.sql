-- Схема базы данных для учебного проекта TaskFlow (PostgreSQL)
-- 1) Создайте базу:      createdb php_unik   (или CREATE DATABASE php_unik; в psql)
-- 2) Импортируйте схему: psql -U postgres -d php_unik -f database/schema.sql

-- Пользователи
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(10) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Категории задач (справочник)
CREATE TABLE IF NOT EXISTS categories (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    color      VARCHAR(20) NOT NULL DEFAULT 'slate',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Задачи пользователей
CREATE TABLE IF NOT EXISTS tasks (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    status      VARCHAR(20) NOT NULL DEFAULT 'todo' CHECK (status IN ('todo', 'in_progress', 'done')),
    priority    VARCHAR(10) NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
    due_date    DATE,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Триггер: автоматически обновлять updated_at при изменении задачи
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_tasks_updated_at ON tasks;
CREATE TRIGGER trg_tasks_updated_at
    BEFORE UPDATE ON tasks
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

-- Начальные категории
INSERT INTO categories (name, color) VALUES
    ('Работа', 'blue'),
    ('Учёба', 'purple'),
    ('Личное', 'green'),
    ('Другое', 'slate')
ON CONFLICT (name) DO NOTHING;

-- Демонстрационный администратор: email admin@taskflow.local / пароль admin123
-- (хэш ниже сгенерирован через password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO users (name, email, password_hash, role) VALUES
    ('Администратор', 'admin@taskflow.local', '$2y$10$AAkZp5rouJTCWOVZdHIJFeNKHT/RPhUC1SFC0J.pJFe3wIN9lISV2', 'admin')
ON CONFLICT (email) DO NOTHING;
