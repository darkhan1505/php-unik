CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(10) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id   SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS products (
    id          SERIAL PRIMARY KEY,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name        VARCHAR(150) NOT NULL,
    subtitle    VARCHAR(200) NOT NULL DEFAULT '',
    description TEXT,
    price       INTEGER NOT NULL CHECK (price >= 0),
    image_url   VARCHAR(500) NOT NULL DEFAULT '',
    sizes       VARCHAR(100) NOT NULL DEFAULT 'S,M,L,XL',
    in_stock    BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    full_name  VARCHAR(150) NOT NULL,
    phone      VARCHAR(40) NOT NULL,
    address    VARCHAR(300) NOT NULL,
    total      INTEGER NOT NULL,
    status     VARCHAR(20) NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'shipped', 'done', 'cancelled')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id           SERIAL PRIMARY KEY,
    order_id     INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id   INTEGER REFERENCES products(id) ON DELETE SET NULL,
    product_name VARCHAR(150) NOT NULL,
    price        INTEGER NOT NULL,
    size         VARCHAR(10) NOT NULL,
    quantity     INTEGER NOT NULL CHECK (quantity > 0)
);

CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_products_updated_at ON products;
CREATE TRIGGER trg_products_updated_at
    BEFORE UPDATE ON products
    FOR EACH ROW
    EXECUTE FUNCTION set_updated_at();

INSERT INTO categories (name) VALUES ('Кожаные'), ('Городские'), ('Повседневные'), ('Пуховики')
ON CONFLICT (name) DO NOTHING;

INSERT INTO products (category_id, name, subtitle, description, price, image_url, sizes)
SELECT c.id, p.name, p.subtitle, p.description, p.price, p.image_url, p.sizes
FROM (VALUES
    ('Кожаные', 'Classic', 'Кожаная куртка · Чёрный',
     'Классическая куртка из натуральной кожи с мягкой подкладкой. Подходит для прохладных вечеров и на каждый день.',
     29990, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=900&q=85', 'S,M,L,XL'),
    ('Городские', 'Urban', 'Лёгкая куртка для города',
     'Лёгкая куртка с водоотталкивающей пропиткой. Не стесняет движений и быстро складывается в рюкзак.',
     24990, 'https://images.unsplash.com/photo-1544923246-77307dd654cb?w=900&q=85', 'S,M,L'),
    ('Повседневные', 'Daily', 'Повседневная куртка',
     'Универсальная куртка на каждый день: плотная ткань, удобный крой и несколько карманов.',
     34990, 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=900&q=85', 'M,L,XL,XXL')
) AS p(category, name, subtitle, description, price, image_url, sizes)
JOIN categories c ON c.name = p.category
WHERE NOT EXISTS (SELECT 1 FROM products);

UPDATE users SET email = 'admin@gmail.com'
WHERE email = 'admin@north.local' AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@gmail.com');

INSERT INTO users (name, email, password_hash, role) VALUES
    ('Администратор', 'admin@gmail.com', '$2y$10$AAkZp5rouJTCWOVZdHIJFeNKHT/RPhUC1SFC0J.pJFe3wIN9lISV2', 'admin')
ON CONFLICT (email) DO NOTHING;
